<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<!DOCTYPE html>
<html>
<head>
	<title></title>
</head>
<body>

<img src="physician-icon-png-15318.png">

<?php

$sex = htmlspecialchars($_GET['sex']);
$month = htmlspecialchars($_GET['month']);
$birthday = htmlspecialchars($_GET['date']);
$surname = htmlspecialchars($_GET['surname']);

// date_default_timezone_set();

$timeRN = date("Y-m-d H:i:s");
$date = date("y-m-d");
echo "Time: " . $timeRN . "<br>";

require 'mySQL_connector.php';

// Get patient ID based on the data provided.
$PIDQuery = "SELECT pid FROM patient_data WHERE lname LIKE '" . $surname . "%' AND dob LIKE '%-" . $month ."-" . $birthday . "'";
$patient_id = mysql_fetch_array(mysqli_query($dbc, $PIDQuery));
echo $patient_id;

if ($patient_id==NULL) {


	echo '

	<style type="text/css">
	
body {

	background-color: red;
	color: white;
	text-align: center;
	font-size: 24pt;
}

img {

	text-align: center;
}

</style>

	Sorry. We could not find you in the database, please go to the reception desk to check in.';


}

else {


echo '<style type="text/css">
	
body {

	background-color: green;
	text-align: center;
	font-size: 24pt;
}

img {

	text-align: center;
}

</style>';

$id = $patient_id['pid'];

// Change appt status

$apptsql = "UPDATE openemr_postcalendar_events SET `pc_apptstatus` = '@', `pc_time` = '".$timeRN."' WHERE pc_pid = '".$id."'";
mysqli_query($dbc, $apptsql);

// Get the ID of the last encounter in the DB

$lastencounterIDsql = "SELECT id FROM sequences ORDER BY id DESC LIMIT 1;";
$lastencounterID = mysqli_fetch_assoc(mysqli_query($dbc, $lastencounterIDsql));
$newencounterID = $lastencounterID['id']+1;

// Create the encounter

$encountersql = "INSERT INTO form_encounter SET date = '". $date ."', onset_date = '". $date ."', reason = 'Routine Appointment', facility = 'GP', facility_id = '3', pid = '". $id ."', encounter = '". $newencounterID ."'";
mysqli_query($dbc, $encountersql);

// ... And update the sequence table

$sequenceupdateSql = "UPDATE sequences SET id = '". $newencounterID ."';";
mysqli_query($dbc, $sequenceupdateSql);

// Find the last form reference and plus 1

$lastformref = "SELECT form_id FROM forms ORDER BY form_id DESC LIMIT 1";
$lastformrefNo = mysqli_fetch_assoc(mysqli_query($dbc, $lastformref));
$new_form_id = $lastformrefNo['form_id']+1;

// Create the form reference

$formsql = "INSERT into forms (date, encounter, form_name, form_id, pid, user, groupname, authorized, deleted, formdir) VALUES ('".$date."', '".$newencounterID."', 'New Patient Encounter', '".$new_form_id."', '".$id."', 'carlisle43', 'Default', '1', '0', 'newpatient');"; // TODO
echo $doctornameresult['username'];
mysqli_query($dbc, $formsql);

// Find the last sequence number for this patient

$patienttrackerQuerysql = "SELECT lastseq, id FROM patient_tracker WHERE apptdate = '".$date."' AND pid = '".$id."';";

$lastseq = mysqli_fetch_assoc(mysqli_query($dbc, $patienttrackerQuerysql));

// Generate the next sequence number by +1

$newseq = $lastseq['lastseq'] + 1;

// Update the patient status and sequence to show they have arrived @

$patienttrackerUpdatesql = "UPDATE patient_tracker SET lastseq = '".$newseq."', date = '".$timeRN."', encounter = '".$newencounterID."' WHERE apptdate = '".$date."' AND pid = '".$id."'; ";
mysqli_query($dbc, $patienttrackerUpdatesql);

$patient_tracker_element_UPDATE = "INSERT into patient_tracker_element (pt_tracker_id, start_datetime, status, seq, user) VALUES ('".$lastseq['id']."', '".$timeRN."', '@', '".$newseq."', 'SelfCheckin');";
mysqli_query($dbc, $patient_tracker_element_UPDATE);

// Find which doctor is assigned to the patient and show their name

$getdoctorid = "SELECT `pc_aid` FROM openemr_postcalendar_events where pc_pid = '".$id."' and pc_eventDate = '".$date."';";
$doctorIDresult = mysqli_fetch_assoc(mysqli_query($dbc, $getdoctorid));
$docid = $doctorIDresult['pc_aid'];

// Find the Drs name.

$lookupdoctor = "SELECT * FROM users where ID = '".$docid."';";
$doctornameresult = mysqli_fetch_assoc(mysqli_query($dbc, $lookupdoctor));
echo "Thank you. <br>You have checked in for your appointment with Dr. " . $doctornameresult['lname'] . ".";

} // end else

?>

<!-- <meta http-equiv="refresh" content="6;url=/" /> -->

</body>
</html>