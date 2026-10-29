<?php

/**
 * Isolated tests for the Service Date From / To limits of the invoice search.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\InvoiceServiceDates;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class InvoiceServiceDatesTest extends TestCase
{
    /**
     * @param list<string> $binds
     */
    #[DataProvider('limitsProvider')]
    public function testEachLimitIsAppliedOnItsOwn(string $from, string $to, string $condition, array $binds): void
    {
        $dates = new InvoiceServiceDates($from, $to);

        $this->assertTrue($dates->isSet());
        $this->assertSame([$condition, $binds], $dates->condition('f.date'));
    }

    /**
     * @return array<string, array{string, string, string, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function limitsProvider(): array
    {
        return [
            'from alone lists that date on' => ['2026-09-01', '', 'f.date >= ?', ['2026-09-01']],
            'to alone lists up to the end of that day' => ['', '2026-09-01', 'f.date < DATE_ADD(?, INTERVAL 1 DAY)', ['2026-09-01']],
            'both make an inclusive range' => [
                '2026-03-01',
                '2026-09-01',
                'f.date >= ? AND f.date < DATE_ADD(?, INTERVAL 1 DAY)',
                ['2026-03-01', '2026-09-01'],
            ],
        ];
    }

    public function testNoDatesAddNoCondition(): void
    {
        $dates = new InvoiceServiceDates('', '');

        $this->assertFalse($dates->isSet());
        $this->assertSame(['', []], $dates->condition('f.date'));
    }
}
