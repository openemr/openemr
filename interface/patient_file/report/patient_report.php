<?
include_once("../../globals.php");

?>

<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">


</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>


<font class=title>Patient Report</font><br>

<a class="link_submit" href="full_report.php">[View Comprehensive Patient Report]</a>

<form name=report_form method=post action=custom_report.php>

<table><tr><td>
<input type=checkbox name=include_demographics value="demographics" checked><span class=text>Demographics</span><br>
<input type=checkbox name=include_history value="history"><span class=text>History</span><br>
<input type=checkbox name=include_employer value="employer"><span class=text>Employer</span><br>
<input type=checkbox name=include_insurance value="insurance"><span class=text>Insurance</span><br>
<input type=checkbox name=include_billing value="billing" checked><span class=text>Billing</span><br>
</td><td>
<input type=checkbox name=include_allergies value="allergies"><span class=text>Allergies</span><br>
<input type=checkbox name=include_medications value="medications"><span class=text>Medications</span><br>
<input type=checkbox name=include_immunizations value="immunizations"><span class=text>Immunizations</span><br>
<input type=checkbox name=include_notes value="notes"><span class=text>Patient Notes</span><br>
<input type=checkbox name=include_transactions value="transactions"><span class=text>Transactions</span><br>
</td></tr></table>
<br>
<a href="javascript:document.report_form.submit();" class=link_submit>Generate Report</a>
<hr>
<span class=bold>Select the Encounter Forms to Include in this Report:</span>
<br>
<?
$last_encounter = 1;
$in_last_encounter = 0;
$isfirst=1;
$res = sqlStatement("select * from forms where pid='$pid' order by date DESC");
while($result = sqlFetchArray($res)) {
	if ($result{"form_name"} == "New Patient Encounter") {
		if ($isfirst==0) {
		print "</blockquote>\n\n";
		}
		$isfirst=0;
		print "<br><input type=checkbox name='".$result{"formdir"}."[]' value='".$result{"form_id"}.":".$result{"encounter"}."'";
		if($last_encounter === 1) {
			print " checked";
			$in_last_encounter = 1;
			$last_encounter = 0;
		}
		else {
			$in_last_encounter = 0;
		}
		print " ><span class=bold>".$result{"form_name"}." </span><span class=text>(".date("Y-m-d",strtotime($result{"date"})).")</span><br><blockquote>\n";
	} else {
		print "<input type=checkbox name='".$result{"formdir"}."[]' value='".$result{"form_id"}.":".$result{"encounter"}."'";
		/*******
		**	Comment this out if you want the indented sub encounters checked by default
		** as well.
		if($in_last_encounter === 1) {
			print "checked";
		}
		*/
		print "><span class=bold>".$result{"form_name"}." </span><span class=text>(".date("Y-m-d",strtotime($result{"date"})).")</span><br>\n";
	}
	//call_user_func($result{"formdir"} . "_report", $pid, $result{"encounter"}, $cols, $result{"form_id"});
}
?>
</blockquote>
<span class="bold">Documents</span>:<br>
<ul>
<?

//code lists available images
$db = $GLOBALS['adodb']['db'];
$sql = "SELECT d.id,d.url,c.name from documents as d LEFT JOIN categories_to_documents as ctd on d.id=ctd.document_id LEFT JOIN categories as c on c.id = ctd.category_id where d.foreign_id = " . $db->qstr($pid);
$result = $db->Execute($sql);
echo $db->ErrorMsg();
while ($result && !$result->EOF) {
	echo '<span class="bold"><input type="checkbox" name="documents[]" value="' . $result->fields['id'] . '">';
	echo '&nbsp;&nbsp;<i>' . $result->fields['name'] . "</i>";
	echo '&nbsp;&nbsp;Name: <i>' . basename($result->fields['url']) . "</i>";
	echo '</span><br>';
	$result->MoveNext();	
}

?>
</ul>
</form>

<a href="javascript:document.report_form.submit();" class=link_submit>Generate Report</a>



</body>
</html>
