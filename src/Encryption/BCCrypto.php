<?php

declare(strict_types=1);

namespace OpenEMR\Encryption;

use OpenEMR\Common\Crypto\{
    CryptoInterface,
    KeySource,
};

class BCCrypto implements CryptoInterface
{
    public function __construct(
        private Keychain $keychain,
    ) {
    }

    // Singleton for BC?
    public static function instance(): BCCrypto
    {
        $keychain = new Keychain();
        $keychain->addKey('one', Cipher\Id::Aes256CbcNoHmac, Keys\Storage\Id::PlaintextDisk);
        $keychain->addKey('twoa', Cipher\Id::Aes256CbcHmacSha256, Keys\Storage\Id::PlaintextDisk);
        $keychain->addKey('twob', Cipher\Id::Aes256CbcHmacSha256, Keys\Storage\Id::PlaintextDisk);
        // three{a|b} does not exist for historic reasons
        $keychain->addKey('foura', Cipher\Id::Aes256CbcHmacSha256, Keys\Storage\Id::PlaintextDisk);
        $keychain->addKey('fourb', Cipher\Id::Aes256CbcHmacSha256, Keys\Storage\Id::PlaintextDisk);
        // 5-7 depend on keysource but we can rewrap it internally I think

        // fivea-disk (encr.)
        // fiveb-disk
        // fivea-db (plaintext)
        // fiveb-db

        // sinleton-ify
        return new BCCrypto($keychain);
    }

    public function encryptStandard(?string $value, KeySource $keySource = KeySource::Drive): string
    {
        // $message = new Message(current format, key id, cyphertext)
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



            // something translates keyId into CipherInterface
            $cipher = $this->keychain->getCipher($message->keyId);

            return $cipher->decode($message->ciphertext);
        } catch (\Throwable $e) {
            // log me
            return false;
        }
    }

    public function cryptCheckStandard(?string $value): bool
    {
        throw new \BadMethodCallException();
    }
}
