<?php

namespace Skywarth\ChaoticSchedule\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Config;
use Skywarth\ChaoticSchedule\Providers\SeedGenerationServiceProvider;
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;
use Skywarth\ChaoticSchedule\Services\ChaoticSchedule;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    //TODO: split these tests into separate files

    public function test_seed_generation_service_binding()
    {
        $seedGenerationService=app(SeedGenerationService::class);
        $bound=$this->app->bound(SeedGenerationService::class);
        $this->assertTrue($bound);
        $this->assertSame(SeedGenerationService::class,get_class($seedGenerationService));

    }

    public function test_rng_factory_service_binding()
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

    public function test_chaotic_schedule_service_binding()
    {
        $chaoticSchedule=app(ChaoticSchedule::class);
        $bound=$this->app->bound(ChaoticSchedule::class);
        $this->assertTrue($bound);
        $this->assertSame(ChaoticSchedule::class,get_class($chaoticSchedule));

    }

    public function test_chaotic_schedule_time_based_macro_registration()
    {
        $this->assertTrue(Event::hasMacro('atRandom'));
        $this->assertTrue(Event::hasMacro('dailyAtRandom'));
        $this->assertTrue(Event::hasMacro('hourlyAtRandom'));

        $schedule = new Schedule();
        $event=$schedule->command('qwe');

        $event->atRandom('13:00','15:00');
        $event->dailyAtRandom('10:00','12:00');
        $event->hourlyAtRandom(15,38);


    }





}