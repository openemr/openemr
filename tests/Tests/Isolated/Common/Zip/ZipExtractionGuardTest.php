<?php

/**
 * Isolated tests for ZipExtractionGuard.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Zip;

use OpenEMR\Common\Zip\ZipExtractionGuard;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ZipArchive;

#[Group('isolated')]
class ZipExtractionGuardTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'openemr-zguard-' . uniqid('', true);
        mkdir($this->tmpDir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->tmpDir . '/*') ?: [] as $f) {
            @unlink($f);
        }
        @rmdir($this->tmpDir);
    }

    public function testAcceptsSmallArchive(): void
    {
        $zipPath = $this->tmpDir . '/small.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('one.txt', str_repeat('a', 1024));
        $zip->addFromString('two.txt', str_repeat('b', 2048));
        $zip->close();

        $zip = new ZipArchive();
        $zip->open($zipPath);
        $this->assertTrue(ZipExtractionGuard::isSafe($zip));
        $zip->close();
    }

    public function testRejectsPerEntryOverLimit(): void
    {
        $zipPath = $this->tmpDir . '/entry-over.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('big.bin', str_repeat('x', 2048));
        $zip->close();

        $zip = new ZipArchive();
        $zip->open($zipPath);
        $this->assertFalse(ZipExtractionGuard::isSafe($zip, maxEntryBytes: 1024));
        $zip->close();
    }

    public function testRejectsTotalOverLimit(): void
    {
        $zipPath = $this->tmpDir . '/total-over.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('a.bin', str_repeat('x', 1024));
        $zip->addFromString('b.bin', str_repeat('y', 1024));
        $zip->addFromString('c.bin', str_repeat('z', 1024));
        $zip->close();

        $zip = new ZipArchive();
        $zip->open($zipPath);
        $this->assertFalse(ZipExtractionGuard::isSafe($zip, maxTotalBytes: 2048));
        $zip->close();
    }

    public function testRejectsTooManyEntries(): void
    {
        $zipPath = $this->tmpDir . '/many.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);
        for ($i = 0; $i < 5; $i++) {
            $zip->addFromString("f$i.txt", 'tiny');
        }
        $zip->close();

        $zip = new ZipArchive();
        $zip->open($zipPath);
        $this->assertFalse(ZipExtractionGuard::isSafe($zip, maxEntries: 3));
        $zip->close();
    }

    public function testRejectsZipBombRatio(): void
    {
        $zipPath = $this->tmpDir . '/bomb.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);
        // Highly compressible payload — 1 MiB of zeros compresses to ~1 KiB.
        $zip->addFromString('bomb.bin', str_repeat("\0", 1024 * 1024));
        $zip->close();

        $zip = new ZipArchive();
        $zip->open($zipPath);
        // Ratio limit tight enough to reject the actual payload.
        $this->assertFalse(ZipExtractionGuard::isSafe($zip, maxRatio: 10.0));
        $zip->close();
    }

    public function testEmptyArchiveIsSafe(): void
    {
        $zipPath = $this->tmpDir . '/empty.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);
        $zip->addFromString('placeholder.txt', '');
        $zip->close();

        $zip = new ZipArchive();
        $zip->open($zipPath);
        $this->assertTrue(ZipExtractionGuard::isSafe($zip));
        $zip->close();
    }
}
