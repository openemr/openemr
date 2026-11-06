<?php

/**
 * InsuranceService::getEffectiveInsuranceRows(): each policy in force with its stored row and its
 * company's name. Which policies it chooses is the comparison test's subject
 * (InsuranceServiceEffectiveInsurancesTest).
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
use OpenEMR\Services\Insurance\InsuranceLevel;
use OpenEMR\Services\InsuranceService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InsuranceServiceCoverageReadsTest extends TestCase
{
    private const DAY = '2026-11-04';
    private const COMPANY_ID = 970101;
    private const COMPANY_NAME = 'test-fixture Synthetic Mutual';

    private InsuranceService $service;
    private FixtureManager $fixtureManager;
    /** @var list<int> four fixture patients */
    private array $pids = [];

    /**
     * Four fixture patients with no policies.
     */
    protected function setUp(): void
    {
        $this->service = new InsuranceService();
        $this->fixtureManager = new FixtureManager();
        $this->fixtureManager->installPatientFixtures();

        foreach (
            QueryUtils::fetchTableColumn(
                "SELECT `pid` FROM `patient_data` WHERE `pubpid` LIKE ? ORDER BY `pid` LIMIT 4",
                'pid',
                [FixtureManager::PATIENT_FIXTURE_PUBPID_PREFIX . '%']
            ) as $pid
        ) {
            if (!is_int($pid)) {
                self::fail('a fixture pid that is not an int');
            }
            $this->pids[] = $pid;
        }
        self::assertCount(4, $this->pids, 'four patient fixtures');
        $this->removeRows();
    }

    /**
     * Removes the test's policies, its company and the fixture patients.
     */
    protected function tearDown(): void
    {
        $this->removeRows();
        $this->fixtureManager->removePatientFixtures();
    }

    /**
     * Deletes the fixture patients' policies with their uuid registry entries, and the one company the test adds.
     */
    private function removeRows(): void
    {
        $placeholders = implode(',', array_fill(0, count($this->pids), '?'));
        $uuids = QueryUtils::fetchTableColumn(
            "SELECT `uuid` FROM `insurance_data` WHERE `pid` IN ($placeholders) AND `uuid` IS NOT NULL",
            'uuid',
            $this->pids
        );
        foreach ($uuids as $uuid) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM `uuid_registry` WHERE `table_name` = 'insurance_data' AND `uuid` = ?",
                [$uuid]
            );
        }
        QueryUtils::sqlStatementThrowException("DELETE FROM `insurance_data` WHERE `pid` IN ($placeholders)", $this->pids);
        // Only the company this test adds: its id and its name.
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `insurance_companies` WHERE `id` = ? AND `name` = ?",
            [self::COMPANY_ID, self::COMPANY_NAME]
        );
    }

    /**
     * Company ids other than COMPANY_ID needn't exist: which policy is in force is read from insurance_data alone.
     *
     * @param array<string, string> $columns more insurance_data columns
     * @return int the policy's id
     */
    private function addPolicy(int $pid, string $type, ?string $provider, ?string $start, ?string $end, array $columns = []): int
    {
        $values = array_merge(['uuid' => (new UuidRegistry(['table_name' => 'insurance_data']))->createUuid(), 'pid' => $pid,
            'type' => $type, 'provider' => $provider, 'date' => $start, 'date_end' => $end, 'plan_name' => 'test-fixture'], $columns);
        $names = implode(', ', array_map(static fn(string $name): string => "`$name`", array_keys($values)));
        return QueryUtils::sqlInsert(
            "INSERT INTO `insurance_data` ($names) VALUES (" . implode(', ', array_fill(0, count($values), '?')) . ")",
            array_values($values)
        );
    }

    /**
     * The day every read in this test asks about, unless a test names another.
     */
    private function day(): DateTimeImmutable
    {
        return new DateTimeImmutable(self::DAY);
    }

    /**
     * A row comes back with every insurance_data column as stored and its company's name, an inactive company's included.
     */
    #[Test]
    public function aRowCarriesItsColumnsAndItsCompanysName(): void
    {
        [$pid] = $this->pids;
        QueryUtils::sqlInsert("INSERT INTO `insurance_companies` (`id`, `name`) VALUES (?, ?)", [self::COMPANY_ID, self::COMPANY_NAME]);
        $primary = $this->addPolicy($pid, 'primary', (string) self::COMPANY_ID, '2026-01-01', null, [
            'plan_name' => 'Synthetic Gold', 'policy_number' => 'SYN-0001', 'subscriber_lname' => 'Testcase', 'subscriber_fname' => 'Pat',
        ]);
        $this->addPolicy($pid, 'primary', (string) self::COMPANY_ID, '2025-01-01', '2025-12-31', ['plan_name' => 'Synthetic Bronze']);
        $secondary = $this->addPolicy($pid, 'secondary', '102', '2020-01-01', null);

        $rows = $this->service->getEffectiveInsuranceRows($pid, $this->day());

        self::assertCount(2, $rows);
        [$first, $second] = $rows;
        self::assertSame(InsuranceLevel::Primary, $first->policy->level);
        self::assertSame($primary, $first->policy->id);
        self::assertSame(self::COMPANY_NAME, $first->companyName);
        self::assertSame($primary, $first->columns['id']);
        self::assertSame('Synthetic Gold', $first->columns['plan_name']);
        self::assertSame('SYN-0001', $first->columns['policy_number']);
        self::assertSame('Testcase', $first->columns['subscriber_lname']);
        self::assertSame('Pat', $first->columns['subscriber_fname']);

        self::assertSame(InsuranceLevel::Secondary, $second->policy->level);
        self::assertSame($secondary, $second->columns['id']);
        self::assertNull($second->companyName, 'company 102 has no row');
    }
}
