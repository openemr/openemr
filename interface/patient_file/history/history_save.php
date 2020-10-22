<?php

/**
 * history_save.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("history.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$module_call_pid = $_GET['requestPid'] ?? null;
if (!empty($module_call_pid)) {
    $pid = (int)$module_call_pid;
}

// Check authorization.
if (AclMain::aclCheckCore('patients', 'med')) {
    $tmp = getPatientData($pid, "squad");
    if ($tmp['squad'] && ! AclMain::aclCheckCore('squads', $tmp['squad'])) {
        die(xlt("Not authorized for this squad."));
    }
}

if (!AclMain::aclCheckCore('patients', 'med', '', array('write','addonly'))) {
    die(xlt("Not authorized"));
}

foreach ($_POST as $key => $val) {
    if ($val == "YYYY-MM-DD") {
        $_POST[$key] = "";
    }
}

// Update history_data:
//
$newdata = array();
$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'HIS' AND uor > 0 AND field_id != '' " .
  "ORDER BY group_id, seq");
while ($frow = sqlFetchArray($fres)) {
    $field_id  = $frow['field_id'];
    // get value only if field exist in $_POST (prevent deleting of field with disabled attribute)
    if (isset($_POST["form_$field_id"])) {
        $newdata[$field_id] = get_layout_form_value($frow);
    // php fix for risk factor checkboxes unchecked after one was checked
    } elseif ($field_id == 'usertext11') {
        $newdata[$field_id] = get_layout_form_value($frow);
    }
}

updateHistoryData($pid, $newdata);

if ($module_call_pid) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

header("Location: history.php");
