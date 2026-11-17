<?php

/**
 * What the policy screens open on: InsuranceService::search()'s order (the
 * REST and FHIR lists, the policy editor) and the demographics card's current
 * policy (getPoliciesOrganizedByTypeForPatientPid()). Both put the policy in
 * force today first.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use DateTimeImmutable;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\InsuranceService;
use OpenEMR\Services\Search\TokenSearchField;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InsuranceServicePolicyScreensTest extends TestCase
{
    private const PID = 99995101;
    private const PLAN = 99995111;
    private const NEXT_PLAN = 99995112;

    protected function setUp(): void
    {
        $this->cleanUp();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (uuid, pid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, ?, 'JANE', 'TESTPATIENT', '1980-01-01', 'Female', NOW())",
            [(new UuidRegistry(['table_name' => 'patient_data']))->createUuid(), self::PID, (string) self::PID]
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    /**
     * Next year's card, entered in advance, starts later than the current
     * one; neither has an end. The screens open on the current one.
     */
    #[Test]
    public function nextYearsCardComesAfterThePolicyInForceToday(): void
    {
        $this->addPolicy('primary', self::PLAN, 'CURRENT', -1000);
        $this->addPolicy('primary', self::NEXT_PLAN, 'NEXT-YEAR', 60);

        self::assertSame(['CURRENT', 'NEXT-YEAR'], $this->searchOrder());
        self::assertSame(['current' => 'CURRENT', 'history' => ['NEXT-YEAR']], $this->organized()['primary'] ?? null);
    }

    /**
     * Of two undated policies at a level, the later entry is in force (the
     * coverage rule's tie-break): the list and the card put it first, whatever
     * their policy numbers.
     */
    #[Test]
    public function ofTwoUndatedPoliciesTheScreensOpenOnTheLaterEntry(): void
    {
        $this->addPolicy('primary', self::PLAN, 'A-FIRST-ENTERED', null);
        $this->addPolicy('primary', self::NEXT_PLAN, 'B-ENTERED-LATER', null);

        self::assertSame(['B-ENTERED-LATER', 'A-FIRST-ENTERED'], $this->searchOrder());
        self::assertSame(['current' => 'B-ENTERED-LATER', 'history' => ['A-FIRST-ENTERED']], $this->organized()['primary'] ?? null);
    }

    /**
     * With nothing in force at a level, the card keeps the list's first
     * policy, as before.
     */
    #[Test]
    public function aLevelWithNothingInForceKeepsTheListsFirst(): void
    {
        $this->addPolicy('secondary', self::PLAN, 'ENDED-LONG-AGO', -1000, -500);
        $this->addPolicy('secondary', self::NEXT_PLAN, 'ENDED-LATELY', -400, -100);

        self::assertSame(['ENDED-LATELY', 'ENDED-LONG-AGO'], $this->searchOrder());
        self::assertSame(['current' => 'ENDED-LATELY', 'history' => ['ENDED-LONG-AGO']], $this->organized()['secondary'] ?? null);
    }

    /**
     * @return list<mixed> policy numbers in search()'s order
     */
    private function searchOrder(): array
    {
        $policies = (new InsuranceService())->search(['pid' => new TokenSearchField('pid', [(string) self::PID])])->getData();
        self::assertIsArray($policies);
        $numbers = [];
        foreach ($policies as $policy) {
            $numbers[] = is_array($policy) ? ($policy['policy_number'] ?? null) : null;
        }
        return $numbers;
    }

    /**
     * @return array<mixed> by level: the current policy's number and the others'
     */
    private function organized(): array
    {
        $organized = [];
        foreach ((new InsuranceService())->getPoliciesOrganizedByTypeForPatientPid(self::PID) as $level => $policies) {
            self::assertIsArray($policies);
            $current = $policies['current'] ?? null;
            $history = [];
            foreach (is_array($policies['history'] ?? null) ? $policies['history'] : [] as $policy) {
                $history[] = is_array($policy) ? ($policy['policy_number'] ?? null) : null;
            }
            $organized[$level] = ['current' => is_array($current) ? ($current['policy_number'] ?? null) : null, 'history' => $history];
        }
        return $organized;
    }

    /**
     * Dates are days from today, so the policy in force doesn't depend on the day the test runs.
     */
    private function addPolicy(string $type, int $company, string $number, ?int $startDays, ?int $endDays = null): void
    {
        $day = static fn(?int $days): ?string => $days === null ? null : (new DateTimeImmutable())->modify("$days days")->format('Y-m-d');
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO insurance_data (uuid, pid, type, provider, policy_number, date, date_end, plan_name) VALUES (?, ?, ?, ?, ?, ?, ?, 'test-fixture')",
            [(new UuidRegistry(['table_name' => 'insurance_data']))->createUuid(), self::PID, $type, (string) $company, $number, $day($startDays), $day($endDays)]
        );
    }

    private function cleanUp(): void
    {
        foreach (['insurance_data', 'patient_data'] as $table) {
            foreach (QueryUtils::fetchTableColumn("SELECT uuid FROM `$table` WHERE pid = ? AND uuid IS NOT NULL", 'uuid', [self::PID]) as $uuid) {
                QueryUtils::sqlStatementThrowException('DELETE FROM uuid_registry WHERE table_name = ? AND uuid = ?', [$table, $uuid]);
            }
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [self::PID]);
    }
}
