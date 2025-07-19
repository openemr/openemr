<?php

/**
 * Rest Dispatch
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
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

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\RestControllers\ApiApplication;
// create the Request object
try {
    $request = HttpRestRequest::createFromGlobals();
    $apiApplication = new ApiApplication();
    $apiApplication->run($request);
} catch (\Throwable $e) {
    // should never reach here, but if we do, we can log the error and return a generic error response
    error_log($e->getMessage());
    // should never get here, but if we do, we can return a generic error response
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_send_status(500);
    }
    die(json_encode([
        'error' => 'An error occurred while processing the request.',
        'message' => $e->getMessage(),
    ]));
}
