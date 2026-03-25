<?php

declare(strict_types=1);

namespace OpenEMR\Encryption;

use OpenEMR\Common\Crypto\{
    CryptoInterface,
    KeySource,
};
use Psr\Log\LoggerInterface;

class BCCrypto implements CryptoInterface
{
    public function __construct(
        private Keys\KeychainInterface $keychain,
        private LoggerInterface $logger,
        private string $currentKeyId = 'seven',
        private MessageFormat $format = MessageFormat::v7,
    ) {
    }

    // Singleton for BC?
    public static function instance(LoggerInterface $logger): BCCrypto
    {
        $keychain = Keys\BCKeychain::load(createKeyIfNeeded: 'seven');
        $logger->warning("BCC instance");
        $logger->warning(print_r($keychain, true));
        return new BCCrypto($keychain, $logger);
    }

    public function encryptStandard(?string $value, KeySource $keySource = KeySource::Drive): string
    {
        $keyId = self::remapKeyId($this->currentKeyId, $keySource);
        $cipher = $this->keychain->getCipher($keyId);

        $wrapped = new Plaintext($value);
        return $cipher->encrypt($wrapped);

        // $message = new Message(current format, key id, ciphertext)
        // return $message->encode();
        throw new \BadMethodCallException();
    }

    public function decryptStandard(?string $value, KeySource $keySource = KeySource::Drive, ?int $minimumVersion = null): false|string
    {
        if ($value === null) {
            // warn?
            return '';
        }

        try {
            $message = Message::parse($value);

            // BC hack: remap the key id by namebased on the source
            $keyId = self::remapKeyId($message->keyId, $keySource);

            $cipher = $this->keychain->getCipher($keyId);

            return $cipher->decrypt($message->ciphertext)->wrapped;
        } catch (\Throwable $e) {
            // log me
            return false;
        }
    }

    private static function remapKeyId(string $id, KeySource $source): string
    {
        // General BC concept: key versions 5-7 for disk-backed keys were
        // encrypted-on-disk using a db-managed key of the same name.
        return match ($id) {
            'five',
            'six',
            'seven' => match ($source) {
                KeySource::Drive => "$id-drive",
                KeySource::Database => "$id-db",
            },
            default => $id,
        };
        // Versions 1-4 always used a drive key regardless of specification
        // v8 will embed the key id in the message properly; TBD on Source
    }

    public function cryptCheckStandard(?string $value): bool
    {
        throw new \BadMethodCallException();
    }
}
