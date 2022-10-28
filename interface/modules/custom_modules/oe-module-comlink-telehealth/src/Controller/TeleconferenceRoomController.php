<?php

/**
 * Handles all of the page rendering and api communications for a teleconference room.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule\Controller;

use Comlink\OpenEMR\Modules\TeleHealthModule\Bootstrap;
use Comlink\OpenEMR\Modules\TeleHealthModule\Exception\TelehealthProviderNotEnrolledException;
use Comlink\OpenEMR\Modules\TeleHealthModule\Exception\TeleHealthProviderSuspendedException;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthSessionRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\Controller\TeleHealthFrontendSettingsController;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthUserRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\Controller\TeleHealthVideoRegistrationController;
use Comlink\OpenEMR\Modules\TeleHealthModule\The;
use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\EncounterSessionUtil;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\AddressService;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\ListService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\ProcessingResult;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Exception;
use InvalidArgumentException;
use RuntimeException;

class TeleconferenceRoomController
{
    const PATIENT_PORTAL_USER  = 'portal-user';

    const APPOINTMENT_TRANSFER_STATUS = "TRNSFR";

    /**
     * Status code sent to client to disable registration check as the user is not enrolled or their enrollment is
     * suspended.
     */
    const REGISTRATION_CHECK_REQUIRES_ENROLLMENT_CODE = 402;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var boolean  Whether we are running as a patient in the portal context
     */
    private $isPatient;

    /**
     * @var string The location where the module assets are stored
     */
    private $assetPath;

    /**
     * @var EncounterService
     */
    private $encounterService;

    /**
     * @var AppointmentService
     */
    private $appointmentService;

    /**
     * @var \Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthSessionRepository
     */
    private $sessionRepository;

    /**
     * @var TeleHealthUserRepository
     */
    private $telehealthUserRepo;

    /**
     * @var TeleHealthVideoRegistrationController
     */
    private $telehealthRegistrationController;

    public function __construct(Environment $twig, LoggerInterface $logger, TeleHealthVideoRegistrationController $registrationController, $assetPath, $isPatient = false)
    {
        $this->assetPath = $assetPath;
        $this->twig = $twig;
        $this->logger = $logger;
        $this->isPatient = $isPatient;
        $this->appointmentService = new AppointmentService();
        $this->encounterService = new EncounterService();
        $this->sessionRepository = new TeleHealthSessionRepository();
        $this->telehealthRegistrationController = $registrationController;
        $this->telehealthUserRepo = new TeleHealthUserRepository();
    }

    public function dispatch($action, $queryVars)
    {
        $this->logger->debug("TeleconferenceRoomController->dispatch()", ['action' => $action, 'queryVars' => $queryVars, 'isPatient' => $this->isPatient]);

        if ($action == 'get_telehealth_launch_data') {
            $this->getTeleHealthLaunchDataAction($queryVars);
        } else if ($action == 'set_appointment_status') {
            $this->setAppointmentStatusAction($queryVars);
        } else if ($action == 'set_current_appt_encounter') {
            return $this->setCurrentAppointmentEncounter($queryVars);
        } else if ($action == 'patient_appointment_ready') {
            return $this->patientAppointmentReadyAction($queryVars);
        } else if ($action == 'conference_session_update') {
            return $this->conferenceSessionUpdateAction($queryVars);
        } else if ($action == 'check_registration') {
            return $this->checkRegistrationAction($queryVars);
        } else if ($action == 'get_telehealth_settings') {
            return $this->getTeleHealthFrontendSettingsAction($queryVars);
        } else {
            $this->logger->error(self::class . '->dispatch() invalid action found', ['action' => $action]);
            echo "action not supported";
            return;
        }
    }

    public function setAppointmentStatusAction($queryVars)
    {
        $pc_eid = $queryVars['pc_eid'];
        $status = $queryVars['status'];
        $authUser = $queryVars['authUser'];

        try {
            $userRepo = new UserService();
            $user = $userRepo->getUserByUsername($authUser);

            $appt = $this->appointmentService->getAppointment($pc_eid);

            if (empty($appt)) {
                throw new InvalidArgumentException("Invalid appointment pc_eid.  Appointment does not exist");
            }

            if ($this->isPatient) {
                // throw error
                throw new InvalidArgumentException("Cannot update appointment status in a patient context");
            }
            if (!AclMain::aclCheckCore('patients', 'appt', '', array('write','wsome'))) {
                throw new AccessDeniedException("patients", "appt", "Does not have ACL permission to update appointment status");
            }

            $session = $this->sessionRepository->getSessionByAppointmentId($pc_eid);
            if (empty($session)) {
                // throw an error
                throw new InvalidArgumentException("Invalid appointment eid");
            }
            if ($session['user_id'] != $user['id']) {
                // throw an error
                throw new AccessDeniedException("patients", "appt", "Cannot update appointment that belongs to different user");
            }

//            if (!$this->appointmentService->isValidAppointmentStatus($status)) {
            if (!$this->isValidAppointmentStatus($status)) {
                throw new InvalidArgumentException("Invalid appointment status received");
            }
            $this->appointmentService->updateAppointmentStatus($pc_eid, $status, $session['user_id'], $session['encounter']);
            $this->logger->debug(
                "TeleconferenceRoomController->setAppointmentStatusAction() Updated appointment status",
                ['pc_eid' => $pc_eid, 'status' => $status, 'authUser' => $authUser]
            );
            echo json_encode(['status' => 'success']);
        } catch (InvalidArgumentException $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString(),
                'queryVars' => $queryVars]);
            http_response_code(400);
            echo json_encode(['error' => 'invalid argument sent.  Check server logs for details']);
        } catch (AccessDeniedException $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString(),
                'queryVars' => $queryVars]);
            http_response_code(403);
            echo json_encode(['error' => 'Access denied to patient telehealth information.']);
        } catch (Exception $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString(),
                'queryVars' => $queryVars]);
            http_response_code(500);
            echo json_encode(['error' => 'server error occurred.  Check server logs for details']);
        }
    }

    public function getTeleHealthLaunchDataAction($queryVars)
    {
        try {
            // grab the settings, grab the waiting room & grab the conference room
            $waitingRoom = $this->renderWaitingRoom($queryVars);
            if ($this->isPatient) {
                $settings = $this->getPatientSettings($queryVars);
            } else {
                $settings = $this->getProviderSettings($queryVars);
            }
            $conferenceRoom = $this->renderConferenceRoom($queryVars);

            $result = [
                // waiting room has already been escaped via twig rendering
                'waitingRoom' => $waitingRoom
                , 'callerSettings' => textArray($settings)
                // conference room has already been escaped via twig rendering
                , 'conferenceRoom' => $conferenceRoom
            ];
            echo json_encode($result);
        } catch (\Exception $exception) {
            $this->logger->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString(),
                'queryVars' => $queryVars]);
            http_response_code(500);
            echo json_encode(['error' => 'server error occurred.  Check server logs for details']);
        }
    }

    public function getTeleHealthFrontendSettingsAction($queryVars)
    {
        $controller = new TeleHealthFrontendSettingsController($this->assetPath, $this->twig);
        echo $controller->renderFrontendSettings();
    }

    public function conferenceSessionUpdateAction($queryVars)
    {
        $pc_eid = $queryVars['eid'];
        $session = $this->sessionRepository->getSessionByAppointmentId($pc_eid);
        try {
            if (empty($session)) {
                // throw error
                throw new \InvalidArgumentException("Could not find session for appointment id");
            }
            $isProvider = !$this->isPatient;

            if ($this->isPatient && empty($session['patient_start_time'])) {
                $this->sessionRepository->updateStartTimestamp($pc_eid, $isProvider);
            } else if ($isProvider && empty($session['provider_start_time'])) {
                $this->sessionRepository->updateStartTimestamp($pc_eid, $isProvider);
            }
            $this->logger->debug("Updating lastSeenTimestamp", ['pc_eid' => $pc_eid, 'isProvider' => $isProvider]);
            $this->sessionRepository->updateLastSeenTimestamp($pc_eid, $isProvider);
            echo json_encode(['status' => 'success']);
        } catch (\Exception $exception) {
            $this->logger->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString(),
                'queryVars' => $queryVars]);
            http_response_code(500);
            echo json_encode(['error' => 'server error occurred.  Check server logs for details']);
        }
    }

    public function patientAppointmentReadyAction($queryVars)
    {
        $pc_eid = $queryVars['eid'];

        $session = $this->sessionRepository->getSessionByAppointmentId($pc_eid);
        $result = [
            'session' => [
                'pc_eid' => $pc_eid
                ,'providerReady' => false
                ,'calleeUuid' => null
            ]
        ];
        $this->logger->debug("patientAppointmentReadyAction()", ['session' => $session]);
        try {
            if (!empty($session)) {
                $result['session']['id'] = $session['id'];
                // provider has started the session, let's verify the last update time
                if (!empty($session['provider_last_update'])) {
                    $dateTime = \DateTime::createFromFormat("Y-m-d H:i:s", $session['provider_last_update']);
                    $currentDateTime = new \DateTime();
                    $this->logger->debug("checking time ", ['provider_last_update' => $currentDateTime->format("Y-m-d H:i:s"), 'now' => $currentDateTime->format("Y-m-d H:i:s")]);
                    if ($currentDateTime < $dateTime->add(new \DateInterval("PT15S"))) {
                        $result['session']['providerReady'] = true;
                    }
                    $userRepo = new UserService();
                    $user = $userRepo->getUser($session['user_id']);
                    if (empty($user)) {
                        throw new InvalidArgumentException("Failed to get user with session id " . $session['id']);
                    }

                    // in the event that we never change the login credentials we grab it from the session here...
                    $telehealthCredentials = $this->telehealthUserRepo->getUser($user['uuid']);
                    if (empty($telehealthCredentials)) {
                        throw new InvalidArgumentException("Failed to get telehealth credentials with username " . $user['username']);
                    }
                    $result['session']['calleeUuid'] = $telehealthCredentials->getUsername();
                }
            }
            echo text(json_encode($result));
        } catch (Exception $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString(),
                'queryVars' => $queryVars]);
            http_response_code(500);
            echo json_encode(['error' => 'server error occurred.  Check server logs for details']);
        }
    }

    public function checkRegistrationAction($queryVars)
    {
        try {
            if ($this->isPatient) {
                $patient = $this->getPatientForPid($queryVars['pid']);
                $settings = $this->getOrCreateTelehealthPatient($patient);
            } else {
                $userRepo = new UserService();
                $user = $userRepo->getUserByUsername($queryVars['authUser']);
                $settings = $this->getOrCreateTelehealthProvider($user);
            }
            $jsonSettings = json_encode([
                'username' => $settings->getUsername()
                , 'isPatient' => $this->isPatient == true
                , 'dbRecordId' => $settings->getId()
            ]);
            $this->logger->debug("check registration finished ", ['settings' => $jsonSettings]);
            echo text($jsonSettings);
        } catch (TelehealthProviderNotEnrolledException | TeleHealthProviderSuspendedException $exception) {
            $jsonSettings = text(json_encode(['errorCode' => self::REGISTRATION_CHECK_REQUIRES_ENROLLMENT_CODE
                , 'errorMessage' => xl("User has no active TeleHealth enrollment and registration is skipped")]));
            $this->logger->debug("check registration finished ", ['settings' => $jsonSettings]);
            echo $jsonSettings;
        } catch (Exception $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString(),
                'queryVars' => $queryVars]);
            http_response_code(500);
            echo json_encode(['error' => 'server error occurred.  Check server logs for details']);
        }
    }

    public function shouldChangeProvider($appt, $userId)
    {
        if ($appt['pc_aid'] != $userId) {
            return true;
        }
        return false;
    }

    public function copyAppointmentAndChangeProvider($appt, $user)
    {
        $userId = $user['id'];
        $auditTrail = $user['fname'] . ' ' . $user['lname'] . '(' . $user['username'] . ')';

        $sql = "UPDATE openemr_postcalendar_events SET pc_aid =? WHERE pc_eid =? ";
        QueryUtils::sqlStatementThrowException($sql, [$userId, $appt['pc_eid']]);

        if (!AclMain::aclCheckCore('patients', 'appt', '', array('write','wsome'))) {
            throw new AccessDeniedException("patients", "appt", "No access to change appointments");
        }

        if ($appt['pc_apptstatus'] != self::APPOINTMENT_TRANSFER_STATUS) {
            $insertKeys = [
                "pc_catid",
                "pc_title",
                "pc_duration",
                "pc_hometext",
                "pc_eventDate",
                "pc_apptstatus",
                "pc_startTime",
                "pc_facility",
                "pc_billing_location",
                "pc_aid"
            ];
            $insertArray = [];
            foreach ($insertKeys as $key) {
                $insertArray[$key] = $appt[$key] ?? null;
            }

            $homeText = $insertArray['pc_hometext'] ?? '';
            $homeText .= "\r\n" . xlt("Transferred TeleHealth session to another provider.")
                . xl("Transfer Time") . " " . date("Y-m-d H:i:s") . ". " . xl("Provider") . ' ' . $auditTrail;
            $insertArray['pc_hometext'] = $homeText;
            $this->logger->debug("Attempting to insert appointment", ['appointment' => $insertArray, 'pid' => $appt['pid'], 'origAppt' => $appt]);
            $newApt = $this->appointmentService->insert($appt['pc_pid'], $insertArray);
            $this->appointmentService->updateAppointmentStatus($newApt, self::APPOINTMENT_TRANSFER_STATUS, $userId);
        }
        $appt['pc_aid'] = $userId;

        return $appt;
    }
    public function setCurrentAppointmentEncounter($queryVars)
    {
        try {
            // grab the appointment and make sure the current user has access to the calendar
            // if no access throw access denied exception
            // otherwise set current patient and current encounter for the calendar
            $pc_eid = $queryVars['pc_eid'] ?? null;
            $appointmentService = new AppointmentService();
            // we need all of the appointment fields

            $apptResult = $appointmentService->search(['pc_eid' => $pc_eid]);
            if (!$apptResult->hasData()) {
                throw new InvalidArgumentException("Could not find appointment for pc_eid " . $pc_eid);
            } else {
                // TODO: this is very strange that getAppointment returns an array of arrays.
                $appt = $apptResult->getData()[0];
            }
            $userService = new UserService();
            $user = $userService->getUserByUsername($queryVars['authUser']);
            $userId = $user['id'];

            // if the a
            if ($this->shouldChangeProvider($appt, $userId)) {
                // we change the current appointment to the new provider in case the patient is waiting for the session to start...
                // TODO: Could there be a problem if someone tries to launch an appointment midstream of the telehealth session of another appointment?
                // Do we want to resolve this right now?
                $appt = $this->copyAppointmentAndChangeProvider($appt, $user);
            }

            $pid = $appt['pc_pid'];
            $encounter = $appointmentService->getEncounterForAppointment($appt['pc_eid'], $pid);
            $encounterService = $appointmentService->getEncounterService();
            if (empty($encounter)) {
                // create an encounter for the appointment
                $encounterId = $appointmentService->createEncounterForAppointment($appt['pc_eid']);
                $encounterResult = $encounterService->getEncounterById($encounterId);
                $encounter = $encounterResult->getData()[0];
            }

            // we need to use the current user as our session.

            // now we need to grab all of our encounters
            $encountersList = $encounterService->getPatientEncounterListWithCategories($pid);
            $patientService = new PatientService();
            $puuid = $patientService->getUuid($pid);
            $result = $patientService->getOne(UuidRegistry::uuidToString($puuid));
            $patient = $result->getData()[0];
            PatientSessionUtil::setPid($pid);
            EncounterSessionUtil::setEncounter($encounter['eid']);

            // make sure we've registered the patient, and the provider for this session before we launch
            $this->getOrCreateTelehealthProvider($user);
            $this->getOrCreateTelehealthPatient($patient);

            // get our tracking session and create it if we don't have one so we have a session between these user and the appointment
            // we have to make sure we can keep track of our session which links the appointments w/ the encounter and the video session
            $session = $this->sessionRepository->getSessionByAppointmentId($pc_eid);
            if (empty($session)) {
                $session = $this->sessionRepository->createSession($pc_eid, $userId, $encounter['eid'], $pid);
            }

            // now we will echo the json encoding of this
            $dobStr = oeFormatShortDate($patient['DOB']) . " " . xl('Age') . ": " . $patientService->getPatientAgeDisplay($patient['DOB']);
            $jsonData = [
                'selectedEncounter' => [
                    'id' => $encounter['eid']
                    , 'dateStr' => date("Y-m-d", strtotime($encounter['date']))
                ]
                , 'encounterList' => $encountersList
                , 'patient' => [
                    'fullName' => $patient['fname'] . " " . $patient['lname']
                    , 'pid' => $pid
                    , 'pubpid' => $patient['pubpid']
                    , 'dob_str' => $dobStr
                ]
                ,'user' => [
                    'username' => $user['username']
                ]
            ];
            echo text(json_encode($jsonData));
        } catch (InvalidArgumentException $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString(),
                'queryVars' => $queryVars]);
            http_response_code(400);
            echo json_encode(['error' => 'invalid argument sent.  Check server logs for details']);
        } catch (Exception $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString(),
                'queryVars' => $queryVars]);
            http_response_code(500);
            echo json_encode(['error' => 'server error occurred.  Check server logs for details']);
        }
    }

    public function setEncounterService(EncounterService $service)
    {
        $this->encounterService = $service;
    }

    public function setAppointmentService(AppointmentService $service)
    {
        $this->appointmentService = $service;
    }

    private function getPatientForPid($pid)
    {
        $patientService = new PatientService();
        $patientResult = $patientService->getAll(['pid' => $pid])->getData();
        if (empty($patientResult)) {
            throw new InvalidArgumentException("patient could not be found for pid " . $pid);
        }

        $patientResult = $patientResult[0];
        $date = \DateTime::createFromFormat("Y-m-d", $patientResult['DOB']);
        $dobYmd = $date->format("Ymd");
        $patientResult['dobFormatted'] = $dobYmd;
        $patientResult['age'] = $patientService->getPatientAgeDisplay($dobYmd);
        $addressService = new AddressService();
        $patientResult['addressFull'] = $addressService->getAddressFromRecordAsString($patientResult);
        return $patientResult;
    }


    public function renderWaitingRoom($queryVars)
    {
        if ($this->isPatient) {
            $this->initalizeAppointmentForTelehealth($queryVars['eid']);
            $data = $this->getPatientSettings($queryVars);
        } else {
            $data = $this->getProviderSettings($queryVars);
        }
        $this->logger->debug("Appointment is ", $data['appointment']);
        return $this->twig->render('comlink/waiting-room.twig', $data);
    }

    public function initalizeAppointmentForTelehealth($pc_eid)
    {
        $appointmentService = $this->appointmentService;
        $appointment = $appointmentService->getAppointment($pc_eid);
        if (empty($appointment)) {
            throw new InvalidArgumentException("appointment eid could not be found for " . $pc_eid);
        } else {
            // TODO: so wierd... why is appointment returning an array list?
            $appointment = $appointment[0];
        }
        if ($appointment['pc_apptstatus'] === '^') { // pending status
            (new SystemLogger())->errorLogCaller("Telehealth appointment was launched for pending appointment.  This should not happen.", ['pc_eid' => $pc_eid, 'appointment' => $appointment]);
            throw new InvalidArgumentException("appointment status cannot be initialized as the appointment was not confirmed by the provider" . $pc_eid);
        }

        if (!$appointmentService->isCheckInStatus($appointment['pc_apptstatus'])) {
            if ($appointmentService->isCheckOutStatus($appointment['pc_apptstatus'])) {
                // we need to log this... we shouldn't even be launching a telehealth session if this is a checkout appointment
                (new SystemLogger())->errorLogCaller("Telehealth appointment was launched for completed appointment", ['pc_eid' => $pc_eid, 'appointment' => $appointment]);
            } else {
                // need to check them in.
                // else set appointment to '@'
                $appointment['pc_apptstatus'] = "@";
                $appointmentService->updateAppointmentStatus($pc_eid, $appointment['pc_apptstatus'], self::PATIENT_PORTAL_USER);
            }
        }

        $encounter = $appointmentService->getEncounterForAppointment($pc_eid, $appointment['pid']);

        if (empty($encounter)) {
            $encounterId = $appointmentService->createEncounterForAppointment($pc_eid);
            $encounterResult = $this->encounterService->getEncounterById($encounterId);
            $encounter = $encounterResult->getData()[0];
        }

        $appointment['encounter'] = $encounter;
        $appointment['telehealthSession'] = $this->initalizeTelehealthSession($pc_eid, $appointment['pc_aid'], $encounter['eid'], $appointment['pid']);
        return $appointment;
    }

    private function initalizeTelehealthSession($pc_eid, $user_id, $encounter, $pid)
    {
        $telehealthSession = $this->sessionRepository->getSessionByAppointmentId($pc_eid);
        if (empty($telehealthSession)) {
            $telehealthSession = $this->sessionRepository->createSession($pc_eid, $user_id, $encounter, $pid);
        }

        return $telehealthSession;
    }

    public function renderConferenceRoom($queryVars)
    {
        if ($this->isPatient) {
            $data = $this->getPatientSettings($queryVars);
        } else {
            $data = $this->getProviderSettings($queryVars);
        }
//        $apptRepo = new AppointmentService();
//        $statuses = $apptRepo->getAppointmentStatuses();
        $statuses = $this->getAppointmentStatuses();

        $data['statuses'] = $statuses;
        return $this->twig->render('comlink/conference-room.twig', $data);
    }

    /**
     * TODO: when this method is in core, refactor this to use the AppointmentServices method
     * Returns a list of appointment statuses (also used with encounters).
     * @return array
     */
    private function getAppointmentStatuses()
    {
        $listService = new ListService();
        $options = $listService->getOptionsByListName('apptstat', ['activity' => 1]);
        return $options;
    }

    /**
     * TODO: when this method is in core, refactor this to use the AppointmentServices method
     * Checks to see if the passed in status is a valid appointment status for calendar appointments.
     * @param $status_option_id The status to check if its a valid appointment status
     * @return bool True if its valid, false otherwise
     */
    private function isValidAppointmentStatus($status_option_id)
    {
        $listService = new ListService();
        $option = $listService->getListOption('apptstat', $status_option_id);
        if (!empty($option)) {
            return true;
        }
        return false;
    }

    private function getProviderSettings($queryVars)
    {
        $pid = $queryVars['pid'];
        if (empty($pid)) {
            throw new InvalidArgumentException("patient pid is missing from queryVars");
        }

        $eid = $queryVars['eid'];
        if (empty($eid)) {
            throw new InvalidArgumentException("encounter eid is missing from queryVars");
        }

        $session = $this->sessionRepository->getSessionByAppointmentId($eid);
        if (empty($session)) {
            throw new InvalidArgumentException("telehealth session could not be found for encounter " . $eid);
        }
        $appt = $this->appointmentService->getAppointment($session['pc_eid']);
        $encounter = ProcessingResult::extractDataArray($this->encounterService->getEncounterById($session['encounter']));
        if (empty($encounter)) {
            throw new InvalidArgumentException("encounter could not be found for eid " . $eid);
        } else {
            $encounter = $encounter[0]; // get the first one.
        }
        if (empty($appt)) {
            throw new InvalidArgumentException("appointment could not be found for pc_eid" . $session['pc_eid']);
        }
        // TODO: we need to change core to return a single appointment rather than an array of appointments.
        $appt = $appt[0];

        // any provider can access other patients, but we still make sure they have the right ACLs
        $result = AclMain::aclCheckCore('patients', 'demo', $queryVars['authUser']);
        if ($result === false) {
            throw new AccessDeniedException('patients', 'demo', "Access denied to patient data for user " . $queryVars['authUser']);
        }

        $this->logger->debug("Appointment is ", $appt);

        $userService = new UserService();
        $user = $userService->getUserByUsername($queryVars['authUser']);

        if (empty($user)) {
            throw new RuntimeException("Could not find provider for encounter " . $queryVars['eid']
                . " this is a data integrity issue as telehealth appts should have providers");
        }

        $patientResult = $this->getPatientForPid($pid);
        $patientTelehealthSettings = $this->getOrCreateTelehealthPatient($patientResult);
        $providerTelehealthSettings = $this->getOrCreateTelehealthProvider($user);

        $data = [
            'calleeName' => $patientResult['fname'] . ' ' . $patientResult['lname']
            ,'calleeUuid' => $patientTelehealthSettings->getUsername()
            ,'apiKey' => $this->getApiKeyForPassword($providerTelehealthSettings->getAuthToken())
            ,'assetPath' => $this->assetPath
            ,'callerName' => $user['fname'] . ' ' . $user['lname']
            ,'callerUuid' => $providerTelehealthSettings->getUsername()
            ,'isPatient' => $this->isPatient
            ,'provider' => $user
            ,'patient' => $patientResult
            ,'appointment' => [
                'eid' => $session['pc_eid'],
                'apptstatus' => $appt['pc_apptstatus']
            ]
            ,'encounter' => $encounter
            ,'serviceUrl' => $GLOBALS[Bootstrap::COMLINK_VIDEO_TELEHEALTH_API]
            ,'sessionId' => $session['id']
        ];
        return $data;
    }


    private function getPatientSettings($queryVars)
    {
        $pid = $queryVars['pid'];
        if (empty($pid)) {
            throw new InvalidArgumentException("patient pid is missing from queryVars");
        }

        $apptId = $queryVars['eid'];
        if (empty($apptId)) {
            throw new InvalidArgumentException("appointment eid is missing from queryVars");
        }

        $appt = $this->appointmentService->getAppointment($apptId);
        if (!empty($appt)) {
            // TODO: get the appointment service to return a single appointment so odd it returns a list.
            $appt = $appt[0];
        }

        if (empty($appt)) {
            throw new InvalidArgumentException("appointment could not be found for pc_eid" . $apptId);
        }

        // overly paranoid but we double check the pid
        if ($appt['pid'] != $pid) {
            $this->logger->debug("Invalid access! Appointment pid did not match.  This could be "
                . "a security violation!", ["pid" => $pid, 'pc_eid' => $apptId]);
            throw new InvalidArgumentException("Invalid access!");
        }

        $userService = new UserService();
        $user = $userService->getUser($appt['pc_aid']);

        if (empty($user)) {
            throw new RuntimeException("Could not find provider for appointment " . $apptId . " this is a data integrity issue as telehealth appts should have providers");
        }
        $patientResult = $this->getPatientForPid($pid);

        $patientTelehealthSettings = $this->getOrCreateTelehealthPatient($patientResult);
        $providerTelehealthSettings = $this->getOrCreateTelehealthProvider($user);

        $data = [
            'calleeName' => $user['fname'] . ' ' . $user['lname']
            ,'calleeUuid' => $providerTelehealthSettings->getUsername()
            ,'apiKey' => $this->getApiKeyForPassword($patientTelehealthSettings->getAuthToken())
            ,'assetPath' => $this->assetPath
            ,'callerName' => $patientResult['fname'] . ' ' . $patientResult['lname']
            ,'callerUuid' => $patientTelehealthSettings->getUsername()
            ,'isPatient' => $this->isPatient
            ,'provider' => $user
            ,'patient' => $patientResult
            ,'appointment' => [
                'eid' => $appt['pc_eid'],
                'apptstatus' => $appt['pc_apptstatus']
            ]
            ,'serviceUrl' => $GLOBALS[Bootstrap::COMLINK_VIDEO_TELEHEALTH_API]
        ];
        return $data;
    }

    private function getOrCreateTelehealthProvider($user)
    {
        $providerTelehealthSettings = $this->telehealthUserRepo->getUser($user['uuid']);
        if (empty($providerTelehealthSettings)) {
            if ($this->telehealthRegistrationController->shouldCreateRegistrationForProvider($user['id'])) {
                if ($this->telehealthRegistrationController->createUserRegistration($user)) {
                    $providerTelehealthSettings = $this->telehealthUserRepo->getUser($user['uuid']);
                } else {
                    throw new RuntimeException("Could not create telehealth registration for user " . $user['uuid']);
                }
            } else {
                // we should never hit this situation as we are supposed to prevent launching of appointments on the client side of things.
                throw new TelehealthProviderNotEnrolledException("Provider is either suspended or not enrolled in telehealth. Cannot create telehealth registration for user " . $user['uuid']);
            }
        } else if (!$providerTelehealthSettings->getIsActive()) {
            // provider is disabled... can't launch settings with this provider
            throw new TeleHealthProviderSuspendedException("Provider's telehealth subscription is suspended for user " . $user['uuid']);
        }
        return $providerTelehealthSettings;
    }

    private function getOrCreateTelehealthPatient($patient)
    {
        $telehealthSettings = $this->telehealthUserRepo->getUser($patient['uuid']);
        if (empty($telehealthSettings)) {
            if ($this->telehealthRegistrationController->createPatientRegistration($patient)) {
                $telehealthSettings = $this->telehealthUserRepo->getUser($patient['uuid']);
            } else {
                throw new RuntimeException("Could not create video registration for patient " . $patient['uuid']);
            }
        }
        return $telehealthSettings;
    }

    private function getApiKeyForPassword($password)
    {
        $decrypted = $this->telehealthUserRepo->decryptPassword($password);
        $hash = hash('sha256', $decrypted);
        return $hash;
    }
}
