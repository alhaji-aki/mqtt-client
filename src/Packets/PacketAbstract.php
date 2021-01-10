<?php

namespace AlhajiAki\Mqtt\Packets;

use AlhajiAki\Mqtt\Contracts\Version;

abstract class PacketAbstract
{
    /**
     * Version to use
     *
     * @var \AlhajiAki\Mqtt\Contracts\Version
     */
    protected $version;

    protected string $payload = '';

    public function __construct(Version $version)
    {
        $this->version = $version;
    }

    abstract protected function packetType(): int;

    abstract protected function buildPayload();

    /**
     * Fixed header is the control field + packet length
     *
     * byte 1 = control packet type and flags
     * byte 2 = remaining length
     *
     * @return string
     */
    protected function fixedHeader()
    {
        return chr($this->packetType()) . $this->remainingLength();
    }

    abstract protected function variableHeader();

    /**
     * Get the remaining length
     *
     * @return string
     */
    protected function remainingLength()
    {
        $length = strlen($this->variableHeader()) + strlen($this->payload());

        return chr($length);
    }

    public function get()
    {
        $this->buildPayload();

        return $this->fixedHeader() .
            $this->variableHeader() .
            $this->payload();
    }

    protected function lengthPrefixedField($payload)
    {
        $length = strlen($payload);

        // msb + lsb + payload
        return chr($length >> 8) . chr($length % 256) . $payload;
    }

    protected function payload()
    {
        return $this->payload;
    }
}
