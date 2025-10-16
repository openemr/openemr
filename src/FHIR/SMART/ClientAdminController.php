<?php

/**
 * ClientAdminController is both the controller and presentation class for the OAUTH2 clients in the OpenEMR system.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\FHIR\SMART;

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\OAuth2KeyConfig;
use OpenEMR\Common\Auth\OAuth2KeyException;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\JWT\JsonWebKeyParser;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\AccessTokenRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\RefreshTokenRepository;
use OpenEMR\Common\Csrf\CsrfInvalidException;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\Kernel;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Events\Core\TemplatePageEvent;
use OpenEMR\FHIR\SMART\ExternalClinicalDecisionSupport\RouteController;
use OpenEMR\Services\DecisionSupportInterventionService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\TrustedUserService;
use OpenEMR\Services\UserService;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Twig\Environment;
use Exception;
use JsonException;

class ClientAdminController
{
    use SystemLoggerAwareTrait;

    const REVOKE_TRUSTED_USER = 'revoke-trusted-user';
    const REVOKE_ACCESS_TOKEN = 'revoke-access-token';
    const REVOKE_REFRESH_TOKEN = 'revoke-refresh-token';
    const TOKEN_TOOLS_ACTION = 'token-tools';
    const PARSE_TOKEN_ACTION = "parse-token";

    const CSRF_TOKEN_NAME = 'ClientAdminController';
    const SCOPE_PREVIEW_DISPLAY = 6;

    private RouteController $externalCDRController;

    private ActionUrlBuilder $actionUrlBuilder;

    private Environment $twig;

    private Kernel $kernel;

    private AccessTokenRepository $accessTokenRepository;

    private string $webroot;

    /**
     * ClientAdminController constructor.
     * @param ClientRepository $clientRepo The repository object that let's us retrieve OAUTH2 EntityClient objects
     * @param string $actionURL The URL that we will send requests back to
     */
    public function __construct(private OEGlobalsBag $globalsBag, private SessionInterface $session, private ClientRepository $clientRepo, private string $actionURL)
    {
        $this->kernel = $this->globalsBag->get('kernel');
        $this->actionUrlBuilder = new ActionUrlBuilder($this->session, $this->actionURL, self::CSRF_TOKEN_NAME);
        $this->twig = (new TwigContainer(null, $this->kernel))->getTwig();
        $this->webroot = $this->globalsBag->get('web_root');
    }

    public function setTwig(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function setExternalCDRController(RouteController $controller): void
    {
        $this->externalCDRController = $controller;
    }

    public function getExternalCDRController(): RouteController
    {
        if (!isset($this->externalCDRController)) {
            $this->externalCDRController = new RouteController(
                $this->session,
                $this->clientRepo,
                $this->getSystemLogger(),
                $this->getTwig(),
                $this->actionUrlBuilder,
                new DecisionSupportInterventionService()
            );
        }
        return $this->externalCDRController;
    }

    /**
     * Validates the given request for CSRF attacks as well as ACL permissions.
     * @param string $action The action that is being requested
     * @throws CsrfInvalidException If the CSRF token is required but invalid for the given action request
     * @throws AccessDeniedException If the user does not have permission to make the requested action
     */
    public function checkSecurity(string $action): void
    {
        if ($this->shouldCheckCSRFTokenForRequest($action)) {
            $CSRFToken = $this->getCSRFToken();
            if (!CsrfUtils::verifyCsrfToken($CSRFToken, self::CSRF_TOKEN_NAME, $this->session)) {
                throw new CsrfInvalidException(xlt('Authentication Error'));
            }
        }

        if (!AclMain::aclCheckCore('admin', 'super')) {
            throw new AccessDeniedException('admin', 'super');
        }
    }

    /**
     * Given an action and request array dispatch the action to the different request routes this
     * controller handles.
     * @param Request $request
     * @throws AccessDeniedException
     * @return Response
     */
    public function dispatch(Request $request): Response
    {
        $action = $this->normalizeAction($request);
        $this->checkSecurity($action);

        if (empty($action)) {
            return $this->listAction();
        } else if ($this->getExternalCDRController()->supportsRequest($request)) {
            return $this->getExternalCDRController()->dispatch($request);
        }

        $parts = explode("/", $action);

        $mainAction = $parts[0];
        $mainActionChild = $parts[1] ?? '';
        $subAction = $parts[2] ?? '';

        // route /list

        return match ($mainAction) {
            'list' => $this->listAction(),
            self::TOKEN_TOOLS_ACTION => match ($mainActionChild) {
                '' => $this->tokenToolsAction($request)
                ,self::PARSE_TOKEN_ACTION => $this->parseTokenAction($request)
                ,self::REVOKE_ACCESS_TOKEN => $this->toolsRevokeAccessTokenAction($request)
                ,self::REVOKE_REFRESH_TOKEN => $this->toolsRevokeRefreshToken($request)
                ,default => $this->notFoundAction()
            },
            'edit' => match ($subAction) {
                '' => $this->editAction($mainActionChild, $request)
                ,'enable' => $this->enableAction($mainActionChild)
                ,'disable' => $this->disableAction($mainActionChild)
                ,self::REVOKE_TRUSTED_USER => $this->revokeTrustedUserAction($mainActionChild, $request)
                ,self::REVOKE_ACCESS_TOKEN => $this->revokeAccessToken($mainActionChild, $request)
                ,self::REVOKE_REFRESH_TOKEN => $this->revokeRefreshToken($mainActionChild, $request)
                ,'enable-authorization-flow-skip' => $this->enableAuthorizationFlowSkipAction($mainActionChild)
                ,'disable-authorization-flow-skip' => $this->disableAuthorizationFlowSkipAction($mainActionChild)
                , default => $this->notFoundAction()
            }
            ,default => $this->notFoundAction()
        };
    }

    /**
     * Renders the list of OAUTH2 clients to the screen.
     */
    public function listAction(): Response
    {
        $clients = $this->clientRepo->listClientEntities();
        $clientListRecords = [];
        foreach ($clients as $client) {
            $scopeList = $client->getScopes();
            $count = count($scopeList);
            if ($count > self::SCOPE_PREVIEW_DISPLAY) {
                $scopeList = array_splice($scopeList, 0, self::SCOPE_PREVIEW_DISPLAY);
            }
            $clientListRecords[] = [
                'link' => $this->actionUrlBuilder->buildUrl(['edit', $client->getIdentifier()])
                ,'client' => $client
                ,'scopes' => $scopeList
                ,'scopeCount' => $count
                ,'hasMoreScopes' => $count > self::SCOPE_PREVIEW_DISPLAY
                ,'moreScopeCount' => $count - self::SCOPE_PREVIEW_DISPLAY
            ];
        }
        $params = [
            'nav' => $this->getDefaultNavData()
            ,'clients' => $clientListRecords
        ];
        return $this->renderTwigPage("interface/smart/admin-client/list", "interface/smart/admin-client/list.html.twig", $params);
    }

    private function getDefaultNavData(): array
    {
        return [
                'title' => xl('Client Registrations'),
                'navs' => [
                    ['title' => xl('Token Tools'), 'url' => $this->actionUrlBuilder->buildUrl([self::TOKEN_TOOLS_ACTION])]
                    ,['title' => xl('Register New App'), 'url' => $this->webroot . '/interface/smart/register-app.php']
                ]
        ];
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * @return EventDispatcher
     * @throws Exception
     */
    public function getEventDispatcher(): EventDispatcher
    {
        return $this->kernel->getEventDispatcher();
    }

    /**
     * @param string $pageName
     * @param string $template
     * @param array $templateVars
     * @param int $statusCode
     * @return Response
     */
    private function renderTwigPage(string $pageName, string $template, array $templateVars, int $statusCode = Response::HTTP_OK): Response
    {
        try {
            $twig = $this->getTwig();
            $templatePageEvent = new TemplatePageEvent($pageName, [], $template, $templateVars);
            $dispatcher = $this->getEventDispatcher();
            $updatedTemplatePageEvent = $dispatcher->dispatch($templatePageEvent);
            $template = $updatedTemplatePageEvent->getTwigTemplate();
            $vars = $updatedTemplatePageEvent->getTwigVariables();
            $responseBody = $twig->render($template, $vars);
        } catch (Exception $e) {
            $this->getSystemLogger()->errorLogCaller("caught exception rendering template", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            try {
                $responseBody = $twig->render("error/general_http_error.html.twig", ['statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR]);
            } catch (Exception $e) {
                $this->getSystemLogger()->errorLogCaller("caught exception rendering error template", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                $responseBody = "Error rendering template";
            }
        }
        return new Response($responseBody, $statusCode);
    }

    /**
     * Action handler that displays the details/edit view of a client represented by the OAUTH2 $clientId
     * @param $clientId
     * @param $request
     * @return Response
     */
    public function editAction($clientId, $request): Response
    {
        $client = $this->clientRepo->getClientEntity($clientId);
        if ($client === false) {
            return $this->notFoundAction();
        }
        $trustedUserService = new TrustedUserService();
        $trustedUsers = $trustedUserService->getTrustedUsersForClient($clientId);

        $usersWithAccessTokens = [];
        $userService = new UserService();
        $patientService = new PatientService();
        foreach ($trustedUsers as $user) {
            // TODO: we need to open an issue to handle pagination as a client could have thousands / tens of thousands of active tokens
            if (UuidRegistry::isValidStringUUID($user['user_id'])) {
                $registryRecord = UuidRegistry::getRegistryRecordForUuid($user['user_id']);
                if ($registryRecord['table_name'] == 'patient_data') {
                    $user['user_type'] = 'patient';
                    $result = $patientService->getOne($user['user_id']);
                    $patient = $result->hasData() ? $result->getData()[0] : null;
                    $user['patient'] = $patient;
                    $user['display_name'] = isset($patient) ? $patient['fname'] . ' ' . $patient['lname'] : "Patient record not found";
                } else {
                    $user['user_type'] = 'user';
                    $user['user'] = $userService->getUserByUUID($user['user_id']);
                    $user['display_name'] = !empty($user['user']) ? $user['user']['username'] : "Record not found";
                }
            }
            $user['accessTokens'] = $this->getAccessTokensForClientUser($clientId, $user['user_id']);
            $user['refreshTokens'] = $this->getRefreshTokensForClientUser($clientId, $user['user_id']);
            $usersWithAccessTokens[] = $user;
        }
        $client->setTrustedUsers($usersWithAccessTokens);
        return $this->renderEdit($client, $request);
    }

    private function getAccessTokenRepository(): AccessTokenRepository
    {
        if (!isset($this->accessTokenRepository)) {
            $this->accessTokenRepository = new AccessTokenRepository($this->globalsBag, $this->session);
        }
        return $this->accessTokenRepository;
    }

    private function getAccessTokensForClientUser($clientId, $user_id): array
    {
        $accessTokenRepository = $this->getAccessTokenRepository();
        $result = [];
        $accessTokens = $accessTokenRepository->getActiveTokensForUser($clientId, $user_id) ?? [];
        foreach ($accessTokens as $token) {
            try {
                $token['scope'] = json_decode((string) $token['scope'], true, 512, JSON_THROW_ON_ERROR);
                $result[] = $token;
            } catch (JsonException $exception) {
                (new SystemLogger())->error("Failed to json_decode api_token scope column. "
                    . $exception->getMessage(), ['id' => $token['id'], 'clientId' => $clientId, 'user_id' => $user_id]);
            }
        }
        return $result;
    }

    private function getRefreshTokensForClientUser($clientId, $user_id): array
    {
        $tokenRepository = new RefreshTokenRepository();
        return $tokenRepository->getActiveTokensForUser($clientId, $user_id) ?? [];
    }

    /**
     * * Action handler that takes care of disabling an OAUTH2 client represented by the $clientId
     * @param string $clientId
     * @return Response
     */
    public function disableAction(string $clientId): Response
    {
        $client = $this->clientRepo->getClientEntity($clientId);
        if ($client === false) {
            return $this->notFoundAction();
        }

        // TODO: adunsulag when PR brought in disable app
        // TODO: adunsulag we should also as part of the disabling the app piece revoke every single client token
        // including access tokens and refresh tokens...
        $message = xl('Disabled Client') . " " . $client->getName();
        return $this->handleEnabledAction($client, false, $message);
    }

    /**
     * Action handler that takes care of enabling an OAUTH2 client represented by the $clientId
     * @param $clientId
     * @return Response
     */
    public function enableAction($clientId): Response
    {
        $client = $this->clientRepo->getClientEntity($clientId);
        if ($client === false) {
            return $this->notFoundAction();
        }
        $message = xl('Enabled Client') . " " . $client->getName();
        return $this->handleEnabledAction($client, true, $message);
    }

    /**
     * Handles any action that the system doesn't currently know how to address.
     * @return Response
     */
    public function notFoundAction(): Response
    {
        $params = [
            'nav' => $this->getDefaultNavData()
        ];
        return $this->renderTwigPage("interface/smart/admin-client/404", "interface/smart/admin-client/404.html.twig", $params, Response::HTTP_NOT_FOUND);
    }

    /**
     * Handles the toggling of the Client isEnabled flag.  Saves the client entity and then redirects back to the
     * edit list displaying the success message passed in.  On error it redirects back to the edit page with the save
     * error.
     * @param ClientEntity $client
     * @param $isEnabled
     * @param $successMessage
     * @return Response
     */
    private function handleEnabledAction(ClientEntity $client, $isEnabled, $successMessage): Response
    {
        $client->setIsEnabled($isEnabled);
        try {
            $this->clientRepo->saveIsEnabled($client, $isEnabled);
            $url = $this->getActionUrl(['edit', $client->getIdentifier()], ["queryParams" => ['message' => $successMessage]]);
            return new Response(null, Response::HTTP_TEMPORARY_REDIRECT, ['Location' => $url]);
        } catch (Exception $ex) {
            return $this->returnFailedToSaveClientResponse($ex, $client);
        }
    }

    /**
     * Displays the passed in client entity as a form to the screen.
     * @param ClientEntity $client
     * @param Request $request
     * @return Response
     */
    private function renderEdit(ClientEntity $client, Request $request): Response
    {
        $listAction = $this->getActionUrl(['list']);
        $disableClientLink = $this->getActionUrl(['edit', $client->getIdentifier(), 'disable']);
        $enableClientLink = $this->getActionUrl(['edit', $client->getIdentifier(), 'enable']);
        $disableSkipAuthorizationFlowLink = $this->getActionUrl(['edit', $client->getIdentifier(), 'disable-authorization-flow-skip']);
        $enableSkipAuthorizationFlowLink = $this->getActionUrl(['edit', $client->getIdentifier(), 'enable-authorization-flow-skip']);
        $isEnabled = $client->isEnabled();
        $allowSkipAuthSetting = $GLOBALS['oauth_ehr_launch_authorization_flow_skip'] === '1';
        $skipAuthorizationFlow = $client->shouldSkipEHRLaunchAuthorizationFlow();
        if (!$allowSkipAuthSetting) {
            $skipAuthorizationFlow = false; // globals overrides this setting
        }

        $requestMessage = $request->get('message', '');

        $formValues = [
            'id' => [
                'type' => 'text'
                ,'label' => xl("Identifier")
                ,'value' => $client->getIdentifier()
            ],
            'name' => [
                'type' => 'text'
                ,'label' => xl("Name")
                ,'value' => $client->getName()
            ],
            'contacts' => [
                'type' => 'text'
                ,'label' => xl("Contacts")
                ,'value' => is_array($client->getContacts()) ? implode("|", $client->getContacts()) : ''
            ],
            'registrationDate' => [
                'type' => 'text'
                ,'label' => xl("Date Registered")
                ,'value' => $client->getRegistrationDate()
            ],
            'confidential' => [
                'type' => 'checkbox'
                ,'label' => xl('Is Confidential')
                , 'checked' => $client->isConfidential()
            ],
            'isEnabled' => [
                'type' => 'checkbox'
                ,'label' => xl('Is Enabled')
                , 'checked' => $isEnabled
                , 'value' => 1
            ],
            'skipEHRLaunchAuthorizationFlow' => [
                'type' => 'checkbox'
                ,'label' => xl('Skip EHR Launch Authorization Flow')
                , 'checked' => $skipAuthorizationFlow
                , 'value' => 1
            ],
            'evidenceBasedDSI' => [
                'type' => 'checkbox'
                ,'label' => xl('Evidence Based Decision Support Intervention Service')
                , 'checked' => $client->hasEvidenceDSI()
                , 'value' => 1
            ],
            'predictiveDSI' => [
                'type' => 'checkbox'
                ,'label' => xl('Predictive Decision Support Intervention Service')
                , 'checked' => $client->hasPredictiveDSI()
                , 'value' => 1
            ],
            'role' => [
                'type' => 'text'
                ,'label' => xl("Role")
                ,'value' => $client->getClientRole()
            ],
            'redirectUri' => [
                'type' => 'text'
                ,'label' => xl("Redirect URI")
                ,'value' => implode("|", $client->getRedirectUri())
            ],
            'launchUri' => [
                'type' => 'text'
                ,'label' => xl("Launch URI")
                ,'value' => $client->getLaunchUri()
            ],
            'jwksUri' => [
                'type' => 'text'
                ,'label' => xl("JSON Web Key Set URI")
                ,'value' => $client->getJwksUri()
            ],
            'jwks' => [
                'type' => 'text'
                ,'label' => xl("JSON Web Key Set")
                ,'value' => $client->getJwks()
            ],
        ];

        if (!$allowSkipAuthSetting) {
            unset($formValues['skipEHRLaunchAuthorizationFlow']);
        }

        $trustedUsersList = [];
        foreach ($client->getTrustedUsers() as $user) {
            $accessTokenList = [];
            foreach ($user['accessTokens'] as $tokenObject) {
                $accessTokenList[] = [
                    'link' => $this->getActionUrl(['edit', $client->getIdentifier(), self::REVOKE_ACCESS_TOKEN, $tokenObject['id']])
                    ,'tokenObj' => $tokenObject
                ];
            }
            $refreshTokenList = [];
            foreach ($user['refreshTokens'] as $tokenObject) {
                $refreshTokenList[] = [
                    'link' => $this->getActionUrl(['edit', $client->getIdentifier(), self::REVOKE_REFRESH_TOKEN, $tokenObject['id']])
                    ,'tokenObj' => $tokenObject
                ];
            }
            $user['accessTokens'] = $accessTokenList;
            $user['refreshTokens'] = $refreshTokenList;
            $trustedUsersList[] = [
                'link' => $this->getActionUrl(['edit', $client->getIdentifier(), self::REVOKE_TRUSTED_USER, $user['user_id']])
                ,'user' => $user
            ];
        }

        $servicesList =  [];
        if ($client->hasDSI()) {
            $dsiService = new DecisionSupportInterventionService();
            $service = $dsiService->getServiceForClient($client);
            // we want to make sure we can add additional services at some point
            $servicesList[] = [
                'service' => $service
                ,'link' => $this->getActionUrl([RouteController::EXTERNAL_CDR_ACTION, 'edit', $client->getIdentifier()])
            ];
        }
        $data = [
            'listAction' => $listAction
            ,'client' => $client
            ,'allowSkipAuthSetting' => $allowSkipAuthSetting
            ,'skipAuthorizationFlow' => $skipAuthorizationFlow
            ,'disableSkipAuthorizationFlowLink' => $disableSkipAuthorizationFlowLink
            ,'enableSkipAuthorizationFlowLink' => $enableSkipAuthorizationFlowLink
            ,'disableClientLink' => $disableClientLink
            ,'enableClientLink' => $enableClientLink
            ,'requestMessage' => $requestMessage
            ,'isEnabled' => $isEnabled
            ,'formValues' => $formValues
            ,'scopes' => $client->getScopes()
            ,'trustedUsers' => $trustedUsersList
            ,'services' => $servicesList
            ,'nav' => $this->getDefaultNavData()
        ];
        return $this->renderTwigPage("interface/smart/admin-client/edit", "interface/smart/admin-client/edit.html.twig", $data);
    }

    /**
     * Prepares the default request array passed into the class, filling in any missing parameters
     * the class needs in the request.
     * @param Request $request
     * @return string
     */
    private function normalizeAction(Request $request): string
    {
        $action = $request->query->getString('action');
        // if the request is empty with us on a list page we want to populate it
        // anything else we do to the request should be put there
        if (empty($action)) {
            $action = 'list/';
        }
        return $action;
    }

    /**
     * Checks to see if the request needs a CSRF check.  Which everything but
     * the list action (default page) does.
     * @param string $action
     * @return bool True if CSRF is required, false otherwise.
     */
    private function shouldCheckCSRFTokenForRequest(string $action): bool
    {
        // we don't check CSRF for a basic get and list action
        // anything else requires the CSRF token
        if ($action === 'list/') {
            return false;
        }
        return true;
    }

    /**
     * Retrieves the CSRF token string to use
     * @return false|string
     */
    private function getCSRFToken(): false|string
    {
        return CsrfUtils::collectCsrfToken(self::CSRF_TOKEN_NAME, $this->session);
    }

    /**
     * Returns a URL string representing an action request to this controller.  You can pass in a single action
     * or pass in an array with each element representing a segment of the action.  Additional query params to pass
     * to the actions can be specified in the options array.
     * @param $action
     * @param array $options
     * @return string
     */
    private function getActionUrl($action, array $options = []): string
    {
        if (is_array($action)) {
            $action = implode("/", $action);
        }
        $url = $this->actionURL . "?action=" . urlencode((string) $action) . "&csrf_token=" . urlencode($this->getCSRFToken());
        if (!empty($options['queryParams'])) {
            foreach ($options['queryParams'] as $key => $param) {
                $url .= "&" . urlencode((string) $key) . "=" . urlencode((string) $param);
            }
        }

        return $url;
    }

    /**
     * @param $clientId
     * @param Request $request
     * @throws AccessDeniedException
     * @return Response
     */
    private function revokeTrustedUserAction($clientId, Request $request): Response
    {
        $action = $request->query->get('action', '');
        $parts = explode("/", $action);
        $trustedUserId = $parts[3] ?? null;
        if (empty($trustedUserId)) {
            return $this->notFoundAction();
        }
        // need to delete the trusted user action
        $trustedUserService = new TrustedUserService();
        $originalUser = $trustedUserService->getTrustedUser($clientId, $trustedUserId);
        // make sure the client is the same
        if (empty($originalUser)) {
            throw new AccessDeniedException('admin', 'super', "Attempted to delete trusted user for different client");
        }

        $trustedUserService->deleteTrustedUserById($originalUser['id']);

        $url = $this->getActionUrl(['edit', $clientId], ["queryParams" => ['message' => xlt("Successfully revoked Trusted User")]]);
        return new Response(null, Response::HTTP_TEMPORARY_REDIRECT, ['Location' => $url]);
    }

    /**
     * @param $clientId
     * @param Request $request
     * @return Response
     * @throws AccessDeniedException
     */
    private function revokeRefreshToken($clientId, Request $request): Response
    {
        $action = $request->query->get('action', '');
        $parts = explode("/", $action);
        $tokenId = $parts[3] ?? null;
        if (empty($tokenId)) {
            return $this->notFoundAction();
        }
        // need to delete the trusted user action
        $service = new RefreshTokenRepository();
        $token = $service->getTokenById($tokenId);
        // make sure the client is the same
        if (empty($token) || $token['client_id'] != $clientId) {
            throw new AccessDeniedException('admin', 'super', "Attempted to refresh access token for different client");
        }
        $service->revokeRefreshToken($token['token']);

        $url = $this->getActionUrl(['edit', $clientId], ["queryParams" => ['message' => xlt("Successfully revoked refresh token")]]);
        return new Response(null, Response::HTTP_TEMPORARY_REDIRECT, ['Location' => $url]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws AccessDeniedException
     */
    private function toolsRevokeAccessTokenAction(Request $request): Response
    {
        $clientId = $request->query->get('clientId');
        $token = $request->query->get('token');
        $service = $this->getAccessTokenRepository();
        $accessToken = $service->getTokenById($token);
        // make sure the client is the same
        if (empty($accessToken) || $accessToken['client_id'] != $clientId) {
            throw new AccessDeniedException('admin', 'super', "Attempted to delete access token for different client");
        }
        $service->revokeAccessToken($accessToken['token']);

        $url = $this->getActionUrl([self::TOKEN_TOOLS_ACTION], ["queryParams" => ['message' => xlt("Successfully revoked access token")]]);
        return new Response(null, Response::HTTP_TEMPORARY_REDIRECT, ['Location' => $url]);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws AccessDeniedException
     */
    private function toolsRevokeRefreshToken(Request $request): Response
    {
        $clientId = $request->query->get('clientId');
        $token = $request->query->get('token');
        $service = new RefreshTokenRepository();
        $accessToken = $service->getTokenById($token);
        // make sure the client is the same
        if (empty($accessToken) || $accessToken['client_id'] != $clientId) {
            throw new AccessDeniedException('admin', 'super', "Attempted to delete refresh token for different client");
        }
        $service->revokeRefreshToken($accessToken['token']);

        $url = $this->getActionUrl([self::TOKEN_TOOLS_ACTION], ["queryParams" => ['message' => xlt("Successfully revoked refresh token")]]);
        return new Response(null, Response::HTTP_TEMPORARY_REDIRECT, ['Location' => $url]);
    }

    /**
     * @param $clientId
     * @param Request $request
     * @return Response
     * @throws AccessDeniedException
     */
    private function revokeAccessToken($clientId, Request $request): Response
    {
        $action = $request->query->get('action', '');
        $parts = explode("/", $action);
        $accessToken = $parts[3] ?? null;
        if (empty($accessToken)) {
            return $this->notFoundAction();
        }
        // need to delete the trusted user action
        $service = $this->getAccessTokenRepository();
        $accessToken = $service->getTokenById($accessToken);
        // make sure the client is the same
        if (empty($accessToken) || $accessToken['client_id'] != $clientId) {
            throw new AccessDeniedException('admin', 'super', "Attempted to delete access token for different client");
        }
        $service->revokeAccessToken($accessToken['token']);

        $url = $this->getActionUrl(['edit', $clientId], ["queryParams" => ['message' => xlt("Successfully revoked access token")]]);
        return new Response(null, Response::HTTP_TEMPORARY_REDIRECT, ['Location' => $url]);
    }

    private function tokenToolsAction(Request $request): Response
    {
        $params = [
            'nav' => [
                'title' => xl('Token Tools')
                ,'navs' => [
                    ['title' => xl('Back to Client List'), 'url' => $this->getActionUrl(['list'])]
                ]
            ]
            ,'requestMessage' => $request->query->get('message', '')
            ,'actionUrl' => $this->getActionUrl([self::TOKEN_TOOLS_ACTION, self::PARSE_TOKEN_ACTION])
            ,'tokenSettings' => [
                'value' => ''
                ,'label' => 'Token to parse'
                ,'type' => 'textarea'
                ,'enabled' => true
            ]
        ];
        return $this->renderTwigPage("interface/smart/admin-client/token-tools", "interface/smart/admin-client/token-tools.html.twig", $params);
    }

    private function parseTokenAction(Request $request): Response
    {
        $parts = null;
        $token = $request->request->get('token');
        $textSetting = [
                'value' => $token
                ,'label' => 'Token to parse'
                ,'type' => 'textarea'
                ,'enabled' => true
        ];
        $databaseRecord = null;
        $message = $request->query->get('message', '');
        try {
            if (!empty($token)) {
                $parts = $this->parseTokenIntoParts($token);
                $databaseRecord = $this->getDatabaseRecordForToken($parts['jti'], $parts['token_type']);
                if (!empty($databaseRecord)) {
                    $parts['client_id'] = $databaseRecord['client_id'];
                    $parts['status'] = $databaseRecord['revoked'] != 0 ? 'revoked' : $parts['status'];

                    $queryParams = ['token' => $databaseRecord['id'], 'clientId' => $databaseRecord['client_id']];
                    if ($parts['token_type'] == 'refresh_token') {
                        $parts['revoke_link'] = $this->getActionUrl([self::TOKEN_TOOLS_ACTION, self::REVOKE_REFRESH_TOKEN], ['queryParams' => $queryParams]);
                    } else {
                        $parts['revoke_link'] = $this->getActionUrl([self::TOKEN_TOOLS_ACTION, self::REVOKE_ACCESS_TOKEN], ['queryParams' => $queryParams]);
                    }
                    $parts['user_link'] = $this->getActionUrl(['edit', $databaseRecord['client_id']], ['fragment' => $databaseRecord['user_id']]);
                } else {
                    $message = xl("JWT not found in system");
                    $parts = [];
                }
            }
        } catch (Exception $exception) {
            $this->getSystemLogger()->errorLogCaller("caught exception parsing token", ['message' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);
            $message = xl('Failed to parse token. Check system logs');
            $parts = [];
            $databaseRecord = null;
        }
        $params = [
            'nav' => [
                'title' => xl('Token Tools')
                ,'navs' => [
                    ['title' => xl('Back to Client List'), 'url' => $this->getActionUrl(['list'])]
                ]
            ]
            ,'requestMessage' => $message
            ,'actionUrl' => $this->getActionUrl([self::TOKEN_TOOLS_ACTION, self::PARSE_TOKEN_ACTION])
            ,'tokenSettings' => $textSetting
            ,'databaseRecord' => $databaseRecord
            ,'parts' => $parts
            ,'encodedParts' =>  json_encode($parts, JSON_PRETTY_PRINT)
        ];
        return $this->renderTwigPage("interface/smart/admin-client/token-parse", "interface/smart/admin-client/token-parse.html.twig", $params);
    }

    private function getDatabaseRecordForToken($tokenId, $tokenType): ?array
    {
        if ($tokenType == 'refresh_token') {
            $repo = new RefreshTokenRepository();
        } else {
            $repo = $this->getAccessTokenRepository();
        }
        return $repo->getTokenByToken($tokenId);
    }

    /**
     * @param $rawToken
     * @return array
     * @throws OAuth2KeyException if the token is not valid or cannot be parsed
     */
    private function parseTokenIntoParts($rawToken): array
    {
        $keyConfig = new OAuth2KeyConfig();
        $keyConfig->configKeyPairs();
        $webKeyParser = new JsonWebKeyParser($keyConfig->getEncryptionKey(), $keyConfig->getPublicKeyLocation());

        $tokenType = $webKeyParser->getTokenHintFromToken($rawToken);
        if ($tokenType == 'refresh_token') {
            $tokenParts = $webKeyParser->parseRefreshToken($rawToken);
        } else {
            $tokenParts = $webKeyParser->parseAccessToken($rawToken);
        }
        $tokenParts['token_type'] = $tokenType;
        return $tokenParts;
    }

    private function enableAuthorizationFlowSkipAction(string $clientId): Response
    {
         $client = $this->clientRepo->getClientEntity($clientId);
        if ($client === false) {
            return $this->notFoundAction();
        }
        $message = xl('Enabled Authorization Flow Skip') . " " . $client->getName();
        return $this->handleAuthorizationFlowSkipAction($client, true, $message);
    }
    private function disableAuthorizationFlowSkipAction(string $clientId): Response
    {
         $client = $this->clientRepo->getClientEntity($clientId);
        if ($client === false) {
            return $this->notFoundAction();
        }
        $message = xl('Disabled Authorization Flow Skip') . " " . $client->getName();
        return $this->handleAuthorizationFlowSkipAction($client, false, $message);
    }
    private function handleAuthorizationFlowSkipAction(ClientEntity $client, bool $skipFlow, string $successMessage): Response
    {
        $client->setSkipEHRLaunchAuthorizationFlow($skipFlow);
        try {
            $this->clientRepo->saveSkipEHRLaunchFlow($client, $skipFlow);
            $url = $this->getActionUrl(['edit', $client->getIdentifier()], ["queryParams" => ['message' => $successMessage]]);
            return new Response(null, Response::HTTP_TEMPORARY_REDIRECT, ['Location' => $url]);
        } catch (Exception $ex) {
            return $this->returnFailedToSaveClientResponse($ex, $client);
        }
    }

    /**
     * @param Exception $ex
     * @param ClientEntity $client
     * @return Response
     */
    private function returnFailedToSaveClientResponse(Exception $ex, ClientEntity $client): Response
    {
        $this->getSystemLogger()->error(
            "Failed to save client",
            [
                "exception" => $ex->getMessage(), "trace" => $ex->getTraceAsString()
                , 'client' => $client->getIdentifier()
            ]
        );

        $message = xl('Client failed to save. Check system logs');
        $url = $this->getActionUrl(['edit', $client->getIdentifier()], ["queryParams" => ['message' => $message]]);
        return new Response(null, Response::HTTP_TEMPORARY_REDIRECT, ['Location' => $url]);
    }
}
