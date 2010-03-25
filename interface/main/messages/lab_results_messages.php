<?php
// Copyright (C) 2010 OpenEMR Support LLC   
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("$include_root/globals.php");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/auth.inc");
include_once("$srcdir/formdata.inc.php");

function lab_results_messages($set_pid, $rid, $provider_id="") {
    if ($provider_id != "") {
        $where = "AND id = '".$provider_id."'";
    }
    // Get all active users.
    $rez = sqlStatement("select id, username from users where username != '' AND active = '1' $where");
    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        $result[$iter] = $row;
    }

    if (!empty($result)) {
        foreach ($result as $user_detail) {
            unset($thisauth); // Make sure it is empty.
            // Check user authorization. Only send the panding review message to authorised user.
            // $thisauth = acl_check('patients', 'sign', $user_detail['username']);

            // Route message to administrators if there is no provider match.
            if ($provider_id == "") {
                $thisauth = acl_check('admin', 'super', $user_detail['username']);
            }
            else {
                $thisauth = true;
            }

            if ($thisauth) {
                // Send lab result message to the ordering provider when there is a new lab report.
                $userauthorized = formData("userauthorized");
                $pname = getPatientName($set_pid);
                $link = "<a href='../../orders/orders_results.php?review=1&set_pid=$set_pid'" .
                " onclick='return top.restoreSession()'>here</a>";
                $note = "Patient $pname's lab results have arrived. Please click $link to review them.<br/>";
                $note_type = "Lab Results";
                $message_status = "New";
                // Add pnote.
                $noteid = addPnote($set_pid, $note, $userauthorized, '1', $note_type, $user_detail['username']);
                sqlQ("update pnotes set message_status='".$message_status."' where id = '$noteid'");
            }
        }
    }
}

?>
