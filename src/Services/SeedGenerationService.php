<?php

namespace Skywarth\ChaoticSchedule\Services;

class SeedGenerationService
{
    public function seedForDay():int{
        return intval(date('Ymd'));
    }

    public function seedForWeek():int{
        return intval(date('YW'));
    }

}