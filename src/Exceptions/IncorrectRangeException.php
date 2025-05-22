<?php

namespace Skywarth\ChaoticSchedule\Exceptions;

class IncorrectRangeException extends \Exception
{

    /**
     * @param string $min
     * @param string $max
     */
    public function __construct(private string $min, private string $max)
    {
        $msg="{$this->getMin()} is bigger/later than {$this->getMax()}! Please correct your parameters.";
        parent::__construct($msg);
    }

    public function getMax(): string
    {
        return $this->max;
    }

    public function getMin(): string
    {
        return $this->min;
    }




}