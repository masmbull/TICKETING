<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        // Get users by new email addresses
        $admin   = User::where('email', 'admin@mito.local')->first();
        $rina    = User::where('email', 'rina.sari@mito.local')->first();
        $ahmad   = User::where('email', 'ahmad.hidayat@mito.local')->first();
        $dewi    = User::where('email', 'dewi.lestari@mito.local')->first();
        $firmansyah = User::where('email', 'firmansyah@mito.local')->first();
        $siti    = User::where('email', 'siti.nurhaliza@mito.local')->first();
        $budi    = User::where('email', 'budi.prasetyo@mito.local')->first();
        $maya    = User::where('email', 'maya.putri@mito.local')->first();

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
            // Open tickets (various priorities)
            [
                'subject'     => 'Laptop tidak bisa menyala',
                'description' => 'Laptop Dell Latitude 5520 saya tidak bisa menyala sama sekali sudah 2 hari. Sudah dicoba charge tetap tidak menyala.',
                'status'      => 'Open',
                'priority'    => 'critical',
                'user_id'     => $budi->id,
                'category_id' => $hwCat->id,
                'sub_category_id' => $desktopSub?->id,
                'assignee_id' => $siti->id,
                'department_id' => $itDept->id,
                'days_ago'    => 1,
            ],
            [
                'subject'     => 'Internet lambat di lantai 3',
                'description' => 'Koneksi internet di lantai 3 gedung sangat lambat sejak kemarin. Speed test hanya 2 Mbps padahal biasanya 100 Mbps.',
                'status'      => 'Open',
                'priority'    => 'high',
                'user_id'     => $maya->id,
                'category_id' => $netCat->id,
                'sub_category_id' => $inetSub?->id,
                'assignee_id' => $dewi->id,
                'department_id' => $netDept->id,
                'days_ago'    => 2,
            ],
            [
                'subject'     => 'Printer lantai 2 tidak bisa mencetak',
                'description' => 'Printer HP LaserJet di lantai 2 tidak bisa mencetak. Tampilan layar printer menunjukkan error "Paper Jam" sudah dibersihkan tetap error.',
                'status'      => 'Open',
                'priority'    => 'medium',
                'user_id'     => $budi->id,
                'category_id' => $prCat->id,
                'sub_category_id' => $jamSub?->id,
                'assignee_id' => $ahmad->id,
                'department_id' => $itDept->id,
                'days_ago'    => 3,
            ],
            [
                'subject'     => 'Request install Adobe Photoshop',
                'description' => 'Saya membutuhkan Adobe Photoshop untuk keperluan desain marketing. Mohon segera diinstall.',
                'status'      => 'Open',
                'priority'    => 'low',
                'user_id'     => $maya->id,
                'category_id' => $swCat->id,
                'sub_category_id' => $appSub?->id,
                'assignee_id' => null,
                'department_id' => null,
                'days_ago'    => 5,
            ],

            // In Progress tickets
            [
                'subject'     => 'Monitor berkedip terus menerus',
                'description' => 'Monitor LG 24 inch saya berkedip terus menerus sejak update driver kemarin. Sudah coba ganti kabel VGA tetap sama.',
                'status'      => 'In Progress',
                'priority'    => 'high',
                'user_id'     => $budi->id,
                'category_id' => $hwCat->id,
                'sub_category_id' => $monitorSub?->id,
                'assignee_id' => $ahmad->id,
                'department_id' => $itDept->id,
                'days_ago'    => 4,
            ],
            [
                'subject'     => 'VPN tidak bisa connect dari rumah',
                'description' => 'VPN Cisco AnyConnect tidak bisa connect dari rumah. Sudah coba reinstall tetap tidak bisa. Error: "Connection attempt has timed out".',
                'status'      => 'In Progress',
                'priority'    => 'critical',
                'user_id'     => $maya->id,
                'category_id' => $netCat->id,
                'sub_category_id' => $vpnSub?->id,
                'assignee_id' => $dewi->id,
                'department_id' => $netDept->id,
                'days_ago'    => 1,
            ],
            [
                'subject'     => 'Microsoft Teams freeze saat meeting',
                'description' => 'Microsoft Teams sering freeze dan crash saat meeting dengan lebih dari 10 orang. Sudah clear cache dan reinstall tetap terjadi.',
                'status'      => 'In Progress',
                'priority'    => 'medium',
                'user_id'     => $budi->id,
                'category_id' => $m365->id,
                'sub_category_id' => $teamsSub?->id,
                'assignee_id' => $firmansyah->id,
                'department_id' => $itDept->id,
                'days_ago'    => 3,
            ],

            // Waiting User
            [
                'subject'     => 'Blue screen error Windows 11',
                'description' => 'Laptop saya mengalami blue screen error (BSOD) dengan error code IRQL_NOT_LESS_OR_EQUAL. Sudah saya upload dump file.',
                'status'      => 'Waiting User',
                'priority'    => 'high',
                'user_id'     => $maya->id,
                'category_id' => $swCat->id,
                'sub_category_id' => $osSub?->id,
                'assignee_id' => $ahmad->id,
                'department_id' => $itDept->id,
                'days_ago'    => 6,
            ],
            [
                'subject'     => 'Email tidak bisa kirim ke eksternal',
                'description' => 'Email outlook saya tidak bisa mengirim email ke alamat eksternal. Email ke internal bisa. Sudah coba restart Outlook.',
                'status'      => 'Waiting User',
                'priority'    => 'medium',
                'user_id'     => $budi->id,
                'category_id' => $emCat->id,
                'sub_category_id' => $emailSub?->id,
                'assignee_id' => $siti->id,
                'department_id' => $itDept->id,
                'days_ago'    => 5,
            ],

            // Resolved tickets
            [
                'subject'     => 'Keyboard beberapa tombol tidak berfungsi',
                'description' => 'Tombol WASD dan Space pada keyboard mechanical saya tidak berfungsi. Sudah 1 minggu.',
                'status'      => 'Resolved',
                'priority'    => 'medium',
                'user_id'     => $budi->id,
                'category_id' => $hwCat->id,
                'sub_category_id' => null,
                'assignee_id' => $siti->id,
                'department_id' => $itDept->id,
                'days_ago'    => 10,
            ],
            [
                'subject'     => 'Request akses folder shared',
                'description' => 'Saya butuh akses ke folder shared \\\\server\\marketing. Mohon di grant permission.',
                'status'      => 'Resolved',
                'priority'    => 'low',
                'user_id'     => $maya->id,
                'category_id' => $swCat->id,
                'sub_category_id' => $appSub?->id,
                'assignee_id' => $firmansyah->id,
                'department_id' => $itDept->id,
                'days_ago'    => 8,
            ],

            // Closed tickets
            [
                'subject'     => 'Password email expired',
                'description' => 'Password email saya sudah expired dan tidak bisa login. Mohon reset password.',
                'status'      => 'Closed',
                'priority'    => 'medium',
                'user_id'     => $budi->id,
                'category_id' => $emCat->id,
                'sub_category_id' => $emailSub?->id,
                'assignee_id' => $ahmad->id,
                'department_id' => $itDept->id,
                'days_ago'    => 15,
            ],
            [
                'subject'     => 'Printer baru tidak terdeteksi',
                'description' => 'Printer Canon Pixma yang baru dipasang tidak terdeteksi di komputer. Sudah install driver tetap tidak terdeteksi.',
                'status'      => 'Closed',
                'priority'    => 'low',
                'user_id'     => $maya->id,
                'category_id' => $prCat->id,
                'sub_category_id' => $printSub?->id,
                'assignee_id' => $ahmad->id,
                'department_id' => $itDept->id,
                'days_ago'    => 20,
            ],
            [
                'subject'     => 'Laptop overheating',
                'description' => 'Laptop Lenovo ThinkPad saya sering overheating saat menjalankan aplikasi berat. Kipas berputar sangat kencang.',
                'status'      => 'Closed',
                'priority'    => 'medium',
                'user_id'     => $budi->id,
                'category_id' => $hwCat->id,
                'sub_category_id' => $desktopSub?->id,
                'assignee_id' => $siti->id,
                'department_id' => $itDept->id,
                'days_ago'    => 25,
            ],
        ];

        $ticketNumber = 1;

        foreach ($tickets as $data) {
            $daysAgo = $data['days_ago'];
            unset($data['days_ago']);

            $createdAt = Carbon::now()->subDays($daysAgo)->subHours(rand(0, 8));
            $updatedAt = $createdAt->copy()->addHours(rand(1, 24));

            $ticket = Ticket::create(array_merge($data, [
                'ticket_number' => 'HD-' . $createdAt->format('Ymd') . '-' . str_pad($ticketNumber, 6, '0', STR_PAD_LEFT),
                'sla_priority'  => $data['priority'],
                'created_at'    => $createdAt,
                'updated_at'    => $updatedAt,
            ]));

            // Add first response for non-Open tickets
            if ($data['status'] !== 'Open' && $data['assignee_id']) {
                $ticket->update([
                    'first_response_at' => $createdAt->copy()->addMinutes(rand(15, 120)),
                ]);
            }

            // Add resolved_at for Resolved/Closed
            if (in_array($data['status'], ['Resolved', 'Closed'])) {
                $ticket->update([
                    'resolved_at' => $updatedAt->copy()->subHours(rand(1, 48)),
                ]);
            }

            // Add closed_at for Closed
            if ($data['status'] === 'Closed' && $ticket->resolved_at) {
                $ticket->update([
                    'closed_at' => $ticket->resolved_at->copy()->addHours(rand(1, 72)),
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