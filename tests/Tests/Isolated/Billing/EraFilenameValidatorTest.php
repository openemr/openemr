<?php

/**
 * Isolated tests for EraFilenameValidator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\EraFilenameValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class EraFilenameValidatorTest extends TestCase
{
    #[DataProvider('sanitizeProvider')]
    public function testSanitize(string $candidate, ?string $expected): void
    {
        $this->assertSame($expected, EraFilenameValidator::sanitize($candidate));
    }

    /**
     * @return array<string, array{string, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function sanitizeProvider(): array
    {
        return [
            // Well-formed names round-trip unchanged.
            'numeric payer' => ['20261011_12345_67890', '20261011_12345_67890'],
            'alphanumeric payer' => ['20261011_12345_AETNA', '20261011_12345_AETNA'],
            'mixed payer' => ['20261011_12345_BCBSTX01', '20261011_12345_BCBSTX01'],
            'alphanumeric control number' => ['20261011_ABC123_PAYER', '20261011_ABC123_PAYER'],
            // Rejected: empty.
            'empty' => ['', null],
            // Rejected: traversal.
            'traversal in payer segment' => ['20261011_12345_../tmp/EVIL', null],
            'traversal in date segment' => ['../../tmp_12345_PAYER', null],
            'traversal only' => ['../../etc/passwd', null],
            // Rejected: path separators.
            'forward slash' => ['20261011/12345/PAYER', null],
            'backslash' => ['20261011\\12345\\PAYER', null],
            // Rejected: shape mismatch.
            'too few segments' => ['20261011_12345', null],
            'too many segments' => ['20261011_12345_PAYER_EXTRA', null],
            'short date' => ['2026_12345_PAYER', null],
            'long date' => ['202610110_12345_PAYER', null],
            'non-numeric date' => ['OCT202611_12345_PAYER', null],
            // Rejected: special characters.
            'dot in payer' => ['20261011_12345_PAY.ER', null],
            'dash in payer' => ['20261011_12345_PAY-ER', null],
            'space in payer' => ['20261011_12345_PAY ER', null],
            // Rejected: null byte.
            'null byte in payer' => ["20261011_12345_PAY\0ER", null],
        ];
    }

    public function testResolveWithinDirectoryAcceptsValidNameInsideBase(): void
    {
        $tmp = self::makeTempDir();
        try {
            $resolved = EraFilenameValidator::resolveWithinDirectory($tmp, '20261011_12345_AETNA', '.edi');
            $this->assertSame($tmp . DIRECTORY_SEPARATOR . '20261011_12345_AETNA.edi', $resolved);
        } finally {
            @rmdir($tmp);
        }
    }

    public function testResolveWithinDirectoryHonorsPrefix(): void
    {
        $tmp = self::makeTempDir();
        try {
            $resolved = EraFilenameValidator::resolveWithinDirectory($tmp, '20261011_12345_AETNA', '.edi', '.pending_');
            $this->assertSame($tmp . DIRECTORY_SEPARATOR . '.pending_20261011_12345_AETNA.edi', $resolved);
        } finally {
            @rmdir($tmp);
        }
    }

    public function testResolveWithinDirectoryRejectsTraversal(): void
    {
        $tmp = self::makeTempDir();
        try {
            $this->assertNull(EraFilenameValidator::resolveWithinDirectory($tmp, '../../tmp/EVIL', '.edi'));
        } finally {
            @rmdir($tmp);
        }
    }

    public function testResolveWithinDirectoryRejectsEmptyCandidate(): void
    {
        $tmp = self::makeTempDir();
        try {
            $this->assertNull(EraFilenameValidator::resolveWithinDirectory($tmp, '', '.edi'));
        } finally {
            @rmdir($tmp);
        }
    }

    public function testResolveWithinDirectoryRejectsMissingBaseDir(): void
    {
        $this->assertNull(EraFilenameValidator::resolveWithinDirectory(
            '/nonexistent_base_' . uniqid('', true),
            '20261011_12345_AETNA',
            '.edi'
        ));
    }

    public function testResolveWithinDirectoryHonorsDifferentSuffix(): void
    {
        $tmp = self::makeTempDir();
        try {
            $resolved = EraFilenameValidator::resolveWithinDirectory($tmp, '20261011_12345_AETNA', '.835');
            $this->assertSame($tmp . DIRECTORY_SEPARATOR . '20261011_12345_AETNA.835', $resolved);
        } finally {
            @rmdir($tmp);
        }
    }

    private static function makeTempDir(): string
    {
        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'openemr-era-' . uniqid('', true);
        if (!mkdir($tmp)) {
            throw new \RuntimeException('Failed to create temp dir');
        }
        $real = realpath($tmp);
        return is_string($real) ? $real : $tmp;
    }
}
