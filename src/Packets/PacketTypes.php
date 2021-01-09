<?php

namespace AlhajiAki\Mqtt\Packets;

class PacketTypes
{
    const CONNECT     = 0x10; // Client request to connect to Server
    const CONNACK     = 0x20; // Connect acknowledgment
    const PUBLISH     = 0x30; // Publish message
    const PUBACK      = 0x40; // Publish acknowledgment
    const PUBREC      = 0x50; // Publish received (assured delivery part 1)
    const PUBREL      = 0x62; // Publish release (assured delivery part 2)
    const PUBCOMP     = 0x70; // Publish complete (assured delivery part 3)
    const SUBSCRIBE   = 0x80; // Client subscribe request
    const SUBACK      = 0x90; // Subscribe acknowledgment
    const UNSUBSCRIBE = 0xa0; // Unsubscribe request
    const UNSUBACK    = 0xb0; // Unsubscribe acknowledgment
    const PINGREQ     = 0xc0; // PING request
    const PINGRESP    = 0xd0; // PING response
    const DISCONNECT  = 0xe0; // Client is disconnecting

    const MOST_SIGNIFICANT_BYTE = 0x00;
}
