<?php

/**
 * InsuranceService::getEffectiveInsurances() against the two readers it grew from,
 * on the same rows: the claim's payer list (Claim::loadPayerInfo(), which decides
 * who is billed) and getEffectiveInsurances() in library/patient.inc.php.
 *
 * Each case names what the claim and the old function choose. Where one of them
 * differs from the service, the case says so in its name.
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

    protected function tearDown(): void
    {
        $this->removePolicies();
        $this->fixtureManager->removePatientFixtures();
    }

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
            static fn(EffectiveInsurance $p): string => "{$p->level->value} {$p->companyId}",
            $this->service->getEffectiveInsurances($this->pid, new DateTimeImmutable($day))
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
    private function oldFunctionChoice(string $day): array
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
     * claim chooses, and what getEffectiveInsurances() chooses, on 2026-11-04.
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
            // The old function stops at the first level with nothing in force.
            'an ended primary and a current secondary (the old function differs)' => [
                [['primary', '101', '2020-01-01', '2026-07-07'], ['secondary', '104', '2020-01-01', null]],
                ['secondary 104'], ['secondary 104'], [],
            ],
            'a blank primary and a current secondary (the old function differs)' => [
                [['primary', '', null, null], ['secondary', '104', '2020-01-01', null]],
                ['secondary 104'], ['secondary 104'], [],
            ],
            'an ended secondary between a primary and a tertiary (the old function differs)' => [
                [['primary', '101', '2020-01-01', null], ['secondary', '102', '2020-01-01', '2026-01-01'], ['tertiary', '103', '2020-01-01', null]],
                ['primary 101', 'tertiary 103'], ['primary 101', 'tertiary 103'], ['primary 101'],
            ],
            // sql_mode = '' stores a mistyped date as 0000-00-00. The claim and the old function compare it
            // as a day in year 0, so the policy has ended; the service reads it as no end.
            'a zero end date (the claim and the old function differ)' => [
                [['primary', '101', '2020-01-01', '0000-00-00']],
                $current, [], [],
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
        self::assertSame($old, $this->oldFunctionChoice(self::DAY), 'getEffectiveInsurances()');
    }

    /**
     * Two undated policies at one level: the unique key on (pid, type, date) doesn't stop
     * them, since NULLs never collide. The claim and the old function take whichever row
     * the database returns first; the service takes the later entry.
     */
    #[Test]
    public function twoUndatedPoliciesAtOneLevelGoToTheLaterEntry(): void
    {
        $this->addPolicy('primary', '101', null, null);
        $this->addPolicy('primary', '102', null, null);

        self::assertSame(['primary 102'], $this->serviceChoice(self::DAY));
        self::assertContains($this->claimChoice(self::DAY), [['primary 101'], ['primary 102']]);
    }

    #[Test]
    public function aPolicyCarriesItsUuidAndDates(): void
    {
        $uuid = $this->addPolicy('primary', '101', '2026-01-01', '2026-12-31');

        $policies = $this->service->getEffectiveInsurances($this->pid, new DateTimeImmutable(self::DAY));

        self::assertCount(1, $policies);
        self::assertSame($uuid, $policies[0]->uuid);
        self::assertSame('2026-01-01', $policies[0]->start?->format('Y-m-d'));
        self::assertSame('2026-12-31', $policies[0]->end?->format('Y-m-d'));
    }

    #[Test]
    public function aPatientWithNoPoliciesHasNoCoverage(): void
    {
        self::assertSame([], $this->service->getEffectiveInsurances($this->pid, new DateTimeImmutable(self::DAY)));
    }
}
