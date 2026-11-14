<?php

/**
 * PatientBalanceLoader and get_patient_balance() against the per-encounter
 * queries get_patient_balance() ran before, on one fixture patient's
 * encounters, billing, payments, drug sales and insurance.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\PatientBalance\PatientBalanceLoader;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../library/patient.inc.php';

final class PatientBalanceLoaderTest extends TestCase
{
    /** Out to primary insurance, nothing closed. */
    private const ENC_OUT = 990000101;
    /** Before the policy started, in collection. */
    private const ENC_UNINSURED = 990000102;
    /** Primary closed, in collection. */
    private const ENC_CLOSED = 990000103;

    private FixtureManager $fixtureManager;
    private int $pid;

    protected function setUp(): void
    {
        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();
        $pid = QueryUtils::fetchSingleValue(
            "SELECT `pid` FROM `patient_data` WHERE `pubpid` LIKE ? ORDER BY `pid` LIMIT 1",
            'pid',
            [FixtureManager::PATIENT_FIXTURE_PUBPID_PREFIX . '%']
        );
        if (!is_numeric($pid)) {
            self::fail('patient fixture not found');
        }
        $this->pid = (int) $pid;
        $this->removeRows();
        $this->addRows();
    }

    protected function tearDown(): void
    {
        $this->removeRows();
        $this->fixtureManager->removePatientFixtures();
    }

    private function removeRows(): void
    {
        foreach (['form_encounter', 'billing', 'ar_activity', 'drug_sales', 'insurance_data'] as $table) {
            QueryUtils::sqlStatementThrowException("DELETE FROM `$table` WHERE `pid` = ?", [$this->pid]);
        }
    }

    private function addRows(): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO `insurance_data` (`pid`, `type`, `provider`, `date`, `plan_name`)"
                . " VALUES (?, 'primary', '101', '2025-01-01', 'test-fixture')",
            [$this->pid]
        );

        $this->addEncounter(self::ENC_OUT, '2025-01-10 09:00:00', 0, null);
        $this->addBilling(self::ENC_OUT, 'CPT4', '100.00', 1);
        $this->addBilling(self::ENC_OUT, 'copay', '20.00', 1);
        $this->addActivity(self::ENC_OUT, 1, 0, '15.00', '0.00', null);
        $this->addActivity(self::ENC_OUT, 2, 1, '50.00', '10.00', null);
        $this->addDrugSale(self::ENC_OUT, '5.00');

        $this->addEncounter(self::ENC_UNINSURED, '2024-01-10 09:00:00', 0, 1);
        $this->addBilling(self::ENC_UNINSURED, 'CPT4', '200.00', 1);
        $this->addBilling(self::ENC_UNINSURED, 'CPT4', '999.00', 0);
        $this->addActivity(self::ENC_UNINSURED, 1, 0, '50.00', '0.00', null);
        $this->addActivity(self::ENC_UNINSURED, 2, 0, '999.00', '0.00', '2024-02-01 00:00:00');

        $this->addEncounter(self::ENC_CLOSED, '2025-02-01 09:00:00', 1, 1);
        $this->addBilling(self::ENC_CLOSED, 'CPT4', '80.00', 1);
        $this->addActivity(self::ENC_CLOSED, 1, 1, '30.00', '20.00', null);
    }

    private function addEncounter(int $encounter, string $date, int $lastLevelClosed, ?int $inCollection): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO `form_encounter` (`pid`, `encounter`, `date`, `last_level_closed`, `in_collection`)"
                . " VALUES (?, ?, ?, ?, ?)",
            [$this->pid, $encounter, $date, $lastLevelClosed, $inCollection]
        );
    }

    private function addBilling(int $encounter, string $codeType, string $fee, int $activity): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO `billing` (`date`, `code_type`, `code`, `pid`, `encounter`, `fee`, `activity`)"
                . " VALUES (NOW(), ?, '99213', ?, ?, ?, ?)",
            [$codeType, $this->pid, $encounter, $fee, $activity]
        );
    }

    private function addActivity(
        int $encounter,
        int $sequence,
        int $payerType,
        string $pay,
        string $adj,
        ?string $deleted,
    ): void {
        QueryUtils::sqlInsert(
            "INSERT INTO `ar_activity` (`pid`, `encounter`, `sequence_no`, `code`, `payer_type`, `post_time`,"
                . " `post_user`, `session_id`, `pay_amount`, `adj_amount`, `modified_time`, `follow_up`,"
                . " `account_code`, `deleted`)"
                . " VALUES (?, ?, ?, '', ?, NOW(), 1, 0, ?, ?, NOW(), '', '', ?)",
            [$this->pid, $encounter, $sequence, $payerType, $pay, $adj, $deleted]
        );
    }

    private function addDrugSale(int $encounter, string $fee): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO `drug_sales` (`drug_id`, `inventory_id`, `pid`, `encounter`, `sale_date`, `fee`)"
                . " VALUES (0, 0, ?, ?, '2025-01-10', ?)",
            [$this->pid, $encounter, $fee]
        );
    }

    /**
     * get_patient_balance() as it was before PatientBalanceLoader: the
     * insurance lookup and SUM queries run once per encounter.
     */
    private function perEncounterBalance(bool $withInsurance, ?int $eid, bool $inCollection): string
    {
        $sql = "SELECT date, encounter, last_level_closed FROM form_encounter WHERE pid = ?";
        $binds = [$this->pid];
        if ($eid !== null) {
            $sql .= " AND encounter = ?";
            $binds[] = $eid;
        }
        if ($inCollection) {
            $sql .= " AND in_collection = 1";
        }
        $balance = 0;
        foreach (QueryUtils::fetchRecords($sql, $binds) as $fe) {
            $encounter = $fe['encounter'];
            $inscount = count(getEffectiveInsurances($this->pid, substr(is_string($fe['date']) ? $fe['date'] : '', 0, 10)));
            if (!$withInsurance && $fe['last_level_closed'] < $inscount) {
                $amount = QueryUtils::fetchSingleValue(
                    "SELECT SUM(fee) AS amount FROM billing WHERE pid = ? AND encounter = ?"
                        . " AND code_type = 'copay' AND activity = 1",
                    'amount',
                    [$this->pid, $encounter]
                );
                $payments = QueryUtils::fetchSingleValue(
                    "SELECT SUM(pay_amount) AS payments FROM ar_activity WHERE deleted IS NULL"
                        . " AND pid = ? AND encounter = ? AND payer_type = 0",
                    'payments',
                    [$this->pid, $encounter]
                );
                $balance += self::num($amount) - self::num($payments);
            } else {
                $amount = QueryUtils::fetchSingleValue(
                    "SELECT SUM(fee) AS amount FROM billing WHERE pid = ? AND encounter = ? AND activity = 1",
                    'amount',
                    [$this->pid, $encounter]
                );
                $activity = QueryUtils::fetchRecords(
                    "SELECT SUM(pay_amount) AS payments, SUM(adj_amount) AS adjustments FROM ar_activity"
                        . " WHERE deleted IS NULL AND pid = ? AND encounter = ?",
                    [$this->pid, $encounter]
                )[0];
                $sales = QueryUtils::fetchSingleValue(
                    "SELECT SUM(fee) AS amount FROM drug_sales WHERE pid = ? AND encounter = ?",
                    'amount',
                    [$this->pid, $encounter]
                );
                $balance += self::num($amount) + self::num($sales)
                    - self::num($activity['payments']) - self::num($activity['adjustments']);
            }
        }
        return sprintf('%01.2f', $balance);
    }

    private static function num(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }

    /**
     * @return array<string, array{bool, ?int, bool, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function balanceCases(): array
    {
        return [
            // out: copay 20 - patient 15; uninsured: 200 - 50; closed: 80 - 30 - 20
            'patient' => [false, null, false, '185.00'],
            // out: 100 + 20 + 5 - 65 - 10
            'with insurance' => [true, null, false, '230.00'],
            'collection' => [false, null, true, '180.00'],
            'one encounter out to insurance' => [false, self::ENC_OUT, false, '5.00'],
            'one encounter with insurance' => [true, self::ENC_OUT, false, '50.00'],
            'one encounter not in collection' => [false, self::ENC_OUT, true, '0.00'],
            'unknown encounter' => [true, 990000199, false, '0.00'],
        ];
    }

    #[Test]
    #[DataProvider('balanceCases')]
    public function matchesPerEncounterQueries(bool $withInsurance, ?int $eid, bool $inCollection, string $expected): void
    {
        self::assertSame($expected, $this->perEncounterBalance($withInsurance, $eid, $inCollection));
        self::assertSame(
            $expected,
            (new PatientBalanceLoader())->load($this->pid, $eid)->balance($withInsurance, $inCollection)
        );
        self::assertSame(
            $expected,
            get_patient_balance($this->pid, $withInsurance, $eid ?? false, $inCollection)
        );
    }

    #[Test]
    public function digitStringIdsWork(): void
    {
        self::assertSame('185.00', get_patient_balance((string) $this->pid));
        self::assertSame('5.00', get_patient_balance((string) $this->pid, false, (string) self::ENC_OUT));
    }

    #[Test]
    public function invalidIdsGiveZero(): void
    {
        self::assertSame('0.00', get_patient_balance(0, true));
        self::assertSame('0.00', get_patient_balance('abc', true));
        self::assertSame('0.00', get_patient_balance($this->pid, true, 'abc'));
    }

    #[Test]
    public function patientWithNoEncountersIsZero(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM `form_encounter` WHERE `pid` = ?", [$this->pid]);
        self::assertSame('0.00', (new PatientBalanceLoader())->load($this->pid)->balance(true));
    }
}
