<?php

namespace Skywarth\ChaoticSchedule\Tests\Feature\ChaoticSchedule\RandomTimeMacros;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Skywarth\ChaoticSchedule\Exceptions\IncompatibleClosureResponse;
use Skywarth\ChaoticSchedule\Exceptions\IncorrectRangeException;
use Skywarth\ChaoticSchedule\RNGs\Adapters\MersenneTwisterAdapter;
use Skywarth\ChaoticSchedule\RNGs\Adapters\SeedSpringAdapter;
use Skywarth\ChaoticSchedule\Tests\Feature\ChaoticSchedule\AbstractChaoticScheduleTest;

class RandomMinuteScheduleTest extends AbstractChaoticScheduleTest
{


    public function testRandomMinuteIncorrectParameterFormatException()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(\OutOfRangeException::class);
        $this->makeChaoticSchedule()->randomMinuteSchedule($schedule,-55,44);
    }



    public function testRandomMinuteIncorrectParameterOrderException()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(IncorrectRangeException::class);
        $this->makeChaoticSchedule()->randomMinuteSchedule($schedule,44,13);
    }

    public function testRandomMinuteClosureThrowsIncompatibleResponse()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(IncompatibleClosureResponse::class);
        $this->makeChaoticSchedule()->randomMinuteSchedule($schedule,2,11,null,function (int $minute){
            return [];
        });
    }

    public function testRandomMinuteSameCommandNoIdentifierConsistency()
    {
        // Mirror production: ChaoticSchedule is bound as a singleton in
        // ChaoticScheduleServiceProvider, so one instance handles many macro
        // calls per schedule:run. Carbon::setTestNow stabilises both the
        // basisDate captured at instance construction and nextRunDate()'s
        // "next cron match" search.
        Carbon::setTestNow(Carbon::create(2024, 4, 13, 9, 42, 0));
        $chaoticSchedule = $this->makeChaoticSchedule();

        $schedule1 = new Schedule();
        $comm1=$schedule1->command('test')->tuesdays();
        $schedule2 = new Schedule();
        $comm2=$schedule2->command('test')->tuesdays();
        $comm1NextRun=$chaoticSchedule->randomMinuteSchedule($comm1,10,55)->nextRunDate();
        $comm2NextRun=$chaoticSchedule->randomMinuteSchedule($comm2,10,55)->nextRunDate();
        $datesEqual=$comm1NextRun->eq($comm2NextRun);
        $this->assertEquals(true,$datesEqual);
    }

    public function testRandomMinuteSameCommandCustomIdentifierDifference()
    {
        // Pin the basis so this is a deterministic claim, not a 1/59 coin flip:
        // randomMinuteSchedule picks one int in [1,59], so two distinct seeds
        // collide ~1.7% of basis-hours. Verified that auto-id 'test' and
        // 'get-customized-lmao' produce different output at this moment.
        // Use one ChaoticSchedule across both macro calls to mirror the
        // production singleton binding.
        Carbon::setTestNow(Carbon::create(2024, 4, 13, 9, 42, 0));
        $chaoticSchedule = $this->makeChaoticSchedule();

        $schedule1 = new Schedule();
        $comm1=$schedule1->command('test')->weekdays();
        $schedule2 = new Schedule();
        $comm2=$schedule2->command('test')->weekdays();
        $comm1NextRun=$chaoticSchedule->randomMinuteSchedule($comm1,1,59)->nextRunDate();
        $comm2NextRun=$chaoticSchedule->randomMinuteSchedule($comm2,1,59,'get-customized-lmao')->nextRunDate();
        $datesEqual=$comm1NextRun->notEqualTo($comm2NextRun);
        $this->assertEquals(true,$datesEqual);
    }

    public function testRandomMinuteWithinLimits()
    {

        $min=23;
        $max=42;

        $schedules=$this->generateRandomMinuteConsecutiveHours(
            100,
            self::DEFAULT_RNG_ENGINE_SLUG,
            $min,
            $max,
            Carbon::createFromDate(2023,9,10)
        );
        $designatedRuns=$schedules->map(function (Event $schedule){
            return $schedule->nextRunDate();
        });


        $designatedRuns=$designatedRuns->filter(function (Carbon $carbon) use($min,$max){
            $minute=$carbon->minute;
            return !($minute>=$min && $minute<=$max);
        });

        $this->assertEquals(0,$designatedRuns->count());
    }

    public function testRandomMinuteConsistencyThroughoutTheHour()
    {
        $schedules=$this->generateRandomMinuteConsecutiveMinutes(60,1,59,Carbon::createFromDate(2023,7,12)->startOfDay(),MersenneTwisterAdapter::getAdapterSlug());
        $designatedRuns=$schedules->map(function (Event $schedule){
            return $schedule->nextRunDate()->minute;
        });

        $uniqueRunMinutes=$designatedRuns->unique();

        $this->assertEquals(1,$uniqueRunMinutes->count());


    }

    public function testRandomMinuteInConsistencyThroughoutTheDay()
    {
        $schedules=$this->generateRandomMinuteConsecutiveMinutes(1440,1,59,Carbon::createFromDate(2012,4,13)->startOfDay(),SeedSpringAdapter::getAdapterSlug());
        $designatedRuns=$schedules->map(function (Event $schedule){
            return $schedule->nextRunDate()->minute;
        });

        $uniqueRunMinutes=$designatedRuns->unique();

        $this->assertNotEquals(1,$uniqueRunMinutes->count());


    }


}
