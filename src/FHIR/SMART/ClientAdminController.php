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

use Lcobucci\JWT\Parser;
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
use OpenEMR\Services\PatientService;
use OpenEMR\Services\TrustedUserService;
use OpenEMR\Services\UserService;
use Psr\Log\LoggerInterface;

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

    /**
     * ClientAdminController constructor.
     * @param ClientRepository $repo The repository object that let's us retrieve OAUTH2 EntityClient objects
     * @param $actionURL The URL that we will send requests back to
     */
    public function __construct(ClientRepository $repo, LoggerInterface $logger, $actionURL)
    {
        $this->clientRepo = $repo;
        $this->logger = $logger;
        $this->actionURL = $actionURL;
    }

    /**
     * Validates the given request for CSRF attacks as well as ACL permissions.
     * @param $request
     * @throws CsrfInvalidException If the CSRF token is required but invalid for the given action request
     * @throws AccessDeniedException If the user does not have permission to make the requested action
     */
    public function checkSecurity($request)
    {
        if ($this->shouldCheckCSRFTokenForRequest($request)) {
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
    public function dispatch($action, $request)
    {
        $request = $this->normalizeRequest($request);
        $this->checkSecurity($request);

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
        $this->renderHeader();
        $this->renderList($request);
        $this->renderFooter();
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
            // TODO: do we need to optimize this query?
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

        $this->renderHeader();
        $this->renderEdit($client, $request);
        $this->renderFooter();
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
        $this->renderHeader();
        ?><h1>404 <?php echo xlt("Page not found"); ?></h1><?php
        $this->renderFooter();
        // could return a 404 page here, but for now we will just skip it.
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
     * Renders a list of the system oauth2 clients to the screen.
     * @param $request
     */
    private function renderList($request)
    {
        $clients = $this->clientRepo->listClientEntities();
        ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>
                        <?php echo xlt('Edit'); ?>
                    </th>
                    <th><?php echo xlt('Client Name / Client ID'); ?></th>
                    <th><?php echo xlt('Enabled'); ?></th>
                    <th><?php echo xlt('Client Type'); ?></th>
                    <th><?php echo xlt('Scopes Requested'); ?></th>

                </tr>
                </thead>
                <tbody>
                <?php if (count($clients) <= 0) : ?>
                    <tr>
                        <td colspan="7"><?php echo xlt('There are no clients registered in the system'); ?></td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($clients as $client) : ?>
                    <tr>
                        <td>
                            <a class="btn btn-primary btn-sm" href="<?php echo attr($this->getActionUrl(['edit', $client->getIdentifier()])); ?>" onclick="top.restoreSession()"><?php echo xlt("Edit"); ?></a>
                        </td>
                        <td>
                            <?php echo text($client->getName()); ?>
                            <br />
                            <em><?php echo text($client->getIdentifier()); ?></em>
                        </td>
                        <td><?php echo $client->isEnabled() ? xlt("Enabled") : xlt("Disabled"); ?></td>
                        <td><?php echo $client->isConfidential() ? xlt("Confidential") : xlt("Public"); ?></td>
                        <td>
                            <?php $this->renderScopeList($client, true); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Displays the passed in client entity as a form to the screen.
     * @param ClientEntity $client
     * @param $request
     */
    private function renderEdit(ClientEntity $client, $request)
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

        ?>
        <a href="<?php echo attr($listAction); ?>" class="btn btn-sm btn-secondary" onclick="top.restoreSession()">&lt; <?php echo xlt("Back to Client List"); ?></a>

        <div class="card mt-3">
            <div class="card-header">
                <h2>
                    <?php echo xlt('Edit'); ?> <em><?php echo text($client->getName()); ?></em>
                    <div class="float-right">
                        <?php if ($allowSkipAuthSetting) : ?>
                            <?php if ($skipAuthorizationFlow) : ?>
                            <a href="<?php echo attr($disableSkipAuthorizationFlowLink); ?>" class="btn btn-sm btn-primary" onclick="top.restoreSession()"><?php echo xlt('Enable EHR Launch Authorization Flow'); ?></a>
                            <?php else : ?>
                            <a href="<?php echo attr($enableSkipAuthorizationFlowLink); ?>" class="btn btn-sm btn-danger" onclick="top.restoreSession()"><?php echo xlt('Disable EHR Launch Authorization Flow'); ?></a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($isEnabled) : ?>
                        <a href="<?php echo attr($disableClientLink); ?>" class="btn btn-sm btn-danger" onclick="top.restoreSession()"><?php echo xlt('Disable Client'); ?></a>
                        <?php else : ?>
                        <a href="<?php echo attr($enableClientLink); ?>" class="btn btn-sm btn-primary" onclick="top.restoreSession()"><?php echo xlt('Enable Client'); ?></a>
                        <?php endif; ?>
                    </div>
                </h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <?php if (!empty($request['message'])) : ?>
                        <div class="alert alert-info">
                            <?php echo text($request['message']); ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!$isEnabled) : ?>
                            <div class="alert alert-danger">
                                <?php echo xlt("This client is currently disabled"); ?>
                            </div>
                        <?php endif; ?>
                        <form>
                            <?php foreach ($formValues as $key => $setting) {
                                switch ($setting['type']) {
                                    case 'text':
                                        $this->renderTextInput($key, $setting);
                                        break;
                                    case 'textarea':
                                        $this->renderTextarea($key, $setting);
                                        break;
                                    case 'checkbox':
                                        $this->renderCheckbox($key, $setting);
                                        break;
                                }
                            } ?>
                        </form>
                    </div>
                    <div class="col-6">
                            <label><?php echo xlt("Scopes"); ?></label>
                            <?php $this->renderScopeList($client); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 ">
                        <h3 class="text-center"><?php echo xlt("Authenticated API Users"); ?></h3>
                        <hr class="w-50" />
                        <?php if (empty($client->getTrustedUsers())) : ?>
                            <div class="row">
                                <div class="col">
                                    <div class="alert alert-info text-center">
                                        <?php echo xlt("No authorized users found for this client"); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($client->getTrustedUsers())) : ?>
                            <?php foreach ($client->getTrustedUsers() as $trustedUser) : ?>
                                <div class="card m-3">
                                    <div class="card-header">
                                        <h4 class="text-center"><?php echo text($trustedUser['display_name']); ?>
                                            <a href="<?php echo attr($this->getActionUrl(['edit', $client->getIdentifier()
                                                , self::REVOKE_TRUSTED_USER, $trustedUser['user_id']])); ?>"
                                               class="btn btn-sm btn-primary float-right" onclick="top.restoreSession()"><?php
                                                echo xlt('Revoke User');
                                                ?></a></h4>
                                    </div>
                                    <div class="card-body p-5">
                                        <div class="row font-weight-bold bg-secondary rounded text-dark">
                                            <div class="col-1">
                                                <?php echo xlt("Type"); ?>
                                            </div>
                                            <div class="col-4">
                                                <?php echo xlt("UUID"); ?>
                                            </div>
                                            <div class="col-3">
                                                <?php echo xlt("Name/Username"); ?>
                                            </div>
                                            <div class="col-1">
                                                <?php echo xlt("Date"); ?>
                                            </div>
                                            <div class="col-1">
                                                <?php echo xlt("Persist Login"); ?>
                                            </div>
                                            <div class="col-2">
                                                <?php echo xlt("Grant Type"); ?>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-1">
                                                <?php echo text($trustedUser['user_type']); ?>
                                            </div>
                                            <div class="col-4">
                                                <?php echo text($trustedUser['user_id']); ?>
                                            </div>
                                            <div class="col-3">
                                                <?php echo text($trustedUser['display_name']); ?>
                                            </div>
                                            <div class="col-1">
                                                <?php echo text($trustedUser['time']); ?>
                                            </div>
                                            <div class="col-1">
                                                <?php echo text($trustedUser['persist_login']); ?>
                                            </div>
                                            <div class="col-2">
                                                <?php echo text($trustedUser['grant_type']); ?>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col">
                                                <h4><?php echo xlt("Access Tokens") ?></h4>
                                                <hr class="w-100 mt-3 mb-3"/>
                                                <div class="row bg-primary rounded text-light mb-2 pt-3 pb-3">
                                                    <div class="col-3">
                                                        <?php echo xlt("Token"); ?>
                                                    </div>
                                                    <div class="col-2">
                                                        <?php echo xlt("Expiry"); ?>
                                                    </div>
                                                    <div class="col-4">
                                                        <?php echo xlt("Scopes"); ?>
                                                    </div>
                                                    <div class="col-3">
                                                        <?php echo xlt("Action"); ?>
                                                    </div>
                                                </div>
                                                <?php if (empty($trustedUser['accessTokens'])) : ?>
                                                <div class="row">
                                                    <div class="col">
                                                        <div class="alert alert-info text-center">
                                                            <?php echo xlt("No active access tokens found"); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!empty($trustedUser['accessTokens'])) : ?>
                                                    <?php foreach ($trustedUser['accessTokens'] as $token) : ?>
                                                        <div class="row">
                                                            <div class="col-3">
                                                                <?php echo text($token['token']); ?>
                                                            </div>
                                                            <div class="col-2">
                                                                <?php echo text($token['expiry']); ?>
                                                            </div>
                                                            <div class="col-4">
                                                                <?php $this->renderScopeListArray($token['scope']); ?>
                                                            </div>
                                                            <div class="col-3">
                                                                <a href="<?php echo attr($this->getActionUrl(['edit', $client->getIdentifier(), self::REVOKE_ACCESS_TOKEN, $token['id']])); ?>"
                                                                   class="btn btn-sm btn-primary" onclick="top.restoreSession()"><?php echo xlt('Revoke Token'); ?></a>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col">
                                                <h4><?php echo xlt("Refresh Tokens") ?></h4>
                                                <hr class="w-100 mt-3 mb-3"/>
                                                <div class="row bg-primary rounded text-light mb-2 pt-3 pb-3">
                                                    <div class="col-7">
                                                        <?php echo xlt("Token"); ?>
                                                    </div>
                                                    <div class="col-2">
                                                        <?php echo xlt("Expiry"); ?>
                                                    </div>
                                                    <div class="col-3">
                                                        <?php echo xlt("Action"); ?>
                                                    </div>
                                                </div>
                                                <?php if (empty($trustedUser['refreshTokens'])) : ?>
                                                    <div class="row">
                                                        <div class="col">
                                                            <div class="alert alert-info text-center">
                                                                <?php echo xlt("No active refresh tokens found"); ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($trustedUser['refreshTokens'])) : ?>
                                                    <?php foreach ($trustedUser['refreshTokens'] as $token) : ?>
                                                        <div class="row">
                                                            <div class="col-7">
                                                                <?php echo text($token['token']); ?>
                                                            </div>
                                                            <div class="col-2">
                                                                <?php echo text($token['expiry']); ?>
                                                            </div>
                                                            <div class="col-3">
                                                                <a href="<?php echo attr($this->getActionUrl(['edit', $client->getIdentifier(), self::REVOKE_REFRESH_TOKEN, $token['id']])); ?>"
                                                                   class="btn btn-sm btn-primary" onclick="top.restoreSession()"><?php echo xlt('Revoke Token'); ?></a>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- List client information below -->
        </div>
        <?php
    }

    private function renderCheckbox($key, $setting)
    {
        ?>
        <div class="form-check form-check-inline">
            <input type="checkbox" id="<?php echo attr($key); ?>" name="<?php echo attr($key) ?>"
                   class="form-check-input" value="<?php echo attr($setting['value'] ?? ''); ?>" readonly
                <?php echo ($setting['checked'] ? "checked='checked'" : ""); ?> />
            <label for="<?php echo attr($key); ?>" class="form-check-label">
                <?php echo text($setting['label']); ?>
            </label>
        </div>
        <?php
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

    private function renderTextInput($key, $setting)
    {
        ?>
        <div class="form-group">
            <label for="<?php echo attr($key); ?>"><?php echo text($setting['label']); ?></label>
            <input type="text" id="<?php echo attr($key); ?>" name="<?php echo attr($key) ?>"
                   class="form-control" value="<?php echo attr($setting['value']); ?>" readonly disabled />
        </div>
        <?php
    }

    /**
     * Prepares the default request array passed into the class, filling in any missing parameters
     * the class needs in the request.
     * @param $request
     * @return array
     */
    private function normalizeRequest($request)
    {
        // if the request is empty with us on a list page we want to populate it
        // anything else we do to the request should be put there
        if (empty($request)) {
            return [
                'action' => '/list'
            ];
        }
        return $request;
    }

    /**
     * Checks to see if the request needs a CSRF check.  Which everything but
     * the list action (default page) does.
     * @param $request
     * @return bool True if CSRF is required, false otherwise.
     */
    private function shouldCheckCSRFTokenForRequest($request)
    {
        // we don't check CSRF for a basic get and list action
        // anything else requires the CSRF token
        if ($request['action'] === '/list') {
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
    private function revokeTrustedUserAction($clientId, array $request)
    {
        $action = $request['action'] ?? '';
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

    private function revokeRefreshToken($clientId, array $request)
    {
        $action = $request['action'] ?? '';
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

    private function toolsRevokeAccessTokenAction(array $request)
    {
        $clientId = $request['clientId'] ?? null;
        $token = $request['token'] ?? null;
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

    private function toolsRevokeRefreshToken(array $request)
    {
        $clientId = $request['clientId'] ?? null;
        $token = $request['token'] ?? null;
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

    private function revokeAccessToken($clientId, array $request)
    {
        $action = $request['action'] ?? '';
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

    private function tokenToolsAction(array $request)
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

    private function parseTokenAction(array $request)
    {
        $parts = null;
        $actionUrl = $this->getActionUrl([self::TOKEN_TOOLS_ACTION, self::PARSE_TOKEN_ACTION]);
        $textSetting = [
                'value' => $request['token'] ?? null
                ,'label' => 'Token to parse'
                ,'type' => 'textarea'
                ,'enabled' => true
        ];
        if (!empty($request['token'])) {
            $parts = $this->parseTokenIntoParts($request['token']);
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

    private function renderTokenToolsHeader(array $request)
    {
        $listAction = $this->getActionUrl(['list']);
        $message = $request['message'] ?? null;

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
    private function enableAuthorizationFlowSkipAction(string $clientId, array $request)
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
    private function disableAuthorizationFlowSkipAction(string $clientId, array $request)
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
