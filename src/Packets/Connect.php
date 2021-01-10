<?php

namespace AlhajiAki\Mqtt\Packets;

use AlhajiAki\Mqtt\Config;
use AlhajiAki\Mqtt\Contracts\Version;
use AlhajiAki\Mqtt\Qos\Levels;
use AlhajiAki\Mqtt\Validators\CheckClientId;

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

    public function __construct(Version $version, Config $config)
    {
        parent::__construct($version);

        $this->clientId = $config->clientId;

        $this->cleanSession = $config->cleanSession;

        $this->username = $config->username;

        $this->password = $config->password;

        $this->lastWillTopic = $config->lastWillTopic;

        $this->lastWillRetain = $config->lastWillRetain;

        $this->lastWillMessage = $config->lastWillMessage;

        $this->lastWillQos = $config->lastWillQos;

        $this->keepAlive = $config->keepAlive;
    }

    protected function packetType(): int
    {
        return PacketTypes::CONNECT;
    }

    protected function variableHeader(): string
    {
        $header = '';
        // 3.1.2.1 Protocol Name
        // MSB(0) byte 1
        $header .= chr(PacketTypes::MOST_SIGNIFICANT_BYTE);

        // LSB(4) - byte 2
        $header .= chr($this->version->protocolVersion());

        // bytes 3,4,5,6
        $header .= $this->version->protocolIdentifierString();

        // 3.1.2.2 Protocol Level
        //  Protocol Level byte - byte 7
        $header .= chr($this->version->protocolVersion());

        // 3.1.2.3 Connect Flags
        $header .= chr($this->connectFlags());

        // keep alive MSB
        $header .= chr($this->keepAlive >> 8);
        // keep alive LSB
        $header .= chr($this->keepAlive % 256);

        return $header;
    }

    protected function buildPayload()
    {
        if (CheckClientId::check($this->clientId)) {
            $this->payload .= $this->lengthPrefixedField($this->clientId);
        }

        if (!empty($this->lastWillTopic) && !empty($this->lastWillMessage)) {
            $this->payload .= $this->lengthPrefixedField($this->lastWillTopic);
            $this->payload .= $this->lengthPrefixedField($this->lastWillMessage);
        }

        if (!empty($this->username)) {
            $this->payload .= $this->lengthPrefixedField($this->username);
        }

        if (!empty($this->password)) {
            $this->payload .= $this->lengthPrefixedField($this->password);
        }

        // var_dump($this->payload);
        return $this->payload;
    }

    protected function connectFlags()
    {
        $byte = 0;

        if ($this->cleanSession) {
            $byte += 0x02;
        }

        if (!empty($this->lastWillTopic) && !empty($this->lastWillMessage)) {
            $byte += 0x04;
            if ($this->lastWillQos) {
                $byte += 1 << 3;
            }

            if ($this->lastWillRetain) {
                $byte += 0x20;
            }
        }

        if (!empty($this->username)) {
            $byte += 0x80;

            if (!empty($this->password)) {
                $byte += 0x40;
            }
        }

        return $byte;
    }
}
