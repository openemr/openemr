<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\BC\Crypto;

use OpenEMR\Common\Crypto\{
    KeySource,
    KeyVersion,
};
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Encryption\{
    Cipher,
    Keys\Keychain as EagerKeychain,
    Keys\KeychainInterface,
    Keys\KeyMaterial,
    Message,
    MessageFormat,
    Plaintext,
    Storage,
};
use Throwable;

/**
 * Loads v1-v7 keys from legacy storage locations and maps them to the new
 * KeychainInterface.
 *
 * CRITICALLY IMPORTANT: This loader registers keys under names defined by the
 * `Key` enum (e.g., 'seven-drive'), NOT the numeric prefixes used by CryptoGen
 * (e.g., '007'). Use only with `Crypto` in this namespace.
 *
 * @deprecated
 */
final class LegacyKeychainLoader
{
    public static function load(): KeychainInterface
    {
        $bag = OEGlobalsBag::getInstance();
        $storageDir = sprintf(
            '%s/documents/logs_and_misc/methods',
            $bag->getString('OE_SITE_DIR')
        );

        $pkod = new Storage\PlaintextKeyOnDisk($storageDir);
        $pkidb = new Storage\PlaintextKeyInDbKeysTableQueryUtils();

        $keychain = new EagerKeychain();
        // v1: broken crypto (no hmac)
        $one = self::tryLoadKey(new Storage\KeyMaterialId('one'), $pkod);
        if ($one !== null) {
            $keychain->registerCipher(Key::v1->getId(), new Cipher\Aes256CbcNoHmac($one));
        }

        // v2-3: legacy crypto (256CBC-HS256) and storage
        $twoa = self::tryLoadKey(new Storage\KeyMaterialId('twoa'), $pkod);
        $twob = self::tryLoadKey(new Storage\KeyMaterialId('twob'), $pkod);
        if ($twoa !== null && $twob !== null) {
            $keychain->registerCipher(Key::v2->getId(), new Cipher\Aes256CbcHmacSha256(key: $twoa, hmacKey: $twob));
        }
        // No "three" key for historic reasons

        // v4: 256CBC-HS384 encryption, has drive+db but drive key is plaintext
        $foura = self::tryLoadKey(new Storage\KeyMaterialId('foura'), $pkod);
        $fourb = self::tryLoadKey(new Storage\KeyMaterialId('fourb'), $pkod);
        if ($foura !== null && $fourb !== null) {
            $keychain->registerCipher(Key::v4Drive->getId(), new Cipher\Aes256CbcHmacSha384(key: $foura, hmacKey: $fourb));
        }
        $fouraDB = self::tryLoadKey(new Storage\KeyMaterialId('foura'), $pkidb);
        $fourbDB = self::tryLoadKey(new Storage\KeyMaterialId('fourb'), $pkidb);
        if ($fouraDB !== null && $fourbDB !== null) {
            $keychain->registerCipher(Key::v4Db->getId(), new Cipher\Aes256CbcHmacSha384(key: $fouraDB, hmacKey: $fourbDB));
        }

        self::tryLoadDbKey(new Storage\KeyMaterialId('five'), $pkidb, $storageDir, $keychain);
        self::tryLoadDbKey(new Storage\KeyMaterialId('six'), $pkidb, $storageDir, $keychain);
        self::tryLoadDbKey(new Storage\KeyMaterialId('seven'), $pkidb, $storageDir, $keychain);

        // CryptoGen had a create-keys-on-first-use logic, recreate for now.
        // DB Key
        if (!$keychain->hasKey(Key::v7Db->getId())) {
            $dbCipher = KeyV7Generator::generateDbKey($pkidb);
            $keychain->registerCipher(Key::v7Db->getId(), $dbCipher);
        }

        // Drive key (encrypted)
        if (!$keychain->hasKey(Key::v7Drive->getId())) {
            if (!isset($dbCipher)) {
                $dbCipher = $keychain->getCipher(Key::v7Db->getId());
            }
            $driveCipher = KeyV7Generator::generateEncryptedDiskKey(
                dbCipher: $dbCipher,
                storageDir: $storageDir,
            );

            $keychain->registerCipher(Key::v7Drive->getId(), $driveCipher);
        }

        return $keychain;
    }

    private static function tryLoadKey(
        Storage\KeyMaterialId $keyId,
        Storage\KeyStorageInterface $storage,
    ): ?KeyMaterial {
        try {
            return $storage->getKey($keyId);
        } catch (Throwable) {
            return null;
        }
    }

    private static function tryLoadDbKey(
        Storage\KeyMaterialId $name,
        Storage\KeyStorageInterface $storage,
        string $storageDir,
        EagerKeychain $keychain,
    ): void {
        // Checking KeyVersion early ensures this only runs on legacy keys
        $version = KeyVersion::fromString($name->id);
        $key = self::tryLoadKey(new Storage\KeyMaterialId("{$name->id}a"), $storage);
        $hmacKey = self::tryLoadKey(new Storage\KeyMaterialId("{$name->id}b"), $storage);
        if ($key === null || $hmacKey === null) {
            return;
        }
        $dbCipher = new Cipher\Aes256CbcHmacSha384(key: $key, hmacKey: $hmacKey);
        $keychain->registerCipher(
            Key::fromCryptoGen($version, KeySource::Database)->getId(),
            $dbCipher,
        );

        $diskKeyMsg = self::tryLoadEncryptedKey("$storageDir/{$name->id}a");
        $diskHmacKeyMsg = self::tryLoadEncryptedKey("$storageDir/{$name->id}b");
        if ($diskKeyMsg === null || $diskHmacKeyMsg === null) {
            return;
        }

        $diskKey = $dbCipher->decrypt($diskKeyMsg->ciphertext);
        $diskHmacKey = $dbCipher->decrypt($diskHmacKeyMsg->ciphertext);
        $keychain->registerCipher(
            Key::fromCryptoGen($version, KeySource::Drive)->getId(),
            new Cipher\Aes256CbcHmacSha384(
                key: new KeyMaterial($diskKey->bytes),
                hmacKey: new KeyMaterial($diskHmacKey->bytes),
            ),
        );
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
