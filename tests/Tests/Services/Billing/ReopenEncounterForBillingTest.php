<?php

/**
 * Database-backed coverage of BillingUtilities::reOpenEncounterForBilling(): what re-opening a
 * billed visit without voiding it puts back, and what it leaves alone.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use PHPUnit\Framework\TestCase;

class ReopenEncounterForBillingTest extends TestCase
{
    private const PID = 99990091;
    /** A checked-out visit with a service and a product, and a payment. */
    private const SERVICE_AND_PRODUCT = 99990191;
    /** A checked-out visit with a product only. */
    private const PRODUCT_ONLY = 99990192;
    private const BILL_DATE = '2026-09-01 10:00:00';
    /** A visit with only products, checked out twice: at BILL_DATE and again at SECOND_BILL_DATE. */
    private const TWO_CHECKOUTS = 99990193;
    private const SECOND_BILL_DATE = '2026-09-02 15:30:00';

    /** The session's user before the test: a void is recorded with who did it. */
    private mixed $previousUserId = null;

    protected function setUp(): void
    {
        $this->cleanUp();
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $this->previousUserId = $session->get('authUserID');
        $session->set('authUserID', 1);
        foreach ([self::SERVICE_AND_PRODUCT, self::PRODUCT_ONLY] as $encounter) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO form_encounter (pid, encounter, date, reason, last_level_billed, last_level_closed, stmt_count, last_stmt_date) "
                . "VALUES (?, ?, '2026-09-01', 'test', 1, 1, 2, '2026-09-15')",
                [self::PID, $encounter]
            );
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO drug_sales (drug_id, inventory_id, pid, encounter, user, sale_date, quantity, fee, billed, bill_date) "
                . "VALUES (1, 1, ?, ?, 'admin', '2026-09-01', 1, 25.00, 1, ?)",
                [self::PID, $encounter, self::BILL_DATE]
            );
        }
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO billing (date, code_type, code, pid, encounter, provider_id, user, groupname, authorized, activity, "
            . "billed, bill_date, units, fee, code_text, justify) "
            . "VALUES (NOW(), 'CPT4', '99213', ?, ?, 1, 1, 'Default', 1, 1, 1, ?, 1, 100.00, 'Office visit', '')",
            [self::PID, self::SERVICE_AND_PRODUCT, self::BILL_DATE]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO ar_activity (pid, encounter, sequence_no, code_type, code, modifier, payer_type, post_time, post_user, "
            . "session_id, pay_amount, adj_amount, account_code) "
            . "VALUES (?, ?, 1, '', '', '', 0, ?, 1, 0, 125.00, 0.00, 'PP')",
            [self::PID, self::SERVICE_AND_PRODUCT, self::BILL_DATE]
        );
    }

    public function testAVoidOfTheLastCheckoutLeavesAnEarlierCheckoutsProductLineForALaterVoid(): void
    {
        $this->addTwoProductCheckouts();

        // The default void takes the last checkout only.
        BillingUtilities::doVoid(self::PID, self::TWO_CHECKOUTS, true);

        $this->assertSame(
            [['1', self::BILL_DATE], ['0', '']],
            $this->rows('SELECT billed, bill_date FROM drug_sales WHERE pid = ? AND encounter = ? ORDER BY sale_id', self::TWO_CHECKOUTS),
            'the earlier checkout keeps its bill date: a later void finds it by that'
        );
        $this->assertSame(
            [['10.00', '0'], ['20.00', '1']],
            $this->rows('SELECT pay_amount, deleted IS NOT NULL FROM ar_activity WHERE pid = ? AND encounter = ? ORDER BY sequence_no', self::TWO_CHECKOUTS)
        );

        // And the next void takes the earlier one, payment included.
        BillingUtilities::doVoid(self::PID, self::TWO_CHECKOUTS, true);

        $this->assertSame(
            [['0', ''], ['0', '']],
            $this->rows('SELECT billed, bill_date FROM drug_sales WHERE pid = ? AND encounter = ? ORDER BY sale_id', self::TWO_CHECKOUTS)
        );
        $this->assertSame(
            [['10.00', '1'], ['20.00', '1']],
            $this->rows('SELECT pay_amount, deleted IS NOT NULL FROM ar_activity WHERE pid = ? AND encounter = ? ORDER BY sequence_no', self::TWO_CHECKOUTS)
        );
    }

    public function testReopeningWithoutTheProductLinesLeavesThemBilled(): void
    {
        BillingUtilities::reOpenEncounterForBilling(self::PID, self::SERVICE_AND_PRODUCT, false);

        $this->assertSame([['0', '']], $this->rows('SELECT billed, bill_date FROM billing WHERE pid = ? AND encounter = ?', self::SERVICE_AND_PRODUCT));
        $this->assertSame([['1', self::BILL_DATE]], $this->rows('SELECT billed, bill_date FROM drug_sales WHERE pid = ? AND encounter = ?', self::SERVICE_AND_PRODUCT));
    }

    private function addTwoProductCheckouts(): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO form_encounter (pid, encounter, date, reason, last_level_billed, last_level_closed) VALUES (?, ?, '2026-09-01', 'test', 0, 0)",
            [self::PID, self::TWO_CHECKOUTS]
        );
        foreach ([[1, self::BILL_DATE, 10.00], [2, self::SECOND_BILL_DATE, 20.00]] as [$sequence, $billDate, $paid]) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO drug_sales (drug_id, inventory_id, pid, encounter, user, sale_date, quantity, fee, billed, bill_date) "
                . "VALUES (1, 1, ?, ?, 'admin', '2026-09-01', 1, ?, 1, ?)",
                [self::PID, self::TWO_CHECKOUTS, $paid, $billDate]
            );
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO ar_activity (pid, encounter, sequence_no, code_type, code, modifier, payer_type, post_time, post_user, "
                . "session_id, pay_amount, adj_amount, account_code) "
                . "VALUES (?, ?, ?, '', '', '', 0, ?, 1, 0, ?, 0.00, 'PP')",
                [self::PID, self::TWO_CHECKOUTS, $sequence, $billDate, $paid]
            );
        }
    }

    protected function tearDown(): void
    {
        SessionWrapperFactory::getInstance()->getActiveSession()->set('authUserID', $this->previousUserId);
        $this->cleanUp();
    }

    public function testReopeningPutsTheServiceAndTheProductBackToUnbilledAndKeepsThePayment(): void
    {
        BillingUtilities::reOpenEncounterForBilling(self::PID, self::SERVICE_AND_PRODUCT);

        $this->assertSame([['0', '']], $this->rows('SELECT billed, bill_date FROM billing WHERE pid = ? AND encounter = ?', self::SERVICE_AND_PRODUCT));
        // The product line too: left billed, the fee sheet shows it read-only and does not save it.
        $this->assertSame([['0', '']], $this->rows('SELECT billed, bill_date FROM drug_sales WHERE pid = ? AND encounter = ?', self::SERVICE_AND_PRODUCT));
        $this->assertSame(
            [['0', '0', '0', '']],
            $this->rows('SELECT last_level_billed, last_level_closed, stmt_count, last_stmt_date FROM form_encounter WHERE pid = ? AND encounter = ?', self::SERVICE_AND_PRODUCT)
        );
        // Not a void: what was paid stays posted.
        $this->assertSame([['125.00', '']], $this->rows('SELECT pay_amount, deleted FROM ar_activity WHERE pid = ? AND encounter = ?', self::SERVICE_AND_PRODUCT));
        // Another visit of the patient is not touched.
        $this->assertSame([['1', self::BILL_DATE]], $this->rows('SELECT billed, bill_date FROM drug_sales WHERE pid = ? AND encounter = ?', self::PRODUCT_ONLY));
    }

    public function testAVisitWithOnlyAProductIsNoLongerBilledAfterReopening(): void
    {
        $this->assertTrue(BillingUtilities::isEncounterBilled(self::PID, self::PRODUCT_ONLY));

        BillingUtilities::reOpenEncounterForBilling(self::PID, self::PRODUCT_ONLY);

        $this->assertFalse(BillingUtilities::isEncounterBilled(self::PID, self::PRODUCT_ONLY), 'the fee sheet would still offer only Re-Open');
    }

    /**
     * @return list<list<string>> each row's values as text, NULL as ''
     */
    private function rows(string $sql, int $encounter): array
    {
        return array_map(
            static fn(array $row): array => array_values(array_map(
                static fn(mixed $value): string => is_scalar($value) ? (string) $value : '',
                $row
            )),
            QueryUtils::fetchRecords($sql, [self::PID, $encounter])
        );
    }

    private function cleanUp(): void
    {
        foreach (['ar_activity', 'billing', 'drug_sales', 'form_encounter'] as $table) {
            QueryUtils::sqlStatementThrowException("DELETE FROM $table WHERE pid = ?", [self::PID]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM voids WHERE patient_id = ?', [self::PID]);
    }
}
