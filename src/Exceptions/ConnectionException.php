<?php

namespace AlhajiAki\Mqtt\Exceptions;

use Exception;

class ConnectionException extends Exception
{
    public function __construct($responseCode)
    {
        parent::__construct("Unable to connect to broker. Response Code: $responseCode");
    }
}
