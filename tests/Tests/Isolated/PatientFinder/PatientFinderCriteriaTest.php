<?php

/**
 * Isolated PatientFinderCriteria Test
 *
 * Tests how the Patient Finder's criteria (sex, age range, primary insurance, last visit) are read
 * from the request and turned into a WHERE fragment with bind values, and that they reach the
 * query builder's data and filtered-count queries but not the total.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PatientFinder;

use OpenEMR\Events\BoundFilter;
use OpenEMR\Services\PatientFinder\PatientFinderCriteria;
use OpenEMR\Services\PatientFinder\PatientFinderQueryBuilder;
use OpenEMR\Services\PatientFinder\PatientFinderRequest;
use PHPUnit\Framework\TestCase;

class PatientFinderCriteriaTest extends TestCase
{
    public function testNoParametersMeansNoCriteria(): void
    {
        $criteria = PatientFinderCriteria::fromArray([]);

        $this->assertTrue($criteria->isEmpty());
        $this->assertSame(['', []], $criteria->toWhere());
        $this->assertTrue(PatientFinderRequest::fromArray([])->criteria->isEmpty());
    }

    public function testRequestCarriesTheCriteria(): void
    {
        $request = PatientFinderRequest::fromArray(['criteria_sex' => 'Female', 'criteria_age_min' => '18']);

        $this->assertSame('Female', $request->criteria->sex);
        $this->assertSame(18, $request->criteria->ageMin);
    }

    public function testBadValuesAreDropped(): void
    {
        $criteria = PatientFinderCriteria::fromArray([
            'criteria_age_min' => '-5',
            'criteria_age_max' => 'abc',
            'criteria_insurance' => 'maybe',
            'criteria_visit' => 'sometimes',
            'criteria_visit_days' => '0',
        ]);

        $this->assertTrue($criteria->isEmpty());
        $this->assertSame(365, $criteria->visitDays);
    }

    public function testAgeBoundsAreSwappedWhenReversed(): void
    {
        $criteria = PatientFinderCriteria::fromArray(['criteria_age_min' => '65', 'criteria_age_max' => '18']);

        $this->assertSame(18, $criteria->ageMin);
        $this->assertSame(65, $criteria->ageMax);
    }

    public function testSexMatchesTheStoredValue(): void
    {
        [$where, $binds] = PatientFinderCriteria::fromArray(['criteria_sex' => 'Male'])->toWhere();

        $this->assertSame('patient_data.sex = ?', $where);
        $this->assertSame(['Male'], $binds);
    }

    public function testSexNotRecordedMatchesEmptyAndNull(): void
    {
        [$where, $binds] = PatientFinderCriteria::fromArray(
            ['criteria_sex' => PatientFinderCriteria::SEX_NOT_RECORDED]
        )->toWhere();

        $this->assertSame("(patient_data.sex IS NULL OR patient_data.sex = '')", $where);
        $this->assertSame([], $binds);
    }

    public function testAgeRangeUsesDateOfBirthAndRequiresAUsableDate(): void
    {
        [$where, $binds] = PatientFinderCriteria::fromArray(
            ['criteria_age_min' => '18', 'criteria_age_max' => '64']
        )->toWhere();

        $this->assertSame(
            "(patient_data.DOB IS NOT NULL AND patient_data.DOB > '1000-01-01')"
            . ' AND patient_data.DOB <= DATE_SUB(CURDATE(), INTERVAL ? YEAR)'
            . ' AND patient_data.DOB > DATE_SUB(CURDATE(), INTERVAL ? YEAR)',
            $where
        );
        // Aged 64 or younger means born after the date 65 years ago.
        $this->assertSame([18, 65], $binds);
    }

    public function testInsuranceChecksTheCurrentPrimaryPolicy(): void
    {
        [$has] = PatientFinderCriteria::fromArray(['criteria_insurance' => 'has'])->toWhere();
        [$none, $binds] = PatientFinderCriteria::fromArray(['criteria_insurance' => 'none'])->toWhere();

        $this->assertStringStartsWith('EXISTS (SELECT 1 FROM insurance_data ins', $has);
        $this->assertStringContainsString("ins.type = 'primary'", $has);
        $this->assertStringContainsString('ins.date_end >= CURDATE()', $has);
        $this->assertStringStartsWith('NOT EXISTS (SELECT 1 FROM insurance_data ins', $none);
        $this->assertSame([], $binds);
    }

    public function testVisitWithinOlderAndNever(): void
    {
        [$within, $withinBinds] = PatientFinderCriteria::fromArray(
            ['criteria_visit' => 'within', 'criteria_visit_days' => '90']
        )->toWhere();
        [$older, $olderBinds] = PatientFinderCriteria::fromArray(['criteria_visit' => 'older'])->toWhere();
        [$never, $neverBinds] = PatientFinderCriteria::fromArray(['criteria_visit' => 'never'])->toWhere();

        $this->assertStringStartsWith('EXISTS (SELECT 1 FROM form_encounter fe', $within);
        $this->assertStringContainsString('INTERVAL ? DAY', $within);
        $this->assertSame([90], $withinBinds);

        $this->assertStringStartsWith('(EXISTS (', $older);
        $this->assertStringContainsString('AND NOT EXISTS (', $older);
        $this->assertSame([365], $olderBinds);

        $this->assertStringStartsWith('NOT EXISTS (SELECT 1 FROM form_encounter fe', $never);
        $this->assertSame([], $neverBinds);
    }

    public function testCriteriaCombineInPlaceholderOrder(): void
    {
        [$where, $binds] = PatientFinderCriteria::fromArray([
            'criteria_sex' => 'Female',
            'criteria_age_min' => '40',
            'criteria_visit' => 'within',
            'criteria_visit_days' => '30',
        ])->toWhere();

        $this->assertCount(substr_count($where, '?'), $binds);
        $this->assertSame(['Female', 40, 30], $binds);
    }

    public function testBuilderAppliesCriteriaToDataAndFilteredCountButNotTotal(): void
    {
        $builder = new PatientFinderQueryBuilder(
            static fn(string $col, array $tables): string => '`' . $col . '`',
            static fn(string $term): string => 'DATE(' . $term . ')'
        );
        $request = PatientFinderRequest::fromArray([
            'sColumns' => 'sex',
            'iDisplayStart' => '0',
            'iDisplayLength' => '10',
            'criteria_sex' => 'Female',
        ]);

        $plan = $builder->build([], $request, new BoundFilter());

        $this->assertStringContainsString('WHERE 1 AND ( patient_data.sex = ? ) LIMIT ? OFFSET ?', $plan->dataSql);
        $this->assertSame(['Female', 10, 0], $plan->dataBinds);
        $this->assertSame('SELECT COUNT(id) AS count FROM patient_data WHERE 1 AND ( patient_data.sex = ? )', $plan->filteredCountSql);
        $this->assertSame(['Female'], $plan->filteredCountBinds);
        $this->assertSame('SELECT COUNT(id) AS count FROM patient_data WHERE 1', $plan->totalCountSql);
        $this->assertSame([], $plan->totalCountBinds);
    }
}
