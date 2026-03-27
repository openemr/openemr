<?php

declare(strict_types=1);

namespace OpenEMR\Encryption;

use OpenEMR\Common\Crypto\{
    CryptoInterface,
    KeySource,
    KeyVersion,
};
use Psr\Log\LoggerInterface;

final readonly class BCCrypto implements CryptoInterface
{
    public function __construct(
        private Keys\KeychainInterface $keychain,
        private LoggerInterface $logger,
        private Keys\Id $currentKeyId,
    ) {
    }

    public static function instance(LoggerInterface $logger): BCCrypto
    {
        // Note: this is NOT a singleton otherwise newly-generated keys don't
        // get picked up properly.
        // @phpstan-ignore staticMethod.deprecatedClass
        $keychain = Keys\BCKeychain::load(createKeyIfNeeded: KeyVersion::CURRENT->toString());
        return new BCCrypto(
            $keychain,
            $logger,
            new Keys\Id(KeyVersion::CURRENT->toString()),
        );
    }

    public function encryptStandard(?string $value, KeySource $keySource = KeySource::Drive): string
    {
        if ($value === null || $value === '') {
            // Should this warn?
            return '';
        }
        $keyId = self::remapKeyId($this->currentKeyId, $keySource);
        $cipher = $this->keychain->getCipher($keyId);

        $wrapped = new Plaintext($value);
        $ciphertext = $cipher->encrypt($wrapped);
        return (new Message(
            keyId: $this->currentKeyId,
            ciphertext: $ciphertext,
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
            if ($minimumVersion !== null && $message->format->value < $minimumVersion) {
                throw new \Exception('Data is below minimum allowed version');
            }

            // BC hack: remap the key id by namebased on the source
            $keyId = self::remapKeyId($message->keyId, $keySource);

            $cipher = $this->keychain->getCipher($keyId);

            return $cipher->decrypt($message->ciphertext)->wrapped;
        } catch (\Throwable $e) {
            $this->logger->warning('Decrypting data failed', ['exception' => $e]);
            return false;
        }
    }

    private static function remapKeyId(Keys\Id $id, KeySource $source): Keys\Id
    {
        // General BC concept:
        // Versions 1-3 always used a drive key regardless of specification
        // Version 4 used the specified key type, but the drive key was plaintext
        // Versions 5-7 for disk-backed keys were encrypted-on-disk using a
        //   db-managed key of the same name.
        // v8 will embed the key id in the message properly; TBD on Source
        //   (it may be ignored)
        return match ($id->id) {
            'four',
            'five',
            'six',
            'seven' => match ($source) {
                KeySource::Drive => new Keys\Id("{$id->id}-drive"),
                KeySource::Database => new Keys\Id("{$id->id}-db"),
            },
            default => $id,
        };
    }

    public function cryptCheckStandard(?string $value): bool
    {
        try {
            Message::parse($value);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
