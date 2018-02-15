<?php

use OpenEMR\Core\Header;

$ignoreAuth = true;
require_once "../../interface/globals.php";
if (!$GLOBALS['self_checkin_enable'] == 1) {
    die("This feature has not been enabled");
}
?>

<link rel="stylesheet" type="text/css" href="/interface/themes/selfCheckIn.css">

<!DOCTYPE html>
<html>
<head>

    <title><?php echo xlt("Check In"); ?></title>
<?php

Header::setupHeader();

?>
</head>
<body class="step5">

<p>
    <img style="text-align: center"
         width="500px" src='<?php echo $GLOBALS['images_static_relative']; ?>/logo-full-con.png'/>
</p>

<?php

$month = htmlspecialchars($_GET['month']);
$birthday = htmlspecialchars($_GET['date']);
$surname = htmlspecialchars($_GET['surname']);

// date_default_timezone_set();

$timeRN = date("Y-m-d H:i:s");
$date = date("y-m-d");
echo "<h1> Time: " . $timeRN . "</h1><br>";

require 'mySQL_connector.php';

// Get the patient IDs of everyone with an appointment today. Enter them into an array which we can call upon later.

$todaysPIDsQuery = "SELECT pc_pid from `openemr_postcalendar_events` WHERE `pc_eventDate` = '" . $date ."'";

$todaysPIDs_result = mysqli_query($dbc, $todaysPIDsQuery); // This line executes the MySQL query that you typed above

$PIDArray = array(); // make a new array to hold all your data

$index = 0;
while ($row = mysqli_fetch_array($todaysPIDs_result)) { // loop to store the data in an associative array.
     $PIDArray[$index] = $row;
     $index++;
}

// Create an array which we can pass to the subsequent MySQL query.

$PIDArrayValues = array_map('array_pop', $PIDArray);
$implodedArray = implode(',', $PIDArrayValues);

// Get patient ID based on the data provided.
// Criteria: a) has an appointment today i.e. their patient ID is one of the ones in the appointment list for today
// b) Matches all three demographics asked earlier - dob, surname

$PIDQuery = "SELECT pid
FROM patient_data
WHERE lname LIKE '" . $surname . "%' 
AND dob LIKE '%-" . $month ."-" . $birthday . "'  
AND id IN (".$implodedArray.")";

$patient_id = mysqli_fetch_array(mysqli_query($dbc, $PIDQuery));

if ($patient_id==null) {
    echo '
	<p><i class="fa fa-times fa-3x" aria-hidden="true"></i></p>
	<br>' . '
	<h1>' . xlt("Sorry. We could not find you in the database, please go to the reception desk to check in.") . '</h1>';
} else {
    $id = $patient_id['pid'];

    // Ensure the patient hasn't already checked in...
    // We expect @ for a checked in patient and - for a patient who has not yet checked in

    $patientcheck = "SELECT `pc_apptstatus` 
FROM `openemr_postcalendar_events` 
WHERE pc_pid = ". $id ." 
AND pc_eventDate = '".$date."'";
    $patientcheckresult = mysqli_fetch_assoc(mysqli_query($dbc, $patientcheck));

    if ($patientcheckresult['pc_apptstatus'] == "@") {
        echo '
	<p><i class="fa fa-check fa-3x" aria-hidden="true"></i></p>
	<br>' . xlt("You have already checked in. If you have any questions, please go to the reception desk.") . '';
    } else {
        // Change appt status
        $apptsql = "UPDATE openemr_postcalendar_events 
SET `pc_apptstatus` = '@', `pc_time` = '".$timeRN."' 
WHERE pc_pid = '".$id."'";
        mysqli_query($dbc, $apptsql);

        // Get the ID of the last encounter in the DB

        $lastencounterIDsql = "SELECT id FROM sequences ORDER BY id DESC LIMIT 1;";
        $lastencounterID = mysqli_fetch_assoc(mysqli_query($dbc, $lastencounterIDsql));
        $newencounterID = $lastencounterID['id']+1;

        // Create the encounter

        $encountersql = "INSERT INTO form_encounter 
SET date = '". $date ."', onset_date = '". $date ."', 
reason = 'Routine Appointment', 
facility = 'GP', 
facility_id = '3', 
pid = '". $id ."', encounter = '". $newencounterID ."'";
        mysqli_query($dbc, $encountersql);

        // ... And update the sequence table

        $sequenceupdateSql = "UPDATE sequences SET id = '". $newencounterID ."';";
        mysqli_query($dbc, $sequenceupdateSql);

        // Find the last form reference and plus 1

        $lastformref = "SELECT form_id FROM forms ORDER BY form_id DESC LIMIT 1";
        $lastformrefNo = mysqli_fetch_assoc(mysqli_query($dbc, $lastformref));
        $new_form_id = $lastformrefNo['form_id']+1;

        // Find which doctor is assigned to the patient and show their name

        $getdoctorid = "SELECT `pc_aid` 
FROM openemr_postcalendar_events 
where pc_pid = '".$id."' 
and pc_eventDate = '".$date."';";
        $doctorIDresult = mysqli_fetch_assoc(mysqli_query($dbc, $getdoctorid));
        $docid = $doctorIDresult['pc_aid'];

        // Find the name of the doctor whom the appointment is with

        $lookupdoctor = "SELECT * FROM users where ID = '".$docid."';";
        $doctornameresult = mysqli_fetch_assoc(mysqli_query($dbc, $lookupdoctor));
        echo '
<p><i class="fa fa-check fa-3x" aria-hidden="true"></i></p>
<br>' .
        xlt("Thank you.") . '<br>' . xlt("You have checked in for your appointment with Dr. ") . $doctornameresult['lname'] . '.';

        // Create the form reference

        $formsql = "INSERT into forms (date, encounter, form_name, form_id, pid, user, groupname, authorized, deleted, formdir) VALUES ('".$date."', '".$newencounterID."', 'New Patient Encounter', '".$new_form_id."', '".$id."', '" . $doctornameresult['username'] . "', 'Default', '1', '0', 'newpatient');"; // TODO
        mysqli_query($dbc, $formsql);

        // Find the last sequence number for this patient

        $patienttrackerQuerysql = "SELECT lastseq, id 
FROM patient_tracker 
WHERE apptdate = '".$date."' 
AND pid = '".$id."';";

        $lastseq = mysqli_fetch_assoc(mysqli_query($dbc, $patienttrackerQuerysql));

        // Generate the next sequence number by +1

        $newseq = $lastseq['lastseq'] + 1;

        // Update the patient status and sequence to show they have arrived @

        $patienttrackerUpdatesql = "UPDATE patient_tracker 
SET lastseq = '".$newseq."', date = '".$timeRN."', encounter = '".$newencounterID."' 
WHERE apptdate = '".$date."' 
AND pid = '".$id."'; ";
        mysqli_query($dbc, $patienttrackerUpdatesql);

        $patient_tracker_element_UPDATE = "INSERT into patient_tracker_element 
(pt_tracker_id, start_datetime, status, seq, user) 
VALUES ('".$lastseq['id']."', '".$timeRN."', '@', '".$newseq."', 'SelfCheckin');";
        mysqli_query($dbc, $patient_tracker_element_UPDATE);
    } // end else
}
?>

<!-- Send user back to start to allow next patient to use the kiosk-->
<meta http-equiv="refresh" content="12;url=/library/patient_self_checkin/" />

</body>
</html>
