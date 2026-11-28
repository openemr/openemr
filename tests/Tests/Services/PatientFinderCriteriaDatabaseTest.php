<?php

/**
 * The Patient Finder's age, sex and last-visit criteria run against stored patient_data and
 * form_encounter rows, to check the SQL each one writes picks the patients its label describes:
 * the birthday boundary for age, "not recorded" for sex, and which visits count as the last one.
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
use OpenEMR\Services\PatientFinder\Criteria\SexCriterion;
use OpenEMR\Services\PatientFinder\PatientFinderCriteria;
use OpenEMR\Tests\Fixtures\FixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PatientFinderCriteriaDatabaseTest extends TestCase
{
    /** Encounter numbers well above what the test database hands out. */
    private const FIRST_ENCOUNTER = 990000001;

    private FixtureManager $fixtureManager;
    private int $pid;
    private DateTimeImmutable $today;
    private int $encounters = 0;

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

        $today = QueryUtils::fetchSingleValue('SELECT CURDATE() AS `today`', 'today');
        self::assertIsString($today);
        $this->today = new DateTimeImmutable($today);
        $this->removeEncounters();
    }

    protected function tearDown(): void
    {
        $this->removeEncounters();
        $this->fixtureManager->removePatientFixtures();
    }

    private function removeEncounters(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM `form_encounter` WHERE `pid` = ?", [$this->pid]);
    }

    /**
     * The database's today moved by a relative date, e.g. "-18 years +1 day". Whole years
     * go first and keep to the end of the month the way MySQL's DATE_SUB() does, which the
     * age criterion uses: on 29 February, PHP's "-18 years" would give 1 March instead.
     */
    private function day(string $modifier): string
    {
        $date = $this->today;
        if (preg_match('/^([+-]\d+) years?(.*)$/', $modifier, $match) === 1) {
            $year = (int) $date->format('Y') + (int) $match[1];
            $month = (int) $date->format('n');
            $lastDay = (int) $date->setDate($year, $month, 1)->format('t');
            $date = $date->setDate($year, $month, min((int) $date->format('j'), $lastDay));
            $modifier = trim($match[2]);
        }
        return ($modifier === '' ? $date : $date->modify($modifier))->format('Y-m-d');
    }

    /**
     * The criteria read the date from CURDATE() each time they run, so a test that runs past
     * midnight would set its rows for one day and filter them on the next.
     */
    private function assertSameDay(): void
    {
        self::assertSame(
            $this->today->format('Y-m-d'),
            QueryUtils::fetchSingleValue('SELECT CURDATE() AS `today`', 'today'),
            'the date changed while the test ran; run it again'
        );
    }

    private function setPatient(string $column, ?string $value): void
    {
        QueryUtils::sqlStatementThrowException(
            "UPDATE `patient_data` SET `$column` = ? WHERE `pid` = ?",
            [$value, $this->pid]
        );
    }

    private function addVisit(string $dateTime): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO `form_encounter` (`date`, `reason`, `pid`, `encounter`) VALUES (?, 'test-fixture', ?, ?)",
            [$dateTime, $this->pid, self::FIRST_ENCOUNTER + $this->encounters++]
        );
    }

    /**
     * Whether the finder keeps the fixture patient under one filter.
     *
     * @param string|list<string> $value
     */
    private function finderMatches(string $key, string $operator, string|array $value): bool
    {
        $criteria = PatientFinderCriteria::fromArray([
            'criteria' => Json::encode([['key' => $key, 'operator' => $operator, 'value' => $value]]),
        ]);
        self::assertFalse($criteria->isEmpty(), 'the filter is usable');
        [$where, $binds] = $criteria->toWhere();
        $found = QueryUtils::fetchSingleValue(
            'SELECT `pid` FROM `patient_data` WHERE `pid` = ? AND ' . $where,
            'pid',
            [$this->pid, ...$binds]
        );
        $this->assertSameDay();
        return $found !== null && $found !== false;
    }

    /**
     * Each case: the date of birth as a change to today (null = none), the filter, and whether the
     * patient is kept.
     *
     * @return array<string, array{?string, string, string|list<string>, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function ageCases(): array
    {
        return [
            'eighteen today is at least 18' => ['-18 years', 'at_least', '18', true],
            'eighteen today is not at most 17' => ['-18 years', 'at_most', '17', false],
            'eighteen today is between 18 and 18' => ['-18 years', 'between', ['18', '18'], true],
            'eighteen tomorrow is not at least 18' => ['-18 years +1 day', 'at_least', '18', false],
            'eighteen tomorrow is at most 17' => ['-18 years +1 day', 'at_most', '17', true],
            'eighteen tomorrow is between 10 and 17' => ['-18 years +1 day', 'between', ['10', '17'], true],
            'seventy is not between 18 and 64' => ['-70 years', 'between', ['18', '64'], false],
            'seventy is in a range given high end first' => ['-70 years', 'between', ['80', '65'], true],
            'born today is at most 0' => ['+0 days', 'at_most', '0', true],
            'no date of birth is not at most 150' => [null, 'at_most', '150', false],
            'no date of birth is not at least 0' => [null, 'at_least', '0', false],
            'a date of birth after today is not at most 150' => ['+1 day', 'at_most', '150', false],
        ];
    }

    /**
     * @param string|list<string> $value
     */
    #[Test]
    #[DataProvider('ageCases')]
    public function ageCountsWholeYearsSinceTheDateOfBirth(?string $born, string $operator, string|array $value, bool $kept): void
    {
        $this->setPatient('DOB', $born === null ? null : $this->day($born));

        self::assertSame($kept, $this->finderMatches('age', $operator, $value));
    }

    /**
     * Each case: the stored sex, the choice, and whether the patient is kept.
     *
     * @return array<string, array{string, string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function sexCases(): array
    {
        return [
            'the stored value' => ['Female', 'Female', true],
            'another value' => ['Female', 'Male', false],
            'nothing stored is not recorded' => ['', SexCriterion::NOT_RECORDED, true],
            'a stored value is recorded' => ['Male', SexCriterion::NOT_RECORDED, false],
        ];
    }

    #[Test]
    #[DataProvider('sexCases')]
    public function sexMatchesTheStoredValueOrNotRecorded(string $stored, string $choice, bool $kept): void
    {
        $this->setPatient('sex', $stored);

        self::assertSame($kept, $this->finderMatches('sex', 'is', $choice));
    }

    /**
     * Each case: visits as (change to today, time of day), and whether the patient is kept for
     * "within the last 30 days", "more than 30 days ago" and "never".
     *
     * @return array<string, array{list<array{string, string}>, bool, bool, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function visitCases(): array
    {
        return [
            'no visits' => [[], false, false, true],
            'seen this morning' => [[['+0 days', '08:00:00']], true, false, false],
            'seen late tonight' => [[['+0 days', '23:59:59']], true, false, false],
            'seen 30 days ago' => [[['-30 days', '00:00:00']], true, false, false],
            'seen 31 days ago' => [[['-31 days', '23:59:59']], false, true, false],
            'seen long ago and recently' => [[['-400 days', '09:00:00'], ['-3 days', '09:00:00']], true, false, false],
            'a visit booked for tomorrow is not a visit' => [[['+1 day', '00:00:00']], false, false, true],
            'seen long ago with a visit booked for tomorrow' => [[['-400 days', '09:00:00'], ['+1 day', '09:00:00']], false, true, false],
        ];
    }

    /**
     * @param list<array{string, string}> $visits
     */
    #[Test]
    #[DataProvider('visitCases')]
    public function lastVisitCountsOnlyVisitsThatHaveHappened(array $visits, bool $within, bool $older, bool $never): void
    {
        foreach ($visits as [$day, $time]) {
            $this->addVisit($this->day($day) . ' ' . $time);
        }

        self::assertSame($within, $this->finderMatches('last_visit', 'within', '30'), 'within the last 30 days');
        self::assertSame($older, $this->finderMatches('last_visit', 'older', '30'), 'more than 30 days ago');
        self::assertSame($never, $this->finderMatches('last_visit', 'never', ''), 'never');
    }
}
