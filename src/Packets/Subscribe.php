<?php

namespace AlhajiAki\Mqtt\Packets;

use AlhajiAki\Mqtt\Validators\CheckTopicsArray;

class Subscribe extends PacketAbstract
{
    /**
     * @var int
     */
    protected $packetId;

    protected $topicFilters = [];

    protected function packetType(): int
    {
        return PacketTypes::SUBSCRIBE;
    }

    public function packetTypeString(): string
    {
        return 'SUBSCRIBE';
    }

    protected function fixedHeader(): string
    {
        return chr(($this->packetType() << 4) + 0x02) . $this->encodeRemainingLength($this->remainingLength());
    }

    /**
     * @return string
     */
    protected function variableHeader()
    {
        $this->packetId = $this->version->getNextPacketId();

        return $this->version->getPacketIdPayload($this->packetId);
    }

    public function addSubscriptionArray(array $topics)
    {
        foreach ($topics as $key => $item) {
            CheckTopicsArray::check($key, $item);

            $this->addSubscription($item['topic'], $item['qos']);
        }
    }

    public function addSubscription(string $topic, int $qos)
    {
        array_push($this->topicFilters, [
            'topic' => $topic,
            'qos' => $qos
        ]);
    }

    protected function buildPayload()
    {
        foreach ($this->topicFilters as $topic) {
            $this->payload .= $this->lengthPrefixedField($topic['topic']);
            $this->payload .= chr($topic['qos']);
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
