<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Encryption;

use BadMethodCallException;
use UnexpectedValueException;

final readonly class Message
{
    public function __construct(
        public KeyId $keyId,
        public Ciphertext $ciphertext,
        public MessageFormat $format = MessageFormat::LATEST,
    ) {
        if ($this->format === MessageFormat::ImplicitKey){
            if (!preg_match('/^00[1-7]$/', $this->keyId->id)) {
                throw new BadMethodCallException('Only legacy key versions can use ImplicitKey format');
            }
        }
    }

    public static function parse(string $encodedMessage): Message
    {
        $format = MessageFormat::detect($encodedMessage);
        return match ($format) {
            MessageFormat::ImplicitKey => self::parseImplicitKey($encodedMessage),
            // @codeCoverageIgnoreStart
            MessageFormat::UnusedCaseToSupportConditionals => throw new BadMethodCallException('Unhandled message type'),
            // @codeCoverageIgnoreEnd
        };
    }

    /**
     * The messages in Implicit format MUST have their keys remapped to the new
     * Keychain-based ids prior to use. It's the responsibility of the
     * interacting service to handle this.
     *
     * This is unavoidable: the legacy CryptoInterface allows specifying
     * a KeySource and that information is both needed to know which key to use
     * and not available in the message.
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
            keyId: new KeyId($numericKeyId),
            ciphertext: new Ciphertext($ciphertext),
        );
    }

    public function encode(): string
    {
        return match ($this->format) {
            MessageFormat::ImplicitKey => $this->encodeImplicitKey(),
            // @codeCoverageIgnoreStart
            MessageFormat::UnusedCaseToSupportConditionals => throw new BadMethodCallException('Unhandled message type'),
            // @codeCoverageIgnoreEnd
        };
    }

    private function encodeImplicitKey(): string
    {
        assert($this->format === MessageFormat::ImplicitKey);
        return sprintf('%s%s',
            $this->keyId->id,
            base64_encode($this->ciphertext->value),
        );
    }
}
