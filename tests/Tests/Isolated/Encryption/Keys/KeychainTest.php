<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Encryption\Keys;

use OpenEMR\Encryption\Cipher\CipherInterface;
use OpenEMR\Encryption\KeyId;
use OpenEMR\Encryption\Keys\{
    Keychain,
};
use OutOfBoundsException;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class KeychainTest extends TestCase
{
    public function testHasKey(): void
    {
        $keychain = new Keychain();
        $id = new KeyId('abc');
        self::assertFalse($keychain->hasKey($id), 'Initial state should have no keys');

        $cipher = self::createStub(CipherInterface::class);
        $keychain->registerCipher($id, $cipher);
        self::assertTrue($keychain->hasKey($id), 'Key should exist after adding');
    }

    public function testGetCipher(): void
    {
        $keychain = new Keychain();

        $id1 = new KeyId('one');
        $cipher1 = self::createStub(CipherInterface::class);
        $keychain->registerCipher($id1, $cipher1);

        $id2 = new KeyId('two');
        $cipher2 = self::createStub(CipherInterface::class);
        $keychain->registerCipher($id2, $cipher2);


        assert($cipher1 !== $cipher2);

        $retr1 = $keychain->getCipher($id1);
        self::assertSame($cipher1, $retr1);
        $retr2 = $keychain->getCipher($id2);
        self::assertSame($cipher2, $retr2);
    }

    public function testGetNonexistentCipher(): void
    {
        $keychain = new Keychain();
        self::expectException(OutOfBoundsException::class);
        $keychain->getCipher(new KeyId('not registered'));
    }

    public function testCurrentKeyIsMostRecent(): void
    {
        $keychain = new Keychain();
        try {
            $_ = $keychain->getCurrentKeyId();
            $this->fail('getCurrentKeyId before registering ciphers should be an error');
        } catch (\Error) {
            $this->addToAssertionCount(1);
        }

        $id1 = new KeyId('key one');
        $keychain->registerCipher($id1, self::createStub(CipherInterface::class));
        self::assertSame($id1, $keychain->getCurrentKeyId());

        $id2 = new KeyId('key two');
        $keychain->registerCipher($id2, self::createStub(CipherInterface::class));
        self::assertSame($id2, $keychain->getCurrentKeyId());
    }
}
