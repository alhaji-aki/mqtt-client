<?php

namespace AlhajiAki\Mqtt\Validators;

use Exception;
use Illuminate\Support\Arr;

class CheckTopicsArray
{
    /**
     * Checks if the array item has the correct properties
     *
     * @param int $index
     * @param array $item
     * @return bool
     * @throws Exception
     */
    public static function check(int $index, array $item)
    {
        if (!Arr::has($item, ['topic', 'qos'])) {
            throw new Exception("Array at position $index must contain a topic and qos keys");
        }

        if (!is_string($item['topic'])) {
            throw new Exception("Topic in topics array at position $index must be a string");
        }

        if (!is_int($item['qos'])) {
            throw new Exception("Qos in topics array at position $index must be an integer");
        }

        return true;
    }
}
