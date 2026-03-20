<?php

declare(strict_types=1);

namespace OpenEMR\Encryption;

use UnexpectedValueException;

class Message
{
    public function __construct(
        public Format $format,
        public string $keyId,
        public string $ciphertext,
    ) {
    }

    public static function parse(string $encodedMessage): Message
    {
        if (strlen($encodedMessage) < 3) {
            throw new UnexpectedValueException('Message is missing expected prefix');
        }
        $format = Format::from(intval(substr($encodedMessage, 0, 3)));

        $keyId = match ($format) {
            Format::v1 => 'one',
            Format::v2, // Intentional: v3 uses key id 'two' for historic reasons
            Format::v3 => 'two',
            Format::v4 => 'four',
            Format::v5 => 'five',
            Format::v6 => 'six',
            Format::v7 => 'seven',
            // v8 will look at the message for more keyId info
        };

        $ciphertext = base64_decode(substr($encodedMessage, 3), strict: true);
        if ($ciphertext === false) {
            throw new UnexpectedValueException('Could not parse ciphertext');
        }

        return new Message($format, $keyId, $ciphertext);
    }

    public function encode(): string
    {
        $prefix = sprintf('%03d', $this->format->value);

        // if format encodes key id properly, add that in (new path?)

        return sprintf('%s%s', $prefix, base64_encode($this->ciphertext));
    }
}
