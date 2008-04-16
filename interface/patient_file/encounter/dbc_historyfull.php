<?php
include_once("../../../interface/globals.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
   "http://www.w3.org/TR/html4/loose.dtd">
<html>  
<head>
    <title>Full DBC History </title>

    <style type="text/css">
        body {
            color: #126E09;
            background-color: #DCFFBC;
        }
        
        .tblztn {
            margin: 10px;
        }
        
        .tblztn tr {
            background-color: #BFDDA3;
        }
        
        .tblztn th {
            background-color: #6CD372; color: #234425; padding: 5px;
        }
        
        .tblztn td {
            padding: 5px;
        }
        
        .tbldbc {
            margin: 0 0 10px 50px;
        }
        
        .tbldbc tr {
            background-color: #DDDDA0;
        }
        
        .tbldbc th {
            background-color: #DD7F71; color: #3D231F; padding: 5px;
        }
        
        .tbldbc td {
            padding: 5px;
            color: #884417;
        }
  </style>

</head>


<body>

<?php
echo 'Full DBC History for: <strong>' .dutch_name($_SESSION['pid']). '</strong> (' .$_SESSION['pid']. ')';

$allztn = all_ztn($_SESSION['pid']);
if ( empty($allztn) ) {
    echo "NO PREVIOUS RECORDS!";
} else {
    foreach ( $allztn as $az ) { 
        // obtain all dbc's associated with a ztn
        $alldbc = all_dbc($az['cn_ztn']);

        dbch_header_ztn();
        dbch_display_ztn($az);

        if ( empty($alldbc) ) {
            echo "NO DBC's.";
        } else {
            foreach ( $alldbc as $ad ) {
                dbch_header_dbc();
                dbch_display_dbc($ad);
                echo '</table>';
            }

        } // if-else
        echo '</table>';
    }// foreach

} // else
?>


<table>

</table>

</body>
</html>


<?php
// ----------------------------------------------------------------------------
/**
DISPLAY A DBC

@param array dbc content
@return void - just echo the string (html formatted string)
*/
function dbch_display_dbc($ad) {
    $status = ( $ad['ax_open'] ) ? 'Open' : 'Closed';
    $sti = ( $ad['ax_sti'] ) ? 'Sent' : 'Not sent';
    
    // some preparations for axes (ax1-ax5)
    $as1 = unserialize($ad['ax_as1']);
        $as1c = $as1['content']; $mainpos = (int)$as1['mainpos']; // mainpos is written in both places
    $as2 = unserialize($ad['ax_as2']);
        $as2c = $as2['content']; 		
    $as3 = unserialize($ad['ax_as3']);
    $as4 = unserialize($ad['ax_as4']);
    $as5 = unserialize($ad['ax_as5']);

    // as1 transformation
    $counter = 1;
    foreach ( $as1c as $a) {
            $as1_str .= what_as($a);
            if ( $counter == $mainpos ) $as1_str .= ' (MD)';
            $as1_str .= '<br />'; $counter++; 
    }
    // as2 transformation
    foreach ( $as2c as $a) {
            $as2_str .= what_as($a['code']). '(' .$a['trekken']. ')';
            if ( $counter == $mainpos ) $as2_str .= ' (MD)';
            $as2_str .= '<br />'; $counter++;
    }

    // as3 and as4
    $as3_str = what_as($as3); $as4_str = what_as($as4);
    // as5
    $as5_str = what_as($as5['gaf1']) .'<br />'. what_as($as5['gaf2']) .'<br />'. what_as($as5['gaf3']);


    $string = '<tr>';
    $string .= "<td>{$ad['ax_id']}</td><td>$status</td><td>{$ad['ax_odate']}</td>
                <td>{$ad['ax_cdate']}</td><td>$sti</td></tr>";
    $string .= "<tr><td colspan='2'>$as1_str</td><td colspan='2'>$as3_str</td><td rowspan='2'>$as5_str</td></tr>";
    $string .= "<tr><td colspan='2'>$as2_str</td><td colspan='2'>$as4_str</td></tr>";
    //$string .= '</tr>';
    
    echo $string;
}


// ----------------------------------------------------------------------------
/**
DISPLAY A ZTN

@param array ztn content
@return void - just echo the string (html formatted string)
*/
function dbch_display_ztn($az) {
    $status = ( $az['cn_open'] ) ? 'Open' : 'Closed';
   
    $string = "<tr><td>{$az['cn_ztn']}</td><td>{$az['cn_dopen']}</td><td>{$az['cn_dclosed']}</td><td>$status</td></tr>";
    
    echo $string;
}


// ----------------------------------------------------------------------------
/**
DISPLAY HEADER FOR DBC TABLES

@param none
@return void - just echo the string (html formatted string)
*/
function dbch_header_dbc() {
    $string = '<table class="tbldbc"><tr><th>DBC ID</th><th>Status</th><th>Opening Date</th><th>Closing Date</th>
                <th>Sent to insurer</th></tr>';
    
    echo $string;
}

// ----------------------------------------------------------------------------
/**
DISPLAY HEADER FOR ZTN TABLES

@param none
@return void - just echo the string (html formatted string)
*/
function dbch_header_ztn() {
    $string = '<table class="tblztn"><tr><th>ZTN ID</th><th>Opening Date</th><th>Closing Date</th><th>Status</th></tr>';
    
    echo $string;
}
?>