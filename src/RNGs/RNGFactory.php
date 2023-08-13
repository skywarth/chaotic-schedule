<?php

namespace Skywarth\ChaoticSchedule\RNGs;

use Skywarth\ChaoticSchedule\RNGs\Adapters\MersenneTwisterAdapter;
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\Adapters\SeedSpringAdapter;

class RNGFactory
{

    private string $rngEngineSlug;

    /**
     * @param string $rngEngineSlug
     */
    public function __construct(string $rngEngineSlug)
    {
        $this->rngEngineSlug = $rngEngineSlug;
        return $this;
    }

    /**
     * @return string
     */
    public function getRngEngineSlug(): string
    {
        return $this->rngEngineSlug;
    }


    public function getRngEngine(int $seed=null):RandomNumberGeneratorAdapter{
        //TODO: array containing ::class for comparison, you don't really need if-else
        if($this->getRngEngineSlug()===MersenneTwisterAdapter::getAdapterSlug()){
            return new MersenneTwisterAdapter($seed);
        }else if($this->getRngEngineSlug()===SeedSpringAdapter::getAdapterSlug()){
            return new SeedSpringAdapter($seed);
        }else{
            //TODO: throw PROPER exception
            throw new \Exception('Uhh what');
        }

    }

}