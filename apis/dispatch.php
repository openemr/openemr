<?php

/**
 * Rest Dispatch
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019-2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// below brings in autoloader
require_once "../vendor/autoload.php";

use OpenEMR\BC\FallbackRouter;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\ApiApplication;
use Symfony\Component\HttpFoundation\Response;

// create the Request object
try {
    $request = HttpRestRequest::createFromGlobals();
    // Publish before anything downstream can reach for a request. globals.php is
    // included much later (SiteSetupListener::loadApplicationGlobals) from a scope
    // that cannot see $request, so without this it would build a second, bare
    // instance missing the api type, scopes, and session set during the run.
    CurrentRequest::set($request);
    FallbackRouter::handleRoutingTestIfRequested($request->getRequestUri(), 'apis');
    $apiApplication = new ApiApplication();
    $apiApplication->run($request);
} catch (\Throwable $e) {
    // should never reach here, but if we do, we can log the error and return a generic error response
    // we manually handle it as we don't know if something failed in the symfony component or in our code
    error_log($e->getMessage());
    error_log($e->getTraceAsString());
    // Only answer when nothing has gone out yet. Once headers are sent the response
    // body is already being read by the client, so emitting an error document here
    // appends a second JSON object to the first and every client fails to parse
    // what was otherwise a complete, correct payload. A post-send failure -- a
    // kernel.terminate listener, a broken output filter -- is a log-only event.
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(Response::HTTP_INTERNAL_SERVER_ERROR);
        // The message stays out of the body. Exception text here is whatever
        // failed deepest in the stack -- openemr#13905 put a SQLSTATE naming a
        // missing column into this field, readable by any anonymous caller of
        // /apis/default/fhir/metadata. It is already in the error log above,
        // where it is useful and not disclosed.
        die(json_encode([
            'error' => 'An error occurred while processing the request.',
        ]));
    }
}
