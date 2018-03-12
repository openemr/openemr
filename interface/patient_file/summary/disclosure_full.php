<?php
/**
 * Patient disclosures main screen.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Visolve <vicareplus_engg@visolve.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) Visolve <vicareplus_engg@visolve.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/log.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Core\Header;

//retrieve the user name
$res = sqlQuery("select username from users where username=?", array($_SESSION{"authUser"}));
$uname=$res{"username"};
//if the mode variable is set to disclosure, retrieve the values from 'disclosure_form ' in record_disclosure.php to store it in database.
if (isset($_POST["mode"]) and  $_POST["mode"] == "disclosure") {
    $dates=trim($_POST['dates']);
    $event=trim($_POST['form_disclosure_type']);
    $recipient_name=trim($_POST['recipient_name']);
    $disclosure_desc=trim($_POST['desc_disc']);
    $disclosure_id=trim($_POST['disclosure_id']);
    if (isset($_POST["updatemode"]) and $_POST["updatemode"] == "disclosure_update") {
        //update the recorded disclosure in the extended_log table.
        updateRecordedDisclosure($dates, $event, $recipient_name, $disclosure_desc, $disclosure_id);
    } else {
        //insert the disclosure records in the extended_log table.
         recordDisclosure($dates, $event, $pid, $recipient_name, $disclosure_desc, $uname);
    }
    // added ajax submit to record_disclosure thus an exit() 12/19/17
    exit();
}

if (isset($_GET['deletelid'])) {
    $deletelid=$_GET['deletelid'];
//function to delete the recorded disclosures
    deleteDisclosure($deletelid);
}
?>
<html>
<head>

    <?php Header::setupHeader(['common']); ?>

</head>

<body class="body_top">
<div>
    <span class="title"><?php echo xlt('Disclosures'); ?></span>
</div>
<div class="disclosure_wrap">
<div style='float: left; margin-right: 10px'><?php echo xlt('for'); ?>&nbsp;
    <span class="title"><a href="../summary/demographics.php" onclick="top.restoreSession()"><?php $pname = getPatientName($pid);
    echo text($pname); ?></a></span>
</div>
<div>
    <a href="record_disclosure.php" class="css_button iframe" onclick="top.restoreSession()"><span><?php echo xlt('Record'); ?></span></a>
</div>
<div>
    <a href="demographics.php"
    class="css_button" onclick="top.restoreSession()"> <span><?php echo xlt('View Patient') ?></span></a>
</div>
</div>
<br>
<br>
<?php
$N=15;
$offset = $_REQUEST['offset'];
if (!isset($offset)) {
    $offset = 0;
}

$disclQry = " SELECT el.id, el.event, el.recipient, el.description, el.date, CONCAT(u.fname, ' ', u.lname) as user_fullname FROM extended_log el" .
  " LEFT JOIN users u ON u.username = el.user " .
  " WHERE el.patient_id = ? AND el.event IN (SELECT option_id FROM list_options WHERE list_id='disclosure_type' AND activity = 1)" .
  " ORDER BY el.date DESC ";
$r2= sqlStatement($disclQry, array($pid));
$totalRecords=sqlNumRows($r2);

$disclInnerQry = " SELECT el.id, el.event, el.recipient, el.description, el.date, CONCAT(u.fname, ' ', u.lname) as user_fullname FROM extended_log el" .
  " LEFT JOIN users u ON u.username = el.user" .
  " WHERE patient_id = ? AND event IN (SELECT option_id FROM list_options WHERE list_id = 'disclosure_type' AND activity = 1)" .
  " ORDER BY date DESC LIMIT " . escape_limit($offset) . " , " . escape_limit($N);

$r1= sqlStatement($disclInnerQry, array($pid));
$n=sqlNumRows($r1);
$noOfRecordsLeft=($totalRecords - $offset);
if ($n>0) {?>
    <table border='0' class="text">
        <tr>
        <td colspan='5' style="padding: 5px;"><a href="disclosure_full.php" class="" id='Submit' onclick="top.restoreSession()"><span><?php echo xlt('Refresh'); ?></span></a></td>
        </tr>
    </table>
<div id='pnotes'>
    <table border='0' cellpadding="1" width='80%'>
        <tr class="showborder_head" align='left' height="22">
            <th style='width: 120px';>&nbsp;</th>
            <th style="border-style: 1px solid #000" width="140px"><?php echo xlt('Recipient Name'); ?></th>
            <th style="border-style: 1px solid #000" width="140px"><?php echo xlt('Disclosure Type'); ?></th>
            <th style="border-style: 1px solid #000"><?php echo xlt('Description'); ?></th>
            <th style="border-style: 1px solid #000"><?php echo xlt('Provider'); ?></th>
        </tr>
    <?php
    $result2 = array();
    for ($iter = 0; $frow = sqlFetchArray($r1); $iter++) {
        $result2[$iter] = $frow;
    }

    foreach ($result2 as $iter) {
        $description =nl2br(text($iter{'description'})); //for line break if there is any new lines in the input text area field.
        ?>
        <!-- List the recipient name, description, date and edit and delete options-->
        <tr  class="noterow" height='25'>
            <!--buttons for edit and delete.-->
            <td valign='top'><a href='record_disclosure.php?editlid=<?php echo text($iter{'id'}); ?>'
            class='css_button_small iframe' onclick='top.restoreSession()'><span><?php echo xlt('Edit');?></span></a>
            <a href='#' class='deletenote css_button_small'
            id='<?php echo text($iter{'id'}); ?>' onclick='top.restoreSession()'><span><?php echo xlt('Delete');?></span></a></td>
            <td class="text" valign='top'><?php echo text($iter{'recipient'});?>&nbsp;</td>
            <td class='text' valign='top'><?php echo text(getListItemTitle('disclosure_type', $iter['event'])); ?>&nbsp;</td>
            <td class='text'><?php echo text($iter{'date'})." ".$description;?>&nbsp;</td>
            <td class='text'><?php echo text($iter{'user_fullname'});?></td>
        </tr>
        <?php
    }
} else {?>
    <br>
    <!-- Display None, if there is no disclosure -->
    <span class='text' colspan='3'><?php echo xlt('None');?></span>
    <?php
}
?>
</table>
<table width='400' border='0' cellpadding='0' cellspacing='0'>
 <tr>
  <td>
<?php
if ($offset > ($N-1) && $n!=0) {
    echo "   <a class='link' href='disclosure_full.php?active=" . $active .
        "&offset=" . attr($offset-$N) . "' onclick='top.restoreSession()'>[" .
        xlt('Previous') . "]</a>\n";
}
?>

<?php

if ($n >= $N && $noOfRecordsLeft!=$N) {
    echo "&nbsp;&nbsp;   <a class='link' href='disclosure_full.php?active=" . $active.
        "&offset=" . attr($offset+$N)  ."&leftrecords=".$noOfRecordsLeft."' onclick='top.restoreSession()'>[" .
        xlt('Next') . "]</a>\n";
}
?>
  </td>
 </tr>
</table>
</div>
</body>

<script type="text/javascript">
$(document).ready(function () {
    // todo, move this to a common library
    //for row highlight.
    $(".noterow").mouseover(function () {
        $(this).toggleClass("highlight");
    });
    $(".noterow").mouseout(function () {
        $(this).toggleClass("highlight");
    });

    //for deleting the disclosures
    $(".deletenote").click(function () {
        DeleteNote(this);
    });

    var DeleteNote = function (logevent) {
        if (confirm("<?php echo htmlspecialchars(xl('Are you sure you want to delete this disclosure?', '', '', '\n ') . xl('This action CANNOT be undone.'), ENT_QUOTES); ?>")) {
            top.restoreSession();
            window.location.replace("disclosure_full.php?deletelid=" + logevent.id)
        }
    }

    $(".iframe").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 500, 310, '', '', {
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

});
// for record disclosure dlgclose callback
function refreshme() {
    top.restoreSession();
    document.location.reload();
}
</script>
</html>


