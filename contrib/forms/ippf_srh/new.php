<?php

// Copyright (C) 2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc.php");

use OpenEMR\Core\Header;

$CPR = 4; // cells per row

$pprow = array();

if (! $encounter) { // comes from globals.php
    die("Internal error: we do not seem to be in an encounter!");
}

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

$formid = $_GET['id'];

// If Save was clicked, save the info.
//
if ($_POST['bn_save']) {
    $sets = "";
    $sqlBindArray = array();
    $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = 'SRH' AND uor > 0 AND field_id != '' AND " .
    "edit_options != 'H' " .
    "ORDER BY group_name, seq");
    while ($frow = sqlFetchArray($fres)) {
        $field_id  = $frow['field_id'];
        $value = get_layout_form_value($frow);
        if ($sets) {
            $sets .= ", ";
        }

        $sets .= escape_sql_column_name($field_id, array('form_ippf_srh')) . " = ?";
        array_push($sqlBindArray, $value);
    }

    if ($formid) {
        // Updating an existing form.
        $query = "UPDATE form_ippf_srh SET " . $sets . " WHERE id = ?";
        array_push($sqlBindArray, $formid);
        sqlStatement($query, $sqlBindArray);
    } else {
        // Adding a new form.
        $query = "INSERT INTO form_ippf_srh SET " . $sets;
        $newid = sqlInsert($query, $sqlBindArray);
        addForm($encounter, "IPPF SRH Data", $newid, "ippf_srh", $pid, $userauthorized);
    }

    formHeader("Redirecting....");
    formJump();
    formFooter();
    exit;
}

$enrow = sqlQuery("SELECT p.fname, p.mname, p.lname, fe.date FROM " .
  "form_encounter AS fe, forms AS f, patient_data AS p WHERE " .
  "p.pid = ? AND f.pid = ? AND f.encounter = ? AND " .
  "f.formdir = 'newpatient' AND f.deleted = 0 AND " .
  "fe.id = f.form_id LIMIT 1", array($pid, $pid, $encounter));

if ($formid) {
    $pprow = sqlQuery("SELECT * FROM form_ippf_srh WHERE " .
    "id = ? AND activity = '1'", array($formid));
}
?>
<html>
<head>
    <?php Header::setupHeader(); ?>
<style>

td, input, select, textarea {
 font-family: Arial, Helvetica, sans-serif;
 font-size: 10pt;
}

div.section {
 border: solid;
 border-width: 1px;
 border-color: #0000ff;
 margin: 0 0 0 10pt;
 padding: 5pt;
}

</style>

<script>

// Supports customizable forms (currently just for IPPF).
function divclick(cb, divid) {
 var divstyle = document.getElementById(divid).style;
 if (cb.checked) {
  divstyle.display = 'block';
 } else {
  divstyle.display = 'none';
 }
 return true;
}

</script>
</head>

<body <?php echo $top_bg_line; ?> topmargin="0" rightmargin="0" leftmargin="2" bottommargin="0" marginwidth="2" marginheight="0">
<form method="post" action="<?php echo $rootdir ?>/forms/ippf_srh/new.php?id=<?php echo attr_url($formid); ?>"
 onsubmit="return top.restoreSession()">

<p class='title' style='margin-top:8px;margin-bottom:8px;text-align:center'>
<?php
  echo xlt('IPPF SRH Data for') . ' ';
  echo text($enrow['fname']) . ' ' . text($enrow['mname']) . ' ' . text($enrow['lname']);
  echo ' ' . xlt('on') . ' ' . text(substr($enrow['date'], 0, 10));
?>
</p>

<?php
$shrow = getHistoryData($pid);

// echo "<div id='ippf_srh' style='display:none'>\n";

$fres = sqlStatement("SELECT * FROM layout_options " .
"WHERE form_id = 'SRH' AND uor > 0 " .
"ORDER BY group_name, seq");
$last_group = '';
$cell_count = 0;
$item_count = 0;
$display_style = 'block';

while ($frow = sqlFetchArray($fres)) {
    $this_group = $frow['group_name'];
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
            $group_seq  = 'srh' . substr($this_group, 0, 1);
            $group_name = substr($this_group, 1);
            $last_group = $this_group;
            echo "<br /><span class='bold'><input type='checkbox' name='form_cb_" . attr($group_seq) . "' value='1' " .
        "onclick='return divclick(this," . attr_js("div_" . $group_seq) . ");'";
        if ($display_style == 'block') {
            echo " checked";
        }

            echo " /><b>" . text($group_name) . "</b></span>\n";
            echo "<div id='div_" . attr($group_seq) . "' class='section' style='display:" . attr($display_style) . ";'>\n";
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
// echo "</div>\n";
?>

<p style='text-align:center'>
<input type='submit' name='bn_save' value='Save' />
&nbsp;
<input type='button' value='Cancel' onclick="parent.closeTab(window.name, false)" />
&nbsp;
</p>

</form>
<?php

// TBD: If $alertmsg, display it with a JavaScript alert().

?>
</body>
</html>
