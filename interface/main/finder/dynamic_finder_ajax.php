<?php
// Copyright (C) 2012 Rod Roark <rod@sunsetsystems.com>
// Sponsored by David Eschelbacher, MD
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once($GLOBALS['srcdir']."/options.inc.php");

$popup = empty($_REQUEST['popup']) ? 0 : 1;

// With the ColReorder or ColReorderWithResize plug-in, the expected column
// ordering may have been changed by the user.  So we cannot depend on
// list_options to provide that.
//
$aColumns = explode(',', $_GET['sColumns']);

// Paging parameters.  -1 means not applicable.
//
$iDisplayStart  = isset($_GET['iDisplayStart' ]) ? 0 + $_GET['iDisplayStart' ] : -1;
$iDisplayLength = isset($_GET['iDisplayLength']) ? 0 + $_GET['iDisplayLength'] : -1;
$limit = '';
if ($iDisplayStart >= 0 && $iDisplayLength >= 0) {
    $limit = "LIMIT " . escape_limit($iDisplayStart) . ", " . escape_limit($iDisplayLength);
}
// Search parameter.  -1 means .
//
$searchMethodInPatientList = isset($_GET['searchType' ]) && $_GET['searchType' ]==="true" ?  true : false;

// Column sorting parameters.
//
$orderby = '';
if (isset($_GET['iSortCol_0'])) {
    for ($i = 0; $i < intval($_GET['iSortingCols']); ++$i) {
        $iSortCol = intval($_GET["iSortCol_$i"]);
        if ($_GET["bSortable_$iSortCol"] == "true") {
            $sSortDir = escape_sort_order($_GET["sSortDir_$i"]); // ASC or DESC
            // We are to sort on column # $iSortCol in direction $sSortDir.
            $orderby .= $orderby ? ', ' : 'ORDER BY ';
            //
            if ($aColumns[$iSortCol] == 'name') {
                $orderby .= "lname $sSortDir, fname $sSortDir, mname $sSortDir";
            } else {
                $orderby .= "`" . escape_sql_column_name($aColumns[$iSortCol], array('patient_data')) . "` $sSortDir";
            }
        }
    }
}

// Global filtering.
//
$where = '';
if (isset($_GET['sSearch']) && $_GET['sSearch'] !== "") {
    $sSearch = add_escape_custom(trim($_GET['sSearch']));
    foreach ($aColumns as $colname) {
        $where .= $where ? "OR " : "WHERE ( ";
        if ($colname == 'name') {
            if ($searchMethodInPatientList) { // exact search
                $where .=
                    "lname LIKE '$sSearch' OR " .
                    "fname LIKE '$sSearch' OR " .
                    "mname LIKE '$sSearch' ";
            } else {
                $where .= // like search
                    "lname LIKE '$sSearch%' OR " .
                    "fname LIKE '$sSearch%' OR " .
                    "mname LIKE '$sSearch%' ";
            }
        } elseif ($searchMethodInPatientList) {
            $where .= "`" . escape_sql_column_name($colname, array('patient_data')) . "` LIKE '$sSearch' ";
        } else { // exact search
            $where .= "`" . escape_sql_column_name($colname, array('patient_data')) . "` LIKE '$sSearch%' ";
        }
    }

    if ($where) {
        $where .= ")";
    }
}

// Column-specific filtering.
//
for ($i = 0; $i < count($aColumns); ++$i) {
    $colname = $aColumns[$i];
    if (isset($_GET["bSearchable_$i"]) && $_GET["bSearchable_$i"] == "true" && $_GET["sSearch_$i"] != '') {
        $where .= $where ? ' AND' : 'WHERE';
        $sSearch = add_escape_custom($_GET["sSearch_$i"]);
        if ($colname == 'name') {
            if ($searchMethodInPatientList) { // like search
                $where .= " ( " .
                    "lname LIKE '$sSearch%' OR " .
                    "fname LIKE '$sSearch%' OR " .
                    "mname LIKE '$sSearch%' )";
            } else {  // exact search
                $where .= " ( " .
                    "lname LIKE '$sSearch' OR " .
                    "fname LIKE '$sSearch' OR " .
                    "mname LIKE '$sSearch' )";
            }
        } elseif ($searchMethodInPatientList) {
            $where .= " `" . escape_sql_column_name($colname, array('patient_data')) . "` LIKE '$sSearch%'"; // like search
        } else {
            $where .= " `" . escape_sql_column_name($colname, array('patient_data')) . "` LIKE '$sSearch'"; // exact search
        }
    }
}

// Compute list of column names for SELECT clause.
// Always includes pid because we need it for row identification.
//
$sellist = 'pid';
foreach ($aColumns as $colname) {
    if ($colname == 'pid') {
        continue;
    }

    $sellist .= ", ";
    if ($colname == 'name') {
        $sellist .= "lname, fname, mname";
    } else {
        $sellist .= "`" . escape_sql_column_name($colname, array('patient_data')) . "`";
    }
}

// Get total number of rows in the table.
//
$row = sqlQuery("SELECT COUNT(id) AS count FROM patient_data");
$iTotal = $row['count'];

// Get total number of rows in the table after filtering.
//
$row = sqlQuery("SELECT COUNT(id) AS count FROM patient_data $where");
$iFilteredTotal = $row['count'];

// Build the output data array.
//
$out = array(
    "sEcho"                => intval($_GET['sEcho']),
    "iTotalRecords"        => $iTotal,
    "iTotalDisplayRecords" => $iFilteredTotal,
    "aaData"               => array()
);

// save into variable data about fields of 'patient_data' from 'layout_options'
$fieldsInfo = array();
$quoteSellist = preg_replace('/(\w+)/i', '"${1}"', str_replace('`', '', $sellist));
$res = sqlStatement('SELECT data_type, field_id, list_id FROM layout_options WHERE form_id = "DEM" AND field_id IN(' . $quoteSellist . ')');
while ($row = sqlFetchArray($res)) {
    $fieldsInfo[$row['field_id']] = $row;
}

$query = "SELECT $sellist FROM patient_data $where $orderby $limit";
$res = sqlStatement($query);
while ($row = sqlFetchArray($res)) {
    // Each <tr> will have an ID identifying the patient.
    $arow = array('DT_RowId' => 'pid_' . $row['pid']);
    foreach ($aColumns as $colname) {
        if ($colname == 'name') {
            $name = $row['lname'];
            if ($name && $row['fname']) {
                $name .= ', ';
            }

            if ($row['fname']) {
                $name .= $row['fname'];
            }

            if ($row['mname']) {
                $name .= ' ' . $row['mname'];
            }

            $arow[] = attr($name);
        } else {
            $arow[] = isset($fieldsInfo[$colname]) ? attr(generate_plaintext_field($fieldsInfo[$colname], $row[$colname])) : attr($row[$colname]);
        }
    }

    $out['aaData'][] = $arow;
}

// error_log($query); // debugging

// Dump the output array as JSON.
//
// Encoding with options for escaping a special chars - JSON_HEX_TAG (<)(>), JSON_HEX_AMP(&), JSON_HEX_APOS('), JSON_HEX_QUOT(").
echo json_encode($out, 15);
