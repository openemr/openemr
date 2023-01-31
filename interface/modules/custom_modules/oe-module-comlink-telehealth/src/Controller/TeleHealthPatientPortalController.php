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
        // we need to grab any sessions where the
        if (empty($_SESSION['pid'])) {
            return $event;
        }

        $sessionRepo = new TeleHealthSessionRepository();
        $apptService = new AppointmentService();
        $userService = new UserService();
        $listService = new ListService();

        // only grab the 20 most recent sessions we've been a part of and we can do all our searching from there.
        // we do that to avoid grabbing an entire telehealth session history for the patient.
        $sessions = $sessionRepo->getSessionsWithAppointmentDataForRelatedPatient($_SESSION['pid'], 20);
        // we need to check if our calendar time is here
        $filteredSessions = [];
        foreach ($sessions as $session) {
            $dateTime = \DateTime::createFromFormat("Y-m-d H:i:s", $session['pc_eventDate']
                . " " . $session['pc_startTime']);
            // we want to display title, event status, provider information
            // pc_title, pc_eventDate, pc_startTime, pc_apptstatus, pc_eid

            if ($dateTime !== false
                && CalendarUtils::isAppointmentDateTimeInSafeRange($dateTime)
                && !$apptService->isCheckOutStatus($session['pc_apptstatus'])
            )
            {
                $user =  $userService->getUser($session['pc_aid']);
                $filteredSessions[] = [
                    'appointmentDate' => $dateTime->format('l, Y-m-d H:i:s A')
                    ,'appointmentType' => xl('Type') . ': ' . $session['pc_catname']
                    ,'provider' => xl('Provider') . ': ' . ($user['provider_fname'] ?? '') . ' ' . ($user['provider_lname'] ?? '')
                    ,'status' => xl('Status') . ': ' . $session['pc_apptstatus_lo_title']
                    ,'mode' => (int)$session['pc_recurrtype'] > 0 ? 'recurring' : $session['pc_recurrtype']
                    ,'icon_type' => (int)$session['pc_recurrtype'] > 0
                    ,'etitle' => !empty($row['pc_hometext']) ? (xl('Comments') . ': ' . $row['pc_hometext'] . "\r\n") : ""
                    ,'pc_eid' => $session['pc_eid']
                    ,'active' => CalendarUtils::isTelehealthSessionInActiveTimeRange($session)
                ];
            }
        }
        $activeSessionToLaunch = null;
        if (count($filteredSessions) == 1 && $filteredSessions[0]['active'] == true) {
            $activeSessionToLaunch = $filteredSessions[0];
        }
        $data = [
            'assetPath' => $this->assetPath,
            'debug' => $this->config->isDebugModeEnabled(),
            'thirdPartySessions' => $filteredSessions,
            'activeSession' => $activeSessionToLaunch
        ];
        echo $this->twig->render('comlink/patient-portal.twig', $data);
    }

    public function filterPatientAppointment(AppointmentFilterEvent $event)
    {
        // TODO: need to handle data element where this is a third party joining...
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
