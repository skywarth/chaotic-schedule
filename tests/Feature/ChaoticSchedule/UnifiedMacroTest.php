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

    protected function randomDateTimeScheduleTestingBoilerplate(Carbon $nowMock, string $rngEngineSlug , string $periodType, callable $scheduleMacroInjection ):Collection{

        //WIP
        //assertion
        $periodBegin=$nowMock->clone()->startOf(RandomDateScheduleBasis::getString($periodType));
        $periodEnd=$nowMock->clone()->endOf(RandomDateScheduleBasis::getString($periodType));

        $period=CarbonPeriod::create($periodBegin, $periodEnd);


        $runDates=collect();
        foreach ($period as $index=>$date){
            $date=$date->startOfDay();
            for($i=0;$i<=(24*60);$i++){
                $date=$date->addMinute();

                $schedule = new Schedule();
                $schedule=$schedule->command('test');
                $chaoticSchedule=new ChaoticSchedule(
                    new SeedGenerationService($date),
                    new RNGFactory($rngEngineSlug)
                );

                $schedule=$scheduleMacroInjection($chaoticSchedule,$schedule);

                Carbon::setTestNow($date); //Mock carbon now for Laravel event
                if($schedule->isDue(app())){

                    $runDates->push($date);
                }
                Carbon::setTestNow();//resetting the carbon::now to original
            }

        }

        return $runDates;
    }

    public function testRandomTimeWeeklyBasisBasic()
    {

        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $nowMock=Carbon::createFromDate(2021,10,05);
        $this->assertTrue(true);
        return;//WIP
        $macroInjectionClosure=function(ChaoticSchedule $chaoticSchedule, Event $schedule){
            $dateAppliedSchedule=$chaoticSchedule->randomDaysSchedule($schedule,RandomDateScheduleBasis::WEEK,[],3,3);
            return $chaoticSchedule->randomTimeSchedule($dateAppliedSchedule,'10:00','18:00');
        };
        $runDateTimes=$this->randomDateTimeScheduleTestingBoilerplate($nowMock,'seed-spring',RandomDateScheduleBasis::WEEK,$macroInjectionClosure);
        dd($runDateTimes);
        $this->assertTrue(true);
    }


}