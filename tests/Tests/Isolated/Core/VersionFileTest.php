<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Core;

use OpenEMR\Core\VersionFile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class VersionFileTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/version_file_test_' . uniqid('', true);
        if (!mkdir($this->tempDir, 0700, true) && !is_dir($this->tempDir)) {
            // @codeCoverageIgnoreStart
            // Defensive - only fires if the OS refuses to create a fresh
            // temp directory, which is not a path real CI exercises.
            $this->fail('could not create temp dir ' . $this->tempDir);
            // @codeCoverageIgnoreEnd
        }
    }

    protected function tearDown(): void
    {
        $file = $this->tempDir . '/version.php';
        if (is_file($file)) {
            unlink($file);
        }
        if (is_dir($this->tempDir)) {
            rmdir($this->tempDir);
        }
    }

    private function writeVersionFile(string $body): string
    {
        $path = $this->tempDir . '/version.php';
        file_put_contents($path, '<?php' . PHP_EOL . PHP_EOL . $body);
        return $path;
    }

    /**
     * The values version.php declares survive a prior inclusion elsewhere.
     *
     * This is the regression: interface/globals.php used to read the $v_*
     * variables a require_once left in its own scope, and a require_once is a
     * no-op once any other entry point has included the file.
     */
    #[Test]
    public function readsValuesEvenWhenTheFileWasAlreadyIncluded(): void
    {
        $path = $this->writeVersionFile(<<<'PHP'
        $v_major = '8';
        $v_minor = '4';
        $v_patch = '0';
        $v_tag = '-dev';
        $v_realpatch = '0';
        $v_database = 543;
        $v_acl = 13;
        $v_js_includes = 82;
        PHP);

        // Consume the one-shot inclusion the way admin.php does.
        require_once $path;

        $version = VersionFile::fromFile($path);

        $this->assertSame('8', $version->major);
        $this->assertSame('4', $version->minor);
        $this->assertSame('0', $version->patch);
        $this->assertSame('-dev', $version->tag);
        $this->assertSame('0', $version->realpatch);
        $this->assertSame(543, $version->database);
        $this->assertSame(13, $version->acl);
        $this->assertSame(82, $version->jsIncludes);
    }

    #[Test]
    public function loadResolvesVersionPhpUnderTheProjectDirectory(): void
    {
        $this->writeVersionFile(<<<'PHP'
        $v_major = '7';
        $v_minor = '0';
        $v_patch = '3';
        $v_tag = '';
        $v_realpatch = '2';
        $v_database = 500;
        $v_acl = 10;
        $v_js_includes = 'ab12cd34';
        PHP);

        $version = VersionFile::load($this->tempDir);

        $this->assertSame('7', $version->major);
        $this->assertSame('2', $version->realpatch);
        $this->assertSame(500, $version->database);
        $this->assertSame('ab12cd34', $version->jsIncludes);
    }

    /**
     * A dev environment computes $v_js_includes as an md5 rather than an int,
     * and forks are free to quote the numeric values.
     */
    #[Test]
    public function acceptsTheAlternateTypesVersionFilesUseInPractice(): void
    {
        $path = $this->writeVersionFile(<<<'PHP'
        $v_major = 8;
        $v_minor = '4';
        $v_patch = '0';
        $v_tag = '';
        $v_realpatch = '0';
        $v_database = '543';
        $v_acl = '13';
        $v_js_includes = md5('fixed');
        PHP);

        $version = VersionFile::fromFile($path);

        $this->assertSame('8', $version->major);
        $this->assertSame(543, $version->database);
        $this->assertSame(13, $version->acl);
        $this->assertSame(md5('fixed'), $version->jsIncludes);
    }

    #[Test]
    public function rejectsAMissingFile(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Version file not found');

        VersionFile::load($this->tempDir);
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unusableValueProvider(): array
    {
        return [
            'missing variable' => ['unset($v_major);', 'v_major'],
            'null value' => ['$v_major = null;', 'v_major'],
            'array value' => ['$v_major = [];', 'v_major'],
            'non-numeric database' => ["\$v_database = 'five';", 'v_database'],
            'float js includes' => ['$v_js_includes = 1.5;', 'v_js_includes'],
        ];
    }

    #[Test]
    #[DataProvider('unusableValueProvider')]
    public function rejectsAnUnusableValue(string $override, string $expectedName): void
    {
        $body = <<<'PHP'
        $v_major = '8';
        $v_minor = '4';
        $v_patch = '0';
        $v_tag = '';
        $v_realpatch = '0';
        $v_database = 543;
        $v_acl = 13;
        $v_js_includes = 82;
        PHP;
        $path = $this->writeVersionFile($body . PHP_EOL . $override);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('does not declare a usable $' . $expectedName);

        VersionFile::fromFile($path);
    }

    /**
     * The project's own version.php has to satisfy the reader.
     */
    #[Test]
    public function readsTheProjectVersionFile(): void
    {
        $version = VersionFile::load(dirname(__DIR__, 4));

        $this->assertNotSame('', $version->major);
        $this->assertGreaterThan(0, $version->database);
        $this->assertGreaterThan(0, $version->acl);
        $this->assertNotSame('', (string) $version->jsIncludes);
    }
}
