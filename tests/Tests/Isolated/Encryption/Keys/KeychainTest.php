<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Encryption\Keys;

use OpenEMR\Encryption\Cipher\CipherInterface;
use OpenEMR\Encryption\Keys\{
    Id,
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
        $id = new Id('abc');
        self::assertFalse($keychain->hasKey($id), 'Initial state should have no keys');

        $cipher = self::createStub(CipherInterface::class);
        $keychain->addCipher($id, $cipher);
        self::assertTrue($keychain->hasKey($id), 'Key should exist after adding');
    }

    public function testGetCipher(): void
    {
        $keychain = new Keychain();

        $id1 = new Id('one');
        $cipher1 = self::createStub(CipherInterface::class);
        $keychain->addCipher($id1, $cipher1);

        $id2 = new Id('two');
        $cipher2 = self::createStub(CipherInterface::class);
        $keychain->addCipher($id2, $cipher2);


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
        $keychain->getCipher(new Id('not registered'));
    }
}
