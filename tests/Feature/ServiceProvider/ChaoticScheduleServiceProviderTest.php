<?php

namespace Skywarth\ChaoticSchedule\Tests\Feature\ServiceProvider;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Config;
use Skywarth\ChaoticSchedule\Enums\RandomDateScheduleBasis;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;
use Skywarth\ChaoticSchedule\Services\ChaoticSchedule;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\TestCase;

class ChaoticScheduleServiceProviderTest extends TestCase
{
    public function testChaoticScheduleServiceBinding()
    {
        $chaoticSchedule=app(ChaoticSchedule::class);
        $bound=$this->app->bound(ChaoticSchedule::class);
        $this->assertTrue($bound);
        $this->assertSame(ChaoticSchedule::class,get_class($chaoticSchedule));

    }

    public function testChaoticScheduleTimeBasedMacroRegistration()
    {
        $this->assertTrue(Event::hasMacro('atRandom'));
        $this->assertTrue(Event::hasMacro('dailyAtRandom'));
        $this->assertTrue(Event::hasMacro('hourlyAtRandom'));
        $this->assertTrue(Event::hasMacro('randomDays'));

        $schedule = new Schedule();
        $event=$schedule->command('foo');

        $event->atRandom('13:00','15:00');
        $event->dailyAtRandom('10:00','12:00');
        $event->hourlyAtRandom(15,38);
        $event->randomDays(RandomDateScheduleBasis::WEEK,null,1,3);


    }





}