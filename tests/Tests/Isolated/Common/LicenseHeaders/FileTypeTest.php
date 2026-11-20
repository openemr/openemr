<?php

/**
 * Tests which file extensions the header checker scans
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\LicenseHeaders;

use OpenEMR\Common\LicenseHeaders\FileType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
final class FileTypeTest extends TestCase
{
    #[DataProvider('pathProvider')]
    public function testFromPath(string $path, ?FileType $expected): void
    {
        self::assertSame($expected, FileType::fromPath($path));
    }

    /**
     * @return array<string, array{string, ?FileType}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function pathProvider(): array
    {
        return [
            'php' => ['src/A.php', FileType::Php],
            'upper-case extension' => ['legacy/OLD.PHP', FileType::Php],
            'js' => ['a/b.js', FileType::CStyle],
            'mjs' => ['eslint.config.mjs', FileType::CStyle],
            'css' => ['a.css', FileType::CStyle],
            'scss' => ['a.scss', FileType::CStyle],
            'twig' => ['templates/a.html.twig', FileType::Twig],
            'rector fixture' => ['tests/Rector/Fixture/a.php.inc', null],
            'markdown' => ['README.md', null],
            'no extension' => ['bin/console', null],
        ];
    }
}
