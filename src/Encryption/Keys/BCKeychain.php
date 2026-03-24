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
                    key: new KeyMaterial($fiveadiskkey->wrapped),
                    hmacKey: new KeyMaterial($fivebdiskkey->wrapped),
                ));
            }
        }
        // TODO: repeat this for 6+7

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
