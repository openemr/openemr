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
use League\OAuth2\Server\RedirectUriValidators\RedirectUriValidator;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Events\Core\TemplatePageEvent;
use OpenEMR\FHIR\SMART\SmartLaunchController;
use OpenEMR\Services\LogoService;
use OpenEMR\Services\PatientService;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig\Environment;

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

    /**
     * @var Environment The twig template engine
     */
    private $twig;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    const PATIENT_SELECT_PATH = "/smart/patient-select";

    const PATIENT_SELECT_CONFIRM_ENDPOINT = "/smart/patient-select-confirm";

    const PATIENT_SEARCH_ENDPOINT = "/smart/patient-search";

    /**
     * Maximum number of patients that can be displayed in a search result.
     */
    const PATIENT_SEARCH_MAX_RESULTS = 100;

    const EHR_SMART_LAUNCH_AUTOSUBMIT = "/smart/ehr-launch-autosubmit";

    const SMART_STYLE_URL = "/smart/smart-style";


    /**
     * SMARTAuthorizationController constructor.
     * @param $authBaseFullURL
     * @param $smartFinalRedirectURL The URL that should be redirected to once all SMART authorizations are complete.
     */
    public function __construct(LoggerInterface $logger, $authBaseFullURL, $smartFinalRedirectURL, $oauthTemplateDir, Environment $twig, EventDispatcher $dispatcher)
    {
        $this->logger = $logger;
        $this->authBaseFullURL = $authBaseFullURL;
        $this->smartFinalRedirectURL = $smartFinalRedirectURL;
        $this->oauthTemplateDir = $oauthTemplateDir;
        $this->twig = $twig;
        $this->dispatcher = $dispatcher;
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
        if (false !== stripos($end_point, self::EHR_SMART_LAUNCH_AUTOSUBMIT)) {
            return true;
        }
        if (false !== stripos($end_point, self::SMART_STYLE_URL)) {
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
        } else if (false !== stripos($end_point, self::EHR_SMART_LAUNCH_AUTOSUBMIT)) {
            $this->ehrLaunchAutoSubmit();
            exit;
        } else if (false !== stripos($end_point, self::SMART_STYLE_URL)) {
            $this->smartAppStyles();
        } else {
            $this->logger->error("SMARTAuthorizationController->dispatchRoute() called with invalid route. verify isValidRoute configured properly", ['end_point' => $end_point]);
            http_response_code(404);
        }
    }

    public function ehrLaunchAutoSubmit()
    {
        // grab the server query string and let's go back to our authorize endpoint
        $endpoint = $this->authBaseFullURL . "/authorize?autosubmit=1&" . http_build_query($_GET);
        $data = [
            'endpoint' => $endpoint
        ];
        $this->renderTwigPage('oauth2/authorize/ehr-launch-auto-submit', "oauth2/ehr-launch-autosubmit.html.twig", $data);
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
            // make sure to grab the redirect uri before the session is destroyed
            $redirectUri = $this->getClientRedirectURI();
            SessionUtil::oauthSessionCookieDestroy();
            $error = OAuthServerException::accessDenied("No access to patient data for this user", $redirectUri, $error);
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

            $this->renderTwigPage(
                'oauth2/authorize/patient-select',
                "oauth2/patient-select.html.twig",
                [
                    'patients' => $patients
                    , 'hasMore' => $hasMore
                    , 'errorMessage' => $errorMessage
                    , 'searchAction' => $searchAction
                    , 'fname' => $searchParams['fname'] ?? ''
                    , 'lname' => $searchParams['lname'] ?? ''
                    , 'mname' => $searchParams['mname'] ?? ''
                    , 'redirect' => $redirect
                ]
            );
        } catch (AccessDeniedException $error) {
            // make sure to grab the redirect uri before the session is destroyed
            $redirectUri = $this->getClientRedirectURI();
            $this->logger->error("AuthorizationController->patientSelect() Exception thrown", ['exception' => $error->getMessage(), 'userId' => $user_uuid]);
            SessionUtil::oauthSessionCookieDestroy();
            $error = OAuthServerException::accessDenied("No access to patient data for this user", $redirectUri, $error);
            $response = (new Psr17Factory())->createResponse();
            $this->emitResponse($error->generateHttpResponse($response));
        } catch (\Exception $error) {
            // error occurred, no patients found just display the screen with an error message
            $error_message = "There was a server error in loading patients.  Contact your system administrator for assistance";
            $this->logger->error("AuthorizationController->patientSelect() Exception thrown", ['exception' => $error->getMessage()]);
            echo $this->twig->render(
                "smart/patient-select.html.twig",
                [
                    'patients' => $patients
                    , 'hasMore' => $hasMore
                    , 'errorMessage' => $errorMessage
                    , 'searchAction' => $searchAction
                    , 'fname' => $searchParams['fname'] ?? ''
                    , 'lname' => $searchParams['lname'] ?? ''
                    , 'mname' => $searchParams['mname'] ?? ''
                    , 'redirect' => $redirect
                ]
            );
        }
    }

    private function getTwig()
    {
        return $this->twig;
    }

    private function renderTwigPage($pageName, $template, $templateVars)
    {
        $twig = $this->getTwig();
        $templatePageEvent = new TemplatePageEvent($pageName, [], $template, $templateVars);
        $updatedTemplatePageEvent = $this->dispatcher->dispatch($templatePageEvent);
        $template = $updatedTemplatePageEvent->getTwigTemplate();
        $vars = $updatedTemplatePageEvent->getTwigVariables();
        // TODO: @adunsulag do we want to catch exceptions here?
        try {
            echo $twig->render($template, $vars);
        } catch (\Exception $e) {
            $this->logger->errorLogCaller("caught exception rendering template", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            echo $twig->render("error/general_http_error.html.twig", ['statusCode' => 500]);
            die();
        }
    }

    private function renderTwigJson($pageName, $template, $templateVars, $defaultTemplate = null)
    {
        $twig = $this->getTwig();
        $templatePageEvent = new TemplatePageEvent($pageName, [], $template, $templateVars);
        $updatedTemplatePageEvent = $this->dispatcher->dispatch($templatePageEvent);
        $template = $updatedTemplatePageEvent->getTwigTemplate();
        $vars = $updatedTemplatePageEvent->getTwigVariables();
        // TODO: @adunsulag do we want to catch exceptions here?
        try {
            $templates = [$template];
            if (isset($defaultTemplate)) {
                $templates[] = $defaultTemplate;
            }
            $resolvedTemplate = $twig->resolveTemplate($templates);
            echo $resolvedTemplate->render($vars);
        } catch (\Exception $e) {
            $this->logger->errorLogCaller("caught exception rendering template", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            echo $twig->render("error/general_http_error.json.twig", ['statusCode' => 500]);
            die();
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
        $uriList = $client->getRedirectUri();
        $uri = $uriList;
        if (is_array($uriList) && !empty($uriList)) {
            $validator = new RedirectUriValidator($uri);
            $uri = $uriList[0]; // we grab the first one if we don't have one in the session already

            // this is probably overly paranoid but we want to safeguard against any session tampering and use the same logic
            // to validate the redirect_uri as we do elsewhere in the system
            // if we have multiple redirect_uris and we have the redirect uri in our session
            if (!empty($_SESSION['redirect_uri'])) {
                if ($validator->validateRedirectUri($_SESSION['redirect_uri'])) {
                    $uri = $_SESSION['redirect_uri'];
                }
            }
        }
        return $uri;
    }

    private function smartAppStyles()
    {
        $cssTheme = $GLOBALS['css_header'];
        $baseCssTheme = basename($cssTheme);
        $parts = explode(".", $baseCssTheme);
        $coreTheme = $parts[0] ?? "style_light";
        $logoService = new LogoService();
        // do we want to expose each of the logos?  These really need to be cached instead of hitting FS each time...
        $primaryLogo = $GLOBALS['site_addr_oath'] . $GLOBALS['web_root'] . $logoService->getLogo("core/login/primary");
        $context = [
            'logo' => [
                'primary' => $primaryLogo
            ]
        ];
        $defaultFile = "/api/smart/smart-style_light.json.twig";
        $this->renderTwigJson('oauth2/authorize/smart-style', "/api/smart/smart-" . $coreTheme . ".json.twig", $context, $defaultFile);
    }
}
