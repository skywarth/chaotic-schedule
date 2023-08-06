<?php

namespace Skywarth\ChaoticSchedule\Services;

use Carbon\Carbon;

class SeedGenerationService
{
    //TODO: constructor and property: date. It'll enable mocking date.


    public function seedForDay(string $uniqueIdentifier):int{
        return intval($this->dateString('ymd').$this->castUniqueIdentifier($uniqueIdentifier));
    }

    public function seedForWeek(string $uniqueIdentifier):int{
        return intval($this->dateString('yW').$this->castUniqueIdentifier($uniqueIdentifier));
    }

    protected function dateString(string $format):string{
        return Carbon::now()->format($format);
    }


    protected function castUniqueIdentifier(string $uniqueIdentifier){
        return crc32($uniqueIdentifier);
    }

}