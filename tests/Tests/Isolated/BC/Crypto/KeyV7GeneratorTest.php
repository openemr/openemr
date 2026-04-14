<?php

/**
 * Isolated KeyV7Generator Test
 *
 * Tests the database key generation portion of KeyV7Generator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BC\Crypto;

use OpenEMR\BC\Crypto\KeyV7Generator;
use OpenEMR\Encryption\Cipher\Aes256CbcHmacSha384;
use OpenEMR\Encryption\Plaintext;
use OpenEMR\Encryption\Storage\KeyMaterialId;
use OpenEMR\Tests\Fixtures\InMemoryKeyStorage;
use PHPUnit\Framework\TestCase;

final class KeyV7GeneratorTest extends TestCase
{
    public function testGenerateDbKeyReturnsCorrectCipher(): void
    {
        $storage = new InMemoryKeyStorage();

        $cipher = KeyV7Generator::generateDbKey($storage);

        self::assertInstanceOf(Aes256CbcHmacSha384::class, $cipher);
    }

    public function testGenerateDbKeyStoresEncryptionKey(): void
    {
        $storage = new InMemoryKeyStorage();

        KeyV7Generator::generateDbKey($storage);

        self::assertTrue($storage->has('sevena'));
        self::assertSame(
            Aes256CbcHmacSha384::KEY_LENGTH,
            strlen($storage->getKey(new KeyMaterialId('sevena'))->key),
        );

        self::assertTrue($storage->has('sevenb'));
        self::assertSame(32, strlen($storage->getKey(new KeyMaterialId('sevenb'))->key));
    }

    public function testReturnedCipherCanEncryptAndDecrypt(): void
    {
        $storage = new InMemoryKeyStorage();
        $cipher = KeyV7Generator::generateDbKey($storage);
        $plaintext = new Plaintext('test data for encryption');

        $ciphertext = $cipher->encrypt($plaintext);
        $decrypted = $cipher->decrypt($ciphertext);

        self::assertSame($plaintext->bytes, $decrypted->bytes);
    }

    public function testStoredKeysMatchReturnedCipher(): void
    {
        $storage = new InMemoryKeyStorage();
        $originalCipher = KeyV7Generator::generateDbKey($storage);
        $plaintext = new Plaintext('test data');

        $ciphertext = $originalCipher->encrypt($plaintext);

        // Create a new cipher from the stored keys
        $reconstructedCipher = new Aes256CbcHmacSha384(
            key: $storage->getKey(new KeyMaterialId('sevena')),
            hmacKey: $storage->getKey(new KeyMaterialId('sevenb')),
        );

        // The reconstructed cipher should decrypt data from the original
        $decrypted = $reconstructedCipher->decrypt($ciphertext);
        self::assertSame($plaintext->bytes, $decrypted->bytes);
    }

    public function testGenerateDbKeyProducesUniqueKeysEachCall(): void
    {
        $storage1 = new InMemoryKeyStorage();
        $storage2 = new InMemoryKeyStorage();

        KeyV7Generator::generateDbKey($storage1);
        KeyV7Generator::generateDbKey($storage2);

        // Keys should be different between calls
        self::assertNotSame(
            $storage1->getKey(new KeyMaterialId('sevena'))->key,
            $storage2->getKey(new KeyMaterialId('sevena'))->key,
        );
        self::assertNotSame(
            $storage1->getKey(new KeyMaterialId('sevenb'))->key,
            $storage2->getKey(new KeyMaterialId('sevenb'))->key,
        );
    }
}
