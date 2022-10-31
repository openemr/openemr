<?php

/**
 * Responsible for rendering TeleHealth features on the patient portal
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Controller;

use Comlink\OpenEMR\Modules\TeleHealthModule\Util\CalendarUtils;
use OpenEMR\Events\PatientPortal\AppointmentFilterEvent;
use OpenEMR\Services\AppointmentService;
use Symfony\Component\EventDispatcher\EventDispatcher;
use OpenEMR\Events\PatientPortal\RenderEvent;
use Symfony\Component\EventDispatcher\GenericEvent;
use Twig\Environment;

class TeleHealthPatientPortalController
{
    private $twig;
    private $assetPath;
    public function __construct(Environment $twig, $assetPath)
    {
        $this->twig = $twig;
        $this->assetPath = $assetPath;
    }

    public function subscribeToEvents(EventDispatcher $eventDispatcher)
    {
        $eventDispatcher->addListener(AppointmentFilterEvent::EVENT_NAME, [$this, 'filterPatientAppointment']);
        $eventDispatcher->addListener(RenderEvent::EVENT_SECTION_RENDER_POST, [$this, 'renderTeleHealthPatientVideo']);
    }

    public function renderTeleHealthPatientVideo(GenericEvent $event)
    {
        echo $this->twig->render('comlink/patient-portal.twig', ['assetPath' => $this->assetPath]);
    }

    public function filterPatientAppointment(AppointmentFilterEvent $event)
    {
        $dbRecord = $event->getDbRecord();
        $appointment = $event->getAppointment();
        // 'appointmentDate' => $dayname . ', ' . $row['pc_eventDate'] . ' ' . $disphour . ':' . $dispmin . ' ' . $dispampm,
        $dateTime = \DateTime::createFromFormat("Y-m-d H:i:s", $dbRecord['pc_eventDate']
            . " " . $dbRecord['pc_startTime']);

        $apptService = new AppointmentService();

        $appointment['showTelehealth'] = false;
        if (
            $dateTime !== false && CalendarUtils::isAppointmentDateTimeInSafeRange($dateTime)
            // since this hits the database we do this one last
        ) {
            if ($apptService->isCheckOutStatus($dbRecord['pc_apptstatus'])) {
                $appointment['showTelehealth'] = false;
            } else {
                $appointment['showTelehealth'] = true;
            }
        }
        $event->setAppointment($appointment);
    }
}
