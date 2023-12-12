<?php

namespace Feature\ChaoticSchedule;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Skywarth\ChaoticSchedule\Enums\RandomDateScheduleBasis;
use Skywarth\ChaoticSchedule\Exceptions\IncompatibleClosureResponse;
use Skywarth\ChaoticSchedule\Exceptions\IncorrectRangeException;
use Skywarth\ChaoticSchedule\Exceptions\InvalidDateFormatException;
use Skywarth\ChaoticSchedule\RNGs\Adapters\MersenneTwisterAdapter;
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\Adapters\SeedSpringAdapter;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;
use Skywarth\ChaoticSchedule\Services\ChaoticSchedule;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\Feature\ChaoticSchedule\AbstractChaoticScheduleTest;
use Skywarth\ChaoticSchedule\Tests\TestCase;

class UnifiedMacroTest extends AbstractChaoticScheduleTest
{

    protected function randomDateTimeScheduleTestingBoilerplate(Carbon $nowMock, string $rngEngineSlug , string $periodType, array $daysOfWeek ,string $minTime,string $maxTime, int $runAmountMin, int $runAmountMax,   callable $scheduleMacroInjection ):Collection{

        //WIP

        //$daysOfWeek=empty($daysOfWeek)?ChaoticSchedule::ALL_DOW:$daysOfWeek;

        $periodBegin=$nowMock->clone()->startOf(RandomDateScheduleBasis::getString($periodType));
        $periodEnd=$nowMock->clone()->endOf(RandomDateScheduleBasis::getString($periodType));

        $period=CarbonPeriod::create($periodBegin, $periodEnd);


        $minuteIteration=1;
        $runDateTimes=collect();
        foreach ($period as $index=>$date){
            $date=$date->startOfDay();
            for($i=0;$i<=((24*60)-1);$i+=$minuteIteration){
                $date=$date->addMinutes($minuteIteration);

                //dump($date->format('d-m-Y H:i'));
                $schedule = new Schedule();
                $schedule=$schedule->command('test');
                $chaoticSchedule=new ChaoticSchedule(
                    new SeedGenerationService($date),
                    new RNGFactory($rngEngineSlug)
                );

                $schedule=$scheduleMacroInjection($chaoticSchedule,$schedule);

                Carbon::setTestNow($date); //Mock carbon now for Laravel event
                if($schedule->isDue(app())){


                    $runDateTimes->push($date->format('d-m-Y H:i'));

                    //maybe make these below as assertion closure
                    $this->assertTrue($date->isBetween($periodBegin,$periodEnd), "$date->day-$date->month-$date->year is not in between the designated period");
                    $this->assertTrue($date->isBetween($date->clone()->setTimeFromTimeString($minTime),$date->clone()->setTimeFromTimeString($maxTime)),"$date->hour:$date->minute is not in between $minTime - $maxTime");
                    $this->assertContains($date->dayOfWeek,$daysOfWeek);
                }
                Carbon::setTestNow();//resetting the carbon::now to original
            }

        }

        $runAmount=$runDateTimes->count();
        $this->assertTrue($runAmount>=$runAmountMin && $runAmount<=$runAmountMax,"Ran $runAmount times while expecting between $runAmountMin to $runAmountMax");

        return $runDateTimes;
    }

    public function testRandomTimeWeeklyBasisAllDowExactAmount()
    {

        $minTime='10:00';
        $maxTime='18:00';
        $daysOfWeek=ChaoticSchedule::ALL_DOW;

        $runAmountMin=3;
        $runAmountMax=3;


        $nowMock=Carbon::createFromDate(2023,12,12);
        $macroInjectionClosure=function(ChaoticSchedule $chaoticSchedule, Event $schedule) use($daysOfWeek,$minTime,$maxTime,$runAmountMin,$runAmountMax){
            $dateAppliedSchedule=$chaoticSchedule->randomDaysSchedule($schedule,RandomDateScheduleBasis::WEEK,$daysOfWeek,$runAmountMin,$runAmountMax);
            return $chaoticSchedule->randomTimeSchedule($dateAppliedSchedule,$minTime,$maxTime);
        };
        $runDateTimes=$this->randomDateTimeScheduleTestingBoilerplate($nowMock,'seed-spring',RandomDateScheduleBasis::WEEK,$daysOfWeek,$minTime,$maxTime,$runAmountMin,$runAmountMax,$macroInjectionClosure);


    }

    public function testRandomTimeWeeklyBasisAllDowRandomAmount()
    {

        $minTime='01:00';
        $maxTime='06:00';
        $daysOfWeek=ChaoticSchedule::ALL_DOW;

        $runAmountMin=2;
        $runAmountMax=6;


        $nowMock=Carbon::createFromDate(2023,6,4);
        $macroInjectionClosure=function(ChaoticSchedule $chaoticSchedule, Event $schedule) use($daysOfWeek,$minTime,$maxTime,$runAmountMin,$runAmountMax){
            $dateAppliedSchedule=$chaoticSchedule->randomDaysSchedule($schedule,RandomDateScheduleBasis::WEEK,$daysOfWeek,$runAmountMin,$runAmountMax);
            return $chaoticSchedule->randomTimeSchedule($dateAppliedSchedule,$minTime,$maxTime);
        };
        $runDateTimes=$this->randomDateTimeScheduleTestingBoilerplate($nowMock,'seed-spring',RandomDateScheduleBasis::WEEK,$daysOfWeek,$minTime,$maxTime,$runAmountMin,$runAmountMax,$macroInjectionClosure);


    }

    public function testRandomTimeWeeklyBasisSelectiveDowExactAmount()
    {

        $minTime='19:42';
        $maxTime='22:11';
        $daysOfWeek=[Carbon::MONDAY,Carbon::WEDNESDAY,Carbon::FRIDAY];

        $runAmountMin=2;
        $runAmountMax=2;


        $nowMock=Carbon::createFromDate(2004,12,8);
        $macroInjectionClosure=function(ChaoticSchedule $chaoticSchedule, Event $schedule) use($daysOfWeek,$minTime,$maxTime,$runAmountMin,$runAmountMax){
            $dateAppliedSchedule=$chaoticSchedule->randomDaysSchedule($schedule,RandomDateScheduleBasis::WEEK,$daysOfWeek,$runAmountMin,$runAmountMax);
            return $chaoticSchedule->randomTimeSchedule($dateAppliedSchedule,$minTime,$maxTime);
        };
        $runDateTimes=$this->randomDateTimeScheduleTestingBoilerplate($nowMock,'seed-spring',RandomDateScheduleBasis::WEEK,$daysOfWeek,$minTime,$maxTime,$runAmountMin,$runAmountMax,$macroInjectionClosure);


    }


    public function testRandomTimeWeeklyBasisSelectiveDowRandomAmount()
    {

        $minTime='23:12';
        $maxTime='23:55';
        $daysOfWeek=[Carbon::MONDAY,Carbon::WEDNESDAY,Carbon::FRIDAY,Carbon::SATURDAY];

        $runAmountMin=1;
        $runAmountMax=3;


        $nowMock=Carbon::createFromDate(2006,3,7);
        $macroInjectionClosure=function(ChaoticSchedule $chaoticSchedule, Event $schedule) use($daysOfWeek,$minTime,$maxTime,$runAmountMin,$runAmountMax){
            $dateAppliedSchedule=$chaoticSchedule->randomDaysSchedule($schedule,RandomDateScheduleBasis::WEEK,$daysOfWeek,$runAmountMin,$runAmountMax);
            return $chaoticSchedule->randomTimeSchedule($dateAppliedSchedule,$minTime,$maxTime);
        };
        $runDateTimes=$this->randomDateTimeScheduleTestingBoilerplate($nowMock,'seed-spring',RandomDateScheduleBasis::WEEK,$daysOfWeek,$minTime,$maxTime,$runAmountMin,$runAmountMax,$macroInjectionClosure);


    }


    public function testRandomTimeMonthlyBasisAllDowRandomAmount()
    {

        $minTime='00:05';
        $maxTime='00:13';
        $daysOfWeek=ChaoticSchedule::ALL_DOW;

        $runAmountMin=7;
        $runAmountMax=20;


        $nowMock=Carbon::createFromDate(2014,5,21);
        $macroInjectionClosure=function(ChaoticSchedule $chaoticSchedule, Event $schedule) use($daysOfWeek,$minTime,$maxTime,$runAmountMin,$runAmountMax){
            $dateAppliedSchedule=$chaoticSchedule->randomDaysSchedule($schedule,RandomDateScheduleBasis::MONTH,$daysOfWeek,$runAmountMin,$runAmountMax);
            return $chaoticSchedule->randomTimeSchedule($dateAppliedSchedule,$minTime,$maxTime);
        };
        $runDateTimes=$this->randomDateTimeScheduleTestingBoilerplate($nowMock,'seed-spring',RandomDateScheduleBasis::MONTH,$daysOfWeek,$minTime,$maxTime,$runAmountMin,$runAmountMax,$macroInjectionClosure);


    }

    public function testRandomTimeMonthlyBasisSelectiveDowExactAmount()
    {

        $minTime='03:17';
        $maxTime='09:37';
        $daysOfWeek=[Carbon::SUNDAY,Carbon::THURSDAY,Carbon::SATURDAY];

        $runAmountMin=2;
        $runAmountMax=8;


        $nowMock=Carbon::createFromDate(2017,11,7);
        $macroInjectionClosure=function(ChaoticSchedule $chaoticSchedule, Event $schedule) use($daysOfWeek,$minTime,$maxTime,$runAmountMin,$runAmountMax){
            $dateAppliedSchedule=$chaoticSchedule->randomDaysSchedule($schedule,RandomDateScheduleBasis::MONTH,$daysOfWeek,$runAmountMin,$runAmountMax);
            return $chaoticSchedule->randomTimeSchedule($dateAppliedSchedule,$minTime,$maxTime);
        };
        $runDateTimes=$this->randomDateTimeScheduleTestingBoilerplate($nowMock,'seed-spring',RandomDateScheduleBasis::MONTH,$daysOfWeek,$minTime,$maxTime,$runAmountMin,$runAmountMax,$macroInjectionClosure);


    }


}