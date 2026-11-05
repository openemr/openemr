<?php

/**
 * Patient portal front controller for portal/patient: the profile, portal documents and the
 * staff review pages, and their JSON API.
 *
 * _machine_config.php sorts out the session first: a portal patient, a visitor registering for
 * the portal, or a staff user with the patientportal/portal ACL. Writes must carry the session's
 * CSRF token. The request is then routed by OpenEMR\Portal\Routing\PortalRouter, which applies
 * each route's access rule, and served by the controllers in
 * OpenEMR\Controllers\Portal\Patient.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Csrf\PortalApiCsrfGuard;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\PortalSessionPidGuard;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Controllers\Portal\Patient\PortalErrorPages;
use OpenEMR\Controllers\Portal\Patient\PortalJson;
use OpenEMR\Controllers\Portal\Patient\PortalPatientApp;
use OpenEMR\Controllers\Portal\Patient\TwigPageRenderer;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Portal\Routing\PortalPrincipal;
use OpenEMR\Portal\Routing\PortalRequestPath;
use OpenEMR\Services\VersionService;
use Symfony\Component\HttpFoundation\Response;

require_once(__DIR__ . "/../../vendor/autoload.php");

// Chooses the portal or the staff session, checks the staff ACL, and loads globals.
require_once(__DIR__ . "/_machine_config.php");

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$globalsBag = OEGlobalsBag::getInstance();
// The base URL the pages use, with trailing slash.
$portalRootUrl = ($globalsBag->getBoolean('portal_onsite_two_basepath')
    ? $globalsBag->getString('qualified_site_addr')
    : $globalsBag->getString('web_root')) . '/portal/patient/';
$request = CurrentRequest::get();
$pages = new TwigPageRenderer(ServiceContainer::getTwig(), [
    'ROOT_URL' => $portalRootUrl,
    'web_root' => $globalsBag->getString('web_root'),
    'assets_static_relative' => $globalsBag->getString('assets_static_relative'),
    'v_js_includes' => $globalsBag->getString('v_js_includes'),
    'is_portal_session' => $session->has('patient_portal_onsite_two') && (bool) $session->get('patient_portal_onsite_two'),
    'software_version' => (new VersionService())->getSoftwareVersion(),
    'current_year' => date('Y'),
]);
$path = PortalRequestPath::of($request, $globalsBag->getString('web_root') . '/portal/patient/');

try {
    // Requests that can change data must carry the session's CSRF token (sent by scripts/portal-api.js).
    PortalApiCsrfGuard::assertValid(
        $request->getRealMethod(),
        $request->headers->get(PortalApiCsrfGuard::HEADER),
        $session,
    );

    $globalsBag->set('bootstrap_register', !empty($session->get('register', null)));
    if ($session->has('pid') && $session->has('patient_portal_onsite_two')) {
        // Bootstrap the session pid so downstream code can bind to it.
        $globalsBag->set('bootstrap_pid', $session->get('pid'));
        $recordId = QueryUtils::fetchSingleValue('SELECT `id` FROM `patient_data` WHERE `pid` = ?', 'id', [$globalsBag->get('bootstrap_pid')]);
        $globalsBag->set('bootstrap_uri_id', $recordId);
        // Scan every request source for any pid-family key that disagrees with the session pid.
        PortalSessionPidGuard::assertRequestKeysMatchSession($_POST, $_GET, $_REQUEST);
    }

    $bootstrapPid = filter_var($globalsBag->get('bootstrap_pid'), FILTER_VALIDATE_INT);
    if (is_int($bootstrapPid) && $bootstrapPid > 0) {
        $recordId = filter_var($globalsBag->get('bootstrap_uri_id'), FILTER_VALIDATE_INT);
        $caller = PortalPrincipal::portalPatient($bootstrapPid, is_int($recordId) ? $recordId : null);
    } elseif ($globalsBag->getBoolean('bootstrap_register')) {
        $caller = PortalPrincipal::registering();
    } else {
        $caller = PortalPrincipal::staff();
    }

    $response = (new PortalPatientApp($pages))->handle($request, $path, $caller);
} catch (\Throwable $ex) {
    // An API call gets the JSON error; a page gets the error page. Neither shows the failure.
    ServiceContainer::getLogger()->error('Portal request failed', ['exception' => $ex]);
    $publicMessage = xl('An unexpected error occurred');
    if (str_starts_with($path, 'api/')) {
        $response = PortalJson::error($publicMessage);
    } else {
        try {
            $response = (new PortalErrorPages($pages))->failed($publicMessage);
        } catch (\Throwable $ex2) {
            ServiceContainer::getLogger()->error('Portal error template failed', ['exception' => $ex2]);
            $response = new Response(
                "<style>* { font-family: verdana, arial, helvetica, sans-serif; }</style>\n<h1>Fatal Error:</h1>\n<h3>" . text($publicMessage) . "</h3>\n",
            );
        }
    }
}

$response->send();
