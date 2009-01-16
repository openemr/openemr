<?php
include_once("../../globals.php");
include_once($GLOBALS["srcdir"]."/api.inc");

/** CHANGE THIS, the name of the function is significant and  **
 **              must be changed to match the folder name     **/
function example_report( $pid, $encounter, $cols, $id) {
    
    /** CHANGE THIS - name of the database table associated with this form **/
    $table_name = "form_example";

    $count = 0;
    $data = formFetch($table_name, $id);
   
    if ($data) {
 
        print "<table><tr>";
       
        foreach($data as $key => $value) {
            if ($key == "id" || $key == "pid" || $key == "user" || 
                $key == "groupname" || $key == "authorized" || 
                $key == "activity" || $key == "date" || 
                $value == "" || $value == "0000-00-00 00:00:00" || 
                $value == "n") 
            {
                // skip certain fields and blank data
	        continue;
            }

            $key=ucwords(str_replace("_"," ",$key));
            print("<tr>\n");  
            print("<tr>\n");  
            print "<td><span class=bold>$key: </span><span class=text>$value</span></td>";
            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
    }
    print "</tr></table>";
}

?> 
