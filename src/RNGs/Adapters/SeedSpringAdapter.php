<?php

namespace Skywarth\ChaoticSchedule\RNGs\Adapters;


use ParagonIE\SeedSpring\SeedSpring;

class SeedSpringAdapter extends AbstractRNGAdapter
{

    const PROVIDER_SEED_BYTES=16;
    private SeedSpring $seedSpring;


    public function intBetween(int $floor, int $ceil): int
    {
        return $this->seedSpring->getInt($floor,$ceil);
    }

    public static function getAdapterSlug(): string
    {
        return 'seed-spring';
    }

    public function getSlug(): string
    {
        return 'seed-spring';
    }

    public function validateSeed(int $seed): bool
    {
        $binaryRepresentation = decbin($seed);

        // Check the length of the binary representation
        return strlen($binaryRepresentation) <= (self::PROVIDER_SEED_BYTES * 8);
    }

    protected function setProviderSeed(int $seed): RandomNumberGeneratorAdapter
    {
        $this->seedSpring=new SeedSpring($seed);
        return $this;
    }
}