<?php

/**
 * Interactive code finder AJAX support.
 * For DataTables documentation see: http://legacy.datatables.net/
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2015-2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/options.inc.php");
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

// Paging parameters.  -1 means not applicable.
//
$iDisplayStart  = isset($_GET['iDisplayStart' ]) ? 0 + $_GET['iDisplayStart' ] : -1;
$iDisplayLength = isset($_GET['iDisplayLength']) ? 0 + $_GET['iDisplayLength'] : -1;
$limit = '';
if ($iDisplayStart >= 0 && $iDisplayLength >= 0) {
    $limit = "LIMIT " . escape_limit($iDisplayStart) . ", " . escape_limit($iDisplayLength);
}
$searchTerm = isset($_GET['sSearch']) ? $_GET['sSearch'] : '';

// What we are picking from: codes, fields, lists or groups
$what = $_GET['what'];
$layout_id = '';

if ($what == 'codes') {
    $codetype = $_GET['codetype'];
    $prod = $codetype == 'PROD';
    $ncodetype = $code_types[$codetype]['id'];
    $include_inactive = !empty($_GET['inactive']);
} elseif ($what == 'fields') {
    $source = empty($_GET['source']) ? 'D' : $_GET['source'];
    if ($source == 'D') {
        $layout_id = 'DEM';
    } elseif ($source == 'H') {
        $layout_id = 'HIS';
    } elseif ($source == 'E') {
        $layout_id = 'LBF%';
    }
} elseif ($what == 'groups') {
    if (!empty($_GET['layout_id'])) {
        $layout_id = $_GET['layout_id'];
    }
}

$form_encounter_layout = array(
  array('field_id'     => 'date',
        'title'        => xl('Visit Date'),
        'uor'          => '1',
        'data_type'    => '4',               // Text-date
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'facility_id',
        'title'        => xl('Service Facility'),
        'uor'          => '1',
        'data_type'    => '35',              // Facilities
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'pc_catid',
        'title'        => xl('Visit Category'),
        'uor'          => '1',
        'data_type'    => '18',              // Visit Category
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'reason',
        'title'        => xl('Reason for Visit'),
        'uor'          => '1',
        'data_type'    => '2',               // Text
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'onset_date',
        'title'        => xl('Date of Onset'),
        'uor'          => '1',
        'data_type'    => '4',               // Text-date
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'referral_source',
        'title'        => xl('Referral Source'),
        'uor'          => '1',
        'data_type'    => '1',               // List
        'list_id'      => 'refsource',
        'edit_options' => '',
       ),
  array('field_id'     => 'shift',
        'title'        => xl('Shift'),
        'uor'          => '1',
        'data_type'    => '1',               // List
        'list_id'      => 'shift',
        'edit_options' => '',
       ),
  array('field_id'     => 'billing_facility',
        'title'        => xl('Billing Facility'),
        'uor'          => '1',
        'data_type'    => '35',              // Facilities
        'list_id'      => '',
        'edit_options' => '',
       ),
  array('field_id'     => 'voucher_number',
        'title'        => xl('Voucher Number'),
        'uor'          => '1',
        'data_type'    => '2',               // Text
        'list_id'      => '',
        'edit_options' => '',
       ),
);

function feSearchSort($search = '', $column = 0, $reverse = false)
{
    global $form_encounter_layout;
    $arr = array();
    foreach ($form_encounter_layout as $feitem) {
        if (
            $search && stripos($feitem['field_id'], $search) === false &&
            stripos($feitem['title'], $search) === false
        ) {
            continue;
        }
        $feitem['fld_length' ] = 20;
        $feitem['max_length' ] = 0;
        $feitem['titlecols'  ] = 1;
        $feitem['datacols'   ] = 3;
        $feitem['description'] = '';
        $feitem['fld_rows'   ] = 0;
        $key = $column ? 'title' : 'field_id';
        $arr[$feitem[$key]] = $feitem;
    }
    ksort($arr);
    if ($reverse) {
        $arr = array_reverse($arr);
    }
    return $arr;
}

function genFieldIdString($row)
{
    return 'CID|' . json_encode($row);
}

// Column sorting parameters.
//
$orderby = '';
$ordermode = null;
$fe_column = 0;
$fe_reverse = false;
if (isset($_GET['iSortCol_0'])) {
    for ($i = 0; $i < intval($_GET['iSortingCols']); ++$i) {
        $iSortCol = intval($_GET["iSortCol_$i"]);
        if ($_GET["bSortable_$iSortCol"] == "true") {
            $sSortDir = escape_sort_order($_GET["sSortDir_$i"]); // ASC or DESC
      // We are to sort on column # $iSortCol in direction $sSortDir.
            $orderby .= $orderby ? ', ' : 'ORDER BY ';

      // Note the primary sort column and direction for later logic.
            if ($i == 0) {
                $fe_column = $iSortCol;
                $fe_reverse = $sSortDir == 'DESC';
            }

            if ($what == 'codes') {
                if ($iSortCol == 0) {
                  // $orderby .= $prod ? "d.drug_id $sSortDir, t.selector $sSortDir" : "c.code $sSortDir";
                    $ordermode = array('code', 'description');
                } else {
                  // $orderby .= $prod ? "d.name $sSortDir" : "c.code_text $sSortDir";
                    $ordermode = array('description', 'code');
                }
            } elseif ($what == 'fields') {
                if ($source == 'V') {
                  // No action needed here.
                } else {
                  // Remaining sources (D, H, E) come from a layout.
                    if ($iSortCol == 0) {
                        $orderby .= "lo.field_id $sSortDir";
                    } else {
                        $orderby .= "lo.title $sSortDir";
                    }
                }
            } elseif ($what == 'lists') {
                if ($iSortCol == 0) {
                    $orderby .= "li.list_id $sSortDir";
                } else {
                    $orderby .= "li.option_id $sSortDir";
                }
            } elseif ($what == 'groups') {
                if ($iSortCol == 0) {
                    $orderby .= "code $sSortDir";
                } else {
                    $orderby .= "description $sSortDir";
                }
            }
        }
    }
}

if ($what == 'codes') {
  // Nothing to do here.
} elseif ($what == 'fields') {
    if ($source == 'V') {
      // No setup needed.
    } elseif ($source == 'E') {
        $sellist = "lo.field_id, " .
        "MIN(lo.group_id    ) AS group_id, "     .
        "MIN(lo.title       ) AS title, "        .
        "MIN(lo.data_type   ) AS data_type, "    .
        "MIN(lo.uor         ) AS uor, "          .
        "MIN(lo.fld_length  ) AS fld_length, "   .
        "MIN(lo.max_length  ) AS max_length, "   .
        "MIN(lo.list_id     ) AS list_id, "      .
        "MIN(lo.titlecols   ) AS titlecols, "    .
        "MIN(lo.datacols    ) AS datacols, "     .
        "MIN(lo.edit_options) AS edit_options, " .
        "MIN(lo.description ) AS description, "  .
        "MIN(lo.fld_rows    ) AS fld_rows";
        $orderby = "GROUP BY lo.field_id $orderby";
        $from = "layout_options AS lo";
        $where1 = "WHERE lo.form_id LIKE '" . add_escape_custom($layout_id) . "' AND lo.uor > 0 AND lo.source = 'E'";
        if ($searchTerm !== "") {
            $sSearch = add_escape_custom($searchTerm);
            $where2 = "AND (lo.field_id LIKE '%$sSearch%' OR lo.title LIKE '%$sSearch%')";
        }
    } elseif ($source == 'D' || $source == 'H') {
        $sellist = "lo.*";
        $from = "layout_options AS lo";
        $where1 = "WHERE lo.form_id LIKE '" . add_escape_custom($layout_id) . "' AND lo.uor > 0";
        if ($searchTerm !== "") {
            $sSearch = add_escape_custom($searchTerm);
            $where2 = "AND (lo.field_id LIKE '%$sSearch%' OR lo.title LIKE '%$sSearch%')";
        }
    }
} elseif ($what == 'lists') {
    $sellist = "li.option_id AS code, li.title AS description";
    $from = "list_options AS li";
    $where1 = "WHERE li.list_id LIKE 'lists' AND li.activity = 1";
    if ($searchTerm !== "") {
        $sSearch = add_escape_custom($searchTerm);
        $where2 = "AND (li.list_id LIKE '%$sSearch%' OR li.title LIKE '%$sSearch%')";
    }
} elseif ($what == 'groups') {
    $sellist .= "DISTINCT lp.grp_group_id AS code, lp.grp_title AS description";
    $from = "layout_group_properties AS lp";
    $where1 = "WHERE lp.grp_form_id LIKE '" . add_escape_custom($layout_id) . "' AND lp.grp_group_id != ''";
    if ($searchTerm !== "") {
        $sSearch = add_escape_custom($searchTerm);
        $where2 = "AND lp.grp_title LIKE '%$sSearch%'";
    }
} else {
    error_log('Invalid request to find_code_dynamic_ajax.php');
    exit();
}

if ($what == 'fields' && $source == 'V') {
    $fe_array = feSearchSort($searchTerm, $fe_column, $fe_reverse);
    $iTotal = count($form_encounter_layout);
    $iFilteredTotal = count($fe_array);
} elseif ($what == 'codes') {
    $iTotal = main_code_set_search($codetype, '', null, null, !$include_inactive, null, true);
    $iFilteredTotal = main_code_set_search($codetype, $searchTerm, null, null, !$include_inactive, null, true);
} else {
  // Get total number of rows with no filtering.
    $iTotal = sqlNumRows(sqlStatement("SELECT $sellist FROM $from $where1 $orderby"));
  // Get total number of rows after filtering.
    $iFilteredTotal = sqlNumRows(sqlStatement("SELECT $sellist FROM $from $where1 " . ($where2 ?? '') . " $orderby"));
}

// Build the output data array.
//
$out = array(
  "sEcho"                => intval($_GET['sEcho']),
  "iTotalRecords"        => ($iTotal) ? $iTotal : 0,
  "iTotalDisplayRecords" => ($iFilteredTotal) ? $iFilteredTotal : 0,
  "aaData"               => array()
);

if ($what == 'fields' && $source == 'V') {
    foreach ($fe_array as $feitem) {
        $arow = array('DT_RowId' => genFieldIdString($feitem));
        $arow[] = $feitem['field_id'];
        $arow[] = $feitem['title'];
        $out['aaData'][] = $arow;
    }
} elseif ($what == 'codes') {
    $start = null;
    $number = null;
    if ($iDisplayStart >= 0 && $iDisplayLength >= 0) {
        $start  = (int) $iDisplayStart;
        $number = (int) $iDisplayLength;
    }
    $res = main_code_set_search(
        $codetype,
        $searchTerm,
        null,
        null,
        !$include_inactive,
        $ordermode,
        false,
        $start,
        $number
    );
    if (!empty($res)) {
        while ($row = sqlFetchArray($res)) {
            $dynCodeType = $codetype;
            if (stripos($codetype, 'VALUESET') !== false) {
                $dynCodeType = $row['valueset_code_type'] ?? 'VALUESET';
            }
            $arow = array('DT_RowId' => genFieldIdString(array(
              'code' => $row['code'],
              'description' => $row['code_text'],
              'codetype' => $dynCodeType,
            )));
            $arow[] = str_replace('|', ':', rtrim($row['code'], '|'));
            $arow[] = $row['code_text'];
            $out['aaData'][] = $arow;
        }
    }
} else {
    $query = "SELECT $sellist FROM $from $where1 " . ($where2 ?? '') . " $orderby $limit";
    $res = sqlStatement($query);
    while ($row = sqlFetchArray($res)) {
        $arow = array('DT_RowId' => genFieldIdString($row));
        if ($what == 'fields') {
            $arow[] = $row['field_id'];
            $arow[] = $row['title'];
        } else {
            $arow[] = str_replace('|', ':', rtrim($row['code'], '|'));
            $arow[] = $row['description'];
        }
        $out['aaData'][] = $arow;
    }
}

// error_log($query); // debugging

// Dump the output array as JSON.
//
echo json_encode($out);
