<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ImportUsersFromExcel extends Command
{
    protected $signature = 'users:import {file} {--dry-run}';
    protected $description = 'Import users from Excel file';

    private $stats = [
        'total_rows' => 0,
        'new_users' => 0,
        'updated_users' => 0,
        'invalid_emails' => 0,
        'duplicate_emails' => 0,
        'skipped_rows' => 0,
        'failed' => 0,
    ];

    private $processed_emails = [];
    private $default_password = 'Mahakarya2026';

    public function handle()
    {
        $file = $this->argument('file');
        $dry_run = $this->option('dry-run');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        try {
            $rows = $this->readExcel($file);

            if (empty($rows)) {
                $this->error("Excel file is empty");
                return 1;
            }

            // Skip header row
            $headers = array_shift($rows);
            $this->info("Headers: " . implode(", ", $headers));

            $processed = [];

            foreach ($rows as $index => $row) {
                $row_number = $index + 2; // +2 because we removed header and array index starts at 0
                
                // Skip completely empty rows
                if (empty(array_filter($row))) {
                    $this->stats['skipped_rows']++;
                    continue;
                }

                $this->stats['total_rows']++;

                // Extract columns - adjust indices based on actual Excel structure
                $email = trim($row[0] ?? '');
                $first_name = trim($row[1] ?? '');
                $last_name = trim($row[2] ?? '');
                $job_title = trim($row[3] ?? '');
                $department_name = trim($row[4] ?? '');
                $email_smtp = trim($row[5] ?? '');

                // Use email_smtp as fallback
                if (empty($email) && !empty($email_smtp)) {
                    $email = $email_smtp;
                }

                // Normalize email
                $email = strtolower($email);

                // Validate email
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->stats['invalid_emails']++;
                    continue;
                }

                // Check for duplicates within this import
                if (in_array($email, $this->processed_emails)) {
                    $this->stats['duplicate_emails']++;
                    $this->warn("Row $row_number: Duplicate email in import - $email");
                    continue;
                }

                $this->processed_emails[] = $email;

                // Build full name
                $name = trim("$first_name $last_name");
                if (empty($name)) {
                    $name = $email; // Fallback to email
                }

                $processed[] = [
                    'row_number' => $row_number,
                    'email' => $email,
                    'name' => $name,
                    'job_title' => !empty($job_title) ? $job_title : null,
                    'department_name' => !empty($department_name) ? $department_name : null,
                ];
            }

            // Get default role (user)
            $user_role = Role::where('slug', 'user')->first();
            if (!$user_role) {
                $this->error("Default role 'user' not found in database");
                return 1;
            }

            if ($dry_run) {
                $this->performDryRun($processed, $user_role);
            } else {
                $this->performActualImport($processed, $user_role);
            }

            $this->displaySummary();
            return 0;

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }

    private function readExcel($file)
    {
        // Check if file is .xlsx
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'xlsx') {
            return $this->readXlsx($file);
        } else if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'csv') {
            return $this->readCsv($file);
        } else {
            throw new \Exception("Unsupported file format. Use .xlsx or .csv");
        }
    }

    private function readXlsx($file)
    {
        $rows = [];
        
        // Create temporary directory
        $tempDir = sys_get_temp_dir() . '/xlsx_' . uniqid();
        @mkdir($tempDir);

        try {
            // Extract XLSX (it's a ZIP file)
            $zip = new \ZipArchive();
            if (!$zip->open($file)) {
                throw new \Exception("Cannot open Excel file");
            }
            $zip->extractTo($tempDir);
            $zip->close();

            // Read the sheet XML
            $xml_file = $tempDir . '/xl/worksheets/sheet1.xml';
            if (!file_exists($xml_file)) {
                throw new \Exception("Cannot find sheet1.xml in Excel file");
            }

            $xml_content = file_get_contents($xml_file);
            $xml = simplexml_load_string($xml_content);
            
            // Register namespace if needed
            $namespaces = $xml->getNamespaces(true);
            
            $sharedStrings = $this->readSharedStrings($tempDir);

            // Parse rows
            $max_row = 0;
            foreach ($xml->children() as $child) {
                if ($child->getName() == 'sheetData') {
                    foreach ($child->children() as $row_elem) {
                        if ($row_elem->getName() == 'row') {
                            $row_index = (int)$row_elem['r'];
                            $max_row = max($max_row, $row_index);
                            
                            $rowData = [];
                            
                            foreach ($row_elem->children() as $cell) {
                                if ($cell->getName() == 'c') {
                                    $value = '';
                                    $cellRef = (string)$cell['r'];
                                    
                                    // Extract column letter
                                    preg_match('/([A-Z]+)/', $cellRef, $matches);
                                    $col_letter = $matches[0] ?? 'A';
                                    $col_index = $this->colLetterToIndex($col_letter);
                                    
                                    // Pad array to reach this column
                                    while (count($rowData) <= $col_index) {
                                        $rowData[] = '';
                                    }
                                    
                                    // Get cell value
                                    $cell_type = (string)$cell['t'] ?? '';
                                    
                                    if ($cell->v) {
                                        if ($cell_type === 's') {
                                            // String reference
                                            $index = (int)$cell->v;
                                            $value = isset($sharedStrings[$index]) ? $sharedStrings[$index] : '';
                                        } else {
                                            // Direct value
                                            $value = (string)$cell->v;
                                        }
                                    }
                                    
                                    $rowData[$col_index] = $value;
                                }
                            }
                            
                            $rows[] = array_values($rowData);
                        }
                    }
                }
            }
        } finally {
            // Cleanup
            $this->deleteDirectory($tempDir);
        }

        return $rows;
    }

    private function colLetterToIndex($letters)
    {
        $index = 0;
        $letters = strtoupper($letters);
        for ($i = 0; $i < strlen($letters); $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    private function readSharedStrings($tempDir)
    {
        $strings = [];
        $xml_file = $tempDir . '/xl/sharedStrings.xml';
        
        if (!file_exists($xml_file)) {
            return $strings;
        }

        $xml_content = file_get_contents($xml_file);
        $xml = simplexml_load_string($xml_content);
        
        $index = 0;
        foreach ($xml->children() as $si) {
            if ($si->getName() == 'si') {
                $text = '';
                foreach ($si->children() as $child) {
                    if ($child->getName() == 't') {
                        $text .= (string)$child;
                    }
                }
                $strings[$index] = $text;
                $index++;
            }
        }

        return $strings;
    }

    private function readCsv($file)
    {
        $rows = [];
        if (($handle = fopen($file, 'r')) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }
        return $rows;
    }

    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            $this->deleteDirectory($dir . '/' . $item);
        }

        return rmdir($dir);
    }

    private function performDryRun($processed, $user_role)
    {
        $this->info("\n--- DRY RUN MODE ---\n");
        $this->info("No changes will be made to database.\n");

        foreach ($processed as $data) {
            $existing = User::where('email', $data['email'])->first();

            if ($existing) {
                $this->line("Row {$data['row_number']}: UPDATE - {$data['email']} (existing user)");
                $this->stats['updated_users']++;
            } else {
                $this->line("Row {$data['row_number']}: CREATE - {$data['email']} as '{$data['name']}'");
                $this->stats['new_users']++;
            }
        }
    }

    private function performActualImport($processed, $user_role)
    {
        $this->info("\n--- ACTUAL IMPORT ---\n");

        DB::beginTransaction();

        try {
            foreach ($processed as $data) {
                $existing = User::where('email', $data['email'])->first();

                if ($existing) {
                    // Update existing user
                    $existing->update([
                        'name' => $data['name'],
                        'job_title' => $data['job_title'],
                    ]);

                    // Handle department
                    if (!empty($data['department_name'])) {
                        $dept = Department::where('name', $data['department_name'])->first();
                        if ($dept) {
                            $existing->update(['department_id' => $dept->id]);
                        }
                    }

                    $this->line("✓ Row {$data['row_number']}: Updated {$data['email']}");
                    $this->stats['updated_users']++;
                } else {
                    // Create new user
                    $department_id = null;
                    if (!empty($data['department_name'])) {
                        $dept = Department::where('name', $data['department_name'])->first();
                        if ($dept) {
                            $department_id = $dept->id;
                        }
                    }

                    User::create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'password' => Hash::make($this->default_password),
                        'role_id' => $user_role->id,
                        'department_id' => $department_id,
                        'job_title' => $data['job_title'],
                        'is_active' => true,
                        'force_password_change' => false,
                    ]);

                    $this->line("✓ Row {$data['row_number']}: Created {$data['email']}");
                    $this->stats['new_users']++;
                }
            }

            DB::commit();
            $this->info("\n✓ Import completed successfully!");

        } catch (\Exception $e) {
            DB::rollback();
            $this->error("\n✗ Import failed: " . $e->getMessage());
            $this->stats['failed']++;
        }
    }

    private function displaySummary()
    {
        $this->info("\n--- SUMMARY ---\n");
        $this->line("Total rows processed    : " . $this->stats['total_rows']);
        $this->line("New users created       : " . $this->stats['new_users']);
        $this->line("Existing users updated  : " . $this->stats['updated_users']);
        $this->line("Invalid emails          : " . $this->stats['invalid_emails']);
        $this->line("Duplicate emails        : " . $this->stats['duplicate_emails']);
        $this->line("Skipped rows            : " . $this->stats['skipped_rows']);
        $this->line("Failed                  : " . $this->stats['failed']);
        $this->info("");
    }
}