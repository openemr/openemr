<?php

/**
 * admin-client.php  Main entry point for the OpenEMR OAUTH2 / SMART client registration management page
 * Provides functionality to see the list of registered client's and the ability to enable / disable
 * client registrations.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2020 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// need to make sure our autoloader is present.
/**
 * @var \OpenEMR\Core\OEGlobalsBag $oeGlobals
 */
$oeGlobals = require_once("../globals.php");

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Csrf\CsrfInvalidException;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\FHIR\SMART\ClientAdminController;
use OpenEMR\Common\Logging\SystemLogger;
use Symfony\Component\HttpFoundation\Request;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\HttpSessionFactory;


try {
    // TODO: @adunsulag at some point we'd like to have a CoreApplication like the ApiApplication that will dispatch controllers, refactor this once we have that
    $request = HttpRestRequest::createFromGlobals();
    $sessionFactory = new HttpSessionFactory($request, $oeGlobals->getString('web_root'), HttpSessionFactory::SESSION_TYPE_CORE);
    $sessionFactory->setUseExistingSessionBridge(true);
    $session = $sessionFactory->createSession();
    $request = Request::createFromGlobals();
    $router = new ClientAdminController(
        $oeGlobals,
        $session,
        new ClientRepository(),
        'admin-client.php'
    );
    $response = $router->dispatch($request);
    $response->send();
} catch (CsrfInvalidException) {
    CsrfUtils::csrfNotVerified();
} catch (AccessDeniedException $exception) {
    (new SystemLogger())->critical($exception->getMessage(), ["trace" => $exception->getTraceAsString()]);
    die();
} catch (Exception $exception) {
    (new SystemLogger())->error($exception->getMessage(), ["trace" => $exception->getTraceAsString()]);
    die("Unknown system error occurred");
}
