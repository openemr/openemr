<?php

/**
 * Authorization Server Member
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\BC\FallbackRouter;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\ApiApplication;

require_once "../vendor/autoload.php";

// TODO: @adunsulag at some point we can have the .htaccess file just hit
// everything in the dispatch.php file and then we can remove this file
// create the Request object
try {
    $request = HttpRestRequest::createFromGlobals();
    FallbackRouter::handleRoutingTestIfRequested($request->getRequestUri(), 'oauth2');
    $apiApplication = new ApiApplication();
    $apiApplication->run($request);
} catch (\Throwable $e) {
    // TODO: handle exceptions properly
    error_log($e->getMessage());
    // should never get here, but if we do, we can return a generic error response
    die("An error occurred while processing the request. Please check the logs for more details.");
}
