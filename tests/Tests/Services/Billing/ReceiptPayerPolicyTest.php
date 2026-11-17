<?php

/**
 * The policy Receipts by Payer reports an insurer payment posted without a
 * payer under (ReceiptPayerPolicy): the policy at the payment's level in
 * force on the date of service, whose company names the payer and whose
 * number fills the Policy column.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\ReceiptPayerPolicy;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Insurance\InsuranceLevel;
use OpenEMR\Services\InsuranceService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ReceiptPayerPolicyTest extends TestCase
{
    private const PID = 99995801;
    private const SERVICE_DATE = '2026-06-01 10:00:00';
    private const PLAN = 99995811;
    private const OTHER_PLAN = 99995812;

    protected function setUp(): void
    {
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    /**
     * A visit after a change of insurer: the new plan, and its number.
     */
    #[Test]
    public function afterAChangeOfInsurerTheNewPlanAndItsNumber(): void
    {
        $this->addPolicy('primary', self::PLAN, '2025-01-01', '2026-04-11', 'old-number');
        $this->addPolicy('primary', self::OTHER_PLAN, '2026-04-12', null, 'new-number');

        self::assertEquals(new ReceiptPayerPolicy(self::OTHER_PLAN, 'new-number'), $this->inForce(InsuranceLevel::Primary));
    }

    /**
     * Two undated policies at one level: the later entry, company and number.
     * The Policy column took the first row the database returned.
     */
    #[Test]
    public function twoUndatedPoliciesGiveTheLaterEntrysCompanyAndNumber(): void
    {
        $this->addPolicy('primary', self::PLAN, null, null, 'first-number');
        $this->addPolicy('primary', self::OTHER_PLAN, null, null, 'second-number');

        self::assertEquals(new ReceiptPayerPolicy(self::OTHER_PLAN, 'second-number'), $this->inForce(InsuranceLevel::Primary));
    }

    /**
     * A 0000-00-00 end (what sql_mode = '' stores for a mistyped date) is
     * open, so the policy is in force and its number shows. The Policy column
     * read it as ended and showed none.
     */
    #[Test]
    public function aZeroEndIsOpenAndItsNumberShows(): void
    {
        $this->addPolicy('primary', self::PLAN, '2025-01-01', '0000-00-00', 'zero-end-number');

        self::assertEquals(new ReceiptPayerPolicy(self::PLAN, 'zero-end-number'), $this->inForce(InsuranceLevel::Primary));
    }

    /**
     * An "Unassigned" row (no company) ends the level: no policy, so no payer
     * and no number. The Policy column showed the Unassigned row's number.
     */
    #[Test]
    public function anUnassignedRowLeavesNoPolicy(): void
    {
        $this->addPolicy('primary', self::PLAN, '2025-01-01', null, 'was-number');
        $this->addPolicy('primary', null, '2026-03-01', null, 'unassigned-number');

        self::assertNull($this->inForce(InsuranceLevel::Primary));
    }

    /**
     * The payment's level picks the policy: a secondary's payment is the
     * secondary's.
     */
    #[Test]
    public function theLevelPicksThePolicy(): void
    {
        $this->addPolicy('primary', self::PLAN, '2025-01-01', null, 'primary-number');
        $this->addPolicy('secondary', self::OTHER_PLAN, '2025-01-01', null, 'secondary-number');

        self::assertEquals(new ReceiptPayerPolicy(self::OTHER_PLAN, 'secondary-number'), $this->inForce(InsuranceLevel::Secondary));
        self::assertNull($this->inForce(InsuranceLevel::Tertiary));
    }

    /**
     * A date of service that can't be read, or a patient id that isn't one,
     * has no policy.
     */
    #[Test]
    public function aDateOrPatientThatCantBeReadHasNoPolicy(): void
    {
        $this->addPolicy('primary', self::PLAN, null, null, 'undated-number');
        $service = new InsuranceService();

        self::assertNotNull($this->inForce(InsuranceLevel::Primary));
        self::assertNull(ReceiptPayerPolicy::inForce($service, self::PID, InsuranceLevel::Primary, ''));
        self::assertNull(ReceiptPayerPolicy::inForce($service, self::PID, InsuranceLevel::Primary, '0000-00-00 00:00:00'));
        self::assertNull(ReceiptPayerPolicy::inForce($service, '', InsuranceLevel::Primary, self::SERVICE_DATE));
    }

    private function inForce(InsuranceLevel $level): ?ReceiptPayerPolicy
    {
        return ReceiptPayerPolicy::inForce(new InsuranceService(), (string) self::PID, $level, self::SERVICE_DATE);
    }

    private function addPolicy(string $type, ?int $company, ?string $start, ?string $end, string $number): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO insurance_data (uuid, pid, type, provider, date, date_end, policy_number, plan_name) VALUES (?, ?, ?, ?, ?, ?, ?, 'test-fixture')",
            [(new UuidRegistry(['table_name' => 'insurance_data']))->createUuid(), self::PID, $type, $company === null ? '' : (string) $company, $start, $end, $number]
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
