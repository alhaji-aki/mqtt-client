<?php

namespace AlhajiAki\Mqtt\Exceptions;

use Exception;

class UnexpectedPacketException extends Exception
{
    public function __construct($packet)
    {
        parent::__construct("Unexpected packet: $packet");
    }
}
