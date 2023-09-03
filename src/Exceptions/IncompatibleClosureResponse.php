<?php

namespace Skywarth\ChaoticSchedule\Exceptions;

class IncompatibleClosureResponse extends \Exception
{
    protected string $expectedType;
    protected string $returnedType;

    /**
     * @return string
     */
    public function getExpectedType(): string
    {
        return $this->expectedType;
    }

    /**
     * @return string
     */
    public function getReturnedType(): string
    {
        return $this->returnedType;
    }




    /**
     * @param string $expectedType
     * @param string $returnedType
     */
    public function __construct(string $expectedType, string $returnedType)
    {
        $this->expectedType = $expectedType;
        $this->returnedType = $returnedType;
        $msg="The closure you've provided returned an incompatible type. Expected ${expectedType}, got ${returnedType}";
        parent::__construct($msg);
    }


}