<?php

/**
 * The SQL condition an invoice search's filters make.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\Invoice;

use OpenEMR\Billing\Invoice\InvoiceCategory;
use OpenEMR\Billing\Invoice\InvoiceSearchCriteria;
use OpenEMR\Billing\InvoiceServiceDates;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class InvoiceSearchCriteriaTest extends TestCase
{
    public function testNoFilterListsEveryOpenInvoice(): void
    {
        $this->assertSame(['1 = 1', []], (new InvoiceSearchCriteria(InvoiceCategory::DuePt))->condition());
    }

    public function testAnAllSearchNeedsAFilter(): void
    {
        $criteria = new InvoiceSearchCriteria(InvoiceCategory::All);

        $this->assertFalse($criteria->hasFilter());
        $this->assertNull($criteria->condition());
    }

    /**
     * @param array{string, list<string>} $expected
     */
    #[DataProvider('nameProvider')]
    public function testTheNameFilter(string $name, array $expected): void
    {
        $this->assertSame($expected, (new InvoiceSearchCriteria(InvoiceCategory::All, name: $name))->condition());
    }

    /**
     * @return array<string, array{string, array{string, list<string>}}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nameProvider(): array
    {
        return [
            'any part of the last name' => ['oe', ['p.lname LIKE ?', ['%oe%']]],
            'last, first prefixes' => ['Doe , Jo', ['p.lname LIKE ? AND p.fname LIKE ?', ['Doe%', 'Jo%']]],
            'a range of initials' => ['A-C', ['(p.lname LIKE ? OR p.lname LIKE ? OR p.lname LIKE ?)', ['A%', 'B%', 'C%']]],
            'a backwards range matches nothing' => ['C-A', ['1 = 2', []]],
            'a value with a wildcard is bound, not spliced' => ["O'Br%", ['p.lname LIKE ?', ["%O'Br%%"]]],
        ];
    }

    public function testEveryFilterTogether(): void
    {
        $criteria = new InvoiceSearchCriteria(
            InvoiceCategory::Open,
            name: 'Doe',
            pid: '12',
            encounter: '345',
            serviceDates: new InvoiceServiceDates('2026-01-01', '2026-01-31'),
            lastStatementBefore: '2026-10-15',
        );

        $this->assertTrue($criteria->hasFilter());
        $this->assertSame([
            'p.lname LIKE ? AND f.pid = ? AND f.encounter = ? AND f.date >= ? AND f.date < DATE_ADD(?, INTERVAL 1 DAY)'
                . ' AND (f.last_stmt_date IS NULL OR f.last_stmt_date < ?)',
            ['%Doe%', '12', '345', '2026-01-01', '2026-01-31', '2026-10-15'],
        ], $criteria->condition());
    }

    public function testAnErasClaims(): void
    {
        $this->assertSame(
            ['((f.pid = ? AND f.encounter = ?) OR (f.pid = ? AND f.encounter = ?))', ['12', '345', '13', '346X']],
            InvoiceSearchCriteria::forEncounters([['12', '345'], ['13', '346X']])
        );
    }

    public function testAnEraWithNoClaimOfOursListsNothing(): void
    {
        $this->assertSame(['1 = 2', []], InvoiceSearchCriteria::forEncounters([]));
    }

    public function testAnUnknownCategoryListsAsOpen(): void
    {
        $this->assertSame(InvoiceCategory::DuePt, InvoiceCategory::fromForm('Due Pt'));
        $this->assertSame(InvoiceCategory::Open, InvoiceCategory::fromForm(''));
        $this->assertSame(InvoiceCategory::Open, InvoiceCategory::fromForm('Pendiente'));
    }
}
