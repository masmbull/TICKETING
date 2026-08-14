<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Department;
use App\Models\SlaPolicy;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        // Get users by email addresses (must match UserSeeder)
        $admin      = User::where('email', 'admin@mito.local')->first();
        $manager    = User::where('email', 'manager.it@mito.local')->first();
        $shohibul   = User::where('email', 'shohibul@mito.local')->first();
        $riyanto    = User::where('email', 'riyanto.it@mito.local')->first();
        $daniel     = User::where('email', 'daniel@mito.local')->first();
        $marketing1 = User::where('email', 'marketing1@mito.local')->first();
        $finance1   = User::where('email', 'finance1@mito.local')->first();
        $warehouse1 = User::where('email', 'warehouse1@mito.local')->first();

        // Get categories
        $hwCat  = Category::where('slug', 'hardware')->first();
        $swCat  = Category::where('slug', 'software')->first();
        $netCat = Category::where('slug', 'network')->first();
        $prCat  = Category::where('slug', 'printer')->first();
        $emCat  = Category::where('slug', 'email')->first();
        $m365   = Category::where('slug', 'microsoft-365')->first();

        // Get subcategories
        $desktopSub = SubCategory::where('slug', 'desktop-laptop')->first();
        $monitorSub = SubCategory::where('slug', 'monitor')->first();
        $osSub      = SubCategory::where('slug', 'operating-system')->first();
        $appSub     = SubCategory::where('slug', 'application')->first();
        $inetSub    = SubCategory::where('slug', 'internet')->first();
        $vpnSub     = SubCategory::where('slug', 'vpn')->first();
        $printSub   = SubCategory::where('slug', 'setup')->first();
        $jamSub     = SubCategory::where('slug', 'paper-jam')->first();
        $emailSub   = SubCategory::where('slug', 'access')->first();
        $teamsSub   = SubCategory::where('slug', 'teams')->first();

        // Departments
        $itDept  = Department::where('slug', 'it-support')->first();
        $netDept = Department::where('slug', 'network')->first();

        $tickets = [
            // Waiting Confirmation tickets (newly submitted)
            [
                'description' => 'Laptop Dell Latitude 5520 saya tidak bisa menyala sama sekali sudah 2 hari. Sudah dicoba charge tetap tidak menyala.',
                'status'      => 'Waiting Confirmation',
                'priority'    => 'critical',
                'user_id'     => $marketing1->id,
                'category_id' => $hwCat->id,
                'sub_category_id' => $desktopSub?->id,
                'assignee_id' => $shohibul->id,
                'department_id' => $itDept->id,
                'days_ago'    => 1,
            ],
            [
                'description' => 'Koneksi internet di lantai 3 gedung sangat lambat sejak kemarin. Speed test hanya 2 Mbps padahal biasanya 100 Mbps.',
                'status'      => 'Waiting Confirmation',
                'priority'    => 'high',
                'user_id'     => $finance1->id,
                'category_id' => $netCat->id,
                'sub_category_id' => $inetSub?->id,
                'assignee_id' => $riyanto->id,
                'department_id' => $netDept->id,
                'days_ago'    => 2,
            ],
            [
                'description' => 'Printer HP LaserJet di lantai 2 tidak bisa mencetak. Tampilan layar printer menunjukkan error "Paper Jam" sudah dibersihkan tetap error.',
                'status'      => 'Waiting Confirmation',
                'priority'    => 'medium',
                'user_id'     => $marketing1->id,
                'category_id' => $prCat->id,
                'sub_category_id' => $jamSub?->id,
                'assignee_id' => $shohibul->id,
                'department_id' => $itDept->id,
                'days_ago'    => 3,
            ],
            [
                'description' => 'Saya membutuhkan Adobe Photoshop untuk keperluan desain marketing. Mohon segera diinstall.',
                'status'      => 'Waiting Confirmation',
                'priority'    => 'low',
                'user_id'     => $finance1->id,
                'category_id' => $swCat->id,
                'sub_category_id' => $appSub?->id,
                'assignee_id' => null,
                'department_id' => null,
                'days_ago'    => 5,
            ],

            // In Progress tickets (analysed and being worked on)
            [
                'description' => 'Monitor LG 24 inch saya berkedip terus menerus sejak update driver kemarin. Sudah coba ganti kabel VGA tetap sama.',
                'status'      => 'In Progress',
                'priority'    => 'high',
                'user_id'     => $marketing1->id,
                'category_id' => $hwCat->id,
                'sub_category_id' => $monitorSub?->id,
                'assignee_id' => $shohibul->id,
                'department_id' => $itDept->id,
                'problem_analysis' => 'Flickering occurs after the latest graphics driver update; suspecting driver regression or refresh-rate conflict on the LG panel.',
                'days_ago'    => 4,
            ],
            [
                'description' => 'VPN Cisco AnyConnect tidak bisa connect dari rumah. Sudah coba reinstall tetap tidak bisa. Error: "Connection attempt has timed out".',
                'status'      => 'In Progress',
                'priority'    => 'critical',
                'user_id'     => $finance1->id,
                'category_id' => $netCat->id,
                'sub_category_id' => $vpnSub?->id,
                'assignee_id' => $riyanto->id,
                'department_id' => $netDept->id,
                'problem_analysis' => 'Timeout at the connection phase points to the corporate firewall blocking AnyConnect UDP port; testing TCP fallback and checking firewall ACLs.',
                'days_ago'    => 1,
            ],
            [
                'description' => 'Microsoft Teams sering freeze dan crash saat meeting dengan lebih dari 10 orang. Sudah clear cache dan reinstall tetap terjadi.',
                'status'      => 'In Progress',
                'priority'    => 'medium',
                'user_id'     => $marketing1->id,
                'category_id' => $m365->id,
                'sub_category_id' => $teamsSub?->id,
                'assignee_id' => $shohibul->id,
                'department_id' => $itDept->id,
                'problem_analysis' => 'Crashes occur specifically in large meetings; GPU hardware acceleration is the likely cause and is being disabled for the affected profile.',
                'days_ago'    => 3,
            ],
            [
                'description' => 'Laptop saya mengalami blue screen error (BSOD) dengan error code IRQL_NOT_LESS_OR_EQUAL. Sudah saya upload dump file.',
                'status'      => 'Waiting Confirmation',
                'priority'    => 'high',
                'user_id'     => $finance1->id,
                'category_id' => $swCat->id,
                'sub_category_id' => $osSub?->id,
                'assignee_id' => $shohibul->id,
                'department_id' => $itDept->id,
                'days_ago'    => 6,
            ],
            [
                'description' => 'Email outlook saya tidak bisa mengirim email ke alamat eksternal. Email ke internal bisa. Sudah coba restart Outlook.',
                'status'      => 'Waiting Confirmation',
                'priority'    => 'medium',
                'user_id'     => $marketing1->id,
                'category_id' => $emCat->id,
                'sub_category_id' => $emailSub?->id,
                'assignee_id' => $riyanto->id,
                'department_id' => $itDept->id,
                'days_ago'    => 5,
            ],

            // Completed tickets (analysis + resolution + completion metadata)
            [
                'description' => 'Tombol WASD dan Space pada keyboard mechanical saya tidak berfungsi. Sudah 1 minggu.',
                'status'      => 'Completed',
                'priority'    => 'medium',
                'user_id'     => $marketing1->id,
                'category_id' => $hwCat->id,
                'sub_category_id' => null,
                'assignee_id' => $riyanto->id,
                'department_id' => $itDept->id,
                'problem_analysis' => 'Multiple keys on the mechanical keyboard stopped registering at the same time; issue isolated to the keyboard PCB rather than the OS.',
                'resolution' => 'Replaced the faulty mechanical keyboard with a spare unit; all keys verified and working.',
                'days_ago'    => 10,
            ],
            [
                'description' => 'Saya butuh akses ke folder shared \\\\server\\marketing. Mohon di grant permission.',
                'status'      => 'Completed',
                'priority'    => 'low',
                'user_id'     => $finance1->id,
                'category_id' => $swCat->id,
                'sub_category_id' => $appSub?->id,
                'assignee_id' => $shohibul->id,
                'department_id' => $itDept->id,
                'problem_analysis' => 'User requires read/write access to the shared marketing folder; access was not present in the security group.',
                'resolution' => 'Added user to the MARKETING-FS-RO group; access to \\\\server\\marketing verified successfully.',
                'days_ago'    => 8,
            ],
            [
                'description' => 'Password email saya sudah expired dan tidak bisa login. Mohon reset password.',
                'status'      => 'Completed',
                'priority'    => 'medium',
                'user_id'     => $marketing1->id,
                'category_id' => $emCat->id,
                'sub_category_id' => $emailSub?->id,
                'assignee_id' => $shohibul->id,
                'department_id' => $itDept->id,
                'problem_analysis' => 'Password reached its expiry date and the user was locked out; confirmed via the identity provider audit log.',
                'resolution' => 'Reset the password and enforced a password change at next logon; user confirmed login is working.',
                'days_ago'    => 15,
            ],
            [
                'description' => 'Printer Canon Pixma yang baru dipasang tidak terdeteksi di komputer. Sudah install driver tetap tidak terdeteksi.',
                'status'      => 'Completed',
                'priority'    => 'low',
                'user_id'     => $finance1->id,
                'category_id' => $prCat->id,
                'sub_category_id' => $printSub?->id,
                'assignee_id' => $shohibul->id,
                'department_id' => $itDept->id,
                'problem_analysis' => 'New printer connected via USB but the spooler did not detect the device; driver installation had been done before the printer was connected.',
                'resolution' => 'Re-installed the driver with the printer connected and restarted the print spooler; test page printed successfully.',
                'days_ago'    => 20,
            ],
            [
                'description' => 'Laptop Lenovo ThinkPad saya sering overheating saat menjalankan aplikasi berat. Kipas berputar sangat kencang.',
                'status'      => 'Completed',
                'priority'    => 'medium',
                'user_id'     => $marketing1->id,
                'category_id' => $hwCat->id,
                'sub_category_id' => $desktopSub?->id,
                'assignee_id' => $riyanto->id,
                'department_id' => $itDept->id,
                'problem_analysis' => 'High CPU/GPU temperatures under load with the fan at maximum; dust build-up inside the cooling assembly was blocking airflow.',
                'resolution' => 'Cleaned the cooling assembly, replaced the thermal paste and calibrated the fan curve; temperatures are back to normal under load.',
                'days_ago'    => 25,
            ],
        ];

        $ticketNumber = 1;

        foreach ($tickets as $data) {
            $daysAgo = $data['days_ago'];
            unset($data['days_ago']);

            $createdAt = Carbon::now()->subDays($daysAgo)->subHours(rand(0, 8));
            $updatedAt = $createdAt->copy()->addHours(rand(1, 24));

            $slaPolicy = SlaPolicy::where('priority', $data['priority'])->where('is_active', true)->first();
            $slaStartedAt = $slaPolicy ? $createdAt : null;
            $slaDeadline = $slaPolicy ? $createdAt->copy()->addHours($slaPolicy->resolution_hours) : null;

            $ticket = Ticket::create(array_merge($data, [
                'ticket_number' => 'ITSUP-' . $createdAt->format('Ymd') . '-' . str_pad($ticketNumber, 5, '0', STR_PAD_LEFT),
                'sla_priority'  => $slaPolicy ? $data['priority'] : null,
                'sla_started_at' => $slaStartedAt,
                'sla_deadline'   => $slaDeadline,
                'created_at'    => $createdAt,
                'updated_at'    => $updatedAt,
            ]));

            // Add first response for tickets being worked on (In Progress / Completed)
            if ($data['status'] !== 'Waiting Confirmation' && $data['assignee_id']) {
                $ticket->update([
                    'first_response_at' => $createdAt->copy()->addMinutes(rand(15, 120)),
                ]);
            }

            // Add completion metadata for Completed tickets
            if ($data['status'] === 'Completed') {
                $ticket->update([
                    'completed_at' => $updatedAt->copy()->subHours(rand(1, 48)),
                    'completed_by' => $data['assignee_id'],
                ]);
            }

            // Add sample comments for some tickets
            if (in_array($ticketNumber, [1, 2, 5, 6, 8, 9, 10, 11])) {
                if ($data['assignee_id']) {
                    TicketComment::create([
                        'ticket_id'  => $ticket->id,
                        'user_id'    => $data['assignee_id'],
                        'comment'    => 'Sedang saya cek, mohon tunggu sebentar.',
                        'created_at' => $createdAt->copy()->addMinutes(rand(30, 120)),
                    ]);
                }
            }

            if (in_array($ticketNumber, [5, 6, 8])) {
                TicketComment::create([
                    'ticket_id'  => $ticket->id,
                    'user_id'    => $data['user_id'],
                    'comment'    => 'Ada update dari saya, silakan dicek kembali.',
                    'created_at' => $createdAt->copy()->addHours(rand(2, 6)),
                ]);
            }

            $ticketNumber++;
        }
    }
}