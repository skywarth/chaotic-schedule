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
        $this->getChaoticSchedule()->randomMinuteSchedule($schedule,-55,44);
    }



    public function testRandomMinuteIncorrectParameterOrderException()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(IncorrectRangeException::class);
        $this->getChaoticSchedule()->randomMinuteSchedule($schedule,44,13);
    }

    public function testRandomMinuteClosureThrowsIncompatibleResponse()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(IncompatibleClosureResponse::class);
        $this->getChaoticSchedule()->randomMinuteSchedule($schedule,2,11,null,function (int $minute){
            return [];
        });
    }

    public function testRandomMinuteSameCommandNoIdentifierConsistency()
    {
        $schedule1 = new Schedule();
        $comm1=$schedule1->command('test')->tuesdays();
        $schedule2 = new Schedule();
        $comm2=$schedule2->command('test')->tuesdays();
        $comm1NextRun=$this->getChaoticSchedule()->randomMinuteSchedule($comm1,10,55)->nextRunDate();
        $comm2NextRun=$this->getChaoticSchedule()->randomMinuteSchedule($comm2,10,55)->nextRunDate();
        $datesEqual=$comm1NextRun->eq($comm2NextRun);
        $this->assertEquals(true,$datesEqual);
    }

    public function testRandomMinuteSameCommandCustomIdentifierDifference()
    {
        $schedule1 = new Schedule();
        $comm1=$schedule1->command('test')->weekdays();
        $schedule2 = new Schedule();
        $comm2=$schedule2->command('test')->weekdays();
        $comm1NextRun=$this->getChaoticSchedule()->randomMinuteSchedule($comm1,1,59)->nextRunDate();
        $comm2NextRun=$this->getChaoticSchedule()->randomMinuteSchedule($comm2,1,59,'get-customized-lmao')->nextRunDate();
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
        $schedules=$this->generateRandomMinuteConsecutiveMinutes(60,1,59,Carbon::createFromDate(2023,7,12),MersenneTwisterAdapter::getAdapterSlug());
        $designatedRuns=$schedules->map(function (Event $schedule){
            return $schedule->nextRunDate()->minute;
        });

        $uniqueRunMinutes=$designatedRuns->unique();

        $this->assertEquals(1,$uniqueRunMinutes->count());


    }

    public function testRandomMinuteInConsistencyThroughoutTheDay()
    {
        $schedules=$this->generateRandomMinuteConsecutiveMinutes(1440,1,59,Carbon::createFromDate(2012,4,13),SeedSpringAdapter::getAdapterSlug());
        $designatedRuns=$schedules->map(function (Event $schedule){
            return $schedule->nextRunDate()->minute;
        });

        $uniqueRunMinutes=$designatedRuns->unique();

        $this->assertNotEquals(1,$uniqueRunMinutes->count());


    }


}
