<?php

namespace Skywarth\ChaoticSchedule\RNGs\Adapters;

use Skywarth\ChaoticSchedule\RNGs\MersenneTwister;

class MersenneTwisterAdapter extends AbstractRNGAdapter
{
    private MersenneTwister $mersenneTwister;



    public function setSeed(int $seed):MersenneTwisterAdapter
    {
        $this->seed=$seed;
        $this->mersenneTwister=new MersenneTwister($seed);
        return $this;
    }


    public function intBetween(int $floor, int $ceil): int
    {
        return $this->mersenneTwister->rangeint($floor,$ceil);
    }


    public static function getSlug(): string
    {
        return 'mersenne-twister';
    }

    public static function validateSeed(): bool
    {
        // TODO: Implement validateSeed() method.
    }
}