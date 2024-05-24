<?php

namespace Skywarth\ChaoticSchedule\Tests\Feature\ChaoticSchedule\RandomTimeMacros;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use LogicException;
use OutOfRangeException;
use Skywarth\ChaoticSchedule\Exceptions\IncompatibleClosureResponse;
use Skywarth\ChaoticSchedule\Exceptions\IncorrectRangeException;
use Skywarth\ChaoticSchedule\Exceptions\RunTimesExpectationCannotBeMet;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;
use Skywarth\ChaoticSchedule\Services\ChaoticSchedule;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\Feature\ChaoticSchedule\AbstractChaoticScheduleTest;

class RandomMultipleMinuteScheduleTest extends AbstractChaoticScheduleTest
{



    protected function randomMultipleMinuteTestingBoilerplate(Carbon $nowMock,string $rngEngineSlug,int $minutesMin, int $minutesMax, int $timesMin, int $timesMax,callable $closure=null):Collection{
        $runMinutes=collect();

        for($i=0;$i<=59;$i++){
            $schedule = new Schedule();
            $command=$schedule->command('test');
            $chaoticSchedule=new ChaoticSchedule(
                new SeedGenerationService($nowMock),
                new RNGFactory($rngEngineSlug)
            );

            Carbon::setTestNow($nowMock); //Mock carbon now for Laravel event
            $schedule=$chaoticSchedule->randomMultipleMinutesSchedule($command,$minutesMin,$minutesMax,$timesMin,$timesMax,null,$closure);

            if($schedule->isDue(app())){
                $runMinutes->push($nowMock->minute);
            }
            $nowMock->addminute();
            Carbon::setTestNow();
        }
        return $runMinutes->unique();
    }




    public function testRandomMultipleMinuteVariableTimesBetweenLimits()
    {
        $nowMock=Carbon::createFromDate(2022,07,16)->setTime(15,0);
        $rngEngineSlug='mersenne-twister';
        $minutesMin=17;
        $minutesMax=56;
        $timesMin=4;
        $timesMax=8;

        $runMinutes=$this->randomMultipleMinuteTestingBoilerplate($nowMock,$rngEngineSlug,$minutesMin,$minutesMax,$timesMin,$timesMax);

        $this->assertLessThanOrEqual($timesMax,$runMinutes->count());
        $this->assertGreaterThanOrEqual($timesMin,$runMinutes->count());


    }

    public function testRandomMultipleMinuteExactTimes()
    {
        $nowMock=Carbon::createFromDate(2048,9,12)->setTime(07,0);
        $rngEngineSlug='mersenne-twister';
        $minutesMin=8;
        $minutesMax=39;
        $times=7;

        $runMinutes=$this->randomMultipleMinuteTestingBoilerplate($nowMock,$rngEngineSlug,$minutesMin,$minutesMax,$times,$times);

        $this->assertEquals($times,$runMinutes->count());

    }

    public function testRandomMultipleMinuteRangeBoundary()
    {
        $nowMock=Carbon::createFromDate(2011,6,15)->setTime(23,0);
        $rngEngineSlug='mersenne-twister';
        $minutesMin=7;
        $minutesMax=32;
        $timesMin=10;
        $timesMax=15;

        $runMinutes=$this->randomMultipleMinuteTestingBoilerplate($nowMock,$rngEngineSlug,$minutesMin,$minutesMax,$timesMin,$timesMax);

        $runMinutesOutOfBoundary=$runMinutes->filter(fn(int $minute)=>$minute>$minutesMax&&$minute<$minutesMin);
        $this->assertEquals(0,$runMinutesOutOfBoundary->count());

    }

    public function testRandomMultipleMinuteClosureExceptLostNumbers()
    {
        $nowMock=Carbon::createFromDate(2024,5,5)->setTime(23,15);
        $rngEngineSlug='seed-spring';
        $minutesMin=2;
        $minutesMax=50;
        $timesMin=10;
        $timesMax=15;

        $lostNumbers=[4,8,15,16,23,42];
        $closure=function (Collection $designatedRunMinutes,Event $event) use ($lostNumbers){

            return $designatedRunMinutes->diff($lostNumbers)->values();
        };

        $runMinutes=$this->randomMultipleMinuteTestingBoilerplate($nowMock,$rngEngineSlug,$minutesMin,$minutesMax,$timesMin,$timesMax,$closure);

        $runMinutesOfLostNumbers=$runMinutes->filter(fn(int $minute)=>in_array($minute,$lostNumbers));

        $this->assertEquals(0,$runMinutesOfLostNumbers->count());

    }

    public function testRandomMultipleMinuteClosureOnlyMultipleOfNumber()
    {
        $nowMock=Carbon::createFromDate(2022,11,16)->setTime(12,42);
        $rngEngineSlug='seed-spring';
        $minutesMin=5;
        $minutesMax=57;
        $timesMin=2;
        $timesMax=2;

        $multipleOf=3;


        $closure=function (Collection $designatedRunMinutes,Event $event) use ($multipleOf){
            return $designatedRunMinutes->map(function (int $minute) use ($multipleOf){

                if($minute%$multipleOf!==0){
                    return round($minute,$multipleOf)*$multipleOf;
                }else{
                    return $minute;
                }
            })->values();
        };

        $runMinutes=$this->randomMultipleMinuteTestingBoilerplate($nowMock,$rngEngineSlug,$minutesMin,$minutesMax,$timesMin,$timesMax,$closure);

        $runMinutesNonMultiplyOfNumber=$runMinutes->filter(fn(int $minute)=>$minute%$multipleOf!==0)->values();

        $this->assertEquals(0,$runMinutesNonMultiplyOfNumber->count());


    }



    public function testRandomMultipleMinutesUseCaseFromRedditN1Variant2()
    {
        //https://www.reddit.com/r/laravel/comments/18v714l/comment/ktkyc72/?utm_source=share&utm_medium=web2x&context=3
        //Possible variant #2, since use case is a bit vague:
        //"Do you think I can simply set up a command that runs on weekdays (Monday till Friday) between 8:00 and 18:00 about 4 to 5 times randomly/"humanly" per hour?"
        // Run 4-5 times per hour, only on weekdays (constant, every day), between 08:00 and 18:00 (constant, every hour). Minutes of each hour are random
        $nowMock=Carbon::createFromDate(2024,04,21)->startOfDay();
        $daysOfWeek=[Carbon::MONDAY,Carbon::TUESDAY,Carbon::WEDNESDAY,Carbon::THURSDAY,Carbon::FRIDAY];
        $rngEngineSlug='mersenne-twister';

        //Can't assert ->between(). See: https://github.com/laravel/framework/issues/50670
        //So I'm setting the time range to whole
        $minTime='00:00';
        $maxTime='24:00';

        $minutesMin=5;
        $minutesMax=45;
        $timesMin=4;
        $timesMax=5;
        $runDateTimes=collect();

        $periodBegin=$nowMock->clone();
        $periodEnd=$nowMock->clone()->next('sunday');

        $period=CarbonPeriod::create($periodBegin, $periodEnd);
        $minuteIteration=1;
        //dd(Carbon::createFromDate(2024,04,21)->startOfDay()->dayOfWeek);
        foreach ($period as $index=>$date){
            //Each day loop
            $date=$date->startOfDay();
            for($i=0;$i<24;$i++){
                //Each hour loop

                for($k=0;$k<(60/$minuteIteration);$k++){
                    //Each minute iteration loop
                    $date=$date->addMinutes($minuteIteration);

                    $schedule = new Schedule();
                    $schedule=$schedule->command('test')->weekdays();
                    $chaoticSchedule=new ChaoticSchedule(
                        new SeedGenerationService($date),
                        new RNGFactory($rngEngineSlug)
                    );
                    $schedule=$chaoticSchedule->randomMultipleMinutesSchedule($schedule,$minutesMin,$minutesMax,$timesMin,$timesMax);

                    Carbon::setTestNow($date); //Mock carbon now for Laravel event
                    $this->travelTo($date);//redundant
                    if($schedule->isDue(app())){
                        $runDateTimes->push($date->clone());
                    }
                    Carbon::setTestNow();//resetting the carbon::now to original
                }
            }


        }


        $this->assertTrue($runDateTimes->doesntContain(function (Carbon $runDateTime) use($daysOfWeek){
            return !in_array($runDateTime->dayOfWeek,$daysOfWeek);
        }));

        $this->assertTrue($runDateTimes->doesntContain(function (Carbon $runDateTime) use($periodBegin,$periodEnd){
            return !$runDateTime->isBetween($periodBegin,$periodEnd);
        }),'Run datetimes outside of range has been detected');


        $this->assertTrue($runDateTimes->groupBy(function (Carbon $runDateTime){
            return $runDateTime->format('z-H');//Grouping per day-of-year and hour of the day
        })->doesntContain(function(Collection $collection) use ($timesMin,$timesMax){
            $count=$collection->count();//Asserting that each hour contains run times between timesMin and timesMax
            return $count>$timesMax || $count<$timesMin;
        }),'Hourly run amounts are out of designated range.');

        $this->assertTrue($runDateTimes->doesntContain(function (Carbon $runDateTime) use($minTime,$maxTime){
            return !$runDateTime->isBetween($runDateTime->clone()->setTimeFromTimeString($minTime),$runDateTime->clone()->setTimeFromTimeString($maxTime));
        }));



    }


    public function testRandomMultipleMinuteMinMinutesBiggerThanMaxMinutesException()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(IncorrectRangeException::class);
        $this->getChaoticSchedule()->randomMultipleMinutesSchedule($schedule,16,9);
    }

    public function testRandomMultipleMinuteMinuteOutOfRangeException()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(OutOfRangeException::class);
        $this->getChaoticSchedule()->randomMultipleMinutesSchedule($schedule,14,62);
    }

    public function testRandomMultipleMinuteTimesAmountOutOfRangeException()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(LogicException::class);
        $this->getChaoticSchedule()->randomMultipleMinutesSchedule($schedule,10,55,-3,4);
    }

    public function testRandomMultipleMinuteTimesMinimumBiggerThanMaximumException()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(IncorrectRangeException::class);
        $this->getChaoticSchedule()->randomMultipleMinutesSchedule($schedule,5,30,4,2);
    }

    public function testRandomMultipleMinuteRunTimesMaxExceedsPossibleRunsException()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(RunTimesExpectationCannotBeMet::class);
        $this->getChaoticSchedule()->randomMultipleMinutesSchedule($schedule,5,10,4,10);
    }

    public function testRandomMultipleMinuteIncompatibleClosureResponseException()
    {
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->expectException(IncompatibleClosureResponse::class);
        $closure=function (Collection $designatedRunTimes,Event $schedule){
            return 55;
        };
        $this->getChaoticSchedule()->randomMultipleMinutesSchedule($schedule,10,45,2,5,null,$closure);
    }


}
