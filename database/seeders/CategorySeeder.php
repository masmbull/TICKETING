<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Hardware
        $hardware = Category::create([
            'name' => 'Hardware',
            'slug' => 'hardware',
            'description' => 'Physical computer components and peripherals',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $hardware->id,
            'name' => 'Desktop/Laptop',
            'slug' => 'desktop-laptop',
            'description' => 'Desktop computers and laptops',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $hardware->id,
            'name' => 'Monitor',
            'slug' => 'monitor',
            'description' => 'Display monitors and screens',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $hardware->id,
            'name' => 'Keyboard/Mouse',
            'slug' => 'keyboard-mouse',
            'description' => 'Input devices',
            'is_active' => true,
        ]);

        // Software
        $software = Category::create([
            'name' => 'Software',
            'slug' => 'software',
            'description' => 'Software applications and operating systems',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $software->id,
            'name' => 'Operating System',
            'slug' => 'operating-system',
            'description' => 'Windows, macOS, Linux issues',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $software->id,
            'name' => 'Application',
            'slug' => 'application',
            'description' => 'Software applications and programs',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $software->id,
            'name' => 'Installation',
            'slug' => 'installation',
            'description' => 'Software installation requests',
            'is_active' => true,
        ]);

        // Network
        $network = Category::create([
            'name' => 'Network',
            'slug' => 'network',
            'description' => 'Network connectivity and infrastructure',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $network->id,
            'name' => 'Internet',
            'slug' => 'internet',
            'description' => 'Internet connectivity issues',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $network->id,
            'name' => 'VPN',
            'slug' => 'vpn',
            'description' => 'VPN connection issues',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $network->id,
            'name' => 'LAN/WAN',
            'slug' => 'lan-wan',
            'description' => 'Local and wide area network issues',
            'is_active' => true,
        ]);

        // Printer
        $printer = Category::create([
            'name' => 'Printer',
            'slug' => 'printer',
            'description' => 'Printers and printing issues',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $printer->id,
            'name' => 'Setup',
            'slug' => 'setup',
            'description' => 'Printer installation and setup',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $printer->id,
            'name' => 'Driver',
            'slug' => 'driver',
            'description' => 'Printer driver issues',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $printer->id,
            'name' => 'Paper Jam',
            'slug' => 'paper-jam',
            'description' => 'Paper jam and hardware issues',
            'is_active' => true,
        ]);

        // Email
        $email = Category::create([
            'name' => 'Email',
            'slug' => 'email',
            'description' => 'Email services and issues',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $email->id,
            'name' => 'Access',
            'slug' => 'access',
            'description' => 'Email access issues',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $email->id,
            'name' => 'Configuration',
            'slug' => 'configuration',
            'description' => 'Email client configuration',
            'is_active' => true,
        ]);

        // Microsoft 365
        $m365 = Category::create([
            'name' => 'Microsoft 365',
            'slug' => 'microsoft-365',
            'description' => 'Microsoft 365 suite applications',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $m365->id,
            'name' => 'Outlook',
            'slug' => 'outlook',
            'description' => 'Microsoft Outlook issues',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $m365->id,
            'name' => 'Teams',
            'slug' => 'teams',
            'description' => 'Microsoft Teams issues',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $m365->id,
            'name' => 'OneDrive',
            'slug' => 'onedrive',
            'description' => 'OneDrive file sharing and sync',
            'is_active' => true,
        ]);

        // Server
        $server = Category::create([
            'name' => 'Server',
            'slug' => 'server',
            'description' => 'Server infrastructure and services',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $server->id,
            'name' => 'Access',
            'slug' => 'server-access',
            'description' => 'Server access issues',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $server->id,
            'name' => 'Performance',
            'slug' => 'server-performance',
            'description' => 'Server performance issues',
            'is_active' => true,
        ]);

        // CCTV
        $cctv = Category::create([
            'name' => 'CCTV',
            'slug' => 'cctv',
            'description' => 'CCTV and surveillance systems',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $cctv->id,
            'name' => 'Camera',
            'slug' => 'camera',
            'description' => 'Camera issues',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $cctv->id,
            'name' => 'Recording',
            'slug' => 'recording',
            'description' => 'Recording and storage issues',
            'is_active' => true,
        ]);

        // Website
        $website = Category::create([
            'name' => 'Website',
            'slug' => 'website',
            'description' => 'Company website and web applications',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $website->id,
            'name' => 'Content',
            'slug' => 'content',
            'description' => 'Website content updates',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $website->id,
            'name' => 'Bug',
            'slug' => 'bug',
            'description' => 'Website bugs and errors',
            'is_active' => true,
        ]);

        // Other
        $other = Category::create([
            'name' => 'Other',
            'slug' => 'other',
            'description' => 'Other issues not covered by other categories',
            'is_active' => true,
        ]);

        SubCategory::create([
            'category_id' => $other->id,
            'name' => 'General',
            'slug' => 'general',
            'description' => 'General inquiries',
            'is_active' => true,
        ]);
    }
}