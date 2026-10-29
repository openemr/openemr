<?php

/**
 * Isolated PatientFinderQueryBuilder Test
 *
 * Tests the pure SQL building for the Patient Finder's server-side endpoint:
 * the data query, the two COUNT queries, bound-value ordering, the exact/like/
 * any match modes, the name + DOB special cases, and SQL (JOIN/subquery) and
 * enriched extension columns. A trivial identifier escaper and date-search are
 * injected so no database is required.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Open Plan IT Ltd
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PatientFinder;

use OpenEMR\Events\BoundFilter;
use OpenEMR\Services\PatientFinder\PatientFinderColumn;
use OpenEMR\Services\PatientFinder\PatientFinderQueryBuilder;
use OpenEMR\Services\PatientFinder\PatientFinderRequest;
use PHPUnit\Framework\TestCase;

class PatientFinderQueryBuilderTest extends TestCase
{
    private function builder(): PatientFinderQueryBuilder
    {
        return new PatientFinderQueryBuilder(
            static fn(string $col, array $tables): string => '`' . $col . '`',
            static fn(string $term): string => 'DATE(' . $term . ')'
        );
    }

    /**
     * @param array<int, string>                         $columnKeys
     * @param array<int, array{index: int, dir: string}> $sort
     * @param array<int, string>                         $columnSearches
     */
    private function request(
        string $globalSearch = '',
        bool $exact = false,
        bool $any = false,
        array $columnKeys = [],
        array $sort = [],
        array $columnSearches = [],
        int $start = -1,
        int $length = -1
    ): PatientFinderRequest {
        return new PatientFinderRequest(
            1,
            $start,
            $length,
            $globalSearch,
            $exact,
            $any,
            $columnKeys,
            $sort,
            $columnSearches
        );
    }

    public function testGlobalLikeSearchWithNameAndTableColumns(): void
    {
        $columns = [
            PatientFinderColumn::fromTable(PatientFinderQueryBuilder::NAME_KEY, 'Name'),
            PatientFinderColumn::fromTable('DOB', 'DOB'),
            PatientFinderColumn::fromTable('sex', 'Sex'),
        ];
        $plan = $this->builder()->build(
            $columns,
            $this->request(globalSearch: 'smith', start: 0, length: 10),
            new BoundFilter()
        );

        $this->assertSame(
            'SELECT patient_data.pid, lname, fname, mname, `DOB`, `sex` FROM patient_data WHERE 1 AND '
            . '( ( lname LIKE ? OR fname LIKE ? OR mname LIKE ? OR `DOB` LIKE ? OR `sex` LIKE ? ) )'
            . ' LIMIT ? OFFSET ?',
            $plan->dataSql
        );
        $this->assertSame(['smith%', 'smith%', 'smith%', 'smith%', 'smith%', 10, 0], $plan->dataBinds);

        $this->assertSame('SELECT COUNT(id) AS count FROM patient_data WHERE 1', $plan->totalCountSql);
        $this->assertSame([], $plan->totalCountBinds);

        $this->assertSame(
            'SELECT COUNT(id) AS count FROM patient_data WHERE 1 AND '
            . '( ( lname LIKE ? OR fname LIKE ? OR mname LIKE ? OR `DOB` LIKE ? OR `sex` LIKE ? ) )',
            $plan->filteredCountSql
        );
        $this->assertSame(['smith%', 'smith%', 'smith%', 'smith%', 'smith%'], $plan->filteredCountBinds);
    }

    public function testExactSearchBindsTermVerbatim(): void
    {
        $columns = [PatientFinderColumn::fromTable('sex', 'Sex')];
        $plan = $this->builder()->build($columns, $this->request(globalSearch: 'male', exact: true), new BoundFilter());

        $this->assertStringContainsString('( `sex` LIKE ? )', $plan->dataSql);
        $this->assertSame(['male'], $plan->filteredCountBinds);
    }

    public function testAnySearchBindsContains(): void
    {
        $columns = [PatientFinderColumn::fromTable('sex', 'Sex')];
        $plan = $this->builder()->build($columns, $this->request(globalSearch: 'ale', any: true), new BoundFilter());

        $this->assertSame(['%ale%'], $plan->filteredCountBinds);
    }

    public function testPerColumnDobUsesDateSearch(): void
    {
        $columns = [
            PatientFinderColumn::fromTable(PatientFinderQueryBuilder::NAME_KEY, 'Name'),
            PatientFinderColumn::fromTable('DOB', 'DOB'),
        ];
        $plan = $this->builder()->build(
            $columns,
            $this->request(columnKeys: ['name', 'DOB'], columnSearches: [1 => '1990']),
            new BoundFilter()
        );

        $this->assertSame(
            'SELECT patient_data.pid, lname, fname, mname, `DOB` FROM patient_data WHERE 1 AND ( `DOB` LIKE ? )',
            $plan->dataSql
        );
        $this->assertSame(['DATE(1990)'], $plan->dataBinds);
    }

    public function testSortExpandsNameAndQuotesColumns(): void
    {
        $columns = [
            PatientFinderColumn::fromTable(PatientFinderQueryBuilder::NAME_KEY, 'Name'),
            PatientFinderColumn::fromTable('sex', 'Sex'),
        ];
        $plan = $this->builder()->build(
            $columns,
            $this->request(sort: [['index' => 0, 'dir' => 'DESC'], ['index' => 1, 'dir' => 'ASC']]),
            new BoundFilter()
        );

        $this->assertStringContainsString(
            'ORDER BY lname DESC, fname DESC, mname DESC, `sex` ASC',
            $plan->dataSql
        );
    }

    public function testSqlSubqueryColumnIsSelectedSearchedAndSorted(): void
    {
        $expr = '(SELECT i.ihi_number FROM ihi_data i WHERE i.pid = patient_data.pid LIMIT 1)';
        $columns = [
            PatientFinderColumn::fromTable(PatientFinderQueryBuilder::NAME_KEY, 'Name'),
            PatientFinderColumn::fromSql('ihi_number', 'IHI', $expr),
        ];
        $plan = $this->builder()->build(
            $columns,
            $this->request(globalSearch: '539', sort: [['index' => 1, 'dir' => 'ASC']]),
            new BoundFilter()
        );

        $this->assertStringContainsString($expr . ' AS `ihi_number`', $plan->dataSql);
        $this->assertStringContainsString('(' . $expr . ') LIKE ?', $plan->dataSql);
        $this->assertStringContainsString('ORDER BY (' . $expr . ') ASC', $plan->dataSql);
        // Subquery column needs no JOIN, so the filtered count stays COUNT(id).
        $this->assertStringContainsString('SELECT COUNT(id) AS count FROM patient_data WHERE', $plan->filteredCountSql);
    }

    public function testSqlJoinColumnAddsJoinAndDistinctCount(): void
    {
        $join = 'LEFT JOIN ihi_data ihi ON ihi.pid = patient_data.pid';
        $columns = [
            PatientFinderColumn::fromSql('ihi_number', 'IHI', 'ihi.ihi_number', $join),
        ];
        $plan = $this->builder()->build($columns, $this->request(globalSearch: '539'), new BoundFilter());

        $this->assertStringContainsString('FROM patient_data ' . $join . ' WHERE', $plan->dataSql);
        $this->assertStringContainsString('ihi.ihi_number AS `ihi_number`', $plan->dataSql);
        $this->assertSame(
            'SELECT COUNT(DISTINCT patient_data.pid) AS count FROM patient_data ' . $join . ' WHERE 1 AND '
            . '( ( (ihi.ihi_number) LIKE ? ) )',
            $plan->filteredCountSql
        );
    }

    public function testEnrichedColumnNeverReachesSql(): void
    {
        $columns = [
            PatientFinderColumn::fromTable(PatientFinderQueryBuilder::NAME_KEY, 'Name'),
            PatientFinderColumn::enriched('care_flag', 'Care Flag'),
        ];
        $plan = $this->builder()->build(
            $columns,
            $this->request(globalSearch: 'smith', columnKeys: ['name', 'care_flag'], columnSearches: [1 => 'x']),
            new BoundFilter()
        );

        $this->assertStringNotContainsString('care_flag', $plan->dataSql);
        // Only the name OR-group should be present; the enriched column is skipped.
        $this->assertStringContainsString('( ( lname LIKE ? OR fname LIKE ? OR mname LIKE ? ) )', $plan->dataSql);
    }

    public function testCustomBoundFilterComposesAndBindsFirst(): void
    {
        $filter = new BoundFilter();
        $filter->setFilterClause('patient_data.pid NOT IN (?, ?)');
        $filter->setBoundValues([5, 6]);

        $columns = [PatientFinderColumn::fromTable('sex', 'Sex')];
        $plan = $this->builder()->build($columns, $this->request(globalSearch: 'male', start: 0, length: 25), $filter);

        $this->assertStringContainsString(
            'WHERE patient_data.pid NOT IN (?, ?) AND ( ( `sex` LIKE ? ) ) LIMIT ? OFFSET ?',
            $plan->dataSql
        );
        $this->assertSame([5, 6, 'male%', 25, 0], $plan->dataBinds);

        $this->assertSame(
            'SELECT COUNT(id) AS count FROM patient_data WHERE patient_data.pid NOT IN (?, ?)',
            $plan->totalCountSql
        );
        $this->assertSame([5, 6], $plan->totalCountBinds);
        $this->assertSame([5, 6, 'male%'], $plan->filteredCountBinds);
    }

    public function testGlobalSearchColumnsOverrideDisplayedColumnsForAnyMode(): void
    {
        // Displayed columns are name + sex, but search_any searches a wider set
        // of DEM fields (here: street, phone_home) in "contains" mode.
        $displayed = [
            PatientFinderColumn::fromTable(PatientFinderQueryBuilder::NAME_KEY, 'Name'),
            PatientFinderColumn::fromTable('sex', 'Sex'),
        ];
        $global = [
            PatientFinderColumn::fromTable('street', 'Street'),
            PatientFinderColumn::fromTable('phone_home', 'Phone'),
        ];
        $plan = $this->builder()->build(
            $displayed,
            $this->request(globalSearch: 'main', any: true),
            new BoundFilter(),
            $global
        );

        // SELECT is driven by the displayed columns...
        $this->assertStringContainsString('SELECT patient_data.pid, lname, fname, mname, `sex` FROM', $plan->dataSql);
        // ...while the global WHERE searches the DEM field set with contains binds.
        $this->assertStringContainsString('( `street` LIKE ? OR `phone_home` LIKE ? )', $plan->dataSql);
        $this->assertSame(['%main%', '%main%'], $plan->filteredCountBinds);
    }

    public function testNoPagingOmitsLimit(): void
    {
        $columns = [PatientFinderColumn::fromTable('sex', 'Sex')];
        $plan = $this->builder()->build($columns, $this->request(globalSearch: 'male'), new BoundFilter());

        $this->assertStringNotContainsString('LIMIT', $plan->dataSql);
        $this->assertSame(['male%'], $plan->dataBinds);
    }

    public function testSqlColumnSelectBindsLeadDataBinds(): void
    {
        // A parameterised SELECT expression binds ahead of the WHERE/LIMIT values.
        $columns = [
            PatientFinderColumn::fromSql(
                'tag',
                'Tag',
                "(SELECT t.label FROM tags t WHERE t.pid = patient_data.pid AND t.kind = ? LIMIT 1)",
                null,
                null,
                ['care'],
                false // not searchable, so it only appears in SELECT
            ),
            PatientFinderColumn::fromTable('sex', 'Sex'),
        ];
        $plan = $this->builder()->build(
            $columns,
            $this->request(globalSearch: 'male', columnKeys: ['tag', 'sex'], start: 0, length: 5),
            new BoundFilter()
        );

        // select bind 'care' first, then the search term, then the limit binds.
        $this->assertSame(['care', 'male%', 5, 0], $plan->dataBinds);
    }
}
