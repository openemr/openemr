<?php

/**
 * Isolated PatientFinderCriteria Test
 *
 * Tests how the Patient Finder's filters (sex, age, primary insurance, last visit) are read from
 * the request, how each criterion turns its operator and value into a WHERE fragment with bind
 * values, what the filter panel is told about them, and that they reach the query builder's data
 * and filtered-count queries but not the total.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PatientFinder;

use OpenEMR\Events\BoundFilter;
use OpenEMR\Services\PatientFinder\Criteria\PatientFinderCriteriaRegistry;
use OpenEMR\Services\PatientFinder\Criteria\SexCriterion;
use OpenEMR\Services\PatientFinder\PatientFinderCriteria;
use OpenEMR\Services\PatientFinder\PatientFinderQueryBuilder;
use OpenEMR\Services\PatientFinder\PatientFinderRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PatientFinderCriteriaTest extends TestCase
{
    /**
     * @param list<array<string, mixed>> $filters
     * @return array<string, string>
     */
    private static function request(array $filters): array
    {
        return ['criteria' => json_encode($filters, JSON_THROW_ON_ERROR)];
    }

    /**
     * @param list<array<string, mixed>> $filters
     * @return array{0: string, 1: list<string|int>}
     */
    private static function where(array $filters): array
    {
        return PatientFinderCriteria::fromArray(self::request($filters))->toWhere();
    }

    public function testNoParameterMeansNoFilters(): void
    {
        $criteria = PatientFinderCriteria::fromArray([]);

        $this->assertTrue($criteria->isEmpty());
        $this->assertSame(['', []], $criteria->toWhere());
        $this->assertTrue(PatientFinderRequest::fromArray([])->criteria->isEmpty());
    }

    public function testRequestCarriesTheFilters(): void
    {
        $request = PatientFinderRequest::fromArray(
            self::request([['key' => 'sex', 'operator' => 'is', 'value' => 'Female']])
        );

        $this->assertFalse($request->criteria->isEmpty());
        $this->assertSame('sex', $request->criteria->filters[0]['criterion']->key());
        $this->assertSame(['Female'], $request->criteria->filters[0]['values']);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function unusableRequests(): array
    {
        $bad = [
            'not json' => 'not json',
            'json scalar' => '"sex"',
            'unknown key' => '[{"key":"height","operator":"is","value":"1"}]',
            'unknown operator' => '[{"key":"sex","operator":"is_not","value":"Male"}]',
            'sex without a value' => '[{"key":"sex","operator":"is","value":""}]',
            'age not a number' => '[{"key":"age","operator":"at_least","value":"abc"}]',
            'negative age' => '[{"key":"age","operator":"at_least","value":"-5"}]',
            'age over the maximum' => '[{"key":"age","operator":"at_most","value":"151"}]',
            'range with one end' => '[{"key":"age","operator":"between","value":[18]}]',
            'range given a scalar' => '[{"key":"age","operator":"between","value":"18"}]',
            'zero days' => '[{"key":"last_visit","operator":"within","value":"0"}]',
            'entry not an object' => '["sex"]',
        ];
        return array_map(static fn (string $json): array => [$json], $bad);
    }

    #[DataProvider('unusableRequests')]
    public function testUnusableRequestsAreDropped(string $json): void
    {
        $criteria = PatientFinderCriteria::fromArray(['criteria' => $json]);

        $this->assertTrue($criteria->isEmpty());
    }

    public function testOnlyTheFirstFilterOnAKeyCounts(): void
    {
        [$where, $binds] = self::where([
            ['key' => 'sex', 'operator' => 'is', 'value' => 'Male'],
            ['key' => 'sex', 'operator' => 'is', 'value' => 'Female'],
        ]);

        $this->assertSame('(patient_data.sex = ?)', $where);
        $this->assertSame(['Male'], $binds);
    }

    public function testSexMatchesTheStoredValueIncludingLongIds(): void
    {
        $id = str_repeat('x', 100);
        [$where, $binds] = self::where([['key' => 'sex', 'operator' => 'is', 'value' => $id]]);

        $this->assertSame('(patient_data.sex = ?)', $where);
        $this->assertSame([$id], $binds);
        $this->assertTrue(
            PatientFinderCriteria::fromArray(self::request([['key' => 'sex', 'operator' => 'is', 'value' => $id . 'x']]))->isEmpty()
        );
    }

    public function testSexNotRecordedMatchesEmptyAndNull(): void
    {
        [$where, $binds] = self::where([['key' => 'sex', 'operator' => 'is', 'value' => SexCriterion::NOT_RECORDED]]);

        $this->assertSame("((patient_data.sex IS NULL OR patient_data.sex = ''))", $where);
        $this->assertSame([], $binds);
    }

    public function testAgeBetweenUsesDateOfBirthAndNeedsAUsablePastDate(): void
    {
        [$where, $binds] = self::where([['key' => 'age', 'operator' => 'between', 'value' => ['18', '64']]]);

        $this->assertSame(
            "(patient_data.DOB IS NOT NULL AND patient_data.DOB > '1000-01-01' AND patient_data.DOB <= CURDATE()"
            . ' AND patient_data.DOB <= DATE_SUB(CURDATE(), INTERVAL ? YEAR)'
            . ' AND patient_data.DOB > DATE_SUB(CURDATE(), INTERVAL ? YEAR))',
            $where
        );
        // Aged 64 or younger means born after the date 65 years ago.
        $this->assertSame([18, 65], $binds);
    }

    public function testAgeUpperBoundAloneStillExcludesFutureDates(): void
    {
        [$where, $binds] = self::where([['key' => 'age', 'operator' => 'at_most', 'value' => '18']]);

        $this->assertStringContainsString('patient_data.DOB <= CURDATE()', $where);
        $this->assertStringNotContainsString('INTERVAL ? YEAR) AND patient_data.DOB >', $where);
        $this->assertSame([19], $binds);
    }

    public function testAgeAtLeastAndReversedRange(): void
    {
        [, $least] = self::where([['key' => 'age', 'operator' => 'at_least', 'value' => '65']]);
        [, $range] = self::where([['key' => 'age', 'operator' => 'between', 'value' => ['65', '18']]]);

        $this->assertSame([65], $least);
        $this->assertSame([18, 66], $range);
    }

    public function testInsuranceFollowsTheEffectivePrimaryPolicy(): void
    {
        [$has] = self::where([['key' => 'insurance', 'operator' => 'has', 'value' => '']]);
        [$none, $binds] = self::where([['key' => 'insurance', 'operator' => 'none', 'value' => '']]);

        $this->assertStringStartsWith('(EXISTS (SELECT 1 FROM insurance_data ins', $has);
        $this->assertStringContainsString("ins.type = 'primary'", $has);
        // Started on or before today, not ended, and naming a company (not blank or 0).
        $this->assertStringContainsString('ins.date <= CURDATE()', $has);
        $this->assertStringContainsString('ins.date_end >= CURDATE()', $has);
        $this->assertStringContainsString("ins.provider REGEXP '^0*[1-9][0-9]*$'", $has);
        // A later policy in force outranks it, as EffectiveInsuranceRule picks the winner.
        $this->assertStringContainsString('AND NOT EXISTS (SELECT 1 FROM insurance_data later', $has);
        $this->assertStringStartsWith('(NOT EXISTS (SELECT 1 FROM insurance_data ins', $none);
        $this->assertSame([], $binds);
    }

    public function testVisitWithinOlderAndNever(): void
    {
        [$within, $withinBinds] = self::where([['key' => 'last_visit', 'operator' => 'within', 'value' => '90']]);
        [$older, $olderBinds] = self::where([['key' => 'last_visit', 'operator' => 'older', 'value' => '365']]);
        [$never, $neverBinds] = self::where([['key' => 'last_visit', 'operator' => 'never', 'value' => '']]);

        $this->assertStringStartsWith('(EXISTS (SELECT 1 FROM form_encounter fe', $within);
        $this->assertStringContainsString('INTERVAL ? DAY', $within);
        $this->assertSame([90], $withinBinds);

        $this->assertStringStartsWith('(EXISTS (SELECT 1 FROM form_encounter fe', $older);
        $this->assertStringContainsString('AND NOT EXISTS (', $older);
        $this->assertSame([365], $olderBinds);

        $this->assertStringStartsWith('(NOT EXISTS (SELECT 1 FROM form_encounter fe', $never);
        $this->assertSame([], $neverBinds);
    }

    public function testFiltersCombineInPlaceholderOrder(): void
    {
        [$where, $binds] = self::where([
            ['key' => 'sex', 'operator' => 'is', 'value' => 'Female'],
            ['key' => 'age', 'operator' => 'at_least', 'value' => '40'],
            ['key' => 'last_visit', 'operator' => 'within', 'value' => '30'],
        ]);

        $this->assertCount(substr_count($where, '?'), $binds);
        $this->assertSame(['Female', 40, 30], $binds);
    }

    public function testRegistryDescribesTheCriteriaForThePanel(): void
    {
        // xl() returns its input when translation is disabled, so no database is needed.
        $GLOBALS['disable_translation'] = true;
        try {
            $definitions = PatientFinderCriteriaRegistry::standard([['id' => 'Male', 'title' => 'Male']])->definitions();
        } finally {
            unset($GLOBALS['disable_translation']);
        }

        $this->assertSame(['sex', 'age', 'insurance', 'last_visit'], array_column($definitions, 'key'));
        $this->assertSame(['Male', SexCriterion::NOT_RECORDED], array_column($definitions[0]['options'], 'id'));
        $this->assertSame(['between', 'at_least', 'at_most'], array_column($definitions[1]['operators'], 'key'));
        $this->assertSame('range', $definitions[1]['operators'][0]['input']);
        $this->assertSame(150, $definitions[1]['operators'][0]['max']);
        $this->assertSame('none', $definitions[3]['operators'][2]['input']);
    }

    public function testBuilderAppliesFiltersToDataAndFilteredCountButNotTotal(): void
    {
        $builder = new PatientFinderQueryBuilder(
            static fn(string $col, array $tables): string => '`' . $col . '`',
            static fn(string $term): string => 'DATE(' . $term . ')'
        );
        $request = PatientFinderRequest::fromArray([
            'sColumns' => 'sex',
            'iDisplayStart' => '0',
            'iDisplayLength' => '10',
        ] + self::request([['key' => 'sex', 'operator' => 'is', 'value' => 'Female']]));

        $plan = $builder->build([], $request, new BoundFilter());

        $this->assertStringContainsString('WHERE 1 AND ( (patient_data.sex = ?) ) LIMIT ? OFFSET ?', $plan->dataSql);
        $this->assertSame(['Female', 10, 0], $plan->dataBinds);
        $this->assertSame('SELECT COUNT(id) AS count FROM patient_data WHERE 1 AND ( (patient_data.sex = ?) )', $plan->filteredCountSql);
        $this->assertSame(['Female'], $plan->filteredCountBinds);
        $this->assertSame('SELECT COUNT(id) AS count FROM patient_data WHERE 1', $plan->totalCountSql);
        $this->assertSame([], $plan->totalCountBinds);
    }
}
