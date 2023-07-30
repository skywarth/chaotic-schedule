<?php

namespace Skywarth\ChaoticSchedule\RNGs\Adapters;

use RandomLib\Factory;
use RandomLib\Generator;

class RandomLibAdapter implements RandomNumberGeneratorAdapter
{

    private \RandomLib\Factory $factory;
    private Generator $generator;

    public function __construct(int $seed)
    {
        $this->factory=new \RandomLib\Factory();
        $this->generator=$this->factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::MEDIUM));
    }

    public function setSeed(int $seed)
    {
        // TODO: Implement setSeed() method.
    }

    public function intBetween(int $floor, int $ceil): int
    {
        $this->generator->generateInt($floor,$ceil);
    }
}