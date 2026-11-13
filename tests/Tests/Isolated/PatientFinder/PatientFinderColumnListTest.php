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

use OpenEMR\Events\PatientFinder\PatientFinderColumnRegistrationEvent;
use OpenEMR\Services\PatientFinder\PatientFinderColumn;
use OpenEMR\Services\PatientFinder\PatientFinderColumnList;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PatientFinderColumnListTest extends TestCase
{
    public function testBothFinderListsAreColumnLists(): void
    {
        $this->assertTrue(PatientFinderColumnList::isColumnList('ptlistcols'));
        $this->assertTrue(PatientFinderColumnList::isColumnList('recent_patient_columns'));
        $this->assertFalse(PatientFinderColumnList::isColumnList('sex'));
        $this->assertFalse(PatientFinderColumnList::isColumnList(null), 'a list id that is not a string');
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

    /**
     * The column picker offers the finder's own columns, then the patient_data fields sorted
     * by name, leaving out anything that is not a field name.
     */
    public function testColumnChoicesOfferTheFindersColumnsThenSortedFields(): void
    {
        $choices = PatientFinderColumnList::columnChoices('DOB', ['sex', 'DOB', 7, 'city']);

        $this->assertSame('name', $choices['combined'][0]);
        $this->assertContains('address_full', $choices['combined']);
        $this->assertSame(['DOB', 'city', 'sex'], $choices['fields']);
        $this->assertSame([], $choices['other']);
    }

    /**
     * An entry whose id is neither one of the finder's columns nor a field, such as a
     * module's, is offered as its own choice so saving the list keeps it.
     */
    public function testColumnChoicesKeepAnIdTheFinderDoesNotKnow(): void
    {
        $this->assertSame(['ihi'], PatientFinderColumnList::columnChoices('ihi', ['sex'])['other']);
        $this->assertSame([], PatientFinderColumnList::columnChoices('', ['sex'])['other'], 'a new, blank entry');
        $this->assertSame([], PatientFinderColumnList::columnChoices('age', ['sex'])['other'], 'a combined column');
    }

    /**
     * The columns modules register are asked for through the registration event, for the
     * list's own tab, leaving out the finder's own columns a listener might also add.
     */
    public function testModuleColumnKeysComeFromTheRegistrationEvent(): void
    {
        $dispatcher = new EventDispatcher();
        $seen = [];
        $dispatcher->addListener(
            PatientFinderColumnRegistrationEvent::EVENT_HANDLE,
            static function (PatientFinderColumnRegistrationEvent $e) use (&$seen): void {
                $seen[] = $e->getColumnList();
                $e->addColumn(PatientFinderColumn::enriched('ihi', 'IHI'));
                $e->addColumn(PatientFinderColumn::enriched('age', 'Age'));
                $e->addColumn(PatientFinderColumn::enriched('allergy_count', 'Allergies'));
            }
        );

        $this->assertSame(['allergy_count', 'ihi'], PatientFinderColumnList::Recent->moduleColumnKeys($dispatcher));
        $this->assertSame([PatientFinderColumnList::Recent], $seen);
    }

    /**
     * A module's column is offered in its own group and accepted as an entry id, so an
     * administrator can place, sort and size it; it is no longer an unknown id.
     */
    public function testModuleColumnsAreOfferedAndAccepted(): void
    {
        $choices = PatientFinderColumnList::columnChoices('', ['sex'], ['ihi', 'sex']);

        $this->assertSame(['ihi'], $choices['module'], "a module column named like a field is listed with the fields");
        $this->assertSame([], PatientFinderColumnList::columnChoices('ihi', ['sex'], ['ihi'])['other']);
        $noFields = static fn(string $field): bool => false;
        $this->assertTrue(PatientFinderColumnList::isValidColumnId('ihi', $noFields, ['ihi']));
        $this->assertFalse(PatientFinderColumnList::isValidColumnId('ihi', $noFields));
    }
}
