<?php

/**
 * Authorizations full script.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/patient.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\Header;

if (isset($_GET["mode"]) && $_GET["mode"] == "authorize") {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    EventAuditLogger::instance()->newEvent("authorize", $_SESSION["authUser"], $_SESSION["authProvider"], 1, '', $_GET["pid"]);
    sqlStatement("update billing set authorized=1 where pid=?", array($_GET["pid"]));
    sqlStatement("update forms set authorized=1 where pid=?", array($_GET["pid"]));
    sqlStatement("update pnotes set authorized=1 where pid=?", array($_GET["pid"]));
    sqlStatement("update transactions set authorized=1 where pid=?", array($_GET["pid"]));
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
if ($res = sqlStatement("select *, concat(u.fname,' ', u.lname) as user from billing LEFT JOIN users as u on billing.user = u.id where billing.authorized=0 and groupname=?", array ($groupname))) {
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $result[$iter] = $row;
    }

    if (!empty($result)) {
        foreach ($result as $iter) {
            $authorize[$iter["pid"]]["billing"] .= "<span class=small>" .
              text($iter["user"]) . ": </span><span class=text>" .
              text($iter["code_text"] . " " . date("n/j/Y", strtotime($iter["date"]))) .
              "</span><br />\n";
        }
    }
}

//fetch transaction information:
if ($res = sqlStatement("select * from transactions where authorized=0 and groupname=?", array($groupname))) {
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $result2[$iter] = $row;
    }

    if (!empty($result2)) {
        foreach ($result2 as $iter) {
            $authorize[$iter["pid"]]["transaction"] .= "<span class=small>" .
              text($iter["user"]) . ": </span><span class=text>" .
              text($iter["title"] . ": " . strterm($iter["body"], 25) . " " . date("n/j/Y", strtotime($iter["date"]))) .
              "</span><br />\n";
        }
    }
}

if (empty($GLOBALS['ignore_pnotes_authorization'])) {
  //fetch pnotes information, exclude ALL deleted notes
    if ($res = sqlStatement("select * from pnotes where authorized=0 and deleted!=1 and groupname=?", array($groupname))) {
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $result3[$iter] = $row;
        }

        if ($result3) {
            foreach ($result3 as $iter) {
                $authorize[$iter["pid"]]["pnotes"] .= "<span class=small>" .
                text($iter["user"]) . ": </span><span class=text>" .
                text(strterm($iter["body"], 25) . " " . date("n/j/Y", strtotime($iter["date"]))) .
                "</span><br />\n";
            }
        }
    }
}

//fetch forms information:
if ($res = sqlStatement("select * from forms where authorized=0 and groupname=?", array($groupname))) {
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $result4[$iter] = $row;
    }

    if (!empty($result4)) {
        foreach ($result4 as $iter) {
            $authorize[$iter["pid"]]["forms"] .= "<span class=small>" .
              text($iter["user"]) . ": </span><span class=text>" .
              text($iter["form_name"] . " " . date("n/j/Y", strtotime($iter["date"]))) .
              "</span><br />\n";
        }
    }
}
?>

<table border=0 cellpadding=0 cellspacing=2 width=100%>
<tr>
<td valign=top>

<?php
if (!empty($authorize)) {
    foreach ($authorize as $ppid => $patient) {
        $name = getPatientData($ppid);

        echo "<tr><td valign=top><span class=bold>" . text($name["fname"] . " " . $name["lname"]) .
             "</span><br /><a class=link_submit href='authorizations_full.php?mode=authorize&pid=" .
             attr_url($ppid) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' onclick='top.restoreSession()'>" . xlt('Authorize') . "</a></td>\n";
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
}
?>

</td>

</tr>
</table>

</body>
</html>
