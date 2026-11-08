<?php

/**
 * Isolated PatientFinderBuiltInColumns Test
 *
 * Checks that each combined Patient Finder column is a searchable, sortable SQL
 * column with no bound parameters, that appointment columns display in the
 * site's formats but search and sort on the stored value, and that the registry
 * factory seeds a ptlistcols entry with a built-in key as that column.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PatientFinder;

use OpenEMR\Events\BoundFilter;
use OpenEMR\Services\PatientFinder\PatientFinderBuiltInColumns;
use OpenEMR\Services\PatientFinder\PatientFinderColumn;
use OpenEMR\Services\PatientFinder\PatientFinderColumnRegistryFactory;
use OpenEMR\Services\PatientFinder\PatientFinderQueryBuilder;
use OpenEMR\Services\PatientFinder\PatientFinderRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PatientFinderBuiltInColumnsTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function builtInKeyProvider(): array
    {
        $cases = [];
        foreach (PatientFinderBuiltInColumns::keys() as $key) {
            $cases[$key] = [$key];
        }
        return $cases;
    }

    #[DataProvider('builtInKeyProvider')]
    public function testEveryKeyIsASearchableSortableSqlColumnWithoutBinds(string $key): void
    {
        $column = (new PatientFinderBuiltInColumns())->column($key, 'Title', 'desc');

        $this->assertInstanceOf(PatientFinderColumn::class, $column);
        $this->assertTrue($column->isSql());
        $this->assertTrue($column->searchable);
        $this->assertTrue($column->sortable);
        $this->assertSame('desc', $column->defaultSortDir);
        $this->assertSame([], $column->selectBoundValues);
        $this->assertNull($column->joinClause, 'a correlated subquery keeps the finder counts per patient');
        // The finder re-embeds the search expression in WHERE and ORDER BY without binds.
        $this->assertStringNotContainsString('?', (string) $column->selectExpression);
        $this->assertStringNotContainsString('?', (string) $column->searchExpression);
    }

    public function testUnknownKeyIsNotABuiltInColumn(): void
    {
        $this->assertFalse(PatientFinderBuiltInColumns::has('phone_home'));
        $this->assertNull((new PatientFinderBuiltInColumns())->column('phone_home', 'Home Phone'));
    }

    /**
     * @return array<string, array{int, int, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function displayFormatProvider(): array
    {
        return [
            'ISO, 24 hr' => [0, 0, "'%Y-%m-%d %H:%i'"],
            'US, 12 hr' => [1, 1, "'%m/%d/%Y %l:%i %p'"],
            'European, 24 hr' => [2, 0, "'%d/%m/%Y %H:%i'"],
            'unknown falls back to ISO, 24 hr' => [9, 9, "'%Y-%m-%d %H:%i'"],
        ];
    }

    #[DataProvider('displayFormatProvider')]
    public function testAppointmentDisplaysInSiteFormatButSearchesStoredValue(
        int $dateFormat,
        int $timeFormat,
        string $pattern
    ): void {
        $column = (new PatientFinderBuiltInColumns($dateFormat, $timeFormat))
            ->column(PatientFinderBuiltInColumns::NEXT_APPOINTMENT, 'Next Appt');

        $this->assertNotNull($column);
        $this->assertStringStartsWith('DATE_FORMAT(', (string) $column->selectExpression);
        $this->assertStringEndsWith(", $pattern)", (string) $column->selectExpression);
        $this->assertStringNotContainsString('DATE_FORMAT', (string) $column->searchExpression);
    }

    public function testNextAndLastAppointmentLookInOppositeDirections(): void
    {
        $columns = new PatientFinderBuiltInColumns();
        $next = (string) $columns->column(PatientFinderBuiltInColumns::NEXT_APPOINTMENT, 'Next')?->searchExpression;
        $last = (string) $columns->column(PatientFinderBuiltInColumns::LAST_APPOINTMENT, 'Last')?->searchExpression;

        $this->assertStringStartsWith('(SELECT MIN(', $next);
        $this->assertStringContainsString('> NOW()', $next);
        $this->assertStringStartsWith('(SELECT MAX(', $last);
        $this->assertStringContainsString('< NOW()', $last);
        $this->assertStringContainsString("pc_apptstatus NOT IN ('x', '%')", $next);
    }

    public function testSortingOnABuiltInColumnOrdersByItsExpression(): void
    {
        $age = (new PatientFinderBuiltInColumns())->column(PatientFinderBuiltInColumns::AGE, 'Age');
        $this->assertNotNull($age);
        $request = PatientFinderRequest::fromArray([
            'sColumns' => 'age',
            'iSortingCols' => '1',
            'iSortCol_0' => '0',
            'sSortDir_0' => 'desc',
            'bSortable_0' => 'true',
            'bSearchable_0' => 'true',
            'sSearch_0' => '4',
        ]);
        $builder = new PatientFinderQueryBuilder(static fn(string $name): string => "`$name`");

        $plan = $builder->build([0 => $age], $request, new BoundFilter());

        $this->assertStringContainsString(
            ' ORDER BY (' . $age->searchExpression . ') DESC',
            $plan->dataSql
        );
        $this->assertStringContainsString('(' . $age->searchExpression . ') LIKE ?', $plan->dataSql);
        $this->assertContains('4%', $plan->dataBinds);
    }

    public function testFactorySeedsABuiltInKeyAsItsSqlColumn(): void
    {
        $fetch = static fn(string $sql, array $binds): array => str_contains($sql, 'ptlistcols')
            ? [
                ['option_id' => 'phone_home', 'title' => 'Home Phone', 'toggle_setting_1' => ''],
                ['option_id' => 'city_state', 'title' => 'City State', 'toggle_setting_1' => '1'],
            ]
            : [];
        $listMap = static fn(string $listId): array => ['0' => 'asc', '1' => 'desc'];

        $registry = (new PatientFinderColumnRegistryFactory(
            new EventDispatcher(),
            $fetch,
            $listMap,
            new PatientFinderBuiltInColumns()
        ))->create();

        $this->assertSame(['phone_home', 'city_state'], $registry->keys());
        $this->assertSame(PatientFinderColumn::TYPE_PATIENT_DATA, $registry->get('phone_home')?->type);
        $cityState = $registry->get('city_state');
        $this->assertNotNull($cityState);
        $this->assertSame(PatientFinderColumn::TYPE_SQL, $cityState->type);
        $this->assertSame('City State', $cityState->title);
        $this->assertSame('desc', $cityState->defaultSortDir);
    }
}
