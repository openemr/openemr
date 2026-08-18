<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Reports\AMC;

use OpenEMR\Reports\AMC\DateRangeValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DateRangeValidatorTest extends TestCase
{
    public function testBeginBeforeEndIsNotInverted(): void
    {
        self::assertFalse(DateRangeValidator::isInverted('2025-01-01 00:00:00', '2025-12-31 23:59:59'));
    }

    public function testEqualTimestampsAreNotInverted(): void
    {
        self::assertFalse(DateRangeValidator::isInverted('2025-06-15 12:30:45', '2025-06-15 12:30:45'));
    }

    public function testBeginAfterEndIsInverted(): void
    {
        self::assertTrue(DateRangeValidator::isInverted('2025-12-31 23:59:59', '2025-01-01 00:00:00'));
    }

    #[DataProvider('unsupportedTimestampProvider')]
    public function testEmptyAndMalformedTimestampsDoNotChangeLegacyHandling(?string $begin, ?string $end): void
    {
        self::assertFalse(DateRangeValidator::isInverted($begin, $end));
    }

    /**
     * @return array<string, array{0: ?string, 1: ?string}>
     */
    public static function unsupportedTimestampProvider(): array
    {
        return [
            'empty begin' => ['', '2025-12-31 23:59:59'],
            'null end' => ['2025-01-01 00:00:00', null],
            'unsupported shape' => ['12/31/2025 23:59:59', '2025-01-01 00:00:00'],
            'invalid calendar date' => ['2025-02-30 00:00:00', '2025-01-01 00:00:00'],
        ];
    }
}
