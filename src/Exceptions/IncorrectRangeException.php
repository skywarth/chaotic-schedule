<?php

namespace Skywarth\ChaoticSchedule\Exceptions;

class IncorrectRangeException extends \Exception
{

    private string $min;
    private string $max;

    /**
     * @param string $min
     * @param string $max
     */
    public function __construct(string $min, string $max)
    {
        $this->min = $min;
        $this->max = $max;
        $msg="${min} is bigger/later than ${max}! Please correct your parameters.";
        parent::__construct($msg);
    }


}