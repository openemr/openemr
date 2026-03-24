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

        // TODO: add these
        // fivea-disk (encr.)
        // fiveb-disk
        // fivea-db (plaintext)
        // fiveb-db
        // six+seven

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
            'fivea',
            'fiveb',
            'sixa',
            'sixb',
            'sevena',
            'sevenb' => match ($source) {
                KeySource::Drive => "$id-drive",
                KeySource::Database => "$id-db",
            },
            default => $id,
        };
    }

    public function cryptCheckStandard(?string $value): bool
    {
        throw new \BadMethodCallException();
    }
}
