<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class OverlappingBookingException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->message = "Can't have overlapping bookings for the same position!" . $message ? $message : null;
    }
}
