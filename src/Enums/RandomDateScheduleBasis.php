<?php

namespace Skywarth\ChaoticSchedule\Enums;

enum RandomDateScheduleBasis: int
{
    case WEEK = 10;
    case MONTH = 20;
    case YEAR = 30;

    /**
     * Returns the Carbon period name corresponding to this basis.
     * Used by ChaoticSchedule to call $date->startOf('week') / endOf('month') etc.
     */
    public function periodString(): string
    {
        return match ($this) {
            self::WEEK => 'WEEK',
            self::MONTH => 'MONTH',
            self::YEAR => 'YEAR',
        };
    }

    /**
     * Approximate day count for the period.
     * Coarse heuristic; not authoritative (months and years vary).
     */
    public function dayCount(): int
    {
        return match ($this) {
            self::WEEK => 7,
            self::MONTH => 30,
            self::YEAR => 365,
        };
    }
}
