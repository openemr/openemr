<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Storage;

use InvalidArgumentException;
use OpenEMR\Services\Storage\CacheDirectory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CacheDirectoryTest extends TestCase
{
    private string $testBaseDir;

    protected function setUp(): void
    {
        $this->testBaseDir = sys_get_temp_dir() . '/openemr-cache-test-' . bin2hex(random_bytes(8));
        mkdir($this->testBaseDir, 0700, true);
    }

    protected function tearDown(): void
    {
        $this->recursiveDelete($this->testBaseDir);
    }

    public function testForCreatesDirectoryWhenNotExists(): void
    {
        $cache = new CacheDirectory($this->testBaseDir);

        $path = $cache->for('smarty');

        self::assertDirectoryExists($path);
        self::assertStringEndsWith('/smarty', $path);
    }

    public function testForReturnsConsistentPath(): void
    {
        $cache = new CacheDirectory($this->testBaseDir);

        $first = $cache->for('smarty');
        $second = $cache->for('smarty');

        self::assertSame($first, $second);
    }

    public function testForReturnsDifferentPathsForDifferentScopes(): void
    {
        $cache = new CacheDirectory($this->testBaseDir);

        $smarty = $cache->for('smarty');
        $mpdf = $cache->for('mpdf');

        self::assertNotSame($smarty, $mpdf);
        self::assertStringEndsWith('/smarty', $smarty);
        self::assertStringEndsWith('/mpdf', $mpdf);
    }

    public function testForCreatesDirectoryWithRestrictivePermissions(): void
    {
        $cache = new CacheDirectory($this->testBaseDir);

        $path = $cache->for('smarty');

        $perms = fileperms($path) & 0777;
        self::assertSame(0700, $perms);
    }

    public function testForRejectsSymlink(): void
    {
        $realDir = $this->testBaseDir . '/real';
        $symlinkPath = $this->testBaseDir . '/smarty';
        mkdir($realDir, 0700);
        symlink($realDir, $symlinkPath);

        $cache = new CacheDirectory($this->testBaseDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('symlink');

        $cache->for('smarty');
    }

    /**
     * @return array<string, array{int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function insecurePermissionsProvider(): array
    {
        return [
            'group writable (0770)' => [0770],
            'world writable (0707)' => [0707],
            'group readable (0740)' => [0740],
            'world readable (0704)' => [0704],
            'group executable (0710)' => [0710],
            'world executable (0701)' => [0701],
            'fully open (0777)' => [0777],
            'common default (0755)' => [0755],
        ];
    }

    #[DataProvider('insecurePermissionsProvider')]
    public function testForRejectsInsecurePermissions(int $mode): void
    {
        $path = $this->testBaseDir . '/smarty';
        mkdir($path);
        chmod($path, $mode);

        $cache = new CacheDirectory($this->testBaseDir);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('permissions');

        $cache->for('smarty');
    }

    public function testForAcceptsExistingSecureDirectory(): void
    {
        $path = $this->testBaseDir . '/smarty';
        mkdir($path, 0700);

        $cache = new CacheDirectory($this->testBaseDir);

        $result = $cache->for('smarty');

        self::assertSame($path, $result);
    }

    public function testForUsesSystemTempDirWhenNoBaseDirProvided(): void
    {
        $scope = 'openemr-cache-test-' . bin2hex(random_bytes(8));
        $cache = new CacheDirectory();
        $tempDir = sys_get_temp_dir();

        try {
            $path = $cache->for($scope);

            self::assertSame($tempDir . '/' . $scope, $path);
            self::assertDirectoryExists($path);
        } finally {
            if (is_dir($tempDir . '/' . $scope)) {
                rmdir($tempDir . '/' . $scope);
            }
        }
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function invalidScopeProvider(): array
    {
        return [
            'empty string' => [''],
            'forward slash' => ['foo/bar'],
            'backslash (windows)' => ['foo\\bar'],
            'dot' => ['.'],
            'double dot' => ['..'],
            'path traversal' => ['../etc'],
        ];
    }

    #[DataProvider('invalidScopeProvider')]
    public function testForRejectsInvalidScope(string $scope): void
    {
        $cache = new CacheDirectory($this->testBaseDir);

        $this->expectException(InvalidArgumentException::class);

        $cache->for($scope);
    }

    private function recursiveDelete(string $path): void
    {
        if (!file_exists($path) && !is_link($path)) {
            return;
        }

        if (is_link($path)) {
            unlink($path);
            return;
        }

        if (is_dir($path)) {
            foreach (scandir($path) as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                $this->recursiveDelete($path . '/' . $item);
            }
            rmdir($path);
        } else {
            unlink($path);
        }
    }
}
