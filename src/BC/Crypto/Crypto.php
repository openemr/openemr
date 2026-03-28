<?php

declare(strict_types=1);

namespace OpenEMR\BC\Crypto;

use BadMethodCallException;
use OpenEMR\Common\Crypto\{
    CryptoInterface,
    KeySource,
    KeyVersion,
};
use OpenEMR\Encryption\Keys\Id;
use OpenEMR\Encryption\Keys\KeychainInterface;
use OpenEMR\Encryption\Message;
use OpenEMR\Encryption\MessageFormat;
use OpenEMR\Encryption\Plaintext;
use Psr\Log\LoggerInterface;

/**
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
        $keychain = Keychain::load(createKeyIfNeeded: KeyVersion::CURRENT->toString());
        return new Crypto($keychain, $logger);
    }

    public function encryptStandard(?string $value, KeySource $keySource = KeySource::Drive): string
    {
        if ($value === null || $value === '') {
            // Should this warn?
            return '';
        }

        $keyVersion = KeyVersion::CURRENT;

        $bcKey = Key::fromCryptoGen($keyVersion, $keySource);

        $keyId = $bcKey->getId();
        $cipher = $this->keychain->getCipher($keyId);

        // There's the option (once MessageFormat::ExplicitKey exists) to avoid
        // the key munging and still emit the new format.
        $wrapped = new Plaintext($value);
        $ciphertext = $cipher->encrypt($wrapped);
        return (new Message(
            keyId: new Id($keyVersion->toPaddedString()),
            ciphertext: $ciphertext,
            format: MessageFormat::ImplicitKey,
        ))->encode();
    }

    public function decryptStandard(?string $value, KeySource $keySource = KeySource::Drive, ?int $minimumVersion = null): false|string
    {
        if ($value === null || $value === '') {
            // Should this warn?
            return '';
        }

        try {
            $message = Message::parse($value);

            if ($message->format !== MessageFormat::ImplicitKey) {
                throw new BadMethodCallException('Unhandled message type');
            }

            $keyVersion = KeyVersion::fromPrefix($value);

            if ($minimumVersion !== null && $keyVersion->value < $minimumVersion) {
                throw new \Exception('Data is below minimum allowed version');
            }

            $bcKey = Key::fromCryptoGen($keyVersion, $keySource);

            // Delegate back to the "real" version? This is duplicative
            $keyId = $bcKey->getId();

            $cipher = $this->keychain->getCipher($keyId);

            return $cipher->decrypt($message->ciphertext)->wrapped;
        } catch (\Throwable $e) {
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
        } catch (\Throwable) {
            return false;
        }
    }
}
