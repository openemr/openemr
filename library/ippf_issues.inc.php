<?php

/**
 * This module is invoked by add_edit_issue.php as an extension to
 * add support for issue types that are specific to IPPF.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2008-2009 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");

$CPR = 4; // cells per row

$pprow = array();

function end_cell()
{
    global $item_count, $cell_count;
    if ($item_count > 0) {
        echo "</td>";
        $item_count = 0;
    }
}

function end_row()
{
    global $cell_count, $CPR;
    end_cell();
    if ($cell_count > 0) {
        for (; $cell_count < $CPR; ++$cell_count) {
            echo "<td></td>";
        }

        echo "</tr>\n";
        $cell_count = 0;
    }
}

function end_group()
{
    global $last_group;
    if (strlen($last_group) > 0) {
        end_row();
        echo " </table>\n";
        echo "</div>\n";
    }
}

function issue_ippf_gcac_newtype()
{
    echo "  var gcadisp = (aitypes[index] == 3) ? '' : 'none';\n";
    echo "  document.getElementById('ippf_gcac').style.display = gcadisp;\n";
}

function issue_ippf_gcac_save($issue)
{
    $sets = "id = '" . add_escape_custom($issue) . "'";
    $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = 'GCA' AND uor > 0 AND field_id != '' AND edit_options != 'H' " .
    "ORDER BY group_id, seq");
    while ($frow = sqlFetchArray($fres)) {
        $field_id  = $frow['field_id'];
        $value = get_layout_form_value($frow);
        $sets .= ", " . add_escape_custom($field_id) . " = '" . add_escape_custom($value) . "'";
    }

  // This replaces the row if its id exists, otherwise inserts it.
    sqlStatement("REPLACE INTO lists_ippf_gcac SET $sets");
}

function issue_ippf_gcac_form($issue, $thispid)
{
    global $pprow, $item_count, $cell_count, $last_group;

    $shrow = getHistoryData($thispid);

    if ($issue) {
        $pprow = sqlQuery("SELECT * FROM lists_ippf_gcac WHERE id = ?", array($issue));
    } else {
        $pprow = array();
    }

    echo "<div id='ippf_gcac' style='display:none'>\n";

    // Load array of properties for this layout and its groups.
    $grparr = array();
    getLayoutProperties('GCA', $grparr);

    $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = 'GCA' AND uor > 0 " .
    "ORDER BY group_id, seq");
    $last_group = '';
    $cell_count = 0;
    $item_count = 0;
    $display_style = 'block';

    while ($frow = sqlFetchArray($fres)) {
        $this_group = $frow['group_id'];
        $titlecols  = $frow['titlecols'];
        $datacols   = $frow['datacols'];
        $data_type  = $frow['data_type'];
        $field_id   = $frow['field_id'];
        $list_id    = $frow['list_id'];

        $currvalue  = '';

        if ($frow['edit_options'] == 'H') {
            // This data comes from static history
            if (isset($shrow[$field_id])) {
                $currvalue = $shrow[$field_id];
            }
        } else {
            if (isset($pprow[$field_id])) {
                $currvalue = $pprow[$field_id];
            }
        }

        // Handle a data category (group) change.
        if (strcmp($this_group, $last_group) != 0) {
            end_group();
            $group_seq  = 'gca' . substr($this_group, 0, 1);
            $group_name = $grparr[$this_group]['grp_title'];
            $last_group = $this_group;
            echo "<br /><span class='bold'><input type='checkbox' name='form_cb_" . attr($group_seq) . "' value='1' " .
            "onclick='return divclick(this," . attr_js('div_' . $group_seq) . ");'";
            if ($display_style == 'block') {
                echo " checked";
            }

            echo " /><b>" . text(xl_layout_label($group_name)) . "</b></span>\n";
            echo "<div id='div_" . attr($group_seq) . "' class='section' style='display:$display_style;'>\n";
            echo " <table border='0' cellpadding='0' width='100%'>\n";
            $display_style = 'none';
        }

        // Handle starting of a new row.
        if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
            end_row();
            echo " <tr>";
        }

        if ($item_count == 0 && $titlecols == 0) {
            $titlecols = 1;
        }

        // Handle starting of a new label cell.
        if ($titlecols > 0) {
            end_cell();
            echo "<td valign='top' colspan='" . attr($titlecols) . "' width='1%' nowrap";
            echo ($frow['uor'] == 2) ? " class='required'" : " class='bold'";
            if ($cell_count == 2) {
                echo " style='padding-left:10pt'";
            }

            echo ">";
            $cell_count += $titlecols;
        }

        ++$item_count;

        echo "<b>";
        if ($frow['title']) {
            echo (text(xl_layout_label($frow['title'])) . ":");
        } else {
            echo "&nbsp;";
        }

        echo "</b>";

        // Handle starting of a new data cell.
        if ($datacols > 0) {
            end_cell();
            echo "<td valign='top' colspan='" . attr($datacols) . "' class='text'";
            if ($cell_count > 0) {
                echo " style='padding-left:5pt'";
            }

            echo ">";
            $cell_count += $datacols;
        }

        ++$item_count;

        if ($frow['edit_options'] == 'H') {
            echo generate_display_field($frow, $currvalue);
        } else {
            generate_form_field($frow, $currvalue);
        }
    }

    end_group();
    echo "</div>\n";
}

function issue_ippf_con_newtype()
{
    echo "  var condisp = (aitypes[index] == 4) ? '' : 'none';\n";
    echo "  document.getElementById('ippf_con').style.display = condisp;\n";
}

function issue_ippf_con_save($issue)
{
    $sets = "id = '" . add_escape_custom($issue) . "'";
    $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = 'CON' AND uor > 0 AND field_id != '' AND edit_options != 'H' " .
    "ORDER BY group_id, seq");
    while ($frow = sqlFetchArray($fres)) {
        $field_id  = $frow['field_id'];
        $value = get_layout_form_value($frow);
        $sets .= ", " . add_escape_custom($field_id) . " = '" . add_escape_custom($value) . "'";
    }

  // This replaces the row if its id exists, otherwise inserts it.
    sqlStatement("REPLACE INTO lists_ippf_con SET $sets");
}

function issue_ippf_con_form($issue, $thispid)
{
    global $pprow, $item_count, $cell_count, $last_group;

    $shrow = getHistoryData($thispid);

    if ($issue) {
        $pprow = sqlQuery("SELECT * FROM lists_ippf_con WHERE id = ?", array($issue));
    } else {
        $pprow = array();
    }

    echo "<div id='ippf_con' style='display:none'>\n";

    // Load array of properties for this layout and its groups.
    $grparr = array();
    getLayoutProperties('CON', $grparr);

    $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = 'CON' AND uor > 0 " .
    "ORDER BY group_id, seq");
    $last_group = '';
    $cell_count = 0;
    $item_count = 0;
    $display_style = 'block';

    while ($frow = sqlFetchArray($fres)) {
        $this_group = $frow['group_id'];
        $titlecols  = $frow['titlecols'];
        $datacols   = $frow['datacols'];
        $data_type  = $frow['data_type'];
        $field_id   = $frow['field_id'];
        $list_id    = $frow['list_id'];

        $currvalue  = '';

        if ($frow['edit_options'] == 'H') {
            // This data comes from static history
            if (isset($shrow[$field_id])) {
                $currvalue = $shrow[$field_id];
            }
        } else {
            if (isset($pprow[$field_id])) {
                $currvalue = $pprow[$field_id];
            }
        }

        // Handle a data category (group) change.
        if (strcmp($this_group, $last_group) != 0) {
            end_group();
            $group_seq  = 'con' . substr($this_group, 0, 1);
            $group_name = $grparr[$this_group]['grp_title'];
            $last_group = $this_group;
            echo "<br /><span class='bold'><input type='checkbox' name='form_cb_" . attr($group_seq) . "' value='1' " .
            "onclick='return divclick(this," . attr_js('div_' . $group_seq) . ");'";
            if ($display_style == 'block') {
                echo " checked";
            }

            echo " /><b>" . text($group_name) . "</b></span>\n";
            echo "<div id='div_" . attr($group_seq) . "' class='section' style='display:$display_style;'>\n";
            echo " <table border='0' cellpadding='0' width='100%'>\n";
            $display_style = 'none';
        }

        // Handle starting of a new row.
        if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
            end_row();
            echo " <tr>";
        }

        if ($item_count == 0 && $titlecols == 0) {
            $titlecols = 1;
        }

        // Handle starting of a new label cell.
        if ($titlecols > 0) {
            end_cell();
            echo "<td valign='top' colspan='" . attr($titlecols) . "' width='1%' nowrap";
            echo ($frow['uor'] == 2) ? " class='required'" : " class='bold'";
            if ($cell_count == 2) {
                echo " style='padding-left:10pt'";
            }

            echo ">";
            $cell_count += $titlecols;
        }

        ++$item_count;

        echo "<b>";
        if ($frow['title']) {
            echo text($frow['title']) . ":";
        } else {
            echo "&nbsp;";
        }

        echo "</b>";

        // Handle starting of a new data cell.
        if ($datacols > 0) {
            end_cell();
            echo "<td valign='top' colspan='" . attr($datacols) . "' class='text'";
            if ($cell_count > 0) {
                echo " style='padding-left:5pt'";
            }

            echo ">";
            $cell_count += $datacols;
        }

        ++$item_count;

        if ($frow['edit_options'] == 'H') {
            echo generate_display_field($frow, $currvalue);
        } else {
            generate_form_field($frow, $currvalue);
        }
    }

    end_group();
    echo "</div>\n";
}
