<?php
/**
 * message_status.php - generate contents for pop-up messenger.
 *
 * This file is included as an invisible iframe by forms that want to be
 * notified when a new message for the user comes in.
 *
 * Copyright (C) 2016 Terry Hill <terry@lillysystems.com>
 * Copyright (C) 2012 Julia Longtin <julialongtin@diasp.org>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 *
 * This file is included as an invisible iframe by forms that want to be
 * notified when a new message for the user comes in.
 *
 * @package OpenEMR
 * @author  Julia Longtin <julialongtin@diasp.org>
 * @author  Terry Hill <terry@lillysystems.com>
 * @link    http://open-emr.org
 */
/* Sanitize All Escapes */
$fake_register_globals = false;
/* Stop fake register globals */
$sanitize_all_escapes = true;
/* include required globals */
require_once('../../globals.php');
/* for acl_check() */
require_once($GLOBALS['srcdir'] . '/acl.inc');
/* for text() */
require_once($GLOBALS['srcdir'] . '/htmlspecialchars.inc.php');
/* for getPnotesByUser(). */
require_once($GLOBALS['srcdir'] . '/pnotes.inc');
/* for GetDueReminderCount(). */
require_once($GLOBALS['srcdir'] . '/dated_reminder_functions.php');
?>
<html>
<head>
    <title><?php echo text(xl('Invisible Messaging IFrame')); ?></title>
</head>
<body>
<div id="notices"><?php
    $notices = 0;
    if ($GLOBALS['floating_message_alerts']) {
        // if this user has permission to patient notes..
        if (acl_check('patients', 'notes')) {
            // generate notice if the user has pending (unread) messages or reminders.
            $total = getPnotesByUser(true, false, $_SESSION['authUser'], true);
            $total += GetAllReminderCount();
            if ($total > 0) {
                echo '<div id="notice' . $notices . '"><div class="sticky"></div><div class="colour">blue</div><div class="title">' . xlt('Notice') . '</div><div class="text">' . xlt('You have') . ' ' . $total . ' ' . xlt('active notes and reminders' . (($total > 1) ? 's' : '')) . '.</div></div>';
                $notices++;
            }
        }
        // generate warning if user has overdue reminders.
        $total = GetDueReminderCount(0, strtotime(date('Y/m/d')));
        if ($total > 0) {
            echo '<div id="notice' . $notices . '"><div class="sticky">1</div><div class="UUID">OVERDUEWARN1</div><div class="colour">red</div><div class="title">' . xlt('WARNING') . '</div><div class="text">' . xlt('You have') . ' ' . $total . ' ' . xlt('overdue reminder' . (($total > 1) ? 's' : '')) . '.</div></div>';
            $notices++;
        }
    }
    if ($GLOBALS['floating_message_alerts_allergies']) {
        // Check for Allergies with Reaction/severity 
        if (acl_check('patients', 'med')) {
           $sql = "SELECT * FROM lists WHERE pid = ? AND type = 'allergy' ORDER BY begdate";
           $res = sqlStatement($sql, array($pid));
           while ($row = sqlFetchArray($res)) {
                if (!empty($row['reaction'])) {
                   $reaction = " (". $row['reaction'] . ") / " . $row['severity_al'];
                   if ($row['severity_al'] =="fatal") {
                       echo '<div id="notice' . $notices . '"><div class="sticky"></div><div class="colour">red</div><div class="title">' . xlt('FATAL ALLERGY REACTION FATAL') . '</div><div class="text">' . htmlspecialchars($row['title'] . $reaction , ENT_NOQUOTES) . '</div></div>';
                   }else{
                       echo '<div id="notice' . $notices . '"><div class="sticky"></div><div class="colour">red</div><div class="title">' . xlt('ALLERGY REACTION') . '</div><div class="text">' . htmlspecialchars($row['title'] . $reaction , ENT_NOQUOTES) . '</div></div>';
                   }
                   $notices++;
                }
            }
        }
    }
    if ($GLOBALS['floating_message_alerts']) {
        #Check for Patient alerts
        if (acl_check('patients', 'med')) {
            $sql = "SELECT * FROM lists WHERE pid = ? AND type = 'patient_alert' ORDER BY begdate";
            $res = sqlStatement($sql, array($pid));
            while ($row = sqlFetchArray($res)) {
                $reaction = " ";
                echo '<div id="notice' . $notices . '"><div class="sticky"></div><div class="colour">red</div><div class="title">' . xlt('PATIENT ALERT') . '</div><div class="text">' . htmlspecialchars($row['title'] . $reaction , ENT_NOQUOTES) . '</div></div>';
                $notices++;
            }
        }
    }
    /* uncomment this for demos
    echo '<div id="notice'.$notices.'"><div class="sticky">1</div><div class="UUID">VMTESTING1</div><div class="colour">red</div><div class="title">'.xlt('WARNING').'</div><div class="text">'.xlt('This VM is for TESTING ONLY').'</div>';
    $notices++;
    */
    if ($notices > 0) {
        echo '<div id="noticecount">' . $notices . '</div>';
    }
    ?></div>
</body>
</html>