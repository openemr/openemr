<?php

/**
 * Authorization Server Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\RestControllers\AuthorizationController;

require_once "../vendor/autoload.php";

// TODO: @adunsulag at some point we can have the .htaccess file just hit
// everything in the dispatch.php file and then we can remove this file
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\ApiApplication;
// create the Request object
try {
    $request = HttpRestRequest::createFromGlobals();
    $apiApplication = new ApiApplication();
    $apiApplication->run($request);
} catch (\Throwable $e) {
    // TODO: handle exceptions properly
    error_log($e->getMessage());
    // should never get here, but if we do, we can return a generic error response
    die("An error occurred while processing the request. Please check the logs for more details.");
}
