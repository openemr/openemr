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
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\Header;
use OpenEMR\FHIR\SMART\ExternalClinicalDecisionSupport\RouteController;
use OpenEMR\Services\DecisionSupportInterventionService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\TrustedUserService;
use OpenEMR\Services\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

class ClientAdminController
{
    const REVOKE_TRUSTED_USER = 'revoke-trusted-user';
    const REVOKE_ACCESS_TOKEN = 'revoke-access-token';
    const REVOKE_REFRESH_TOKEN = 'revoke-refresh-token';
    const TOKEN_TOOLS_ACTION = 'token-tools';
    const PARSE_TOKEN_ACTION = "parse-token";

    private $actionURL;

    /**
     * @var ClientRepository
     */
    private $clientRepo;

    /**
     * @var LoggerInterface
     */
    private $logger;

    const CSRF_TOKEN_NAME = 'ClientAdminController';
    const SCOPE_PREVIEW_DISPLAY = 6;

    private RouteController $externalCDRController;

    private ActionUrlBuilder $actionUrlBuilder;

    private Environment $twig;

    /**
     * ClientAdminController constructor.
     * @param ClientRepository $repo The repository object that let's us retrieve OAUTH2 EntityClient objects
     * @param $actionURL The URL that we will send requests back to
     */
    public function __construct(ClientRepository $repo, LoggerInterface $logger, Environment $twig, $actionURL)
    {
        $this->clientRepo = $repo;
        $this->logger = $logger;
        $this->actionURL = $actionURL;
        $this->actionUrlBuilder = new ActionUrlBuilder($actionURL, self::CSRF_TOKEN_NAME);
        $this->twig = $twig;
        $this->externalCDRController = new RouteController($repo, $logger, $twig, $this->actionUrlBuilder, new DecisionSupportInterventionService());
    }

    public function setExternalCDRController(RouteController $controller)
    {
        $this->externalCDRController = $controller;
    }

    /**
     * Validates the given request for CSRF attacks as well as ACL permissions.
     * @param $request
     * @throws CsrfInvalidException If the CSRF token is required but invalid for the given action request
     * @throws AccessDeniedException If the user does not have permission to make the requested action
     */
    public function checkSecurity(string $action, Request $request)
    {
        if ($this->shouldCheckCSRFTokenForRequest($action)) {
            $CSRFToken = $this->getCSRFToken();
            if (!CsrfUtils::verifyCsrfToken($CSRFToken, self::CSRF_TOKEN_NAME)) {
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
     * @param $action
     * @param $request
     * @throws AccessDeniedException
     */
    public function dispatch(Request $request)
    {
        $action = $this->normalizeAction($request);
        $this->checkSecurity($action, $request);

        if (empty($action)) {
            return $this->listAction($request);
        }

        $parts = explode("/", $action);

        $mainAction = $parts[0] ?? null;
        $mainActionChild = $parts[1] ?? null;
        $subAction = $parts[2] ?? null;

        // route /list
        // TODO: @adunsulag this router has gotten way too big and unwieldy... we need to refactor it into something simpler
        if ($mainAction == 'list') {
            $this->listAction($request);
        } else if ($mainAction == self::TOKEN_TOOLS_ACTION) {
            if (empty($mainActionChild)) {
                $this->tokenToolsAction($request);
            } else if ($mainActionChild == self::PARSE_TOKEN_ACTION) {
                return $this->parseTokenAction($request);
            } else if ($mainActionChild == self::REVOKE_ACCESS_TOKEN) {
                return $this->toolsRevokeAccessTokenAction($request);
            } else if ($mainActionChild == self::REVOKE_REFRESH_TOKEN) {
                return $this->toolsRevokeRefreshToken($request);
            } else {
                return $this->notFoundAction($request);
            }
        } else if ($mainAction == 'edit' && !empty($mainActionChild)) {
            $clientId = $mainActionChild;

            if (empty($subAction)) { // route /edit/:clientId
                return $this->editAction($clientId, $request);
            } else if ($subAction == 'enable') { // route /edit/:clientId/enable
                return $this->enableAction($clientId, $request);
            } else if ($subAction == 'disable') { // route /edit/:clientId/disable
                return $this->disableAction($clientId, $request);
            } else if ($subAction == self::REVOKE_TRUSTED_USER) {
                return $this->revokeTrustedUserAction($clientId, $request);
            } else if ($subAction == self::REVOKE_ACCESS_TOKEN) {
                return $this->revokeAccessToken($clientId, $request);
            } else if ($subAction == self::REVOKE_REFRESH_TOKEN) {
                return $this->revokeRefreshToken($clientId, $request);
            } else if ($subAction == 'enable-authorization-flow-skip') {
                return $this->enableAuthorizationFlowSkipAction($clientId, $request);
            } else if ($subAction == 'disable-authorization-flow-skip') {
                return $this->disableAuthorizationFlowSkipAction($clientId, $request);
            } else {
                return $this->notFoundAction($request);
            }
        } else if ($this->externalCDRController->supportsRequest($request)) {
            return $this->externalCDRController->dispatch($request);
        } else {
            return $this->notFoundAction($request);
        }
    }

    /**
     * Renders the list of OAUTH2 clients to the screen.
     * @param $request
     */
    public function listAction($request)
    {
        /**
* <a class="btn btn-secondary btn-sm float-right" href="<?php echo attr($this->getActionUrl([self::TOKEN_TOOLS_ACTION])); ?>" onclick="top.restoreSession()"><?php echo xlt("Token Tools"); ?></a>
                            <a class="btn btn-secondary btn-sm float-right mr-2" href="<?php echo $GLOBALS['webroot']; ?>/interface/smart/register-app.php" onclick="top.restoreSession()"><?php echo xlt("Register New App"); ?></a>
                            <a class="btn btn-secondary btn-sm float-right mr-2" href="<?php echo attr($this->getActionUrl([RouteController::EXTERNAL_CDR_ACTION])); ?>" onclick="top.restoreSession()"><?php echo xlt("External CDR"); ?></a>
 */
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
            'nav' => [
                'title' => xl('Client Registrations'),
                'navs' => [
                    ['title' => xl('Token Tools'), 'url' => $this->actionUrlBuilder->buildUrl([self::TOKEN_TOOLS_ACTION])]
                    ,['title' => xl('Register New App'), 'url' => $GLOBALS['webroot'] . '/interface/smart/register-app.php']
                ]
            ]
            ,'clients' => $clientListRecords
        ];
        echo $this->twig->render("interface/smart/admin-client/list.html.twig", $params);
    }

    /**
     * Action handler that displays the details/edit view of a client represented by the OAUTH2 $clientId
     * @param $clientId
     * @param $request
     */
    public function editAction($clientId, $request)
    {
        $client = $this->clientRepo->getClientEntity($clientId);
        if ($client === false) {
            $this->notFoundAction($request);
            return;
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
        $this->renderEdit($client, $request);
    }

    private function getAccessTokensForClientUser($clientId, $user_id)
    {
        $accessTokenRepository = new AccessTokenRepository();
        $result = [];
        $accessTokens = $accessTokenRepository->getActiveTokensForUser($clientId, $user_id) ?? [];
        foreach ($accessTokens as $token) {
            try {
                $token['scope'] = json_decode($token['scope'], true);
                $result[] = $token;
            } catch (\JsonException $exception) {
                (new SystemLogger())->error("Failed to json_decode api_token scope column. "
                    . $exception->getMessage(), ['id' => $token['id'], 'clientId' => $clientId, 'user_id' => $user_id]);
            }
        }
        return $result;
    }

    private function getRefreshTokensForClientUser($clientId, $user_id)
    {
        $tokenRepository = new RefreshTokenRepository();
        $result = $tokenRepository->getActiveTokensForUser($clientId, $user_id) ?? [];
        return $result;
    }

    /**
     * * Action handler that takes care of disabling an OAUTH2 client represented by the $clientId
     * @param $clientId
     * @param $request
     */
    public function disableAction($clientId, $request)
    {
        $client = $this->clientRepo->getClientEntity($clientId);
        if ($client === false) {
            $this->notFoundAction($request);
            return;
        }

        // TODO: adunsulag when PR brought in disable app
        // TODO: adunsulag we should also as part of the disabling the app piece revoke every single client token
        // including access tokens and refresh tokens...
        $message = xl('Disabled Client') . " " . $client->getName();
        $this->handleEnabledAction($client, false, $message);
        exit;
    }

    /**
     * Action handler that takes care of enabling an OAUTH2 client represented by the $clientId
     * @param $clientId
     * @param $request
     */
    public function enableAction($clientId, $request)
    {
        $client = $this->clientRepo->getClientEntity($clientId);
        if ($client === false) {
            $this->notFoundAction($request);
            return;
        }
        $message = xl('Enabled Client') . " " . $client->getName();
        $this->handleEnabledAction($client, true, $message);
        exit;
    }

    /**
     * Handles any action that the system doesn't currently know how to address.
     * @param $request
     */
    public function notFoundAction($request)
    {
        http_response_code(404);
        echo $this->twig->render("interface/smart/admin-client/404.html.twig");
    }

    /**
     * Handles the toggling of the Client isEnabled flag.  Saves the client entity and then redirects back to the
     * edit list displaying the success message passed in.  On error it redirects back to the edit page with the save
     * error.
     * @param ClientEntity $client
     * @param $isEnabled
     * @param $successMessage
     */
    private function handleEnabledAction(ClientEntity $client, $isEnabled, $successMessage)
    {
        $client->setIsEnabled($isEnabled);
        try {
            $this->clientRepo->saveIsEnabled($client, $isEnabled);
            $url = $this->getActionUrl(['edit', $client->getIdentifier()], ["queryParams" => ['message' => $successMessage]]);
            header("Location: " . $url);
        } catch (\Exception $ex) {
            $this->logger->error(
                "Failed to save client",
                [
                    "exception" => $ex->getMessage(), "trace" => $ex->getTraceAsString()
                    , 'client' => $client->getIdentifier()
                ]
            );

            $message = xl('Client failed to save. Check system logs');
            $url = $this->getActionUrl(['edit', $client->getIdentifier()], ["queryParams" => ['message' => $message]]);
            header("Location: " . $url);
        }
    }

    /**
     * Displays the passed in client entity as a form to the screen.
     * @param ClientEntity $client
     * @param $request
     */
    private function renderEdit(ClientEntity $client, Request $request)
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
        $scopes = $client->getScopes();

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
                ,'value' => implode("|", $client->getContacts())
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
        ];

        echo $this->twig->render("interface/smart/admin-client/edit.html.twig", $data);
    }

    private function renderTextarea($key, $setting)
    {
        $disabled = $setting['enabled'] !== true ? "disabled readonly" : "";
        ?>
        <div class="form-group">
            <label for="<?php echo attr($key); ?>"><?php echo text($setting['label']); ?></label>
            <textarea id="<?php echo attr($key); ?>" name="<?php echo attr($key) ?>"
                      class="form-control" rows="10" <?php echo $disabled; ?>><?php echo attr($setting['value']); ?></textarea>
        </div>
        <?php
    }

    /**
     * Prepares the default request array passed into the class, filling in any missing parameters
     * the class needs in the request.
     * @param $request
     * @return array
     */
    private function normalizeAction(Request $request)
    {
        $action = $request->query->get('action', '');
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
     * @return bool|string
     */
    private function getCSRFToken()
    {
        return CsrfUtils::collectCsrfToken(self::CSRF_TOKEN_NAME);
    }

    /**
     * Returns a URL string representing an action request to this controller.  You can pass in a single action
     * or pass in an array with each element representing a segment of the action.  Additional query params to pass
     * to the actions can be specified in the options array.
     * @param $action
     * @param array $options
     * @return string
     */
    private function getActionUrl($action, $options = array())
    {
        if (\is_array($action)) {
            $action = implode("/", $action);
        }
        $url = $this->actionURL . "?action=" . urlencode($action) . "&csrf_token=" . urlencode($this->getCSRFToken());
        if (!empty($options['queryParams'])) {
            foreach ($options['queryParams'] as $key => $param) {
                $url .= "&" . urlencode($key) . "=" . urlencode($param);
            }
        }

        return $url;
    }

    /**
     * Renders a list of scope elements for a given client to the screen.  If the preview flag is set to true
     * it truncates the list to the number specified in self::SCOPE_PREVIEW_DISPLAY
     * @param ClientEntity $client
     * @param bool $preview
     */
    private function renderScopeList(ClientEntity $client, $preview = false)
    {
       // TODO: adunsulag we can in the future can group these and make this list easier to navigate.
        $scopeList = $client->getScopes();
        if (empty($scopeList)) {
            echo xlt("No scopes");
        }

        $this->renderScopeListArray($scopeList, $preview);
    }

    private function renderScopeListArray(array $scopeList, $preview = false)
    {
        $count = count($scopeList);
        if ($preview && $count > self::SCOPE_PREVIEW_DISPLAY) {
            $scopeList = array_splice($scopeList, 0, self::SCOPE_PREVIEW_DISPLAY);
        }
        ?>
        <ul>
            <?php foreach ($scopeList as $scope) : ?>
                <li><?php echo text($scope); ?></li>
            <?php endforeach; ?>
            <?php if ($preview && $count > self::SCOPE_PREVIEW_DISPLAY) : ?>
                <li>
                    <em><?php echo ($count - self::SCOPE_PREVIEW_DISPLAY) . " " . xlt("additional scopes"); ?></em>...
                </li>
            <?php endif; ?>
        </ul>
        <?php
    }

    /**
     * Renders out the header that each controller action will use.
     */
    private function renderHeader()
    {
        $title = xl('Client Registrations');
        ?>
<html>
    <head>
        <title><?php echo text($title); ?></title>

        <?php Header::setupHeader(); ?>

    </head>
    <body class="body_top">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="page-title">
                        <h2>
                            <?php echo text($title); ?>
                            <a class="btn btn-secondary btn-sm float-right" href="<?php echo attr($this->getActionUrl([self::TOKEN_TOOLS_ACTION])); ?>" onclick="top.restoreSession()"><?php echo xlt("Token Tools"); ?></a>
                            <a class="btn btn-secondary btn-sm float-right mr-2" href="<?php echo $GLOBALS['webroot']; ?>/interface/smart/register-app.php" onclick="top.restoreSession()"><?php echo xlt("Register New App"); ?></a>
                            <a class="btn btn-secondary btn-sm float-right mr-2" href="<?php echo attr($this->getActionUrl([RouteController::EXTERNAL_CDR_ACTION])); ?>" onclick="top.restoreSession()"><?php echo xlt("External CDR"); ?></a>
                        </h2>
                    </div>
                </div>
            </div>
        <?php
    }

    /**
     * Renders the footer html to the screen.
     */
    private function renderFooter()
    {
        ?>
        <div class="row mt-5">
            <div class="col alert alert-info">
                <p><?php echo xlt("This administration page is for managing the list of OAUTH2 registered applications that are authorized to use the APIs."); ?></p>
                <p>
                    <?php echo xlt("SMART apps that display on the patient summary page can be enabled or disabled from this screen. Any OAUTH2 client requesting the 'launch' scope will allow EMR users to launch an app from within the EHR."); ?>
                </p>
                <p>
                    <?php echo xlt("Note it is recommended to avoid approving SMART apps that do not register as a confidential client. These apps are less secure and should be highly restricted in the OAUTH2 scopes you allow them to access."); ?>
                </p>
            </div>
        </div>
        </div> <!-- end .container -->
    </body> <!-- end body.body_top -->
</html>
        <?php
    }


    /**
     * @param $clientId
     * @param array $request
     * @throws AccessDeniedException
     */
    private function revokeTrustedUserAction($clientId, Request $request)
    {
        $action = $request->query->get('action', '');
        $parts = explode("/", $action);
        $trustedUserId = $parts[3] ?? null;
        if (empty($trustedUserId)) {
            return $this->notFoundAction($request);
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
        header("Location: " . $url);
        exit;
    }

    private function revokeRefreshToken($clientId, Request $request)
    {
        $action = $request->query->get('action', '');
        $parts = explode("/", $action);
        $tokenId = $parts[3] ?? null;
        if (empty($tokenId)) {
            return $this->notFoundAction($request);
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
        header("Location: " . $url);
        exit;
    }

    private function toolsRevokeAccessTokenAction(Request $request)
    {
        $clientId = $request->query->get('clientId', null);
        $token = $request->query->get('token', null);
        $service = new AccessTokenRepository();
        $accessToken = $service->getTokenById($token);
        // make sure the client is the same
        if (empty($accessToken) || $accessToken['client_id'] != $clientId) {
            throw new AccessDeniedException('admin', 'super', "Attempted to delete access token for different client");
        }
        $service->revokeAccessToken($accessToken['token']);

        $url = $this->getActionUrl([self::TOKEN_TOOLS_ACTION], ["queryParams" => ['message' => xlt("Successfully revoked access token")]]);
        header("Location: " . $url);
        exit;
    }

    private function toolsRevokeRefreshToken(Request $request)
    {
        $clientId = $request->query->get('clientId', null);
        $token = $request->query->get('token', null);
        $service = new RefreshTokenRepository();
        $accessToken = $service->getTokenById($token);
        // make sure the client is the same
        if (empty($accessToken) || $accessToken['client_id'] != $clientId) {
            throw new AccessDeniedException('admin', 'super', "Attempted to delete refresh token for different client");
        }
        $service->revokeRefreshToken($accessToken['token']);

        $url = $this->getActionUrl([self::TOKEN_TOOLS_ACTION], ["queryParams" => ['message' => xlt("Successfully revoked refresh token")]]);
        header("Location: " . $url);
        exit;
    }

    private function revokeAccessToken($clientId, Request $request)
    {
        $action = $request->query->get('action', '');
        $parts = explode("/", $action);
        $accessToken = $parts[3] ?? null;
        if (empty($accessToken)) {
            return $this->notFoundAction($request);
        }
        // need to delete the trusted user action
        $service = new AccessTokenRepository();
        $accessToken = $service->getTokenById($accessToken);
        // make sure the client is the same
        if (empty($accessToken) || $accessToken['client_id'] != $clientId) {
            throw new AccessDeniedException('admin', 'super', "Attempted to delete access token for different client");
        }
        $service->revokeAccessToken($accessToken['token']);

        $url = $this->getActionUrl(['edit', $clientId], ["queryParams" => ['message' => xlt("Successfully revoked access token")]]);
        header("Location: " . $url);
        exit;
    }

    private function tokenToolsAction(Request $request)
    {
        $this->renderTokenToolsHeader($request);
        $actionUrl = $this->getActionUrl([self::TOKEN_TOOLS_ACTION, self::PARSE_TOKEN_ACTION]);
        $textSetting = [
                'value' => ''
                ,'label' => 'Token to parse'
                ,'type' => 'textarea'
                ,'enabled' => true
        ];
        ?>
        <form method="POST" action="<?php echo $actionUrl; ?>">
        <?php
            $this->renderTextarea("token", $textSetting);
        ?>
        <input type="submit" class="btn btn-sm btn-primary" value="<?php echo xla("Parse Token"); ?>" />
        <?php
        $this->renderTokenToolsFooter();
    }

    private function parseTokenAction(Request $request)
    {
        $parts = null;
        $token = $request->query->get('token', null);
        $actionUrl = $this->getActionUrl([self::TOKEN_TOOLS_ACTION, self::PARSE_TOKEN_ACTION]);
        $textSetting = [
                'value' => $token
                ,'label' => 'Token to parse'
                ,'type' => 'textarea'
                ,'enabled' => true
        ];
        if (!empty($token)) {
            $parts = $this->parseTokenIntoParts($token);
            $databaseRecord = $this->getDatabaseRecordForToken($parts['jti'], $parts['token_type']);
            if (!empty($databaseRecord)) {
                $parts['client_id'] = $databaseRecord['client_id'];
                $parts['status'] = $databaseRecord['revoked'] != 0 ? 'revoked' : $parts['status'];
            }

            $queryParams = ['token' => $databaseRecord['id'], 'clientId' => $databaseRecord['client_id']];
            if ($parts['token_type'] == 'refresh_token') {
                $parts['revoke_link'] = $this->getActionUrl([self::TOKEN_TOOLS_ACTION, self::REVOKE_REFRESH_TOKEN], ['queryParams' => $queryParams]);
            } else {
                $parts['revoke_link'] = $this->getActionUrl([self::TOKEN_TOOLS_ACTION, self::REVOKE_ACCESS_TOKEN], ['queryParams' => $queryParams]);
            }
            $parts['user_link'] = $this->getActionUrl(['edit', $databaseRecord['client_id']], ['fragment' => $databaseRecord['user_id']]);
        }

        // now let's grab our parser and see what we can do with all of this.
        $this->renderTokenToolsHeader($request);
        if (empty($databaseRecord)) {
            ?>
        <div class="alert alert-info">
            <?php echo xlt("JWT not found in system"); ?>
        </div>
            <?php
        }
        ?>
        <form method="POST" action="<?php echo $actionUrl; ?>">
        <?php
            $this->renderTextarea("token", $textSetting);
        ?>
        <input type="submit" class="btn btn-sm btn-primary" value="<?php echo xla("Parse Token"); ?>" />

        <?php if (!empty($databaseRecord)) { ?>
        <hr />
        <h3><?php echo xlt("Token details"); ?>
            <?php if ($databaseRecord['revoked'] == 0) : ?>
              <a href="<?php echo attr($parts['revoke_link']); ?>"
                       class="btn btn-sm btn-primary float-right" onclick="top.restoreSession()"><?php echo xlt('Revoke Token'); ?></a>
            <?php endif; ?>
        </h3>
        <ul>
        <li><?php echo xlt("Token JTI(DB token value)"); ?>: <?php echo text($parts['jti']); ?></li>
        <li><?php echo xlt("Token DB Id"); ?>: <?php echo text($databaseRecord['id']); ?></li>
        <li><?php echo xlt("Token Status"); ?>: <?php echo text($parts['status']); ?></li>
        <li><?php echo xlt("Token Type"); ?>: <?php echo text($parts['token_type']); ?></li>
        <li><?php echo xlt("Token Expiration"); ?>: <?php echo text($databaseRecord['expiry']); ?></li>
        <li><?php echo xlt("User UUID"); ?>:
        <a href="<?php echo attr($parts['user_link']); ?>">
            <?php echo text($databaseRecord['user_id']); ?>
        </a>
        </li>
        </ul>
        <pre><?php echo text(json_encode($parts, JSON_PRETTY_PRINT)); ?></pre>
            <?php
        }

        $this->renderTokenToolsFooter();
    }

    private function getDatabaseRecordForToken($tokenId, $tokenType)
    {
        if ($tokenType == 'refresh_token') {
            $repo = new RefreshTokenRepository();
        } else {
            $repo = new AccessTokenRepository();
        }
        return $repo->getTokenByToken($tokenId);
    }

    private function parseTokenIntoParts($rawToken)
    {
        $tokenParts = [];
        try {
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
        } catch (OAuth2KeyException $exception) {
            var_dump($exception->getMessage());
            // TODO: @adunsulag handle how we will work with our key exceptions
        }
        return $tokenParts;
    }

    private function renderTokenToolsHeader(Request $request)
    {
        $listAction = $this->getActionUrl(['list']);
        $message = $request->query->get('message') ?? null;

        $this->renderHeader();
        ?>
        <div class="card mt-3">
        <div class="card-header">
            <h2>
                <?php echo xlt('Token Tools'); ?>
            </h2>
            <a href="<?php echo attr($listAction); ?>" class="btn btn-sm btn-secondary" onclick="top.restoreSession()">&lt; <?php echo xlt("Back to Client List"); ?></a>
        </div>
        <div class="card-body">
            <?php if (!empty($message)) : ?>
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-success">
                        <?php echo text($message); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-12">
        <?php
    }

    private function renderTokenToolsFooter()
    {
        ?>
        </div>
                </div>
            </div>
        </div>
        <?php
        $this->renderFooter();
    }
    private function enableAuthorizationFlowSkipAction(string $clientId, Request $request)
    {
         $client = $this->clientRepo->getClientEntity($clientId);
        if ($client === false) {
            $this->notFoundAction($request);
            return;
        }
        $message = xl('Enabled Authorization Flow Skip') . " " . $client->getName();
        $this->handleAuthorizationFlowSkipAction($client, true, $message);
        exit;
    }
    private function disableAuthorizationFlowSkipAction(string $clientId, Request $request)
    {
         $client = $this->clientRepo->getClientEntity($clientId);
        if ($client === false) {
            $this->notFoundAction($request);
            return;
        }
        $message = xl('Disabled Authorization Flow Skip') . " " . $client->getName();
        $this->handleAuthorizationFlowSkipAction($client, false, $message);
        exit;
    }
    private function handleAuthorizationFlowSkipAction(ClientEntity $client, bool $skipFlow, string $successMessage)
    {
        $client->setSkipEHRLaunchAuthorizationFlow($skipFlow);
        try {
            $this->clientRepo->saveSkipEHRLaunchFlow($client, $skipFlow);
            $url = $this->getActionUrl(['edit', $client->getIdentifier()], ["queryParams" => ['message' => $successMessage]]);
            header("Location: " . $url);
        } catch (\Exception $ex) {
            $this->logger->error(
                "Failed to save client",
                [
                    "exception" => $ex->getMessage(), "trace" => $ex->getTraceAsString()
                    , 'client' => $client->getIdentifier()
                ]
            );

            $message = xl('Client failed to save. Check system logs');
            $url = $this->getActionUrl(['edit', $client->getIdentifier()], ["queryParams" => ['message' => $message]]);
            header("Location: " . $url);
        }
    }
}
