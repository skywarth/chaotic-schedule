<?php

namespace Skywarth\ChaoticSchedule\RNGs\Adapters;

use Skywarth\ChaoticSchedule\Exceptions\MissingSeedException;
use Skywarth\ChaoticSchedule\RNGs\MersenneTwister;

class MersenneTwisterAdapter extends AbstractRNGAdapter
{
    private ?MersenneTwister $mersenneTwister = null;


    /**
     * @throws MissingSeedException
     */
    public function intBetween(int $floor, int $ceil): int
    {
        if ($this->mersenneTwister === null) {
            throw new MissingSeedException();
        }
        //Boundaries are inclusive
        return $this->mersenneTwister->rangeint($floor,$ceil);
    }


    public static function getAdapterSlug(): string
    {
        return 'mersenne-twister';
    }

    public function getSlug(): string
    {
        return 'mersenne-twister';
    }

    public function validateSeed(int $seed): bool
    {
        //Mersenne twister accepts any seed that is int
        return true;
    }

    protected function setProviderSeed(int $seed): RandomNumberGeneratorAdapter
    {
        $this->mersenneTwister=new MersenneTwister($seed);
        return $this;
    }


}