<?php

/**
 * Patient portal front controller for portal/patient: the profile, portal documents and the
 * staff review pages, and their JSON API.
 *
 * _machine_config.php sorts out the session first: a portal patient, a visitor registering for
 * the portal, or a staff user with the patientportal/portal ACL. PortalFrontController then
 * checks the CSRF token on writes, decides who is calling, and hands the request to
 * OpenEMR\Portal\Routing\PortalRouter, which applies each route's access rule; the controllers
 * in OpenEMR\Controllers\Portal\Patient serve it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Controllers\Portal\Patient\LazyPageRenderer;
use OpenEMR\Controllers\Portal\Patient\PortalFrontController;
use OpenEMR\Controllers\Portal\Patient\PortalPatientApp;
use OpenEMR\Controllers\Portal\Patient\PortalValues;
use OpenEMR\Controllers\Portal\Patient\TwigPageRenderer;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\VersionService;

require_once(__DIR__ . "/../../vendor/autoload.php");

// Chooses the portal or the staff session, checks the staff ACL, and loads globals.
require_once(__DIR__ . "/_machine_config.php");

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$globalsBag = OEGlobalsBag::getInstance();
// The base URL the pages use, with trailing slash.
$portalRootUrl = ($globalsBag->getBoolean('portal_onsite_two_basepath')
    ? $globalsBag->getString('qualified_site_addr')
    : $globalsBag->getString('web_root')) . '/portal/patient/';
// Built on the first page render: a JSON API call never needs it.
$pages = new LazyPageRenderer(static fn(): TwigPageRenderer => new TwigPageRenderer(ServiceContainer::getTwig(), [
    'ROOT_URL' => $portalRootUrl,
    'web_root' => $globalsBag->getString('web_root'),
    'assets_static_relative' => $globalsBag->getString('assets_static_relative'),
    'v_js_includes' => $globalsBag->getString('v_js_includes'),
    'is_portal_session' => PortalValues::isFilled($session->get('patient_portal_onsite_two')),
    'software_version' => (new VersionService())->getSoftwareVersion(),
    'current_year' => date('Y'),
]));
$app = new PortalPatientApp($pages);

(new PortalFrontController($pages, ServiceContainer::getLogger(), $app->handle(...)))
    ->handle(CurrentRequest::get(), $session, $globalsBag, $globalsBag->getString('web_root') . '/portal/patient/')
    ->send();
