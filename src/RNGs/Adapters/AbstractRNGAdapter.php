<?php

namespace Skywarth\ChaoticSchedule\RNGs\Adapters;

use Skywarth\ChaoticSchedule\Exceptions\InvalidSeedFormatException;
use Skywarth\ChaoticSchedule\Exceptions\MissingSeedException;

abstract class AbstractRNGAdapter implements RandomNumberGeneratorAdapter
{

    protected int $seed;

    public function __construct(int $seed=null)
    {
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



    public final function setSeed(int $seed):RandomNumberGeneratorAdapter
    {
        if(!$this->validateSeed($seed)){//Maybe another method for padding the missing bytes/length ?
            throw new InvalidSeedFormatException('Seed format is invalid.');
        }

        return $this->setProviderSeed($seed);
    }

    abstract protected function setProviderSeed(int $seed):RandomNumberGeneratorAdapter;


}