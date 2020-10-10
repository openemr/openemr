<?php

/**
 * prior auth form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc");
require_once("C_FormPriorAuth.class.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$_POST['date_from']  = (!empty($_POST['date_from'])) ? DateToYYYYMMDD($_POST['date_from']) : null;
$_POST['date_to']  = (!empty($_POST['date_to'])) ? DateToYYYYMMDD($_POST['date_to']) : null;

$c = new C_FormPriorAuth();
echo $c->default_action_process($_POST);
@formJump();
