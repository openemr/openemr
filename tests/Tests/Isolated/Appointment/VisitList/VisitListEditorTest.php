<?php

/**
 * Isolated VisitListEditor Test
 *
 * The Visit List part of the appointment editor without a database: what the form shows for
 * an existing or new appointment, and when a save places the appointment on its list.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Appointment\VisitList;

use DateTimeImmutable;
use DomainException;
use Lcobucci\Clock\FrozenClock;
use OpenEMR\Appointment\VisitList\VisitList;
use OpenEMR\Appointment\VisitList\VisitListBlock;
use OpenEMR\Appointment\VisitList\VisitListBlockRelease;
use OpenEMR\Appointment\VisitList\VisitListBlockSave;
use OpenEMR\Appointment\VisitList\VisitListCopies;
use OpenEMR\Appointment\VisitList\VisitListEditor;
use OpenEMR\Appointment\VisitList\VisitListEntry;
use OpenEMR\Appointment\VisitList\VisitListRepeatScope;
use OpenEMR\Appointment\VisitList\VisitListService;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

class VisitListEditorTest extends TestCase
{
    /** @var list<array{string, array<mixed>}> Each query run: SQL and binds. */
    private array $queries = [];

    /** @var list<array{string, array<mixed>}> Each statement run: SQL and binds. */
    private array $statements = [];

    /**
     * An editor whose appointment query returns the given rows, whose blocks are the given
     * fetchEvents() rows, and which records each query and statement it runs.
     *
     * @param list<array<string, mixed>> $appointmentRows
     * @param list<array<string, mixed>> $blockRows
     * @param list<array<string, mixed>> $entryRows the Visit List patients already on lists
     */
    private function editor(
        array $appointmentRows = [],
        array $blockRows = [],
        array $entryRows = [],
        string $now = '2026-01-01 12:00'
    ): VisitListEditor {
        return new VisitListEditor(new VisitListService(
            function (string $sql, array $binds) use ($appointmentRows, $entryRows): array {
                $this->queries[] = [$sql, $binds];
                if (str_contains($sql, 'openemr_postcalendar_categories')) {
                    return [['pc_catid' => '21']];
                }
                return str_contains($sql, '`pc_multiple`') ? $appointmentRows : $entryRows;
            },
            static fn (string $from, string $to, string $where, array $binds): array => $blockRows,
            execute: function (string $sql, array $binds): void {
                $this->statements[] = [$sql, $binds];
            },
            repeatPattern: static fn (array $row, string $from, string $to): array => [$from],
            // By default before every date in these tests, so no patient is on a past date.
            clock: new FrozenClock(new DateTimeImmutable($now)),
        ), 15);
    }

    /**
     * @return array<string, string>
     */
    private static function appointmentRow(int $eid, int $provider): array
    {
        return [
            'pc_eid' => (string) $eid,
            'pc_aid' => (string) $provider,
            'pc_multiple' => '0',
            'pc_eventDate' => '2026-03-09',
            'pc_startTime' => '09:00:00',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function blockRow(int $eid, int $provider): array
    {
        return [
            'pc_eid' => (string) $eid,
            'uprovider_id' => (string) $provider,
            'pc_eventDate' => '2026-03-09',
            'pc_startTime' => '09:00:00',
            'pc_duration' => '28800',
            'pc_facility' => '3',
        ];
    }

    /**
     * @param array<string, string> $fields
     */
    private static function post(string $action, array $fields = []): ParameterBag
    {
        return new ParameterBag(['form_action' => $action, 'form_visit_list' => '1'] + $fields);
    }

    public function testAnExistingVisitListPatientShowsItsOrderAndStop(): void
    {
        $form = $this->editor()->form(0, 42, ['pc_visit_list' => 1, 'pc_visit_seq' => 3, 'pc_visit_stop' => '2'], new ParameterBag(), '2026-03-09');

        $this->assertTrue($form->active);
        $this->assertSame('3', $form->sequence);
        $this->assertSame('2', $form->stop);
        $this->assertSame(' d-none', $form->hiddenClass());
    }

    public function testAnExistingTimedAppointmentIsNotAVisitListPatient(): void
    {
        $form = $this->editor()->form(0, 42, ['pc_visit_list' => '0', 'pc_visit_seq' => null, 'pc_visit_stop' => null], new ParameterBag(), '2026-03-09');

        $this->assertFalse($form->active);
        $this->assertSame('', $form->sequence);
        $this->assertSame('', $form->stop);
        $this->assertSame('', $form->hiddenClass());
    }

    public function testAnExistingAppointmentThatCouldNotBeReadIsNotAVisitListPatient(): void
    {
        $this->assertFalse($this->editor()->form(0, 42, false, new ParameterBag(), '2026-03-09')->active);
    }

    public function testOnlyPatientAppointmentsCanBeOnAVisitList(): void
    {
        $query = new ParameterBag(['visitlist' => '1', 'userid' => '7', 'starttimeh' => '9', 'starttimem' => '00']);

        $this->assertFalse($this->editor()->form(1, null, null, $query, '2026-03-09')->active, 'a provider event');
        $this->assertFalse($this->editor()->form(3, 42, ['pc_visit_list' => 1], $query, '2026-03-09')->active, 'a group event');
        $this->assertSame([], $this->queries, 'nothing is read');
    }

    public function testANewPatientFromAVisitListBlockGetsTheNextOrder(): void
    {
        $editor = $this->editor(
            blockRows: [self::blockRow(1, 7)],
            entryRows: [[
                'pc_eid' => '10',
                'pc_pid' => '510',
                'pc_aid' => '7',
                'pc_eventDate' => '2026-03-09',
                'pc_startTime' => '09:00:00',
                'pc_visit_seq' => '4',
                'pc_visit_stop' => null,
            ]]
        );
        $query = new ParameterBag(['visitlist' => '1', 'userid' => '7', 'starttimeh' => '9', 'starttimem' => '00']);

        $form = $editor->form(0, null, null, $query, '2026-03-09');

        $this->assertTrue($form->active);
        $this->assertSame('5', $form->sequence);
        $this->assertSame('', $form->stop);
    }

    public function testANewAppointmentNotFromAVisitListBlockIsTimed(): void
    {
        $query = new ParameterBag(['userid' => '7', 'starttimeh' => '9', 'starttimem' => '00']);

        $this->assertFalse($this->editor()->form(0, null, null, $query, '2026-03-09')->active);
    }

    public function testANewVisitListPatientNeedsAProvider(): void
    {
        $query = new ParameterBag(['visitlist' => '1', 'starttimeh' => '9', 'starttimem' => '00']);

        $this->assertFalse($this->editor()->form(0, null, null, $query, '2026-03-09')->active);
    }

    public function testANewVisitListPatientWithAnUnreadableDateHasNoOrderYet(): void
    {
        $query = new ParameterBag(['visitlist' => '1', 'userid' => '7', 'starttimeh' => '9', 'starttimem' => '00']);

        $form = $this->editor()->form(0, null, null, $query, null);

        $this->assertTrue($form->active);
        $this->assertSame('', $form->sequence);
    }

    public function testBeforeSavingAVisitListPatientNotesItsCopies(): void
    {
        $copies = $this->editor([self::appointmentRow(42, 7)])->beforeSave(self::post('save'), 42);

        $this->assertInstanceOf(VisitListCopies::class, $copies);
        $this->assertSame('2026-03-09 09:00', $copies->startByProvider[7]->format('Y-m-d H:i'));
    }

    /**
     * @return array<string, array{ParameterBag, ?int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nothingToNoteProvider(): array
    {
        return [
            'a new appointment' => [self::post('save'), null],
            'a duplicate' => [self::post('duplicate'), 42],
            'a delete' => [self::post('delete'), 42],
        ];
    }

    public function testBeforeSavingATimedAppointmentNotesNothing(): void
    {
        // It is checked for being a Visit List block, which it is not.
        $this->assertNull($this->editor([self::appointmentRow(42, 7)])->beforeSave(new ParameterBag(['form_action' => 'save']), 42));
    }

    #[DataProvider('nothingToNoteProvider')]
    public function testBeforeSavingNotesNothingForAnythingElse(ParameterBag $post, ?int $eventId): void
    {
        $this->assertNull($this->editor([self::appointmentRow(42, 7)])->beforeSave($post, $eventId));
        $this->assertSame([], $this->queries);
    }

    public function testSavingAVisitListPatientPlacesItWithItsOrderAndStop(): void
    {
        $editor = $this->editor([self::appointmentRow(42, 7)], [self::blockRow(1, 7)]);

        $editor->afterSave(self::post('save', ['form_visit_seq' => ' 3 ', 'form_visit_stop' => '2']), 42, null);

        $this->assertCount(1, $this->statements);
        $this->assertSame([3, 2, '09:00:00', 42], $this->statements[0][1]);
    }

    public function testDuplicatingAVisitListPatientPlacesTheCopy(): void
    {
        $editor = $this->editor([self::appointmentRow(42, 7)], [self::blockRow(1, 7)]);

        $editor->afterSave(self::post('duplicate', ['form_visit_seq' => '3']), 42, null);

        $this->assertCount(1, $this->statements);
    }

    public function testOrderAndStopThatAreNotPositiveNumbersAreLeftEmpty(): void
    {
        $editor = $this->editor([self::appointmentRow(42, 7)], [self::blockRow(1, 7)]);

        $editor->afterSave(self::post('save', ['form_visit_seq' => 'x', 'form_visit_stop' => '0']), 42, null);

        $this->assertSame([null, null, '09:00:00', 42], $this->statements[0][1]);
    }

    public function testSavingUsesTheCopiesNotedBefore(): void
    {
        $editor = $this->editor([self::appointmentRow(43, 8)]);

        $editor->afterSave(self::post('save'), 42, new VisitListCopies(77, []));

        $this->assertSame([42, 77], $this->queries[0][1]);
    }

    /**
     * @return array<string, array{ParameterBag, ?int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function nothingToPlaceProvider(): array
    {
        return [
            'no appointment' => [self::post('save'), null],
            'a timed appointment' => [new ParameterBag(['form_action' => 'save']), 42],
            'a delete' => [self::post('delete'), 42],
        ];
    }

    #[DataProvider('nothingToPlaceProvider')]
    public function testSavingPlacesNothingForAnythingElse(ParameterBag $post, ?int $eventId): void
    {
        $this->editor([self::appointmentRow(42, 7)])->afterSave($post, $eventId, null);

        $this->assertSame([], $this->statements);
    }

    /**
     * @return array<string, array{mixed, ?int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function eventIdProvider(): array
    {
        return [
            'an int' => [42, 42],
            'a numeric string' => ['42', 42],
            'zero, a new appointment' => [0, null],
            'a negative' => [-1, null],
            'not a number' => ['abc', null],
            'null' => [null, null],
            'false' => [false, null],
        ];
    }

    #[DataProvider('eventIdProvider')]
    public function testEventId(mixed $eid, ?int $expected): void
    {
        $this->assertSame($expected, VisitListEditor::eventId($eid));
    }

    public function testBeforeSavingABlockOfAnotherCategoryNotesNothing(): void
    {
        $post = new ParameterBag(['form_action' => 'save', 'form_category' => '9', 'recurr_affect' => 'all', 'selected_date' => '2026-03-09']);

        $this->assertNull($this->editor([self::appointmentRow(42, 7)])->beforeSave($post, 42));
    }

    public function testBeforeSavingAVisitListBlockWithoutPatientsNotesNothing(): void
    {
        $block = self::appointmentRow(42, 7) + ['pc_catid' => '21', 'pc_recurrtype' => '0', 'pc_recurrspec' => ''];
        $post = new ParameterBag(['form_action' => 'save', 'form_category' => '21', 'recurr_affect' => '', 'selected_date' => '2026-03-09']);

        $this->assertNull($this->editor([$block], [self::blockRow(42, 7)])->beforeSave($post, 42));
    }

    public function testBeforeSavingAVisitListBlockNotesItsLists(): void
    {
        $block = self::appointmentRow(42, 7) + ['pc_catid' => '21', 'pc_recurrtype' => '0', 'pc_recurrspec' => ''];
        $patient = ['pc_eid' => '10', 'pc_pid' => '510', 'pc_aid' => '7', 'pc_eventDate' => '2026-03-09', 'pc_startTime' => '09:00:00',
            'pc_visit_seq' => '1', 'pc_visit_stop' => null, 'last' => '2026-03-09'];
        $post = new ParameterBag(['form_action' => 'save', 'form_category' => '21', 'recurr_affect' => '', 'selected_date' => '2026-03-09']);

        $save = $this->editor([$block], [self::blockRow(42, 7)], [$patient])->beforeSave($post, 42);

        $this->assertInstanceOf(VisitListBlockSave::class, $save);
        $this->assertSame(VisitListRepeatScope::AllDates, $save->scope, 'a block that does not repeat changes its one date');
        $this->assertSame([42], $save->eventIds);
        $this->assertSame([7], $save->providerIds);
        $this->assertCount(1, $save->lists);
        $this->assertSame([], $this->statements, 'nothing is written before the save');
    }

    public function testAfterSavingABlockItSaysWhichPatientsCouldNotMove(): void
    {
        $translation = OEGlobalsBag::getInstance()->getBoolean('disable_translation');
        OEGlobalsBag::getInstance()->set('disable_translation', true);
        try {
            $start = new DateTimeImmutable('2026-03-09 09:00');
            $list = new VisitList(
                new VisitListBlock(42, 7, 3, $start, $start->modify('+8 hours')),
                [new VisitListEntry(10, 510, 7, $start, 1, null), new VisitListEntry(11, 511, 7, $start, 2, null)]
            );
            $before = new VisitListBlockSave(VisitListRepeatScope::AllDates, [42], [7], '0', ['2026-03-09'], $start, [$list], 99);

            // The block is gone after the save, so neither patient has anywhere to go.
            $message = $this->editor()->afterSave(new ParameterBag(['form_action' => 'save']), 42, $before);
        } finally {
            OEGlobalsBag::getInstance()->set('disable_translation', $translation);
        }

        $this->assertStringStartsWith('2 Visit List patients have no date on the block', $message);
        $this->assertSame([], $this->statements);
    }

    public function testSavingAPatientSaysNothing(): void
    {
        $editor = $this->editor([self::appointmentRow(42, 7)], [self::blockRow(1, 7)]);

        $this->assertSame('', $editor->afterSave(self::post('save', ['form_visit_seq' => '1']), 42, null));
    }

    /**
     * The editor for a Visit List block (event 42, provider 7, March 9 at 9:00, not repeating)
     * with two patients on it.
     */
    private function blockEditor(string $now = '2026-01-01 12:00'): VisitListEditor
    {
        $block = self::appointmentRow(42, 7) + ['pc_catid' => '21', 'pc_recurrtype' => '0', 'pc_recurrspec' => ''];
        $patients = [];
        foreach ([10, 11] as $index => $eid) {
            $patients[] = ['pc_eid' => (string) $eid, 'pc_pid' => (string) (500 + $eid), 'pc_aid' => '7', 'pc_eventDate' => '2026-03-09',
                'pc_startTime' => '09:00:00', 'pc_visit_seq' => (string) ($index + 1), 'pc_visit_stop' => null, 'last' => '2026-03-09'];
        }
        return $this->editor([$block], [self::blockRow(42, 7)], $patients, $now);
    }

    public function testTheCalendarIntervalMustBePositive(): void
    {
        $this->expectException(DomainException::class);
        new VisitListEditor(new VisitListService(), 0);
    }

    public function testBeforeDeletingAVisitListBlockNotesItsListsToRelease(): void
    {
        $post = new ParameterBag(['form_action' => 'delete', 'recurr_affect' => '', 'selected_date' => '2026-03-09']);

        $release = $this->blockEditor()->beforeSave($post, 42);

        $this->assertInstanceOf(VisitListBlockRelease::class, $release);
        $this->assertCount(2, $release->before->lists[0]->entries);
        $this->assertSame([], $this->statements, 'nothing is written before the delete');
    }

    public function testBeforeGivingAVisitListBlockAnotherCategoryNotesItsListsToRelease(): void
    {
        $post = new ParameterBag(['form_action' => 'save', 'form_category' => '9', 'recurr_affect' => '', 'selected_date' => '2026-03-09']);

        $this->assertInstanceOf(VisitListBlockRelease::class, $this->blockEditor()->beforeSave($post, 42));
    }

    public function testAfterDeletingABlockItsPatientsGetTimesOneIntervalApart(): void
    {
        $editor = $this->blockEditor();
        $release = $editor->beforeSave(new ParameterBag(['form_action' => 'delete', 'selected_date' => '2026-03-09']), 42);

        $message = $editor->afterSave(new ParameterBag(['form_action' => 'delete']), 42, $release);

        $this->assertSame('', $message);
        $this->assertCount(2, $this->statements);
        $this->assertStringContainsString('`pc_visit_list` = 0', $this->statements[0][0]);
        $this->assertSame(['09:00:00', '09:15:00', 900, 10, '7', '2026-03-09', '09:00:00'], $this->statements[0][1]);
        $this->assertSame(['09:15:00', '09:30:00', 900, 11, '7', '2026-03-09', '09:00:00'], $this->statements[1][1]);
        $this->assertStringContainsString('`pc_aid` = ? AND `pc_eventDate` = ? AND `pc_startTime` = ?', $this->statements[0][0], 'only while the patient is still where it was');
    }

    public function testAfterDeletingABlockPatientsOnPastDatesAreLeft(): void
    {
        $editor = $this->blockEditor('2026-03-10 08:00');
        $release = $editor->beforeSave(new ParameterBag(['form_action' => 'delete', 'selected_date' => '2026-03-09']), 42);

        $editor->afterSave(new ParameterBag(['form_action' => 'delete']), 42, $release);

        $this->assertSame([], $this->statements);
    }

    public function testAVisitListBlockWithPatientsWarnsBeforeItIsDeleted(): void
    {
        $translation = OEGlobalsBag::getInstance()->getBoolean('disable_translation');
        OEGlobalsBag::getInstance()->set('disable_translation', true);
        try {
            $event = self::appointmentRow(42, 7) + ['pc_catid' => '21', 'pc_recurrtype' => '0'];
            $warning = $this->blockEditor()->form(1, 42, $event, new ParameterBag(), '2026-03-09')->blockWarning;
        } finally {
            OEGlobalsBag::getInstance()->set('disable_translation', $translation);
        }

        $this->assertSame(21, $warning['categoryId'] ?? null);
        $this->assertSame(['all'], array_keys($warning['messages']), 'a block that does not repeat has one date');
        $this->assertStringStartsWith('2 patients are scheduled in this Visit List on ', $warning['messages']['all'] ?? '');
        $this->assertStringContainsString('15 minutes apart', $warning['messages']['all'] ?? '');
        $this->assertStringStartsWith('Deleting this event cannot be undone.', $warning['deleteText']);
    }

    public function testABlockOfAnotherCategoryHasNoWarning(): void
    {
        $event = self::appointmentRow(42, 7) + ['pc_catid' => '9', 'pc_recurrtype' => '0'];

        $form = $this->blockEditor()->form(1, 42, $event, new ParameterBag(), '2026-03-09');

        $this->assertSame([], $form->blockWarning);
        $this->assertFalse($form->active);
    }

    public function testAVisitListBlockWhosePatientsArePastHasNoWarning(): void
    {
        $event = self::appointmentRow(42, 7) + ['pc_catid' => '21', 'pc_recurrtype' => '0'];

        $this->assertSame([], $this->blockEditor('2026-03-10 08:00')->form(1, 42, $event, new ParameterBag(), '2026-03-09')->blockWarning);
    }
}
