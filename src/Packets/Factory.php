<?php

namespace AlhajiAki\Mqtt\Packets;

use AlhajiAki\Mqtt\Contracts\Version;
use AlhajiAki\Mqtt\Exceptions\UnexpectedPacketException;

class Factory
{
    /**
     * @param Version $version
     * @param string $data
     * @throws UnexpectedPacketException
     * @return PacketEvent|void
     */
    public static function getNextPacket(Version $version, $data)
    {
        var_dump($data);
        while (isset($data[1])) {
            $remainingLength = ord($data[1]);
            $packetLength = 2 + $remainingLength;
            $nextPacketData = substr($data, 0, $packetLength);
            $data = substr($data, $packetLength);

            yield self::getByMessage($version, $nextPacketData);
        }
    }

    private static function getByMessage(Version $version, $input)
    {
        $packet = ord($input[0]);

        switch ($packet) {
            case PacketTypes::CONNACK:
                return ConnectAck::parse($version, $input);

            case PacketTypes::PINGRESP:
                return PingResponse::parse($version, $input);

                // case SubscribeAck::getControlPacketType():
                //     return SubscribeAck::parse($version, $input);

                // case Publish::getControlPacketType():
                //     return Publish::parse($version, $input);

                // case PublishComplete::getControlPacketType():
                //     return PublishComplete::parse($version, $input);

                // case PublishRelease::getControlPacketType():
                //     return PublishRelease::parse($version, $input);

                // case PublishReceived::getControlPacketType():
                //     return PublishReceived::parse($version, $input);
        }

        throw new UnexpectedPacketException($packet);
    }
}
