<?
include_once("../../globals.php");
define("CODE_TYPE_CPT4",1);
define("CODE_TYPE_ICD9",2);
define("CODE_TYPE_HCPCS",3);

$code_type_array = array("","CPT4","ICD9","HCPCS");
include_once("$srcdir/sql.inc");

//the maximum number of records to pull out with the search:
$M = 30;

//the number of records to display before starting a second column:
$N = 15;
?>

<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $bottom_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>


<table border=0 cellspacing=0 cellpadding=0 height=100%>
<tr>
<td background="<?echo $linepic;?>" width=7 height=100%>
&nbsp;
</td>
<td valign=top>


<form name=icd9_form method=post action=cptcm_codes.php>
<input type=hidden name=mode value="search">

<span class="title" href="icd9cm_codes.php">CPT Codes</span><br>

<input type=entry size=15 name=text><a href="javascript:document.icd9_form.submit();" class="text">Search</a>
</form>

<?
if (isset($_POST["mode"]) && $_POST["mode"] == "search") {
	$sql = "select * from codes where (code_text like '%".$_POST["text"]."%' or code like '%".$_POST["text"]."%') and code_type = '" . CODE_TYPE_CPT4 . "' order by code limit " . ($M + 1);
	
if ($res = sqlStatement($sql) ) {
	for($iter=0; $row=sqlFetchArray($res); $iter++)
	{
		$result[$iter] = $row;
	}

?>


<table><tr><td valign=top>
<?
$count = 0;
$total = 0;
if ($result) {
foreach ($result as $iter) {
if ($count == $N) {
echo "</td><td valign=top>\n";
$count = 0;
}

echo "<a target=Diagnosis class=text href='diagnosis.php?mode=add&type=".urlencode($code_type_array[$iter{"code_type"}])."&code=".urlencode($iter{"code"})."&modifier=".urlencode($iter{"modifier"})."&units=".urlencode($iter{"units"})."&fee=".urlencode($iter{"fee"})."&text=".urlencode($iter{"code_text"})."'>".ucwords("<b>" . strtoupper($iter{"code"}) . "&nbsp;" . $iter['modifier'] . "</b>" . " " . strtolower($iter{"code_text"}))."</a><br>\n";
//echo ucwords(strtolower($iter{"name"})) . "<br>\n";
$count++;
$total++;
if ($total == $M) {
echo "</span><span class=alert>Some codes were not displayed.\n";
break;
}
}
}
?>
</td></tr></table>


<?
}
}
?>



</td>
</tr>
</table>




</body>
</html>
