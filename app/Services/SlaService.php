<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class SlaService
{
    public const START_HOUR = 8;
    public const START_MINUTE = 30;
    public const END_HOUR = 17;
    public const END_MINUTE = 30;

    public function calculateDeadline(Carbon $start, int $businessDays): Carbon
    {
        $deadline = $start->copy();
        $daysAdded = 0;

        while ($daysAdded < $businessDays) {
            $deadline->addDay();
            if ($deadline->isWeekend()) {
                continue;
            }
            $daysAdded++;
        }

        return $deadline->setTime(self::END_HOUR, self::END_MINUTE);
    }
}
