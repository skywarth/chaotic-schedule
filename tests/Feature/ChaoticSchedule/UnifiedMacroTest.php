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

    protected function randomDateScheduleTestingBoilerplate(Carbon $nowMock, string $periodType, callable $scheduleMacroInjection ):Collection{

        //WIP
        //assertion
        $periodBegin=$nowMock->clone()->startOf(RandomDateScheduleBasis::getString($periodType));
        $periodEnd=$nowMock->clone()->endOf(RandomDateScheduleBasis::getString($periodType));

        $period=CarbonPeriod::create($periodBegin, $periodEnd);


        $runDates=collect();
       /* foreach ($period as $index=>$date){
            $schedule = new Schedule();
            $schedule=$schedule->command('test');
            $chaoticSchedule=new ChaoticSchedule(
                new SeedGenerationService($date),
                new RNGFactory($rngEngineSlug)
            );
            $schedule=$chaoticSchedule->randomDaysSchedule($schedule,$periodType,$daysOfWeek,$timesMin,$timesMax,$uniqueIdentifier,$closure);



            Carbon::setTestNow($date); //Mock carbon now for Laravel event
            if($schedule->isDue(app())){

                $runDates->push($date);

                $this->assertTrue($date->isBetween($periodBegin,$periodEnd),'Generated random run date is not in the range');
                $this->assertContains($date->dayOfWeek,$daysOfWeek);

            }
            Carbon::setTestNow();//resetting the carbon::now to original
        }

        $this->assertGreaterThanOrEqual($timesMin,$runDates->count());
        $this->assertLessThanOrEqual($timesMax,$runDates->count());*/
        return $runDates;
    }

    public function testRandomTimeWeeklyBasisBasic()
    {
        //WIP
        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $this->getChaoticSchedule()->randomTimeSchedule($schedule,'09:00','12:00')->randomDays(RandomDateScheduleBasis::WEEK,[Carbon::MONDAY,Carbon::SATURDAY,Carbon::WEDNESDAY],2,2,);
        $this->assertTrue(true);
    }


}