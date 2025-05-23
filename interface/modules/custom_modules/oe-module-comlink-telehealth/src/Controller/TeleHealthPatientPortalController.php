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

use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\CalendarEventCategoryRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthSessionRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\TelehealthGlobalConfig;
use Comlink\OpenEMR\Modules\TeleHealthModule\Util\CalendarUtils;
use OpenEMR\Events\PatientPortal\AppointmentFilterEvent;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\ListService;
use OpenEMR\Services\UserService;
use Symfony\Component\EventDispatcher\EventDispatcher;
use OpenEMR\Events\PatientPortal\RenderEvent;
use Symfony\Component\EventDispatcher\GenericEvent;
use Twig\Environment;

class TeleHealthPatientPortalController
{
    private $twig;
    private $assetPath;
    /**
     * @var TelehealthGlobalConfig
     */
    private $config;
    public function __construct(Environment $twig, $assetPath, TelehealthGlobalConfig $config)
    {
        $this->twig = $twig;
        $this->assetPath = $assetPath;
        $this->config = $config;
    }

    public function subscribeToEvents(EventDispatcher $eventDispatcher)
    {
        $eventDispatcher->addListener(AppointmentFilterEvent::EVENT_NAME, [$this, 'filterPatientAppointment']);
        $eventDispatcher->addListener(RenderEvent::EVENT_SECTION_RENDER_POST, [$this, 'renderTeleHealthPatientVideo']);
    }

    public function renderTeleHealthPatientVideo(GenericEvent $event)
    {

        $data = [
            'assetPath' => $this->assetPath,
            'debug' => $this->config->isDebugModeEnabled()
        ];
        echo $this->twig->render('comlink/patient-portal.twig', $data);
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
            if (
                $apptService->isCheckOutStatus($dbRecord['pc_apptstatus'])
                || $apptService->isPendingStatus($dbRecord['pc_apptstatus'])
            ) {
                $appointment['showTelehealth'] = false;
            } else {
                $appointment['showTelehealth'] = true;
            }
        }
        $event->setAppointment($appointment);
    }
}
