<?php

namespace Skywarth\ChaoticSchedule\RNGs\Adapters;


use ParagonIE\SeedSpring\SeedSpring;

class SeedSpringAdapter extends AbstractRNGAdapter
{


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