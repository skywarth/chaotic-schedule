<?php

namespace Skywarth\ChaoticSchedule\Exceptions;

class MissingSeedException extends \Exception
{
    protected $message='RNGAdapter is missing seed value! Set the seed first, before accessing';

}