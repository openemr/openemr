<?php

/**
 * Tests for BC\Utilities helper methods.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\BC;

use DateTimeImmutable;
use OpenEMR\BC\Utilities;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UtilitiesTest extends TestCase
{
    #[DataProvider('dateProvider')]
    public function testIsDateEmpty(mixed $value, bool $expected): void
    {
        self::assertSame($expected, Utilities::isDateEmpty($value));
    }

    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function dateProvider(): array
    {
        return [
            'null' => [null, true],
            'empty string' => ['', true],
            'zero date' => ['0000-00-00', true],
            'zero datetime' => ['0000-00-00 00:00:00', true],
            'zero date slash format' => ['00/00/0000', true],
            'zero date reversed' => ['00-00-0000', true],
            'valid date string' => ['2024-01-15', false],
            'valid datetime string' => ['2024-01-15 10:30:00', false],
            'DateTimeImmutable' => [new DateTimeImmutable('2024-01-15'), false],
            'unix epoch' => ['1970-01-01', false],
            'unix epoch datetime' => ['1970-01-01 00:00:00', false],
        ];
    }
}
