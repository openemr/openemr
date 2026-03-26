<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Encryption;

use UnexpectedValueException;

final readonly class Message
{
    public function __construct(
        public Keys\Id $keyId,
        public Ciphertext $ciphertext,
        public MessageFormat $format = MessageFormat::LATEST,
    ) {
    }

    public static function parse(string $encodedMessage): Message
    {
        if (strlen($encodedMessage) < 3) {
            throw new UnexpectedValueException('Message is missing expected prefix');
        }
        $format = MessageFormat::from(intval(substr($encodedMessage, 0, 3)));

        // Backwards compatibility: versions 1-7 coupled the data storage with
        // the key version.
        $keyId = match ($format) {
            MessageFormat::v1 => 'one',
            MessageFormat::v2, // Intentional: v3 uses key id 'two' for historic reasons
            MessageFormat::v3 => 'two',
            MessageFormat::v4 => 'four',
            MessageFormat::v5 => 'five',
            MessageFormat::v6 => 'six',
            MessageFormat::v7 => 'seven',
            // v8 will look at the message for more keyId info
        };

        $ciphertext = base64_decode(substr($encodedMessage, 3), strict: true);
        if ($ciphertext === false) {
            throw new UnexpectedValueException('Could not parse ciphertext');
        }

        return new Message(
            format: $format,
            keyId: new Keys\Id($keyId),
            ciphertext: new Ciphertext($ciphertext),
        );
    }

    public function encode(): string
    {
        $prefix = sprintf('%03d', $this->format->value);

        // if format encodes key id properly, add that in (new path?)

        return sprintf('%s%s', $prefix, base64_encode($this->ciphertext->wrapped));
    }
}
