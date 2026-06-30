<?php

/**
 * Isolated tests for the UDS Table 3A age band.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting;

use DomainException;
use OpenEMR\FQHC\Reporting\Table3aAgeBand;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class Table3aAgeBandTest extends TestCase
{
    #[DataProvider('ageLineProvider')]
    public function testFromAgeMapsToManualLineAndLabel(int $age, int $expectedLine, string $expectedLabel): void
    {
        $band = Table3aAgeBand::fromAge($age);

        self::assertSame($expectedLine, $band->line);
        self::assertSame($expectedLabel, $band->label());
    }

    public function testFromAgeRejectsNegativeAge(): void
    {
        $this->expectException(DomainException::class);

        Table3aAgeBand::fromAge(-1);
    }

    public function testConstructorRejectsLineOutOfRange(): void
    {
        $this->expectException(DomainException::class);

        new Table3aAgeBand(39);
    }

    /**
     * Lines confirmed against Documentation/UDS/UDS_2025_Manual.txt, Table 3A.
     *
     * @return array<string, array{int, int, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function ageLineProvider(): array
    {
        return [
            'infant under 1 -> line 1' => [0, 1, 'Under age 1'],
            'age 1 -> line 2' => [1, 2, 'Age 1'],
            'age 24 -> line 25' => [24, 25, 'Age 24'],
            'age 25 starts 5-year bands -> line 26' => [25, 26, 'Ages 25–29'],
            'age 29 still line 26' => [29, 26, 'Ages 25–29'],
            'age 30 -> line 27' => [30, 27, 'Ages 30–34'],
            'age 64 -> line 33' => [64, 33, 'Ages 60–64'],
            'age 65 -> line 34' => [65, 34, 'Ages 65–69'],
            'age 84 -> line 37' => [84, 37, 'Ages 80–84'],
            'age 85 -> line 38' => [85, 38, 'Age 85 and over'],
            'age 103 still line 38' => [103, 38, 'Age 85 and over'],
        ];
    }
}
