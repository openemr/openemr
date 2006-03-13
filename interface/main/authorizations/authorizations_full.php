<?
include_once("../../globals.php");

include_once("$srcdir/patient.inc");




if (isset($_GET["mode"]) && $_GET["mode"] == "authorize") {
newEvent("view",$_SESSION["authUser"],$_SESSION["authProvider"],$_GET["pid"]);
sqlStatement("update billing set authorized=1 where pid='".$_GET["pid"]."'");
sqlStatement("update forms set authorized=1 where pid='".$_GET["pid"]."'");
sqlStatement("update pnotes set authorized=1 where pid='".$_GET["pid"]."'");
sqlStatement("update transactions set authorized=1 where pid='".$_GET["pid"]."'");

}



?>

<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<a href="../main.php" target=Main><font class=title>Authorizations</font><font class=more><?echo $tback;?></font></a>

<?
//	billing
//	forms
//	pnotes
//	transactions

//fetch billing information:
if ($res = sqlStatement("select *, concat(u.fname,' ', u.lname) as user from billing LEFT JOIN users as u on billing.user = u.id where billing.authorized=0 and groupname='$groupname'")) {
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
		$result[$iter] = $row;

if ($result) {
foreach ($result as $iter) {

$authorize{$iter{"pid"}}{"billing"} .= "<span class=small>" . $iter{"user"} . ": </span><span class=text>" . $iter{"code_text"} . " " . date("n/j/Y",strtotime($iter{"date"})) . "</span><br>\n";


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

$authorize{$iter{"pid"}}{"transaction"} .= "<span class=small>" . $iter{"user"} . ": </span><span class=text>" . $iter{"title"} . ": " . strterm($iter{"body"},25) . " " . date("n/j/Y",strtotime($iter{"date"})) . "</span><br>\n";


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

$authorize{$iter{"pid"}}{"pnotes"} .= "<span class=small>" . $iter{"user"} . ": </span><span class=text>" . strterm($iter{"body"},25) . " " . date("n/j/Y",strtotime($iter{"date"})) . "</span><br>\n";


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

$authorize{$iter{"pid"}}{"forms"} .= "<span class=small>" . $iter{"user"} . ": </span><span class=text>" . $iter{"form_name"} . " " . date("n/j/Y",strtotime($iter{"date"})) . "</span><br>\n";


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

while(list($ppid,$patient) = each($authorize)){
	
	$name = getPatientData($ppid);
	
	echo "<tr><td valign=top><span class=bold>". $name{"fname"} . " " . $name{"lname"} ."</span><br><a class=link_submit href='authorizations.php?mode=authorize&pid=$ppid'>".xl('Authorize')."</a></td>\n";
	echo "<td valign=top><span class=bold>".xl('Billing').":</span><span class=text><br>" . $patient{"billing"} . "</td>\n";
	
	echo "<td valign=top><span class=bold>".xl('Transactions').":</span><span class=text><br>" . $patient{"transaction"} . "</td>\n";
	
	echo "<td valign=top><span class=bold>".xl('Patient Notes').":</span><span class=text><br>" . $patient{"pnotes"} . "</td>\n";
	
	echo "<td valign=top><span class=bold>".xl('Encounter Forms').":</span><span class=text><br>" . $patient{"forms"} . "</td>\n";
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
