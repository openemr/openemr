<?php

declare(strict_types=1);

namespace OpenEMR\Encryption;

use OpenEMR\Common\Crypto\{
    CryptoInterface,
    KeySource,
};
use OpenEMR\Core\OEGlobalsBag;
use Throwable;

class BCCrypto implements CryptoInterface
{
    public function __construct(
        private Keys\KeychainInterface $keychain,
    ) {
    }

    // Singleton for BC?
    public static function instance(): BCCrypto
    {
        $bag = OEGlobalsBag::getInstance();
        $storageDir = sprintf(
            '%s/documents/logs_and_misc/methods',
            $bag->getString('OE_SITE_DIR')
        );

        $pkod = new Keys\Storage\PlaintextKeyOnDisk($storageDir);
        $pkidb = new Keys\Storage\PlaintextKeyInDbKeysTableAdodb();

        $keychain = new Keys\Keychain();
        if ($one = self::tryLoadKey('one', $pkod)) {
            $keychain->addCipher('one', new Cipher\Aes256CbcNoHmac($one));
        }
        if (($twoa = self::tryLoadKey('twoa', $pkod)) && ($twob = self::tryLoadKey('twob', $pkod))) {
            $keychain->addCipher('two', new Cipher\Aes256CbcHmacSha256(key: $twoa, hmacKey: $twob));
        }
        // No "three"
        if (($foura = self::tryLoadKey('foura', $pkod)) && ($fourb = self::tryLoadKey('fourb', $pkod))) {
            // 384 here?
            $keychain->addCipher('four', new Cipher\Aes256CbcHmacSha256(key: $foura, hmacKey: $fourb));
        }

        if (($fiveadb = self::tryLoadKey('fivea', $pkidb)) && ($fivebdb = self::tryLoadKey('fiveb', $pkidb))) {
            $fiveDb = new Cipher\Aes256CbcHmacSha384(key: $fiveadb, hmacKey: $fivebdb);
            $keychain->addCipher('five-db', $fiveDb);

            // If we get the DB keys, then try to load the encrypted disk ones.
            if (
                ($fiveadisk = self::tryLoadEncryptedKey("$storageDir/fivea"))
                && ($fivebdisk = self::tryLoadEncryptedKey("$storageDir/fiveb"))
            ) {
                $fiveadiskkey = $fiveDb->decrypt($fiveadisk->ciphertext);
                $fivebdiskkey = $fiveDb->decrypt($fivebdisk->ciphertext);
                $keychain->addCipher('five-disk', new Cipher\Aes256CbcHmacSha384(
                    key: new Keys\KeyMaterial($fiveadiskkey->wrapped),
                    hmacKey: new Keys\KeyMaterial($fivebdiskkey->wrapped),
                ));
            }
        }
        // TODO: repeat this for 6+7
        //
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

    private static function tryLoadKey(
        string $keyId,
        Keys\Storage\KeyStorageInterface $storage,
    ): ?Keys\KeyMaterial {
        try {
            return $storage->getKey($keyId);
        } catch (Throwable) {
            return null;
        }
    }

    private static function tryLoadEncryptedKey(string $file): ?Message
    {
        if (!file_exists($file)) {
            return null;
        }
        $data = file_get_contents($file);
        if ($data === false) {
            // should be unreachable normally, but file perms could do it.
            return null;
        }

        try {
            return Message::parse($data);
        } catch (Throwable) {
            return null;
        }
    }
}
