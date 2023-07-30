<?php

namespace Skywarth\ChaoticSchedule\RNGs\Adapters;

interface RandomNumberGeneratorAdapter
{
    public function __construct(int $seed);

    public function setSeed(int $seed);
    public function intBetween(int $floor, int $ceil):int;

}