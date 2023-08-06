<?php

namespace Skywarth\ChaoticSchedule\Services;

class SeedGenerationService
{
    //TODO: constructor and property: date. It'll enable mocking date.


    public function seedForDay(string $uniqueIdentifier):int{
        return intval(date('Ymd').$this->castUniqueIdentifier($uniqueIdentifier));
    }

    public function seedForWeek(string $uniqueIdentifier):int{
        return intval(date('YW').$this->castUniqueIdentifier($uniqueIdentifier));
    }


    protected function castUniqueIdentifier(string $uniqueIdentifier){
        return crc32($uniqueIdentifier);
    }

}