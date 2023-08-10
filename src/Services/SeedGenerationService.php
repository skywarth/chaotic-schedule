<?php

namespace Skywarth\ChaoticSchedule\Services;

use Carbon\Carbon;

class SeedGenerationService
{
    //TODO: constructor and property: date. It'll enable mocking date.

    const SEED_LENGTH=16;//Because 'Seed Spring' RNG requires 16 byte integer. Maybe we may move padding to adapters if this alternates greatly between RNGs
    const PADDING_STRING='0';


    private Carbon $basisDate;

    /**
     * @param Carbon|null $basisDate
     */
    public function __construct(?Carbon $basisDate=null)
    {
        if(empty($basisDate)){
            $basisDate=Carbon::now();
        }
        $this->basisDate = $basisDate;
    }


    public function seedForDay(string $uniqueIdentifier):int{
        $str= $this->castUniqueIdentifier($uniqueIdentifier).$this->dateString('Dzy');
        $hashed=$this->hash($str);
        $seed= $this->castToSeedFormat($hashed);
        return $seed;
    }

    public function seedForWeek(string $uniqueIdentifier):int{
        $str= $this->castUniqueIdentifier($uniqueIdentifier).$this->dateString('Wy');
        $hashed=$this->hash($str);
        $seed= $this->castToSeedFormat($hashed);
        return $seed;
    }

    private function dateString(string $format):string{
        return $this->basisDate->format($format);
    }

    private function castToSeedFormat(string $hash):int{
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