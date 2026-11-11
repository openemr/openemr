<?php

/**
 * Isolated PatientFinderColumnList Test
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PatientFinder;

use OpenEMR\Services\PatientFinder\PatientFinderColumnList;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PatientFinderColumnListTest extends TestCase
{
    public function testBothFinderListsAreColumnLists(): void
    {
        $this->assertTrue(PatientFinderColumnList::isColumnList('ptlistcols'));
        $this->assertTrue(PatientFinderColumnList::isColumnList('recent_patient_columns'));
        $this->assertFalse(PatientFinderColumnList::isColumnList('sex'));
    }

    public function testRequestValueRecentPicksTheRecentList(): void
    {
        $this->assertSame(PatientFinderColumnList::Recent, PatientFinderColumnList::fromRequestValue('recent'));
        $this->assertSame(PatientFinderColumnList::PatientList, PatientFinderColumnList::fromRequestValue('patient'));
        $this->assertSame(PatientFinderColumnList::PatientList, PatientFinderColumnList::fromRequestValue(''));
    }

    public function testRecentUsesThePatientListColumnsOnlyWhenShared(): void
    {
        $this->assertSame(PatientFinderColumnList::PatientList, PatientFinderColumnList::Recent->columnsSource(true));
        $this->assertSame(PatientFinderColumnList::Recent, PatientFinderColumnList::Recent->columnsSource(false));
        $this->assertSame(PatientFinderColumnList::PatientList, PatientFinderColumnList::PatientList->columnsSource(false));
    }

    #[DataProvider('columnIdProvider')]
    public function testValidColumnIds(string $optionId, bool $expected): void
    {
        $isPatientDataField = static fn(string $field): bool => in_array($field, ['fname', 'DOB'], true);

        $this->assertSame($expected, PatientFinderColumnList::isValidColumnId($optionId, $isPatientDataField));
    }

    /**
     * @return array<string, array{string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function columnIdProvider(): array
    {
        return [
            'the Name column' => ['name', true],
            'a combined column' => ['city_state', true],
            'a patient_data field' => ['fname', true],
            'an unknown field' => ['nope', false],
            'a wildcard' => ['*', false],
            'empty' => ['', false],
        ];
    }
}
