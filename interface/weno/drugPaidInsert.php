<?php
/**
 * weno drug paid insert
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once('../globals.php');

$drugs = file_get_contents('../../contrib/weno/drugspaidinsert.sql');
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

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
