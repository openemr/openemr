<?php

/**
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Auth\Oidc\Cache;

use OpenEMR\Common\Auth\Oidc\Cache\OidcCacheDirectoryFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class OidcCacheDirectoryFactoryTest extends TestCase
{
    private string $baseTempDir;

    protected function setUp(): void
    {
        // Per-test base under the system temp dir so concurrent test
        // runs don't trip on each other and tearDown can scrub safely.
        $this->baseTempDir = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'oidc_cache_factory_test_'
            . bin2hex(random_bytes(8));
        if (!mkdir($this->baseTempDir, 0o700, true)) {
            self::fail('Could not create per-test base: ' . $this->baseTempDir);
        }
    }

    protected function tearDown(): void
    {
        $this->recursiveCleanup($this->baseTempDir);
    }

    public function testCreatesDirectoryWhenAbsentAndReturnsCanonicalPath(): void
    {
        $factory = new OidcCacheDirectoryFactory();
        $cacheDir = $factory->create($this->baseTempDir);

        self::assertSame(
            realpath($this->baseTempDir) . DIRECTORY_SEPARATOR . OidcCacheDirectoryFactory::DIRECTORY_NAME,
            $cacheDir,
        );
        self::assertDirectoryExists($cacheDir);
    }

    public function testCreatedDirectoryHas0700Permissions(): void
    {
        $factory = new OidcCacheDirectoryFactory();
        $cacheDir = $factory->create($this->baseTempDir);

        // Strip the file-type bits, keep the permission bits. Expect 0700
        // — only the PHP process user can read/write/execute. Anything
        // looser leaks cached JWKS/discovery metadata to other local users.
        $perms = fileperms($cacheDir);
        self::assertNotFalse($perms);
        self::assertSame(0o700, $perms & 0o777);
    }

    public function testReturnsExistingDirectoryWithoutRecreating(): void
    {
        $factory = new OidcCacheDirectoryFactory();

        $first = $factory->create($this->baseTempDir);

        // Sentinel file inside the cache dir — if the factory recreated
        // the directory the file would be gone.
        $sentinel = $first . DIRECTORY_SEPARATOR . 'sentinel';
        file_put_contents($sentinel, 'x');

        $second = $factory->create($this->baseTempDir);

        self::assertSame($first, $second);
        self::assertFileExists($sentinel);
    }

    public function testThrowsWhenBaseTempDirDoesNotExist(): void
    {
        $factory = new OidcCacheDirectoryFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('temporary_files_dir does not exist');

        $factory->create('/nonexistent-' . bin2hex(random_bytes(6)));
    }

    /**
     * Aisle round-3 finding #6 (CWE-377) regression. If a local
     * attacker can write to `temporary_files_dir` (e.g. the classic
     * world-writable /tmp), they can pre-create the cache path as a
     * symlink to an attacker-controlled directory. is_dir() follows
     * symlinks so the previous code happily wrote cache entries to
     * the symlink target. The factory must refuse this outright.
     */
    public function testThrowsWhenCachePathIsSymlink(): void
    {
        // Create a dummy directory that the symlink will point at —
        // worst case, this is what the attacker would have hijacked.
        $hijackedTarget = $this->baseTempDir . DIRECTORY_SEPARATOR . 'hijacked_target';
        mkdir($hijackedTarget, 0o700);

        // Pre-create the cache path as a symlink. This is the attack.
        $cachePath = $this->baseTempDir . DIRECTORY_SEPARATOR . OidcCacheDirectoryFactory::DIRECTORY_NAME;
        symlink($hijackedTarget, $cachePath);
        self::assertTrue(is_link($cachePath), 'Test fixture: symlink should be in place');

        $factory = new OidcCacheDirectoryFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('symlink');

        try {
            $factory->create($this->baseTempDir);
        } finally {
            // Belt-and-braces: assert nothing was written to the
            // hijacked target during this attempted run. If the
            // symlink check is ever silently removed, this catches it.
            // scandir on an empty dir returns ['.', '..']; ?: [] guards
            // against the false return on read failure (won't happen
            // here since we just created the dir, but it satisfies the
            // type checker without a dead callable filter).
            self::assertSame(
                ['.', '..'],
                scandir($hijackedTarget) ?: [],
                'Hijacked target must remain empty — factory must reject before any I/O',
            );
        }
    }

    public function testConcurrentCreateRaceIsHandledIdempotently(): void
    {
        // Simulate the race: another process won and created the dir
        // *exactly as the factory would*. Subsequent factory calls
        // must succeed without throwing — Aisle's `&& !is_dir` guard.
        $factory = new OidcCacheDirectoryFactory();
        $cachePath = $this->baseTempDir . DIRECTORY_SEPARATOR . OidcCacheDirectoryFactory::DIRECTORY_NAME;
        mkdir($cachePath, 0o700, true);

        $result = $factory->create($this->baseTempDir);

        self::assertSame(
            realpath($this->baseTempDir) . DIRECTORY_SEPARATOR . OidcCacheDirectoryFactory::DIRECTORY_NAME,
            $result,
        );
    }

    /**
     * Recursive teardown: the symlink test deliberately creates
     * symlinks and target dirs; the cache test seeds files. Use
     * `lstat`-aware logic so we delete symlinks themselves instead of
     * accidentally walking through them.
     */
    private function recursiveCleanup(string $path): void
    {
        if (!file_exists($path) && !is_link($path)) {
            return;
        }

        if (is_link($path)) {
            @unlink($path);
            return;
        }

        if (is_dir($path)) {
            $entries = scandir($path);
            if ($entries !== false) {
                foreach ($entries as $entry) {
                    if ($entry === '.' || $entry === '..') {
                        continue;
                    }
                    $this->recursiveCleanup($path . DIRECTORY_SEPARATOR . $entry);
                }
            }
            @rmdir($path);
            return;
        }

        @unlink($path);
    }
}
