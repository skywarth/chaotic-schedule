<?php

namespace Skywarth\ChaoticSchedule\Enums;

use Skywarth\ChaoticSchedule\Exceptions\InvalidScheduleBasisProvided;

class RandomDateScheduleBasis
{
    const WEEK=10;
    const MONTH=20;

    const YEAR=30;


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
        return array_flip(self::getAll())[$enumVal];
    }

}