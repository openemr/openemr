<?php

date_default_timezone_set('Europe/London');
$id = $_GET["id"];

$timeRN = date("Y-m-d H:i:s");
$date = date("y-m-d");
echo $timeRN . "<br>";

require 'mySQL_connector.php';

// Find which doctor is assigned to the patient and show their name

$getdoctorid = "SELECT `pc_aid` FROM openemr_postcalendar_events where pc_pid = '3' and pc_eventDate = '".$date."';";
$doctorIDresult = mysqli_fetch_assoc(mysqli_query($dbc, $getdoctorid));
$docid = $doctorIDresult['pc_aid'];

// Find the Drs name.

$lookupdoctor = "SELECT `lname` FROM users where ID = '".$docid."';";
$doctornameresult = mysqli_fetch_assoc(mysqli_query($dbc, $lookupdoctor));
echo "Thank you. <br>You have checked in for your appointment with Dr. " . $doctornameresult['lname'] . ".";

?>