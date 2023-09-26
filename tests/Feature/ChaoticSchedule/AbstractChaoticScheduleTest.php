<?php

namespace Skywarth\ChaoticSchedule\Tests\Feature\ChaoticSchedule;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Skywarth\ChaoticSchedule\Exceptions\IncompatibleClosureResponse;
use Skywarth\ChaoticSchedule\Exceptions\IncorrectRangeException;
use Skywarth\ChaoticSchedule\Exceptions\InvalidDateFormatException;
use Skywarth\ChaoticSchedule\RNGs\Adapters\RandomNumberGeneratorAdapter;
use Skywarth\ChaoticSchedule\RNGs\RNGFactory;
use Skywarth\ChaoticSchedule\Services\ChaoticSchedule;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\TestCase;

abstract class AbstractChaoticScheduleTest extends TestCase
{
    protected static ChaoticSchedule $chaoticSchedule;
    protected static function getChaoticSchedule(){
        if(empty(self::$chaoticSchedule)){
            self::$chaoticSchedule=new ChaoticSchedule(
                new SeedGenerationService(),
                new RNGFactory('seed-spring')
            );
        }
        return self::$chaoticSchedule;
    }

    protected const DefaultRNGEngineSlug='mersenne-twister';


    protected function generateRandomTimeConsecutiveDays(
        int $daysCount,
        string $rngEngineSlug=self::DefaultRNGEngineSlug,
        string $minTime='06:18',
        string $maxTime='19:42'
    ):Collection{
        $date=Carbon::now()->startOfDay();
        $schedule = new Schedule();
        $schedule=$schedule->command('foo')->daily();
        $schedules=collect();
        for($i=0;$i<$daysCount;$i++){
            $chaoticSchedule=new ChaoticSchedule(
                new SeedGenerationService($date),
                new RNGFactory($rngEngineSlug)
            );
            //$designatedRuns->push($nextRun->format('H:i'));
            $schedules->push(clone $chaoticSchedule->randomTimeSchedule($schedule,$minTime,$maxTime));
            $date->addDay();
        }
        return $schedules;
    }

    protected function calcChiSquared(Collection $observed, Collection $expected): float {
        $sum = 0;

        foreach ($observed as $index => $value) {
            $sum += pow($value - $expected[$index], 2) / $expected[$index];
        }

        return $sum;
    }


}