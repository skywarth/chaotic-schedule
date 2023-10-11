<?php

namespace Skywarth\ChaoticSchedule\Enums;

use Skywarth\ChaoticSchedule\Exceptions\InvalidScheduleBasisProvided;

class RandomDateScheduleBasis
{
    public const WEEK=10;
    public const MONTH=20;

    public const YEAR=30;


    private const DAYS_PER_PERIOD=[
      self::WEEK=>7,
      self::MONTH=>30,
      self::YEAR=>365,
    ];


    /**
     * @throws InvalidScheduleBasisProvided
     */
    public static function validate(int $basis):void{
        if(!self::isValid($basis)){
            throw new InvalidScheduleBasisProvided("Provided schedule basis is invalid, '$basis' given.");
        }
    }

    public static function isValid(int $basis):bool{
        return in_array($basis,self::getAll());
    }

    public static function getAll():array{
        return[
            'WEEK'=>self::WEEK,
            'MONTH'=>self::MONTH,
            'YEAR'=>self::YEAR,
        ];
    }

    public static function getString(int $enumVal):string{
        self::validate($enumVal);
        return array_flip(self::getAll())[$enumVal];
    }

    public static function getDayCount(int $enumVal):int{
        self::validate($enumVal);
        return self::DAYS_PER_PERIOD[$enumVal];
    }

}