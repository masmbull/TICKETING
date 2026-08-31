<?php

namespace Database\Seeders;

use App\Models\SecurityInsight;
use Illuminate\Database\Seeder;

class SecurityInsightSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            [
                'description' => 'Multiple login attempts detected from an unrecognized IP address for user j.developer.',
                'subject' => 'Brute-force login attempts',
                'insight_type' => 'authentication',
                'severity' => 'high',
                'scan_source' => 'Fail2ban',
                'scan_performed_on' => $now->copy()->subHours(2),
                'status' => 'active',
            ],
            [
                'description' => 'The SSL certificate for mail.mitoelectronics.com expires within the next 14 days.',
                'subject' => 'Expiring SSL certificate',
                'insight_type' => 'certificate',
                'severity' => 'moderate',
                'scan_source' => 'Certificate Monitor',
                'scan_performed_on' => $now->copy()->subHours(5),
                'status' => 'active',
            ],
            [
                'description' => 'A workstation has been running with critical security patches pending for more than 30 days.',
                'subject' => 'Missing critical patches',
                'insight_type' => 'patch',
                'severity' => 'critical',
                'scan_source' => 'WSUS',
                'scan_performed_on' => $now->copy()->subDay(),
                'status' => 'active',
            ],
            [
                'description' => 'Default administrator password still active on the staging database server.',
                'subject' => 'Default credentials in use',
                'insight_type' => 'credential',
                'severity' => 'critical',
                'scan_source' => 'Database Scanner',
                'scan_performed_on' => $now->copy()->subDay(),
                'status' => 'active',
            ],
            [
                'description' => 'Antivirus definitions on the finance department are older than 7 days.',
                'subject' => 'Outdated antivirus definitions',
                'insight_type' => 'endpoint',
                'severity' => 'moderate',
                'scan_source' => 'EDR Console',
                'scan_performed_on' => $now->copy()->subDays(2),
                'status' => 'active',
            ],
            [
                'description' => 'Port 445 is open to the public interface on an edge firewall, increasing exposure risk.',
                'subject' => 'Open SMB port on edge firewall',
                'insight_type' => 'network',
                'severity' => 'high',
                'scan_source' => 'Nessus',
                'scan_performed_on' => $now->copy()->subDays(2),
                'status' => 'active',
            ],
            [
                'description' => 'A staff member has two-factor authentication disabled on their corporate account.',
                'subject' => '2FA disabled on corporate account',
                'insight_type' => 'authentication',
                'severity' => 'low',
                'scan_source' => 'MFA Report',
                'scan_performed_on' => $now->copy()->subDays(3),
                'status' => 'active',
            ],
            [
                'description' => 'Sensitive files were found accessible in a public share without proper permissions.',
                'subject' => 'Over-permissive file share',
                'insight_type' => 'permissions',
                'severity' => 'high',
                'scan_source' => 'SharePoint Scanner',
                'scan_performed_on' => $now->copy()->subDays(4),
                'status' => 'archived',
            ],
            [
                'description' => 'Unused service account found with administrative privileges that can be removed.',
                'subject' => 'Stale privileged account',
                'insight_type' => 'identity',
                'severity' => 'low',
                'scan_source' => 'AD Audit',
                'scan_performed_on' => $now->copy()->subDays(5),
                'status' => 'archived',
            ],
        ];

        foreach ($rows as $row) {
            SecurityInsight::updateOrCreate(
                ['subject' => $row['subject']],
                $row
            );
        }
    }
}
