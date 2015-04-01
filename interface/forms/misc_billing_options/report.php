<?php
include_once(dirname(__FILE__).'/../../globals.php');
include_once($GLOBALS["srcdir"]."/api.inc");
require_once("date_qualifier_options.php");
function misc_billing_options_report( $pid, $encounter, $cols, $id) {
    $count = 0;
    $data = formFetch("form_misc_billing_options", $id);
    if ($data) {
    print "<table><tr>";
        foreach($data as $key => $value) {
            if ($key == "id" || $key == "pid" || $key == "user" || $key == "groupname" || $key == "authorized" || $key == "activity" || $key == "date" || $value == "" || $value == "0" || $value == "0000-00-00 00:00:00" || $value =="0000-00-00") {
                continue;
            }
            if ($value == "1") {
                $value = "yes"; 
            }
            if(($key==='box_14_date_qual')||$key==='box_15_date_qual')
            {
                $value=text(qual_id_to_description($key,$value));
            }
            $key=ucwords(str_replace("_"," ",$key));
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
