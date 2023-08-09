<?php

namespace Skywarth\ChaoticSchedule\Tests\Unit;

use Carbon\Carbon;
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\TestCase;

class RNGFactoryTest extends TestCase
{

    public function test_provides_valid_adapter()
    {
        $factory=new RNGFactory('mersenne-twister');
        $adapter=$factory->getRngEngine();
        $this->assertInstanceOf(RandomNumberGeneratorAdapter::class,$adapter);

    }

    public function test_provides_different_()
    {
        $factory=new RNGFactory('mersenne-twister');
        $adapter=$factory->getRngEngine();
        $this->assertInstanceOf(RandomNumberGeneratorAdapter::class,$adapter);

    }





}