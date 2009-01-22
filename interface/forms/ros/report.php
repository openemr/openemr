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
            if ($key == "id" || $key == "pid" || $key == "user" || $key == "groupname" || 
                $key == "authorized" ||  $key == "date" || $value == "" || $value == "0000-00-00 00:00:00")
            {
    	        continue;
            }

            // skip the N/A values -- cfapress, Jan 2009
            if ($value == "N/A") { continue; }
            
            if ($value == "on") { $value = "yes"; }
    
            $key=ucwords(str_replace("_"," ",$key));
            print "<td><span class=bold>$key: </span><span class=text>$value</span></td>";
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
