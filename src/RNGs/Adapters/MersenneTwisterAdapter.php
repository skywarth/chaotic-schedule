<?php

namespace Skywarth\ChaoticSchedule\RNGs\Adapters;

use Skywarth\ChaoticSchedule\RNGs\MersenneTwister;

class MersenneTwisterAdapter implements RandomNumberGeneratorAdapter
{
    private MersenneTwister $mersenneTwister;

    private int $seed;

    /**
     * @return int
     */
    public function getSeed(): int
    {
        return $this->seed;
    }

    public function setSeed(int $seed)
    {
        $this->seed=$seed;
        $this->mersenneTwister=new MersenneTwister($seed);
    }



    public function __construct(int $seed)
    {
        $this->mersenneTwister=new MersenneTwister($seed);

    }

    public function intBetween(int $floor, int $ceil): int
    {
        $this->mersenneTwister->rangeint($floor,$ceil);
    }


}