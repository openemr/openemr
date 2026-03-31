<?php

declare(strict_types=1);

namespace OpenEMR\BC\Crypto;

use OpenEMR\Encryption\{
    Cipher\Aes256CbcHmacSha384,
    Cipher\CipherInterface,
    Keys\Id,
    Keys\KeyMaterial,
    Keys\Storage,
    Message,
    Plaintext,
};
use RuntimeException;

class KeyV7Generator
{
    public static function generateDbKey(
        Storage\KeyStorageInterface $storage,
    ): CipherInterface {
        $dbKey = KeyMaterial::generate(Aes256CbcHmacSha384::KEY_LENGTH);
        $dbHmacKey = KeyMaterial::generate(32);
        // This doesn't locally check for existence since the interface
        // promises it won't ovewrite data.
        $storage->storeKey('sevena', $dbKey);
        $storage->storeKey('sevenb', $dbHmacKey);
        return new Aes256CbcHmacSha384(
            key: $dbKey,
            hmacKey: $dbHmacKey,
        );
    }

    public static function generateEncryptedDiskKey(
        CipherInterface $dbCipher,
        string $storageDir,
    ): CipherInterface {
        // Opening with 'x' will error if the file exists; this functions as
        // a cross-process lock for the create operation. These are done as
        // a pair at the start rather than inside of createDriveKey
        // individually to get an all-or-nothing operation.
        $fhKey = fopen("$storageDir/sevena", 'x');
        $fhHmac = fopen("$storageDir/sevenb", 'x');
        if ($fhKey === false || $fhHmac === false) {
            throw new RuntimeException('Could not fopen key file for creation.');
        }

        $driveKey = self::createDriveKey($fhKey, Aes256CbcHmacSha384::KEY_LENGTH, $dbCipher);
        $driveHmacKey = self::createDriveKey($fhHmac, 32, $dbCipher);

        return new Aes256CbcHmacSha384(
            key: $driveKey,
            hmacKey: $driveHmacKey,
        );
    }

    /**
     * @param resource $fh An open file handle to where the key will be written
     * @param int<1, max> $length
     */
    private static function createDriveKey(
        $fh,
        int $length,
        CipherInterface $cipher,
    ): KeyMaterial {
        $key = KeyMaterial::generate($length);
        $encKey = $cipher->encrypt(new Plaintext($key->key));
        $keyMessage = new Message(keyId: new Id('007'), ciphertext: $encKey);
        $result = fwrite($fh, $keyMessage->encode());
        if ($result === false) {
            throw new RuntimeException('Failed to write key');
        }
        return $key;
    }
}
