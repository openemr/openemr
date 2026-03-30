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

class KeyV7Generator
{
    public static function generateDbKey(
        Storage\KeyStorageInterface $storage,
    ): CipherInterface {
        $dbKey = KeyMaterial::generate(Aes256CbcHmacSha384::KEY_LENGTH);
        $dbHmacKey = KeyMaterial::generate(32);
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
        $driveKey = KeyMaterial::generate(Aes256CbcHmacSha384::KEY_LENGTH);
        $driveHmacKey = KeyMaterial::generate(32);

        $encDriveKey = $dbCipher->encrypt(new Plaintext($driveKey->key));
        $encDriveHmacKey = $dbCipher->encrypt(new Plaintext($driveHmacKey->key));

        $driveKeyMessage = new Message(
            keyId: new Id('007'), // $createKeyIfNeeded->toPaddedString(), but don't trust the assertion
            ciphertext: $encDriveKey,
        );
        $driveHmacKeyMessage = new Message(
            keyId: new Id('007'),
            ciphertext: $encDriveHmacKey,
        );

        file_put_contents("$storageDir/sevena", $driveKeyMessage->encode());
        file_put_contents("$storageDir/sevenb", $driveHmacKeyMessage->encode());

        return new Aes256CbcHmacSha384(
            key: $driveKey,
            hmacKey: $driveHmacKey,
        );
    }
}
