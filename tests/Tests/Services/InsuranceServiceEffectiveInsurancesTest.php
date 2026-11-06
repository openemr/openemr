<?php

/**
 * InsuranceService::getEffectiveInsuranceRows() against the claim's payer list
 * (Claim::loadPayerInfo(), which decides who is billed), on the same rows, and
 * against getEffectiveInsurances() in library/patient.inc.php, now a wrapper over
 * it, which must choose as the service does in every case.
 *
 * Each case names what the claim and the wrapper choose. Where the claim differs
 * from the service, the case says so in its name; where the query the wrapper
 * replaced chose differently, the case says that too.
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
use OpenEMR\Billing\Claim;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Insurance\EffectiveInsurance;
use OpenEMR\Services\Insurance\EffectiveInsuranceRow;
use OpenEMR\Services\InsuranceService;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../library/patient.inc.php';

final class InsuranceServiceEffectiveInsurancesTest extends TestCase
{
    private const DAY = '2026-11-04';

    private InsuranceService $service;
    private FixtureManager $fixtureManager;
    private int $pid;

    /**
     * One fixture patient with no policies.
     */
    protected function setUp(): void
    {
        $this->service = new InsuranceService();
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
        $this->removePolicies();
    }

    /**
     * Removes the test's policies and the fixture patient.
     */
    protected function tearDown(): void
    {
        $this->removePolicies();
        $this->fixtureManager->removePatientFixtures();
    }

    /**
     * Deletes the fixture patient's policies with their uuid registry entries.
     */
    private function removePolicies(): void
    {
        $uuids = QueryUtils::fetchTableColumn(
            "SELECT `uuid` FROM `insurance_data` WHERE `pid` = ? AND `uuid` IS NOT NULL",
            'uuid',
            [$this->pid]
        );
        foreach ($uuids as $uuid) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM `uuid_registry` WHERE `table_name` = 'insurance_data' AND `uuid` = ?",
                [$uuid]
            );
        }
        QueryUtils::sqlStatementThrowException("DELETE FROM `insurance_data` WHERE `pid` = ?", [$this->pid]);
    }

    /**
     * Company ids 101-104 needn't exist: which policy is in force is read from insurance_data alone.
     *
     * @return string the policy's uuid
     */
    private function addPolicy(string $type, ?string $provider, ?string $start, ?string $end): string
    {
        $uuid = (new UuidRegistry(['table_name' => 'insurance_data']))->createUuid();
        QueryUtils::sqlInsert(
            "INSERT INTO `insurance_data` (`uuid`, `pid`, `type`, `provider`, `date`, `date_end`, `plan_name`)"
            . " VALUES (?, ?, ?, ?, ?, ?, 'test-fixture')",
            [$uuid, $this->pid, $type, $provider, $start, $end]
        );
        return UuidRegistry::uuidToString($uuid);
    }

    /**
     * @return list<string> each policy as "level company"
     */
    private function serviceChoice(string $day): array
    {
        return array_map(
            static fn(EffectiveInsuranceRow $row): string => "{$row->policy->level->value} {$row->policy->companyId}",
            $this->service->getEffectiveInsuranceRows($this->pid, new DateTimeImmutable($day))
        );
    }

    /**
     * The claim's payers for an encounter on $day, billed to no payer yet, in the order it bills them.
     * Claim's constructor loads an encounter, its billing rows, a facility and users; the payer choice
     * is loadPayerInfo() alone, so the claim here is built without its constructor.
     *
     * @return list<string> each payer as "level company"
     */
    private function claimChoice(string $day): array
    {
        $claim = new class extends Claim {
            /**
             * No encounter, billing rows, facility or users to load: only loadPayerInfo() is under test.
             */
            public function __construct()
            {
            }
        };
        $claim->pid = $this->pid;
        $claim->encounter = ['date' => $day . ' 00:00:00'];
        $billrow = ['payer_id' => 0, 'process_date' => null];
        $claim->loadPayerInfo($billrow);

        self::assertIsArray($claim->payers);
        $payers = [];
        foreach ($claim->payers as $payer) {
            self::assertIsArray($payer);
            $payers[] = $payer['data'] ?? null;
        }
        return self::levelsAndCompanies($payers);
    }

    /**
     * @return list<string> each row as "level company"
     */
    private function wrapperChoice(string $day): array
    {
        return self::levelsAndCompanies(getEffectiveInsurances($this->pid, $day));
    }

    /**
     * @param array<mixed> $rows insurance_data rows
     * @return list<string>
     */
    private static function levelsAndCompanies(array $rows): array
    {
        $chosen = [];
        foreach ($rows as $row) {
            self::assertIsArray($row);
            $type = $row['type'] ?? null;
            $provider = $row['provider'] ?? null;
            self::assertIsString($type);
            self::assertIsString($provider);
            $chosen[] = "$type $provider";
        }
        return $chosen;
    }

    /**
     * Each case: the rows (type, company, start, end), what the service chooses, what the
     * claim chooses, and what the getEffectiveInsurances() wrapper chooses, on 2026-11-04.
     *
     * @return array<string, array{list<array{string, ?string, ?string, ?string}>, list<string>, list<string>, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function cases(): array
    {
        $current = ['primary 101'];
        return [
            'no dates' => [[['primary', '101', null, null]], $current, $current, $current],
            'in force on its first and last day' => [[['primary', '101', '2026-11-04', '2026-11-04']], $current, $current, $current],
            'ended the day before' => [[['primary', '101', '2020-01-01', '2026-11-03']], [], [], []],
            'starts the day after' => [[['primary', '101', '2026-11-05', null]], [], [], []],
            'next year\'s card entered in advance' => [
                [['primary', '101', '2020-01-01', null], ['primary', '102', '2027-01-01', null]],
                $current, $current, $current,
            ],
            'a change of insurer in July' => [
                [['primary', '101', '2020-01-01', '2026-07-07'], ['primary', '102', '2026-07-08', null]],
                ['primary 102'], ['primary 102'], ['primary 102'],
            ],
            'the new-patient screen\'s blank row, then a policy' => [
                [['primary', '', null, null], ['primary', '101', '2024-01-01', null]],
                $current, $current, $current,
            ],
            'an Unassigned row ends the level' => [
                [['primary', '101', '2020-01-01', null], ['primary', '', '2026-07-01', null]],
                [], [], [],
            ],
            'portal registration\'s company 0 ends the level' => [
                [['primary', '101', '2020-01-01', null], ['primary', '0', '2026-07-01', null]],
                [], [], [],
            ],
            'a zero start means always' => [[['primary', '101', '0000-00-00', null]], $current, $current, $current],
            // Entered first, so the lower id: the zero start wins on its date, not its id.
            'a zero start beats an undated row at its level' => [
                [['primary', '101', '0000-00-00', null], ['primary', '102', null, null]],
                $current, $current, $current,
            ],
            'all three levels' => [
                [['primary', '101', '2020-01-01', null], ['secondary', '102', '2020-01-01', null], ['tertiary', '103', null, null]],
                ['primary 101', 'secondary 102', 'tertiary 103'],
                ['primary 101', 'secondary 102', 'tertiary 103'],
                ['primary 101', 'secondary 102', 'tertiary 103'],
            ],
            'a secondary that starts next year' => [
                [['primary', '101', '2020-01-01', null], ['secondary', '102', '2027-01-01', null]],
                $current, $current, $current,
            ],
            // The query the wrapper replaced stopped at the first level with nothing in force.
            'an ended primary and a current secondary (the replaced query stopped)' => [
                [['primary', '101', '2020-01-01', '2026-07-07'], ['secondary', '104', '2020-01-01', null]],
                ['secondary 104'], ['secondary 104'], ['secondary 104'],
            ],
            'a blank primary and a current secondary (the replaced query stopped)' => [
                [['primary', '', null, null], ['secondary', '104', '2020-01-01', null]],
                ['secondary 104'], ['secondary 104'], ['secondary 104'],
            ],
            'an ended secondary between a primary and a tertiary (the replaced query stopped)' => [
                [['primary', '101', '2020-01-01', null], ['secondary', '102', '2020-01-01', '2026-01-01'], ['tertiary', '103', '2020-01-01', null]],
                ['primary 101', 'tertiary 103'], ['primary 101', 'tertiary 103'], ['primary 101', 'tertiary 103'],
            ],
            // sql_mode = '' stores a mistyped date as 0000-00-00. The claim (and the replaced query) compare it
            // as a day in year 0, so the policy has ended; the service and its wrapper read it as no end.
            'a zero end date (the claim differs)' => [
                [['primary', '101', '2020-01-01', '0000-00-00']],
                $current, [], $current,
            ],
        ];
    }

    /**
     * @param list<array{string, ?string, ?string, ?string}> $rows
     * @param list<string> $service
     * @param list<string> $claim
     * @param list<string> $old
     */
    #[Test]
    #[DataProvider('cases')]
    public function theServiceMatchesTheClaimExceptWhereNamed(array $rows, array $service, array $claim, array $old): void
    {
        foreach ($rows as [$type, $provider, $start, $end]) {
            $this->addPolicy($type, $provider, $start, $end);
        }

        self::assertSame($service, $this->serviceChoice(self::DAY), 'the service');
        self::assertSame($claim, $this->claimChoice(self::DAY), 'the claim');
        self::assertSame($old, $this->wrapperChoice(self::DAY), 'getEffectiveInsurances()');
        self::assertSame($service, $old, 'the wrapper chooses as the service does');
    }

    /**
     * A policy ending on the day still covers a visit late that day, and the wrapper returns its stored row.
     */
    #[Test]
    public function theWrapperReturnsEachPolicysRowAndReadsADatetimeAsItsDay(): void
    {
        $this->addPolicy('primary', '101', '2026-01-01', '2026-11-04');

        $rows = getEffectiveInsurances($this->pid, self::DAY . ' 23:59:59');

        self::assertCount(1, $rows);
        self::assertSame('primary', $rows[0]['type']);
        self::assertSame('2026-11-04', $rows[0]['date_end'], 'every insurance_data column, as stored');
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unreadableDays(): array
    {
        return ['blank' => [''], 'zero' => ['0000-00-00'], 'a zero datetime' => ['0000-00-00 00:00:00'], 'the display format' => ['11/04/2026']];
    }

    /**
     * Even an undated policy, in force on any day, isn't returned for a date the wrapper can't read.
     */
    #[Test]
    #[DataProvider('unreadableDays')]
    public function theWrapperGivesNoPoliciesForADayItCantRead(string $day): void
    {
        $this->addPolicy('primary', '101', null, null);

        self::assertSame([], getEffectiveInsurances($this->pid, $day));
    }

    /**
     * Two undated policies at one level: the unique key on (pid, type, date) doesn't stop
     * them, since NULLs never collide. The claim takes whichever row the database returns
     * first (as the query the wrapper replaced did); the service and its wrapper take the
     * later entry.
     */
    #[Test]
    public function twoUndatedPoliciesAtOneLevelGoToTheLaterEntry(): void
    {
        $this->addPolicy('primary', '101', null, null);
        $this->addPolicy('primary', '102', null, null);

        self::assertSame(['primary 102'], $this->serviceChoice(self::DAY));
        self::assertSame(['primary 102'], $this->wrapperChoice(self::DAY));
        self::assertContains($this->claimChoice(self::DAY), [['primary 101'], ['primary 102']]);
    }

    /**
     * The service's policy carries the uuid the row was saved with and its dates.
     */
    #[Test]
    public function aPolicyCarriesItsUuidAndDates(): void
    {
        $uuid = $this->addPolicy('primary', '101', '2026-01-01', '2026-12-31');

        $policies = array_map(
            static fn(EffectiveInsuranceRow $row): EffectiveInsurance => $row->policy,
            $this->service->getEffectiveInsuranceRows($this->pid, new DateTimeImmutable(self::DAY))
        );

        self::assertCount(1, $policies);
        self::assertSame($uuid, $policies[0]->uuid);
        self::assertSame('2026-01-01', $policies[0]->start?->format('Y-m-d'));
        self::assertSame('2026-12-31', $policies[0]->end?->format('Y-m-d'));
    }

    /**
     * No policies give an empty list, not an error.
     */
    #[Test]
    public function aPatientWithNoPoliciesHasNoCoverage(): void
    {
        self::assertSame([], $this->service->getEffectiveInsuranceRows($this->pid, new DateTimeImmutable(self::DAY)));
    }
}
