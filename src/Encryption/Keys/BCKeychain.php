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
    private static ?KeychainInterface $instance = null;

    public static function load(?string $createKeyIfNeeded): KeychainInterface
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        $bag = OEGlobalsBag::getInstance();
        $storageDir = sprintf(
            '%s/documents/logs_and_misc/methods',
            $bag->getString('OE_SITE_DIR')
        );

        $pkod = new Storage\PlaintextKeyOnDisk($storageDir);
        $pkidb = new Storage\PlaintextKeyInDbKeysTableAdodb();

        $keychain = new Keychain();
        // v1: broken crypto (no hmac)
        if ($one = self::tryLoadKey('one', $pkod)) {
            $keychain->addCipher('one', new Cipher\Aes256CbcNoHmac($one));
        }

        // v2-3: legacy crypto (256CBC-HS256) and storage
        if (($twoa = self::tryLoadKey('twoa', $pkod)) && ($twob = self::tryLoadKey('twob', $pkod))) {
            $keychain->addCipher('two', new Cipher\Aes256CbcHmacSha256(key: $twoa, hmacKey: $twob));
        }
        // No "three" key for historic reasons

        // v4: 256CBC-HS384 encryption, has drive+db but drive key is plaintext
        if (($foura = self::tryLoadKey('foura', $pkod)) && ($fourb = self::tryLoadKey('fourb', $pkod))) {
            $keychain->addCipher('four-drive', new Cipher\Aes256CbcHmacSha384(key: $foura, hmacKey: $fourb));
        }

        self::tryLoadDbKey('five', $pkidb, $storageDir, $keychain);
        self::tryLoadDbKey('six', $pkidb, $storageDir, $keychain);
        self::tryLoadDbKey('seven', $pkidb, $storageDir, $keychain);

        if ($createKeyIfNeeded !== null) {
            if (!$keychain->hasKey($createKeyIfNeeded)) {
                // Generate and store keys
                // TODO: actually persist them!!
                $key = KeyMaterial::generate(openssl_cipher_key_length('aes-256-cbc'));
                $hmacKey = KeyMaterial::generate(32);
                // FIXME: persist this data!
                $keychain->addCipher($createKeyIfNeeded, new Cipher\Aes256CbcHmacSha384(
                    key: $key,
                    hmacKey: $hmacKey,
                ));
                // if (!defined('PHPUNIT_COMPOSER_INSTALL')) {
                //     throw new \RuntimeException('Keys need persistence');
                // }
            }
        }

        self::$instance = $keychain;
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
        $keychain->addCipher("{$name}-drive", new Cipher\Aes256CbcHmacSha384(
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
