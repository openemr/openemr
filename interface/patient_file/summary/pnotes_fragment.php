<?php
/**
 * Display patient notes.
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
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */



 require_once("../../globals.php");
 require_once("$srcdir/pnotes.inc");
 require_once("$srcdir/acl.inc");
 require_once("$srcdir/patient.inc");
 require_once("$srcdir/options.inc.php");

 // form parameter docid can be passed to restrict the display to a document.
 $docid = empty($_REQUEST['docid']) ? 0 : 0 + $_REQUEST['docid'];

 //ajax for type 2 notes widget
if (isset($_GET['docUpdateId'])) {
    return disappearPnote($_GET['docUpdateId']);
}

?>
<?php if ($GLOBALS['portal_offsite_enable'] == 1) { ?>
<ul class="tabNav">
  <li class="current" ><a href="#"><?php echo htmlspecialchars(xl('Inbox'), ENT_NOQUOTES); ?></a></li>
  <li><a href="#"><?php echo htmlspecialchars(xl('Sent Items'), ENT_NOQUOTES); ?></a></li>
</ul>
<?php } ?>
<div class='tabContainer' >
  <div class='tab current' >
    <?php
    //display all of the notes for the day, as well as others that are active from previous dates, up to a certain number, $N
    $N = $GLOBALS['num_of_messages_displayed']; ?>

    <br/>

    <?php

     $has_note = 0;
     $thisauth = acl_check('patients', 'notes');
    if ($thisauth) {
        $tmp = getPatientData($pid, "squad");
        if ($tmp['squad'] && ! acl_check('squads', $tmp['squad'])) {
            $thisauth = 0;
        }
    }

    if (!$thisauth) {
        echo "<p>(" . htmlspecialchars(xl('Notes not authorized'), ENT_NOQUOTES) . ")</p>\n";
    } else { ?>
        <table width='100%' border='0' cellspacing='1' cellpadding='1' style='border-collapse:collapse;' >
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
            echo "<tr class='text' style='border-bottom:2px solid #000;' >\n";
            echo "<td valign='top' class='text' ><b>". htmlspecialchars(xl('From'), ENT_NOQUOTES) ."</b></td>\n";
            echo "<td valign='top' class='text' ><b>". htmlspecialchars(xl('To'), ENT_NOQUOTES) ."</b></td>\n";
            echo "<td valign='top' class='text' ><b>". htmlspecialchars(xl('Date'), ENT_NOQUOTES) ."</b></td>\n";
            echo "<td valign='top' class='text' ><b>". htmlspecialchars(xl('Subject'), ENT_NOQUOTES) ."</b></td>\n";
            echo "<td valign='top' class='text' ><b>". htmlspecialchars(xl('Content'), ENT_NOQUOTES) ."</b></td>\n";
            echo "<td valign='top' class='text' ></td>\n";
            echo "</tr>\n";
            foreach ($result as $iter) {
                $has_note = 1;

                $body = $iter['body'];
                $body = preg_replace('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}\s\([^)(]+\s)(to)(\s[^)(]+\))/', '', $body);
                $body = preg_replace('/(\sto\s)-patient-(\))/', '${1}'.$patientname.'${2}', $body);
                echo " <tr class='text' id='".htmlspecialchars($iter['id'], ENT_QUOTES)."' style='border-bottom:1px dashed;height:30px;' >\n";

                // Modified 6/2009 by BM to incorporate the patient notes into the list_options listings
                echo "<td valign='top' class='text'>".htmlspecialchars($iter['user'], ENT_NOQUOTES)."</td>\n";
                echo "<td valign='top' class='text'>".htmlspecialchars($iter['assigned_to'], ENT_NOQUOTES)."</td>\n";
                echo "<td valign='top' class='text'>".htmlspecialchars(oeFormatDateTime(date('Y-m-d H:i', strtotime($iter['date']))), ENT_NOQUOTES)."</td>\n";
                echo "  <td valign='top' class='text'><b>";
                echo generate_display_field(array('data_type'=>'1','list_id'=>'note_type'), $iter['title']);
                echo "</b></td>\n";

                echo "  <td valign='top' class='text'>$body</td>\n";
                echo "<td valign='top' class='text'><button data-id='" . attr($iter['id']) . "' class='complete_btn'>" . xlt('Completed') . "</button></td>\n";
                echo " </tr>\n";

                $notes_count++;
            }
        } ?>

        </table>

        <?php
        if ($has_note < 1) { ?>
            <span class='text'>
            <?php
                echo xlt("There are no notes on file for this patient.");
            if (acl_check('patients', 'notes', '', array('write', 'addonly'))) {
                echo " ";
                echo "<a href='pnotes_full.php' onclick='top.restoreSession()'>";
                echo xlt("To add notes, please click here");
                echo "</a>.";
            }
            ?>
            </span><?php
        } else { ?>
            <br/>
            <span class='text'>
            <?php echo htmlspecialchars(xl('Displaying the following number of most recent notes:'), ENT_NOQUOTES); ?>
            <b><?php echo text($N);?></b><br>
            <a href='pnotes_full.php?s=0' onclick='top.restoreSession()'>
            <?php echo htmlspecialchars(xl('Click here to view them all.'), ENT_NOQUOTES); ?></a>
        </span><?php
        } ?>

        <br/>
        <br/><?php
    } ?>
    </div>
    <?php if ($GLOBALS['portal_offsite_enable'] == 1) { ?>
        <div class='tab'>
            <?php
            //display all of the notes for the day, as well as others that are active from previous dates, up to a certain number, $N
            $M = $GLOBALS['num_of_messages_displayed']; ?>
            <br/>
            <?php
            $has_sent_note = 0;
            if (!$thisauth) {
                echo "<p>(" . htmlspecialchars(xl('Notes not authorized'), ENT_NOQUOTES) . ")</p>\n";
            } else { ?>
                <table width='100%' border='0' cellspacing='1' cellpadding='1' style='border-collapse:collapse;' >
                    <?php
                    //retrieve all active notes
                    $result_sent = getSentPnotesByDate(
                        "",
                        1,
                        "id,date,body,user,title,assigned_to,pid",
                        $pid,
                        "$M",
                        0,
                        '',
                        $docid
                    );
                    if ($result_sent != null) {
                        $notes_sent_count = 0;//number of notes so far displayed
                        echo "<tr class='text' style='border-bottom:2px solid #000;' >\n";
                        echo "<td valign='top' class='text' ><b>". htmlspecialchars(xl('To'), ENT_NOQUOTES) ."</b></td>\n";
                        echo "<td valign='top' class='text' ><b>". htmlspecialchars(xl('Date'), ENT_NOQUOTES) ."</b></td>\n";
                        echo "<td valign='top' class='text' ><b>". htmlspecialchars(xl('Subject'), ENT_NOQUOTES) ."</b></td>\n";
                        echo "<td valign='top' class='text' ><b>". htmlspecialchars(xl('Content'), ENT_NOQUOTES) ."</b></td>\n";
                        echo "</tr>\n";
                        foreach ($result_sent as $iter) {
                            $has_sent_note = 1;
                            $body = $iter['body'];
                            if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d\:\d\d /', $body)) {
                                $body = nl2br(htmlspecialchars(oeFormatPatientNote($body), ENT_NOQUOTES));
                            } else {
                                $body = htmlspecialchars(oeFormatSDFT(strtotime($iter['date'])) . date(' H:i', strtotime($iter['date'])) .
                                        ' (' . $iter['user'] . ') ', ENT_NOQUOTES) .
                                    nl2br(htmlspecialchars(oeFormatPatientNote($body), ENT_NOQUOTES));
                            }

                            $body = preg_replace('/(:\d{2}\s\()'.$iter['pid'].'(\sto\s)/', '${1}'.$patientname.'${2}', $body);
                            $body = strlen($body) > 120 ? substr($body, 0, 120)."<b>.......</b>" : $body;
                            echo " <tr class='text' id='".htmlspecialchars($iter['id'], ENT_QUOTES)."' style='border-bottom:1px dashed;height:30px;' >\n";
                            // Modified 6/2009 by BM to incorporate the patient notes into the list_options listings
                            echo "<td valign='top' class='text'>".htmlspecialchars($iter['assigned_to'], ENT_NOQUOTES)."</td>\n";
                            echo "<td valign='top' class='text'>".htmlspecialchars($iter['date'], ENT_NOQUOTES)."</td>\n";
                            echo "  <td valign='top' class='text'><b>";
                            echo generate_display_field(array('data_type'=>'1','list_id'=>'note_type'), $iter['title']);
                            echo "</b></td>\n";
                            echo "  <td valign='top' class='text'>$body</td>\n";
                            echo " </tr>\n";
                            $notes_sent_count++;
                        }
                    } ?>
                </table>
                <?php
                if ($has_sent_note < 1) { ?>
                    <span class='text'>
                    <?php
                    echo xlt("There are no notes on file for this patient.");
                    if (acl_check('patients', 'notes', '', array('write', 'addonly'))) {
                        echo " ";
                        echo "<a href='pnotes_full.php' onclick='top.restoreSession()'>";
                        echo xlt("To add notes, please click here");
                        echo "</a>.";
                    }
                    ?>
                    </span><?php
                } else { ?>
                    <br/>
                    <span class='text'>
        <?php echo htmlspecialchars(xl('Displaying the following number of most recent notes'), ENT_NOQUOTES).":"; ?>
                        <b><?php echo text($M);?></b><br>
        <a href='pnotes_full.php?s=1' onclick='top.restoreSession()'><?php echo htmlspecialchars(xl('Click here to view them all.'), ENT_NOQUOTES); ?></a>
        </span>
                    <?php
                } ?>
                <br/>
                <br/><?php
            } ?>
        </div>
    <?php } ?>
</div>

<script language="javascript">
// jQuery stuff to make the page a little easier to use

tabbify();

$(document).ready(function(){
    $(".noterow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".noterow").mouseout(function() { $(this).toggleClass("highlight"); });

    //Ajax call for type 2 note widget
    $(".complete_btn").on("click", function(){
        //console.log($(this).attr('data-id'));
        var btn = $(this);
        $.ajax({
            method: "GET",
            url: "pnotes_fragment.php?docUpdateId=" + btn.attr('data-id'),
        })
            .done(function() {
                btn.prop("disabled",true);
                btn.unbind('mouseenter mouseleave');
                btn.css('background-color', 'gray');
            });
    });

});

</script>
