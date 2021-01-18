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

    abstract public function packetTypeString(): string;

    /**
     * Fixed header is the control field + packet length
     *
     * byte 1 = control packet type and flags
     * byte 2 = remaining length
     *
     * @return string
     */
    abstract protected function fixedHeader(): string;

    abstract protected function variableHeader();

    abstract protected function buildPayload();

    /**
     * Get the remaining length
     *
     * @return int
     */
    protected function remainingLength()
    {
        return strlen($this->variableHeader()) + strlen($this->payload());
    }

    protected function encodeRemainingLength(int $length)
    {
        if ($length < 0 || $length >= 128 * 128 * 128 * 128) {
            // illegal length
            return false;
        }

        $output = '';

        do {
            $encodedByte = $length & 0x7f;
            $length = $length >> 7;
            if ($length > 0) {
                $encodedByte = $encodedByte | 128;
            }

            $output .= chr($encodedByte);
        } while ($length > 0);

        return $output;
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

    /**
     * @param int $start
     * @param string $input
     * @return string
     */
    protected static function getPayloadLengthPrefixFieldInRawInput($start, $input)
    {
        $headerLength = 2;
        $header = substr($input, $start, $headerLength);

        $lengthOfMessage = ord($header[1]);

        return substr($input, $start + $headerLength, $lengthOfMessage);
    }
}
