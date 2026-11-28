<?php

/**
 * API index page for receiving requests from the OpenEMR clinician requests.
 *
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// since we are working inside the portal we have to use the portal session verification logic here...
require_once "../../../../globals.php";

use Comlink\OpenEMR\Modules\TeleHealthModule\Bootstrap;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

$kernel = OEGlobalsBag::getInstance()->getKernel();
$bootstrap = new Bootstrap($kernel->getEventDispatcher(), $kernel);
$roomController = $bootstrap->getTeleconferenceRoomController(false);

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$request = CurrentRequest::get();
// Form body values take precedence over query values, matching the request_order the callers rely on.
$queryVars = array_merge($request->query->all(), $request->request->all());
$action = $queryVars['action'] ?? '';
$queryVars['pid'] = $session->get('pid');
$queryVars['authUser'] = $session->get('authUser');
$queryVars['csrf_token'] = $request->headers->get('apicsrftoken') ?? '';
$roomController->dispatch($action, $queryVars);
exit;
