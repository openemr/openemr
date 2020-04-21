<?php

/**
 * weno drug paid insert
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../globals.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}
// check to make sure only administrators access this page.
if (!AclMain::aclCheckCore('admin', 'super')) {
    die(xlt("You are not authorized!"));
}

function insertDrugData()
{
    $drugs = file_get_contents('../../contrib/weno/erx_weno_drugs.sql');
    $drugsArray = explode(";\n", $drugs);

    // Settings to drastically speed up import with InnoDB
    sqlStatementNoLog("SET autocommit=0");
    sqlStatementNoLog("START TRANSACTION");

    foreach ($drugsArray as $drug) {
        if (empty($drug)) {
            continue;
        }
        sqlStatementNoLog($drug);
    }

    // Settings to drastically speed up import with InnoDB
    sqlStatementNoLog("COMMIT");
    sqlStatementNoLog("SET autocommit=1");
}

insertDrugData();

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
