<?php
// Copyright (C) 2009-2016 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This will print a blank form, and if "patientid" is specified then
// any existing data for the specified patient is included.
//
// Reduced and simplified by Sam Johnson (2011-03-19) for version that meets MSI needs.

// Modified by Rod to be invoked from the custom/ directory by the normal
// demographics_print.php script when MSI Rapid Workflow is activated.

//The code below is copied from the original report, and fetches each data item in the demographic layout (if not 'unused')

// require_once("../interface/globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/formatting.inc.php");

if (empty($encounter)) die("Please select or create a visit!");

$PDF_OUTPUT   = true;

$date_init = "";

//THIS IS A VERSION OF GENERATE_PRINT_FIELD() THAT HAS BEEN REVERSED TO SHOW DROP-DOWN LISTS AS SINGLE FIELDS.
//USED ONLY BY MSI WHEN PRODUCING ITS VERSION OF THE PRINT_DEMOGRAPHICS REPORT.
//
function generate_print_field_msi($frow, $currvalue) {
    global $rootdir, $date_init, $PDF_OUTPUT;

    $s = "";
    $currescaped = htmlspecialchars($currvalue, ENT_QUOTES);

    $data_type   = $frow['data_type'];
    $field_id    = $frow['field_id'];
    $list_id     = $frow['list_id'];
    $fld_length  = $frow['fld_length'];

    $description = htmlspecialchars(xl_layout_label($frow['description']), ENT_QUOTES);

    // Can pass $frow['empty_title'] with this variable, otherwise
    //  will default to 'Unassigned'.
    // If it is 'SKIP' then an empty text title is completely skipped.
    $showEmpty = true;
    if (isset($frow['empty_title'])) {
        if ($frow['empty_title'] == "SKIP") {
            //do not display an 'empty' choice
            $showEmpty = false;
            $empty_title = "Unassigned";
        }
        else {     
            $empty_title = $frow['empty_title'];
        }
    }

    // generic single-selection list
    //
    if ($data_type == 1 || $data_type == 26) {
        if (empty($fld_length)) {
            if ($list_id == 'titles') {
                $fld_length = 3;
            } else {
                $fld_length = 10;
            }
        }
        $tmp = '';
        if ($currvalue) {
            $lrow = sqlQuery("SELECT title FROM list_options " .
                "WHERE list_id = '$list_id' AND option_id = '$currvalue' AND activity = 1");
            $tmp = xl_list_label($lrow['title']);
            if (empty($tmp)) $tmp = "($currvalue)";
        }
        if ($tmp === '') $tmp = '&nbsp;';
        $s .= $tmp;
    }

    // simple text field
    if ($data_type == 2 || $data_type == 15) {
        if ($currescaped === '') $currescaped = '&nbsp;';
        $s .= $currescaped;
    }

    // long or multi-line text field
    else if ($data_type == 3) {
        $s .= "<textarea" .
            " cols='$fld_length'" .
            " rows='" . $frow['max_length'] . "'>" .
            $currescaped . "</textarea>";
    }

    // date
    else if ($data_type == 4) {
        if ($currescaped === '') $currescaped = '&nbsp;';
        $s .= oeFormatShortDate($currescaped);
    }

    // provider list
    else if ($data_type == 10 || $data_type == 11) {
        $tmp = '';
        if ($currvalue) {
            $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
                "WHERE id = '$currvalue'");
            $tmp = ucwords($urow['fname'] . " " . $urow['lname']);
            if (empty($tmp)) $tmp = "($currvalue)";
        }
        if ($tmp === '') $tmp = '&nbsp;';
        $s .= $tmp;
    }

    // pharmacy list
    else if ($data_type == 12) {
        $tmp = '';
        if ($currvalue) {
            $pres = get_pharmacies();
            while ($prow = sqlFetchArray($pres)) {
                $key = $prow['id'];
                if ($currvalue == $key) {
                    $tmp = $prow['name'] . ' ' . $prow['area_code'] . '-' .
                        $prow['prefix'] . '-' . $prow['number'] . ' / ' .
                        $prow['line1'] . ' / ' . $prow['city'];
                }
            }
            if (empty($tmp)) $tmp = "($currvalue)";
        }
        if ($tmp === '') $tmp = '&nbsp;';
        $s .= $tmp;
    }

    // squads
    else if ($data_type == 13) {
        $tmp = '';
        if ($currvalue) {
            $squads = acl_get_squads();
            if ($squads) {
                foreach ($squads as $key => $value) {
                    if ($currvalue == $key) {
                        $tmp = $value[3];
                    }
                }
            }
            if (empty($tmp)) $tmp = "($currvalue)";
        }
        if ($tmp === '') $tmp = '&nbsp;';
        $s .= $tmp;
    }

    // Address book.
    else if ($data_type == 14) {
        $tmp = '';
        if ($currvalue) {
            $urow = sqlQuery("SELECT fname, lname, specialty FROM users " .
                "WHERE id = '$currvalue'");
            $uname = $urow['lname'];
            if ($urow['fname']) $uname .= ", " . $urow['fname'];
            $tmp = $uname;
            if (empty($tmp)) $tmp = "($currvalue)";
        }
        if ($tmp === '') $tmp = '&nbsp;';
        $s .= $tmp;
    }

    // a set of labeled checkboxes
    else if ($data_type == 21) {
        // In this special case, fld_length is the number of columns generated.
        $cols = max(1, $fld_length);
        $avalue = explode('|', $currvalue);
        $lres = sqlStatement("SELECT * FROM list_options " .
            "WHERE list_id = '$list_id' AND activity = 1 ORDER BY seq, title");
        $s .= "<table cellpadding='0' cellspacing='0' width='100%'>";
        $tdpct = (int) (100 / $cols);
        for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
            $option_id = $lrow['option_id'];
            if ($count % $cols == 0) {
                if ($count) $s .= "</tr>";
                $s .= "<tr>";
            }
            $s .= "<td width='$tdpct%'>";
            $s .= "<input type='checkbox'";
            if (in_array($option_id, $avalue)) $s .= " checked";
            $s .= ">" . xl_list_label($lrow['title']);
            $s .= "</td>";
        }
        if ($count) {
            $s .= "</tr>";
            if ($count > $cols) {
                // Add some space after multiple rows of checkboxes.
                $s .= "<tr><td colspan='$cols' style='height:0.7em'></td></tr>";
            }
        }
        $s .= "</table>";
    }

    // a set of labeled text input fields
    else if ($data_type == 22) {
        $tmp = explode('|', $currvalue);
        $avalue = array();
        foreach ($tmp as $value) {
            if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                $avalue[$matches[1]] = $matches[2];
            }
        }
        $lres = sqlStatement("SELECT * FROM list_options " .
            "WHERE list_id = '$list_id' AND activity = 1 ORDER BY seq, title");
        $s .= "<table cellpadding='0' cellspacing='0'>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $maxlength = empty($frow['max_length']) ? 255 : $frow['max_length'];
            $fldlength = empty($fld_length) ?  20 : $fld_length;
            $s .= "<tr><td>" . xl_list_label($lrow['title']) . "&nbsp;</td>";
                $s .= "<td><input type='text'" .
                " size='$fldlength'" .
                " value='" . $avalue[$option_id] . "'" .
                " class='under'" .
                " /></td></tr>";
        }
        $s .= "</table>";
    }

    // a set of exam results; 3 radio buttons and a text field:
    else if ($data_type == 23) {
        $tmp = explode('|', $currvalue);
        $avalue = array();
        foreach ($tmp as $value) {
            if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                $avalue[$matches[1]] = $matches[2];
            }
        }
        $maxlength = empty($frow['max_length']) ? 255 : $frow['max_length'];
        $fldlength = empty($fld_length) ?  20 : $fld_length;
        $lres = sqlStatement("SELECT * FROM list_options " .
            "WHERE list_id = '$list_id' AND activity = 1 ORDER BY seq, title");
        $s .= "<table cellpadding='0' cellspacing='0'>";
        $s .= "<tr><td>&nbsp;</td><td class='bold'>" . xl('N/A') .
            "&nbsp;</td><td class='bold'>" . xl('Nor') . "&nbsp;</td>" .
            "<td class='bold'>" . xl('Abn') . "&nbsp;</td><td class='bold'>" .
            xl('Date/Notes') . "</td></tr>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $restype = substr($avalue[$option_id], 0, 1);
            $resnote = substr($avalue[$option_id], 2);
            $s .= "<tr><td>" . xl_list_label($lrow['title']) . "&nbsp;</td>";
            for ($i = 0; $i < 3; ++$i) {
                $s .= "<td><input type='radio'";
                if ($restype === "$i") $s .= " checked";
                $s .= " /></td>";
            }
            $s .= "<td><input type='text'" .
                " size='$fldlength'" .
                " value='$resnote'" .
                " class='under' /></td>" .
                "</tr>";
          }
        $s .= "</table>";
    }

    // the list of active allergies for the current patient
    // this is read-only!
    else if ($data_type == 24) {
        $query = "SELECT title, comments FROM lists WHERE " .
            "pid = '" . $GLOBALS['pid'] . "' AND type = 'allergy' AND enddate IS NULL " .
            "ORDER BY begdate";
        $lres = sqlStatement($query);
        $count = 0;
        while ($lrow = sqlFetchArray($lres)) {
            if ($count++) $s .= "<br />";
            $s .= $lrow['title'];
            if ($lrow['comments']) $s .= ' (' . $lrow['comments'] . ')';
        }
    }

    // a set of labeled checkboxes, each with a text field:
    else if ($data_type == 25) {
        $tmp = explode('|', $currvalue);
        $avalue = array();
        foreach ($tmp as $value) {
            if (preg_match('/^([^:]+):(.*)$/', $value, $matches)) {
                $avalue[$matches[1]] = $matches[2];
            }
        }
        $maxlength = empty($frow['max_length']) ? 255 : $frow['max_length'];
        $fldlength = empty($fld_length) ?  20 : $fld_length;
        $lres = sqlStatement("SELECT * FROM list_options " .
            "WHERE list_id = '$list_id' AND activity = 1 ORDER BY seq, title");
        $s .= "<table cellpadding='0' cellspacing='0'>";
        while ($lrow = sqlFetchArray($lres)) {
            $option_id = $lrow['option_id'];
            $restype = substr($avalue[$option_id], 0, 1);
            $resnote = substr($avalue[$option_id], 2);
            $s .= "<tr><td>" . xl_list_label($lrow['title']) . "&nbsp;</td>";
            $s .= "<td><input type='checkbox'";
            if ($restype) $s .= " checked";
            $s .= " />&nbsp;</td>";
            $s .= "<td><input type='text'" .
                " size='$fldlength'" .
                " value='$resnote'" .
                " class='under'" .
                " /></td>" .
                "</tr>";
        }
        $s .= "</table>";
    }

    // a set of labeled radio buttons or a generic single-selection list
    else if ($data_type == 27) {
        // In this special case, fld_length is the number of columns generated.
        $cols = max(1, $frow['fld_length']);
        $lres = sqlStatement("SELECT * FROM list_options " .
          "WHERE list_id = '$list_id' AND activity = 1 ORDER BY seq, title");
        $s .= "<table cellpadding='0' cellspacing='0' width='100%'>";
        $tdpct = (int) (100 / $cols);
        for ($count = 0; $lrow = sqlFetchArray($lres); ++$count) {
            $option_id = $lrow['option_id'];
            if ($count % $cols == 0) {
                if ($count) $s .= "</tr>";
                $s .= "<tr>";
            }
            $s .= "<td width='$tdpct%'>";
            $s .= "<input type='radio'";
            // if ((strlen($currvalue) == 0 && $lrow['is_default']) ||
            //     (strlen($currvalue)  > 0 && $option_id == $currvalue)) {
            if (strlen($currvalue)  > 0 && $option_id == $currvalue) {
                // Do not use defaults for these printable forms.
                $s .= " checked";
            }
            $s .= ">" . xl_list_label($lrow['title']);
            $s .= "</td>";
        }
        if ($count) {
            $s .= "</tr>";
            if ($count > $cols) {
                // Add some space after multiple rows of radio buttons.
                $s .= "<tr><td colspan='$cols' style='height:0.7em'></td></tr>";
            }
        }
        $s .= "</table>";
    }

    // static text.  read-only, of course.
    else if ($data_type == 31) {
        $s .= nl2br($frow['description']);
    }

    return $s;
}

// Display the next field title and value.
// This works for either PDF or HTML output.
//
function displayMSIField($isNewColumn, $title, $data) {
    global $counter, $tablerow, $table, $PDF_OUTPUT;

    if ($isNewColumn) {
        if (++$counter > 1) {
            if (!$PDF_OUTPUT) {
                echo "</td></tr>\n";
            }
            // a row is filled up so start the next one
            $counter = 0;
            ++$tablerow;
        }
        if (!$PDF_OUTPUT) {
            if ($counter == 0) {
                echo "<tr><td width='200'>";
            }
            else {
                echo "</td><td width='300'>";
            }
        }
    }
    else {
        if ($counter < 0) $counter = 0; // this should not happen
    }

    // Populate the label and data within the cell.
    if ($PDF_OUTPUT) {
        if (!isset($table[$tablerow])) {
            $table[$tablerow] = array('', '');
        }
        if (!empty($table[$tablerow][$counter])) {
            // continuing data in cell (e.g. last name), so insert a space
            $table[$tablerow][$counter] .= ' ';
        }
        if ($title) {
            $table[$tablerow][$counter] .= $title . ": ";
        }
        $table[$tablerow][$counter] .= $data;
    }
    else {
        echo "<b>";
        if ($title) echo ($title . ": "); else echo "&nbsp;";
        echo "</b>";
        echo $data;
    }
}

$patientid = empty($_REQUEST['patientid']) ? 0 : 0 + $_REQUEST['patientid'];
if ($patientid < 0) $patientid = 0 + $pid; // -1 means current pid

$prow = array();
$erow = array();
$irow = array();

if ($patientid) {
    $prow = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD"); 
    $erow = getEmployerData($pid);
    // Check authorization.
    $thisauth = acl_check('patients', 'demo');
    if (!$thisauth)
        die(xl('Demographics not authorized'));
    if ($prow['squad'] && ! acl_check('squads', $prow['squad']))
        die(xl('You are not authorized to access this squad'));
}

$fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = 'DEM' AND uor > 0 " .
    "ORDER BY seq");

if ($PDF_OUTPUT) {
    // documentation for ezpdf is here --> http://www.ros.co.nz/pdf/
    require_once ("$srcdir/classes/class.ezpdf.php");
    $pdf =& new Cezpdf("letter", "landscape");
    $pdf->ezSetMargins(20,20,20,20); // top, bottom, left, right
    $pdf->selectFont("$srcdir/fonts/Helvetica.afm");
}
else {
?>

<html>
<head>
<?php html_header_show();?>

<style>
body, td {
 font-family: Arial, Helvetica, sans-serif;
 font-weight: normal;
 font-size: 11pt;
}

body {
 padding: 5pt 5pt 5pt 5pt;
}

div.section {
 border-style: solid;
 border-width: 1px;
 border-color: #000000;
 margin: 0 0 0 10pt;
 padding: 5pt;
}

.mainhead {
 font-weight: bold;
 font-size: 14pt;
 text-align: center;
}

.under {
 border-style: solid;
 border-width: 0 0 1px 0;
 border-color: #999999;
}

.ftitletable {
 width: 100%;
 margin: 0 0 8pt 0;
}
.ftitlecell1 {
 vertical-align: top;
 text-align: left;
 font-size: 14pt;
 font-weight: bold;
}
.ftitlecell2 {
 vertical-align: top;
 text-align: right;
 font-size: 9pt;
}
</style>
</head>
<body>
<form>

<?php
//  IF INSERTING DOCUMENT AROUND 'LABEL', PUT TEXT ABOVE LABEL HERE:
?>

<?php 
// (Not sure what this does differently to a normal HTML heading!)
// echo genFacilityTitle(xl('Clinic Family Planning/ Reproductive Health Client Card'), -1); ?>

<?php
// This pulls data from the array that contains each item of demographics, and then populates
// the current value of each demographic item (if a current client has been selected)

// Set out into a two-column table for ease of presentation, with a counter to identify column number
  echo "<table width=500>";
  // echo "<tr><td width=300>";

} // end not PDF output

$erow = false;
$encdate = date('Y-m-d');
if ($encounter && $patientid == $pid) {
    $erow = sqlQuery("SELECT fe.date, DATE_FORMAT(fe.date,'%d-%M-%Y') AS date_fmtd, c.pc_catname, lrs.title " .
        "FROM form_encounter AS fe " .
        "LEFT JOIN openemr_postcalendar_categories AS c ON c.pc_catid = fe.pc_catid " .
        "LEFT JOIN list_options AS lrs ON lrs.list_id = 'refsource' AND lrs.option_id = fe.referral_source AND lrs.activity = 1 " .
        "WHERE fe.pid = '$pid' AND fe.encounter = '$encounter'");
    if (!empty($erow['date']))$encdate = $erow['date'];
}

$counter = -1;
$tablerow = 0;
$table = array();

//  Start of while statement that loops through array
while ($frow = sqlFetchArray($fres)) {
    $this_group = $frow['group_id'];
    $titlecols  = $frow['titlecols'];
    $datacols   = $frow['datacols'];
    $data_type  = $frow['data_type'];
    $field_id   = $frow['field_id'];
    $list_id    = $frow['list_id'];
    $currvalue  = '';

    // this sets the current value from either the patient or employer 
    if (strpos($field_id, 'em_') === 0) {
        $tmp = substr($field_id, 3);
        if (isset($erow[$tmp])) $currvalue = $erow[$tmp];
    }
    else {
        if (isset($prow[$field_id])) $currvalue = $prow[$field_id];
    }

    // This was originally == 'DOB' which didn't work. JT
    if ($frow['title'] == 'Age') {
        // Translate DOB to age.
        displayMSIField($titlecols > 0, xl('Age'),
            getPatientAge(str_replace('-','',$currvalue), str_replace('-','',$encdate)));
    }
    else {
        // Separate cell unless TitleCols field in demographics layout indicates otherwise.
        displayMSIField($titlecols > 0, xl_layout_label($frow['title']),
            ($currvalue === '' ? '' : generate_print_field_msi($frow, $currvalue)));
    }

    // End of 'while' statement
}

// Add visit category and referral source.
// Added Visit data and formated.
if ($erow) {
    displayMSIField(true, xl('Visit Date'), $erow['date_fmtd']);
    displayMSIField(true, xl('Visit Cat' ), $erow['pc_catname']);
}

if ($PDF_OUTPUT) {
    $pdf->ezTable($table, '', '', array(
        'showHeadings' => 0,       // no column headings
        'fontSize'     => 9,      // font size in points
        'xPos'         => 'left',  // location of positioning bar
        'xOrientation' => 'right', // position to right of xPos
        'maxWidth'     => 380,     // max width of table in points
        'width'     => 380,     // width of table in points
        'cols' =>  array ( array('width' => 120), array('width' => 280))
    ));
    // For "inline" option see stream() function in library/classes/class.pdf.php.
    $pdf->ezStream(array('inline' => true));
    exit;
}

echo "</td></tr></table>";
?>
<?php
//  IF INSERTING DOCUMENT AROUND 'LABEL', PUT TEXT BELOW LABEL HERE:
?>

</form>

<!-- This should really be in the onload handler but that seems to be unreliable and can crash Firefox 3. -->
<script language='JavaScript'>
window.print();
</script>

</body>
</html>
