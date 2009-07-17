<?php
include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");
include_once ($GLOBALS['fileroot']."/library/patient.inc");

function vitals_report( $pid, $encounter, $cols, $id) {
    $count = 0;
    $data = formFetch("form_vitals", $id);
    $patient_data = getPatientData($GLOBALS['pid']);
    $patient_age = getPatientAge($patient_data['DOB']);

    if ($data) {
        print "<table><tr>";

        foreach($data as $key => $value) {
        
            if ($key == "id" || $key == "pid" || 
                $key == "user" || $key == "groupname" || 
                $key == "authorized" || $key == "activity" || 
                $key == "date" || $value == "" || 
                $value == "0000-00-00 00:00:00" || $value == "0.0" )
            {
                // skip certain data
	        continue;
            }

            if ($value == "on") { $value = "yes"; } 

            $key=ucwords(str_replace("_"," ",$key));
            if ($key == "BMI Status") {
	        if ($patient_age <= 20 || (preg_match('/month/', $patient_age))) { 
		    $value = "See Growth-Chart"; 
		}
	    }

            echo "<td><span class=bold>".xl($key).": </span><span class=text>".$value."</span></td>";

            $count++;

            if ($count == $cols) {
                $count = 0;
                echo "</tr><tr>\n";
            }
        }

        echo "</tr></table>";
    }
}
?> 
