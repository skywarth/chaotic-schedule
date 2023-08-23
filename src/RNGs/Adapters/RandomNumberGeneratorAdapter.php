<?php

namespace Skywarth\ChaoticSchedule\RNGs\Adapters;

use Skywarth\ChaoticSchedule\Exceptions\InvalidSeedFormatException;

interface RandomNumberGeneratorAdapter
{
    public function __construct(int $seed=null);//TODO: maybe make seed into string ?

    public function setSeed(int $seed):RandomNumberGeneratorAdapter;
    public function getSeed():int;
    public function intBetween(int $floor, int $ceil):int;//TODO: determine inclusive/exclusive boundaries!

    public static function getAdapterSlug():string;
    public function getSlug():string;


    /**
     * @return bool
     * @throws InvalidSeedFormatException
     */
    public function validateSeed(int $seed):bool;

}