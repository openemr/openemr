<?php

/**
 * types_ajax.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2012 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

$id = (isset($_GET['id']) ? $_GET['id'] : '') + 0;
$order = (isset($_GET['order']) ? $_GET['order'] : '') + 0;
$labid = (isset($_GET['labid']) ? $_GET['labid'] : '') + 0;

$render = '';

$render .= "<table class=\"table\">";
// Determine indentation level for this container.
for ($level = 0, $parentid = $id; $parentid; ++$level) {
    $row = sqlQuery("SELECT parent FROM procedure_type WHERE procedure_type_id = ?", [$parentid]);
    $parentid = $row['parent'] + 0;
}

$res = sqlStatement("SELECT * FROM procedure_type WHERE parent = ? " .
  "ORDER BY seq, name, procedure_type_id", [$id]);

$encount = 0;
$isOrder = "";
// Generate a table row for each immediate child.
while ($row = sqlFetchArray($res)) {
    $chid = $row['procedure_type_id'] + 0;
    $isOrder = substr($row['procedure_type'], 0, 3);

    // Find out if this child has any children.
    $trow = sqlQuery("SELECT procedure_type_id FROM procedure_type WHERE parent = ? LIMIT 1", [$chid]);
    $iscontainer = !empty($trow['procedure_type_id']);

    $classes = 'col1';
    if ($iscontainer) {
        $classes .= ' haskids';
    }
    // for proper indentation
    if (($isOrder == 'grp' || $isOrder == 'fgp') && $row['parent'] == 0) {
         $classes .= ' oe-grp';
         $classes .= ' oe-pl' . ($level * 10) ;
    } elseif (($isOrder == 'grp' || $isOrder == 'fgp') && $row['parent'] != 0) {
        $classes .= ' oe-bold';
        $classes .= ' oe-pl' . ($level * 10) ;
    } elseif ($isOrder == 'ord' || $isOrder == 'for') {
         $classes .= ' oe-ord';
          $classes .= ' oe-pl' . ($level * 10) ;
    } else {
        $classes .= ' oe-pl' . ($level * 10) ;
    }

    $render .= "<tr>";
    $render .= "<td id=\"td" . attr($chid) . "\"";
    $render .= " onclick=\"toggle(" . attr_js($chid) . ")\"";
    $render .= " class=\"" . attr($classes) . "\">";
    $render .= "<span style=\"margin: 0 4 0 " . round(($level * 9) * 1.3333) . "px\" class=\"plusminus\">";
    $render .= "<span class=\"plusminus\">";
    $render .= $iscontainer ? "+ " : '| ';
    $render .= "</span>";
    if ($isOrder == 'ord') {
        $render .= "<mark class=\"oe-patient-background\">" . text($row['name']) . "</mark></td>";
    } elseif ($isOrder == 'for') {
        $render .= "<mark class=\"oe-pink-background\">" . text($row['name']) . "</mark></td>";
    } else {
        $render .= text($row['name']) . "</td>";
    }
  //
    $render .= "<td class=\"col2\">";
    if ($isOrder == 'ord' || $isOrder == 'for') {
        if ($order && ($labid == 0 || $row['lab_id'] == $labid)) {
            $render .= "<input type=\"radio\" name=\"form_order\" value=\"" . attr($chid) . "\"";
            if ($chid == $order) {
                $render .= " checked";
            }

            $render .= " />";
        } else {
            if ($isOrder == 'ord') {
                $render .= "<mark class=\"oe-patient-background\">" . xlt('Order') . "</mark>";
            } elseif ($isOrder == 'for') {
                $render .= "<mark class=\"oe-pink-background\">" . xlt('Custom Order') . "</mark>";
            }
        }
    } else {
        //$render .= '&nbsp;';
        if ($isOrder == 'grp' && $row['parent'] == 0) {
            $render .=  xlt('Top Group');
        } elseif ($isOrder == 'fgp' && $row['parent'] == 0) {
            $render .= "<mark class=\"oe-pink-background\">" . xlt('Custom Top Group') . "</mark>";
        } elseif ($isOrder == 'grp') {
             $render .=  xlt('Sub Group');
        } elseif ($isOrder == 'fgp') {
            $render .= "<mark class=\"oe-pink-background\">" . xlt('Custom Sub Group') . "</mark>";
        } elseif ($isOrder == 'res') {
            $render .=  xlt('Result');
        } elseif ($isOrder == 'rec') {
            $render .=  xlt('Recommendation');
        }
    }

    $render .= "</td>";
    if (($isOrder != 'grp' && $isOrder != 'fgp') &&  !empty($row['procedure_code'])) {
        $render .= "<td class=\"col3\">" . text($row['procedure_code']) . "</td>";
    } elseif (($isOrder != 'grp' && $isOrder != 'fgp') &&  empty($row['procedure_code'])) {
        $render .= "<td class=\"col3\" style=\"padding-left: 15px\"><span class=\"required-tooltip\" title=\"" .
            xla("Missing Identifying Code") .
            "\"><i class=\"fa fa-exclamation-triangle text-center text-danger\" aria-hidden=\"true\" ></i></span></td>";
    } elseif ($isOrder == 'grp' || $isOrder == 'fgp') {
        $render .= "<td class=\"col3\">" . text($row['procedure_code']) . "</td>";
    }
    $typeIs = 0;
    $thislab = $row['lab_id'] ? $row['lab_id'] + 0 : 0;
    if ($isOrder == 'fgp') {
        $typeIs = 1;
    } elseif ($isOrder == 'for') {
        $typeIs = 2;
    }
    $render .= "<td class=\"col6\">" . text($level + 1) . "</td>";
    $render .= "<td class=\"col4\">" . text($row['description']) . "</td>";
    $render .= "<td class=\"col5\">";
    $render .= "<span onclick=\"handleNode(" . attr_js($chid) . "," .
        attr_js($typeIs) . ",false," . attr_js($thislab) .
        ")\" class=\"text-body haskids fa fa-pencil-alt fa-lg\" title=" . xla("Edit") . "></span>";
    $render .= "</td>";
    $render .= "<td class=\"col5\">";
    //if ($isOrder != 'for') {//RP_MODIFIED 2018-08-03 to allow for manual lab entry
        $render .= "<span style=\"margin-left: 30px\" onclick=\"handleNode(" .
        attr_js($chid) . "," . attr_js($typeIs) . ",true," . attr_js($thislab) .
        ")\" class=\"haskids text-body fa fa-plus fa-lg\" title=" . xla("Add") . " ></span>";
    //}//RP_MODIFIED 2018-08-03
    $render .= "</td>";
    $render .= "</tr>";
}

$render .= "</table>\n";
$rendered = "$('#con" . attr($id) . "').html(";
$rendered .= js_escape($render) . ");";
$rendered .= "nextOpen();\n";
echo $rendered;
