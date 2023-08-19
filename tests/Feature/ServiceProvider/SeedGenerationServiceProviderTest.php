<?php

namespace Skywarth\ChaoticSchedule\Tests\Feature\ServiceProvider;

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

class SeedGenerationServiceProviderTest extends TestCase
{

    public function test_seed_generation_service_binding()
    {
        $seedGenerationService=app(SeedGenerationService::class);
        $bound=$this->app->bound(SeedGenerationService::class);
        $this->assertTrue($bound);
        $this->assertSame(SeedGenerationService::class,get_class($seedGenerationService));

    }




}