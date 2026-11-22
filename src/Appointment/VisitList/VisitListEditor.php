<?php

/**
 * VisitListEditor
 *
 * The Visit List part of the appointment editor (add_edit_event.php). It works out what the
 * editor shows for a Visit List patient, places a saved Visit List patient on its list, and
 * moves a saved Visit List block's patients with the block.
 *
 * A patient on a Visit List has no time or duration of its own, only a place on the list
 * (Order #) and an optional Stop #. It keeps the block's start time and no duration, so the
 * provider, date and time put it on the block's list.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

use DateTimeImmutable;
use DomainException;
use OpenEMR\Services\Utils\DateFormatterUtils;
use Symfony\Component\HttpFoundation\ParameterBag;

final readonly class VisitListEditor
{
    /** pc_cattype of patient appointments, the only kind a Visit List holds. */
    private const PATIENT_CATEGORY_TYPE = 0;

    /** pc_cattype of provider events, the kind a Visit List block is. */
    private const PROVIDER_CATEGORY_TYPE = 1;

    /**
     * @param int $calendarInterval minutes between the times a deleted block's patients get
     *                              (the calendar_interval setting)
     */
    public function __construct(
        private VisitListService $service = new VisitListService(),
        private int $calendarInterval = 15,
    ) {
        if ($calendarInterval <= 0) {
            throw new DomainException('Calendar interval must be positive');
        }
    }

    /**
     * What the editor shows. An existing appointment shows its own Visit List fields. A new
     * one is a Visit List patient when it comes from a Visit List block's + button
     * (visitlist=1), which passes the block's provider, date and start time; it gets the next
     * Order # on that list.
     *
     * An existing Visit List block with patients from today on carries what to ask before it
     * is deleted or given another category (blockWarning()).
     *
     * @param ParameterBag $query the editor's query string
     * @param mixed        $event the appointment's row, when it exists
     * @param mixed        $date  a new appointment's date, or the date an existing one was opened on (Y-m-d)
     */
    public function form(int $categoryType, ?int $eventId, mixed $event, ParameterBag $query, mixed $date): VisitListForm
    {
        if ($categoryType === self::PROVIDER_CATEGORY_TYPE && $eventId !== null && is_array($event) && is_string($date)) {
            return new VisitListForm(false, blockWarning: $this->blockWarning($eventId, $event, $date));
        }
        if ($categoryType !== self::PATIENT_CATEGORY_TYPE) {
            return VisitListForm::inactive();
        }

        if ($eventId !== null) {
            if (!is_array($event)) {
                return VisitListForm::inactive();
            }
            return new VisitListForm(
                self::number($event['pc_visit_list'] ?? null) === '1',
                self::number($event['pc_visit_seq'] ?? null),
                self::number($event['pc_visit_stop'] ?? null),
            );
        }

        $providerId = $query->getInt('userid');
        if ($query->getString('visitlist') !== '1' || $providerId <= 0) {
            return VisitListForm::inactive();
        }

        // Digits, not getInt(): the minutes come as "00" or "05", which an int filter rejects.
        $start = is_string($date) ? DateTimeImmutable::createFromFormat(
            '!Y-m-d G:i',
            sprintf('%s %d:%02d', $date, (int) $query->getDigits('starttimeh'), (int) $query->getDigits('starttimem'))
        ) : false;
        return new VisitListForm(
            true,
            $start === false ? '' : (string) $this->service->nextSequence($providerId, $start),
        );
    }

    /**
     * Before the editor saves or deletes: for a Visit List patient being saved, its copies as
     * they are now, which the save changes. For a Visit List block with patients on the dates
     * the change affects: when it is saved as a Visit List block, those lists, so its patients
     * can move with it; when it is deleted or saved with another category, the lists to
     * release. Null for anything else.
     *
     * @param ParameterBag $post the editor's posted form
     */
    public function beforeSave(ParameterBag $post, ?int $eventId): VisitListCopies|VisitListBlockSave|VisitListBlockRelease|null
    {
        $action = $post->getString('form_action');
        if ($eventId === null || !in_array($action, ['save', 'delete'], true)) {
            return null;
        }
        if (self::isVisitList($post)) {
            return $action === 'save' ? $this->service->copiesBefore($eventId) : null;
        }

        // A Visit List block, if the event is one with patients on the dates the change affects.
        $block = $this->service->blockSave(
            $eventId,
            VisitListRepeatScope::fromPosted($post->getString('recurr_affect')),
            $post->getString('selected_date')
        );
        if ($block === null) {
            return null;
        }
        $categoryId = self::positive($post->getString('form_category'));
        $staysABlock = $action === 'save' && $categoryId !== null && $categoryId === $this->service->categoryId();
        return $staysABlock ? $block : new VisitListBlockRelease($block);
    }

    /**
     * After the editor saves or duplicates a Visit List patient: record its place on the list.
     * After it saves a Visit List block: move the block's patients with it. After it deletes a
     * block or gives it another category: the block's patients become timed appointments, one
     * calendar interval apart. Returns a message for the user when some patients could not
     * move with their block, else ''.
     *
     * @param ParameterBag                                                $post   the editor's posted form
     * @param VisitListCopies|VisitListBlockSave|VisitListBlockRelease|null $before what beforeSave() returned
     */
    public function afterSave(
        ParameterBag $post,
        ?int $eventId,
        VisitListCopies|VisitListBlockSave|VisitListBlockRelease|null $before
    ): string {
        if ($before instanceof VisitListBlockRelease) {
            $this->service->release($before->before->lists, $this->calendarInterval);
            return '';
        }
        if ($before instanceof VisitListBlockSave) {
            return self::moveMessage($this->service->moveWithBlock($before));
        }

        if (
            $eventId === null
            || !self::isVisitList($post)
            || !in_array($post->getString('form_action'), ['save', 'duplicate'], true)
        ) {
            return '';
        }

        $this->service->place(
            $eventId,
            self::positive($post->getString('form_visit_seq')),
            self::positive($post->getString('form_visit_stop')),
            $before
        );
        return '';
    }

    /**
     * What the editor asks before a Visit List block with patients from today on is deleted or
     * given another category, for each repeat scope with such patients. Empty for any other
     * event.
     *
     * @param array<mixed> $event        the event's row
     * @param string       $selectedDate the date it was opened on (Y-m-d)
     * @return array{categoryId: int, messages: array<string, string>, deleteText: string}|array{}
     */
    private function blockWarning(int $eventId, array $event, string $selectedDate): array
    {
        $categoryId = $this->service->categoryId();
        if ($categoryId === null || self::positive(self::number($event['pc_catid'] ?? null)) !== $categoryId) {
            return [];
        }

        $repeats = self::positive(self::number($event['pc_recurrtype'] ?? null)) !== null;
        $start = self::number($event['pc_startTime'] ?? null);
        $then = sprintf(
            xl('Their appointments will stay on the schedule in list order, %1$s minutes apart, starting at %2$s. Continue?'),
            $this->calendarInterval,
            DateFormatterUtils::oeFormatTime($start)
        );
        $date = DateFormatterUtils::oeFormatShortDate($selectedDate);
        $date = is_string($date) ? $date : $selectedDate;

        $messages = [];
        $scopes = $repeats ? VisitListRepeatScope::cases() : [VisitListRepeatScope::AllDates];
        foreach ($scopes as $scope) {
            $block = $this->service->blockSave($eventId, $scope, $selectedDate);
            $count = $block === null ? 0 : $this->service->upcomingPatients($block->lists);
            if ($count === 0) {
                continue;
            }
            $scheduled = match ($repeats ? $scope : VisitListRepeatScope::ThisDate) {
                VisitListRepeatScope::ThisDate => xl('%1$s patients are scheduled in this Visit List on %2$s.'),
                VisitListRepeatScope::ThisAndLater => xl('%1$s patients are scheduled in this Visit List on %2$s and later.'),
                VisitListRepeatScope::AllDates => xl('%1$s patients are scheduled in this Visit List from today on.'),
            };
            $messages[$scope->value] = sprintf($scheduled, $count, $date) . ' ' . $then;
        }
        if ($messages === []) {
            return [];
        }

        return [
            'categoryId' => $categoryId,
            'messages' => $messages,
            'deleteText' => xl('Deleting this event cannot be undone. It cannot be recovered once it is gone. Are you sure you wish to delete this event?'),
        ];
    }

    /**
     * What to tell the user about patients that did not move with their block.
     */
    private static function moveMessage(VisitListMoveResult $result): string
    {
        $lines = [];
        if ($result->stranded > 0) {
            $lines[] = sprintf(
                xl('%s Visit List patients have no date on the block after this change. Their appointments stay where they were, no longer on a list.'),
                $result->stranded
            );
        }
        if ($result->keptForOtherCopy > 0) {
            $lines[] = sprintf(
                xl('%s Visit List patients stayed with their old provider, because the new provider already has their appointment that day.'),
                $result->keptForOtherCopy
            );
        }
        if ($result->leftOnPastDates > 0) {
            $lines[] = sprintf(
                xl('%s Visit List patients on dates before today were left where they were.'),
                $result->leftOnPastDates
            );
        }
        return implode("\n", $lines);
    }

    /**
     * An appointment ID as the editor holds it, or null when there is none.
     */
    public static function eventId(mixed $eid): ?int
    {
        $eventId = is_int($eid) || is_string($eid)
            ? filter_var($eid, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : false;
        return $eventId === false ? null : $eventId;
    }

    private static function isVisitList(ParameterBag $post): bool
    {
        return $post->getString('form_visit_list') === '1';
    }

    private static function positive(string $value): ?int
    {
        $number = filter_var(trim($value), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $number === false ? null : $number;
    }

    /**
     * A number column as the form shows it: '' when not set.
     */
    private static function number(mixed $value): string
    {
        return is_int($value) || is_string($value) ? (string) $value : '';
    }
}
