<?php

namespace Skywarth\ChaoticSchedule\RNGs\Adapters;

abstract class AbstractRNGAdapter implements RandomNumberGeneratorAdapter
{

    protected int $seed;

    public function __construct(int $seed=null)
    {
        //TODO: getSeed function on abstract (this class), throw exception in there perhaps.
        if(!is_null($seed)){
            $this->setSeed($seed);
        }

    }

    /**
     * @return int
     */
    public function getSeed(): int
    {
        return $this->seed;
    }


}