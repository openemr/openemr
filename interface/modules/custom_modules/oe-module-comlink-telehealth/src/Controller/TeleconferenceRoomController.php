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
use Comlink\OpenEMR\Modules\TeleHealthModule\Controller\TeleHealthFrontendSettingsController;
use Comlink\OpenEMR\Modules\TeleHealthModule\Exception\TelehealthProviderNotEnrolledException;
use Comlink\OpenEMR\Modules\TeleHealthModule\Exception\TeleHealthProviderSuspendedException;
use Comlink\OpenEMR\Modules\TeleHealthModule\Exception\TelehealthProvisioningServiceRequestException;
use Comlink\OpenEMR\Modules\TeleHealthModule\Exception\TelehealthValidationException;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthSessionRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthUserRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\Controller\TeleHealthVideoRegistrationController;
use Comlink\OpenEMR\Modules\TeleHealthModule\Services\FormattedPatientService;
use Comlink\OpenEMR\Modules\TeleHealthModule\Services\ParticipantListService;
use Comlink\OpenEMR\Modules\TeleHealthModule\Services\TelehealthConfigurationVerifier;
use Comlink\OpenEMR\Modules\TeleHealthModule\Services\TeleHealthParticipantInvitationMailerService;
use Comlink\OpenEMR\Modules\TeleHealthModule\Services\TeleHealthProvisioningService;
use Comlink\OpenEMR\Modules\TeleHealthModule\TelehealthGlobalConfig;
use Comlink\OpenEMR\Modules\TeleHealthModule\The;
use Comlink\OpenEMR\Modules\TeleHealthModule\Util\TelehealthAuthUtils;
use Comlink\OpenEMR\Modules\TeleHealthModule\Util\CalendarUtils;
use Comlink\OpenEMR\Modules\TeleHealthModule\Validators\TelehealthPatientValidator;
use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\OneTimeAuth;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\EncounterSessionUtil;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\AddressService;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\ListService;
use OpenEMR\Services\PatientAccessOnsiteService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\PatientValidator;
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

    const LAUNCH_PATIENT_SESSION = 'launch_patient_session';

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

    /**
     * @var TeleHealthParticipantInvitationMailerService
     */
    private $mailerService;

    /**
     * @var TeleHealthFrontendSettingsController
     */
    private $settingsController;

    /**
     * @var TelehealthGlobalConfig
     */
    private $config;

    /**
     * @var TeleHealthProvisioningService
     */
    private $provisioningService;

    /**
     * @var ParticipantListService
     */
    private $participantListService;

    public function __construct(Environment $twig, LoggerInterface $logger, TeleHealthVideoRegistrationController $registrationController, TeleHealthParticipantInvitationMailerService $mailerService, TeleHealthFrontendSettingsController $settingsController, TelehealthGlobalConfig $config, TeleHealthProvisioningService $provisioningService, ParticipantListService $participantListService, $assetPath, $isPatient = false)
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
        $this->mailerService = $mailerService;
        $this->settingsController = $settingsController;
        $this->config = $config;
        $this->provisioningService = $provisioningService;
        $this->participantListService = $participantListService;
    }

    public function dispatch($action, $queryVars)
    {
        $this->logger->debug("TeleconferenceRoomController->dispatch()", ['action' => $action, 'queryVars' => $queryVars, 'isPatient' => $this->isPatient]);

        // TODO: @adunsulag need to look at each individual action and make sure we are following access permissions here...
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
        } else if ($action == 'verify_installation_settings') {
            return $this->verifyInstallationSettings($queryVars);
        } else if ($action == 'save_session_participant') {
            return $this->saveSessionParticipantAction($queryVars);
        } else if ($action == 'get_participant_list') {
            return $this->getParticipantListAction($queryVars);
        } else if ($action == self::LAUNCH_PATIENT_SESSION) {
            return $this->launchPatientSessionAction($queryVars);
        } else if ($action == 'generate_participant_link') {
            return $this->generateParticipantLinkAction($queryVars);
        } else if ($action == 'patient_validate_telehealth_ready') {
            return $this->validatePatientIsTelehealthReadyAction($queryVars);
        } else {
            $this->logger->error(self::class . '->dispatch() invalid action found', ['action' => $action]);
            echo "action not supported";
            return;
        }
    }

    public function getParticipantListAction($queryVars)
    {
        try {
            $pc_eid = $queryVars['pc_eid'] ?? null;
            if (empty($pc_eid)) {
                throw new InvalidArgumentException("pc_eid was missing from request");
            }
            $session = $this->sessionRepository->getSessionByAppointmentId($pc_eid);
            if (empty($session)) {
                throw new InvalidArgumentException("session was not found for pc_eid of " + $pc_eid);
            }

            $verifiedUser = null;
            // need to check for access denied exception on user and patient
            if (!empty($queryVars['authUser'])) {
                // throws exception if the user is not found
                $verifiedUser = $this->verifyUsernameCanAccessSession($queryVars['authUser'], $session);
            } else {
                // make sure the patients can access the session
                $this->verifyPidCanAccessSession($queryVars['pid'], $session);
                $userService = new UserService();
                $verifiedUser = $userService->getUser($session['user_id']);
            }

            $participantList = $this->participantListService->getParticipantListForAppointment($verifiedUser, $session);
            echo json_encode([
                'participantList' => textArray($participantList)
            ]);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            http_response_code(400);
            echo $this->twig->render('error/400.html.twig');
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            http_response_code(500);
            echo $this->twig->render('error/general_http_error.html.twig', ['statusCode' => 500]);
        }
    }

    private function verifyUsernameCanAccessSession($userName, $session)
    {
        // grab user id and make sure we can access this
        $userService = new UserService();
        $user = $userService->getUserByUsername($userName);
        if (empty($user) || $user['id'] != $session['user_id']) {
            throw new AccessDeniedException('patient', 'demo', 'Access not allowed to this telehealth session for user' . $userName);
        }
        return $user;
    }

    private function verifyPidCanAccessSession($pid, $session)
    {
        $primaryPid = intval($session['pid'] ?? 0);
        $pidRelated = intval($session['pid_related'] ?? 0);
        // note that this assumes that $queryVars is coming from the session
        $queryVarPid = intval($pid); // should always be populated
        if ($pidRelated !== $queryVarPid && $primaryPid !== $queryVarPid) {
            throw new AccessDeniedException('patient', 'demo', 'Access not allowed to this telehealth session for pid' . $queryVarPid);
        }
    }

    public function launchPatientSessionAction($queryVars)
    {
        try {
            $pc_eid = $queryVars['pc_eid'] ?? null;
            if (empty($pc_eid)) {
                throw new InvalidArgumentException("pc_eid was missing from request");
            }
            $session = $this->sessionRepository->getSessionByAppointmentId($pc_eid);
            if (empty($session)) {
                throw new InvalidArgumentException("session was not found for pc_eid of " + $pc_eid);
            }
            $primaryPid = intval($session['pid'] ?? 0);
            $pidRelated = intval($session['pid_related'] ?? 0);
            // note that this assumes that $queryVars is coming from the session
            $queryVarPid = intval($queryVars['pid']); // should always be populated
            if ($pidRelated !== $queryVarPid && $primaryPid !== $queryVarPid) {
                throw new AccessDeniedException('patient', 'demo', 'Access not allowed to this telehealth session for pid' . $queryVarPid);
            }

            $activeSession = null;
            $apptService = new AppointmentService();
            $appt = $apptService->getAppointment($session['pc_eid']);
            if (empty($appt)) {
                throw new InvalidArgumentException("appointment was not found for pc_eid of " + $pc_eid);
            } else {
                $appt = reset($appt); // annoying that its inside an array
            }
            $dateTime = \DateTime::createFromFormat("Y-m-d H:i:s", $appt['pc_eventDate']
                . " " . $appt['pc_startTime']);
            if (
                $dateTime !== false
                && CalendarUtils::isAppointmentDateTimeInSafeRange($dateTime)
                && !$apptService->isCheckOutStatus($appt['pc_apptstatus'])
            ) {
                $activeSession = ['pc_eid' => $queryVars['pc_eid']];
            }

            $data = [
                'activeSession' => $activeSession
                , 'assetPath' => $this->assetPath
                , 'images_static_relative' => $this->config->getImagesStaticRelative()
                , 'portalUrl' => $this->config->getPortalOnsiteAddress() . '/home.php'
                , 'portal_timeout' => $this->config->getPortalTimeout()
                , 'debug' => $this->config->isDebugModeEnabled()
            ];

            echo $this->twig->render('comlink/portal/thirdparty.html.twig', $data);
        } catch (AccessDeniedException $exception) {
            // we treat it as a not found session as we don't want to reveal that the session exists or does not exist
            $this->logger->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            $data = [
                'activeSession' => null
                , 'assetPath' => $this->assetPath
                , 'images_static_relative' => $this->config->getImagesStaticRelative()
                , 'portalUrl' => $this->config->getPortalOnsiteAddress() . '/home.php'
                , 'portal_timeout' => $this->config->getPortalTimeout()
                , 'debug' => false
            ];

            echo $this->twig->render('comlink/portal/thirdparty.html.twig', $data);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            http_response_code(400);
            echo $this->twig->render('error/400.html.twig');
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            http_response_code(500);
            echo $this->twig->render('error/general_http_error.html.twig', ['statusCode' => 500]);
        }
    }

    public function validatePatientIsTelehealthReadyAction($queryVars)
    {

        // grab the patient pid from the query vars
        // verify the current user can access patient demographics via the acl
        // verify the patient has the portal setup and a valid email
        try {
            $csrfToken = $queryVars['csrf_token'] ?? null;
            if (empty($csrfToken) || !CsrfUtils::verifyCsrfToken($csrfToken, 'api')) {
                throw new InvalidArgumentException("csrf_token was missing or invalid in request");
            }

            $validatePid = $queryVars['validatePid'] ?? null;
            if (empty($validatePid)) {
                throw new InvalidArgumentException("validatePid was missing from request");
            } else {
                $validatePid = intval($validatePid);
            }
            // note we are NOT intentionally using $queryVars['pid'] here as we want to validate the pid that is being passed in
            // the appointment creator can choose a different patient than the one that is currently selected in the pid
            // we still need to make sure they have an ACL check.
            // note this will stop portal access for patients as we don't want them to have access to this api.
            if (!AclMain::aclCheckCore('patients', 'appt')) {
                throw new AccessDeniedException("patients", "appt", "Does not have ACL permission to patient appointments");
            }
            // feels odd to use the OneTimeAuth for verifying if the patient is a valid portal user...
            // TODO: @adunsulag look at moving this isValidPortalPatient function the Patient service or perhaps a PatientPortal service.
            $oneTimeAuth = new OneTimeAuth();
            $patient = $oneTimeAuth->isValidPortalPatient($validatePid) ?? ['valid' => false];
            if (!empty($patient['valid']) && $patient['valid'] == true) {
                http_response_code(200);
                header("Content-type: application/json");
                echo json_encode(['success' => true]);
            } else {
                http_response_code(400);
                header("Content-type: application/json");
                echo json_encode(['success' => false, 'error' => xlt("Patient is not a valid portal user")]);
            }
        } catch (AccessDeniedException $exception) {
            $this->logger->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            http_response_code(401);
            header("Content-type: application/json");
            echo json_encode(['success' => false, 'error' => xlt("Access Denied")]);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            http_response_code(400);
            header("Content-type: application/json");
            echo json_encode(['error' => xlt("Improperly formatted request")]);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => xlt("Server error occurred, Check logs.")]);
        }
    }

    public function generateParticipantLinkAction($queryVars)
    {
        try {
            $json = file_get_contents("php://input");
            $data = json_decode($json, true);
            $pc_eid = $data['pc_eid'] ?? null;
            if (empty($pc_eid)) {
                throw new InvalidArgumentException("pc_eid was missing from request");
            } else {
                $pc_eid = intval($pc_eid);
            }
            $session = $this->sessionRepository->getSessionByAppointmentId($pc_eid);
            if (empty($session)) {
                throw new InvalidArgumentException("session was not found for pc_eid of " + $pc_eid);
            }
            // check to make sure the session user is the same as the logged in user
            $verifiedUser = null;
            // need to check for access denied exception on user and patient
            if (!empty($queryVars['authUser'])) {
                // throws exception if the user is not found
                $verifiedUser = $this->verifyUsernameCanAccessSession($queryVars['authUser'], $session);
            }
            // provider can't grab the invitation if they don't have access to the patient demographics
            if (empty($verifiedUser) || !AclMain::aclCheckCore('patients', 'demo')) {
                throw new AccessDeniedException("patients", "demo", "Does not have ACL permission to patient demographics");
            }
            $pid = $data['pid'];
            if (empty($pid)) {
                throw new InvalidArgumentException("pid was missing from request");
            } else {
                $pid = intval($pid);
            }
            if ($pid !== intval($session['pid']) && $pid !== intval($session['pid_related'])) {
                throw new InvalidArgumentException("pid does not match the session pid");
            }

            $patientService = new PatientService();
            $patient = $patientService->findByPid($pid);
            if (empty($patient)) {
                throw new InvalidArgumentException("patient was not found for pid of " + $session['pid']);
            }

            $invitation = $this->mailerService->getMailerInvitationForManualSend(
                $patient,
                $session,
                self::LAUNCH_PATIENT_SESSION
            );
            // TODO: @adunsulag we really should return Response objects so we can unit test all of this...
            $invitation['generated'] = true; // make sure we mark that this invitation was generated
            echo json_encode(['success' => true, 'invitation' => $invitation]);
        } catch (AccessDeniedException $exception) {
            $this->logger->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            http_response_code(401);
            header("Content-type: application/json");
            echo json_encode(['success' => false, 'error' => xlt("Access Denied")]);
        } catch (InvalidArgumentException $exception) {
            $this->logger->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            http_response_code(400);
            header("Content-type: application/json");
            echo json_encode(['error' => xlt("Improperly formatted request")]);
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => xlt("Server error occurred, Check logs.")]);
        }
    }

    // TODO: @adunsulag we need to break this up into another class, however there's a lot of tight coupling here
    // that will require some refactoring.
    public function saveSessionParticipantAction($queryVars)
    {
        // let's grab the json data if we have it in the post
        try {
            if (!$this->config->isThirdPartyInvitationsEnabled()) {
                throw new InvalidArgumentException("Third party invitations are not enabled and this function should not have been called");
            }

            $json = file_get_contents("php://input");
            $data = json_decode($json, true);

            $pid = intval($data['pid'] ?? 0);
            $pc_eid = intval($data['eid'] ?? 0);

            // provider can't add additional patients if they don't have access to the patient demographics
            if (!AclMain::aclCheckCore('patients', 'demo')) {
                throw new AccessDeniedException("patients", "demo", "Does not have ACL permission to patient demographics");
            }

            // need to create the patient if we don't have a pid
            $isNewPatient = false;
            if (empty($pid)) {
                $pid = $this->createPatientFromSessionInvitationData($data);
                $isNewPatient = true;
            }

            $session = $this->sessionRepository->addRelatedPartyToSession($pc_eid, $pid);
            // send out notification
            if (!empty($session)) {
                $this->sendSessionInvitationToRelatedParty($session, $pid, $isNewPatient);
                $settings = $this->getProviderSettings(['pid' => $pid, 'eid' => $pc_eid, 'authUser' => $queryVars['authUser']]);
                header("Content-type: application/json");
                http_response_code(200);
                // TODO: question for @bradymiller trying to figure out how to avoid double escaping.
                //  I have a link embedded in the caller settings that gets corrupted if I use textArray
                //  I'm still trying to figure out why I need to escape this JS value when I can use the escaping
                //  features on the client side on the handful of places where I interact with the DOM.
                echo json_encode(['callerSettings' => $settings]);
            } else {
                throw new InvalidArgumentException("Failed to find session for pc_eid " . $pc_eid);
            }
        } catch (TelehealthValidationException $exception) {
            http_response_code(400);
            header("Content-type: application/json");
            echo json_encode(['error' => xlt($exception->getMessage()), 'fields' => $exception->getValidationErrors()]);
            $this->logger->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
        } catch (\InvalidArgumentException $exception) {
            http_response_code(400);
            header("Content-type: application/json");
            echo json_encode(['error' => xlt($exception->getMessage())]);
            $this->logger->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
        } catch (Exception $exception) {
            http_response_code(500);
            $this->logger->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
        }
    }

    private function createPatientFromSessionInvitationData($data)
    {
        $patientService = new PatientService();
        $listService = new ListService();
        // wierd that it goes off the option title instead of the option id here
        $sexOption = $listService->getListOption('sex', 'UNK');
        $yesOption = $listService->getListOption('yesno', 'YES');
        // the validator will scream if we are sending the wrong data
        $insertData = [
            'email' => $data['email'] ?? null
            ,'fname' => $data['fname'] ?? null
            ,'lname' => $data['lname'] ?? null
            ,'DOB' => $data['DOB'] ?? null
            ,'sex' => $sexOption['title'] // we set it to unknown.  Patient can fill it in later, we do this to simplify the invitation
            // since we are explicitly sending them an invitation with their email, the provider has gotten verbal confirmation
            // that the patient wants to receive a message via email.
            ,'hipaa_allowemail' => $yesOption['title']
            ,'allow_patient_portal' => $yesOption['title']
        ];
        // do our more strict validation then pass on to the Patient Service Insert
        $patientValidator = new TelehealthPatientValidator();
        $result = $patientValidator->validate($insertData, TelehealthPatientValidator::TELEHEALTH_INSERT_CONTEXT);
        if (!$result->hasErrors()) {
            $result = $patientService->insert($insertData);
        }
        if ($result->hasErrors()) {
            // need to throw the exception
            $message = "";
            foreach ($result->getValidationMessages() as $key => $value) {
                $message .= "Validation failed for key $key with messages " . implode(";", $value) . ".";
            }
            throw new TelehealthValidationException($result->getValidationMessages(), $message);
        } else if ($result->hasInternalErrors()) {
            throw new \RuntimeException(implode(". ", $result->getInternalErrors()));
        }
        $patientResult = $result->getData() ?? [];
        if (empty($patientResult)) {
            throw new \RuntimeException("Patient was not created");
        }

        $pid = $patientResult[0]['pid'];
        $patientData = $patientService->findByPid($pid);

        // now we need to create the portal credentials here
        $patientAccessService = new PatientAccessOnsiteService();
        $pwd = $patientAccessService->getRandomPortalPassword();
        $uname = $patientData['fname'] . $patientData['id'];
        $login_uname = $patientAccessService->getUniqueTrustedUsernameForPid($pid);
        $login_uname = $login_uname ?? $uname;
        $result = $patientAccessService->saveCredentials($pid, $pwd, $uname, $login_uname);

        // TODO: @adunsulag we need to handle if the email credentials don't send, or if we want to bundle all of this
        // into a single email
        $patientAccessService->sendCredentialsEmail($pid, $pwd, $uname, $login_uname, $data['email']);

        return $pid;
    }

    private function sendSessionInvitationToRelatedParty($session, $pid, $isNewPatient = false)
    {
        // need to send out the invitation to the patient
        // for new patients we need to send out a different invitation versus an existing patient
        $patientService = new PatientService();
        $patient = $patientService->findByPid($pid);
        if (!$isNewPatient) {
            $this->mailerService->sendInvitationToExistingPatient($patient, $session, self::LAUNCH_PATIENT_SESSION);
        } else {
            $this->mailerService->sendInvitationToNewPatient($patient, $session, self::LAUNCH_PATIENT_SESSION);
            $this->logger->debug("Sending session invitation to new patient", ['session' => $session]);
        }
    }

    public function verifyInstallationSettings($queryVars)
    {
        $configVerifier = new TelehealthConfigurationVerifier(
            $this->logger,
            $this->provisioningService,
            $this->telehealthUserRepo,
            $this->config
        );
        $userService = new UserService();
        $user = $userService->getUserByUsername($queryVars['authUser']);
        if (empty($user)) {
            echo json_encode(['status' => 'error', 'message' => xlt("Could not find authenticated user")]);
            return;
        }

        $configVerifier->verifyInstallationSettings($user);
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
                // TODO: question for @bradymiller trying to figure out how to avoid double escaping.
                //  I have a link embedded in the caller settings that gets corrupted if I use textArray
                //  I'm still trying to figure out why I need to escape this JS value when I can use the escaping
                //  features on the client side on the handful of places where I interact with the DOM.
                , 'callerSettings' => $settings
                // conference room has already been escaped via twig rendering
                , 'conferenceRoom' => $conferenceRoom
            ];
            echo json_encode($result);
        } catch (TelehealthProvisioningServiceRequestException $exception) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Telehealth Provisioning Failed', 'code' => $exception->getCode()]);
        } catch (\Exception $exception) {
            $this->logger->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString(),
                'queryVars' => $queryVars]);
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'server error occurred.  Check server logs for details']);
        }
    }

    public function getTeleHealthFrontendSettingsAction($queryVars)
    {
        echo $this->settingsController->renderFrontendSettings($this->isPatient);
    }

    public function conferenceSessionUpdateAction($queryVars)
    {
        $pc_eid = $queryVars['pc_eid'];
        $session = $this->sessionRepository->getSessionByAppointmentId($pc_eid);
        try {
            if (empty($session)) {
                // throw error
                throw new \InvalidArgumentException("Could not find session for appointment id");
            }
            $isProvider = !$this->isPatient;

            // now we need to grab the third party...
            $pid = intval($queryVars['pid'] ?? 0);
            $role = 'provider';

            if ($this->isPatient) {
                if (!empty($session['pid'] && intval($session['pid']) == $pid)) {
                    $role = 'patient';
                } else if (!empty($session['pid_related']) && intval($session['pid_related']) == $pid) {
                    $role =  'patient_related';
                }
            }
            if (empty($session[$role . '_start_time'])) {
                $this->logger->debug("Updating startTimestamp", ['pc_eid' => $pc_eid, 'role' => $role]);
                $this->sessionRepository->updateStartTimestamp($pc_eid, $role);
            }
            $this->logger->debug("Updating lastSeenTimestamp", ['pc_eid' => $pc_eid, 'role' => $role]);
            $this->sessionRepository->updateLastSeenTimestamp($pc_eid, $role);
            // send down the participant list here

            // we need this operation to be fast.  Since we already have the session we grab the participant list
            // JUST from what is in the session without grabbing usernames, and other data.
            // if we need to update the participant list, the services polling can compare their local participant list
            // with what is in the update and then hit the server to get an updated calling list
            $participants = $this->participantListService->getSparseParticipantListFromSession($session);
            $escapedParticipants = textArray($participants);
            echo json_encode(['status' => 'success', 'participantList' => $escapedParticipants]);
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
                ,'participantList' => []
            ]
        ];

        $this->logger->debug("patientAppointmentReadyAction()", ['session' => $session]);
        try {
            if (!empty($session)) {
                $result['session']['id'] = $session['id'];
                // provider has started the session, let's verify the last update time
                if (!empty($session['provider_last_update'])) {
                    if (CalendarUtils::isTelehealthSessionInActiveTimeRange($session)) {
                        $result['session']['providerReady'] = true;
                    }
                    $userRepo = new UserService();
                    $user = $userRepo->getUser($session['user_id']);
                    if (empty($user)) {
                        throw new InvalidArgumentException("Failed to get user with session id " . $session['id']);
                    }

                    $participantList = $this->getParticipantListForAppointment($user, $session);
                    if (!empty($queryVars['authUser'])) {
                        // do an ACL check
                        if (!AclMain::aclCheckCore("patients", "demo")) {
                            throw new AccessDeniedException('patients', 'demo', "Invalid access to patient demographics information");
                        }
                    } else {
                        // grab our patient for our pid and find the uuid
                        $patientService = new PatientService();
                        // the pid should always come from the session
                        $uuidBinary = $patientService->getUuid($queryVars['pid']);
                        if (empty($uuidBinary)) {
                            throw new InvalidArgumentException("Invalid session pid");
                        }
                        $uuid = UuidRegistry::uuidToString($uuidBinary);
                        $foundParticipant = false;
                        foreach ($participantList as $participant) {
                            if ($participant['role'] == 'patient' && $participant['uuid'] == $uuid) {
                                $foundParticipant = true;
                                break;
                            }
                        }
                        // none of the patients are on the authorized participant list so we are going to deny this request
                        if (!$foundParticipant) {
                            throw new AccessDeniedException('patients', 'demo', "Invalid access to patient demographics information");
                        }
                    }

                    $result['session']['participantList'] = $participantList;

//                    // in the event that we never change the login credentials we grab it from the session here...
//                    $telehealthCredentials = $this->telehealthUserRepo->getUser($user['uuid']);
//                    if (empty($telehealthCredentials)) {
//                        throw new InvalidArgumentException("Failed to get telehealth credentials with username " . $user['username']);
//                    }
//                    $result['session']['calleeUuid'] = $telehealthCredentials->getUsername();
                }
            }
            echo json_encode(textArray($result));
        } catch (AccessDeniedException $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString(),
                'queryVars' => $queryVars]);
            http_response_code(401);
            echo json_encode(['error' => 'Access Denied']);
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
            if (!AclMain::aclCheckCore('patients', 'appt', '', array('write','wsome'))) {
                throw new AccessDeniedException("patients", "apt", "User does not have access to update current appointment information");
            }
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

            if ($this->isPendingAppointment($appt)) {
                // the fact the provider is launching a pending appointment by its nature confirms the appointment
                $appt = $this->removeAppointmentPendingStatus($appt, $userId);
            }


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
            } else if ($session['pid'] != $pid) {
                // this should be REALLY rare, the provider launched a session which created the session record to a patient
                // they then closed the session, and changed the patient assigned to the current calendar patient
                // it should really almost never happen, but I've triggered it once before so what we will do is update the
                // session pid
                $this->sessionRepository->updatePatientFromAppointment($session, $appt);
            }

            // send off the notification to the patient that we are launching the session
            $this->mailerService->sendInvitationToExistingPatient(
                $patient,
                $session,
                TeleconferenceRoomController::LAUNCH_PATIENT_SESSION
            );

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
        $formattedPatientService = new FormattedPatientService();
        return $formattedPatientService->getPatientForPid($pid);
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

    public function isPendingAppointment($appointment)
    {
        return $this->appointmentService->isPendingStatus($appointment['pc_apptstatus']);
    }

    public function removeAppointmentPendingStatus($appointment, $userId)
    {
        $listService = new ListService();
        $options = $listService->getOptionsByListName('apptstat');
        $noStatus = '-';
        $statusSetting = ""; // if we can't fid the no status option because an installation removed it, we set status to nothing

        if (!empty($options)) {
            foreach ($options as $option) {
                if ($option['option_id'] == $noStatus) {
                    $statusSetting = $noStatus;
                    break;
                }
            }
        }

        $this->appointmentService->updateAppointmentStatus($appointment['pc_eid'], $statusSetting, $userId);
        $appointment['status'] = $statusSetting;
        return $appointment;
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
        if ($this->isPendingAppointment($appointment)) { // pending status
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
        $data['isOneTimePasswordLoginEnabled'] = $this->config->isOneTimePasswordLoginEnabled();
        return $this->twig->render('comlink/conference-room.twig', $data);
    }

    /**
     * Returns a list of appointment statuses (also used with encounters).
     * @return array
     */
    private function getAppointmentStatuses()
    {
        return $this->appointmentService->getAppointmentStatuses();
    }

    /**
     * Checks to see if the passed in status is a valid appointment status for calendar appointments.
     * @param $status_option_id The status to check if its a valid appointment status
     * @return bool True if its valid, false otherwise
     */
    private function isValidAppointmentStatus($status_option_id)
    {
        return $this->appointmentService->isValidAppointmentStatus($status_option_id);
    }

    /**
     * @param $queryVars
     * @return array
     * @throws AccessDeniedException
     * @throws TelehealthProvisioningServiceRequestException
     */
    private function getProviderSettings($queryVars)
    {
        $pid = $queryVars['pid'];
        if (empty($pid)) {
            throw new InvalidArgumentException("patient pid is missing from queryVars");
        }

        // TODO: @adunsulag we should probably rename this to be pc_eid since eid can be confused with the encounter id
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
        $thirdPartyPatient = null;
        if (!empty($session['pid_related'])) {
            $thirdPartyPatient = $this->getPatientForPid($session['pid_related']);
        }

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
                'apptstatus' => $appt['pc_apptstatus'],
                'notes' => $appt['pc_hometext']
            ]
            ,'participantList' => $this->participantListService->getParticipantListWithInvitationsForAppointment($user, $session)
            ,'encounter' => $encounter
            ,'serviceUrl' => $GLOBALS[Bootstrap::COMLINK_VIDEO_TELEHEALTH_API]
            ,'sessionId' => $session['id']
            ,'thirdPartyPatient' => $thirdPartyPatient
        ];
        return $data;
    }


    /**
     * @param $queryVars
     * @return array
     * @throws TelehealthProvisioningServiceRequestException
     */
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

        $session = $this->sessionRepository->getSessionByAppointmentId($apptId);
        if (empty($session)) {
            throw new InvalidArgumentException("telehealth session could not be found for encounter " . $apptId);
        }

        $userService = new UserService();
        $user = $userService->getUser($appt['pc_aid']);

        if (empty($user)) {
            throw new RuntimeException("Could not find provider for appointment " . $apptId . " this is a data integrity issue as telehealth appts should have providers");
        }

        $isPatientDenied = true;
        // grab our list of participants and make sure this patient pid is allowed to join the session
        if (!$this->isPatientPidAuthorizedForSession($pid, $session)) {
        // overly paranoid but we double check the pid
            $this->logger->debug("Invalid access! Pid was not in appointment participant list. This could be "
                . "a security violation!", ["pid" => $pid, 'pc_eid' => $apptId]);
            throw new InvalidArgumentException("Invalid access!");
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
            ,'participantList' => $this->getParticipantListForAppointment($user, $session)
            ,'serviceUrl' => $GLOBALS[Bootstrap::COMLINK_VIDEO_TELEHEALTH_API]
        ];
        return $data;
    }

    private function isPatientPidAuthorizedForSession($pid, $session)
    {
        $convertedPid = intval($pid);
        $related_session_pid = intval($session['pid_related'] ?? 0);
        //$related_session_pid = $session['pid_related'] ?? null;
        $sessionPid = intval($session['pid'] ?? 0);
        if (0 !== $convertedPid && ($convertedPid === $sessionPid || $convertedPid === $related_session_pid)) {
            return true;
        }
        return false;
    }

    private function getParticipantListForAppointment($user, $session)
    {
        return $this->participantListService->getParticipantListForAppointment($user, $session);
    }

    /**
     * @param $user - a user as returned from UserService
     * @return \Comlink\OpenEMR\Modules\TeleHealthModule\Models\TeleHealthUser|null
     * @throws TelehealthProvisioningServiceRequestException
     */
    private function getOrCreateTelehealthProvider($user)
    {
        return $this->provisioningService->getOrCreateTelehealthProvider($user);
    }

    /**
     * @param $patient
     * @return \Comlink\OpenEMR\Modules\TeleHealthModule\Models\TeleHealthUser|null
     * @throws TelehealthProvisioningServiceRequestException
     */
    private function getOrCreateTelehealthPatient($patient)
    {
        return $this->provisioningService->getOrCreateTelehealthPatient($patient);
    }

    private function getApiKeyForPassword($password)
    {
        $decrypted = $this->telehealthUserRepo->decryptPassword($password);
        return TelehealthAuthUtils::getFormattedPassword($decrypted);
    }
}
