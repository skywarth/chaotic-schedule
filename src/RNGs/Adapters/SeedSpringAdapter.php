<?php

namespace Skywarth\ChaoticSchedule\RNGs\Adapters;


use ParagonIE\SeedSpring\SeedSpring;

class SeedSpringAdapter implements RandomNumberGeneratorAdapter
{

    private SeedSpring $rng;

    public function __construct(int $seed)
    {
        $this->setSeed($seed);

    }

    public function setSeed(int $seed)
    {
        $this->rng=new SeedSpring($seed);
    }

    public function intBetween(int $floor, int $ceil): int
    {
        $this->rng->getInt($floor,$ceil);
    }
}