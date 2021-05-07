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
use OpenEMR\Common\Auth\OpenIDConnect\Entities\ClientEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Csrf\CsrfInvalidException;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use Psr\Log\LoggerInterface;

class ClientAdminController
{
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

        // route /list
        if ($parts[0] == 'list') {
            $this->listAction($request);
        } else if ($parts[0] == 'edit' && count($parts) > 1) {
            $clientId = $parts[1];

            if (count($parts) < 3) { // route /edit/:clientId
                return $this->editAction($clientId, $request);
            } else if ($parts[2] == 'enable') { // route /edit/:clientId/enable
                return $this->enableAction($clientId, $request);
            } else if ($parts[2] == 'disable') { // route /edit/:clientId/disable
                return $this->disableAction($clientId, $request);
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

        $this->renderHeader();
        $this->renderEdit($client, $request);
        $this->renderFooter();
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
                            <a class="btn btn-primary btn-sm" href="<?php echo attr($this->getActionUrl(['edit', $client->getIdentifier()])); ?>" onclick="top.restoreSession()">Edit</a>
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
        $isEnabled = $client->isEnabled();
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
            'role' => [
                'type' => 'text'
                ,'label' => xl("Role")
                ,'value' => $client->getClientRole()
            ],
            'redirectUri' => [
                'type' => 'text'
                ,'label' => xl("Redirect URI")
                ,'value' => $client->getRedirectUri()
            ],
            'launchUri' => [
                'type' => 'text'
                ,'label' => xl("Launch URI")
                ,'value' => $client->getLaunchUri()
            ],
        ];

        ?>
        <a href="<?php echo attr($listAction); ?>" class="btn btn-sm btn-secondary" onclick="top.restoreSession()">&lt; <?php echo xlt("Back to Client List"); ?></a>

        <div class="card mt-3">
            <div class="card-header">
                <h2>
                    <?php echo xlt('Edit'); ?> <em><?php echo text($client->getName()); ?></em>
                    <div class="float-right">
                        <?php if ($isEnabled) : ?>
                        <a href="<?php echo attr($disableClientLink); ?>" class="btn btn-sm btn-primary" onclick="top.restoreSession()"><?php echo xlt('Disable Client'); ?></a>
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
        ?>
        <div class="form-group">
            <label for="<?php echo attr($key); ?>"><?php echo text($setting['label']); ?></label>
            <textarea id="<?php echo attr($key); ?>" name="<?php echo attr($key) ?>" readonly
                      class="form-control" rows="10" disabled><?php echo attr($setting['value']); ?></textarea>
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
                        <h2><?php echo text($title); ?></h2>
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
}
