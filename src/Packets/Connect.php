<?php

namespace AlhajiAki\Mqtt\Packets;

use AlhajiAki\Mqtt\Contracts\Version;
use AlhajiAki\Mqtt\Options;
use AlhajiAki\Mqtt\Qos\Levels;

class Connect extends PacketAbstract
{
    protected string $clientId;

    protected bool $cleanSession = true;

    protected string $username = '';

    protected string $password = '';

    protected string $lastWillTopic = '';

    protected string $lastWillRetain = '';

    protected string $lastWillMessage = '';

    protected int $lastWillQos = Levels::AT_MOST_ONCE_DELIVERY;

    protected int $keepAlive = 60;

    public function __construct(Version $version, Options $options)
    {
        parent::__construct($version);

        $this->clientId = $options->clientId;

        $this->cleanSession = $options->cleanSession;

        $this->username = $options->username;

        $this->password = $options->password;

        $this->lastWillTopic = $options->lastWillTopic;

        $this->lastWillRetain = $options->lastWillRetain;

        $this->lastWillMessage = $options->lastWillMessage;

        $this->lastWillQos = $options->lastWillQos;

        $this->keepAlive = $options->keepAlive;
    }

    public function packetType(): int
    {
        return PacketTypes::CONNECT;
    }

    public function build()
    {
        // TODO: build payload here
        $payload = $this->fixedHeader() . $this->variableHeader() . $this->payload();

        return $payload;

        // return $this->getFixedHeader() .
        //     $this->getVariableHeader() .
        //     $this->getPayload();
        // $payload = chr(0x00);
        // var_dump($payload);

        // $payload .= chr($this->version->protocolVersion());
        // var_dump($payload);
    }

    public function variableHeader(): string
    {
        return '';
    }

    public function payload(): string
    {
        return '';
    }

    protected function fixedHeader()
    {
        return $this->packetType() . $this->remainingLength();
    }
}
