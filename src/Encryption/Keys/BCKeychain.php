<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Encryption\{
    Cipher,
    Message,
    MessageFormat,
    Plaintext,
};
use Throwable;

/**
 * Implements v1-v7 keys in a way that matches key names from BCCrypto
 *
 * @deprecated
 */
class BCKeychain
{
    public static function load(?string $createKeyIfNeeded): KeychainInterface
    {
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
            $keychain->addCipher(new Id('one'), new Cipher\Aes256CbcNoHmac($one));
        }

        // v2-3: legacy crypto (256CBC-HS256) and storage
        if (($twoa = self::tryLoadKey('twoa', $pkod)) && ($twob = self::tryLoadKey('twob', $pkod))) {
            $keychain->addCipher(new Id('two'), new Cipher\Aes256CbcHmacSha256(key: $twoa, hmacKey: $twob));
        }
        // No "three" key for historic reasons

        // v4: 256CBC-HS384 encryption, has drive+db but drive key is plaintext
        if (($foura = self::tryLoadKey('foura', $pkod)) && ($fourb = self::tryLoadKey('fourb', $pkod))) {
            $keychain->addCipher(new Id('four-drive'), new Cipher\Aes256CbcHmacSha384(key: $foura, hmacKey: $fourb));
        }
        if (($fouraDB = self::tryLoadKey('foura', $pkidb)) && ($fourbDB = self::tryLoadKey('fourb', $pkidb))) {
            $keychain->addCipher(new Id('four-db'), new Cipher\Aes256CbcHmacSha384(key: $fouraDB, hmacKey: $fourbDB));
        }

        self::tryLoadDbKey('five', $pkidb, $storageDir, $keychain);
        self::tryLoadDbKey('six', $pkidb, $storageDir, $keychain);
        self::tryLoadDbKey('seven', $pkidb, $storageDir, $keychain);

        if ($createKeyIfNeeded !== null) {
            // TODO: support others
            assert($createKeyIfNeeded === 'seven');
            // TODO: extract this out into a reusable service
            // DB Key
            if (!$keychain->hasKey(new Id('seven-db'))) {
                $dbKey = KeyMaterial::generate(Cipher\Aes256CbcHmacSha384::KEY_LENGTH);
                $dbHmacKey = KeyMaterial::generate(32);
                $pkidb->storeKey('sevena', $dbKey);
                $pkidb->storeKey('sevenb', $dbHmacKey);
                $dbCipher =  new Cipher\Aes256CbcHmacSha384(
                    key: $dbKey,
                    hmacKey: $dbHmacKey,
                );
                $keychain->addCipher(new Id('seven-db'), $dbCipher);
            }

            // Drive key (encrypted)
            if (!$keychain->hasKey(new Id('seven-drive'))) {
                if (!isset($dbCipher)) {
                    $dbCipher = $keychain->getCipher(new Id('seven-db'));
                }
                $driveKey = KeyMaterial::generate(Cipher\Aes256CbcHmacSha384::KEY_LENGTH);
                $driveHmacKey = KeyMaterial::generate(32);

                $encDriveKey = $dbCipher->encrypt(new Plaintext($driveKey->key));
                $encDriveHmacKey = $dbCipher->encrypt(new Plaintext($driveHmacKey->key));

                $driveKeyMessage = new Message(MessageFormat::v7, new Id('sevena'), $encDriveKey);
                $driveHmacKeyMessage = new Message(MessageFormat::v7, new Id('sevenb'), $encDriveHmacKey);

                file_put_contents("$storageDir/sevena", $driveKeyMessage->encode());
                file_put_contents("$storageDir/sevenb", $driveHmacKeyMessage->encode());

                $keychain->addCipher(new Id('seven-drive'), new Cipher\Aes256CbcHmacSha384(
                    key: $driveKey,
                    hmacKey: $driveHmacKey,
                ));
            }
        }

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
        $keychain->addCipher(new Id("{$name}-db"), $dbCipher);

        $diskKeyMsg = self::tryLoadEncryptedKey("$storageDir/{$name}a");
        $diskHmacKeyMsg = self::tryLoadEncryptedKey("$storageDir/{$name}b");
        if ($diskKeyMsg === null || $diskHmacKeyMsg === null) {
            return;
        }

        $diskKey = $dbCipher->decrypt($diskKeyMsg->ciphertext);
        $diskHmacKey = $dbCipher->decrypt($diskHmacKeyMsg->ciphertext);
        $keychain->addCipher(new Id("{$name}-drive"), new Cipher\Aes256CbcHmacSha384(
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
