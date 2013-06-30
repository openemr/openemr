<?php
/**
 * Authorizations full script.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

include_once("../../globals.php");
include_once("$srcdir/patient.inc");

if (isset($_GET["mode"]) && $_GET["mode"] == "authorize") {
newEvent("authorize",$_SESSION["authUser"],$_SESSION["authProvider"],1,$_GET["pid"]);
sqlStatement("update billing set authorized=1 where pid=?", array($_GET["pid"]) );
sqlStatement("update forms set authorized=1 where pid=?", array($_GET["pid"]) );
sqlStatement("update pnotes set authorized=1 where pid=?", array($_GET["pid"]) );
sqlStatement("update transactions set authorized=1 where pid=?", array($_GET["pid"]) );

}
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">

<?php if ($GLOBALS['concurrent_layout']) { ?>
<a href="authorizations.php" onclick='top.restoreSession()'>
<?php } else { ?>
<a href="../main.php" target=Main>
<?php } ?>
<font class=title><?php echo htmlspecialchars(xl('Authorizations'),ENT_NOQUOTES); ?></font>
<font class=more><?php echo htmlspecialchars($tback,ENT_NOQUOTES); ?></font></a>

<?php
//	billing
//	forms
//	pnotes
//	transactions

//fetch billing information:
if ($res = sqlStatement("select *, concat(u.fname,' ', u.lname) as user from billing LEFT JOIN users as u on billing.user = u.id where billing.authorized=0 and groupname=?", array ($groupname) )) {
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$result[$iter] = $row;

if ($result) {
foreach ($result as $iter) {

$authorize{$iter{"pid"}}{"billing"} .= "<span class=small>" .
      htmlspecialchars($iter{"user"},ENT_NOQUOTES) . ": </span><span class=text>" .
      htmlspecialchars($iter{"code_text"} . " " . date("n/j/Y",strtotime($iter{"date"})),ENT_NOQUOTES) .
      "</span><br>\n";

}

}
}

//fetch transaction information:
if ($res = sqlStatement("select * from transactions where authorized=0 and groupname=?", array($groupname) )) {
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$result2[$iter] = $row;

if ($result2) {
foreach ($result2 as $iter) {

$authorize{$iter{"pid"}}{"transaction"} .= "<span class=small>" .
      htmlspecialchars($iter{"user"},ENT_NOQUOTES) . ": </span><span class=text>" .
      htmlspecialchars($iter{"title"} . ": " . strterm($iter{"body"},25) . " " . date("n/j/Y",strtotime($iter{"date"})),ENT_NOQUOTES) .
      "</span><br>\n";

}

}
}

if (empty($GLOBALS['ignore_pnotes_authorization'])) {
  //fetch pnotes information, exclude ALL deleted notes
  if ($res = sqlStatement("select * from pnotes where authorized=0 and deleted!=1 and groupname=?", array($groupname) )) {
    for ($iter = 0;$row = sqlFetchArray($res);$iter++) $result3[$iter] = $row;
    if ($result3) {
      foreach ($result3 as $iter) {
        $authorize{$iter{"pid"}}{"pnotes"} .= "<span class=small>" .
          htmlspecialchars($iter{"user"},ENT_NOQUOTES) . ": </span><span class=text>" .
          htmlspecialchars(strterm($iter{"body"},25) . " " . date("n/j/Y",strtotime($iter{"date"})),ENT_NOQUOTES) .
	  "</span><br>\n";
      }
    }
  }
}

//fetch forms information:
if ($res = sqlStatement("select * from forms where authorized=0 and groupname=?", array($groupname) )) {
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$result4[$iter] = $row;

if ($result4) {
foreach ($result4 as $iter) {

$authorize{$iter{"pid"}}{"forms"} .= "<span class=small>" .
      htmlspecialchars($iter{"user"},ENT_NOQUOTES) . ": </span><span class=text>" .
      htmlspecialchars($iter{"form_name"} . " " . date("n/j/Y",strtotime($iter{"date"})),ENT_NOQUOTES) .
      "</span><br>\n";

}

}
}
?>

<table border=0 cellpadding=0 cellspacing=2 width=100%>
<tr>
<td valign=top>

<?php
if ($authorize) {

while(list($ppid,$patient) = each($authorize)){
	
	$name = getPatientData($ppid);
	
	echo "<tr><td valign=top><span class=bold>". htmlspecialchars($name{"fname"} . " " . $name{"lname"},ENT_NOQUOTES) .
             "</span><br><a class=link_submit href='authorizations_full.php?mode=authorize&pid=" .
             htmlspecialchars($ppid,ENT_QUOTES) . "' onclick='top.restoreSession()'>" . htmlspecialchars(xl('Authorize'),ENT_NOQUOTES) . "</a></td>\n";
	echo "<td valign=top><span class=bold>".htmlspecialchars(xl('Billing'),ENT_NOQUOTES).
             ":</span><span class=text><br>" . $patient{"billing"} . "</td>\n";
	echo "<td valign=top><span class=bold>".htmlspecialchars(xl('Transactions'),ENT_NOQUOTES).
             ":</span><span class=text><br>" . $patient{"transaction"} . "</td>\n";
	echo "<td valign=top><span class=bold>".htmlspecialchars(xl('Patient Notes'),ENT_NOQUOTES).
             ":</span><span class=text><br>" . $patient{"pnotes"} . "</td>\n";
	echo "<td valign=top><span class=bold>".htmlspecialchars(xl('Encounter Forms'),ENT_NOQUOTES).
             ":</span><span class=text><br>" . $patient{"forms"} . "</td>\n";
	echo "</tr>\n";
	$count++;
}
}
?>

</td>

</tr>
</table>

</body>
</html>
