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

    abstract public function packetType(): int;

    /**
     * Fixed header is the control field + packet length
     *
     * @return string
     */
    abstract protected function fixedHeader();

    abstract protected function variableHeader();

    abstract protected function build();

    protected function remainingLength()
    {
        return strlen($this->variableHeader()) + $this->payloadLength();
    }

    protected function payloadLength()
    {
        return strlen($this->payload());
    }

    public function payload()
    {
        return $this->payload;
    }

    public function get($message = '')
    {
        $this->build();
        return $this->fixedHeader() .
            $this->variableHeader() .
            $this->payload();
    }
}
