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
use Exception;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\Psr17Factory;
use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\Events\Core\TemplatePageEvent;
use OpenEMR\FHIR\SMART\SmartLaunchController;
use OpenEMR\Services\LogoService;
use OpenEMR\Services\PatientService;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Twig\Environment;

class SMARTAuthorizationController
{
    /**
     * @var SystemLogger
     */
    private readonly SystemLogger $logger;

    /**
     * @var EventDispatcherInterface
     */
    private readonly EventDispatcherInterface $dispatcher;

    private PatientContextSearchController $patientContextSearchController;

    private ClientRepository $clientRepository;

    private readonly OEGlobalsBag $globalsBag;

    private LogoService $logoService;

    const PATIENT_SELECT_PATH = "/smart/patient-select";

    const PATIENT_SELECT_CONFIRM_ENDPOINT = "/smart/patient-select-confirm";

    /**
     * Maximum number of patients that can be displayed in a search result.
     */
    const PATIENT_SEARCH_MAX_RESULTS = 100;

    const EHR_SMART_LAUNCH_AUTOSUBMIT = "/smart/ehr-launch-autosubmit";

    const SMART_STYLE_URL = "/smart/smart-style";


    /**
     * SMARTAuthorizationController constructor.
     * TODO: @adunsulag this constructor has a lot of parameters, we should look at refactoring this to use a configuration object or reduce the scope of what this class does.
     * @param SessionInterface $session The session interface to use for storing session data.
     * @param OEHttpKernel $kernel The OpenEMR kernel to use for getting the system logger and event dispatcher.
     * @param string $authBaseFullURL The base URL of the oauth2 url
     * @param string $smartFinalRedirectURL The URL that should be redirected to once all SMART authorizations are complete.
     * @param string $oauthTemplateDir The directory that the oauth template files can be included from.
     * @param Environment $twig The twig template engine to use for rendering pages.
     */
    public function __construct(
        private readonly SessionInterface $session,
        OEHttpKernel $kernel,
        private readonly string $authBaseFullURL,
        private readonly string $smartFinalRedirectURL,
        private readonly string $oauthTemplateDir,
        private readonly Environment $twig
    ) {
        $this->logger = $kernel->getSystemLogger();
        $this->dispatcher = $kernel->getEventDispatcher();
        $this->globalsBag = $kernel->getGlobalsBag();
    }

    public function setPatientContextSearchController(PatientContextSearchController $patientContextSearchController): void
    {
        $this->patientContextSearchController = $patientContextSearchController;
    }

    public function getPatientContextSearchController(): PatientContextSearchController
    {
        if (!isset($this->patientContextSearchController)) {
            $this->patientContextSearchController = new PatientContextSearchController(new PatientService(), $this->logger);
        }
        return $this->patientContextSearchController;
    }

    /**
     * Checks to make sure that the passed in end point points to a valid SMART oauth2 endpoint
     * @param $end_point string the route url
     * @return bool true if the route should be handled by this controller, false otherwise
     */
    public function isValidRoute(string $end_point): bool
    {
        if (false !== stripos($end_point, self::PATIENT_SELECT_PATH)) {
            return true;
        }
        if (false !== stripos($end_point, self::PATIENT_SELECT_CONFIRM_ENDPOINT)) {
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
     * @param string $end_point The route endpoint that was requested, which should be one of the SMART oauth2 endpoints.
     * @param HttpRestRequest $request The request object containing the query parameters and other request data.
     */
    public function dispatchRoute(string $end_point, HttpRestRequest $request): ResponseInterface
    {

        // order here matters
        if (false !== stripos($end_point, self::PATIENT_SELECT_CONFIRM_ENDPOINT)) {
            // session is maintained
            return $this->patientSelectConfirm($request);
        } else if (false !== stripos($end_point, self::PATIENT_SELECT_PATH)) {
            // session is maintained
            return $this->patientSelect($request);
        } else if (false !== stripos($end_point, self::EHR_SMART_LAUNCH_AUTOSUBMIT)) {
            return $this->ehrLaunchAutoSubmit($request);
        } else if (false !== stripos($end_point, self::SMART_STYLE_URL)) {
            return $this->smartAppStyles();
        } else {
            $this->logger->error("SMARTAuthorizationController->dispatchRoute() called with invalid route. verify isValidRoute configured properly", ['end_point' => $end_point]);
            return (new Psr17Factory())->createResponse()
                ->withStatus(404)
                ->withHeader('Content-Type', 'application/json')
                ->withBody((new Psr17Factory())->createStream(json_encode(['error' => 'Not Found'])));
        }
    }

    public function ehrLaunchAutoSubmit(HttpRestRequest $request): ResponseInterface
    {
        // grab the server query string and let's go back to our authorize endpoint
        $endpoint = $this->authBaseFullURL . "/authorize?autosubmit=1&" . http_build_query($request->query->all());
        $data = [
            'endpoint' => $endpoint
        ];
        return $this->renderTwigPage('oauth2/authorize/ehr-launch-auto-submit', "oauth2/ehr-launch-autosubmit.html.twig", $data);
    }

    /**
     * Does the current request and session data require an oauth2 flow to be interrupted and go through the smart
     * endpoints.
     * @return bool
     */
    public function needSMARTAuthorization(): bool
    {
        if (empty($this->session->get('puuid')) && str_contains((string) $this->session->get('scopes'), SmartLaunchController::CLIENT_APP_STANDALONE_LAUNCH_SCOPE)) {
            $this->logger->debug(
                "AuthorizationController->userLogin() SMART app request for patient context ",
                ['scopes' => $this->session->get('scopes', ''), 'puuid' => $this->session->get('puuid')]
            );
            return true;
        }
        return false;
    }

    /**
     * Returns the first SMART oauth2 authorization path to start the SMART flow.
     * @return string
     */
    public function getSmartAuthorizationPath(): string
    {
        // we can extend this to be a bunch of things based on any additional authorization contexts we need
        // to support things like encounter selection, etc, but for now we only support patient selector launch
        return self::PATIENT_SELECT_PATH;
    }

    /**
     * Receives the response of the patient selected, sets up the session and redirects back to the oauth2 regular flow
     */
    public function patientSelectConfirm(HttpRestRequest $request): ResponseInterface
    {
        $user_uuid = $this->session->get('user_id');
        if (!isset($user_uuid)) {
            $this->logger->error("SMARTAuthorizationController->patientSelect() Unauthorized call, user has not authenticated");
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Unauthorized call');
        }

        if (!CsrfUtils::verifyCsrfToken($request->request->get("csrf_token"), 'oauth2', $this->session)) {
            $this->logger->error("SMARTAuthorizationController->patientSelect() Invalid CSRF token");
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Invalid CSRF token');
        }

        // set our patient information up in our pid so we can handle our code property...
        try {
            $patient_id = $request->request->get('patient_id'); // this patient_id is actually a uuid.. wierd
            $searchController = $this->getPatientContextSearchController();
            // throws access denied if user doesn't have access
            // TODO: @adunsulag we should rename this method if it throws an AccessDeniedException
            $searchController->getPatientForUser($patient_id, $user_uuid);
            // put PID in session
            $this->session->set('puuid', $patient_id);

            // now redirect to our scope-authorize
            $redirect = $this->smartFinalRedirectURL;
            return (new Psr17Factory())->createResponse()->withStatus(Response::HTTP_TEMPORARY_REDIRECT) // 307 Temporary Redirect
                ->withHeader('Location', $redirect);
        } catch (AccessDeniedException $error) {
            // or should we present some kind of error display form...
            $this->logger->error("AuthorizationController->patientSelect() Exception thrown", ['exception' => $error->getMessage(), 'userId' => $user_uuid]);
            // make sure to grab the redirect uri before the session is destroyed
            $redirectUri = $this->getClientRedirectURI();
            $this->session->invalidate(); // destroy the session so we don't have any stale data
            $error = OAuthServerException::accessDenied("No access to patient data for this user", $redirectUri, $error);
            $response = (new Psr17Factory())->createResponse();
            return $error->generateHttpResponse($response);
        } catch (Exception $error) {
            // error occurred, no patients found just display the screen with an error message
            $this->logger->error("AuthorizationController->patientSelect() Exception thrown", ['exception' => $error->getMessage()]);
            $errorMessage = "There was a server error in loading patients.  Contact your system administrator for assistance";
            $url = $this->authBaseFullURL . self::PATIENT_SELECT_PATH . "?error=" . urlencode($errorMessage);
            return (new Psr17Factory())->createResponse()->withStatus(Response::HTTP_TEMPORARY_REDIRECT) // 307 Temporary Redirect
            ->withHeader('Location', $url);
        }
    }

    /**
     * Displays the patient list and let's user's search and choose a patient.  If the user doesn't have access to patient
     * demographics we die on the security piece.
     * @param HttpRestRequest $request
     * @return ResponseInterface
     */
    public function patientSelect(HttpRestRequest $request): ResponseInterface
    {
        $user_uuid = $this->session->get('user_id');
        if (empty($user_uuid)) {
            $this->logger->error("SMARTAuthorizationController->patientSelect() Unauthorized call, user has not authenticated");
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Invalid request');
        }

        $hasMore = false;
        $patients = [];
        //  handle our patient selected piece, populate the session and now present the authorize piece
        $redirect = $this->authBaseFullURL . self::PATIENT_SELECT_CONFIRM_ENDPOINT;
        $searchAction = $this->authBaseFullURL . self::PATIENT_SELECT_PATH;
        $errorMessage = $request->query->get('error', '');

        try {
            // we've got a user by their UUID... we need to grab the db user id
            $searchParams = $request->get('search', []);

            // grab our list of patients to select from.
            $searchController = $this->getPatientContextSearchController();
            $patients = $searchController->searchPatients($searchParams, $user_uuid);
            $hasMore = count($patients) > self::PATIENT_SEARCH_MAX_RESULTS;
            $patients = $hasMore ? array_slice($patients, 0, self::PATIENT_SEARCH_MAX_RESULTS) : $patients;

            return $this->renderTwigPage(
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
                    , 'csrfToken' => CsrfUtils::collectCsrfToken('oauth2', $this->session)
                ]
            );
        } catch (AccessDeniedException $error) {
            // make sure to grab the redirect uri before the session is destroyed
            $redirectUri = $this->getClientRedirectURI();
            $this->logger->error("AuthorizationController->patientSelect() Exception thrown", ['exception' => $error->getMessage(), 'userId' => $user_uuid]);
            $this->session->invalidate();
            $error = OAuthServerException::accessDenied("No access to patient data for this user", $redirectUri, $error);
            $response = (new Psr17Factory())->createResponse();
            return $error->generateHttpResponse($response);
        } catch (Exception $error) {
            // error occurred, no patients found just display the screen with an error message
            $error_message = "There was a server error in loading patients.  Contact your system administrator for assistance";
            $this->logger->error("AuthorizationController->patientSelect() Exception thrown", [
                'exception' => $error->getMessage()
                ,'trace' => $error->getTraceAsString()]);
            return $this->renderTwigPage(
                'oauth2/authorize/patient-select',
                "oauth2/patient-select.html.twig",
                [
                    'patients' => $patients
                    , 'hasMore' => $hasMore
                    , 'errorMessage' => $error_message
                    , 'searchAction' => $searchAction
                    , 'fname' => $searchParams['fname'] ?? ''
                    , 'lname' => $searchParams['lname'] ?? ''
                    , 'mname' => $searchParams['mname'] ?? ''
                    , 'redirect' => $redirect
                ]
            );
        }
    }

    private function getTwig(): Environment
    {
        return $this->twig;
    }

    private function renderTwigPage($pageName, $template, $templateVars): ResponseInterface
    {
        $twig = $this->getTwig();
        $templatePageEvent = new TemplatePageEvent($pageName, [], $template, $templateVars);
        $updatedTemplatePageEvent = $this->dispatcher->dispatch($templatePageEvent);
        $template = $updatedTemplatePageEvent->getTwigTemplate();
        $vars = $updatedTemplatePageEvent->getTwigVariables();
        // TODO: @adunsulag do we want to catch exceptions here?
        try {
            return (new Psr17Factory())->createResponse()
                ->withStatus(Response::HTTP_OK)
                ->withHeader('Content-Type', 'text/html; charset=UTF-8')
                ->withBody((new Psr17Factory())->createStream($twig->render($template, $vars)));
        } catch (Exception $e) {
            $this->logger->errorLogCaller("caught exception rendering template", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return (new Psr17Factory())->createResponse()
                ->withStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
                ->withHeader('Content-Type', 'text/html; charset=UTF-8')
                ->withBody((new Psr17Factory())->createStream($twig->render("error/general_http_error.html.twig", ['statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR])));
        }
    }

    private function renderTwigJson($pageName, $template, $templateVars, $defaultTemplate = null): ResponseInterface
    {
        $twig = $this->getTwig();
        $templatePageEvent = new TemplatePageEvent($pageName, [], $template, $templateVars);
        $updatedTemplatePageEvent = $this->dispatcher->dispatch($templatePageEvent);
        $template = $updatedTemplatePageEvent->getTwigTemplate();
        $vars = $updatedTemplatePageEvent->getTwigVariables();
        try {
            $templates = [$template];
            if (isset($defaultTemplate)) {
                $templates[] = $defaultTemplate;
            }
            $resolvedTemplate = $twig->resolveTemplate($templates);
            $response = new JsonResponse($resolvedTemplate->render($vars));
        } catch (Exception $e) {
            $this->logger->errorLogCaller("caught exception rendering template", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $response = new JsonResponse($twig->render("error/general_http_error.json.twig", ['statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR]), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $psrFactory = new PsrHttpFactory();
        return $psrFactory->createResponse($response);
    }

    public function setClientRepository(ClientRepository $repository): void
    {
        $this->clientRepository = $repository;
    }

    public function getClientRepository(): ClientRepository
    {
        if (!isset($this->clientRepository)) {
            $this->clientRepository = new ClientRepository();
            $this->clientRepository->setSystemLogger($this->logger);
        }
        return $this->clientRepository;
    }

    /**
     * Returns the client redirect URI to send error responses back.
     * @return string
     */
    private function getClientRedirectURI(): string
    {
        $client_id = $this->session->get('client_id');
        $repo = $this->getClientRepository();
        $client = $repo->getClientEntity($client_id);
        $uriList = $client->getRedirectUri();
        $uri = $uriList;
        if (is_array($uriList) && !empty($uriList)) {
            $validator = new RedirectUriValidator($uri);
            $uri = $uriList[0]; // we grab the first one if we don't have one in the session already

            // this is probably overly paranoid but we want to safeguard against any session tampering and use the same logic
            // to validate the redirect_uri as we do elsewhere in the system
            // if we have multiple redirect_uris and we have the redirect uri in our session
            if (!empty($this->session->get('redirect_uri'))) {
                if ($validator->validateRedirectUri($this->session->get('redirect_uri'))) {
                    $uri = $this->session->get('redirect_uri');
                }
            }
        }
        return $uri;
    }

    public function smartAppStyles(): ResponseInterface
    {
        $cssTheme = $this->globalsBag->get('css_header');
        $baseCssTheme = basename((string) $cssTheme);
        $parts = explode(".", $baseCssTheme);
        $coreTheme = !empty($parts[0]) ? $parts[0] : "style_light";
        $logoService = $this->getLogoService();
        // do we want to expose each of the logos?  These really need to be cached instead of hitting FS each time...
        $primaryLogo = $this->globalsBag->get('site_addr_oath') . $this->globalsBag->get('web_root') . $logoService->getLogo("core/login/primary");
        $context = [
            'logo' => [
                'primary' => $primaryLogo
            ]
        ];
        $defaultFile = "/api/smart/smart-style_light.json.twig";
        return $this->renderTwigJson('oauth2/authorize/smart-style', "/api/smart/smart-" . $coreTheme . ".json.twig", $context, $defaultFile);
    }

    public function setLogoService(LogoService $logoService): void
    {
        $this->logoService = $logoService;
    }

    public function getLogoService(): LogoService
    {
        if (!isset($this->logoService)) {
            $this->logoService = new LogoService();
        }
        return $this->logoService;
    }
}
