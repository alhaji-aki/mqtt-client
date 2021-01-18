<?php

namespace AlhajiAki\Mqtt\Packets;

use AlhajiAki\Mqtt\Contracts\PacketEvent;
use AlhajiAki\Mqtt\Contracts\Version;
use AlhajiAki\Mqtt\Qos\Levels;

class Publish extends PacketAbstract implements PacketEvent
{
    const EVENT = 'PUBLISH';

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

    public function packetTypeString(): string
    {
        return 'PUBLISH';
    }

    protected function fixedHeader(): string
    {
        $byte = $this->packetType() << 4 + ($this->qos << 1);

        if ($this->qos > 0) {
            if ($this->dup) {
                $byte += 0x08;
            }
        }

        if ($this->retain) {
            $byte += 0x01;
        }

        return chr($byte) . $this->encodeRemainingLength($this->remainingLength());
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
     * @return int|null
     */
    public function getPacketId()
    {
        return $this->packetId;
    }

    public function setPacketId($packetId)
    {
        $this->packetId = $packetId;
    }

    public function getTopic()
    {
        return $this->topic;
    }

    public function setTopic(string $topic)
    {
        $this->topic = $topic;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    public function getQos()
    {
        return $this->qos;
    }

    public function setQos(int $qos)
    {
        $this->qos = $qos;
    }

    public function getDup()
    {
        return $this->dup;
    }


    public function setDup(bool $dup)
    {
        $this->dup = $dup;
    }

    public function getRetain()
    {
        return $this->retain;
    }

    public function setRetain(bool $retain)
    {
        $this->retain = $retain;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    public static function parse(Version $version, $input)
    {
        $packet = new static($version);

        // the fixed header byte is the 1st byte
        $fixedHeaderByte = $input[0];

        // setting flags
        $flags = ord($fixedHeaderByte) & 0x0f;
        $packet->setRetain($flags == 0x01);
        $packet->setDup($flags == 0x80);
        $packet->setQos(($flags >> 1) & 0x03);

        // the variable header starts from byte 2 which contains the topic and packet identifier
        // the topic is a length prefixed string. so we can get the length from the 2nd and 3rd byte
        $topicLength = (ord($input[2]) << 8) + ord($input[3]);
        $packet->setTopic(substr($input, 4, $topicLength));

        $remaingData = substr($input, 4 + $topicLength);

        // no packet id for QoS 0, remaining data is the message
        if ($packet->getQos() == Levels::AT_MOST_ONCE_DELIVERY) {
            $packet->setPayload($remaingData);
        } else {
            // packet id is the first two bytes of the remaining data
            $packet->setPacketId((ord($remaingData[0]) << 8) + ord($remaingData[1]));
            // rest of the remaining data is the message
            $packet->setPayload(substr($remaingData, 2));
        }

        return $packet;
    }
}
