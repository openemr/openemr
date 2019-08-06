<?php
/**
 * library/log_validation.php to validate audit logs tamper resistance.
 *
 * Copyright (C) 2016 Visolve <services@visolve.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Visolve <services@visolve.com>
 * @link    https://www.open-emr.org
 */


require_once("../interface/globals.php");
require_once("$srcdir/acl.inc");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!acl_check('admin', 'users')) {
    die(xlt("Not Authorized"));
}

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$valid  = true;
$errors = array();
catch_logs();
$sql = sqlStatement("select * from log_validator");
while ($row = sqlFetchArray($sql)) {
    $logEntry = sqlQuery("select * from log where id = ?", array($row['log_id']));
    if (empty($logEntry)) {
        $valid = false;
        array_push($errors, xl("Following audit log entry number is missing") . ": " . $row['log_id']);
    } else if ($row['log_checksum'] != $logEntry['checksum']) {
        $valid = false;
        array_push($errors, xl("Audit log tampering evident at entry number") . " " . $row['log_id']);
    }

    if (!$valid) {
        break;
    }
}

if ($valid) {
    echo xlt("Audit Log Validated Successfully");
} else {
    echo xlt("Audit Log Validation Failed") . "(ERROR:: " . text($errors[0]) . ")";
}

function catch_logs()
{
    $sql = sqlStatement("select * from log where id not in(select log_id from log_validator) and checksum is NOT null and checksum != ''");
    while ($row = sqlFetchArray($sql)) {
        sqlStatement("INSERT into log_validator (log_id,log_checksum) VALUES(?,?)", array($row['id'],$row['checksum']));
    }
}
