<?php

namespace Skywarth\ChaoticSchedule\Services;

use Carbon\Carbon;
use Skywarth\ChaoticSchedule\Enums\RandomDateScheduleBasis;

class SeedGenerationService
{
    public const SEED_LENGTH=16;//Because 'Seed Spring' RNG requires 16 byte integer. Maybe we may move padding to adapters if this alternates greatly between RNGs
    public const PADDING_STRING='0';


    private Carbon $basisDate;

    public function __construct(?Carbon $basisDate = null)
    {
        $this->setBasisDate($basisDate ?? Carbon::now());
    }


    public function seedForHour(string $uniqueIdentifier):int{
        $str= $this->castUniqueIdentifier($uniqueIdentifier).$this->dateString('zHy');
        $hashed=$this->hash($str);
        $seed= $this->castToSeedFormat($hashed);
        return $seed;
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

    public function seedForMonth(string $uniqueIdentifier):int{
        $str= $this->castUniqueIdentifier($uniqueIdentifier).$this->dateString('my');
        $hashed=$this->hash($str);
        $seed= $this->castToSeedFormat($hashed);
        return $seed;
    }

    public function seedForYear(string $uniqueIdentifier):int{
        $str= $this->castUniqueIdentifier($uniqueIdentifier).$this->dateString('Y');
        $hashed=$this->hash($str);
        $seed= $this->castToSeedFormat($hashed);
        return $seed;
    }

    private function dateString(string $format):string{
        return $this->getBasisDate()->format($format);
    }

    private function castToSeedFormat(string $hash):int{
        $hash=str_pad($hash,self::SEED_LENGTH,self::PADDING_STRING);
        $hash=substr($hash,0,self::SEED_LENGTH);
        return intval($hash);

    }

    private function hash(string $raw):string{
        //return hash('crc32',$raw); //Produces hexadecimal format, containing characters.
        return (string)crc32($raw);

    }


    protected function castUniqueIdentifier(string $uniqueIdentifier):string{
        return $uniqueIdentifier;
        //return crc32($uniqueIdentifier);
    }


    public function seedByDateScheduleBasis(string $uniqueIdentifier, RandomDateScheduleBasis $scheduleBasis): int
    {
        return match ($scheduleBasis) {
            RandomDateScheduleBasis::WEEK => $this->seedForWeek($uniqueIdentifier),
            RandomDateScheduleBasis::MONTH => $this->seedForMonth($uniqueIdentifier),
            RandomDateScheduleBasis::YEAR => $this->seedForYear($uniqueIdentifier),
        };
    }

    public function setBasisDate(Carbon $basisDate): self
    {
        //YOU SHOULD BE REALLY CAREFUL USING THIS.
        //This method is meant mostly for testing purposes.
        $this->basisDate = $basisDate;
        return $this;
    }

    public function getBasisDate(): Carbon
    {
        return $this->basisDate->clone();
    }





}