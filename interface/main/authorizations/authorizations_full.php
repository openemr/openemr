<?php

/**
 * Authorizations full script.
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

require_once(OEGlobalsBag::getInstance()->getSrcDir() . "/patient.inc.php");

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$globals = OEGlobalsBag::getInstance();
$groupname = $globals->get('groupname', '');
$tback = $globals->getString('tback');
$authorize = [];
$result = [];
$result2 = [];
$result3 = [];
$result4 = [];
$emptyRow = ['billing' => '', 'transaction' => '', 'pnotes' => '', 'forms' => ''];

if (isset($_GET["mode"]) && $_GET["mode"] == "authorize") {
    CsrfUtils::checkCsrfInput(INPUT_GET, dieOnFail: true);

    EventAuditLogger::getInstance()->newEvent("authorize", $session->get('authUser'), $session->get('authProvider'), 1, '', $_GET["pid"]);
    sqlStatement("update billing set authorized=1 where pid=?", [$_GET["pid"]]);
    sqlStatement("update forms set authorized=1 where pid=?", [$_GET["pid"]]);
    sqlStatement("update pnotes set authorized=1 where pid=?", [$_GET["pid"]]);
    sqlStatement("update transactions set authorized=1 where pid=?", [$_GET["pid"]]);
}
?>
<html>
<head>
<?php Header::setupHeader(); ?>
</head>
<body class="body_top">

<a href="authorizations.php" onclick='top.restoreSession()'>
<font class=title><?php echo xlt('Authorizations'); ?></font>
<font class=more><?php echo text($tback); ?></font></a>

<?php
//  billing
//  forms
//  pnotes
//  transactions

//fetch billing information:
if ($res = sqlStatement("select *, concat(u.fname,' ', u.lname) as user from billing LEFT JOIN users as u on billing.user = u.id where billing.authorized=0 and groupname=?", [$groupname])) {
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $result[$iter] = $row;
    }

    foreach ($result as $iter) {
        $pid = (int) $iter["pid"];
        $authorize[$pid] ??= $emptyRow;
        $authorize[$pid]["billing"] .= "<span class=small>" .
          text($iter["user"]) . ": </span><span class=text>" .
          text($iter["code_text"] . " " . date("n/j/Y", strtotime((string) $iter["date"]))) .
          "</span><br />\n";
    }
}

//fetch transaction information:
if ($res = sqlStatement("select * from transactions where authorized=0 and groupname=?", [$groupname])) {
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $result2[$iter] = $row;
    }

    foreach ($result2 as $iter) {
        $pid = (int) $iter["pid"];
        $authorize[$pid] ??= $emptyRow;
        $authorize[$pid]["transaction"] .= "<span class=small>" .
          text($iter["user"]) . ": </span><span class=text>" .
          text($iter["title"] . ": " . strterm((string) $iter["body"], 25) . " " . date("n/j/Y", strtotime((string) $iter["date"]))) .
          "</span><br />\n";
    }
}

if (!OEGlobalsBag::getInstance()->getBoolean('ignore_pnotes_authorization')) {
  //fetch pnotes information, exclude ALL deleted notes
    if ($res = sqlStatement("select * from pnotes where authorized=0 and deleted!=1 and groupname=?", [$groupname])) {
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $result3[$iter] = $row;
        }

        foreach ($result3 as $iter) {
            $pid = (int) $iter["pid"];
            $authorize[$pid] ??= $emptyRow;
            $authorize[$pid]["pnotes"] .= "<span class=small>" .
            text($iter["user"]) . ": </span><span class=text>" .
            text(strterm((string) $iter["body"], 25) . " " . date("n/j/Y", strtotime((string) $iter["date"]))) .
            "</span><br />\n";
        }
    }
}

//fetch forms information:
if ($res = sqlStatement("select * from forms where authorized=0 and groupname=?", [$groupname])) {
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $result4[$iter] = $row;
    }

    foreach ($result4 as $iter) {
        $pid = (int) $iter["pid"];
        $authorize[$pid] ??= $emptyRow;
        $authorize[$pid]["forms"] .= "<span class=small>" .
          text($iter["user"]) . ": </span><span class=text>" .
          text($iter["form_name"] . " " . date("n/j/Y", strtotime((string) $iter["date"]))) .
          "</span><br />\n";
    }
}
?>

<table border=0 cellpadding=0 cellspacing=2 width=100%>
<tr>
<td valign=top>

<?php
foreach ($authorize as $ppid => $patient) {
    $name = getPatientData($ppid);

    echo "<tr><td valign=top><span class=bold>" . text($name["fname"] . " " . $name["lname"]) .
         "</span><br /><a class=link_submit href='authorizations_full.php?mode=authorize&pid=" .
         attr_url($ppid) . "&csrf_token_form=" . CsrfUtils::collectCsrfToken(session: $session) . "' onclick='top.restoreSession()'>" . xlt('Authorize') . "</a></td>\n";
    echo "<td valign=top><span class=bold>" . xlt('Billing') .
         ":</span><span class=text><br />" . $patient["billing"] . "</td>\n";
    echo "<td valign=top><span class=bold>" . xlt('Transactions') .
         ":</span><span class=text><br />" . $patient["transaction"] . "</td>\n";
    echo "<td valign=top><span class=bold>" . xlt('Patient Notes') .
         ":</span><span class=text><br />" . $patient["pnotes"] . "</td>\n";
    echo "<td valign=top><span class=bold>" . xlt('Encounter Forms') .
         ":</span><span class=text><br />" . $patient["forms"] . "</td>\n";
    echo "</tr>\n";
}
?>

</td>

</tr>
</table>

</body>
</html>
