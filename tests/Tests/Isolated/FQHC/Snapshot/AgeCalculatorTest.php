<?php

/**
 * Isolated tests for UDS age calculation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Snapshot;

use DateTimeImmutable;
use OpenEMR\FQHC\Snapshot\AgeCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AgeCalculatorTest extends TestCase
{
    private DateTimeImmutable $asOf;

    protected function setUp(): void
    {
        $this->asOf = new DateTimeImmutable('2026-06-25');
    }

    public function testComputesWholeYearsBeforeBirthday(): void
    {
        self::assertSame(46, AgeCalculator::years('1979-07-01', $this->asOf));
    }

    public function testComputesWholeYearsAfterBirthday(): void
    {
        self::assertSame(47, AgeCalculator::years('1979-01-01', $this->asOf));
    }

    public function testAcceptsDatetimeStrings(): void
    {
        self::assertSame(47, AgeCalculator::years('1979-01-01 08:30:00', $this->asOf));
    }

    #[DataProvider('unparseableProvider')]
    public function testReturnsNullForUnusableInput(?string $dob): void
    {
        self::assertNull(AgeCalculator::years($dob, $this->asOf));
    }

    /**
     * @return array<string, array{?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unparseableProvider(): array
    {
        return [
            'null' => [null],
            'empty' => [''],
            'whitespace' => ['   '],
            'zero sentinel' => ['0000-00-00'],
            'future date' => ['2030-01-01'],
        ];
    }
}
