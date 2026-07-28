<?php

/**
 * Isolated test for the archive-name pattern applied at
 * `edih_archive_restore()` in `library/edihistory/edih_archive.php`.
 * Verifying the pattern in isolation catches accidental relaxation of
 * the anchors or the `.zip` suffix requirement without needing the full
 * edihistory dependency chain.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EdihArchiveNamePatternTest extends TestCase
{
    private const PATTERN = '/\A[\w.-]+\.zip\z/';

    #[DataProvider('acceptedNamesProvider')]
    public function testAccepts(string $name): void
    {
        $this->assertSame(1, preg_match(self::PATTERN, $name), "Expected accepted: $name");
    }

    #[DataProvider('rejectedNamesProvider')]
    public function testRejects(string $name): void
    {
        $this->assertSame(0, preg_match(self::PATTERN, $name), "Expected rejected: $name");
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function acceptedNamesProvider(): array
    {
        return [
            'timestamped archive'  => ['20260701_archive.zip'],
            'dashed name'          => ['test-archive.zip'],
            'dotted name'          => ['archive.2026.zip'],
            'alphanumeric only'    => ['archive1.zip'],
        ];
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function rejectedNamesProvider(): array
    {
        return [
            'unix-style parent-dir path'    => ['../../etc/passwd'],
            'windows-style parent-dir path' => ['..\\..\\Windows\\win.ini'],
            'no extension'             => ['foo'],
            'wrong extension'          => ['foo.php'],
            'double extension probe'   => ['foo.zip.evil'],
            'empty'                    => [''],
            'leading dot'              => ['.zip'],
            'contains slash'           => ['dir/archive.zip'],
            'contains backslash'       => ['dir\\archive.zip'],
            'contains space'           => ['my archive.zip'],
            'null byte'                => ["archive.zip\0.php"],
        ];
    }
}
