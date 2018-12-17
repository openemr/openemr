<?php

/**
 * interface/main/mobile/m_cal.php
 *
 * Basic Calendar
 *
 * Copyright (C) 2018 Raymond Magauran <magauran@MedExBank.com>
 *
 * @package OpenEMR
 * @author Ray Magauran <magauran@MedExBank.com>
 * @link http://www.open-emr.org
 * @copyright Copyright (c) 2018 MedEx <magauran@MedExBank.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once "../../globals.php";
require_once "$srcdir/patient.inc";
require_once "$srcdir/options.inc.php";
require_once "m_functions.php";

$detect             = new Mobile_Detect;
$device_type        = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
$script_version     = $detect->getScriptVersion();
$display = "cal";

?><!doctype html>
<html style="cursor: pointer;">
<?php
    common_head();
?>

<body style="background-color: #fff;" >
<?php common_header($display); ?>

    <?php
    if (!empty($_GET['eid'])) {
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
    if ($thismonth == '12') {
        $lastmonth = $thismonth-1;
        $lastyear = $thisyear;
        
        $nextmonth = '1';
        $nextyear = $thisyear+1;
    } else if ($thismonth == '1') {
        $lastmonth = '12';
        $lastyear = $thisyear-1;
    
        $nextmonth = '1';
        $nextyear = $thisyear + 1;
    } else {
        $lastmonth = $thismonth-1;
        $lastyear = $thisyear;
        
        $nextmonth =$thismonth+1;
        $nextyear = $thisyear;
    }
        // find out the number of days in the month
        $numdaysinmonth = cal_days_in_month(CAL_GREGORIAN, $thismonth, $thisyear);
        
        // create a calendar object
        $jd = cal_to_jd(CAL_GREGORIAN, $thismonth, date(1), $thisyear);
        
        // get the start day as an int (0 = Sunday, 1 = Monday, etc)
        $startday = jddayofweek($jd, 0);
        
        // get the month as a name
        $monthname = jdmonthname($jd, 1);
    ?>
    <div id="gb-main" class="container-fluid">
        <form id="save_media" name="save_media" action="#" method="post" enctype="multipart/form-data">

            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                    <img src="<?php echo $GLOBALS['images_static_relative']; ?>/calendar.png" id="head_img" alt="OpenEMR <?php echo xla('Calendar'); ?>">
                </div>
                <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 text-center">
                    <div class="row text-center">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 custom-file-upload">
                            <table WIDTH="100%">
                                <tr>
                                    <td colspan="2" bgcolor="#C0C0C0"><div align="center"><a href=m_cal.php?provider=<?php echo attr_url($provider); ?>&day=<?php echo attr_url($today); ?>&month=<?php echo attr_url($lastmonth); ?>><?php echo text(jdmonthname(cal_to_jd(CAL_GREGORIAN, $lastmonth, date(1), $lastyear), 0)); ?></a></div></td>
                                    <td colspan="3" bgcolor="#C0C0C0"><div align="center"><strong><?php echo text($monthname); ?></strong></div></td>
                                    <td colspan="2" bgcolor="#C0C0C0"><div align="center"><a href=m_cal.php?provider=<?php attr_url($provider); ?>&day=<?php echo attr_url($today) ?>&month=<?php echo attr_url($nextmonth); ?>><?php echo jdmonthname(cal_to_jd(CAL_GREGORIAN, $nextmonth, date(1), $nextyear), 0); ?></a></div></td>
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
                                            echo "<td bgcolor=\"#FF0000\">".$counter."</td>\n";
                                        } else {
                                            echo "<td><a href=m_cal.php?provider=".attr_url($provider)."&day=".attr_url($counter)."&month=".attr_url($thismonth).">".text($counter)."</a></td>\n";
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
                         <span class="section_title"><?php echo xlt('Schedule'); ?>:
                         
                         <?php
                            $query = "SELECT concat(fname,' ',lname) as name, id FROM users WHERE authorized = 1";
                            $prov_results = sqlStatement($query);
                            
                            if (is_null($prov_results)) {
                                die("ERROR: Couldn't find any providers!");
                            }
                        
                            if (sqlNumRows($prov_results) ==1) {
                                $row = sqlFetchArray($prov_results);
                                echo text($row["name"]);
                            } else {
                                ?>
                                <select name="prov_select" id="prov_select">
                                    <option <?php echo((empty($provider) ? 'selected="selected"' : '')); ?>>
                                        <?php echo xlt('All Providers'); ?>
                                    </option>
        
                                    <?php
                                        while ($row = sqlFetchArray($prov_results)) {
                                            ?>
                                            <option <?php echo (($provider == $row["id"]) ? 'selected="selected"' : ''); ?> value="<?php echo attr($row['id']); ?>">
                                                <?php echo text($row["name"]); ?>
                                            </option>
                                            <?php
                                        } ?>
                                </select>
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
                                $query .= " AND e.pc_aid=?";
                                array_push($sqlBindArray, $provider);
                            }
                
                            $query .= " ORDER BY e.pc_startTime asc";
                            
                            $sqlBindArray = array();
                            array_push($sqlBindArray,$thisyear."-".$thismonth."-".$today);
                            $result = sqlStatement($query, $sqlBindArray);
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
                                echo '<div class="cal_cat" style="background-color:' . attr($row["pc_catcolor"]) . '">';
                                echo text($row["pc_catname"]) . " ". text($row['pc_hometext']) ."</div>";
                                continue;
                            }
                            echo '<div class="col-xs-12 visit" style="background-color:' . attr($row["pc_catcolor"]) . '">';
                            echo '<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">';
                            echo text($row["name"]);
                            echo "</div>";
                            echo '<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">' . text($row["pc_hometext"]) . "</div>";
                            echo '<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">' . oeFormatTime($row["pc_startTime"]) . "</div>";
                    echo "</div>";
    
                        }
                        ?>
                     </div>
                     </div>
            </div>
        </form>
    </div>

<?php common_footer($display); ?>
<script>
    $(document).ready(function() {
        $("#prov_select").change(function () {
            var prov_sched = this.value;
            top.restoreSession();
            window.location = "m_cal.php?provider="+prov_sched+"&day=<?php echo js_url($day); ?> + "&month=" + <?php echo js_url($month); ?>;
        });
    });
</script>
</body>

</html>
