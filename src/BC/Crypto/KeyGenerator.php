<?php

declare(strict_types=1);

namespace OpenEMR\BC\Crypto;

use OpenEMR\Encryption\{
    Cipher,
    Keys\KeyMaterial,
    Keys\KeychainInterface,
    Keys\Storage,
};

class KeyGenerator
{
    public static function generateDbKey(
        Storage\KeyStorageInterface $storage,
    ): Cipher\CipherInterface
    {
        $dbKey = KeyMaterial::generate(Cipher\Aes256CbcHmacSha384::KEY_LENGTH);
        $dbHmacKey = KeyMaterial::generate(32);
        $storage->storeKey('sevena', $dbKey);
        $storage->storeKey('sevenb', $dbHmacKey);
        $dbCipher =  new Cipher\Aes256CbcHmacSha384(
            key: $dbKey,
            hmacKey: $dbHmacKey,
        );
        return $dbCipher;
        // $keychain->addCipher(Key::v7Db->getId(), $dbCipher);
    }
}
