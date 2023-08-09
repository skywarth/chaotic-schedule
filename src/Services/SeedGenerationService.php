<?php

namespace Skywarth\ChaoticSchedule\Services;

use Carbon\Carbon;

class SeedGenerationService
{
    //TODO: constructor and property: date. It'll enable mocking date.

    const SEED_LENGTH=16;//Because 'Seed Spring' RNG requires 16 byte integer. Maybe we may move padding to adapters if this alternates greatly between RNGs
    const PADDING_STRING='0';


    public function seedForDay(string $uniqueIdentifier):int{
        //return intval($this->dateString('ymd').$this->castUniqueIdentifier($uniqueIdentifier));
        $str= $this->dateString('Dzy').$this->castUniqueIdentifier($uniqueIdentifier);
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
        return Carbon::now()->addDays(6)->format($format);
    }

    private function castToSeedFormat(string $hash):int{
        //TODO: maybe shuffle the string, then take X, then cast to intval ? NO, NO shuffling, it should be consistent !!!
        //TODO: and the formatting (length,bytes)
        $hash=str_pad($hash,self::SEED_LENGTH,self::PADDING_STRING);
        $hash=substr($hash,0,self::SEED_LENGTH);
        return intval($hash);

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