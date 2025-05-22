<?php

namespace Skywarth\ChaoticSchedule\RNGs\Adapters;

use Skywarth\ChaoticSchedule\Exceptions\InvalidSeedFormatException;

interface RandomNumberGeneratorAdapter
{
    public function __construct(?int $seed=null);//TODO: maybe make seed into string ?

    public function setSeed(int $seed):RandomNumberGeneratorAdapter;
    public function getSeed():int;


    //Boundaries are inclusive
    //E.g: [1,3]-> {1,2,3}
    //Make sure to follow this fashion accordingly per adapter.
    /**
     * @param int $floor Inclusive, floor/minimum value for the random value
     * @param int $ceil Inclusive, ceil/maximum value for the random value
     * @return int
     */
    public function intBetween(int $floor, int $ceil):int;

    public static function getAdapterSlug():string;
    public function getSlug():string;


    /**
     * @return bool
     * @throws InvalidSeedFormatException
     */
    public function validateSeed(int $seed):bool;

}