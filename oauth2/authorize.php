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
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\RequestTerminator;
use OpenEMR\RestControllers\ApiApplication;
use Symfony\Component\HttpFoundation\Response;

require_once "../vendor/autoload.php";

// TODO: @adunsulag at some point we can have the .htaccess file just hit
// everything in the dispatch.php file and then we can remove this file
// create the Request object
try {
    $request = HttpRestRequest::createFromGlobals();
    // See the note in apis/dispatch.php: globals.php loads later from a scope that
    // cannot see $request, so publish it here or it builds a second, bare instance.
    CurrentRequest::set($request);
    FallbackRouter::handleRoutingTestIfRequested($request->getRequestUri(), 'oauth2');
    $apiApplication = new ApiApplication();
    $apiApplication->run($request);
} catch (\Throwable $e) {
    // TODO: handle exceptions properly
    error_log($e->getMessage());
    // Only answer when nothing has gone out yet -- see the matching note in
    // apis/dispatch.php. After headers are sent this text would be appended to a
    // response the client is already parsing, corrupting a payload that was correct.
    if (!headers_sent()) {
        (new RequestTerminator())->error(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            'An error occurred while processing the request. Please check the logs for more details.'
        );
    }
}
