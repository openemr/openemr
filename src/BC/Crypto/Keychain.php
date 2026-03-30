<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\BC\Crypto;

use OpenEMR\Common\Crypto\KeyVersion;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Encryption\{
    Cipher,
    Keys\Id,
    Keys\Keychain as EagerKeychain,
    Keys\KeychainInterface,
    Keys\KeyMaterial,
    Keys\Storage,
    Message,
    MessageFormat,
    Plaintext,
};
use Throwable;

/**
 * Implements v1-v7 keys in a way that matches key names from Crypto
 *
 * @deprecated
 */
class Keychain
{
    public static function load(KeyVersion $createKeyIfNeeded): KeychainInterface
    {
        $bag = OEGlobalsBag::getInstance();
        $storageDir = sprintf(
            '%s/documents/logs_and_misc/methods',
            $bag->getString('OE_SITE_DIR')
        );

        $pkod = new Storage\PlaintextKeyOnDisk($storageDir);
        $pkidb = new Storage\PlaintextKeyInDbKeysTableAdodb();

        $keychain = new EagerKeychain();
        // v1: broken crypto (no hmac)
        if ($one = self::tryLoadKey('one', $pkod)) {
            $keychain->addCipher(Key::v1->getId(), new Cipher\Aes256CbcNoHmac($one));
        }

        // v2-3: legacy crypto (256CBC-HS256) and storage
        if (($twoa = self::tryLoadKey('twoa', $pkod)) && ($twob = self::tryLoadKey('twob', $pkod))) {
            $keychain->addCipher(Key::v2->getId(), new Cipher\Aes256CbcHmacSha256(key: $twoa, hmacKey: $twob));
        }
        // No "three" key for historic reasons

        // v4: 256CBC-HS384 encryption, has drive+db but drive key is plaintext
        if (($foura = self::tryLoadKey('foura', $pkod)) && ($fourb = self::tryLoadKey('fourb', $pkod))) {
            $keychain->addCipher(Key::v4Drive->getId(), new Cipher\Aes256CbcHmacSha384(key: $foura, hmacKey: $fourb));
        }
        if (($fouraDB = self::tryLoadKey('foura', $pkidb)) && ($fourbDB = self::tryLoadKey('fourb', $pkidb))) {
            $keychain->addCipher(Key::v4Db->getId(), new Cipher\Aes256CbcHmacSha384(key: $fouraDB, hmacKey: $fourbDB));
        }

        // FIXME: apply the Key enum in here somehow
        self::tryLoadDbKey('five', $pkidb, $storageDir, $keychain);
        self::tryLoadDbKey('six', $pkidb, $storageDir, $keychain);
        self::tryLoadDbKey('seven', $pkidb, $storageDir, $keychain);

        // CryptoGen had a create-keys-on-first-use logic, recreate for now.
        assert($createKeyIfNeeded === KeyVersion::SEVEN);
        // TODO: extract this out into a reusable service
        // DB Key
        if (!$keychain->hasKey(Key::v7Db->getId())) {
            $dbCipher = KeyGenerator::generateDbKey($pkidb);
            $keychain->addCipher(Key::v7Db->getId(), $dbCipher);
        }

        // Drive key (encrypted)
        if (!$keychain->hasKey(Key::v7Drive->getId())) {
            if (!isset($dbCipher)) {
                $dbCipher = $keychain->getCipher(Key::v7Db->getId());
            }
            $driveCipher = KeyGenerator::generateEncryptedDiskKey(
                dbCipher: $dbCipher,
                storageDir: $storageDir,
            );

            $keychain->addCipher(Key::v7Drive->getId(), $driveCipher);
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
        EagerKeychain $keychain,
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
