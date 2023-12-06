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

    protected function randomDateTimeScheduleTestingBoilerplate(Carbon $nowMock, string $rngEngineSlug , string $periodType, array $daysOfWeek ,string $minTime, string $maxTime, callable $scheduleMacroInjection ):Collection{

        //WIP

        //$daysOfWeek=empty($daysOfWeek)?ChaoticSchedule::ALL_DOW:$daysOfWeek;

        $periodBegin=$nowMock->clone()->startOf(RandomDateScheduleBasis::getString($periodType));
        $periodEnd=$nowMock->clone()->endOf(RandomDateScheduleBasis::getString($periodType));

        $period=CarbonPeriod::create($periodBegin, $periodEnd);


        $minuteIteration=1;
        $runDates=collect();
        foreach ($period as $index=>$date){
            $date=$date->startOfDay();
            for($i=0;$i<=(24*60);$i+=$minuteIteration){
                $date=$date->addMinutes($minuteIteration);

                $schedule = new Schedule();
                $schedule=$schedule->command('test');
                $chaoticSchedule=new ChaoticSchedule(
                    new SeedGenerationService($date),
                    new RNGFactory($rngEngineSlug)
                );

                $schedule=$scheduleMacroInjection($chaoticSchedule,$schedule);

                Carbon::setTestNow($date); //Mock carbon now for Laravel event
                if($schedule->isDue(app())){


                    $runDates->push($date->format('d-m-Y H:i'));

                    //maybe make these below as assertion closure
                    $this->assertTrue($date->isBetween($periodBegin,$periodEnd), "$date->day-$date->month-$date->year is not in between the designated period");
                    $this->assertTrue($date->isBetween($date->clone()->setTimeFromTimeString($minTime),$date->clone()->setTimeFromTimeString($maxTime)),"$date->hour:$date->minute is not in between $minTime - $maxTime");
                    $this->assertContains($date->dayOfWeek,$daysOfWeek);
                }
                Carbon::setTestNow();//resetting the carbon::now to original
            }

        }

        return $runDates;
    }

    public function testRandomTimeWeeklyBasisBasic()
    {

        $minTime='10:00';
        $maxTime='18:00';
        $daysOfWeek=ChaoticSchedule::ALL_DOW;


        $nowMock=Carbon::createFromDate(2021,10,05);
        $macroInjectionClosure=function(ChaoticSchedule $chaoticSchedule, Event $schedule) use($daysOfWeek,$minTime,$maxTime){
            $dateAppliedSchedule=$chaoticSchedule->randomDaysSchedule($schedule,RandomDateScheduleBasis::WEEK,$daysOfWeek,3,3);
            return $chaoticSchedule->randomTimeSchedule($dateAppliedSchedule,$minTime,$maxTime);
        };
        $runDateTimes=$this->randomDateTimeScheduleTestingBoilerplate($nowMock,'seed-spring',RandomDateScheduleBasis::WEEK,$daysOfWeek,$minTime,$maxTime,$macroInjectionClosure);

    }


}