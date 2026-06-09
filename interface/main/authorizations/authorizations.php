<?php

/**
 * Authorizations script.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;

$srcDir = OEGlobalsBag::getInstance()->getSrcDir();
require_once("$srcDir/forms.inc.php");
require_once("$srcDir/transactions.inc.php");
require_once("$srcDir/lists.inc.php");
require_once("$srcDir/patient.inc.php");
require_once("$srcDir/options.inc.php");

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$globals = OEGlobalsBag::getInstance();
$groupname = $globals->get('groupname', '');
$tmore = $globals->getString('tmore');
$rootDir = $globals->getString('rootdir');
$authorize = [];
$result1 = [];
$result2 = [];
$result3 = [];
$result4 = [];
$emptyRow = ['billing' => '', 'transaction' => '', 'pnotes' => '', 'forms' => ''];

// The number of authorizations to display in the quick view:
// MAR 20041008 the full authorizations screen sucks... no links to the patient charts
// increase to a high number to make the mini frame more useful.
$N = 50;

$atemp = sqlQuery("SELECT see_auth FROM users WHERE username = ?", [$session->get('authUser')]);
$see_auth = is_array($atemp) ? ($atemp['see_auth'] ?? 0) : 0;

$imauthorized = $session->get('userauthorized') || $see_auth > 2;

// This authorizes everything for the specified patient.
if (isset($_GET["mode"]) && $_GET["mode"] == "authorize" && $imauthorized) {
    CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);

    $retVal = getProviderId($session->get('authUser'));
    EventAuditLogger::getInstance()->newEvent("authorize", $session->get("authUser"), $session->get("authProvider"), 1, $_GET["pid"]);
    sqlStatement("update billing set authorized=1 where pid=?", [$_GET["pid"]]);
    sqlStatement("update forms set authorized=1 where pid=?", [$_GET["pid"]]);
    sqlStatement("update pnotes set authorized=1 where pid=?", [$_GET["pid"]]);
    sqlStatement("update transactions set authorized=1 where pid=?", [$_GET["pid"]]);
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
    <img src="<?php echo OEGlobalsBag::getInstance()->getKernel()->getImagesRelative(); ?>/max.gif" />
</div>
<div id="min" title="Minimize this information">
    <img src="<?php echo OEGlobalsBag::getInstance()->getKernel()->getImagesRelative(); ?>/min.gif" />
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
        "groupname = ?", [$groupname])
    ) {
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $result1[$iter] = $row;
        }

        foreach ($result1 as $iter) {
            $pid = (int) $iter["pid"];
            $authorize[$pid] ??= $emptyRow;
            $authorize[$pid]["billing"] .= "<span class='text'>" .
            text($iter["code_text"] . " " . date("n/j/Y", strtotime((string) $iter["date"]))) .
            "</span><br />\n";
        }
    }

//fetch transaction information:
    if (
        $res = sqlStatement("select * from transactions where " .
        "authorized = 0 and groupname = ?", [$groupname])
    ) {
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $result2[$iter] = $row;
        }

        foreach ($result2 as $iter) {
            $pid = (int) $iter["pid"];
            $authorize[$pid] ??= $emptyRow;
            $authorize[$pid]["transaction"] .= "<span class='text'>" .
            text($iter["title"] . ": " . strterm((string) $iter["body"], 25) . " " . date("n/j/Y", strtotime((string) $iter["date"]))) .
            "</span><br />\n";
        }
    }

    if (!OEGlobalsBag::getInstance()->getBoolean('ignore_pnotes_authorization')) {
          //fetch pnotes information:
        if (
            $res = sqlStatement("select * from pnotes where authorized = 0 and " .
            "groupname = ?", [$groupname])
        ) {
            for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
                $result3[$iter] = $row;
            }

            foreach ($result3 as $iter) {
                $pid = (int) $iter["pid"];
                $authorize[$pid] ??= $emptyRow;
                $authorize[$pid]["pnotes"] .= "<span class='text'>" .
                text(strterm((string) $iter["body"], 25) . " " . date("n/j/Y", strtotime((string) $iter["date"]))) .
                "</span><br />\n";
            }
        }
    }

//fetch forms information:
    if (
        $res = sqlStatement("select * from forms where authorized = 0 and " .
        "groupname = ?", [$groupname])
    ) {
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $result4[$iter] = $row;
        }

        foreach ($result4 as $iter) {
            $pid = (int) $iter["pid"];
            $authorize[$pid] ??= $emptyRow;
            $authorize[$pid]["forms"] .= "<span class='text'>" .
            text($iter["form_name"] . " " . date("n/j/Y", strtotime((string) $iter["date"]))) .
            "</span><br />\n";
        }
    }
    ?>

<table class='border-0 w-100' cellpadding='0' cellspacing='2'>
<tr>
<td valign='top'>

    <?php
    $count = 0;
    foreach ($authorize as $ppid => $patient) {
        $name = getPatientData($ppid);

        // If I want to see mine only and this patient is not mine, skip it.
        if ($see_auth == 2 && $session->get('authUserID') != $name['id']) {
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
        echo "<a href='$rootDir/patient_file/summary/demographics.php?set_pid=" .
        attr_url($ppid) . "' target='RTop' onclick='top.restoreSession()'>";

        echo "<span class='font-weight-bold'>" . text($name["fname"]) . " " .
        text($name["lname"]) . "</span></a><br />" .
        "<a class=link_submit href='authorizations.php?mode=authorize" .
        "&pid=" . attr_url($ppid) . "&csrf_token_form=" . CsrfUtils::collectCsrfToken(session: $session) . "' onclick='top.restoreSession()'>" .
        xlt('Authorize') . "</a></td>\n";

        // Don't use sqlQuery because there might be no match.
        $providerName = sqlFetchArray(sqlStatement(
            "select lname from users where id = ?",
            [$name['providerID']]
        ));
        $providerLname = is_array($providerName) ? ($providerName['lname'] ?? '') : '';

        echo "<td valign='top'><span class='font-weight-bold'>" . xlt('Provider') . ":</span><span class='text'><br />" .
          text($providerLname) . "</td>\n";
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
    const params = new URLSearchParams({
        active: '1',
        noteid: parts[1],
        set_pid: parts[0]
    });
    location.href = "<?php echo OEGlobalsBag::getInstance()->getWebRoot(); ?>/interface/patient_file/summary/pnotes_full.php?" + params;
<?php else : ?>
    // no-op
    alert(<?php echo xlj('You do not have access to view/edit this note'); ?>);
<?php endif; ?>
}

</script>

</html>
