<?php

namespace AlhajiAki\Mqtt\Validators;

use Exception;
use Illuminate\Support\Arr;

class CheckTopicsArray
{
    public static function check($item)
    {
        if (!Arr::has($item, ['topic', 'qos'])) {
            throw new Exception('Array ' . print_r($item) . ' must contain a topic and qos keys');
        }

        if (!is_string($item['topic'])) {
            throw new Exception('Topics must be a string');
        }

        if (!is_int($item['qos'])) {
            throw new Exception('Qos must be an integer');
        }

        return true;
    }
}
