<?php

/**
 * EncryptedGlobalValue isolated tests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Globals;

use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Services\Globals\EncryptedGlobalValue;
use OpenEMR\Services\Globals\GlobalSetting;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EncryptedGlobalValueTest extends TestCase
{
    public function testDecryptForEditReturnsPlaintext(): void
    {
        $crypto = $this->createStub(CryptoInterface::class);
        $crypto->method('decryptFromDatabase')->willReturn('secret');

        self::assertSame('secret', EncryptedGlobalValue::decryptForEdit($crypto, '007abc'));
    }

    public function testDecryptForEditReturnsNullWhenDecryptionFails(): void
    {
        $crypto = $this->createStub(CryptoInterface::class);
        $crypto->method('decryptFromDatabase')->willThrowException(new CryptoGenException('Decryption failed'));

        self::assertNull(EncryptedGlobalValue::decryptForEdit($crypto, '007abc'));
    }

    public function testDecryptForEditPassesNullForNonStringValues(): void
    {
        $crypto = $this->createMock(CryptoInterface::class);
        $crypto->expects(self::once())
            ->method('decryptFromDatabase')
            ->with(null)
            ->willReturn('');

        self::assertSame('', EncryptedGlobalValue::decryptForEdit($crypto, ['not', 'a', 'string']));
    }

    public function testUndecryptableFieldName(): void
    {
        self::assertSame('undecryptable_12', EncryptedGlobalValue::undecryptableFieldName(12));
    }

    /**
     * @return array<string, array{mixed, string, string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function keepStoredValueProvider(): array
    {
        return [
            'marked encrypted field left empty' => [GlobalSetting::DATA_TYPE_ENCRYPTED, '', '1', true],
            'marked hash field left empty' => [GlobalSetting::DATA_TYPE_ENCRYPTED_HASH, '', '1', true],
            'marked field given a new value' => [GlobalSetting::DATA_TYPE_ENCRYPTED, 'new secret', '1', false],
            'unmarked encrypted field cleared on purpose' => [GlobalSetting::DATA_TYPE_ENCRYPTED, '', '', false],
            'marked flag on a text field' => [GlobalSetting::DATA_TYPE_TEXT, '', '1', false],
            'multiple choice field type' => [['a' => 'A'], '', '1', false],
        ];
    }

    #[DataProvider('keepStoredValueProvider')]
    public function testKeepStoredValue(mixed $fieldType, string $submitted, string $flag, bool $expected): void
    {
        self::assertSame($expected, EncryptedGlobalValue::keepStoredValue($fieldType, $submitted, $flag));
    }
}
