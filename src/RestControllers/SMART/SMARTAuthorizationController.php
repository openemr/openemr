<?php

/**
 * SMARTAuthorizationController.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\SMART;

use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\FHIR\SMART\SmartLaunchController;
use OpenEMR\Services\PatientService;
use Psr\Log\LoggerInterface;
use RuntimeException;

class SMARTAuthorizationController
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * The base URL of the oauth2 url
     * @var string
     */
    private $authBaseFullURL;

    /**
     * The oauth2 endpoint url to send to once smart authorization is complete.
     * @var string
     */
    private $smartFinalRedirectURL;

    /**
     * The directory that the oauth template files can be included from
     * @var string
     */
    private $oauthTemplateDir;

    const PATIENT_SELECT_PATH = "/smart/patient-select";

    const PATIENT_SELECT_CONFIRM_ENDPOINT = "/smart/patient-select-confirm";

    const PATIENT_SEARCH_ENDPOINT = "/smart/patient-search";

    /**
     * Maximum number of patients that can be displayed in a search result.
     */
    const PATIENT_SEARCH_MAX_RESULTS = 100;


    /**
     * SMARTAuthorizationController constructor.
     * @param $authBaseFullURL
     * @param $smartFinalRedirectURL The URL that should be redirected to once all SMART authorizations are complete.
     */
    public function __construct(LoggerInterface $logger, $authBaseFullURL, $smartFinalRedirectURL, $oauthTemplateDir)
    {
        $this->logger = $logger;
        $this->authBaseFullURL = $authBaseFullURL;
        $this->smartFinalRedirectURL = $smartFinalRedirectURL;
        $this->oauthTemplateDir = $oauthTemplateDir;
    }

    /**
     * Checks to make sure that the passed in end point points to a valid SMART oauth2 endpoint
     * @param $end_point string the route url
     * @return bool true if the route should be handled by this controller, false otherwise
     */
    public function isValidRoute($end_point)
    {
        if (false !== stripos($end_point, self::PATIENT_SELECT_PATH)) {
            return true;
        }
        if (false !== stripos($end_point, self::PATIENT_SELECT_CONFIRM_ENDPOINT)) {
            return true;
        }
        if (false !== stripos($end_point, self::PATIENT_SEARCH_ENDPOINT)) {
            return true;
        }
        return false;
    }

    /**
     * Handles the route endpoint and terminates the process upon completion.
     * @param $end_point
     */
    public function dispatchRoute($end_point)
    {

        // order here matters
        if (false !== stripos($end_point, self::PATIENT_SELECT_CONFIRM_ENDPOINT)) {
            // session is maintained
            $this->patientSelectConfirm();
            exit;
        } else if (false !== stripos($end_point, self::PATIENT_SELECT_PATH)) {
            // session is maintained
            $this->patientSelect();
            exit;
        } else if (false !== stripos($end_point, self::PATIENT_SEARCH_ENDPOINT)) {
            // session is maintained
            $this->patientSearch();
            exit;
        } else {
            $this->logger->error("SMARTAuthorizationController->dispatchRoute() called with invalid route. verify isValidRoute configured properly", ['end_point' => $end_point]);
            http_response_code(404);
        }
    }

    /**
     * Does the current request and session data require an oauth2 flow to be interrupted and go through the smart
     * endpoints.
     * @return bool
     */
    public function needSMARTAuthorization()
    {
        if (empty($_SESSION['puuid']) && strpos($_SESSION['scopes'], SmartLaunchController::CLIENT_APP_STANDALONE_LAUNCH_SCOPE) !== false) {
            $this->logger->debug("AuthorizationController->userLogin() SMART app request for patient context ", ['scopes' => $_SESSION['scopes'], 'puuid' => $_SESSION['puuid'] ?? null]);
            return true;
        }
        return false;
    }

    /**
     * Returns the first SMART oauth2 authorization path to start the SMART flow.
     * @return string
     */
    public function getSmartAuthorizationPath()
    {
        // we can extend this to be a bunch of things based on any additional authorization contexts we need
        // to support things like encounter selection, etc, but for now we only support patient selector launch
        return self::PATIENT_SELECT_PATH;
    }

    /**
     * Receives the response of the patient selected, sets up the session and redirects back to the oauth2 regular flow
     */
    public function patientSelectConfirm()
    {
        $user_uuid = $_SESSION['user_id'];
        if (!isset($user_uuid)) {
            $this->logger->error("SMARTAuthorizationController->patientSelect() Unauthorized call, user has not authenticated");
            http_response_code(401);
            die(xlt('Invalid Request'));
        }

        if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token"], 'oauth2')) {
            $this->logger->error("SMARTAuthorizationController->patientSelect() Invalid CSRF token");
            CsrfUtils::csrfNotVerified(true, true, true);
            exit();
        }

        // set our patient information up in our pid so we can handle our code property...
        try {
            $patient_id = $_POST['patient_id']; // this patient_id is actually a uuid.. wierd
            $searchController = new PatientContextSearchController(new PatientService(), $this->logger);
            // throws access denied if user doesn't have access
            $foundPatient = $searchController->getPatientForUser($patient_id, $user_uuid);
            // put PID in session
            $_SESSION['puuid'] = $patient_id;

            // now redirect to our scope-authorize
            $redirect = $this->smartFinalRedirectURL;
            header("Location: $redirect");
        } catch (AccessDeniedException $error) {
            // or should we present some kind of error display form...
            $this->logger->error("AuthorizationController->patientSelect() Exception thrown", ['exception' => $error->getMessage(), 'userId' => $user_uuid]);
            SessionUtil::oauthSessionCookieDestroy();
            $error = OAuthServerException::accessDenied("No access to patient data for this user", $this->getClientRedirectURI(), $error);
            $response = (new Psr17Factory())->createResponse();
            $this->emitResponse($error->generateHttpResponse($response));
        } catch (\Exception $error) {
            // error occurred, no patients found just display the screen with an error message
            $this->logger->error("AuthorizationController->patientSelect() Exception thrown", ['exception' => $error->getMessage()]);
            $errorMessage = "There was a server error in loading patients.  Contact your system administrator for assistance";
            $url = $this->authBaseFullURL . self::PATIENT_SELECT_PATH . "?error=" . urlencode($errorMessage);
            header("Location: " . $url);
        }
        exit;
    }

    /**
     * Displays the patient list and let's user's search and choose a patient.  If the user doesn't have access to patient
     * demographics we die on the security piece.
     * @return false|string
     */
    public function patientSelect()
    {
        $user_uuid = $_SESSION['user_id'];
        if (empty($user_uuid)) {
            $this->logger->error("SMARTAuthorizationController->patientSelect() Unauthorized call, user has not authenticated");
            http_response_code(401);
            die(xlt('Invalid Request'));
        }

        $hasMore = false;
        $patients = [];
        $oauthLogin = true;
        //  handle our patient selected piece, populate the session and now present the authorize piece
        $redirect = $this->authBaseFullURL . self::PATIENT_SELECT_CONFIRM_ENDPOINT;
        $searchAction = $this->authBaseFullURL . self::PATIENT_SELECT_PATH;
        $errorMessage = $_GET['error'] ?? '';

        try {
            // we've got a user by their UUID... we need to grab the db user id
            $searchParams = $_GET['search'] ?? [];

            // grab our list of patients to select from.
            $searchController = new PatientContextSearchController(new PatientService(), $this->logger);
            $patients = $searchController->searchPatients($searchParams, $user_uuid);
            $hasMore = count($patients) > self::PATIENT_SEARCH_MAX_RESULTS;
            $patients = $hasMore ? array_slice($patients, 0, self::PATIENT_SEARCH_MAX_RESULTS) : $patients;

            require_once($this->oauthTemplateDir . "smart/patient-select.php");
        } catch (AccessDeniedException $error) {
            $this->logger->error("AuthorizationController->patientSelect() Exception thrown", ['exception' => $error->getMessage(), 'userId' => $user_uuid]);
            SessionUtil::oauthSessionCookieDestroy();

            $error = OAuthServerException::accessDenied("No access to patient data for this user", $this->getClientRedirectURI(), $error);
            $response = (new Psr17Factory())->createResponse();
            $this->emitResponse($error->generateHttpResponse($response));
        } catch (\Exception $error) {
            // error occurred, no patients found just display the screen with an error message
            $error_message = "There was a server error in loading patients.  Contact your system administrator for assistance";
            $this->logger->error("AuthorizationController->patientSelect() Exception thrown", ['exception' => $error->getMessage()]);
            require_once($this->oauthTemplateDir . "smart/patient-select.php");
        }
    }

    // TODO: adunsulag should this be moved into a trait so we can share the functionality with AuthorizationController?
    public function emitResponse($response): void
    {
        if (headers_sent()) {
            throw new RuntimeException('Headers already sent.');
        }
        $statusLine = sprintf(
            'HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );
        header($statusLine, true);
        foreach ($response->getHeaders() as $name => $values) {
            $responseHeader = sprintf('%s: %s', $name, $response->getHeaderLine($name));
            header($responseHeader, false);
        }
        // send it along.
        echo $response->getBody();
    }

    /**
     * Returns the client redirect URI to send error responses back.
     * @return string|null
     */
    private function getClientRedirectURI()
    {
        $client_id = $_SESSION['client_id'];
        $repo = new ClientRepository();
        $client = $repo->getClientEntity($client_id);
        $uri = $client->getRedirectUri();
        return $uri;
    }
}
