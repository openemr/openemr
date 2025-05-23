<?php

/**
 * portal_patient_report.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady@sparmy.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2016-2024 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2024 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Will start the (patient) portal OpenEMR session/cookie.
require_once(dirname(__FILE__) . "/../../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

//landing page definition -- where to go if something goes wrong
$landingpage = "../index.php?site=" . urlencode($_SESSION['site_id']);
//

// kick out if patient not authenticated
if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
    $user = $_SESSION['sessionUser'];
} else {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w');
    exit;
}

$ignoreAuth_onsite_portal = true;
global $ignoreAuth_onsite_portal;

require_once('../../interface/globals.php');
require_once("$srcdir/lists.inc.php");
require_once("$srcdir/forms.inc.php");
require_once("$srcdir/patient.inc.php");

use OpenEMR\Core\Header;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Controllers\Portal\PortalPatientReportController;
use OpenEMR\Events\PatientReport\PatientReportFilterEvent;
use Twig\Error\SyntaxError;

// get various authorization levels
$auth_notes_a = true; //AclMain::aclCheckCore('encounters', 'notes_a');
$auth_notes = true; //AclMain::aclCheckCore('encounters', 'notes');
$auth_coding_a = true; //AclMain::aclCheckCore('encounters', 'coding_a');
$auth_coding = true; //AclMain::aclCheckCore('encounters', 'coding');
$auth_relaxed = true; //AclMain::aclCheckCore('encounters', 'relaxed');
$auth_med = true; //AclMain::aclCheckCore('patients'  , 'med');
$auth_demo = true; //AclMain::aclCheckCore('patients'  , 'demo');

$ignoreAuth_onsite_portal = true;

$portalPatientReportController = new PortalPatientReportController();
$twig = (new TwigContainer(null, $GLOBALS['kernel']))->getTwig();

$issues = [];
$data = [];
try {
    $data['phone_country_code'] = $GLOBALS['phone_country_code'] ?? '';
    $data['returnurl'] = (!empty($returnurl)) ? "$rootdir/patient_file/encounter/$returnurl" : '';
    $data['issues'] = $portalPatientReportController->getIssues($ISSUE_TYPES, $pid);
    $data['encounters'] = $portalPatientReportController->getEncounters($pid);
    $data['procedureOrders'] = $portalPatientReportController->getProcedureOrders($pid);
    $data['documents'] = $portalPatientReportController->getDocuments($pid);
    $data['phimail_enable'] = $GLOBALS['phimail_enable'] ?? false;
    $data['phimail_ccr_enable'] = $GLOBALS['phimail_ccr_enable'] ?? false;
    $data['phimail_ccd_enable'] = $GLOBALS['phimail_ccd_enable'] ?? false;
    $data['sections'] = [
        'demographics' => [
            'selected' => true
            ,'label' => xl('Demographics')
        ]
        ,'history' => [
            'selected' => false
            ,'label' => xl('History')
        ]
        ,'insurance' => [
            'selected' => false
            ,'label' => xl('Insurance')
        ]
        ,'billing' => [
            'selected' => $GLOBALS['simplified_demographics'] ? false : true
            ,'label' => xl('Billing')
        ]
        ,'allergies' => [
            'selected' => false
            ,'label' => xl('Allergies')
        ]
        ,'medications' => [
            'selected' => false
            ,'label' => xl('Medications')
        ]
        ,'immunizations' => [
            'selected' => false
            ,'label' => xl('Immunizations')
        ]
        ,'medical_problems' => [
            'selected' => false
            ,'label' => xl('Medical Problems')
        ]
        ,'notes' => [
            'selected' => false
            ,'label' => xl('Patient Notes')
        ]
        ,'transactions' => [
            'selected' => false
            ,'label' => xl('Transactions')
        ]
        ,'batchcom' => [
            'selected' => false
            ,'label' => xl('Communications')
        ]
    ];
    // what sections display can be controlled by the following two arrays
    $data['section_one'] = [
        'demographics', 'history', 'insurance', 'billing'
    ];
    $data['section_two'] = [
        'allergies', 'medications', 'immunizations', 'medical_problems', 'notes', 'transactions', 'batchcom'
    ];
    $event = new PatientReportFilterEvent();
    $event->populateData($data);
    $updatedEvent = $GLOBALS['kernel']->getEventDispatcher()->dispatch($event, PatientReportFilterEvent::FILTER_PORTAL_TWIG_DATA);
    $updatedData  = $event->getDataAsArray();
    echo $twig->render("portal/portal_patient_report.html.twig", $updatedData);
} catch (SyntaxError $exception) {
    (new SystemLogger())->error($exception->getMessage(), ['trace' => $exception->getTraceAsString(), 'file' => $exception->getFile()]);
    echo $twig->render("error/general_http_error.html.twig", []);
} catch (\Exception $exception) {
    (new SystemLogger())->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
    echo $twig->render("error/general_http_error.html.twig", []);
}
die();
