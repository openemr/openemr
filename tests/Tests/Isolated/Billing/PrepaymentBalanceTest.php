<?php

/**
 * Isolated tests for prepayment balance narrowing and totals.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (C) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\PrepaymentBalance;
use PHPUnit\Framework\TestCase;

/**
 * The report reads eleven untyped columns per row. These tests pin the
 * narrowing in PrepaymentBalance::fromRow() and the footer arithmetic, neither
 * of which needs a database.
 */
class PrepaymentBalanceTest extends TestCase
{
    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function row(array $overrides = []): array
    {
        return array_merge([
            'session_id' => '412',
            'patient_id' => '1088',
            'check_date' => '2026-03-14',
            'reference' => 'CK 20194',
            'pay_total' => '250.00',
            'global_amount' => '0.00',
            'applied' => '75.00',
            'unapplied' => '175.00',
            'days_open' => '169',
            'lname' => 'Alvarez',
            'fname' => 'Renata',
            'mname' => 'K',
        ], $overrides);
    }

    public function testNarrowsStringColumnsToScalars(): void
    {
        $balance = PrepaymentBalance::fromRow($this->row());

        $this->assertSame(412, $balance->sessionId);
        $this->assertSame(1088, $balance->patientId);
        $this->assertSame('Alvarez, Renata K', $balance->patientName);
        $this->assertSame('CK 20194', $balance->reference);
        $this->assertSame('2026-03-14', $balance->checkDate);
        $this->assertSame(169, $balance->daysOpen);
        $this->assertSame(250.00, $balance->received);
        $this->assertSame(75.00, $balance->applied);
        $this->assertSame(0.00, $balance->inGlobal);
        $this->assertSame(175.00, $balance->unapplied);
    }

    public function testMissingColumnsFallBackToZeroAndEmptyString(): void
    {
        $balance = PrepaymentBalance::fromRow([]);

        $this->assertSame(0, $balance->sessionId);
        $this->assertSame('', $balance->patientName);
        $this->assertSame('', $balance->reference);
        $this->assertNull($balance->checkDate);
        $this->assertSame(0.0, $balance->unapplied);
    }

    public function testNonNumericAmountsBecomeZeroRatherThanCoercing(): void
    {
        $balance = PrepaymentBalance::fromRow($this->row([
            'pay_total' => null,
            'applied' => 'not a number',
            'unapplied' => [],
        ]));

        $this->assertSame(0.0, $balance->received);
        $this->assertSame(0.0, $balance->applied);
        $this->assertSame(0.0, $balance->unapplied);
    }

    public function testNullCheckDateStaysNull(): void
    {
        $this->assertNull(PrepaymentBalance::fromRow($this->row(['check_date' => null]))->checkDate);
        $this->assertNull(PrepaymentBalance::fromRow($this->row(['check_date' => '']))->checkDate);
    }

    /**
     * A patient_data row can be missing entirely on a LEFT JOIN, and middle
     * names are frequently blank; neither should leave stray punctuation.
     */
    public function testNameFormattingHandlesAbsentParts(): void
    {
        $this->assertSame(
            'Alvarez, Renata',
            PrepaymentBalance::fromRow($this->row(['mname' => '']))->patientName
        );
        $this->assertSame(
            'Alvarez',
            PrepaymentBalance::fromRow($this->row(['fname' => '', 'mname' => '']))->patientName
        );
        $this->assertSame(
            'Renata',
            PrepaymentBalance::fromRow($this->row(['lname' => '', 'mname' => '']))->patientName
        );
        $this->assertSame(
            '',
            PrepaymentBalance::fromRow($this->row(['lname' => '', 'fname' => '', 'mname' => '']))->patientName
        );
    }

    public function testTotalsSumEachColumn(): void
    {
        $balances = [
            PrepaymentBalance::fromRow($this->row()),
            PrepaymentBalance::fromRow($this->row([
                'pay_total' => '100.00',
                'applied' => '0.00',
                'global_amount' => '40.00',
                'unapplied' => '100.00',
            ])),
        ];

        $totals = PrepaymentBalance::totals($balances);

        $this->assertSame(350.00, $totals['received']);
        $this->assertSame(75.00, $totals['applied']);
        $this->assertSame(40.00, $totals['inGlobal']);
        $this->assertSame(275.00, $totals['unapplied']);
    }

    public function testTotalsOfEmptyResultAreZero(): void
    {
        $totals = PrepaymentBalance::totals([]);

        $this->assertSame(
            ['received' => 0.0, 'applied' => 0.0, 'inGlobal' => 0.0, 'unapplied' => 0.0],
            $totals
        );
    }
}
