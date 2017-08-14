<?php
/**
 * Patient Self Check in
 *
 * This program allows patients to check themselves in using a touchscreen or similar device in the doctors' practice.
 * Doing so, they are marked as arrived (code @) on the calendar module of the clinician's screen and are also marked as present
 * in the Patient Flow Board.
 * The purpose of this program is to free up time for front desk staff.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Alfie Carlisle <asc@carlisles.co>
 * @copyright Copyright (c) 2017 Alfie Carlisle <asc@carlisles.co>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once($GLOBALS['srcdir'].'/sql.inc');
?>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<div id="welcomeText">
<h1>Welcome to the Surgery</h1>
<h2>Today's Appointments</h2>
<p>Select your name to check in</p>
</div>

<style type="text/css">
    
    #welcomeText {

        text-align: center;
    }
table {
    border-collapse: collapse;
    width: 100%;
    font-size: 24pt;
}

th, td {
    text-align: left;
    padding: 8px;
}

tr:nth-child(even){background-color: #f2f2f2}

</style>

<table class="tg">
  <tr>
    <th class="tg-yw4l">First Name</th>
    <th class="tg-yw4l">Last Name</th>
    <th class="tg-yw4l">Tap to check in</th>
  </tr>

<?php

$date = date("y-m-d");

require 'mySQL_connector.php';

$sql = "SELECT * FROM openemr_postcalendar_events WHERE pc_eventDate = '". $date ."' and pc_pid REGEXP '^[0-9]+$' and pc_apptstatus = '-';";
$result = $dbc->query($sql);

if ($result->num_rows > 0) {
    // output data of each row

    while($row = $result->fetch_assoc()) {
        
        $uid = $row['pc_pid'];
        $nameMatchSQL = "SELECT * FROM patient_data where id = '.$uid.'";
        $patientNames = mysqli_fetch_assoc(mysqli_query($dbc, $nameMatchSQL));

         echo "<tr>
    	<td>" . $patientNames["fname"] . "</td>
    	<td>" . $patientNames["lname"] . "</td>
    	<td>" . "<a href='checkin.php?id=" . $patientNames["id"] . "'>Check In</a>". "</td>
    </tr>";


    }
} 

echo "</table>";

?>



