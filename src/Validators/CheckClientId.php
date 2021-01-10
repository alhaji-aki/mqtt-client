<?php

namespace AlhajiAki\Mqtt\Validators;

use Exception;

class CheckClientId
{
    public static function check($clientId)
    {
        // client id must not be empty
        if (empty($clientId)) {
            throw new Exception('Client ID is not specified');
        }

        // between 1 and 23
        $length = strlen($clientId);
        if ($length < 1 || $length > 23) {
            throw new Exception('Client ID must not be between 1 and 23 characters');
        }

        // contains only alpha numeric
        if (!ctype_alnum($clientId)) {
            throw new Exception('Client ID must only contain alpha numeric characters.');
        }

        return true;
    }
}
