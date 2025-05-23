<?php

/**
 * facilities_add.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

// Ensure authorized
if (!AclMain::aclCheckCore('admin', 'users')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Facility Add")]);
    exit;
}

$facilityService = new FacilityService();

$alertmsg = '';
$use_validate_js = 1;
require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php");
//Gets validation rules from Page Validation list.
//Note that for technical reasons, we are bypassing the standard validateUsingPageRules() call.
$rules = collectValidationPageRules("/interface/usergroup/facilities_add.php");

$pc = new POSRef();
$resPBE = $facilityService->getPrimaryBusinessEntity(array("excludedId" => ($my_fid ?? null)));
$disabled = (!empty($resPBE) && sizeof($resPBE) > 0) ? 'disabled' : '';

$args = [
    'collectThis' => (empty($rules)) ? "undefined" : json_sanitize($rules["facility-add"]["rules"]),
    'forceClose' => (isset($_POST["mode"]) && $_POST["mode"] == "facility") ? true : false,
    'erxEnabled' => $GLOBALS['erx_enable'],
    'alertMsg' => trim($alertmsg) ? true : false,
    'disablePBE' => $disabled,
    'pos_code' => $pc->get_pos_ref(),
    'mode' => 'add',
];

$twig = new TwigContainer(null, $GLOBALS["kernel"]);
$t = $twig->getTwig();
echo $t->render("super/facilities/form.html.twig", $args);
