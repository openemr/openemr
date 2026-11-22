<?php

/**
 * VisitListDeleteSubscriber
 *
 * Closes the gap a deleted Visit List patient leaves: before AppointmentService deletes an
 * appointment it notes the list the patient is on, and afterwards renumbers that list 1 to n.
 * AppointmentService::deleteAppointmentRecord() sends both events, so this covers deletes
 * from the appointment editor and from the REST API.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

use OpenEMR\Events\Services\ServiceDeleteEvent;
use OpenEMR\Services\AppointmentService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class VisitListDeleteSubscriber implements EventSubscriberInterface
{
    /** @var array<int, VisitList> the list each appointment being deleted is on, by event ID */
    private array $pending = [];

    public function __construct(
        private readonly VisitListService $service = new VisitListService(),
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ServiceDeleteEvent::EVENT_PRE_DELETE => 'beforeDelete',
            ServiceDeleteEvent::EVENT_POST_DELETE => 'afterDelete',
        ];
    }

    public function beforeDelete(ServiceDeleteEvent $event): void
    {
        $eventId = self::appointmentId($event);
        if ($eventId === null) {
            return;
        }
        $list = $this->service->listOf($eventId);
        if ($list !== null) {
            $this->pending[$eventId] = $list;
        }
    }

    public function afterDelete(ServiceDeleteEvent $event): void
    {
        $eventId = self::appointmentId($event);
        if ($eventId === null || !isset($this->pending[$eventId])) {
            return;
        }
        $left = $this->pending[$eventId];
        unset($this->pending[$eventId]);

        $list = $this->service->current($left);
        if ($list !== null) {
            $this->service->renumber($list);
        }
    }

    /**
     * The appointment's event ID when an appointment is being deleted, else null.
     */
    private static function appointmentId(ServiceDeleteEvent $event): ?int
    {
        if (!$event->getService() instanceof AppointmentService) {
            return null;
        }
        return VisitListEditor::eventId($event->getRecordId());
    }
}
