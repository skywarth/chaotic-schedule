<?php

namespace Skywarth\ChaoticSchedule\RNGs;

use Skywarth\ChaoticSchedule\RNGs\Adapters\MersenneTwisterAdapter;
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\Adapters\SeedSpringAdapter;
use UnexpectedValueException;

class RNGFactory
{
    public function __construct(private readonly string $rngEngineSlug)
    {
    }

    public function getRngEngineSlug(): string
    {
        return $this->rngEngineSlug;
    }

    public function getRngEngine(?int $seed = null): RandomNumberGeneratorAdapter
    {
        return match ($this->rngEngineSlug) {
            MersenneTwisterAdapter::getAdapterSlug() => new MersenneTwisterAdapter($seed),
            SeedSpringAdapter::getAdapterSlug() => new SeedSpringAdapter($seed),
            default => throw new UnexpectedValueException(
                'Please provide a valid RNG Adapter slug. Example: "mersenne-twister". Check the documentation for details.'
            ),
        };
    }
}
