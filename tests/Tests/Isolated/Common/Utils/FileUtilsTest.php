<?php

/**
 * FileUtils Isolated Test
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Utils;

use OpenEMR\Common\Utils\FileUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FileUtilsTest extends TestCase
{
    // =========================================================================
    // getMimeTypeFromExtension
    // =========================================================================

    /**
     * @return iterable<string, array{string, string}>
     * @codeCoverageIgnore
     */
    public static function extensionToMimeProvider(): iterable
    {
        yield 'pdf' => ['pdf', 'application/pdf'];
        yield 'json' => ['json', 'application/json'];
        yield 'xml' => ['xml', 'application/xml'];
        yield 'png' => ['png', 'image/png'];
        yield 'jpg' => ['jpg', 'image/jpeg'];
        yield 'jpeg' => ['jpeg', 'image/jpeg'];
        yield 'gif' => ['gif', 'image/gif'];
        yield 'html' => ['html', 'text/html'];
        yield 'css' => ['css', 'text/css'];
        yield 'js' => ['js', 'application/javascript'];
        yield 'zip' => ['zip', 'application/zip'];
        yield 'svg' => ['svg', 'image/svg+xml'];
        yield 'txt' => ['txt', 'text/plain'];
        yield 'doc' => ['doc', 'application/msword'];
        yield 'mp3' => ['mp3', 'audio/mpeg'];
        yield 'mov' => ['mov', 'video/quicktime'];
    }

    #[DataProvider('extensionToMimeProvider')]
    public function testGetMimeTypeFromExtension(string $extension, string $expectedMime): void
    {
        $this->assertSame($expectedMime, FileUtils::getMimeTypeFromExtension($extension));
    }

    public function testGetMimeTypeFromExtensionIsCaseInsensitive(): void
    {
        $this->assertSame('application/pdf', FileUtils::getMimeTypeFromExtension('PDF'));
        $this->assertSame('image/png', FileUtils::getMimeTypeFromExtension('PNG'));
    }

    public function testGetMimeTypeFromExtensionReturnsDefaultForUnknown(): void
    {
        $this->assertSame('text/plain', FileUtils::getMimeTypeFromExtension('xyz'));
    }

    public function testGetMimeTypeFromExtensionReturnsCustomDefault(): void
    {
        $this->assertSame('application/octet-stream', FileUtils::getMimeTypeFromExtension('xyz', 'application/octet-stream'));
    }

    // =========================================================================
    // getExtensionFromMimeType
    // =========================================================================

    /**
     * @return iterable<string, array{string, string}>
     * @codeCoverageIgnore
     */
    public static function mimeToExtensionProvider(): iterable
    {
        yield 'application/pdf' => ['application/pdf', 'pdf'];
        yield 'application/json' => ['application/json', 'json'];
        yield 'image/png' => ['image/png', 'png'];
        yield 'image/gif' => ['image/gif', 'gif'];
        yield 'text/plain' => ['text/plain', 'txt'];
        yield 'text/css' => ['text/css', 'css'];
        yield 'application/zip' => ['application/zip', 'zip'];
    }

    #[DataProvider('mimeToExtensionProvider')]
    public function testGetExtensionFromMimeType(string $mimeType, string $expectedExtension): void
    {
        $this->assertSame($expectedExtension, FileUtils::getExtensionFromMimeType($mimeType));
    }

    public function testGetExtensionFromMimeTypeIsCaseInsensitive(): void
    {
        $this->assertSame('pdf', FileUtils::getExtensionFromMimeType('APPLICATION/PDF'));
    }

    public function testGetExtensionFromMimeTypeReturnsEmptyForUnknown(): void
    {
        $this->assertSame('', FileUtils::getExtensionFromMimeType('application/x-unknown'));
    }

    public function testGetExtensionFromMimeTypeReturnsFallback(): void
    {
        $this->assertSame('bin', FileUtils::getExtensionFromMimeType('application/x-unknown', 'bin'));
    }

    // =========================================================================
    // getHumanReadableFileSize
    // =========================================================================

    /**
     * @return iterable<string, array{int, string}>
     * @codeCoverageIgnore
     */
    public static function fileSizeProvider(): iterable
    {
        yield 'zero bytes' => [0, 'n/a'];
        yield '1 byte' => [1, '1 Bytes'];
        yield '500 bytes' => [500, '500 Bytes'];
        yield '1023 bytes' => [1023, '1023 Bytes'];
        yield '1 KB' => [1024, '1 KB'];
        yield '1.5 KB' => [1536, '1.5 KB'];
        yield '1 MB' => [1048576, '1 MB'];
        yield '1.5 MB' => [1572864, '1.5 MB'];
        yield '1 GB' => [1073741824, '1 GB'];
    }

    #[DataProvider('fileSizeProvider')]
    public function testGetHumanReadableFileSize(int $bytes, string $expected): void
    {
        $this->assertSame($expected, FileUtils::getHumanReadableFileSize($bytes));
    }

    // =========================================================================
    // ensureExtension
    // =========================================================================

    public function testEnsureExtensionAddsExtensionWhenMissing(): void
    {
        $this->assertSame('document.pdf', FileUtils::ensureExtension('document', 'application/pdf'));
    }

    public function testEnsureExtensionPreservesExistingExtension(): void
    {
        $this->assertSame('document.txt', FileUtils::ensureExtension('document.txt', 'application/pdf'));
    }

    public function testEnsureExtensionWithPathAndNoExtension(): void
    {
        $this->assertSame('/tmp/uploads/file.png', FileUtils::ensureExtension('/tmp/uploads/file', 'image/png'));
    }
}
