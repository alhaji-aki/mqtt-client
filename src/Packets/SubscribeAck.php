<?php

namespace AlhajiAki\Mqtt\Packets;

use AlhajiAki\Mqtt\Contracts\PacketEvent;
use AlhajiAki\Mqtt\Contracts\Version;
use AlhajiAki\Mqtt\Traits\DefaultFixedHeader;
use AlhajiAki\Mqtt\Traits\DoesntBuildPayload;
use AlhajiAki\Mqtt\Traits\EmptyVariableHeader;

class SubscribeAck extends PacketAbstract implements PacketEvent
{
    use DefaultFixedHeader, DoesntBuildPayload, EmptyVariableHeader;

    const EVENT = 'SUBACK';

    const SUCCESS_MAXIMUM_QOS_0 = 0;
    const SUCCESS_MAXIMUM_QOS_1 = 1;
    const SUCCESS_MAXIMUM_QOS_2 = 2;
    const FAILURE = 128;

    protected $qosArray = [];

    protected function packetType(): int
    {
        return PacketTypes::SUBACK;
    }

    public function packetTypeString(): string
    {
        return 'SUBACK';
    }

    public static function parse(Version $version, $input)
    {
        $packet = new static($version);

        $variableHeader = substr($input, 2, 2);

        $packetIdentifier = unpack("n*", $variableHeader);
        $packet->setPacketId($packetIdentifier[1]);

        $payload = substr($input, 4);

        for ($i = 0; $i < strlen($payload); $i++) {
            $packet->addQoS(ord($payload[$i]));
        }

        return $packet;
    }

    public function addQoS(int $qos)
    {
        array_push($this->qosArray, $qos);
    }

    public function getQos(): array
    {
        return $this->qosArray;
    }

    public function setPacketId(int $packetId)
    {
        $this->packetId = $packetId;
    }

    public function getPacketId(): int
    {
        return $this->packetId;
    }

    protected function getStatusMessage($statusCode)
    {
        $statuses = [
            self::SUCCESS_MAXIMUM_QOS_0 => 'Success - Maximum QoS 0.',
            self::SUCCESS_MAXIMUM_QOS_1 => 'Success - Maximum QoS 1.',
            self::SUCCESS_MAXIMUM_QOS_2 => 'Success - Maximum QoS 2.',
            self::FAILURE => 'Failed to subscribe to topic',
        ];

        return $this->statusMessage = $statuses[$statusCode];
    }

    public function getResponse(array $topicFilters)
    {
        for ($i = 0; $i < count($topicFilters); $i++) {
            $topicFilters[$i]['message'] = $this->getStatusMessage($this->qosArray[$i]);
        }

        return $topicFilters;
    }
}
