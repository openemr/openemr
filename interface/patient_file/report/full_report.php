<?php 
include_once("../../globals.php");

include_once("$srcdir/forms.inc");
include_once("$srcdir/billing.inc");
include_once("$srcdir/pnotes.inc");
include_once("$srcdir/patient.inc");
include_once("$srcdir/report.inc");
include_once("$srcdir/acl.inc");

// get various authorization levels
$auth_notes_a  = acl_check('encounters', 'notes_a');
$auth_notes    = acl_check('encounters', 'notes');
$auth_coding_a = acl_check('encounters', 'coding_a');
$auth_coding   = acl_check('encounters', 'coding');
$auth_relaxed  = acl_check('encounters', 'relaxed');
$auth_med      = acl_check('patients'  , 'med');
$auth_demo     = acl_check('patients'  , 'demo');

if (!isset($_GET["viewnum"])) { $N = 6; }
else { $N = $_GET["viewnum"]; }

?>

<html>
<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>

<body class="body_top">
<div id="report_custom">  <!-- large outer DIV -->
<!--
            echo "<hr />";
            echo "<div class='text demographics' id='DEM'>\n";
            print "<h1>".xl('Patient Data').":</h1>";
-->
<a href="patient_report.php"><h1 class='title'><?php  xl('Patient Record Report','e'); ?></h1><span class='back'><?php echo $tback;?></span></a>
<br><br>
<a href="full_report.php?viewnum=6" class=link_submit>[<?php  xl('Normal View','e'); ?>]</a><a href="full_report.php?viewnum=1" class=link_submit>[<?php xl('Expanded View','e'); ?>]</a><br>

<br>

<div class='demographics'>
<h1><?php  xl('Patient Data','e'); ?>:</h1>
<?php 
printRecData($patient_data_array, getRecPatientData ($pid), $N);
?>
</div>

<hr/>

<?php if (acl_check('patients', 'med')): ?>
<div class='history'>
<h1><?php  xl('History Data','e'); ?>:</h1>
<?php printRecData($history_data_array, getRecHistoryData ($pid), $N); ?>
</div>
<hr/>
<?php endif; ?>


<div class='demographics'>
<h1><?php  xl('Employer Data','e'); ?>:</h1>
<?php 
printRecData($employer_data_array, getRecEmployerData ($pid), $N);
?>
</div>
<hr/>


<div class='insurance'>
<h1><?php  xl('Insurance Data','e'); ?>:</h1>
<span class='bold'><?php  xl('Primary Insurance Data','e'); ?>:</span> <br/>
<?php 
printRecData($insurance_data_array, getRecInsuranceData ($pid,"primary"), $N);
?>
<br/>
<br/>
<span class='bold'><?php  xl('Secondary Insurance Data','e'); ?>:</span> <br/>
<?php 
printRecData($insurance_data_array, getRecInsuranceData ($pid,"secondary"), $N);
?>
<br/>
<br/>
<span class='bold'><?php  xl('Tertiary Insurance Data','e'); ?>:</span> <br/>
<?php 
printRecData($insurance_data_array, getRecInsuranceData ($pid,"tertiary"), $N);
?>
</div>
<hr/>

<!-- Patient Issues -->
<?php if (acl_check('patients', 'med')): ?>
<div class='issues history'>
<h1><?php  xl('Issues','e'); ?>:</h1>
<br/>
<h2><?php  xl('Allergies','e'); ?>:</h2>
<?php printListData($pid, "allergy", "1") ?>

<br/>
<h2><?php  xl('Medications','e'); ?>:</h2>
<?php printListData($pid, "medication", "1") ?>

<br/>
<h2><?php  xl('Medical Problems','e'); ?>:</h2>
<?php printListData($pid, "medical_problem", "1") ?>
</div>
<hr/>

<div class='issues history'>
<h1><?php  xl('Immunizations','e'); ?>:</h1>
<?php 
$sql = "select if(i1.administered_date,concat(i1.administered_date,' - ',i2.name) ,substring(i1.note,1,20) ) as immunization_data from immunizations i1 left join immunization i2 on i1.immunization_id = i2.id where i1.patient_id = $pid order by administered_date desc";

$result = sqlStatement($sql);
while ($row=sqlFetchArray($result)){
    echo "<span class=text> " . $row{'immunization_data'} . "</span><br>\n";
}
?>
</div>
<hr>

<?php endif; // end patient-issues ?>

<!-- Patient communications -->
<div class='communications'>
<h1><?php  xl('Patient Comunication Sent','e'); ?>:</h1>
<?php 
$sql="SELECT concat( 'Messsage Type: ', batchcom.msg_type, ', Message Subject: ', batchcom.msg_subject, ', Sent on:', batchcom.msg_date_sent ) AS batchcom_data, batchcom.msg_text, concat( users.fname, users.lname ) AS user_name FROM `batchcom` JOIN `users` ON users.id = batchcom.sent_by WHERE batchcom.patient_id='$pid'";
// echo $sql;
$result = sqlStatement($sql);
while ($row=sqlFetchArray($result)) {
    echo "<tr><td><span class=text>".$row{'batchcom_data'}.", By: ".$row{'user_name'}."</td></tr><tr><td>Text:<br> ".$row{'msg_txt'}."</span></td></tr>\n";
}
?>
</div>
<hr/>

<div class='notes'>
<h1><?php  xl('Patient Notes','e'); ?>:</h1>
<?php printPatientNotes($pid); ?>
</div>
<hr>

<?php if (acl_check('acct', 'rep') || acl_check('acct', 'eob') || acl_check('acct', 'bill')) : ?>
<div class='billing'>
<h1><?php  xl('Billing','e'); ?>:</h1>
<?php printPatientBilling($pid); ?>
</div>
<hr>
<?php endif; ?>

<div class='transactions'>
<h1><?php  xl('Transactions','e'); ?>:</h1>
<?php printPatientTransactions($pid); ?>
</div>
<hr>

<!-- Encounters and Forms -->
<?php if (($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)): ?>
<div class='encounters'>
<h1><?php  xl('Encounters & Forms','e'); ?>:</h1>
<?php printPatientForms($pid, $N); ?>
</div>
<?php endif; ?>

</div> <!-- close large outer DIV -->
</body>
</html>
