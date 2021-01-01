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
require_once("../globals.php");

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Csrf\CsrfInvalidException;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\FHIR\SMART\ClientAdminController;
use OpenEMR\Common\Logging\SystemLogger;

$router = new ClientAdminController(new ClientRepository(), SystemLogger::instance(), 'admin-client.php');
try {
    $router->dispatch(($_REQUEST['action'] ?? null), $_REQUEST);
} catch (CsrfInvalidException $exception) {
    CsrfUtils::csrfNotVerified();
} catch (AccessDeniedException $exception) {
    SystemLogger::instance()->critical($exception->getMessage(), ["trace" => $exception->getTraceAsString()]);
    die();
} catch (Exception $exception) {
    SystemLogger::instance()->error($exception->getMessage(), ["trace" => $exception->getTraceAsString()]);
    die("Unknown system error occurred");
}
