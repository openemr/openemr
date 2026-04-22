<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated;

use Document;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class DocumentTest extends TestCase
{
    /**
     * @return array<string, array{
     *   higherLevelPath: string,
     *   patientId: int|string,
     *   pathDepth: int,
     *   randomSubdir: int,
     *   expectedPath: string,
     *   expectedDepth: int,
     *   expectedPatientId: int|string,
     * }>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function calculateStoragePathProvider(): array
    {
        return [
            'valid patient, no higher level path' => [
                'higherLevelPath' => '',
                'patientId' => 123,
                'pathDepth' => 1,
                'randomSubdir' => 5000,
                'expectedPath' => '123/',
                'expectedDepth' => 1,
                'expectedPatientId' => 123,
            ],
            'valid patient, with higher level path' => [
                'higherLevelPath' => 'ccda/imports',
                'patientId' => 456,
                'pathDepth' => 1,
                'randomSubdir' => 9999,
                'expectedPath' => 'ccda/imports/',
                'expectedDepth' => 1,
                'expectedPatientId' => 456,
            ],
            'invalid patient (string), no higher level path - uses random subdir' => [
                'higherLevelPath' => '',
                'patientId' => 'direct',
                'pathDepth' => 1,
                'randomSubdir' => 42,
                'expectedPath' => 'direct/42/',
                'expectedDepth' => 2,
                'expectedPatientId' => 0,
            ],
            'invalid patient (zero), no higher level path - uses random subdir' => [
                'higherLevelPath' => '',
                'patientId' => 0,
                'pathDepth' => 1,
                'randomSubdir' => 1,
                'expectedPath' => '0/1/',
                'expectedDepth' => 2,
                'expectedPatientId' => 0,
            ],
            'invalid patient (negative), no higher level path - uses random subdir' => [
                'higherLevelPath' => '',
                'patientId' => -5,
                'pathDepth' => 1,
                'randomSubdir' => 999,
                'expectedPath' => '-5/999/',
                'expectedDepth' => 2,
                'expectedPatientId' => 0,
            ],
            'invalid patient (zero), with higher level path - uses random subdir' => [
                'higherLevelPath' => 'exports',
                'patientId' => 0,
                'pathDepth' => 1,
                'randomSubdir' => 7777,
                'expectedPath' => 'exports/7777/',
                'expectedDepth' => 2,
                'expectedPatientId' => 0,
            ],
            'invalid patient (string), with higher level path - preserves patientId' => [
                'higherLevelPath' => 'ccda/exports',
                'patientId' => 'direct',
                'pathDepth' => 1,
                'randomSubdir' => 1234,
                'expectedPath' => 'ccda/exports/1234/',
                'expectedDepth' => 2,
                'expectedPatientId' => 'direct',
            ],
            'path depth is preserved when not overwritten' => [
                'higherLevelPath' => 'custom/path',
                'patientId' => 99,
                'pathDepth' => 5,
                'randomSubdir' => 1,
                'expectedPath' => 'custom/path/',
                'expectedDepth' => 5,
                'expectedPatientId' => 99,
            ],
        ];
    }

    /**
     * @param int|string $patientId
     * @param int|string $expectedPatientId
     */
    #[DataProvider('calculateStoragePathProvider')]
    public function testCalculateStoragePath(
        string $higherLevelPath,
        int|string $patientId,
        int $pathDepth,
        int $randomSubdir,
        string $expectedPath,
        int $expectedDepth,
        int|string $expectedPatientId,
    ): void {
        $result = Document::calculateStoragePath(
            higherLevelPath: $higherLevelPath,
            patientId: $patientId,
            pathDepth: $pathDepth,
            randomSubdir: $randomSubdir,
        );

        self::assertSame($expectedPath, $result['relativePath']);
        self::assertSame($expectedDepth, $result['depth']);
        self::assertSame($expectedPatientId, $result['patientId']);
    }

    /**
     * @return array<string, array{input: string, expected: string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function calculateStoragePathSanitizationProvider(): array
    {
        return [
            'alphanumeric unchanged' => [
                'input' => 'foo/bar',
                'expected' => 'foo/bar/',
            ],
            'dashes replaced' => [
                'input' => 'foo-bar',
                'expected' => 'foo_bar/',
            ],
            'special chars replaced' => [
                'input' => 'foo@bar#baz',
                'expected' => 'foo_bar_baz/',
            ],
            'spaces replaced' => [
                'input' => 'foo bar',
                'expected' => 'foo_bar/',
            ],
            'multiple slashes preserved' => [
                'input' => 'a/b/c/d',
                'expected' => 'a/b/c/d/',
            ],
            'dots replaced' => [
                'input' => 'file.name',
                'expected' => 'file_name/',
            ],
        ];
    }

    #[DataProvider('calculateStoragePathSanitizationProvider')]
    public function testCalculateStoragePathSanitization(string $input, string $expected): void
    {
        $result = Document::calculateStoragePath(
            higherLevelPath: $input,
            patientId: 1,
            pathDepth: 1,
            randomSubdir: 1,
        );

        self::assertSame($expected, $result['relativePath']);
    }
}
