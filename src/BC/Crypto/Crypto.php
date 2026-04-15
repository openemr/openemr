<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\BC\Crypto;

use BadMethodCallException;
use OpenEMR\Common\Crypto\{
    CryptoInterface,
    KeySource,
    KeyVersion,
};
use OpenEMR\Encryption\{
    KeyId,
    Keys\KeychainInterface,
    Message,
    MessageFormat,
    Plaintext,
};
use OutOfBoundsException;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * CryptoInterface implementation that uses the modern tooling in the
 * `OpenEMR\Encryption` namespace to manage keys and perform cryptographic
 * operations.
 *
 * CRITICALLY IMPORTANT: This relies on a KeychainInterface with remapped keys
 * for backwards compatibility. One is provided through `LegacyKeychainLoader`.
 * It will not be able to look up keys using the historic `00x` names alone.
 *
 * @deprecated
 */
final readonly class Crypto implements CryptoInterface
{
    public function __construct(
        private KeychainInterface $keychain,
        private LoggerInterface $logger,
    ) {
    }

    public static function instance(LoggerInterface $logger): Crypto
    {
        // Note: this is NOT a singleton otherwise newly-generated keys don't
        // get picked up properly.
        $keychain = LegacyKeychainLoader::load();
        return new Crypto($keychain, $logger);
    }

    public function encryptStandard(?string $value, KeySource $keySource = KeySource::Drive): string
    {
        if ($value === null || $value === '') {
            $this->logger->warning('Encrypting a null or empty value');
            return '';
        }

        // This ignores the keychain's specified preferred version for
        // backwards compatibility.
        $keyVersion = KeyVersion::CURRENT;

        $bcKey = Key::fromCryptoGen($keyVersion, $keySource);

        $keyId = $bcKey->getId();
        $cipher = $this->keychain->getCipher($keyId);

        // There's the option (once MessageFormat::ExplicitKey exists) to avoid
        // the key munging and still emit the new format.
        $wrapped = new Plaintext($value);
        $ciphertext = $cipher->encrypt($wrapped);
        return (new Message(
            keyId: new KeyId($keyVersion->toPaddedString()),
            ciphertext: $ciphertext,
            format: MessageFormat::ImplicitKey,
        ))->encode();
    }

    public function decryptStandard(?string $value, KeySource $keySource = KeySource::Drive, ?int $minimumVersion = null): false|string
    {
        if ($value === null || $value === '') {
            $this->logger->info('Decrypting a null or empty value');
            return '';
        }

        try {
            $message = Message::parse($value);

            if ($message->format !== MessageFormat::ImplicitKey) {
                throw new BadMethodCallException('Unhandled message type');
            }

            $keyVersion = KeyVersion::fromPrefix($value);

            if ($minimumVersion !== null && $keyVersion->value < $minimumVersion) {
                throw new OutOfBoundsException('Data is below minimum allowed version');
            }

            $bcKey = Key::fromCryptoGen($keyVersion, $keySource);
            $keyId = $bcKey->getId();

            $cipher = $this->keychain->getCipher($keyId);

            return $cipher->decrypt($message->ciphertext)->bytes;
        } catch (Throwable $e) {
            $this->logger->warning('Decrypting data failed', ['exception' => $e]);
            return false;
        }
    }

    public function cryptCheckStandard(?string $value): bool
    {
        if ($value === null) {
            return false;
        }
        try {
            Message::parse($value);
            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
