<?
include_once("../../globals.php");

include_once("$srcdir/forms.inc");
include_once("$srcdir/billing.inc");
include_once("$srcdir/pnotes.inc");
include_once("$srcdir/patient.inc");
include_once("$srcdir/report.inc");

if (!isset($_GET["viewnum"])) {
	$N = 6;
} else {
	$N = $_GET["viewnum"];
}

?>

<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">


</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<a href="patient_report.php"><font class="title"><? xl('Patient Record Report','e'); ?></font><font class=back><?echo $tback;?></font></a>
<br><br>
<a href="full_report.php?viewnum=6" class=link_submit>[<? xl('Normal View','e'); ?>]</a><a href="full_report.php?viewnum=1" class=link_submit>[Expanded View]</a><br>

<br>




<font class=bold><? xl('Patient Data','e'); >?:</font><br>
<?
printRecData($patient_data_array, getRecPatientData ($pid), $N);
?><hr>

<font class=bold><? xl('History Data','e'); ?>:</font><br>
<?
printRecData($history_data_array, getRecHistoryData ($pid), $N);
?><hr>


<font class=bold><? xl('Employer Data','e'); ?>:</font><br>
<?
printRecData($employer_data_array, getRecEmployerData ($pid), $N);
?><hr>


<font class=bold><? xl('Primary Insurance Data','e'); ?>:</font><br>
<?
printRecData($insurance_data_array, getRecInsuranceData ($pid,"primary"), $N);
?><hr>


<font class=bold><? xl('Secondary Insurance Data','e'); ?>:</font><br>
<?
printRecData($insurance_data_array, getRecInsuranceData ($pid,"secondary"), $N);
?><hr>


<font class=bold><? xl('Tertiary Insurance Data','e'); ?>:</font><br>
<?
printRecData($insurance_data_array, getRecInsuranceData ($pid,"tertiary"), $N);
?><hr>


<font class=bold><? xl('Allergies','e'); ?>:</font><br>
<table><tr><td><?
printListData($pid, "allergy", "1")
?></td></tr></table>

<font class=bold><? xl('Medications','e'); ?>:</font><br>
<table><tr><td><?
printListData($pid, "medication", "1")
?></td></tr></table>

<font class=bold><? xl('Medical Problems','e'); ?>:</font><br>
<table><tr><td><?
printListData($pid, "medical_problem", "1")
?></td></tr></table>

<font class=bold><? xl('Immunizations','e'); ?>:</font><br>
<table><tr><td><?
$sql = "select if(i1.administered_date,concat(i1.administered_date,' - ',i2.name) ,substring(i1.note,1,20) ) as immunization_data from immunizations i1 left join immunization i2 on i1.immunization_id = i2.id where i1.patient_id = $pid order by administered_date desc";

$result = sqlStatement($sql);

while ($row=sqlFetchArray($result)){
	echo "<span class=text> " . $row{'immunization_data'} . "</span><br>\n";
}
?></td></tr></table>
<hr>
<font class=bold><? xl('Patient Comunication Sent','e'); ?>:</font><br>
<table><tr><td><?
	   $sql="SELECT concat( 'Messsage Type: ', batchcom.msg_type, ', Message Subject: ', batchcom.msg_subject, ', Sent on:', batchcom.msg_date_sent ) AS batchcom_data, batchcom.msg_text, concat( users.fname, users.lname ) AS user_name FROM `batchcom` JOIN `users` ON users.id = batchcom.sent_by WHERE batchcom.patient_id='$pid'";
	   // echo $sql;
	   $result = sqlStatement($sql);
	   while ($row=sqlFetchArray($result)) {
			 echo "<tr><td><span class=text>".$row{'batchcom_data'}.", By: ".$row{'user_name'}."</td></tr><tr><td>Text:<br> ".$row{'msg_txt'}."</span></td></tr>\n";
	    }
?></td></tr></table>
<hr>
<font class=bold><? xl('Patient Notes','e'); ?>:</font><br>
<table><tr><td><?
printPatientNotes($pid);
?></td></tr></table>
<hr>

<font class=bold><? xl('Billing','e'); ?>:</font><br>
<table><tr><td><?
printPatientBilling($pid);
?></td></tr></table>
<hr>

<font class=bold><? xl('Transactions','e'); ?>:</font><br>
<table><tr><td><?
printPatientTransactions($pid);
?></td></tr></table>
<hr>


<font class=bold><? xl('Forms','e'); ?>:</font><br>
<table><tr><td><?
printPatientForms($pid, $N);
?></td></tr></table>
















</body>
</html>
