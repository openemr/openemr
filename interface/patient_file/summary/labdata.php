<?php

/**
 * How to present clinical parameter.
 *
 *
 * this script needs $pid to run...
 *
 * if you copy this file to another place,
 * make sure you set $path_to_this_script
 * to the proper path...
 * Prepare your data:
 * this script expects proper 'result_code' entries
 * in table 'procedure_results'. If your data miss
 * 'result_code' entries, you won't see anything,
 * so make sure they are there.
 * [additionally, the script will also look for 'units',
 * 'range' and 'code_text'. If these data are not available,
 * the script will run anyway...]
 *
 * the script will list all available patient's 'result_codes'
 * from table 'procedure_results'. Check those you wish to view.
 * If you see nothing to select, then
 *    a) there is actually no lab data of this patient available
 *    b) the lab data are missing 'result_code'-entries in table 'procedure_results'
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Joe Slam <trackanything@produnis.de>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2014 Joe Slam <trackanything@produnis.de>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("../../../library/options.inc.php");
require_once($GLOBALS["srcdir"] . "/api.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// Set the path to this script
$path_to_this_script = $rootdir . "/patient_file/summary/labdata.php";


// is this the printable HTML-option?
$printable = $_POST['print'];


// main db-spell
//----------------------------------------
$main_spell  = "SELECT procedure_result.procedure_result_id, procedure_result.result, procedure_result.result_text,  procedure_result.result_code, procedure_result.units, procedure_result.abnormal, procedure_result.range, ";
$main_spell .= "procedure_report.date_collected, procedure_report.review_status, ";
$main_spell .= "procedure_order.encounter_id ";
$main_spell .= "FROM procedure_result ";
$main_spell .= "JOIN procedure_report ";
$main_spell .= "	ON procedure_result.procedure_report_id = procedure_report.procedure_report_id ";
$main_spell .= "JOIN procedure_order ";
$main_spell .= "	ON procedure_report.procedure_order_id = procedure_order.procedure_order_id ";
$main_spell .= "WHERE procedure_result.result_code = ? "; // '?'
$main_spell .= "AND procedure_order.patient_id = ? ";
$main_spell .= "AND procedure_result.result IS NOT NULL ";
$main_spell .= "AND procedure_result.result != ''";
$main_spell .= "ORDER BY procedure_report.date_collected DESC ";
//----------------------------------------

// some styles and javascripts
// ####################################################
?>
<html>
<head>
<title><?php echo xlt("Labs"); ?></title>

<?php require $GLOBALS['srcdir'] . '/js/xl/dygraphs.js.php'; ?>

<?php Header::setupHeader('dygraphs'); ?>

<?php if ($_SESSION['language_direction'] == "rtl") { ?>
  <link rel="stylesheet" href="<?php echo $GLOBALS['themes_static_relative']; ?>/misc/rtl_labdata.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" />
<?php } else { ?>
  <link rel="stylesheet" href="<?php echo $GLOBALS['themes_static_relative']; ?>/misc/labdata.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" />
<?php } ?>

<script>
function checkAll(bx) {
    for (var tbls=document.getElementsByTagName("table"), i=tbls.length; i--; ) {
        for (var bxs=tbls[i].getElementsByTagName("input"), j=bxs.length; j--; ) {
        if (bxs[j].type=="checkbox") {
            bxs[j].checked = bx.checked;
        }
      }
    }
}
</script>
</head>
<body>
    <div class="container mt-3">
        <div class="row">
            <div class="col-12">
                <h2><?php echo xlt('Labs'); ?></h2>
                <?php if (!$printable) { ?>
                <div class="form-row">
                    <div class="col-md">
                        <a href='../summary/demographics.php' class='btn btn-secondary btn-back' onclick='top.restoreSession()'>
                            <span><?php echo xlt('Back to Patient') ?></span>
                        </a>
                    </div>
                    <div class="col-md text-right">
                        <input type='checkbox' onclick="checkAll(this)" /> <?php echo xlt('Toggle All'); ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            <div class='col-12 jumbotron py-4 mt-3' id='labdata'>
                <?php
                // some patient data...
                $spell  = "SELECT * ";
                $spell .= "FROM patient_data ";
                $spell .= "WHERE pid = ?";
                //---
                $myrow = sqlQuery($spell, array($pid));
                $lastname = $myrow["lname"];
                $firstname  = $myrow["fname"];
                $DOB  = $myrow["DOB"];
                ?>

                <?php
                if ($printable) { ?>
                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <td><?php echo xlt('Patient'); ?></td>
                                <td class="font-weight-bold"><?php echo text($lastname) . ", " . text($firstname) ?></td>
                            </tr>
                            <tr>
                                <td><?php echo xlt('Patient ID') ?></td>
                                <td><?php echo text($pid) ?>"</td>
                            </tr>
                            <tr>
                                <td><?php echo xlt('Date of birth') ?></td>
                                <td><?php echo text($DOB) ?>"</td>
                            </tr>
                            <tr>
                                <td><?php echo xlt('Access date') ?></td>
                                <td><?php echo text(date('Y-m-d - H:i:s')) ?>"</td>
                            </tr>
                        </table>
                    </div>
                <?php } ?>

                <?php if (!$printable) { ?>
                    <form method='post' action='<?php echo attr($path_to_this_script); ?>' onsubmit='return top.restoreSession()'>
                        <div id='reports_list'>
                            <h3><?php echo xlt('Select items'); ?>:</h3>
                            <div class="table-responsive">
                                <table class="table table-borderless border border-dark">
                                    <tr>
                                        <?php
                                        // What items are there for patient $pid?
                                        // -----------------------------------------------
                                        $value_list = array();
                                        $value_select = $_POST['value_code']; // what items are checkedboxed?
                                        $tab = 0;
                                        echo "<td>";

                                        $spell  = "SELECT DISTINCT procedure_result.result_code AS value_code ";
                                        $spell .= "FROM procedure_result ";
                                        $spell .= "JOIN procedure_report ";
                                        $spell .= "	ON procedure_result.procedure_report_id = procedure_report.procedure_report_id ";
                                        $spell .= "JOIN procedure_order ";
                                        $spell .= "	ON procedure_report.procedure_order_id = procedure_order.procedure_order_id ";
                                        $spell .= "WHERE procedure_order.patient_id = ? ";
                                        $spell .= "AND procedure_result.result IS NOT NULL ";
                                        $spell .= "AND procedure_result.result != ''";
                                        $spell .= "ORDER BY procedure_result.result_code ASC ";
                                        $query  = sqlStatement($spell, array($pid));

                                        // Select which items to view...
                                        $i = 0;
                                        while ($myrow = sqlFetchArray($query)) {
                                            echo "<input type='checkbox' name='value_code[]' value=" . attr($myrow['value_code']) . " ";
                                            if ($value_select) {
                                                if (in_array($myrow['value_code'], $value_select)) {
                                                    echo "checked='checked' ";
                                                }
                                            }

                                            echo " /> " . text($myrow['value_code']) . "<br />";
                                            $value_list[$i]['value_code'] = $myrow['value_code'];
                                            $i++;
                                            $tab++;
                                            if ($tab == 10) {
                                                echo "</td><td>";
                                                $tab = 0;
                                            }
                                        }
                                        ?>
                                    </tr>
                                </table>
                            </div>
                        </div> <!-- ends of reports_list -->
                        <hr/>
                        <!-- Choose output mode [list vs. matrix] -->
                        <h3><?php echo xlt('Select output'); ?>:</h3>
                        <div class="form-row">
                            <div class="form-check form-check-inline">
                                <?php
                                echo "<td><input type='radio' name='mode' ";
                                $mode = $_POST['mode'];
                                if ($mode == 'list') {
                                    echo "checked='checked' ";
                                }
                                echo " value='list' /> " . xlt('List') . "<br />";
                                ?>
                            </div>
                            <div class="form-check form-check-inline">
                                <?php
                                echo "<input type='radio' name='mode' ";
                                if ($mode != 'list') {
                                    echo "checked='checked' ";
                                }
                                echo " value='matrix' /> " . xlt('Matrix') . "<br /></td>";
                                ?>
                            </div>
                        </div>
                        <button type='submit' name='submit' class='btn btn-primary btn-save mt-2' value='<?php echo xla('Submit'); ?>'>
                            <?php echo xlt('Submit'); ?>
                        </button>
                    </form>
                    <!-- end "if printable" ? -->
                <?php } ?>
                <hr>

                <?php
                // print results of patient's items
                //-------------------------------------------
                $mode = $_POST['mode'];
                $value_select = $_POST['value_code'];
                // are some Items selected?
                if ($value_select) {
                    // print in List-Mode
                    if ($mode == 'list') {
                        $i = 0;
                        $item_graph = 0;
                        $rowspan = count($value_select);
                        echo "<div class='table-responsive'><table class='border table'>";
                        echo "<tr>";
                        #echo "<th class='list'>Item</td>";
                        echo "<th class='list'>" . xlt('Name') . "</th> ";
                        echo "<th class='list'>&nbsp;" . xlt('Result') . "&nbsp;</th> ";
                        echo "<th class='list'>" . xlt('Range') . "</th> ";
                        echo "<th class='list'>" . xlt('Units') . "</th> ";
                        echo "<th class='list'>" . xlt('Date') . "</th> ";
                        echo "<th class='list'>" . xlt('Review') . "</th> ";
                        echo "<th class='list'>" . xlt('Enc') . "</th> ";
                        #echo "<th class='list'>resultID</th> ";
                        echo "</tr>";
                        // get complete data of each item
                        foreach ($value_select as $this_value) {
                            // set a plot-spacer
                            echo "<tr><td colspan='7'><div id='graph_item_" . attr($item_graph) . "' class='chart-dygraphs'></div></td></tr>";
                            $value_count = 0;
                            $value_array = array(); // reset local array
                            $date_array  = array();//  reset local array

                            // get data from db
                            $spell  = $main_spell;
                            $query  = sqlStatement($spell, array($this_value,$pid));
                            while ($myrow = sqlFetchArray($query)) {
                                $value_array[0][$value_count]   = $myrow['result'];
                                $date_array[$value_count]   = $myrow['date_collected'];
                                $the_item = $myrow['result_text'];
                                echo "<tr>";
                                echo "<td class='list_item'>" . text($myrow['result_text']) . "</td>";


                                if ($myrow['abnormal'] == 'No' || $myrow['abnormal'] == 'no'  || $myrow['abnormal'] == '' || $myrow['abnormal'] == null) {
                                    echo "<td class='list_result'>&nbsp;&nbsp;&nbsp;" . text($myrow['result']) . "&nbsp;&nbsp;</td>";
                                } else {
                                    echo "<td class='list_result_abnorm'>&nbsp;" ;
                                    if ($myrow['abnormal'] == 'high') {
                                        echo "+ ";
                                    } elseif ($myrow['abnormal'] == 'low') {
                                        echo "- ";
                                    } else {
                                        echo "&nbsp;&nbsp;";
                                    }

                                    echo text($myrow['result']) . "&nbsp;&nbsp;</td>";
                                }

                                echo "<td class='list_item'>" . text($myrow['range'])       . "</td>";

                                // echo "<td class='list_item'>" . generate_display_field(array('data_type'=>'1','list_id'=>'proc_unit'),$myrow['units']) . "</td>";
                                echo "<td class='list_item'>" . text($myrow['units']) . "</td>";

                                echo "<td class='list_log'>"  . text($myrow['date_collected']) . "</td>";
                                echo "<td class='list_log'>"  . text($myrow['review_status']) . "</td>";
                                echo "<td class='list_log'>";
                                if (!$printable) {
                                    echo "<a href='../../patient_file/encounter/encounter_top.php?set_encounter=" . attr_url($myrow['encounter_id']) . "' target='RBot'>";
                                    echo text($myrow['encounter_id']);
                                    echo "</a>";
                                } else {
                                    echo text($myrow['encounter_id']);
                                }

                                echo "</td>";
                                echo "</tr>";
                                $value_count++;
                            }

                            if ($value_count > 1 && !$printable) {
                                echo "<tr><td colspan='7' class='text-center'>";
                                echo "<input type='button' class='graph_button btn btn-secondary' onclick='get_my_graph" . attr($item_graph) . "()' name='' value='" . xla('Plot item') . " \"" . attr($the_item) . "\"'>";
                                echo "</td></tr>";
                            }
                            ?>
                            <script>
                            // prepare to plot the stuff
                            top.restoreSession();
                            function get_my_graph<?php echo attr($item_graph) ?>(){
                                var thedates = JSON.stringify(<?php echo js_escape($date_array); ?>);
                                var thevalues =  JSON.stringify(<?php echo js_escape($value_array); ?>);
                                var theitem = JSON.stringify(<?php echo js_escape(array($the_item)); ?>);
                                var thetitle = JSON.stringify(<?php echo js_escape($the_item); ?>);
                                var checkboxfake = JSON.stringify(<?php echo js_escape(array(0)); ?>);

                                $.ajax({ url: '<?php echo $web_root; ?>/library/ajax/graph_track_anything.php',
                                        type: 'POST',
                                        data: { dates:  thedates,
                                                values: thevalues,
                                                track:  thetitle,
                                                items:  theitem,
                                                thecheckboxes: checkboxfake,
                                                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                                            },
                                        dataType: "json",
                                        success: function(returnData){
                                            g2 = new Dygraph(
                                                document.getElementById(<?php echo js_escape('graph_item_' . $item_graph) ?>),
                                                returnData.data_final,
                                                {
                                                    title: returnData.title,
                                                    delimiter: '\t',
                                                    xRangePad: 20,
                                                    yRangePad: 20,
                                                    xlabel: xlabel_translate
                                                }
                                            );
                                        },
                                            error: function (XMLHttpRequest, textStatus, errorThrown) {
                                            alert(XMLHttpRequest.responseText);
                                        }
                                }); // end ajax query
                            }
                            //------------------------------------------------------------------------
                            </script>
                            <?php
                            echo "<tr><td colspan='9'  class='list_spacer'><hr></td></tr>";
                            $item_graph++;
                        }

                        echo "</table></div><br />";
                    }// end if mode = list

                    //##########################################################################################################################
                    if ($mode == 'matrix') {
                        $value_matrix = array();
                        $datelist = array();
                        $i = 0;
                        // get all data of patient's items
                        foreach ($value_select as $this_value) {
                            $spell  = $main_spell;
                            $query  = sqlStatement($spell, array($this_value,$pid));

                            while ($myrow = sqlFetchArray($query)) {
                                $value_matrix[$i]['procedure_result_id']  = $myrow['procedure_result_id'];
                                $value_matrix[$i]['result_code']          = $myrow['result_code'];
                                $value_matrix[$i]['result_text']          = $myrow['result_text'];
                                $value_matrix[$i]['result']               = $myrow['result'];
                                // $value_matrix[$i]['units']                 = generate_display_field(array('data_type'=>'1','list_id'=>'proc_unit'),$myrow['units']) ;
                                $value_matrix[$i]['units']                = $myrow['units'];
                                $value_matrix[$i]['range']                = $myrow['range'];
                                $value_matrix[$i]['abnormal']             = $myrow['abnormal'];
                                $value_matrix[$i]['review_status']        = $myrow['review_status'];
                                $value_matrix[$i]['encounter_id']         = $myrow['encounter_id'];
                                $value_matrix[$i]['date_collected']       = $myrow['date_collected'];
                                $datelist[]                             = $myrow['date_collected'];
                                $i++;
                            }
                        }

                        // get unique datetime
                        $datelist = array_unique($datelist);

                        // sort datetime DESC
                        rsort($datelist);

                        // sort item-data
                        foreach ($value_matrix as $key => $row) {
                            $result_code[$key] = $row['result_code'];
                            $date_collected[$key] = $row['date_collected'];
                        }

                        array_multisort(array_map('strtolower', $result_code), SORT_ASC, $date_collected, SORT_DESC, $value_matrix);

                        $cellcount = count($datelist);
                        $itemcount = count($value_matrix);

                        // print matrix
                        echo "<div class='table-responsive'><table class='border table' cellpadding='2'>";
                        echo "<tr>";
                        #echo "<th class='matrix'>Item</th>";
                        echo "<th class='matrix'>" . xlt('Name') . "</th>";
                        echo "<th class='matrix'>" . xlt('Range') . "</th>";
                        echo "<th class='matrix'>" . xlt('Unit') . "</th>";
                        echo "<th class='matrix_spacer'>|</td>";
                        foreach ($datelist as $this_date) {
                            echo "<th width='30' class='matrix_time'>" . text($this_date) . "</th>";
                        }

                        echo "</tr>";

                        $i = 0;
                        $a = true;
                        while ($a == true) {
                            echo "<tr>";
                            #echo "<td class='matrix_item'>" . text($value_matrix[$i]['result_code']) . "</td>";
                            echo "<td class='matrix_item'>" . text($value_matrix[$i]['result_text']) . "</td>";
                            echo "<td class='matrix_item'>" . text($value_matrix[$i]['range']) . "</td>";
                            echo "<td class='matrix_item'>" . text($value_matrix[$i]['units']) . "</td>";
                            echo "<td class='matrix_spacer'> | </td>";

                            $z = 0;
                            while ($z < $cellcount) {
                                if ($value_matrix[$i]['date_collected'] == $datelist[$z]) {
                                    if ($value_matrix[$i]['result'] == null) {
                                        echo "<td class='matrix_result'> </td>";
                                    } else {
                                        if ($value_matrix[$i]['abnormal'] == 'No' || $value_matrix[$i]['abnormal'] == 'no'  || $value_matrix[$i]['abnormal'] == '' || $value_matrix[$i]['abnormal'] == null) {
                                            echo "<td class='matrix_result'>&nbsp;&nbsp;&nbsp;" . text($value_matrix[$i]['result']) . "&nbsp;&nbsp;</td>";
                                        } else {
                                            echo "<td class='matrix_result_abnorm'>&nbsp;&nbsp;" ;
                                            if ($value_matrix[$i]['abnormal'] == 'high') {
                                                echo "+ ";
                                            } elseif ($value_matrix[$i]['abnormal'] == 'low') {
                                                echo "- ";
                                            }

                                            echo text($value_matrix[$i]['result']) . "&nbsp;&nbsp;</td>";
                                        }
                                    }

                                    $j = $i;
                                    $i++;

                                    if ($value_matrix[$i]['result_code'] != $value_matrix[$j]['result_code']) {
                                        $z = $cellcount;
                                    }
                                } else {
                                    echo "<td class='matrix_result'>&nbsp;</td>";
                                }

                                $z++;
                            }

                            if ($i == $itemcount) {
                                $a = false;
                            }
                        }
                        echo "</table></div>";
                    }// end if mode = matrix
                } else { // end of "are items selected"
                    echo "<p>" . xlt('No parameters selected') . ".</p>";
                    $nothing = true;
                }

                if (!$printable) {
                    if (!$nothing) {
                        echo "<p>";
                        echo "<form method='post' action='" . attr($path_to_this_script) . "' target='_new' onsubmit='return top.restoreSession()'>";
                        echo "<input type='hidden' name='mode' value='" . attr($mode) . "' />";
                        foreach ($_POST['value_code'] as $this_valuecode) {
                            echo "<input type='hidden' name='value_code[]' value='" . attr($this_valuecode) . "' />";
                        }

                        echo "<input type='submit' name='print' class='btn btn-primary' value='" . xla('View Printable Version') . "' />";
                        echo "</form>";
                    }
                } else {
                    echo "<p>" . xlt('End of report') . ".</p>";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>

