<?
include_once("../../globals.php");
include_once("$srcdir/log.inc");

include_once("$srcdir/billing.inc");
include_once("$srcdir/forms.inc");
include_once("$srcdir/pnotes.inc");
include_once("$srcdir/transactions.inc");
include_once("$srcdir/lists.inc");
include_once("$srcdir/patient.inc");


//the number of authorizations to display in the quick view:
// MAR 20041008 the full authorizations screen sucks... no links to the patient charts
// increase to a high number to make the mini frame more useful.
$N = 50;


if (isset($_GET["mode"]) && $_GET["mode"] == "authorize") {
$retVal = getProviderId($_SESSION['authUser']);	
newEvent("view",$_SESSION["authUser"],$_SESSION["authProvider"],$_GET["pid"]);
sqlStatement("update billing set authorized=1, provider_id = '".mysql_real_escape_string($retVal[0]['id']).
						"' where pid='".$_GET["pid"]."'");
sqlStatement("update forms set authorized=1 where pid='".$_GET["pid"]."'");
sqlStatement("update pnotes set authorized=1 where pid='".$_GET["pid"]."'");
sqlStatement("update transactions set authorized=1 where pid='".$_GET["pid"]."'");

}



?>

<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $bottom_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>


<a href="authorizations_full.php" target=Main><font class=title>Authorizations</font><font class=more><?echo $tmore;?>(<a class="more" style="font-size:8pt;" href="../calendar/find_patient.php?no_nav=1&mode=reset" name="Find Patients">Find Patient</a>)</font></a>


<?
//      provider
//	billing
//	forms
//	pnotes
//	transactions
														      

//fetch billing information:
if ($res = sqlStatement("select *, concat(u.fname,' ', u.lname) as user from billing LEFT JOIN users as u on billing.user = u.id where billing.authorized = 0 and billing.activity = 1 and groupname='$groupname'")) {
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$result[$iter] = $row;

if ($result) {
foreach ($result as $iter) {

$authorize{$iter{"pid"}}{"billing"} .= "<span class=text>" . $iter{"code_text"} . " " . date("n/j/Y",strtotime($iter{"date"})) . "</span><br>\n";


}

//$authorize[$iter{"pid"}]{"billing"} = substr($authorize[$iter{"pid"}]{"billing"},0,strlen($authorize[$iter{"pid"}]{"billing"}));

}
}
		

//fetch transaction information:
if ($res = sqlStatement("select * from transactions where authorized=0 and groupname='$groupname'")) {
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$result2[$iter] = $row;

if ($result2) {
foreach ($result2 as $iter) {

$authorize{$iter{"pid"}}{"transaction"} .= "<span class=text>" . $iter{"title"} . ": " . stripslashes(strterm($iter{"body"},25)) . " " . date("n/j/Y",strtotime($iter{"date"})) . "</span><br>\n";


}

//$authorize[$iter{"pid"}]{"transaction"} = substr($authorize[$iter{"pid"}]{"transaction"},0,strlen($authorize[$iter{"pid"}]{"transaction"}));

}
}


//fetch pnotes information:
if ($res = sqlStatement("select * from pnotes where authorized=0 and groupname='$groupname'")) {
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$result3[$iter] = $row;

if ($result3) {
foreach ($result3 as $iter) {

$authorize{$iter{"pid"}}{"pnotes"} .= "<span class=text>" . stripslashes(strterm($iter{"body"},25)) . " " . date("n/j/Y",strtotime($iter{"date"})) . "</span><br>\n";


}

//$authorize[$iter{"pid"}]{"pnotes"} = substr($authorize[$iter{"pid"}]{"pnotes"},0,strlen($authorize[$iter{"pid"}]{"pnotes"}));

}
}



//fetch forms information:
if ($res = sqlStatement("select * from forms where authorized=0 and groupname='$groupname'")) {
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$result4[$iter] = $row;

if ($result4) {
foreach ($result4 as $iter) {

$authorize{$iter{"pid"}}{"forms"} .= "<span class=text>" . $iter{"form_name"} . " " . date("n/j/Y",strtotime($iter{"date"})) . "</span><br>\n";


}

//$authorize[$iter{"pid"}]{"forms"} = substr($authorize[$iter{"pid"}]{"forms"},0,strlen($authorize[$iter{"pid"}]{"forms"}));

}
}




?>

<table border=0 cellpadding=0 cellspacing=2 width=100%>
<tr>
<td valign=top>


<?
if ($authorize) {

$count = 0;

while(list($ppid,$patient) = each($authorize)){
	if ($count >= $N) {
		print "<tr><td colspan=5 align=center><a target=Main href='authorizations_full.php?active=1' class=alert>Some authorizations were not displayed. Click here to view all</a></td></tr>\n";
		break;
	}
	$name = getPatientData($ppid);

	echo "<tr><td valign=top>
<a href='$rootdir/patient_file/patient_file.php?set_pid=$ppid' target=_top><span class=bold>". $name{"fname"} . " " . $name{"lname"} ."</span></a><br><a class=link_submit href='authorizations.php?mode=authorize&pid=$ppid'>Authorize</a></td>\n";
	//Michael A Rowley MD 20041012.
	// added below 4 lines to add provider to authorizations for ez reference.
	$providerID=sqlFetchArray(sqlStatement("select providerID from patient_data where pid=$ppid"));
	$userID=$providerID{"providerID"};
	$providerName=sqlFetchArray(sqlStatement("select lname from users where id=$userID"));
	echo "<td valign=top><span class=bold>Provider:</span><span class=text><br>" . $providerName{"lname"} . "</td>\n";
	//  ha ha, see if that works....mar.
	echo "<td valign=top><span class=bold>Billing:</span><span class=text><br>" . $patient{"billing"} . "</td>\n";

	echo "<td valign=top><span class=bold>Transactions:</span><span class=text><br>" . $patient{"transaction"} . "</td>\n";

	echo "<td valign=top><span class=bold>Patient Notes:</span><span class=text><br>" . $patient{"pnotes"} . "</td>\n";

	echo "<td valign=top><span class=bold>Encounter Forms:</span><span class=text><br>" . $patient{"forms"} . "</td>\n";
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
