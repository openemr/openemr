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

/*/remember state long after session
$uspfx              = substr(__FILE__, strlen($webserver_root)) . '.';
$setting_date       = prevSetting($uspfx, 'setting_date', 'setting_date', $day);
$setting_month      = prevSetting($uspfx, 'setting_month', 'setting_month', $month);
$setting_year       = prevSetting($uspfx, 'setting_year', 'setting_year', $year);
*/
?><!doctype html>
<html style="cursor: pointer;">
<?php
    common_head();
?>

<style>
    .jumbotronA {margin: 8px auto 40px;display:inherit;}
</style>
<body style="background-color: #fff;" >
<?php common_header($display); ?>

    <?php
    if ($_GET['eid']) {
        echo "<script>$(document).ready(function () { ScrollIt(); }); </script>";
    }
        
        $day        = $_GET['day'];
        $month      = $_GET['month'];
        $provider   = $_GET['provider'];
        $eid        = $_GET['eid'];

        //if no provider is assigned, return all appointments
    if (is_null($provider) && empty($provider) || !is_numeric($provider)) {
        $provider = '';
    }
        
        //if no day and month are provided, use the current date
    if ((is_null($day) || empty($day) && is_null($month) || empty($month)) || !is_numeric($day) || !is_numeric($month)) {
        $thismonth  = ( int ) date("m");
        $thisyear   = date("Y");
        $today      = date("j");
    } else {
        $thismonth = $month;
        $thisyear = date("Y");
        $today = $day;
    }
        // find out the number of days in the month
        $numdaysinmonth = cal_days_in_month(CAL_GREGORIAN, $thismonth, $thisyear);
        
        // create a calendar object
        $jd = cal_to_jd(CAL_GREGORIAN, $thismonth, date(1), $thisyear);
        
        // get the start day as an int (0 = Sunday, 1 = Monday, etc)
        $startday = jddayofweek($jd, 0);
        
        // get the month as a name
        $monthname = jdmonthname($jd, 1)
    ?>
    <div id="gb-main" class="container-fluid">
        <form id="save_media" name="save_media" action="#" method="post" enctype="multipart/form-data">

            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                    <img src="<?php echo $GLOBALS['webroot']; ?>/public/images/calendar.png" id="head_img" alt="OpenEMR <?php echo xla('Calendar'); ?>">
                </div>
                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 text-center">
                    <div class="row text-center">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 custom-file-upload">
                            <table WIDTH="100%">
                                <tr>
                                    <td colspan="2" bgcolor="#C0C0C0"><div align="center"><a href=m_cal.php?provider=<?php echo $provider ?>&day=<?php echo $today ?>&month=<?php echo $thismonth-1 ?>><?php echo jdmonthname(cal_to_jd(CAL_GREGORIAN, $thismonth-1, date(1), $thisyear), 0) ?></a></div></td>
                                    <td colspan="3" bgcolor="#C0C0C0"><div align="center"><strong><?php echo $monthname ?></strong></div></td>
                                    <td colspan="2" bgcolor="#C0C0C0"><div align="center"><a href=m_cal.php?provider=<?php $provider ?>&day=<?php echo $today ?>&month=<?php echo $thismonth+1 ?>><?php echo jdmonthname(cal_to_jd(CAL_GREGORIAN, $thismonth+1, date(1), $thisyear), 0) ?></a></div></td>
                                </tr>
                                <tr>
                                    <td><strong><?php echo xlt('S{{Sunday}}'); ?></strong></td>
                                    <td><strong><?php echo xlt('M{{Monday}}'); ?></strong></td>
                                    <td><strong><?php echo xlt('T{{Tuesday}}'); ?></strong></td>
                                    <td><strong><?php echo xlt('W{{Wednesday}}'); ?></strong></td>
                                    <td><strong><?php echo xlt('T{{Thursday}}'); ?></strong></td>
                                    <td><strong><?php echo xlt('F{{Friday}}'); ?></strong></td>
                                    <td><strong><?php echo xlt('S{{Saturday}}'); ?></strong></td>
                                </tr>
                                <tr>
                                    <?php
                                        // put render empty cells
                                        $emptycells = 0;
                                        
                                    for ($counter = 0; $counter <  $startday; $counter ++) {
                                        echo "<td>-</td>\n";
                                        $emptycells ++;
                                    }
                                        
                                        // renders the days
                                        $rowcounter = $emptycells;
                                        $numinrow = 7;
                                        
                                    for ($counter = 1; $counter <= $numdaysinmonth; $counter ++) {
                                        $rowcounter ++;
                                        
                                        if ($counter == $today) {
                                            echo "<td bgcolor=\"#FF0000\">$counter</td>\n";
                                        } else {
                                            echo "<td><a href=m_cal.php?provider=$provider&day=$counter&month=$thismonth>$counter</a></td>\n";
                                        }
                                            
                                        if ($rowcounter % $numinrow == 0) {
                                            echo "</tr>\n";
                                                
                                            if ($counter < $numdaysinmonth) {
                                                echo "<tr>\n";
                                            }
                                                
                                            $rowcounter = 0;
                                        }
                                    }
                                        // clean up
                                        $numcellsleft = $numinrow - $rowcounter;
                                        
                                    if ($numcellsleft != $numinrow) {
                                        for ($counter = 0; $counter < $numcellsleft; $counter ++) {
                                            echo "<td>-</td>\n";
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
                     <div class="visit">
                         <span class="section_title"><?php echo xlt('Schedule'); ?></span>
                         <br />
                         <?php
                            $query = "SELECT concat(fname,' ',lname) as name, id FROM users WHERE authorized = 1";
                            $prov_results = sqlStatement($query);
                            
                            if (is_null($prov_results)) {
                                die("ERROR: Couldn't find any providers!");
                            }
                        
                            if (sqlNumRows($prov_results) ==1) {
                                $row = sqlFetchArray($prov_results);
                                echo "<div class='prov_line white'><a href='m_cal.php?day=$today&month=$thismonth&provider=" . attr($row["id"]) . "'>" . text($row["name"]) . "</a></div>";
                            } else {
                                ?>
                                <div class="visit <?php echo ((empty($provider) ? 'white' : '')); ?>">
                                <?php
                                    echo "<ax href='m_cal.php?day=$today&month=$thismonth'>". xlt('All Providers') ."</ax>";
                                    ?>
                                </div>
                                <?php
                            }
                            while ($row = sqlFetchArray($prov_results)) {
                                ?>
                                <div class="visit <?php echo(($provider == $row["id"]) ? 'white' : ''); ?>">
                                    <?php
                                        echo "<ax href='m_cal.php?day=$today&month=$thismonth&provider=" . attr($row["id"]) . "'>" . text($row["name"]) . "</a>";
                                    ?>
                                </div>
                                <?php
                            }
                            
                            //Need to incorporate acl here - user might not have privileges to see all schedules...
                            //query to get the appointments based on provider and day
                            $query = "SELECT pc_eid, concat(fname,' ',lname) as name, pc_startTime, pc_hometext, pc_catname, pc_catcolor
                                    FROM openemr_postcalendar_events as e
                                    LEFT OUTER JOIN patient_data as p ON e.pc_pid = p.pid
                                    LEFT JOIN openemr_postcalendar_categories as c ON  e.pc_catid = c.pc_catid
                                    WHERE pc_eventDate =?";
                
                            if (!empty($provider)) {
                                $query .= " AND e.pc_aid=$provider";
                            }
                
                            $query .= " ORDER BY e.pc_startTime asc";
                            $result = sqlStatement($query, array($thisyear."-".$thismonth."-".$today));
                //continue BS4 construct
                            //if no appointments are found, output a notice
                            if (sqlNumRows($result) < 1) {
                                echo "<div class='col-xs-12 alert alert-success'>". xlt('No Appointments Found') ."</div>";
                            } else {
                                //echo "<div class='alert alert-success'>".sqlNumRows($result)." ". xlt('Appointments Found') ."</div>";
                            }
                            $p_count = 0;
                            echo "</div>";
                        while ($row = sqlFetchArray($result)) {
                            if (is_null($row["name"])) {
                                echo '<div class="cal_cat" style="background-color:' . $row["pc_catcolor"] . '">';
                                echo $row["pc_catname"] . "</div>";
                                continue;
                            }
                            echo '<div class="col-xs-12 visit" style="background-color:' . $row["pc_catcolor"] . '">';
                            //check if the appointment has a note
                            echo '<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">';
                            if (!empty($row["pc_hometext"])) {
                                //plan to hide reason on small screen and display via js when clicked...  TODO
                                echo "<ax href='m_cal.php?day=$today&month=$thismonth&provider=$provider&eid=" . $row["pc_eid"] . "&offset=" . $p_count . "'>";
                                echo $row["name"];
                            } //no note
                            else {
                                echo $row["name"];
                            }
    
                            echo "</ax></div>";
                             //if the user wants to view a note, show it
                            echo '<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">' . $row["pc_hometext"] . "</div>";
                            //output the time in a clear format
                            echo '<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">'. date("g:i a", strtotime($row["pc_startTime"])) . "</div>";
    echo "</div>";
    
                        }
                        ?>
                     </div>
                     </div>
            </div>
        </form>
    </div>

<?php common_footer($display); ?>
</body>

</html>
