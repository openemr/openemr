<?php

/**
 * Authorizations script.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/forms.inc.php");
require_once("$srcdir/transactions.inc.php");
require_once("$srcdir/lists.inc.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\Header;

// The number of authorizations to display in the quick view:
// MAR 20041008 the full authorizations screen sucks... no links to the patient charts
// increase to a high number to make the mini frame more useful.
$N = 50;

$atemp = sqlQuery("SELECT see_auth FROM users WHERE username = ?", array($_SESSION['authUser']));
$see_auth = $atemp['see_auth'];

$imauthorized = $_SESSION['userauthorized'] || $see_auth > 2;

// This authorizes everything for the specified patient.
if (isset($_GET["mode"]) && $_GET["mode"] == "authorize" && $imauthorized) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $retVal = getProviderId($_SESSION['authUser']);
    EventAuditLogger::instance()->newEvent("authorize", $_SESSION["authUser"], $_SESSION["authProvider"], 1, $_GET["pid"]);
    sqlStatement("update billing set authorized=1 where pid=?", array($_GET["pid"]));
    sqlStatement("update forms set authorized=1 where pid=?", array($_GET["pid"]));
    sqlStatement("update pnotes set authorized=1 where pid=?", array($_GET["pid"]));
    sqlStatement("update transactions set authorized=1 where pid=?", array($_GET["pid"]));
}
?>
<html>
<head>
<?php Header::setupHeader(); ?>
<style>
/* min & max buttons are hidden in the newer concurrent layout */
#min {
    float: right;
    padding: 3px;
    margin: 2px;
    cursor: pointer;
    <?php echo "display: none;"; ?>
}
#max {
    float: right;
    padding: 3px;
    margin: 2px;
    cursor: pointer;
    <?php echo "display: none;"; ?>
}
</style>
</head>
<body class="body_bottom">

<!-- 'buttons' to min/max the bottom frame -JRM -->
<div id="max" title="Restore this information">
    <img src="<?php echo $GLOBALS['images_static_relative']; ?>/max.gif" />
</div>
<div id="min" title="Minimize this information">
    <img src="<?php echo $GLOBALS['images_static_relative']; ?>/min.gif" />
</div>

<?php if ($imauthorized) { ?>
<span class='title'>
<a href='authorizations_full.php' onclick='top.restoreSession()'>
    <?php echo xlt('Authorizations'); ?> <span class='more'><?php echo text($tmore); ?></span></a>
    <?php
}
?>
</span>

<?php
if ($imauthorized && $see_auth > 1) {
//  provider
//  billing
//  forms
//  pnotes
//  transactions

//fetch billing information:
    if (
        $res = sqlStatement("select *, concat(u.fname,' ', u.lname) as user " .
        "from billing LEFT JOIN users as u on billing.user = u.id where " .
        "billing.authorized = 0 and billing.activity = 1 and " .
        "groupname = ?", array($groupname))
    ) {
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $result1[$iter] = $row;
        }

        if ($result1) {
            foreach ($result1 as $iter) {
                $authorize[$iter["pid"]]["billing"] .= "<span class='text'>" .
                text($iter["code_text"] . " " . date("n/j/Y", strtotime($iter["date"]))) .
                "</span><br />\n";
            }
        }
    }

//fetch transaction information:
    if (
        $res = sqlStatement("select * from transactions where " .
        "authorized = 0 and groupname = ?", array($groupname))
    ) {
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $result2[$iter] = $row;
        }

        if ($result2) {
            foreach ($result2 as $iter) {
                $authorize[$iter["pid"]]["transaction"] .= "<span class='text'>" .
                text($iter["title"] . ": " . (strterm($iter["body"], 25)) . " " . date("n/j/Y", strtotime($iter["date"]))) .
                "</span><br />\n";
            }
        }
    }

    if (empty($GLOBALS['ignore_pnotes_authorization'])) {
          //fetch pnotes information:
        if (
            $res = sqlStatement("select * from pnotes where authorized = 0 and " .
            "groupname = ?", array($groupname))
        ) {
            for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
                $result3[$iter] = $row;
            }

            if ($result3) {
                foreach ($result3 as $iter) {
                    $authorize[$iter["pid"]]["pnotes"] .= "<span class='text'>" .
                    text((strterm($iter["body"], 25)) . " " . date("n/j/Y", strtotime($iter["date"]))) .
                    "</span><br />\n";
                }
            }
        }
    }

//fetch forms information:
    if (
        $res = sqlStatement("select * from forms where authorized = 0 and " .
        "groupname = ?", array($groupname))
    ) {
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $result4[$iter] = $row;
        }

        if ($result4) {
            foreach ($result4 as $iter) {
                $authorize[$iter["pid"]]["forms"] .= "<span class='text'>" .
                text($iter["form_name"] . " " . date("n/j/Y", strtotime($iter["date"]))) .
                "</span><br />\n";
            }
        }
    }
    ?>

<table class='border-0 w-100' cellpadding='0' cellspacing='2'>
<tr>
<td valign='top'>

    <?php
    if ($authorize) {
        $count = 0;

        foreach ($authorize as $ppid => $patient) {
            $name = getPatientData($ppid);

            // If I want to see mine only and this patient is not mine, skip it.
            if ($see_auth == 2 && $_SESSION['authUserID'] != $name['id']) {
                continue;
            }

            if ($count >= $N) {
                print "<tr><td colspan='5' align='center'><a" .
                " href='authorizations_full.php?active=1' class='alert' onclick='top.restoreSession()'>" .
                xlt('Some authorizations were not displayed. Click here to view all') .
                "</a></td></tr>\n";
                break;
            }

            echo "<tr><td valign='top'>";
            // Clicking the patient name will load both frames for that patient,
            // as demographics.php takes care of loading the bottom frame.
            echo "<a href='$rootdir/patient_file/summary/demographics.php?set_pid=" .
            attr_url($ppid) . "' target='RTop' onclick='top.restoreSession()'>";

            echo "<span class='font-weight-bold'>" . text($name["fname"]) . " " .
            text($name["lname"]) . "</span></a><br />" .
            "<a class=link_submit href='authorizations.php?mode=authorize" .
            "&pid=" . attr_url($ppid) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' onclick='top.restoreSession()'>" .
            xlt('Authorize') . "</a></td>\n";

            /****
          //Michael A Rowley MD 20041012.
          // added below 4 lines to add provider to authorizations for ez reference.
          $providerID = sqlFetchArray(sqlStatement(
            "select providerID from patient_data where pid=?", array($ppid) ));
          $userID=$providerID["providerID"];
          $providerName = sqlFetchArray(sqlStatement(
            "select lname from users where id=?", array($userID) ));
            ****/
            // Don't use sqlQuery because there might be no match.
            $providerName = sqlFetchArray(sqlStatement(
                "select lname from users where id = ?",
                array($name['providerID'])
            ));

            echo "<td valign='top'><span class='font-weight-bold'>" . xlt('Provider') . ":</span><span class='text'><br />" .
              text($providerName["lname"]) . "</td>\n";
            echo "<td valign='top'><span class='font-weight-bold'>" . xlt('Billing') . ":</span><span class='text'><br />" .
              $patient["billing"] . "</td>\n";
            echo "<td valign='top'><span class='font-weight-bold'>" . xlt('Transactions') . ":</span><span class='text'><br />" .
              $patient["transaction"] . "</td>\n";
            echo "<td valign='top'><span class='font-weight-bold'>" . xlt('Patient Notes') . ":</span><span class='text'><br />" .
              $patient["pnotes"] . "</td>\n";
            echo "<td valign='top'><span class='font-weight-bold'>" . xlt('Encounter Forms') . ":</span><span class='text'><br />" .
              $patient["forms"] . "</td>\n";
            echo "</tr>\n";

            $count++;
        }
    }
    ?>

</td>

</tr>
</table>

<?php } ?>

</body>
<script>

/* added to adjust the height of this frame by the min/max buttons */
var origRows = null;
$(function () {

    $(".noterow").on("mouseover", function() { $(this).toggleClass("highlight"); });
    $(".noterow").on("mouseout", function() { $(this).toggleClass("highlight"); });
    $(".noterow").on("click", function() { EditNote(this); });

});

var EditNote = function(note) {
    var parts = note.id.split("~");
<?php if (true) : ?>
    top.restoreSession();
    location.href = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/pnotes_full.php?noteid=" + encodeURIComponent(parts[1]) + "&set_pid=" + encodeURIComponent(parts[0]) + "&active=1";
<?php else : ?>
    // no-op
    alert(<?php echo xlj('You do not have access to view/edit this note'); ?>);
<?php endif; ?>
}

</script>

</html>
