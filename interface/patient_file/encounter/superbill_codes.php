<?
include_once("../../globals.php");
include_once("$srcdir/billing.inc");
include_once("$srcdir/sql.inc");

define("CODE_TYPE_CPT4",1);
define("CODE_TYPE_ICD9",2);
define("CODE_TYPE_HCPCS",3);

$code_type_array = array("","CPT4","ICD9","HCPCS");

//the number of rows to display before resetting and starting a new column:
$N=10;
$mode = $_GET['mode'];
if (isset($mode)) {
	if ($mode == "add") {
		if (strtolower($type) == "copay") {
			addBilling($encounter, $type, sprintf("%01.2f", $code), $text, $pid, $userauthorized,$_SESSION['authUserID'],$modifier,$units,sprintf("%01.2f", 0 - $code));
		}
		elseif (strtolower($type) == "other") {
			addBilling($encounter, $type, $code, $text, $pid, $userauthorized,$_SESSION['authUserID'],$modifier,$units,sprintf("%01.2f", $fee));
		}
		else {
			addBilling($encounter, $type, $code, $text, $pid, $userauthorized,$_SESSION['authUserID'],$modifier,$units,$fee);
		}
	}
}
?>

<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

</head>
<body <?echo $bottom_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>


<table border=0 cellspacing=0 cellpadding=0 >
<tr>
<!--<td background="<?echo $linepic;?>" width=7 height=100%>
&nbsp;
</td>-->
<td valign=top>

<dl>

<dt><a href="superbill_custom_full.php" ><span class=title>Superbill</span><font class=more><?echo $tmore;?></font></a>
<a href="patient_encounter.php?codefrom=superbill" ><font class=more><?echo $tback;?></font></a></dt>
</td></tr>
</table>

<table border=0 width=100% cellpadding=0 cellspacing=1>
<?
$res = sqlStatement("select * from codes where superbill = 1 order by code_type,code,code_text");
$cpt_count = 0;
$icd9_count = 0;
$hcpcs_count = 0;
for ($iter = 0;$row = sqlFetchArray($res);$iter++){
	//$all[$iter] = $row;
	switch($row["code_type"]){
		case CODE_TYPE_CPT4:
			$cpts[$cpt_count++] = $row;
			break;
		case CODE_TYPE_ICD9:
			$icd9s[$icd9_count++] = $row;
			break;
		case CODE_TYPE_HCPCS:
			$hcpcs[$hcpcs_count++] = $row;
			break;
	}
}
$count=0;
$index=0;
if ($cpt_count > 0 || $icd9_count > 0 || $hcpcs_count > 0) {
	print "<tr><th align=left>CPT Codes</th><th align=left>ICD9 Codes</th><th align=left>HCPCS Codes</th><tr>";
	
	while( $count < $iter ){
		print "<tr><td valign=top>";
		if( !empty( $cpts[$index] ) ){
			$code = $cpts[$index];
			print "<dd><a class=text target=Main href='superbill_codes.php?back=1&mode=add&type=".urlencode($code_type_array[$code{"code_type"}])."&modifier=".urlencode($code{"modifier"})."&units=".urlencode($code{"units"})."&fee=".urlencode($code{"fee"})."&code=".urlencode($code{"code"})."&text=".urlencode($code{"code_text"})."'>";
			echo "<b>" . $code['code'] . "</b>" . "&nbsp;" . $code['modifier'] . "&nbsp;" . $code['code_text'] ;
			echo "</a></dd>\n";
			++$count;
		}
		print "</td><td valign=top>";
		if( !empty( $icd9s[$index] ) ){
			$code = $icd9s[$index];
			print "<dd><a class=text target=Main href='superbill_codes.php?back=1&mode=add&type=".urlencode($code_type_array[$code{"code_type"}])."&modifier=".urlencode($code{"modifier"})."&units=".urlencode($code{"units"})."&fee=".urlencode($code{"fee"})."&code=".urlencode($code{"code"})."&text=".urlencode($code{"code_text"})."'>";
			echo "<b>" . $code['code'] . "</b>" . "&nbsp;" . $code['modifier'] . "&nbsp;" . $code['code_text'] ;
			echo "</a></dd>\n";
			++$count;
		}
		print "</td><td valign=top>";
		if( !empty( $hcpcs[$index] ) ){
			$code = $hcpcs[$index];
			print "<dd><a class=text target=Main href='superbill_codes.php?back=1&mode=add&type=".urlencode($code_type_array[$code{"code_type"}])."&modifier=".urlencode($code{"modifier"})."&units=".urlencode($code{"units"})."&fee=".urlencode($code{"fee"})."&code=".urlencode($code{"code"})."&text=".urlencode($code{"code_text"})."'>";
			echo "<b>" . $code['code'] . "</b>" . "&nbsp;" . $code['modifier'] . "&nbsp;" . $code['code_text'] ;
			echo "</a></dd>\n";
			++$count;
		}
		print "</td></tr>";
		++$index;
	}
	/*foreach($all as $iter) {
		$count++;
		print "<dd><a class=text target=Diagnosis href='diagnosis.php?mode=add&type=".urlencode($code_type_array[$iter{"code_type"}])."&modifier=".urlencode($iter{"modifier"})."&units=".urlencode($iter{"units"})."&fee=".urlencode($iter{"fee"})."&code=".urlencode($iter{"code"})."&text=".urlencode($iter{"code_text"})."'>";
		echo "<b>" . $iter['code'] . "</b>" . "&nbsp;" . $iter['code_text'] ;
		echo "</a></dd>\n";
		if ($count == $N) {
			print "</td><td valign=top>\n";
			$count = 0;
		}
	}
	*/
}

?>



</table>



</dl>


<!--</td>
</tr>-->
<!--</table>-->




</body>
</html>
