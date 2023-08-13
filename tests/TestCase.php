<?php

namespace Skywarth\ChaoticSchedule\Tests;



use Skywarth\ChaoticSchedule\Providers\ChaoticScheduleServiceProvider;
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
            SeedGenerationServiceProvider::class,
            ChaoticScheduleServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
    }




}