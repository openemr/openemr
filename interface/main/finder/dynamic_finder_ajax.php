<?php

/**
 * dynamic_finder_ajax.php
 *
 * Sponsored by David Eschelbacher, MD
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2012 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../../globals.php");
require_once($GLOBALS['srcdir'] . "/options.inc.php");

use OpenEMR\Events\BoundFilter;
use OpenEMR\Events\PatientFinder\PatientFinderFilterEvent;
use OpenEMR\Events\PatientFinder\ColumnFilter;

// Not checking csrf since it breaks when opening up a patient in a new frame.
//  Also note that csrf checking is not needed in this script because of following 2 reasons.
//  1. cookie_samesite in OpenEMR is set to 'Strict', which is an effective security measure to stop csrf vulnerabilities.
//  2. Additionally, in this script there are no state changes, thus it is not even sensitive to csrf vulnerabilities.

$popup = empty($_REQUEST['popup']) ? 0 : 1;
$searchAny = !empty($_GET['search_any']) && empty($_GET['sSearch']) ? $_GET['search_any'] : "";

// With the ColReorder or ColReorderWithResize plug-in, the expected column
// ordering may have been changed by the user.  So we cannot depend on
// list_options to provide that.
// Addition of an any column search from dem layouts. sjp 05/04/2019
// Probably could have used a session var here because datatable server url
// presists not allowing easy way to unset any for normal search but opted not.
//
if ($searchAny) {
    $_GET['sSearch'] = $searchAny;
    $layoutCols = sqlStatement(
        "SELECT field_id FROM layout_options WHERE form_id = 'DEM'
            AND field_id not like ? AND field_id not like ? AND uor !=0",
        array('em\_%', 'add%')
    );
    for ($iter = 0; $row = sqlFetchArray($layoutCols); $iter++) {
        $aColumns[] = $row['field_id'];
    }
} else {
    $aColumns = explode(',', $_GET['sColumns']);
}
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
$searchMethodInPatientList = isset($_GET['searchType' ]) && $_GET['searchType' ] === "true" ?  true : false;

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

// Helper function for filtering dates. Returns a string for use with MySQL LIKE.
// Examples (assuming US date formats):
//   12       => Any date with "12" in it
//   1977     => Any date with "1977" in it (therefore year 1977)
//   197/12/1 => Dec. 1 of any year in the 1970's
//   12/1/197 => Same
//   12/1     => Dec. 1 of any year
//   /1       => The first day of any month of any year
// Any non-digit character may be used instead of "/".
//
function dateSearch($sSearch)
{
    // Determine if MDY date format is used, preferring Date Display Format from
    // global settings if it's not YMD, otherwise guessing from country code.
    $mdy = empty($GLOBALS['date_display_format']) ?
        ($GLOBALS['phone_country_code'] == 1) : ($GLOBALS['date_display_format'] == 1);
    // If no delimiters then just search the whole date.
    $mystr = "%$sSearch%";
    if (preg_match('/[^0-9]/', $sSearch)) {
        // Delimiter found. Separate it all into year, month and day components.
        $parts = preg_split('/[^0-9]/', $sSearch);
        $parts[1] = $parts[1] ?? '';
        $parts[2] = $parts[2] ?? '';
        // If the first part is more than 2 digits then assume y/m/d format.
        // Otherwise assume MDY or DMY format as appropriate.
        if (strlen($parts[0]) <= 2) {
            $parts = $mdy ? array($parts[2], $parts[0], $parts[1]) :
                array($parts[2], $parts[1], $parts[0]);
        }
        // A single-digit day or month is zero-filled. Fill in other missing
        // digits with wildcards. A 2-digit year like 19 becomes 19__, not __19.
        $parts[0] = substr($parts[0] . '____', 0, 4);
        if (strlen($parts[1]) == 0) {
            $parts[1] = '__';
        } elseif (strlen($parts[1]) == 1) {
            $parts[1] = '0' . $parts[1];
        }
        if (strlen($parts[2]) == 0) {
            $parts[2] = '__';
        } elseif (strlen($parts[2]) == 1) {
            $parts[2] = '0' . $parts[2];
        }
        $mystr = $parts[0] . '-' . $parts[1] . '-' . $parts[2];
    }
    return $mystr;
}

// Global filtering.
//
$where = "";
$srch_bind = array();
if (isset($_GET['sSearch']) && $_GET['sSearch'] !== "") {
    $sSearch = trim($_GET['sSearch']);
    foreach ($aColumns as $colname) {
        $where .= $where ? " OR " : " ( ";
        if ($colname == 'name') {
            $where .=
                "lname LIKE ? OR " .
                "fname LIKE ? OR " .
                "mname LIKE ? ";
            if ($searchMethodInPatientList) { // exact search
                array_push($srch_bind, $sSearch, $sSearch, $sSearch);
            } else {// like search
                array_push($srch_bind, ($sSearch . "%"), ($sSearch . "%"), ($sSearch . "%"));
            }
        } elseif ($searchMethodInPatientList) { // exact search
            $where .= "`" . escape_sql_column_name($colname, array('patient_data')) . "` LIKE ? ";
            array_push($srch_bind, $sSearch);
        } elseif ($searchAny) {
            $where .= " `" . escape_sql_column_name($colname, array('patient_data')) . "` LIKE ?"; // any search
            array_push($srch_bind, ('%' . $sSearch . '%'));
        } else {
            $where .= "`" . escape_sql_column_name($colname, array('patient_data')) . "` LIKE ? ";
            array_push($srch_bind, ($sSearch . '%'));
        }
    }

    if ($where) {
        $where .= ")";
    }
}

// Column-specific filtering.
//
$columnFilters = [];
for ($i = 0; $i < count($aColumns); ++$i) {
    $colname = $aColumns[$i];
    if (isset($_GET["bSearchable_$i"]) && $_GET["bSearchable_$i"] == "true" && $_GET["sSearch_$i"] != '') {
        $where .= $where ? ' AND ' : '';
        $sSearch = $_GET["sSearch_$i"];
        $columnFilters[] = new ColumnFilter($colname, $sSearch);
        if ($colname == 'name') {
            $where .=
                "lname LIKE ? OR " .
                "fname LIKE ? OR " .
                "mname LIKE ? ";
            if ($searchMethodInPatientList) { // exact search
                array_push($srch_bind, $sSearch, $sSearch, $sSearch);
            } else {// like search
                array_push($srch_bind, ($sSearch . "%"), ($sSearch . "%"), ($sSearch . "%"));
            }
        } elseif ($colname == 'DOB') {
            $where .= "`" . escape_sql_column_name($colname, array('patient_data')) . "` LIKE ? ";
            array_push($srch_bind, dateSearch($sSearch));
        } elseif ($searchMethodInPatientList) { // exact search
            $where .= "`" . escape_sql_column_name($colname, array('patient_data')) . "` LIKE ? ";
            array_push($srch_bind, $sSearch);
        } else {
            $where .= "`" . escape_sql_column_name($colname, array('patient_data')) . "` LIKE ? ";
            array_push($srch_bind, ($sSearch . '%'));
        }
    }
}

// Custom filtering, before datatables filtering created by the user
// This allows a module to subscribe to a 'patient-finder.filter' event and
// add filtering before data ever gets to the user
$patientFinderFilterEvent = new PatientFinderFilterEvent(new BoundFilter(), $aColumns, $columnFilters);
$patientFinderFilterEvent = $GLOBALS["kernel"]->getEventDispatcher()->dispatch($patientFinderFilterEvent, PatientFinderFilterEvent::EVENT_HANDLE, 10);
$boundFilter = $patientFinderFilterEvent->getBoundFilter();
$customWhere = $boundFilter->getFilterClause();
$srch_bind = array_merge($boundFilter->getBoundValues(), $srch_bind);

// Compute list of column names for SELECT clause.
// Always includes pid because we need it for row identification.
//
if ($searchAny) {
    $aColumns = explode(',', $_GET['sColumns']);
}
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
// Include the custom filter clause and bound values, if any
$row = sqlQuery("SELECT COUNT(id) AS count FROM patient_data WHERE $customWhere", $boundFilter->getBoundValues());
$iTotal = $row['count'];

// Get total number of rows in the table after filtering.
//
if (empty($where)) {
    $where = $customWhere;
} else {
    $where = "$customWhere AND ( $where )";
}
$row = sqlQuery("SELECT COUNT(id) AS count FROM patient_data WHERE $where", $srch_bind);
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

$query = "SELECT $sellist FROM patient_data WHERE $where $orderby $limit";
$res = sqlStatement($query, $srch_bind);
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
