<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Filesystem;

use OpenEMR\Common\Filesystem\SafeIncludeResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class SafeIncludeResolverTest extends TestCase
{
    // ── resolve() ──────────────────────────────────────────────────

    public function testResolveAcceptsValidFileUnderBaseDir(): void
    {
        $projectRoot = dirname(__DIR__, 5); // repository root
        $resolved = SafeIncludeResolver::resolve($projectRoot . '/src/Common/Filesystem', 'SafeIncludeResolver.php');

        $expected = realpath($projectRoot . '/src/Common/Filesystem/SafeIncludeResolver.php');
        $this->assertSame($expected, $resolved);
    }

    public function testResolveReturnsFalseForNonexistentFile(): void
    {
        $projectRoot = dirname(__DIR__, 5);
        $this->assertFalse(SafeIncludeResolver::resolve($projectRoot, 'nonexistent_' . uniqid('', true) . '.php'));
    }

    public function testResolveReturnsFalseForNonexistentBaseDir(): void
    {
        $this->assertFalse(SafeIncludeResolver::resolve('/nonexistent_base_' . uniqid('', true), 'file.php'));
    }

    public function testResolveReturnsFalseForDirectory(): void
    {
        $projectRoot = dirname(__DIR__, 5);
        $this->assertFalse(SafeIncludeResolver::resolve($projectRoot, 'src/Common/Filesystem'));
    }

    public function testResolveReturnsFalseForTraversalOutsideBase(): void
    {
        $projectRoot = dirname(__DIR__, 5);
        $this->assertFalse(SafeIncludeResolver::resolve($projectRoot . '/src', '../composer.json'));
    }

    public function testResolveReturnsFalseForDotSegment(): void
    {
        $projectRoot = dirname(__DIR__, 5);
        $this->assertFalse(SafeIncludeResolver::resolve($projectRoot, './composer.json'));
    }

    public function testResolveReturnsFalseForNulByte(): void
    {
        $projectRoot = dirname(__DIR__, 5);
        $this->assertFalse(SafeIncludeResolver::resolve($projectRoot, "src/Common\0/file.php"));
    }

    public function testResolveReturnsFalseForStreamWrapper(): void
    {
        $projectRoot = dirname(__DIR__, 5);
        $this->assertFalse(SafeIncludeResolver::resolve($projectRoot, 'php://filter/resource=composer.json'));
    }

    public function testResolveReturnsFalseForSymlinkPointingOutsideBaseDir(): void
    {
        $projectRoot = dirname(__DIR__, 5);
        $tempFile = tempnam(sys_get_temp_dir(), 'openemr-safe-');
        if ($tempFile === false) {
            $this->fail('Failed to create temp file');
        }

        $linkName = 'openemr-safe-link-' . uniqid('', true) . '.php';
        $linkPath = $projectRoot . '/' . $linkName;

        try {
            if (!@symlink($tempFile, $linkPath)) {
                $this->markTestSkipped('Unable to create symlink in this environment.');
            }

            $this->assertFalse(SafeIncludeResolver::resolve($projectRoot, $linkName));
        } finally {
            if (is_link($linkPath)) {
                unlink($linkPath);
            }

            if (is_file($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    // ── isSafePathComponent() ──────────────────────────────────────

    /**
     * @return array<string, array{mixed, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function pathComponentProvider(): array
    {
        return [
            'valid simple name' => ['newpatient', true],
            'valid with underscore' => ['my_form', true],
            'valid with dash' => ['my-form', true],
            'valid with dot' => ['form.v2', true],
            'dot' => ['.', false],
            'dot-dot' => ['..', false],
            'empty string' => ['', false],
            'null' => [null, false],
            'integer' => [42, false],
            'false' => [false, false],
            'contains NUL' => ["form\0dir", false],
            'contains forward slash' => ['forms/subdir', false],
            'contains backslash' => ['forms\\subdir', false],
        ];
    }

    #[DataProvider('pathComponentProvider')]
    public function testIsSafePathComponent(mixed $input, bool $expected): void
    {
        $this->assertSame($expected, SafeIncludeResolver::isSafePathComponent($input));
    }
}
