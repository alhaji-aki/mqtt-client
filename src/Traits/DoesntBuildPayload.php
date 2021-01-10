<?php

namespace AlhajiAki\Mqtt\Traits;

trait DoesntBuildPayload
{
    protected function buildPayload()
    {
        return '';
    }
}
