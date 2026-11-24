<?php

/**
 * The Patient Finder's primary-insurance criterion ("has a current policy" / "has no current policy")
 * against EffectiveInsuranceRule::choose(), on the same insurance_data rows. The criterion is SQL so it
 * can sit in the finder's WHERE; the rule decides who is covered everywhere else (claims, the fee sheet),
 * so both must put each patient on the same side.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use DateTimeImmutable;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Utils\Json;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Insurance\EffectiveInsuranceRule;
use OpenEMR\Services\PatientFinder\PatientFinderCriteria;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PatientFinderPrimaryInsuranceTest extends TestCase
{
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

    private function addPolicy(?string $provider, ?string $start, ?string $end, string $type = 'primary'): void
    {
        $uuid = (new UuidRegistry(['table_name' => 'insurance_data']))->createUuid();
        QueryUtils::sqlInsert(
            "INSERT INTO `insurance_data` (`uuid`, `pid`, `type`, `provider`, `date`, `date_end`, `plan_name`)"
            . " VALUES (?, ?, ?, ?, ?, ?, 'test-fixture')",
            [$uuid, $this->pid, $type, $provider, $start, $end]
        );
    }

    /**
     * Whether the finder puts the fixture patient in the group for $operator ('has' or 'none').
     */
    private function finderMatches(string $operator): bool
    {
        $criteria = PatientFinderCriteria::fromArray([
            'criteria' => Json::encode([['key' => 'insurance', 'operator' => $operator, 'value' => '']]),
        ]);
        [$where, $binds] = $criteria->toWhere();
        $found = QueryUtils::fetchSingleValue(
            'SELECT `pid` FROM `patient_data` WHERE `pid` = ? AND ' . $where,
            'pid',
            [$this->pid, ...$binds]
        );
        return $found !== null && $found !== false;
    }

    /**
     * Whether the rule finds a primary policy in force, over the rows the test stored, on the database's today.
     */
    private function ruleCovers(): bool
    {
        $today = QueryUtils::fetchSingleValue('SELECT CURDATE() AS `today`', 'today');
        self::assertIsString($today);
        $rows = QueryUtils::fetchRecords(
            'SELECT `id`, `type`, `provider`, `date`, `date_end` FROM `insurance_data` WHERE `pid` = ?',
            [$this->pid]
        );
        foreach (EffectiveInsuranceRule::choose($rows, new DateTimeImmutable($today)) as $policy) {
            if ($policy->level->value === 'primary') {
                return true;
            }
        }
        return false;
    }

    /**
     * Each case: primary policies as (company, start, end) in insertion order (a later row has a higher id),
     * with dates as offsets in days from today (null = blank, 'zero' = 0000-00-00), and whether the patient
     * is covered.
     *
     * @return array<string, array{list<array{?string, int|string|null, int|string|null}>, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function cases(): array
    {
        return [
            'no policy' => [[], false],
            'open policy' => [[['101', null, null]], true],
            'started yesterday, ends next year' => [[['101', -1, 365]], true],
            'ended yesterday' => [[['101', -400, -1]], false],
            'not started yet' => [[['101', 30, null]], false],
            'zero end means open' => [[['101', -30, 'zero']], true],
            'blank company' => [[['', null, null]], false],
            'zero company' => [[['0', null, null]], false],
            'zero-padded zero company' => [[['00', null, null]], false],
            'company with spaces' => [[[' 101 ', null, null]], true],
            'company not a number' => [[['abc', null, null]], false],
            'later policy replaces an earlier one' => [[['101', -400, null], ['102', -30, null]], true],
            'later policy with no company leaves none' => [[['101', -400, null], ['', -30, null]], false],
            'later policy not started yet does not replace' => [[['101', -400, null], ['', 30, null]], true],
            'later policy already ended does not replace' => [[['101', -400, null], ['', -60, -30]], true],
            'newest start wins over a higher id' => [[['', -30, null], ['101', -400, null]], false],
            'same start: the higher id wins' => [[['101', -30, null], ['', -30, null]], false],
            'a blank start ranks below a zero start' => [[['101', 'zero', null], ['', null, null]], true],
            'a zero start ranks above a blank start' => [[['', null, null], ['101', 'zero', null]], true],
        ];
    }

    /**
     * @param list<array{?string, int|string|null, int|string|null}> $policies
     */
    #[Test]
    #[DataProvider('cases')]
    public function finderAgreesWithTheRule(array $policies, bool $covered): void
    {
        $today = QueryUtils::fetchSingleValue('SELECT CURDATE() AS `today`', 'today');
        self::assertIsString($today);
        $date = static function (int|string|null $offset) use ($today): ?string {
            if ($offset === null) {
                return null;
            }
            if ($offset === 'zero') {
                return '0000-00-00';
            }
            return (new DateTimeImmutable($today))->modify(sprintf('%+d days', $offset))->format('Y-m-d');
        };
        foreach ($policies as [$company, $start, $end]) {
            $this->addPolicy($company, $date($start), $date($end));
        }
        // A secondary policy never makes a patient covered here.
        $this->addPolicy('999', null, null, 'secondary');

        self::assertSame($covered, $this->ruleCovers(), 'the rule');
        self::assertSame($covered, $this->finderMatches('has'), 'the finder, has a current policy');
        self::assertSame(!$covered, $this->finderMatches('none'), 'the finder, has no current policy');
    }
}
