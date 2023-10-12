<?php

namespace Skywarth\ChaoticSchedule\Tests\Feature\ServiceProvider;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Config;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;
use Skywarth\ChaoticSchedule\Services\ChaoticSchedule;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\TestCase;

class RNGFactoryServiceProviderTest extends TestCase
{
    public function testRngFactoryServiceBinding()
    {
        $rngFactory=app(RNGFactory::class);
        $bound=$this->app->bound(RNGFactory::class);
        $this->assertTrue($bound);
        $this->assertSame(RNGFactory::class,get_class($rngFactory));

    }

    public function testRngFactoryDependsOnConfig()
    {
        $this->setConfigActiveSlug('mersenne-twister');
        $rngFactoryMersenne=app(RNGFactory::class);
        $this->setConfigActiveSlug('seed-spring');
        $rngFactorySeedSpring=app(RNGFactory::class);
        $this->assertNotEquals($rngFactoryMersenne->getRngEngineSlug(),$rngFactorySeedSpring->getRngEngineSlug());
        //Maybe reset config afterwards ?
    }

    public function testRngFactoryOverrideSlugByParameter()
    {
        $this->setConfigActiveSlug('mersenne-twister');
        $firstRngFactory=app(RNGFactory::class);
        $secondRngFactory=app(RNGFactory::class,['slug'=>'seed-spring']);
        $this->assertNotEquals($firstRngFactory->getRngEngineSlug(),$secondRngFactory->getRngEngineSlug());
    }




}