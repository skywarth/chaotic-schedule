<?php

namespace Skywarth\ChaoticSchedule\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Skywarth\ChaoticSchedule\Providers\SeedGenerationServiceProvider;
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\TestCase;

class ServiceProviderTest extends TestCase
{

    public function test_seed_generation_service_registration()
    {
        $seedGenerationService=app(SeedGenerationService::class);
        $bound=$this->app->bound(SeedGenerationService::class);
        $this->assertTrue($bound);
        $this->assertSame(SeedGenerationService::class,get_class($seedGenerationService));

    }

    public function test_rng_factory_service_registration()
    {
        $rngFactory=app(RNGFactory::class);
        $bound=$this->app->bound(RNGFactory::class);
        $this->assertTrue($bound);
        $this->assertSame(RNGFactory::class,get_class($rngFactory));

    }

    public function test_rng_factory_depends_on_config()
    {
        Config::set('chaotic-schedule.rng_engine.active_engine_slug','mersenne-twister');
        $rngFactoryMersenne=app(RNGFactory::class);
        Config::set('chaotic-schedule.rng_engine.active_engine_slug','seed-spring');
        $rngFactorySeedSpring=app(RNGFactory::class);
        $this->assertNotEquals($rngFactoryMersenne->getRngEngineSlug(),$rngFactorySeedSpring->getRngEngineSlug());
        //Maybe reset config afterwards ?
    }

    public function test_rng_factory_override_slug_by_parameter()
    {
        Config::set('chaotic-schedule.rng_engine.active_engine_slug','mersenne-twister');
        $firstRngFactory=app(RNGFactory::class);
        $secondRngFactory=app(RNGFactory::class,['slug'=>'seed-spring']);
        $this->assertNotEquals($firstRngFactory->getRngEngineSlug(),$secondRngFactory->getRngEngineSlug());
    }





}