<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Encryption\{
    Cipher,
    Message,
};
use Throwable;

/**
 * Implements v1-v7 keys in a way that matches key names from BCCrypto
 *
 * @deprecated
 */
class BCKeychain
{
    public static function load(): KeychainInterface
    {
        $bag = OEGlobalsBag::getInstance();
        $storageDir = sprintf(
            '%s/documents/logs_and_misc/methods',
            $bag->getString('OE_SITE_DIR')
        );

        $pkod = new Storage\PlaintextKeyOnDisk($storageDir);
        $pkidb = new Storage\PlaintextKeyInDbKeysTableAdodb();

        $keychain = new Keychain();
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

        self::tryLoadDbKey('five', $pkidb, $storageDir, $keychain);
        self::tryLoadDbKey('six', $pkidb, $storageDir, $keychain);
        self::tryLoadDbKey('seven', $pkidb, $storageDir, $keychain);

        return $keychain;
    }

    private static function tryLoadKey(
        string $keyId,
        Storage\KeyStorageInterface $storage,
    ): ?KeyMaterial {
        try {
            return $storage->getKey($keyId);
        } catch (Throwable) {
            return null;
        }
    }

    private static function tryLoadDbKey(
        string $name,
        Storage\KeyStorageInterface $storage,
        string $storageDir,
        Keychain $keychain,
    ): void {
        $key = self::tryLoadKey("{$name}a", $storage);
        $hmacKey = self::tryLoadKey("{$name}b", $storage);
        if ($key === null || $hmacKey === null) {
            return;
        }
        $dbCipher = new Cipher\Aes256CbcHmacSha384(key: $key, hmacKey: $hmacKey);
        $keychain->addCipher("{$name}-db", $dbCipher);

        $diskKeyMsg = self::tryLoadEncryptedKey("$storageDir/{$name}a");
        $diskHmacKeyMsg = self::tryLoadEncryptedKey("$storageDir/{$name}b");
        if ($diskKeyMsg === null || $diskHmacKeyMsg === null) {
            return;
        }

        $diskKey = $dbCipher->decrypt($diskKeyMsg->ciphertext);
        $diskHmacKey = $dbCipher->decrypt($diskHmacKeyMsg->ciphertext);
        $keychain->addCipher("{$name}-disk", new Cipher\Aes256CbcHmacSha384(
            key: new KeyMaterial($diskKey->wrapped),
            hmacKey: new KeyMaterial($diskHmacKey->wrapped),
        ));
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
