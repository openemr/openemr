<?php

/**
 * Handles all of the Provider Calendar events and actions
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Controller;

use Comlink\OpenEMR\Modules\TeleHealthModule\Util\CalendarUtils;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\CalendarEventCategoryRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthProviderRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\TelehealthGlobalConfig;
use Comlink\OpenEMR\Modules\TeleHealthModule\The;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Utils\CacheUtils;
use OpenEMR\Events\Appointments\AppointmentJavascriptEventNames;
use OpenEMR\Events\Appointments\AppointmentRenderEvent;
use OpenEMR\Events\Appointments\CalendarUserGetEventsFilter;
use OpenEMR\Events\Core\ScriptFilterEvent;
use OpenEMR\Events\Core\StyleFilterEvent;
use OpenEMR\Services\AppointmentService;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig\Environment;

class TeleHealthCalendarController
{
    private $logger;
    private $assetPath;
    /**
     * @var The database record if of the currently logged in user
     */
    private $loggedInUserId;

    /**
     * @var CalendarEventCategoryRepository
     */
    private $calendarEventCategoryRepository;

    /**
     * @var AppointmentService
     */
    private $apptService;

    /**
     * @var Environment Twig container
     */
    private $twig;

    public function __construct(TelehealthGlobalConfig $config, Environment $twig, SystemLogger $logger, $assetPath, $loggedInUserId)
    {
        $this->twig = $twig;
        $this->logger = $logger;
        $this->assetPath = $assetPath;
        $this->loggedInUserId = $loggedInUserId;
        $this->calendarEventCategoryRepository = new CalendarEventCategoryRepository();
        $this->teleHealthProviderRepository = new TeleHealthProviderRepository($this->logger, $config);
//        $this->apptService = new AppointmentService();
    }

    public function subscribeToEvents(EventDispatcher $eventDispatcher)
    {
        $eventDispatcher->addListener(CalendarUserGetEventsFilter::EVENT_NAME, [$this, 'filterTelehealthCalendarEvents']);
        $eventDispatcher->addListener(ScriptFilterEvent::EVENT_NAME, [$this, 'addCalendarJavascript']);
        $eventDispatcher->addListener(StyleFilterEvent::EVENT_NAME, [$this, 'addCalendarStylesheet']);

        $eventDispatcher->addListener(AppointmentRenderEvent::RENDER_JAVASCRIPT, [$this, 'renderAppointmentJavascript']);
        $eventDispatcher->addListener(AppointmentRenderEvent::RENDER_BELOW_PATIENT, [$this, 'renderPatientValidationDiv']);
        $eventDispatcher->addListener(AppointmentRenderEvent::RENDER_BELOW_PATIENT, [$this, 'renderAppointmentsLaunchSessionButton']);
    }

    public function getAppointmentService()
    {
        if (!isset($this->apptService)) {
            $this->apptService = new AppointmentService();
        }
        return $this->apptService;
    }

    public function filterTelehealthCalendarEvents(CalendarUserGetEventsFilter $event)
    {
        $canProviderStartTelehealth = $this->teleHealthProviderRepository->isEnabledProvider($this->loggedInUserId);

        $eventsByDay = $event->getEventsByDays();
        $keys = array_keys($eventsByDay);
        $apptService = $this->getAppointmentService();
        foreach ($keys as $key) {
            $eventCount = count($eventsByDay[$key]);
            for ($i = 0; $i < $eventCount; $i++) {
                $catId = $eventsByDay[$key][$i]['catid'];
                // we need to also check and see if the event is an old event (ie less than today's date)
                if (!empty($this->calendarEventCategoryRepository->getEventCategoryForId($catId))) {
                    $eventViewClasses = ["event_appointment", "event_telehealth"];
                    $dateTime = \DateTime::createFromFormat("Y-m-d H:i:s", $eventsByDay[$key][$i]['eventDate']
                        . " " . $eventsByDay[$key][$i]['startTime']);

                    // if the event belongs to a different user then we need to go get it.
                    if ($eventsByDay[$key][$i]['aid'] != $this->loggedInUserId) {
                        $this->logger->debug(
                            "TeleHealthCalendarController->filterTelehealthCalendarEvents() different user id then logged in user",
                            ['assignedProvider' => $eventsByDay[$key][$i]['aid'], 'loggedinProvider' => $this->loggedInUserId]
                        );
                        $eventViewClasses[] = "event_user_different";
                    }

                    if ($apptService->isCheckOutStatus($eventsByDay[$key][$i]['apptstatus'])) {
                        $eventViewClasses[] = "event_telehealth_completed";
                    } else if ($dateTime !== false && CalendarUtils::isAppointmentDateTimeInSafeRange($dateTime)) {
                        // if a provider is not enrolled we want to show that status instead of letting them launch the appt.
                        if (!$canProviderStartTelehealth) {
                            $eventViewClasses[] = "event_telehealth_unenrolled";
                        } else {
                            $this->logger->debug("calendarEvent filter  Time is ", ['time' => $dateTime]);
                            $eventViewClasses[] = "event_telehealth_active";
                        }
                    } else if ($dateTime == false) {
                        $this->logger->errorLogCaller("Failed to create DateTime object for calendar event", ['pc_eid' => $eventsByDay[$key][$i]['eid']]);
                    }
                    $eventsByDay[$key][$i]['eventViewClass'] = implode(" ", $eventViewClasses);
                }
            }
        }
        $event->setEventsByDays($eventsByDay);
        return $event;
    }

    public function addCalendarStylesheet(StyleFilterEvent $event)
    {
        if ($this->isCalendarPageInclude($event->getPageName())) {
            $styles = $event->getStyles();
            $styles[] = $this->getAssetPath() . CacheUtils::addAssetCacheParamToPath("css/telehealth.css");
            $event->setStyles($styles);
        }
    }

    public function renderAppointmentJavascript(AppointmentRenderEvent $event)
    {
        $appt = $event->getAppt();
        // we need to grab the telehealth providers
        $categories = $this->calendarEventCategoryRepository->getEventCategories();
        $categoryIds = array_keys($categories);
        $providers = $this->teleHealthProviderRepository->getEnabledProviders();
        $providerIds = array_map(function ($provider) {
            return intval($provider->getDbRecordId());
        }, $providers);

        $jsAppointmentEventNames = [
            'appointmentSetEvent' => AppointmentJavascriptEventNames::APPOINTMENT_PATIENT_SET_EVENT
        ];
        //
        echo $this->twig->render(
            "comlink/appointment/add_edit_event.js.twig",
            [
                'appt' => $appt
                , 'providers' => $providerIds, 'categories' => $categoryIds
                , 'jsAppointmentEventNames' => $jsAppointmentEventNames
            ]
        );
    }

    public function addCalendarJavascript(ScriptFilterEvent $event)
    {

        $pageName = $event->getPageName();
        $scriptPath = '';
        // backwards compatible with older versions of OpenEMR
        if (defined('OpenEMR\Events\Core\ScriptFilterEvent::CONTEXT_ARGUMENT_SCRIPT_NAME')) {
            $scriptPath = $event->getContextArgument(ScriptFilterEvent::CONTEXT_ARGUMENT_SCRIPT_NAME) ?? '';
        }
        // currently security restrictions support scripts only on the filesystem.  Translations then
        // are being pulled from the top level comlink instead of the inner iframe.  We may need to adjust this
        // if we run into issues.
        if ($this->isCalendarPageInclude($pageName)) {
            $scripts = $event->getScripts();
            // note the cache buster is already being populated in Header.php since this script isn't a registered
            // asset
            $scripts[] = $this->getAssetPath() . "js/telehealth-calendar.js";
            $event->setScripts($scripts);
        } else if ($this->isAppointmentPageInclude($pageName, $scriptPath)) {
            // note the cache buster is already being populated in Header.php since this script isn't a registered
            // asset
            $scripts = $event->getScripts();
            $scripts[] = $this->getAssetPath() . "../index.php?action=get_telehealth_settings";
            $scripts[] = $this->getAssetPath() . "js/telehealth-calendar.js";
            $scripts[] = $this->getAssetPath() . "js/telehealth-appointment.js";
            $event->setScripts($scripts);
        }
    }

    public function renderPatientValidationDiv(AppointmentRenderEvent $event)
    {
        echo "<div class='patient-validation-div d-none alert mt-1 mb-1'></div>";
    }
    public function renderAppointmentsLaunchSessionButton(AppointmentRenderEvent $event)
    {
        $row = $event->getAppt();
        if (empty($row['pc_eid'])) {
            return;
        }
        if (empty($this->calendarEventCategoryRepository->getEventCategoryForId($row['pc_catid']))) {
            return;
        }
        // don't show the launch button for a complete status
        if ($this->getAppointmentService()->isCheckOutStatus($row['pc_apptstatus'])) {
            echo "<button class='mt-2 btn btn-disabled' disabled><i class='fa fa-video m-2'></i>"
                . xlt("TeleHealth Session Ended") . "</button>";
            echo "<p>" . xlt("Session has been completed.") . " "
                . xlt("Change the appointment status in order to launch this session again.") . "</p>";
            return;
        }
        $eventDateTimeString = $row['pc_eventDate'] . " " . $row['pc_startTime'];
        $dateTime = \DateTime::createFromFormat("Y-m-d H:i:s", $eventDateTimeString);
        if ($dateTime === false) {
            (new SystemLogger())->errorLogCaller("appointment date time string was invalid", ['pc_eid' => $row['pc_eid'], 'dateTime' => $eventDateTimeString]);
            return;
        }

        if (CalendarUtils::isAppointmentDateTimeInSafeRange($dateTime)) {
            echo "<button data-eid='" . attr($row['pc_eid']) . "' data-pid='" . attr($row['pc_pid'])
                . "' class='mt-2 btn btn-primary btn-add-edit-appointment-launch-telehealth'><i class='fa fa-video m-2'></i>"
                . xlt("Launch TeleHealth Session") . "</button>";
        } else {
            echo "<button class='mt-2 btn btn-disabled' disabled><i class='fa fa-video m-2'></i>"
                . xlt("TeleHealth Session Expired") . "</button>";
            echo "<p>" . xlt("Session can only be launched two hours before or after an appointment") . "</p>";
        }
    }

    private function isAppointmentPageInclude($pageName, $scriptPath)
    {
        // make sure our script path is in calendar
        return $pageName == "add_edit_event.php" && basename(dirname($scriptPath)) == 'calendar';
    }

    private function isCalendarPageInclude($pageName)
    {
        return $pageName == 'pnuserapi.php' || $pageName == 'pnadmin.php';
    }

    private function getAssetPath()
    {
        return $this->assetPath;
    }
}
