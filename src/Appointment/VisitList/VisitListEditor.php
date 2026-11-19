<?php

/**
 * VisitListEditor
 *
 * The Visit List part of the appointment editor (add_edit_event.php). It works out what the
 * editor shows for a Visit List patient, and places a saved Visit List patient on its list.
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
use Symfony\Component\HttpFoundation\ParameterBag;

final readonly class VisitListEditor
{
    /** pc_cattype of patient appointments, the only kind a Visit List holds. */
    private const PATIENT_CATEGORY_TYPE = 0;

    public function __construct(
        private VisitListService $service = new VisitListService(),
    ) {
    }

    /**
     * What the editor shows. An existing appointment shows its own Visit List fields. A new
     * one is a Visit List patient when it comes from a Visit List block's + button
     * (visitlist=1), which passes the block's provider, date and start time; it gets the next
     * Order # on that list.
     *
     * @param ParameterBag $query the editor's query string
     * @param mixed        $event the appointment's row, when it exists
     * @param mixed        $date  a new appointment's date (Y-m-d)
     */
    public function form(int $categoryType, ?int $eventId, mixed $event, ParameterBag $query, mixed $date): VisitListForm
    {
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
     * Before the editor saves a Visit List patient: its copies as they are now, which the
     * save changes. Null for anything else.
     *
     * @param ParameterBag $post the editor's posted form
     */
    public function beforeSave(ParameterBag $post, ?int $eventId): ?VisitListCopies
    {
        if ($eventId === null || !self::isVisitList($post) || $post->getString('form_action') !== 'save') {
            return null;
        }
        return $this->service->copiesBefore($eventId);
    }

    /**
     * After the editor saves or duplicates a Visit List patient: record its place on the list.
     *
     * @param ParameterBag     $post   the editor's posted form
     * @param ?VisitListCopies $before what beforeSave() returned
     */
    public function afterSave(ParameterBag $post, ?int $eventId, ?VisitListCopies $before): void
    {
        if (
            $eventId === null
            || !self::isVisitList($post)
            || !in_array($post->getString('form_action'), ['save', 'duplicate'], true)
        ) {
            return;
        }

        $this->service->place(
            $eventId,
            self::positive($post->getString('form_visit_seq')),
            self::positive($post->getString('form_visit_stop')),
            $before
        );
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
