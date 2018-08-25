<?php

/**
 * interface/main/mobile/m_cal.php
 *
 * Basic Calendar
 *
 * Copyright (C) 2018 Raymond Magauran <magauran@MedExBank.com>
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEMR
 * @author Ray Magauran <magauran@MedExBank.com>
 * @link http://www.open-emr.org
 * @copyright Copyright (c) 2018 MedEx <magauran@MedExBank.com>
 * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
 */

require_once "../../globals.php";
require_once "$srcdir/patient.inc";
require_once "$srcdir/options.inc.php";
require_once $GLOBALS['srcdir']."/../vendor/mobiledetect/mobiledetectlib/Mobile_Detect.php";
require_once "m_functions.php";

$detect             = new Mobile_Detect;
$device_type        = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
$script_version     = $detect->getScriptVersion();
$display = "cal";

//deletable
$uspfx              = substr(__FILE__, strlen($webserver_root)) . '.';
$setting_mFind      = prevSetting('', 'setting_mFind', 'setting_mFind', 'byRoom');
$setting_mRoom      = prevSetting('', 'setting_mRoom', 'setting_mRoom', '');
$setting_mCategory  = prevSetting('', 'setting_mCategories', 'setting_mCategories', '');
?><!doctype html>
<html style="cursor: pointer;">
<?php
    common_head();
?>

<style>
    #autocomplete {
        background: rgba(0, 0, 0, 0) none repeat scroll 0 0;
        border: 1px solid rgba(0, 0, 0, 0.25);
        border-radius: 4px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.4) inset, 0 1px 0 rgba(255, 255, 255, 0.1);
        color: #fff;
        text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.796), 0 0 10px rgba(255, 255, 255, 0.298);
        padding: 10px;
        margin: 10px 0;
    }
    input[type="file"] {
        display: none;
    }
    .custom-file-upload {
        border: 1px solid #ccc;
        display: inline-block;
        padding: 12px 12px;
        cursor: pointer;
        border-radius: 5px;
        margin: 8px auto;
        text-align: center;
        background-color: #2d98cf66;
        box-shadow: 1px 1px 3px #c0c0c0;
    }
    .fa {
        padding-right:2px;
    }
    #preview {
        text-align: center;
    }
    #preview  img {
        vertical-align: top;
        width: 85%;
        margin: 0px auto;
    }
    obj, audio, canvas, progress, video {
        margin:2%;
        max-width: 8em;
        vertical-align: top;
        text-align: center;
    }
    label {
        margin:5px;
        padding:5px 20px;
        box-shadow: 1px 1px 2px #938282;
    }
    label input {
        padding:left:30px;
    }
    .byCatDisplay {
        display:none;
    }
    .btn {
        font-size: 1.5rem;
    }
    .card-title {
        overflow:hidden;
    }
    .jumbotronA {
        min-height:400px;
        margin:8px;
    }
    @media (min-width:1200px){
        .auto-clear .col-lg-1:nth-child(12n+1){clear:left;}
        .auto-clear .col-lg-2:nth-child(6n+1){clear:left;}
        .auto-clear .col-lg-3:nth-child(4n+1){clear:left;}
        .auto-clear .col-lg-4:nth-child(3n+1){clear:left;}
        .auto-clear .col-lg-6:nth-child(odd){clear:left;}
    }
    @media (min-width:992px) and (max-width:1199px){
        .auto-clear .col-md-1:nth-child(12n+1){clear:left;}
        .auto-clear .col-md-2:nth-child(6n+1){clear:left;}
        .auto-clear .col-md-3:nth-child(4n+1){clear:left;}
        .auto-clear .col-md-4:nth-child(3n+1){clear:left;}
        .auto-clear .col-md-6:nth-child(odd){clear:left;}
    }
    @media (min-width:768px) and (max-width:991px){
        .auto-clear .col-sm-1:nth-child(12n+1){clear:left;}
        .auto-clear .col-sm-2:nth-child(6n+1){clear:left;}
        .auto-clear .col-sm-3:nth-child(4n+1){clear:left;}
        .auto-clear .col-sm-4:nth-child(3n+1){clear:left;}
        .auto-clear .col-sm-6:nth-child(odd){clear:left;}
    }
    @media (max-width:767px) {
        .auto-clear .col-xs-1:nth-child(12n+1) {clear: left;}
        .auto-clear .col-xs-2:nth-child(6n+1) {clear: left;}
        .auto-clear .col-xs-3:nth-child(4n+1) {clear: left;}
        .auto-clear .col-xs-4:nth-child(3n+1) {clear: left;}
        .auto-clear .col-xs-6:nth-child(odd) {clear: left;}
        .jumbotronA {margin: 8px auto;}
        #head_img {margin: 2vH 0 0 0;max-height: 15vH;}
    }

    @media (max-width:400px){
        .auto-clear .col-xs-1:nth-child(12n+1){clear:left;}
        .auto-clear .col-xs-2:nth-child(6n+1){clear:left;}
        .auto-clear .col-xs-3:nth-child(4n+1){clear:left;}
        .auto-clear .col-xs-4:nth-child(3n+1){clear:left;}
        .auto-clear .col-xs-6:nth-child(odd){clear:left;}
        .jumbotronA {margin: 8px auto;}
        #head_img {margin: 2vH 0 0 0;max-height: 10vH;}
    }
    .section_title {font-size:1.2em;text-decoration:underline;font-weight:600;margin-bottom:8px;}
</style>
<body style="background-color: #fff;" >
<?php common_header($display); ?>

    <?php
        if($_GET['eid'])
            echo "<script>$(document).ready(function () { ScrollIt(); }); </script>";
        
        //require_once($_SERVER['DOCUMENT_ROOT']."/mobile/db/dbfunc.php");
        //import the needed variables
        $day = $_GET['day'];
        $month = $_GET['month'];
        $provider = $_GET['provider'];
        $eid = $_GET['eid'];
        
        //check for bad data
        $day = stripslashes($day);
        $month = stripslashes($month);
        //$day = mysql_real_escape_string($day);
        //$month = mysql_real_escape_string($month);
        $provider = stripslashes($provider);
        $eid = stripslashes($eid);
        //$provider = mysql_real_escape_string($provider);
        //$eid = mysql_real_escape_string($eid);
        
        //if no provider is assigned, return all appointments
        if(is_null($provider) && empty($provider) || !is_numeric($provider)) {
            $provider = -1;
        }
        
        //if no day and month are provided, use the current date
        if((is_null($day) || empty($day) && is_null($month) || empty($month)) || !is_numeric($day) || !is_numeric($month) ) {
            $thismonth = ( int ) date("m");
            $thisyear = date( "Y" );
            $today = date("j");
        }
        
        else {
            $thismonth = $month;
            $thisyear = date( "Y" );
            $today = $day;
        }
        
        
        
        // get this month and this years as an int
        //$thismonth = ( int ) date("m");
        //$thisyear = date( "Y" );
        //$today = date("j");
        // find out the number of days in the month
        $numdaysinmonth = cal_days_in_month( CAL_GREGORIAN, $thismonth, $thisyear );
        
        // create a calendar object
        $jd = cal_to_jd( CAL_GREGORIAN, $thismonth ,date( 1 ), $thisyear );
        
        // get the start day as an int (0 = Sunday, 1 = Monday, etc)
        $startday = jddayofweek( $jd , 0 );
        
        // get the month as a name
        $monthname = jdmonthname( $jd, 1 )
    ?>
    <div id="gb-main" class="container-fluid">
        <form id="save_media" name="save_media" action="#" method="post" enctype="multipart/form-data">

            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                    <img src="<?php echo $GLOBALS['webroot']; ?>/interface/main/mobile/calendar.png" id="head_img" alt="<?php echo xla('OpenEMR Calendar'); ?>">
                </div>
                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 text-center">
                    <div class="row text-center">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 custom-file-upload">
                            <table WIDTH="100%">
                                <tr>
                                    <td colspan="2" bgcolor="#C0C0C0"><div align="center"><a href=m_cal.php?provider=<?php echo $provider ?>&day=<?php echo $today ?>&month=<?php echo $thismonth-1 ?>><?php echo jdmonthname(cal_to_jd( CAL_GREGORIAN, $thismonth-1 ,date( 1 ), $thisyear ),0) ?></a></div></td>
                                    <td colspan="3" bgcolor="#C0C0C0"><div align="center"><strong><?php echo $monthname ?></strong></div></td>
                                    <td colspan="2" bgcolor="#C0C0C0"><div align="center"><a href=m_cal.php?provider=<?php $provider ?>&day=<?php echo $today ?>&month=<?php echo $thismonth+1 ?>><?php echo jdmonthname(cal_to_jd( CAL_GREGORIAN, $thismonth+1 ,date( 1 ), $thisyear ),0) ?></a></div></td>
                                </tr>
                                <tr>
                                    <td><strong>S</strong></td>
                                    <td><strong>M</strong></td>
                                    <td><strong>T</strong></td>
                                    <td><strong>W</strong></td>
                                    <td><strong>T</strong></td>
                                    <td><strong>F</strong></td>
                                    <td><strong>S</strong></td>
                                </tr>
                                <tr>
                                    <?php
                                        // put render empty cells
                                        $emptycells = 0;
                                        
                                        for( $counter = 0; $counter <  $startday; $counter ++ ) {
                                            echo "\t\t<td>-</td>\n";
                                            $emptycells ++;
                                        }
                                        
                                        // renders the days
                                        $rowcounter = $emptycells;
                                        $numinrow = 7;
                                        
                                        for( $counter = 1; $counter <= $numdaysinmonth; $counter ++ ) {
                                            $rowcounter ++;
                                            //don't make a link for today
                                            if($counter == $today) echo "\t\t<td bgcolor=\"#FF0000\">$counter</td>\n";
                                            //don't make a link for the weekends
                                            else if($rowcounter % $numinrow == 0 || $rowcounter ==1) echo "\t\t<td>$counter</td>\n";
                                            //create a link to switch days
                                            else echo "\t\t<td><a href=m_cal.php?provider=$provider&day=$counter&month=$thismonth>$counter</a></td>\n";
                                            
                                            if( $rowcounter % $numinrow == 0 ) {
                                                echo "\t</tr>\n";
                                                
                                                if( $counter < $numdaysinmonth ) {
                                                    echo "\t<tr>\n";
                                                }
                                                
                                                $rowcounter = 0;
                                            }
                                        }
                                        
                                        // clean up
                                        $numcellsleft = $numinrow - $rowcounter;
                                        
                                        if( $numcellsleft != $numinrow ) {
                                            
                                            for( $counter = 0; $counter < $numcellsleft; $counter ++ ) {
                                                echo "\t\t<td>-</td>\n";
                                                $emptycells ++;
                                            }
                                        }
                                    ?>
                                </tr>

                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-7 col-md-7 col-lg-7 col-sm-offset-1 col-md-offset-1 col-lg-offset-1 jumbotronA custom-file-upload">
                    <?php
            
                        //query the providers
                        $qs = "SELECT concat(fname,' ',lname) as name, id FROM users WHERE authorized = 1";
            
            
                        //die($qs);
                        //$qy = $db->addQuery($qs);
                        $rs = sqlStatement($qs);
            
                        if (is_null($rs))
                        {
                            die("ERROR: Couldn't execute query.");
                        }
                        //create the providers table
                        while ($row = sqlFetchArray($orders)) {
                            @extract($row);
                
                            $output = '<TABLE WIDTH="100%"><TR><TD  bgcolor="#C0C0C0">Providers</TD></TR>';
                            $output .= "<tr><td align=center";
                            if($provider==-1) $output .= " bgcolor=\"#FF0000\" ";
                            $output .= "><a href=m_cal.php?day=$today&month=$thismonth&provider=-1>All Providers</a></td></tr>";
                
                            $output .= "<TR>";
                            $output .= "<td align=center";
                            if ($provider == $row["id"]) {
                                $output .= " bgcolor=\"#FF0000\" ";
                            }
                            $output .= "><a href=m_cal.php?day=$today&month=$thismonth&provider=" . $row["id"] . ">" . $row["name"] . "</a></td>";
                            $output .= "</TR>";
                        }
                        $output .= "</TABLE>";
                        echo $output;
            
                        //query to get the appointments based on provider and day
                        $qs = "SELECT pc_eid, concat(fname,' ',lname) as name, pc_startTime, pc_hometext, pc_catname, pc_catcolor FROM openemr_postcalendar_events
LEFT JOIN patient_data ON openemr_postcalendar_events.pc_pid = patient_data.pid
LEFT JOIN openemr_postcalendar_categories ON  openemr_postcalendar_events.pc_catid = openemr_postcalendar_categories.pc_catid
WHERE pc_eventDate = \"$thisyear-$thismonth-$today\"";
            
                        if($provider>-1) $qs .= " AND pc_aid=$provider";
            
                        $qs .= " ORDER BY pc_startTime asc";
            
                        //die($qs);
                        //$qy = $db->addQuery($qs);
                        $qy = sqlStatement($qs);
            
                        if (is_null($qy))
                        {
                            die("ERROR: Couldn't execute query.");
                        }
            
                        $output = '<TABLE WIDTH="100%">';
                        //if no appointments are found, output a notice
                        //if($rs->eof()) $output .= "<TR><TD>No Patients Found</TD></TR>";
                        $p_count = 0;
                        while ($row = sqlFetchArray($qy)) {
                
                            $output .= "<TR>";

//save the hometext (note)
                            $hometext = $row["pc_hometext"];
//check to see if the appoitment is for a person or location
                            if(is_null($row["name"])){
                                //if it is for a location, use the category name
                                $output .= "<TD ALIGN=center bgcolor=".$row["pc_catcolor"].">";
                                //check if the appointment has a note
                                if(!empty($hometext)){
                                    //change the name to have a * meaning a note is present
                                    $output .= "<a href=m_cal.php?day=$today&month=$thismonth&provider=$provider&eid=".$row["pc_eid"]."&offset=".$p_count.">";
                                    $output .= $row["pc_catname"]." *</TD>";
                                }
                                //no note
                                else $output .= $row["pc_catname"]."</TD>";
                            }
                            else {
                                //otherwise this is a patient
                                $output .= "<TD ALIGN=center bgcolor=".$row["pc_catcolor"].">";
                                if(!empty($hometext)){
                                    //change the name to have a * meaning a note is present
                                    $output .= "<a href=m_cal.php?day=$today&month=$thismonth&provider=$provider&eid=".$row["pc_eid"]."&offset=".$p_count.">";
                                    $output .= $row["name"]." *</TD>";
                                }
                                else {
                                    //no note
                                    $output .= $row["name"]."</TD>";
                                }
                            }
//output the time in a clear format
                            $output .= "<TD ALIGN=center bgcolor=".$row["pc_catcolor"].">".date("g:i a", strtotime($row["pc_startTime"]))."</TD>";

//if the user wants to view a note, show it
                            if($eid == $row["pc_eid"]){
                                $output .= "</TR><TR><TD COLSPAN=2>".$row["pc_hometext"]."</TD>";
                            }
                
                            $output .= "</TR>";
                            $p_count++;
                
                        }
                        $output .= "</TABLE>";
            
            
                        $output .= " ";
                        echo $output;
                    ?>
                </div>
            </div>
            
        </form>
    </div>

<?php common_footer($display); ?>
</body>

</html>
