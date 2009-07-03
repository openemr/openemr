<?php
//------------Forms generated from formsWiz
include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");

function ros_report( $pid, $encounter, $cols, $id) {

    $count = 0;
    
    $data = formFetch("form_ros", $id);

    if ($data) {
        print "<div id='form_ros_values' style='border: 1px solid black;'><table><tr>";
    
        foreach($data as $key => $value) {
            if ($key == "id" || $key == "pid" || $key == "user" || $key == "groupname" || $key == "activity" ||
                $key == "authorized" ||  $key == "date" || $value == "" || $value == "0000-00-00 00:00:00")
            {
    	        continue;
            }

            // skip the N/A values -- cfapress, Jan 2009
            if ($value == "N/A") { continue; }
            
            if ($value == "on") { $value = "yes"; }
    
            $key=ucwords(str_replace("_"," ",$key));
	    
	    //added by BM 07-2009 to clarify labels
	    if ($key == "Glaucoma History") { $key = "Glaucoma Family History"; }
	    if ($key == "Irritation") { $key = "Eye Irritation"; }
	    if ($key == "Redness") { $key = "Eye Redness"; }
	    if ($key == "Discharge") { $key = "ENT Discharge"; }
	    if ($key == "Pain") { $key = "ENT Pain"; }
	    if ($key == "Biopsy") { $key = "Breast Biopsy"; }
	    if ($key == "Hemoptsyis") { $key = "Hemoptysis"; }
	    if ($key == "Copd") { $key = "COPD"; }
	    if ($key == "Pnd") { $key = "PND"; }
	    if ($key == "Doe") { $key = "DOE"; }
	    if ($key == "Peripheal") { $key = "Peripheral"; }
	    if ($key == "Legpain Cramping") { $key = "Leg Pain/Cramping"; }
	    if ($key == "Frequency") { $key = "Urine Frequency"; }
	    if ($key == "Urgency") { $key = "Urine Urgency"; }
	    if ($key == "Utis") { $key = "UTIs"; }
	    if ($key == "Hesitancy") { $key = "Urine Hesitancy"; }
	    if ($key == "Dribbling") { $key = "Urine Dribbling"; }
	    if ($key == "Stream") { $key = "Urine Stream"; }
	    if ($key == "G") { $key = "Female G"; }
	    if ($key == "P") { $key = "Female P"; }
	    if ($key == "Lc") { $key = "Female LC"; }
	    if ($key == "Ap") { $key = "Female AP"; }
	    if ($key == "Mearche") { $key = "Menarche"; }
	    if ($key == "Lmp") { $key = "LMP"; }
	    if ($key == "F Frequency") { $key = "Menstrual Frequency"; }
	    if ($key == "F Flow") { $key = "Menstrual Flow"; }
	    if ($key == "F Symptoms") { $key = "Female Symptoms"; }
	    if ($key == "F Hirsutism") { $key = "Hirsutism/Striae"; }
	    if ($key == "Swelling") { $key = "Musc Swelling"; }
	    if ($key == "M Redness") { $key = "Musc Redness"; }
	    if ($key == "M Warm") { $key = "Musc Warm"; }
	    if ($key == "M Stiffness") { $key = "Musc Stiffness"; }
	    if ($key == "M Aches") { $key = "Musc Aches"; }
	    if ($key == "Fms") { $key = "FMS"; }
	    if ($key == "Loc") { $key = "LOC"; }
	    if ($key == "Tia") { $key = "TIA"; }
	    if ($key == "N Numbness") { $key = "Neuro Numbness"; }
	    if ($key == "N Weakness") { $key = "Neuro Weakness"; }
	    if ($key == "N Headache") { $key = "Headache"; }
	    if ($key == "S Cancer") { $key = "Skin Cancer"; }
	    if ($key == "S Acne") { $key = "Acne"; }
	    if ($key == "S Other") { $key = "Skin Other"; }
	    if ($key == "S Disease") { $key = "Skin Disease"; }
	    if ($key == "P Diagnosis") { $key = "Psych Diagnosis"; }
	    if ($key == "P Medication") { $key = "Psych Medication"; }
	    if ($key == "Abnormal Blood") { $key = "Endo Abnormal Blood"; }
	    if ($key == "Fh Blood Problems") { $key = "FH Blood Problems"; }
	    if ($key == "Hiv") { $key = "HIV"; }
	    if ($key == "Hai Status") { $key = "HAI Status"; }
	    
	    //modified by BM 07-2009 for internationalization
	    print "<td><span class=bold>" . xl($key) . ": </span><span class=text>" . xl($value) . "</span></td>";
            $count++;
            
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
    }
    print "</tr></table></div>";
}
?> 
