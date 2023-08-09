<?php

namespace Skywarth\ChaoticSchedule\Tests\Unit;

use Carbon\Carbon;
use Skywarth\ChaoticSchedule\Services\SeedGenerationService;
use Skywarth\ChaoticSchedule\Tests\TestCase;

class SeedGenerationServiceTest extends TestCase
{

    private SeedGenerationService $service;
    protected function getServiceInstance():SeedGenerationService{
        if(empty($this->service)){
            $this->service=new SeedGenerationService();
        }
        return $this->service;
    }

    public function test_seed_for_day_same_identifier()
    {
        $identifier='my-unique-identifier';

        $this->assertSame($this->getServiceInstance()->seedForDay($identifier),$this->getServiceInstance()->seedForDay($identifier));
    }

    public function test_seed_for_day_different_identifier()
    {
        $this->assertNotSame(
            $this->getServiceInstance()->seedForDay('dog'),
            $this->getServiceInstance()->seedForDay('cat')
        );
    }

    public function test_seed_for_day_differs_per_date()
    {
        $seeds=collect();
        $id='test';
        Carbon::setTestNow(Carbon::now()->addDay());
        $seeds->push(
            $this->getServiceInstance()->seedForDay($id)
        );
        Carbon::setTestNow(Carbon::now()->addDays(3));
        $seeds->push(
            $this->getServiceInstance()->seedForDay($id)
        );
        Carbon::setTestNow(Carbon::now()->addWeeks(2));
        $seeds->push(
            $this->getServiceInstance()->seedForDay($id)
        );
        Carbon::setTestNow(Carbon::now()->addMonth());
        $seeds->push(
            $this->getServiceInstance()->seedForDay($id)
        );
        Carbon::setTestNow(Carbon::now()->addYear());
        $seeds->push(
            $this->getServiceInstance()->seedForDay($id)
        );
        //assert that all of them are different and unique
        $this->assertSame($seeds->toArray(),$seeds->unique()->toArray());
    }



}