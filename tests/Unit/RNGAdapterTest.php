<?php

namespace Skywarth\ChaoticSchedule\Tests\Unit;

use Carbon\Carbon;
use Skywarth\ChaoticSchedule\Exceptions\InvalidSeedFormatException;
use Skywarth\ChaoticSchedule\Exceptions\MissingSeedException;
use Skywarth\ChaoticSchedule\RNGs\Adapters\MersenneTwisterAdapter;
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\Adapters\SeedSpringAdapter;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\TestCase;

class RNGAdapterTest extends TestCase
{

    public function test_initialization_with_seed()
    {
        $initialSeed=1550;
        $adapter=new MersenneTwisterAdapter($initialSeed);
        $this->assertInstanceOf(RandomNumberGeneratorAdapter::class,$adapter);
        $this->assertEquals($initialSeed,$adapter->getSeed());

    }

    public function test_throws_exception_when_seed_is_missing()
    {
        $adapter=new MersenneTwisterAdapter();
        $this->expectException(MissingSeedException::class);
        $adapter->getSeed();

    }

    public function test_seed_validation_exception()
    {
        $adapter=new SeedSpringAdapter();
        $this->expectException(InvalidSeedFormatException::class);
        $adapter->setSeed(123);
    }





}