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

use BadMethodCallException;
use OpenEMR\Common\Crypto\KeyVersion;
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
            MessageFormat::ImplicitKey => self::parseImplicitKey($encodedMessage),
            MessageFormat::UnusedCaseToSupportConditionals => throw new BadMethodCallException('Unhandled message type'),
        };
    }

    /**
     * The messages in Implicit format MUST have their keys remapped through
     * BC\Crypto; they will not work with the keyring directly since there's not
     * enough context in the message alone to decrypt (it also needs KeySource)
     */
    private static function parseImplicitKey(string $encodedMessage): Message
    {
        assert(strlen($encodedMessage) >= 3);

        // `001`-`007`
        $numericKeyId = substr($encodedMessage, 0, 3);

        $ciphertext = base64_decode(substr($encodedMessage, 3), strict: true);
        if ($ciphertext === false) {
            throw new UnexpectedValueException('Could not parse ciphertext');
        }

        return new Message(
            format: MessageFormat::ImplicitKey,
            keyId: new Keys\Id($numericKeyId),
            ciphertext: new Ciphertext($ciphertext),
        );
    }

    public function encode(): string
    {
        return match ($this->format) {
            MessageFormat::ImplicitKey => $this->encodeImplicitKey(),
            MessageFormat::UnusedCaseToSupportConditionals => throw new BadMethodCallException('Unhandled message type'),
        };
    }

    private function encodeImplicitKey(): string
    {
        assert($this->format === MessageFormat::ImplicitKey);
        return sprintf('%s%s',
            $this->keyId->id,
            base64_encode($this->ciphertext->wrapped),
        );
    }
}
