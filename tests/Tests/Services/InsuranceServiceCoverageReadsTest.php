<?php

/**
 * The reads InsuranceService builds on getEffectiveInsurances(): the policies with their rows and
 * company names, many patients' rows in one query, the runs of days over a range, and one level.
 * Each agrees with getEffectiveInsurances() on the same rows.
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
use OpenEMR\Services\Insurance\CoverageSegment;
use OpenEMR\Services\Insurance\EffectiveInsurance;
use OpenEMR\Services\Insurance\EffectiveInsuranceRow;
use OpenEMR\Services\Insurance\EffectiveInsuranceRule;
use OpenEMR\Services\Insurance\InsuranceLevel;
use OpenEMR\Services\InsuranceService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\DataProviderExternal;
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

    protected function tearDown(): void
    {
        $this->removeRows();
        $this->fixtureManager->removePatientFixtures();
    }

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
     * @param list<EffectiveInsurance> $policies
     * @return list<string> each policy as "level #id company start..end uuid"
     */
    private static function described(array $policies): array
    {
        return array_map(
            static fn(EffectiveInsurance $p): string => "{$p->level->value} #{$p->id} {$p->companyId} "
                . ($p->start?->format('Y-m-d') ?? '-') . '..' . ($p->end?->format('Y-m-d') ?? '-') . ' ' . ($p->uuid ?? 'no uuid'),
            $policies
        );
    }

    private function day(): DateTimeImmutable
    {
        return new DateTimeImmutable(self::DAY);
    }

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

    /**
     * Every case of the comparison test, through each read: they choose as getEffectiveInsurances() does.
     *
     * @param list<array{string, ?string, ?string, ?string}> $rows
     * @param list<string> $service what getEffectiveInsurances() chooses (asserted in that test)
     * @param list<string> $claim   what the claim chooses (asserted in that test, not here)
     * @param list<string> $old     what the procedural function chooses (asserted in that test, not here)
     */
    #[Test]
    #[DataProviderExternal(InsuranceServiceEffectiveInsurancesTest::class, 'cases')]
    public function everyReadChoosesAsGetEffectiveInsurancesDoes(array $rows, array $service, array $claim, array $old): void
    {
        [$pid] = $this->pids;
        foreach ($rows as [$type, $provider, $start, $end]) {
            $this->addPolicy($pid, $type, $provider, $start, $end);
        }
        $expected = $this->service->getEffectiveInsurances($pid, $this->day());
        self::assertCount(count($service), $expected);

        self::assertSame(
            self::described($expected),
            self::described(array_map(static fn(EffectiveInsuranceRow $r): EffectiveInsurance => $r->policy, $this->service->getEffectiveInsuranceRows($pid, $this->day()))),
            'the rows'
        );
        self::assertSame(
            self::described($expected),
            self::described(EffectiveInsuranceRule::choose($this->service->getRuleRowsByPid([$pid])[$pid] ?? [], $this->day())),
            'the batch'
        );
        foreach (InsuranceLevel::cases() as $level) {
            $atLevel = array_values(array_filter($expected, static fn(EffectiveInsurance $p): bool => $p->level === $level));
            $found = $this->service->getEffectiveInsuranceAt($pid, $level, $this->day());
            self::assertSame(self::described($atLevel), self::described($found === null ? [] : [$found]), "the {$level->value} lookup");
            self::assertSame(
                self::described($atLevel),
                self::described(array_map(
                    static fn(CoverageSegment $s): EffectiveInsurance => $s->policy,
                    $this->service->getCoverageSegments($pid, $level, $this->day(), $this->day())
                )),
                "the {$level->value} runs on the day"
            );
        }
    }

    #[Test]
    public function manyPatientsComeBackInOneQuery(): void
    {
        [$a, $b, $c, $none] = $this->pids;
        $this->addPolicy($a, 'primary', '101', '2020-01-01', null);
        $this->addPolicy($b, 'primary', '102', '2020-01-01', '2026-06-30');
        $this->addPolicy($b, 'primary', '103', '2026-07-01', null);
        $this->addPolicy($b, 'secondary', '104', null, null);
        $this->addPolicy($c, 'primary', '', '2026-01-01', null);

        $byPid = $this->service->getRuleRowsByPid([$a, $b, $c, $none, $a]);

        $pids = array_keys($byPid);
        sort($pids);
        self::assertSame([$a, $b, $c], $pids, 'a patient with no rows has no key');
        self::assertCount(1, $byPid[$a]);
        self::assertCount(3, $byPid[$b], 'each row once, though the pid was asked twice');
        foreach ([$a, $b, $c, $none] as $pid) {
            self::assertSame(
                self::described($this->service->getEffectiveInsurances($pid, $this->day())),
                self::described(EffectiveInsuranceRule::choose($byPid[$pid] ?? [], $this->day())),
                "patient $pid"
            );
        }
        self::assertSame([], $this->service->getRuleRowsByPid([]));

        $one = self::statements(fn(): array => $this->service->getRuleRowsByPid([$a]));
        $four = self::statements(fn(): array => $this->service->getRuleRowsByPid([$a, $b, $c, $none]));
        $perPatient = self::statements(function () use ($a, $b, $c, $none): void {
            foreach ([$a, $b, $c, $none] as $pid) {
                $this->service->getEffectiveInsurances($pid, $this->day());
            }
        });
        self::assertSame($one, $four, 'four patients cost what one does');
        self::assertGreaterThan($four, $perPatient, 'control: a read per patient costs more, so the count can tell');
    }

    /**
     * The statements this session sends while $read runs (the counter read itself included, the same each time).
     */
    private static function statements(callable $read): int
    {
        $before = QueryUtils::querySingleRow("SHOW SESSION STATUS LIKE 'Questions'");
        $read();
        $after = QueryUtils::querySingleRow("SHOW SESSION STATUS LIKE 'Questions'");
        $value = static function (mixed $row): int {
            $count = is_array($row) ? filter_var($row['Value'] ?? null, FILTER_VALIDATE_INT) : false;
            return is_int($count) ? $count : self::fail('no Questions counter');
        };
        return $value($after) - $value($before);
    }

    #[Test]
    public function aLevelLookupFindsThePolicyAtThatLevelOnly(): void
    {
        [$pid] = $this->pids;
        $this->addPolicy($pid, 'primary', '101', '2020-01-01', '2026-07-07');
        $secondary = $this->addPolicy($pid, 'secondary', '104', '2020-01-01', null);

        self::assertNull($this->service->getEffectiveInsuranceAt($pid, InsuranceLevel::Primary, $this->day()), 'the primary has ended');
        self::assertSame($secondary, $this->service->getEffectiveInsuranceAt($pid, InsuranceLevel::Secondary, $this->day())?->id);
        self::assertNull($this->service->getEffectiveInsuranceAt($pid, InsuranceLevel::Tertiary, $this->day()));
        self::assertSame(
            101,
            $this->service->getEffectiveInsuranceAt($pid, InsuranceLevel::Primary, new DateTimeImmutable('2026-07-07'))?->companyId
        );
    }

    #[Test]
    public function runsComeFromThePatientsOwnRowsOverTheRange(): void
    {
        [$pid, $other, , $none] = $this->pids;
        $this->addPolicy($pid, 'primary', '101', '2020-01-01', '2026-03-31');
        $this->addPolicy($pid, 'primary', '102', '2026-04-01', null);
        $this->addPolicy($other, 'primary', '109', '2026-02-01', null);

        $runs = array_map(
            static fn(CoverageSegment $s): string => $s->from->format('Y-m-d') . '..' . $s->to->format('Y-m-d') . " {$s->policy->companyId}",
            $this->service->getCoverageSegments($pid, InsuranceLevel::Primary, new DateTimeImmutable('2026-01-01'), new DateTimeImmutable('2026-12-31'))
        );

        self::assertSame(['2026-01-01..2026-03-31 101', '2026-04-01..2026-12-31 102'], $runs);
        self::assertSame(
            [],
            $this->service->getCoverageSegments($none, InsuranceLevel::Primary, new DateTimeImmutable('2026-01-01'), new DateTimeImmutable('2026-12-31'))
        );
    }
}
