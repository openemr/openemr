<?php

/**
 * Display patient notes.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;

// form parameter docid can be passed to restrict the display to a document.
$docid = empty($_REQUEST['docid']) ? 0 : intval($_REQUEST['docid']);

// form parameter orderid can be passed to restrict the display to a procedure order.
$orderid = empty($_REQUEST['orderid']) ? 0 : intval($_REQUEST['orderid']);

$patient_id = $pid;
if ($docid) {
    $row = sqlQuery("SELECT foreign_id FROM documents WHERE id = ?", array($docid));
    $patient_id = intval($row['foreign_id']);
} elseif ($orderid) {
    $row = sqlQuery("SELECT patient_id FROM procedure_order WHERE procedure_order_id = ?", array($orderid));
    $patient_id = intval($row['patient_id']);
}

 $urlparms = "docid=" . attr_url($docid) . "&orderid=" . attr_url($orderid);
?>
<html>
<head>
<?php Header::setupHeader(); ?>
</head>
<body class="body_bottom">

<?php
$thisauth = AclMain::aclCheckCore('patients', 'notes');
if ($thisauth) {
    $tmp = getPatientData($patient_id, "squad");
    if ($tmp['squad'] && !AclMain::aclCheckCore('squads', $tmp['squad'])) {
        $thisauth = 0;
    }
}

if (!$thisauth) {
    echo "<p>(" . xlt('Notes not authorized') . ")</p>\n";
    echo "</body>\n</html>\n";
    exit();
}
?>

<div id='pnotes'>

<?php if (AclMain::aclCheckCore('patients', 'notes', '', array('write','addonly'))) : ?>
<a href="pnotes_full.php?<?php echo $urlparms; ?>" onclick="top.restoreSession()">

<span class="title"><?php echo xlt('Notes'); ?>
    <?php
    if ($docid) {
        echo " " . xlt("linked to document") . " ";
        $d = new Document($docid);
        echo text($d->get_url_file());
    } elseif ($orderid) {
        echo " " . xlt("linked to procedure order") . " " . text($orderid);
    }
    ?>
</span>
<span class="more"><?php echo text($tmore);?></span>
</a>
<?php endif; ?>

<br />

<table>

<?php
//display all of the notes for the day, as well as others that are active from previous dates, up to a certain number, $N
$N = 15;

// Get the billing note if there is one.
$billing_note = "";
$colorbeg = "";
$colorend = "";
$resnote = getPatientData($patient_id, "billing_note");
if (!empty($resnote['billing_note'])) {
    $billing_note = $resnote['billing_note'];
    $colorbeg = "<span style='color:red'>";
    $colorend = "</span>";
}

//Display what the patient owes
$balance = get_patient_balance($patient_id);
if ($balance != "0") {
    $formatted = sprintf((xl('$') . '%01.2f'), $balance);
    echo " <tr class='text billing'>\n";
    echo "  <td>" . $colorbeg . xlt('Balance Due') .
    $colorend . "</td><td>" . $colorbeg .
    text($formatted) . $colorend . "</td>\n";
    echo " </tr>\n";
}

if ($billing_note) {
    echo " <tr class='text billing'>\n";
    echo "  <td>" . $colorbeg . xlt('Billing Note') .
    $colorend . "</td><td>" . $colorbeg .
    text($billing_note) . $colorend . "</td>\n";
    echo " </tr>\n";
}

//retrieve all active notes
$result = getPnotesByDate(
    "",
    1,
    "id,date,body,user,title,assigned_to",
    $patient_id,
    "all",
    0,
    '',
    $docid,
    '',
    $orderid
);

if ($result != null) {
    $notes_count = 0;//number of notes so far displayed
    foreach ($result as $iter) {
        if ($notes_count >= $N) {
            //we have more active notes to print, but we've reached our display maximum
            echo " <tr>\n";
            echo "  <td colspan='3' align='center'>\n";
            echo "   <a ";
            echo "href='pnotes_full.php?active=1&$urlparms" .
            "' class='alert' onclick='top.restoreSession()'>";
            echo xlt('Some notes were not displayed.') . ' ' .
            xlt('Click here to view all.') . "</a>\n";
            echo "  </td>\n";
            echo " </tr>\n";
            break;
        }

        $body = $iter['body'];
        if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d\:\d\d /', $body)) {
            $body = nl2br(text($body));
        } else {
            $body = text(date('Y-m-d H:i', strtotime($iter['date']))) .
            ' (' . text($iter['user']) . ') ' . nl2br(text($body));
        }

        echo " <tr class='text noterow' id='" . text($iter['id']) . "'>\n";

        // Modified 6/2009 by BM to incorporate the patient notes into the list_options listings
        echo "  <td valign='top' class='bold'>";
        echo generate_display_field(array('data_type' => '1','list_id' => 'note_type'), $iter['title']);
        echo "</td>\n";

        echo "  <td valign='top'>$body</td>\n";
        echo " </tr>\n";

        $notes_count++;
    }
}
?>

</table>

</div> <!-- end pnotes -->

</body>

<script>
// jQuery stuff to make the page a little easier to use

$(function () {
    $(".noterow").on("mouseover", function() { $(this).toggleClass("highlight"); });
    $(".noterow").on("mouseout", function() { $(this).toggleClass("highlight"); });
});

</script>

</html>
