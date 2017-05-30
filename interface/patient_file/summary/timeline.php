<?php
/**
 * OpenEMR (http://open-emr.org).
 *
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017 Robert Down
 * @package OpenEMR
 * @subpackage Patient
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once "../../globals.php";
require_once "{$srcdir}/patient.inc";
require_once "{$srcdir}/forms.inc";
require_once "{$srcdir}/acl.inc";
use OpenEMR\Patient\Timeline;
use Symfony\Component\HttpFoundation\Request;

if (!acl_check('patients', 'med')) {
    die('Not authorized');
}

$request = Request::createFromGlobals();
$action = $request->get('action');


if ($action == 'list') {
    $timeline = new Timeline($pid);
    $forms = $timeline->forms();

    $viewArgs = [
        'forms' => $forms,
        'patientName' => getPatientName($pid),
    ];

    echo $GLOBALS['twig']->render('patient/timeline.html.twig', $viewArgs);
}

if ($action == 'detail') {
    $form = getFormById($request->get('form_id'));
    $formdir = $form['formdir'];
    if (substr($formdir,0,3) == 'LBF') {
        include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");

        call_user_func("lbf_report", $attendant_id, $encounter, 2, $iter['form_id'], $formdir, true);
    } else {
        include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
        call_user_func($formdir . "_report", $attendant_id, $encounter, 2, $iter['form_id']);
    }
}

