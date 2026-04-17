<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Encryption;

use OpenEMR\Encryption\Cipher\CipherInterface;
use OpenEMR\Encryption\CipherSuite;
use OpenEMR\Encryption\Ciphertext;
use OpenEMR\Encryption\KeyId;
use OpenEMR\Encryption\Keys\KeychainInterface;
use OpenEMR\Encryption\Plaintext;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[Small]
class CipherSuiteTest extends TestCase
{
    private KeychainInterface&MockObject $keychain;
    private CipherInterface&MockObject $cipher;

    private CipherSuite $suite;
    private KeyId $keyId;

    protected function setUp(): void
    {
        $this->cipher = $this->createMock(CipherInterface::class);

        $this->keyId = new KeyId('005');

        $this->keychain = $this->createMock(KeychainInterface::class);
        $this->keychain->method('getCurrentKeyId')->willReturn($this->keyId);
        $this->keychain->method('getCipher')->with($this->keyId)->willReturn($this->cipher);

        $this->suite = new CipherSuite($this->keychain);
    }

    public function testRoundtripWithStringInput(): void
    {
        $plaintext = 'sensitive data';

        $ciphertext = new Ciphertext('encrypted bytes');

        $this->cipher->method('encrypt')
            ->with(self::callback(fn (Plaintext $pt) => $pt->bytes === $plaintext))
            ->willReturn($ciphertext);

        $encrypted = $this->suite->encrypt($plaintext);

        self::assertStringNotContainsString($plaintext, $encrypted);

        $this->cipher->expects(self::once())
            ->method('decrypt')
            ->with(self::callback(fn (Ciphertext $c) => $c->value === $ciphertext->value))
            ->willReturn(new Plaintext($plaintext));

        $decrypted = $this->suite->decrypt($encrypted);
        self::assertSame($plaintext, $decrypted);
    }

    public function testEncryptWithPlaintextInput(): void
    {
        $plaintext = new Plaintext('sensitive data');

        $ciphertext = new Ciphertext('encrypted bytes');

        $this->cipher->method('encrypt')
            ->with(self::callback(fn (Plaintext $pt) => $pt->bytes === $plaintext->bytes))
            ->willReturn($ciphertext);

        $encrypted = $this->suite->encrypt($plaintext);

        self::assertStringNotContainsString($plaintext->bytes, $encrypted);

        $this->cipher->expects(self::once())
            ->method('decrypt')
            ->with(self::callback(fn (Ciphertext $c) => $c->value === $ciphertext->value))
            ->willReturn($plaintext);

        $decrypted = $this->suite->decrypt($encrypted);
        self::assertSame($plaintext->bytes, $decrypted);
    }
}
