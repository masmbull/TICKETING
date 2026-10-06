<?php

namespace Database\Seeders;

use App\Models\TooltipSetting;
use Illuminate\Database\Seeder;

class TooltipSettingSeeder extends Seeder
{
    public function run(): void
    {
        TooltipSetting::updateOrCreate(
            ['id' => 1],
            [
                'enabled' => false,
                'steps' => TooltipSetting::defaultSteps(),
            ]
        );
    }
}