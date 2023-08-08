<?php

namespace Skywarth\ChaoticSchedule\Services;

use Carbon\Carbon;

class SeedGenerationService
{
    //TODO: constructor and property: date. It'll enable mocking date.


    public function seedForDay(string $uniqueIdentifier):int{
        //return intval($this->dateString('ymd').$this->castUniqueIdentifier($uniqueIdentifier));
        $str= $this->dateString('Dzy').$uniqueIdentifier;
        dump('concat:'.$str);
        $hashed=$this->hash($str);
        dump([
            'hashed'=>$hashed
        ]);
        $seed= $this->castToSeedFormat($hashed);
        dump($seed);
        return $seed;
    }

    public function seedForWeek(string $uniqueIdentifier):int{
        //return intval($this->dateString('yW').$this->castUniqueIdentifier($uniqueIdentifier));
        $str= $this->dateString('zW').$uniqueIdentifier;
        return $this->castToSeedFormat($str);
    }

    private function dateString(string $format):string{
        return Carbon::now()->addDays(29)->format($format);
    }

    private function castToSeedFormat(string $str):int{
        //TODO: maybe shuffle the string, then take X, then cast to intval ? NO, NO shuffling, it should be consistent !!!
        //TODO: and the formatting (length,bytes)
        return intval($str);

    }

    private function hash(string $raw):string{
        //return hash('crc32',$raw); //Produces hexadecimal format, containing characters.
        return crc32($raw);

    }


    protected function castUniqueIdentifier(string $uniqueIdentifier):string{
        return $uniqueIdentifier;
        //return crc32($uniqueIdentifier);
    }

}