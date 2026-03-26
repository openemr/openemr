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

use DomainException;
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
        $format = MessageFormat::detect($encodedMessage);

        return match ($format) {
            MessageFormat::ExplicitKey => self::parseExplicitKey($encodedMessage),
            MessageFormat::ImplicitKey => self::parseImplicitKey($encodedMessage),
        };
    }

    private static function parseExplicitKey(string $encodedMessage): Message
    {
        $parts = explode(':', $encodedMessage);
        if (count($parts) !== 3) {
            throw new UnexpectedValueException('Malformed message');
        }
        [$formatId, $keyId, $ciphertext] = $parts;
        return new Message(
            keyId: new Keys\Id($keyId),
            ciphertext: new Ciphertext(base64_decode($ciphertext)),
            format: MessageFormat::ExplicitKey,
        );
    }

    // Backwards compatibility: versions 1-7 coupled the data storage with
    // the key version.
    private static function parseImplicitKey(string $encodedMessage): Message
    {
        assert(strlen($encodedMessage) >= 3);
        $keyNumber = substr($encodedMessage, 0, 3);
        $keyId = match ($keyNumber) {
            '001' => 'one',
            '002', '003' => 'two', // Intentional: v3 uses key id 'two' for historic reasons
            '004' => 'four',
            '005' => 'five',
            '006' => 'six',
            '007' => 'seven',
            default => throw new DomainException('Invalid prefix in implicit key parsing'),
        };

        $ciphertext = base64_decode(substr($encodedMessage, 3), strict: true);
        if ($ciphertext === false) {
            throw new UnexpectedValueException('Could not parse ciphertext');
        }

        return new Message(
            format: MessageFormat::ImplicitKey,
            keyId: new Keys\Id($keyId),
            ciphertext: new Ciphertext($ciphertext),
        );
    }

    public function encode(): string
    {
        return match ($this->format) {
            MessageFormat::ImplicitKey =>  $this->encodeImplicit(),
            MessageFormat::ExplicitKey => $this->encodeExplicit(),
        };
    }

    private function encodeImplicit(): string
    {
        // Future guard: once >1 MessageFormat, switch encoder
        $prefix = match ($this->keyId->id) {
            'one' => '001',
            'two' => '002',
            'four' => '004',
            'five' => '005',
            'six' => '006',
            'seven' => '007',
            // default => throw 
        };
        // $prefix = sprintf('%03d', $this->format->value);

        // if format encodes key id properly, add that in (new path?)

        return sprintf('%s%s', $prefix, base64_encode($this->ciphertext->wrapped));
    }

    private function encodeExplicit(): string
    {
        return sprintf(
            '%s:%s:%s',
            '008',
            $this->keyId->id,
            base64_encode($this->ciphertext->wrapped),
        );
    }
}
