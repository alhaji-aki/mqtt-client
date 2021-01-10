<?php

namespace AlhajiAki\Mqtt\Packets;

use AlhajiAki\Mqtt\Contracts\PacketEvent;
use AlhajiAki\Mqtt\Contracts\Version;
use AlhajiAki\Mqtt\Qos\Levels;

class Publish extends PacketAbstract implements PacketEvent
{
    /**
     * @var int
     */
    protected $packetId;

    /**
     * @var string
     */
    protected string $topic;

    /**
     * @var string
     */
    protected string $message;

    /**
     * @var int
     */
    protected int $qos = Levels::AT_MOST_ONCE_DELIVERY;

    /**
     * @var bool
     */
    protected bool $dup = false;

    /**
     * @var bool
     */
    protected bool $retain = false;

    public function __construct(Version $version)
    {
        parent::__construct($version);
    }

    protected function packetType(): int
    {
        return PacketTypes::PUBLISH;
    }

    protected function fixedHeader(): string
    {
        $byte = $this->packetType() + ($this->qos << 1);

        if ($this->qos > 0) {
            if ($this->dup) {
                $byte += 0x08;
            }
        }

        if ($this->retain) {
            $byte += 0x01;
        }

        return chr($byte) . $this->remainingLength();
    }

    protected function variableHeader()
    {
        $header = $this->lengthPrefixedField($this->topic);

        $this->packetId = $this->version->getNextPacketId();

        if ($this->qos >= Levels::AT_LEAST_ONCE_DELIVERY) {
            $header .= $this->version->getPacketIdPayload($this->packetId);
        }

        return $header;
    }

    protected function buildPayload()
    {
        $this->payload = $this->message;

        return $this->payload;
    }

    /**
     * @return int
     */
    public function getPacketId(): int
    {
        return $this->packetId;
    }

    public function setTopic(string $topic)
    {
        $this->topic = $topic;
    }

    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    public function setQos(int $qos)
    {
        $this->qos = $qos;
    }

    public function setDup(bool $dup)
    {
        $this->dup = $dup;
    }

    public function setRetain(bool $retain)
    {
        $this->retain = $retain;
    }

    public static function parse(Version $version, $input)
    {
        $packet = new static($version);

        var_dump($input);

        $flags = ord($input[0]) & 0x0f;
        $packet->setDup($flags == 0x80);
        $packet->setRetain($flags == 0x01);
        $packet->setQos(($flags >> 1) & 0x03);

        $topicLength = (ord($input[$bytesRead]) << 8) + ord($input[$bytesRead + 1]);
        $packet->setTopic(substr($input, 2 + $bytesRead, $topicLength));
        $payload = substr($input, $bytesRead + 2 + $topicLength);

        if ($packet->getQos() == QoS\Levels::AT_MOST_ONCE_DELIVERY) {
            // no packet id for QoS 0, the payload is the message
            $packet->setPayload($payload);
        } else {
            if (strlen($payload) >= 2) {
                $packet->setPacketId((ord($payload[0]) << 8) + ord($payload[1]));
                // skip packet id (2 bytes) for QoS 1 and 2
                $packet->setPayload(substr($payload, 2));
            }
        }

        return $packet;
    }
}
