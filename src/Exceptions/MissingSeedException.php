<?php

namespace Skywarth\ChaoticSchedule\Exceptions;

use Throwable;

class MissingSeedException extends \Exception
{
    public function __construct(string $message = 'RNGAdapter is missing seed value! Set the seed first, before accessing', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}