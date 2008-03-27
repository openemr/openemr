<?php 
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
<?php html_header_show();?>


<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">


</head>
<body class="body_top">

<a href="patient_report.php"><font class="title"><?php  xl('Patient Record Report','e'); ?></font><font class=back><?php echo $tback;?></font></a>
<br><br>
<a href="full_report.php?viewnum=6" class=link_submit>[<?php  xl('Normal View','e'); ?>]</a><a href="full_report.php?viewnum=1" class=link_submit>[<?php xl('Expanded View','e'); ?>]</a><br>

<br>




<font class=bold><?php  xl('Patient Data','e'); ?>:</font><br>
<?php 
printRecData($patient_data_array, getRecPatientData ($pid), $N);
?><hr>

<font class=bold><?php  xl('History Data','e'); ?>:</font><br>
<?php 
printRecData($history_data_array, getRecHistoryData ($pid), $N);
?><hr>


<font class=bold><?php  xl('Employer Data','e'); ?>:</font><br>
<?php 
printRecData($employer_data_array, getRecEmployerData ($pid), $N);
?><hr>


<font class=bold><?php  xl('Primary Insurance Data','e'); ?>:</font><br>
<?php 
printRecData($insurance_data_array, getRecInsuranceData ($pid,"primary"), $N);
?><hr>


<font class=bold><?php  xl('Secondary Insurance Data','e'); ?>:</font><br>
<?php 
printRecData($insurance_data_array, getRecInsuranceData ($pid,"secondary"), $N);
?><hr>


<font class=bold><?php  xl('Tertiary Insurance Data','e'); ?>:</font><br>
<?php 
printRecData($insurance_data_array, getRecInsuranceData ($pid,"tertiary"), $N);
?><hr>


<font class=bold><?php  xl('Allergies','e'); ?>:</font><br>
<table><tr><td><?php 
printListData($pid, "allergy", "1")
?></td></tr></table>

<font class=bold><?php  xl('Medications','e'); ?>:</font><br>
<table><tr><td><?php 
printListData($pid, "medication", "1")
?></td></tr></table>

<font class=bold><?php  xl('Medical Problems','e'); ?>:</font><br>
<table><tr><td><?php 
printListData($pid, "medical_problem", "1")
?></td></tr></table>

<font class=bold><?php  xl('Immunizations','e'); ?>:</font><br>
<table><tr><td><?php 
$sql = "select if(i1.administered_date,concat(i1.administered_date,' - ',i2.name) ,substring(i1.note,1,20) ) as immunization_data from immunizations i1 left join immunization i2 on i1.immunization_id = i2.id where i1.patient_id = $pid order by administered_date desc";

$result = sqlStatement($sql);

while ($row=sqlFetchArray($result)){
	echo "<span class=text> " . $row{'immunization_data'} . "</span><br>\n";
}
?></td></tr></table>
<hr>
<font class=bold><?php  xl('Patient Comunication Sent','e'); ?>:</font><br>
<table><tr><td><?php 
	   $sql="SELECT concat( 'Messsage Type: ', batchcom.msg_type, ', Message Subject: ', batchcom.msg_subject, ', Sent on:', batchcom.msg_date_sent ) AS batchcom_data, batchcom.msg_text, concat( users.fname, users.lname ) AS user_name FROM `batchcom` JOIN `users` ON users.id = batchcom.sent_by WHERE batchcom.patient_id='$pid'";
	   // echo $sql;
	   $result = sqlStatement($sql);
	   while ($row=sqlFetchArray($result)) {
			 echo "<tr><td><span class=text>".$row{'batchcom_data'}.", By: ".$row{'user_name'}."</td></tr><tr><td>Text:<br> ".$row{'msg_txt'}."</span></td></tr>\n";
	    }
?></td></tr></table>
<hr>
<font class=bold><?php  xl('Patient Notes','e'); ?>:</font><br>
<table><tr><td><?php 
printPatientNotes($pid);
?></td></tr></table>
<hr>

<font class=bold><?php  xl('Billing','e'); ?>:</font><br>
<table><tr><td><?php 
printPatientBilling($pid);
?></td></tr></table>
<hr>

<font class=bold><?php  xl('Transactions','e'); ?>:</font><br>
<table><tr><td><?php 
printPatientTransactions($pid);
?></td></tr></table>
<hr>


<font class=bold><?php  xl('Forms','e'); ?>:</font><br>
<table><tr><td><?php 
printPatientForms($pid, $N);
?></td></tr></table>

</body>
</html>
