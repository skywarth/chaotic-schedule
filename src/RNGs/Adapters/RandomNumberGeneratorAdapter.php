<?php

namespace Skywarth\ChaoticSchedule\RNGs\Adapters;

interface RandomNumberGeneratorAdapter
{
    public function __construct(int $seed=null);//TODO: maybe make seed into string ?

    public function setSeed(int $seed):RandomNumberGeneratorAdapter;
    public function intBetween(int $floor, int $ceil):int;

    public static function getSlug():string;

}