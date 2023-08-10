<?php

namespace Skywarth\ChaoticSchedule\RNGs\Adapters;

use Skywarth\ChaoticSchedule\Exceptions\MissingSeedException;

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
     * @throws MissingSeedException
     */
    public function getSeed(): int
    {
        if(empty($this->seed)){
            throw new MissingSeedException();
        }
        return $this->seed;
    }

    abstract static function validateSeed(int $seed): bool;

    public function setSeed(int $seed):RandomNumberGeneratorAdapter
    {//TODO: move this to abstract and encapsulate the inner logic to an another abstract method. This setSeed however will be final.
        self::validateSeed($seed);
        return $this;
    }


}