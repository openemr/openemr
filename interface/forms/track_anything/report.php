<?php

/**
 * Encounter form to track any clinical parameter.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Joe Slam <trackanything@produnis.de>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2014 Joe Slam <trackanything@produnis.de>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once($GLOBALS["srcdir"] . "/api.inc");

function track_anything_report($pid, $encounter, $cols, $id)
{
    #$patient_report_flag = 'no';
    echo "<div id='track_anything'>";
    global $web_root;
    $ofc_name = array();
    $ofc_date = array();
    $ofc_value = array();
    $row = 0; // how many rows
    $col = 0; // how many Items per row
    $dummy = array(); // counter to decide if graph-button is shown
    $formid = $id;
    $shownameflag = 0;
    echo "<div id='graph" . attr($formid) . "' class='chart-dygraphs'> </div><br />";
    echo "<table border='1'>";

    // get name of selected track, used for GraphTitle
    $spell  = "SELECT form_track_anything_type.name AS track_name ";
    $spell .= "FROM form_track_anything ";
    $spell .= "INNER JOIN form_track_anything_type ON form_track_anything.procedure_type_id = form_track_anything_type.track_anything_type_id ";
    $spell .= "WHERE id = ? AND form_track_anything_type.active = 1";
    $myrow = sqlQuery($spell, array($formid));
    $the_track_name = $myrow["track_name"];
    //------------

    // get correct track
    $spell0  = "SELECT DISTINCT track_timestamp ";
    $spell0 .= "FROM form_track_anything_results ";
    $spell0 .= "WHERE track_anything_id = ? ";
    $spell0 .= "ORDER BY track_timestamp DESC ";
    $query = sqlStatement($spell0, array($formid));

    // get all data of this specific track
    while ($myrow = sqlFetchArray($query)) {
        $thistime = $myrow['track_timestamp'];
        $shownameflag++;
        $spell  = "SELECT form_track_anything_results.itemid, form_track_anything_results.result, form_track_anything_type.name AS the_name ";
        $spell .= "FROM form_track_anything_results ";
        $spell .= "INNER JOIN form_track_anything_type ON form_track_anything_results.itemid = form_track_anything_type.track_anything_type_id ";
        $spell .= "WHERE track_anything_id = ? AND track_timestamp = ? AND form_track_anything_type.active = 1 ";
        $spell .= "ORDER BY form_track_anything_type.position ASC, the_name ASC ";
        $query2  = sqlStatement($spell, array($formid, $thistime));

        // is this the <tbale>-head?
        if ($shownameflag == 1) {
            echo "<tr><th class='time'>" . xlt('Time') . "</th>";
            while ($myrow2 = sqlFetchArray($query2)) {
                echo "<th class='item'>&nbsp;" . text($myrow2['the_name']) . "&nbsp;</th>";
                $ofc_name[$col] = $myrow2['the_name']; // save for chart-form
                $col++;
            }

            echo "</tr>";
        }

        // post data entries per row
        echo "<tr><td class='time'>" . text($thistime) . "</td>";
        $ofc_date[$row] = $thistime; // save for chart-form
        $col_i = 0; // how many columns
        $query2  = sqlStatement($spell, array($formid, $thistime));
        while ($myrow2 = sqlFetchArray($query2)) {
            echo "<td class='item'>&nbsp;" . text($myrow2['result']) . "&nbsp;</td>";
            if (is_numeric($myrow2['result'])) {
                    $ofc_value[$col_i][$row] = $myrow2['result'];// save for chart-form
            }

            $col_i++;
        }

        echo "</tr>";
        $row++;
    }



    // hide all interactive link stuff if inside a patient report
    // (to keep Patient Report clean...)
    // Thus we use "<div class='navigateLink'>"; see custom_report.php
    //--------------------------------------------------------------
    // Graph-Button row
    //-------------------------------
        echo "<tr>";
        echo "<td class='check'><div class='navigateLink'>" . xlt('Check items to graph') . "</div></td>";
    for ($col_i = 0; $col_i < $col; $col_i++) {
        echo "<td class='check'><div class='navigateLink'>";
        for ($row_b = 0; $row_b < $row; $row_b++) {
            // count more than 1 to show graph-button
            if (is_numeric($ofc_value[$col_i][$row_b])) {
                $dummy[$col_i]++;
            }
        }

        // show graph-button only if we have more than 1 valid data
        if ($dummy[$col_i] > 1) {
            echo "<input type='checkbox' name='check_col" . attr($formid) . "' value='" . attr($col_i) . "'>";
            $showbutton++;
        }

        echo "</div></td>";
    }

        echo "</tr>";

    // end Graph-Button-Row---------

    if ($showbutton > 0) {
        echo "<tr><td></td>";
        echo "<td colspan='" . attr($col) . "'><div class='navigateLink'>";
        echo "<input type='button' class='graph_button' ";
        echo " onclick='ta_report_plot_graph(" . attr_js($formid) . "," . attr_js($ofc_name) . "," . attr_js($the_track_name)  . "," . attr_js($ofc_date) . "," . attr_js($ofc_value) . ")'";
        echo " name='' value='" . xla('Plot selected Items') . "'>";
        echo "</div></td></tr>";
    }

    //---/end graph button------------------
        echo "</table>";
        echo "<br />";
    echo "<div class='navigateLink'>"; // see custom_report.php
        echo "<form method='post' action='../../forms/track_anything/history.php' onsubmit='return top.restoreSession()'>";
        echo "<input type='hidden' name='formid' value='" . attr($formid) . "'>";
        echo "<input type='submit' name='history' value='" . xla('Show track history') . "' />";
        echo "</form>";
    echo "</div>"; // end hide for report
        echo "</div>";
}// end function track_anything_report
