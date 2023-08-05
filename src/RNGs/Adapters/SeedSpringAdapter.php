<?php

namespace Skywarth\ChaoticSchedule\RNGs\Adapters;


use ParagonIE\SeedSpring\SeedSpring;

class SeedSpringAdapter implements RandomNumberGeneratorAdapter
{

    private SeedSpring $rng;

    public function __construct(int $seed=null)
    {
        //TODO: Abstract class for rng adapters. Constructors and some getters are common.
        //TODO: getSeed function on abstract, throw exception in there perhaps.
        if(!is_null($seed)){
            $this->setSeed($seed);
        }

    }

    public function setSeed(int $seed):SeedSpringAdapter
    {
        $this->rng=new SeedSpring($seed);
        return $this;
    }

    public function intBetween(int $floor, int $ceil): int
    {
        return $this->rng->getInt($floor,$ceil);
    }

    public static function getSlug(): string
    {
        return 'seed-spring';
    }
}