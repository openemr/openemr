<?php

/**
 * lab_results_messages.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010 OpenEMR Support LLC
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("$include_root/globals.php");
require_once("$srcdir/pnotes.inc.php");
require_once("$srcdir/patient.inc.php");

use OpenEMR\Common\Acl\AclMain;

function lab_results_messages($set_pid, $rid, $provider_id = "")
{
    global $userauthorized;

    $sqlBindArray = array();
    if ($provider_id != "") {
        $where = "AND id = ?";
        array_push($sqlBindArray, $provider_id);
    }

    // Get all active users.
    $rez = sqlStatement("select id, username from users where username != '' AND active = '1' $where", $sqlBindArray);
    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        $result[$iter] = $row;
    }

    if (!empty($result)) {
        foreach ($result as $user_detail) {
            unset($thisauth); // Make sure it is empty.
            // Check user authorization. Only send the pending review message to authorised user.
            // $thisauth = AclMain::aclCheckCore('patients', 'sign', $user_detail['username']);

            // Route message to administrators if there is no provider match.
            if ($provider_id == "") {
                $thisauth = AclMain::aclCheckCore('admin', 'super', $user_detail['username']);
            } else {
                $thisauth = true;
            }

            if ($thisauth) {
                // Send lab result message to the ordering provider when there is a new lab report.
                $pname = getPatientName($set_pid);
                $link = "<a href='../../orders/orders_results.php?review=1&set_pid=" . attr_url($set_pid) . "'" .
                " onclick='return top.restoreSession()'>here</a>";
                $note = "Patient $pname's lab results have arrived. Please click $link to review them.<br/>";
                $note_type = "Lab Results";
                $message_status = "New";
                // Add pnote.
                $noteid = addPnote($set_pid, $note, $userauthorized, '1', $note_type, $user_detail['username'], '', $message_status);
            }
        }
    }
}
