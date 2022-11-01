<?php

/**
 * Display patient notes.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/pnotes.inc.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

// form parameter docid can be passed to restrict the display to a document.
$docid = empty($_REQUEST['docid']) ? 0 : 0 + $_REQUEST['docid'];

//ajax for type 2 notes widget
if (isset($_GET['docUpdateId'])) {
    return disappearPnote($_GET['docUpdateId']);
}

?>
<div class='tabContainer'>
  <div class='tab current'>
    <?php
    //display all of the notes for the day, as well as others that are active from previous dates, up to a certain number, $N
    $N = $GLOBALS['num_of_messages_displayed'];
    $has_note = 0;
    $thisauth = AclMain::aclCheckCore('patients', 'notes');
    if ($thisauth) {
        $tmp = getPatientData($pid, "squad");
        if ($tmp['squad'] && !AclMain::aclCheckCore('squads', $tmp['squad'])) {
            $thisauth = 0;
        }
    }

    if (!$thisauth) {
        echo "<p>(" . xlt('Notes not authorized') . ")</p>\n";
    } else { ?>
        <table class="table table-sm table-hover">
        <?php
        $pres = getPatientData($pid, "lname, fname");
        $patientname = $pres['lname'] . ", " . $pres['fname'];
        //retrieve all active notes
        $result = getPnotesByDate(
            "",
            1,
            "id,date,body,user,title,assigned_to,message_status",
            $pid,
            "$N",
            0,
            '',
            $docid
        );

        if ($result != null) {
            $notes_count = 0;//number of notes so far displayed
            echo "<thead>\n<tr>";
            echo "<th class='text' >" . xlt('From') . "</th>\n";
            echo "<th class='text' >" . xlt('To{{Destination}}') . "</th>\n";
            if ($GLOBALS['messages_due_date']) {
                echo "<th class='text' >" . xlt('Due date') . "</th>\n";
            } else {
                echo "<th class='text' >" . xlt('Date') . "</th>\n";
            }
            echo "<th class='text' >" . xlt('Subject') . "</th>\n";
            echo "<th class='text' >" . xlt('Content') . "</th>\n";
            echo "<th class='text' ></th>\n";
            echo "</thead>\n</tr>\n<tbody>\n";
            foreach ($result as $iter) {
                $has_note = 1;

                $body = $iter['body'];
                $body = preg_replace('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}\s\([^)(]+\s)(to)(\s[^)(]+\))/', '', $body);
                $body = preg_replace('/(\sto\s)-patient-(\))/', '${1}' . $patientname . '${2}', $body);
                echo " <tr class='text' id=" . text($iter['id']) . ">\n";

                // Modified 6/2009 by BM to incorporate the patient notes into the list_options listings
                echo "<td class='text'>" . text($iter['user']) . "</td>\n";
                echo "<td class='text'>" . text($iter['assigned_to']) . "</td>\n";
                echo "<td class='text'>" . text(oeFormatDateTime(date('Y-m-d H:i', strtotime($iter['date'])))) . "</td>\n";
                echo "  <td class='text'><b>";
                echo generate_display_field(array('data_type' => '1','list_id' => 'note_type'), $iter['title']);
                echo "</b></td>\n";

                echo "  <td class='text'>" . pnoteConvertLinks(nl2br(text($body))) . "</td>\n";
                echo "<td class='text'><button data-id='" . attr($iter['id']) . "' class='complete_btn btn btn-sm btn-secondary'>" . xlt('Completed') . "</button></td>\n";
                echo " </tr>\n</tbody>\n";

                $notes_count++;
            }
        } ?>
        </table>

        <?php
        if ($has_note < 1) { ?>
            <span class='text'>
            <?php
                echo xlt("There are no messages on file for this patient.");
            if (AclMain::aclCheckCore('patients', 'notes', '', array('write', 'addonly'))) {
                echo " ";
                echo "<a href='pnotes_full.php' onclick='top.restoreSession()'>";
                echo xlt("To add messages, please click here");
                echo "</a>.";
            }
            ?>
            </span><?php
        } else { ?>
            <br/>
            <span class='text'>
            <?php echo xlt('Displaying the following number of most recent messages'); ?>:
            <b><?php echo text($N);?></b><br />
            <a href='pnotes_full.php?s=0' onclick='top.restoreSession()'><?php echo xlt('Click here to view them all.'); ?></a>
        </span><?php
        } ?>

        <br/>
        <br/><?php
    } ?>
    </div>
</div>
