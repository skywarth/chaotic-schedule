<?php

namespace Skywarth\ChaoticSchedule\Tests\Feature\ChaoticSchedule;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Skywarth\ChaoticSchedule\Enums\RandomDateScheduleBasis;
use Skywarth\ChaoticSchedule\Exceptions\IncompatibleClosureResponse;
use Skywarth\ChaoticSchedule\Exceptions\IncorrectRangeException;
use Skywarth\ChaoticSchedule\Exceptions\InvalidDateFormatException;
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;
use Skywarth\ChaoticSchedule\Services\ChaoticSchedule;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\TestCase;

class RandomDateMacrosTest extends AbstractChaoticScheduleTest
{

    protected const AllDOW=[//days of week
        Carbon::MONDAY,
        Carbon::TUESDAY,
        Carbon::WEDNESDAY,
        Carbon::THURSDAY,
        Carbon::FRIDAY,
        Carbon::SATURDAY,
        Carbon::SUNDAY,
    ];


    public function test_random_days_week_basis_all_DOW_exact_times()
    {
        $nowMock=Carbon::createFromDate(2023,6,01);
        //$nowMock=Carbon::now();
        $periodType=RandomDateScheduleBasis::WEEK;
        $timesMin=2;
        $timesMax=2;
        $daysOfWeek=[];

        $schedule = new Schedule();
        $schedule=$schedule->command('test');
        $chaoticSchedule=new ChaoticSchedule(
            new SeedGenerationService($nowMock),
            new RNGFactory('mersenne-twister')
        );
        $schedule=$chaoticSchedule->randomDaysSchedule($schedule,$periodType,$daysOfWeek,2,2);

        //assertion
        $periodBegin=$nowMock->clone()->startof(RandomDateScheduleBasis::getString($periodType));
        $periodEnd=$nowMock->clone()->endOf(RandomDateScheduleBasis::getString($periodType));

        $period=CarbonPeriod::create($periodBegin, $periodEnd);


        $runsCounter=0;
        foreach ($period as $index=>$date){
            dump($date->format('d-m-Y'));

            $this->assertTrue($date->isBetween($periodBegin,$periodEnd),'Generated random date is not in the range');

            Carbon::setTestNow($date); //Mock carbon now for Laravel event
            if($schedule->isDue(app())){
                //TODO: isDue not working due (no pun intended) to the fact that we don't register any date scheduling on the command.
                //How about we simply do indicate direct date on the schedule, in the randomDays ?
                $runsCounter++;
                $this->assertContains($date->dayOfWeek,self::AllDOW);
            }
            Carbon::setTestNow($date);//resetting the carbon::now to original
        }

        $this->assertGreaterThanOrEqual($timesMin,$runsCounter);
        $this->assertLessThanOrEqual($timesMax,$runsCounter);


    }


}