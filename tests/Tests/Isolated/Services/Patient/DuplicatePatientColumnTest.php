<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Patient;

use OpenEMR\Services\Patient\DuplicatePatientColumn;
use OpenEMR\Services\Patient\DuplicatePatientRow;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class DuplicatePatientColumnTest extends TestCase
{
    private const HIGHLIGHT = 17;

    protected function setUp(): void
    {
        $GLOBALS['date_display_format'] ??= 0;
        $GLOBALS['disable_translation'] = true;
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private static function row(array $overrides = []): DuplicatePatientRow
    {
        return DuplicatePatientRow::forPrimary(array_merge([
            'pid' => '7',
            'pubpid' => 'PUB7',
            'dupscore' => '14',
            'lname' => 'Nakamura',
            'fname' => 'Aiko',
            'mname' => 'R',
            'DOB' => '1984-11-02 00:00:00',
            'sex' => 'Female',
            'email' => 'aiko@example.com',
            'phone_home' => ' 555-1000 ',
            'phone_biz' => '',
            'phone_cell' => '555-2000',
            'regdate' => '2020-04-01',
            'street' => '12 Elm St',
        ], $overrides), self::HIGHLIGHT);
    }

    /**
     * @param list<DuplicatePatientColumn> $columns
     */
    private static function byKey(array $columns, string $key): DuplicatePatientColumn
    {
        foreach ($columns as $column) {
            if ($column->key === $key) {
                return $column;
            }
        }
        self::fail("no column keyed '$key'");
    }

    #[Test]
    public function forFieldShowsThePatientFieldVerbatim(): void
    {
        $column = DuplicatePatientColumn::forField('pubpid', 'Public');

        $this->assertSame('PUB7', $column->render(self::row()));
    }

    #[Test]
    public function forFieldCanReadAFieldUnderADifferentKey(): void
    {
        $column = DuplicatePatientColumn::forField('social', 'SSN', 'ss');

        $this->assertSame('111-22-3333', $column->render(self::row(['ss' => '111-22-3333'])));
    }

    #[Test]
    public function nameColumnAssemblesTheNameParts(): void
    {
        $column = self::byKey(DuplicatePatientColumn::defaults(), 'name');

        $this->assertSame('Nakamura, Aiko R', $column->render(self::row()));
    }

    #[Test]
    public function telephoneColumnJoinsOnlyTheNumbersThatArePresent(): void
    {
        $column = self::byKey(DuplicatePatientColumn::defaults(), 'phones');

        $this->assertSame('555-1000, 555-2000', $column->render(self::row()));
        $this->assertSame('', $column->render(self::row([
            'phone_home' => '', 'phone_biz' => '', 'phone_cell' => '',
        ])));
    }

    #[Test]
    public function dobColumnDropsAnyTimeComponent(): void
    {
        $column = self::byKey(DuplicatePatientColumn::defaults(), 'DOB');

        $this->assertStringNotContainsString(':', $column->render(self::row()));
    }

    #[Test]
    public function scopeColumnIsEmptyForAnUnflaggedRow(): void
    {
        $column = self::byKey(DuplicatePatientColumn::defaults(), 'scope');

        $this->assertSame('', $column->render(self::row(['dupscore' => '10'])));
        $this->assertSame('Merge From', $column->render(self::row(['dupscore' => '20'])));
    }

    #[Test]
    public function defaultsAreOrderedAndUniquelyKeyed(): void
    {
        $keys = array_map(
            static fn(DuplicatePatientColumn $c): string => $c->key,
            DuplicatePatientColumn::defaults()
        );

        $this->assertSame(
            ['score', 'pid', 'pubpid', 'scope', 'name', 'DOB', 'sex', 'email', 'phones', 'regdate', 'street'],
            $keys
        );
        $this->assertSame($keys, array_values(array_unique($keys)), 'column keys must be unique');
    }

    /**
     * prepare() is the whole reason a lookup column does not become N+1: it sees every row on the
     * report once, before any cell is rendered.
     */
    #[Test]
    public function prepareRunsOnceOverEveryRowBeforeRendering(): void
    {
        $lookups = 0;
        /** @var array<string, string> $names */
        $names = [];

        $column = new DuplicatePatientColumn(
            'facility',
            'Home Facility',
            // By reference: an arrow fn would capture $names by value, i.e. the empty array it holds
            // before prepare() runs.
            function (DuplicatePatientRow $row) use (&$names): string {
                return $names[$row->field('home_facility')] ?? '';
            },
            function (array $rows) use (&$lookups, &$names): void {
                ++$lookups;
                $ids = [];
                foreach ($rows as $row) {
                    $ids[] = $row->field('home_facility');
                }
                // One "query" for the whole report.
                $names = array_fill_keys(array_filter($ids), 'Clinic');
            },
        );

        $rows = [
            self::row(['pid' => '1', 'home_facility' => '3']),
            self::row(['pid' => '2', 'home_facility' => '3']),
            self::row(['pid' => '3', 'home_facility' => '']),
        ];
        $column->prepare($rows);

        $this->assertSame(1, $lookups, 'prepare must run once for the report, not once per row');
        $this->assertSame('Clinic', $column->render($rows[0]));
        $this->assertSame('Clinic', $column->render($rows[1]));
        $this->assertSame('', $column->render($rows[2]));
    }

    #[Test]
    public function prepareIsOptional(): void
    {
        $column = DuplicatePatientColumn::forField('pubpid', 'Public');
        $column->prepare([self::row()]);

        $this->assertSame('PUB7', $column->render(self::row()));
    }
}
