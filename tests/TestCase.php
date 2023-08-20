<?php

namespace Skywarth\ChaoticSchedule\Tests;



use Illuminate\Support\Facades\Config;
use Skywarth\ChaoticSchedule\Providers\ChaoticScheduleServiceProvider;
use Skywarth\ChaoticSchedule\Providers\RNGFactoryServiceProvider;
use Skywarth\ChaoticSchedule\Providers\SeedGenerationServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase{

    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
            RNGFactoryServiceProvider::class,
            SeedGenerationServiceProvider::class,
            ChaoticScheduleServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
    }
    
    
    protected function setConfigActiveSlug(string $slug){
        Config::set('chaotic-schedule.rng_engine.active_engine_slug',$slug);
    }




}