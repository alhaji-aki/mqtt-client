<?php

namespace AlhajiAki\Mqtt\Packets;

use AlhajiAki\Mqtt\Traits\DefaultFixedHeader;

class Unsubscribe extends PacketAbstract
{
    use DefaultFixedHeader;

    /**
     * @var int
     */
    protected $packetId;

    protected $topicFilters = [];

    protected function packetType(): int
    {
        return PacketTypes::UNSUBSCRIBE;
    }

    public function packetTypeString(): string
    {
        return 'UNSUBSCRIBE';
    }

    protected function fixedHeader(): string
    {
        return chr(($this->packetType() << 4) + 0x02) . $this->encodeRemainingLength($this->remainingLength());
    }

    protected function variableHeader()
    {
        $this->packetId = $this->version->getNextPacketId();

        return $this->version->getPacketIdPayload($this->packetId);
    }

    public function removeSubscriptionArray(array $topics)
    {
        foreach ($topics as $item) {
            $this->removeSubscription($item);
        }
    }

    public function removeSubscription(string $topic)
    {
        array_push($this->topicFilters, $topic);
    }

    protected function buildPayload()
    {
        foreach ($this->topicFilters as $topic) {
            $this->payload .= $this->lengthPrefixedField($topic);
        }

        return $this->payload;
    }

    public function getPacketId(): int
    {
        return $this->packetId;
    }

    public function getTopicFilters(): array
    {
        return $this->topicFilters;
    }
}
