<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../../globals.php");

use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;

$serviceType = null;
$action = $_REQUEST['_ACTION_COMMAND'] ?? null;
$route = explode('/', is_scalar($action) ? (string) $action : '');
if (count($route) === 2) {
    $serviceType = $route[0];
    $action = $route[1] ?: $action;
}
$serviceType = $serviceType ?: ($_REQUEST['type'] ?? null);
$serviceType = is_scalar($serviceType) ? (string) $serviceType : '';
// Construct the service (which runs the module ACL gate) then explicitly route
// the request. Routing is no longer a side effect of construction.
$service = AppDispatch::getApiService($serviceType);
if ($service instanceof AppDispatch) {
    $service->dispatch();
}
