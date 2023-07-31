<?php

namespace Skywarth\ChaoticSchedule\RNGs;

use Skywarth\ChaoticSchedule\RNGs\Adapters\MersenneTwisterAdapter;
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\Adapters\SeedSpringAdapter;

class RNGFactory
{
    public static function getRngEngine(int $seed=null):RandomNumberGeneratorAdapter{
        $rngEngineSlug=config('chaotic-schedule.rng_engine.active_engine_slug');
        //TODO: array containing ::class for comparison, you don't really need if-else
        if($rngEngineSlug===MersenneTwisterAdapter::getSlug()){
            return new MersenneTwisterAdapter($seed);
        }else if($rngEngineSlug===SeedSpringAdapter::getSlug()){
            return new SeedSpringAdapter($seed);
        }else{
            //TODO: throw PROPER exception
            throw new \Exception('Uhh what');
        }

    }

}