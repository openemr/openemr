<?php

/**
 * Isolated InfoTxt Test
 *
 * A module's or form's info.txt name and category must come back without the
 * line's newline: they are stored in modules.mod_name and registry.name and
 * registry.category, and compared exactly elsewhere.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Core;

use OpenEMR\Core\InfoTxt;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class InfoTxtTest extends TestCase
{
    /**
     * @return array<string, array{string, ?string, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function contentsProvider(): array
    {
        return [
            'module name with trailing newline' => ["Dashboard Context Service v1.0.0\n", 'Dashboard Context Service v1.0.0', null],
            'module name without newline' => ['UDS Report v1.0.0', 'UDS Report v1.0.0', null],
            'form name and category' => ["PHQ-9\nClinical\n", 'PHQ-9', 'Clinical'],
            'Windows line endings' => ["GAD-7\r\nClinical\r\n", 'GAD-7', 'Clinical'],
            'surrounding spaces' => ["  Vitals  \n  Clinical  \n", 'Vitals', 'Clinical'],
            'blank category line' => ["Physical Exam\n\n", 'Physical Exam', null],
            'blank first line' => ["\nClinical\n", null, 'Clinical'],
            'empty file' => ['', null, null],
        ];
    }

    #[DataProvider('contentsProvider')]
    public function testParseTrimsEachLine(string $contents, ?string $name, ?string $category): void
    {
        $info = InfoTxt::parse($contents);
        self::assertSame($name, $info->name);
        self::assertSame($category, $info->category);
    }

    public function testReadReturnsNullWithoutAFile(): void
    {
        self::assertNull(InfoTxt::read(sys_get_temp_dir() . '/no-such-dir-' . bin2hex(random_bytes(4)) . '/info.txt'));
    }

    public function testReadParsesTheFile(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'info');
        self::assertIsString($path);
        try {
            file_put_contents($path, "Prior Authorization\nAdministrative\n");
            $info = InfoTxt::read($path);
            self::assertNotNull($info);
            self::assertSame('Prior Authorization', $info->name);
            self::assertSame('Administrative', $info->category);
        } finally {
            unlink($path);
        }
    }
}
