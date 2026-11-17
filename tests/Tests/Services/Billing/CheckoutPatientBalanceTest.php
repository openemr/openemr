<?php

/**
 * Checkout's test for whose balance a visit's is (CheckoutPatientBalance),
 * which front payment and the portal's payment page share: the patient's
 * when no insurance level is in force on the visit date, when the visit is
 * closed for every level, or when as many levels have closed it as are on
 * file.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\CheckoutPatientBalance;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CheckoutPatientBalanceTest extends TestCase
{
    private const PID = 99995002;
    private const VISIT = '2026-06-01';
    private const PLAN = 99995031;
    private const OTHER_PLAN = 99995032;

    protected function setUp(): void
    {
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    /**
     * Coverage that ended before the visit leaves its balance to the patient,
     * though a level is still on file.
     */
    #[Test]
    public function withNoLevelInForceOnTheVisitDateTheBalanceIsThePatients(): void
    {
        $this->addPolicy('primary', self::PLAN, '2025-01-01', '2026-03-31');

        self::assertTrue((new CheckoutPatientBalance())->isDue(self::PID, self::VISIT, 0));
    }

    /**
     * A plan that starts after the visit doesn't cover it either.
     */
    #[Test]
    public function aPlanStartingAfterTheVisitLeavesTheBalanceToThePatient(): void
    {
        $this->addPolicy('primary', self::PLAN, '2026-07-01', null);

        self::assertTrue((new CheckoutPatientBalance())->isDue(self::PID, self::VISIT, 0));
    }

    /**
     * An ended primary with a current secondary: the secondary is still in
     * force, so until the levels close the balance is insurance's.
     */
    #[Test]
    public function aCurrentSecondaryKeepsTheBalanceWithInsurance(): void
    {
        $this->addPolicy('primary', self::PLAN, '2025-01-01', '2026-03-31');
        $this->addPolicy('secondary', self::OTHER_PLAN, '2025-01-01', null);
        $balance = new CheckoutPatientBalance();

        self::assertFalse($balance->isDue(self::PID, self::VISIT, 0));
        self::assertFalse($balance->isDue(self::PID, self::VISIT, 1));
        self::assertTrue($balance->isDue(self::PID, self::VISIT, 2), 'both levels on file have closed it');
        self::assertTrue($balance->isDue(self::PID, self::VISIT, 4), 'closed for every level');
    }

    #[Test]
    public function withoutInsuranceTheBalanceIsThePatients(): void
    {
        self::assertTrue((new CheckoutPatientBalance())->isDue(self::PID, self::VISIT, 0));
    }

    /**
     * A visit date that can't be read has no level in force.
     */
    #[Test]
    public function aVisitDateThatCantBeReadHasNoLevelInForce(): void
    {
        $this->addPolicy('primary', self::PLAN, null, null);
        $balance = new CheckoutPatientBalance();

        self::assertFalse($balance->isDue(self::PID, self::VISIT, 0));
        self::assertTrue($balance->isDue(self::PID, '', 0));
        self::assertTrue($balance->isDue(self::PID, '0000-00-00', 0));
    }

    private function addPolicy(string $type, int $company, ?string $start, ?string $end): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO insurance_data (uuid, pid, type, provider, date, date_end, plan_name) VALUES (?, ?, ?, ?, ?, ?, 'test-fixture')",
            [(new UuidRegistry(['table_name' => 'insurance_data']))->createUuid(), self::PID, $type, (string) $company, $start, $end]
        );
    }

    private function cleanUp(): void
    {
        foreach (QueryUtils::fetchTableColumn('SELECT uuid FROM insurance_data WHERE pid = ? AND uuid IS NOT NULL', 'uuid', [self::PID]) as $uuid) {
            QueryUtils::sqlStatementThrowException("DELETE FROM uuid_registry WHERE table_name = 'insurance_data' AND uuid = ?", [$uuid]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid = ?', [self::PID]);
    }
}
