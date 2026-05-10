<?php

namespace Skywarth\ChaoticSchedule\Enums;

enum RandomDateScheduleBasis: int
{
    case WEEK = 10;
    case MONTH = 20;
    case YEAR = 30;

    /**
     * Carbon period unit name for $date->startOf(...)/endOf(...)/next(...).
     * Carbon accepts these case-insensitively.
     */
    public function periodString(): string
    {
        return $this->name;
    }
}
