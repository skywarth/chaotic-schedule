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

    public function testInitializationWithSeed()
    {
        $initialSeed=1550;
        $adapter=new MersenneTwisterAdapter($initialSeed);
        $this->assertInstanceOf(RandomNumberGeneratorAdapter::class,$adapter);
        $this->assertEquals($initialSeed,$adapter->getSeed());

    }

    public function testThrowsExceptionWhenSeedIsMissing()
    {
        $adapter=new MersenneTwisterAdapter();
        $this->expectException(MissingSeedException::class);
        $adapter->getSeed();

    }

    public function testSeedValidationException()
    {
        $adapter=new SeedSpringAdapter();
        $this->expectException(InvalidSeedFormatException::class);
        $adapter->setSeed(123);
    }

    public function testIntBetweenBoundaryParametersAreInclusive()
    {
        $adapter=new SeedSpringAdapter(3434834333333984);
        $rnd1=$adapter->intBetween(5,5);
        $this->assertEquals(5,$rnd1);
        $rnd2=$adapter->intBetween(4096,4096);
        $this->assertEquals(4096,$rnd2);
        $rnd3=$adapter->intBetween(32,33);
        $this->assertTrue(in_array($rnd3,[32,33]));
        $rnd3=$adapter->intBetween(101,103);
        $this->assertTrue(in_array($rnd3,[101,102,103]));


    }





}