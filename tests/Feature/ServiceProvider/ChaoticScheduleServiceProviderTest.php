<?php

namespace Skywarth\ChaoticSchedule\Tests\Feature\ServiceProvider;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Config;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;
use Skywarth\ChaoticSchedule\Services\ChaoticSchedule;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\TestCase;

class ChaoticScheduleServiceProviderTest extends TestCase
{
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