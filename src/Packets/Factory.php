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

            case PacketTypes::PUBLISH:
                return Publish::parse($version, $input);

            case PacketTypes::PUBACK:
                return PublishAck::parse($version, $input);

            case PacketTypes::PUBREC:
                return PublishReceived::parse($version, $input);

            case PacketTypes::PUBREL:
                return PublishRelease::parse($version, $input);

            case PacketTypes::PUBCOMP:
                return PublishComplete::parse($version, $input);

                // case SubscribeAck::getControlPacketType():
                //     return SubscribeAck::parse($version, $input);


        }

        var_dump('error ' . $packet);
        throw new UnexpectedPacketException($packet);
    }
}
