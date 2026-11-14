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
use OpenEMR\Services\PatientFinder\PatientFinderBuiltInColumn;
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

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function sqlKeyProvider(): array
    {
        $cases = [];
        foreach (PatientFinderBuiltInColumn::cases() as $column) {
            if (!$column->isAppointment()) {
                $cases[$column->value] = [$column->value];
            }
        }
        return $cases;
    }

    /**
     * Each combined column but the appointment ones is searchable and sortable SQL with no binds the finder would have to place.
     */
    #[DataProvider('sqlKeyProvider')]
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

    /**
     * The search box leaves out exactly the columns that search through a subquery on another
     * table, which it would run against every patient on each keystroke.
     */
    #[DataProvider('builtInKeyProvider')]
    public function testOnlySubqueryColumnsAreLeftOutOfTheSearchBox(string $key): void
    {
        $column = (new PatientFinderBuiltInColumns())->column($key, 'Title');

        $this->assertNotNull($column);
        $readsAnotherTable = str_contains((string) $column->searchExpression, 'FROM ');
        $this->assertSame(!$readsAnotherTable, $column->inGlobalSearch);
    }

    /**
     * CONCAT_WS returns '' when every part is NULL, and an outer CONCAT_WS keeps '' with its
     * separator, so each nested CONCAT_WS has to become NULL when it is blank.
     */
    public function testBlankAddressPartsLeaveNoStraySeparator(): void
    {
        $columns = new PatientFinderBuiltInColumns();
        $address = $columns->column('address_full', 'Address');
        $withCountry = $columns->column('address_full_country', 'Address');
        $this->assertNotNull($address);
        $this->assertNotNull($withCountry);

        $this->assertStringContainsString("NULLIF(CONCAT_WS('  ', ", (string) $address->selectExpression);
        $this->assertStringStartsWith(
            "CONCAT_WS(', ', NULLIF(" . $address->selectExpression . ", ''), ",
            (string) $withCountry->selectExpression
        );
    }

    /**
     * A patient_data field is not mistaken for a combined column.
     */
    public function testUnknownKeyIsNotABuiltInColumn(): void
    {
        $this->assertFalse(PatientFinderBuiltInColumns::has('phone_home'));
        $this->assertNull((new PatientFinderBuiltInColumns())->column('phone_home', 'Home Phone'));
    }

    /**
     * Next and Last Appt are display-only: a repeating appointment's dates are worked out in
     * PHP once the rows are known, which the database cannot sort or search on.
     */
    public function testAppointmentColumnsAreDisplayOnly(): void
    {
        $columns = new PatientFinderBuiltInColumns();
        foreach ([PatientFinderBuiltInColumn::NextAppointment, PatientFinderBuiltInColumn::LastAppointment] as $appointment) {
            $column = $columns->column($appointment->value, 'Appt');

            $this->assertNotNull($column);
            $this->assertTrue($column->isEnriched());
            $this->assertFalse($column->searchable);
            $this->assertFalse($column->sortable);
            $this->assertTrue($appointment->isAppointment());
        }
        $this->assertFalse(PatientFinderBuiltInColumn::Age->isAppointment());
    }

    /**
     * Sorting and searching a combined column use its expression in the finder query.
     */
    public function testSortingOnABuiltInColumnOrdersByItsExpression(): void
    {
        $age = (new PatientFinderBuiltInColumns())->column(PatientFinderBuiltInColumn::Age->value, 'Age');
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
        $this->assertStringContainsString('(' . $age->searchExpression . ') LIKE ? ESCAPE \'|\'', $plan->dataSql);
        $this->assertContains('4%', $plan->dataBinds);
    }

    /**
     * A ptlistcols entry with a combined key is seeded as that column, with its sort and width.
     */
    public function testFactorySeedsABuiltInKeyAsItsSqlColumn(): void
    {
        $fetch = static fn(string $sql, array $binds): array => in_array('ptlistcols', $binds, true)
            ? [
                ['option_id' => 'phone_home', 'title' => 'Home Phone', 'toggle_setting_1' => ''],
                ['option_id' => 'city_state', 'title' => 'City State', 'toggle_setting_1' => '1', 'notes' => '10rem'],
            ]
            : [];
        $listMap = static fn(string $listId): array => ['0' => 'asc', '1' => 'desc'];

        $registry = (new PatientFinderColumnRegistryFactory(
            new EventDispatcher(),
            $fetch,
            $listMap,
            new PatientFinderBuiltInColumns(),
            patientDataFields: static fn(): array => ['phone_home']
        ))->create();

        $this->assertSame(['phone_home', 'city_state'], $registry->keys());
        $phone = $registry->get('phone_home');
        $this->assertNotNull($phone);
        $this->assertSame(PatientFinderColumn::TYPE_PATIENT_DATA, $phone->type);
        $cityState = $registry->get('city_state');
        $this->assertNotNull($cityState);
        $this->assertSame(PatientFinderColumn::TYPE_SQL, $cityState->type);
        $this->assertSame('City State', $cityState->title);
        $this->assertSame('desc', $cityState->defaultSortDir);
        $this->assertSame('10rem', $cityState->width, 'the width comes from the entry notes');
        $this->assertSame('', $phone->width);
    }

    /**
     * Without patients/appt the appointment columns are left out, on the page and in the
     * search alike, rather than read as patient_data fields of the same name.
     */
    public function testAppointmentColumnsNeedTheAppointmentAcl(): void
    {
        $fetch = static fn(string $sql, array $binds): array => in_array('ptlistcols', $binds, true)
            ? [
                ['option_id' => 'age', 'title' => 'Age', 'toggle_setting_1' => ''],
                ['option_id' => 'next_appointment', 'title' => 'Next Appt', 'toggle_setting_1' => ''],
                ['option_id' => 'last_appointment', 'title' => 'Last Appt', 'toggle_setting_1' => ''],
            ]
            : [];
        $listMap = static fn(string $listId): array => [];
        $checked = [];
        $deny = static function (string $section, string $value) use (&$checked): bool {
            $checked[] = "$section/$value";
            return false;
        };

        $denied = (new PatientFinderColumnRegistryFactory(
            new EventDispatcher(),
            $fetch,
            $listMap,
            new PatientFinderBuiltInColumns(),
            $deny,
            static fn(): array => []
        ))->create();
        $allowed = (new PatientFinderColumnRegistryFactory(
            new EventDispatcher(),
            $fetch,
            $listMap,
            new PatientFinderBuiltInColumns(),
            static fn(string $section, string $value): bool => true,
            static fn(): array => []
        ))->create();

        $this->assertSame(['age'], $denied->keys());
        $this->assertSame(['patients/appt', 'patients/appt'], $checked, 'only the appointment columns are checked');
        $this->assertSame(['age', 'next_appointment', 'last_appointment'], $allowed->keys());
    }

    /**
     * Only the appointment columns need more than the finder's own access.
     */
    public function testRequiredAcl(): void
    {
        $this->assertSame(['patients', 'appt'], PatientFinderBuiltInColumns::requiredAcl('next_appointment'));
        $this->assertSame(['patients', 'appt'], PatientFinderBuiltInColumns::requiredAcl('last_appointment'));
        $this->assertNull(PatientFinderBuiltInColumns::requiredAcl('age'));
        $this->assertNull(PatientFinderBuiltInColumns::requiredAcl('phone_home'));
    }
}
