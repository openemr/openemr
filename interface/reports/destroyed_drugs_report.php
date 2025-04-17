<?php

/**
 * This report lists destroyed drug lots within a specified date range.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once("../drugs/drugs.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$form_from_date = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01'); // From date filter
$form_to_date = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');   // To date filter

function processData($data)
{
    $data['inventory_id'] = [$data['inventory_id']];
    $data['lot_number'] = [$data['lot_number']];
    $data['on_hand'] = [$data['on_hand']];
    $data['destroy_date'] = [$data['destroy_date']];
    $data['destroy_method'] = [$data['destroy_method']];
    $data['destroy_witness'] = [$data['destroy_witness']];
    $data['destroy_notes'] = [$data['destroy_notes']];
    return $data;
}
function mergeData($d1, $d2)
{
    $d1['inventory_id'] = array_merge($d1['inventory_id'], $d2['inventory_id']);
    $d1['lot_number'] = array_merge($d1['lot_number'], $d2['lot_number']);
    $d1['on_hand'] = array_merge($d1['on_hand'], $d2['on_hand']);
    $d1['destroy_date'] = array_merge($d1['destroy_date'], $d2['destroy_date']);
    $d1['destroy_method'] = array_merge($d1['destroy_method'], $d2['destroy_method']);
    $d1['destroy_witness'] = array_merge($d1['destroy_witness'], $d2['destroy_witness']);
    $d1['destroy_notes'] = array_merge($d1['destroy_notes'], $d2['destroy_notes']);
    return $d1;
}
function mapToTable($row)
{
    if ($row) {
        echo "<tr>\n";
        echo "<td> " . text($row["name"]) . " </td>\n";
        echo "<td>" . text($row["ndc_number"]) . " </td>\n";
        echo "<td>";
        foreach ($row['inventory_id'] as $key => $value) {
            echo "<div onclick='doclick(" . attr(addslashes($row['drug_id'])) . "," . attr(addslashes($row['inventory_id'][$key])) . ")'>" .
            "<a href='' onclick='return false'>" . text($row['lot_number'][$key]) . "</a></div>";
        }
        echo "</td>\n<td>";

        foreach ($row['on_hand'] as $value) {
            $value = $value != null ? $value : "N/A";
            echo "<div >" . text($value) . "</div>";
        }
        echo "</td>\n<td>";

        foreach ($row['destroy_date'] as $value) {
            $value = $value != null ? $value : "N/A";
            echo "<div >" . text(oeFormatShortDate($value)) . "</div>";
        }
        echo "</td>\n<td>";

        foreach ($row['destroy_method'] as $value) {
            $value = $value != null ? $value : "N/A";
            echo "<div >" . text($value) . "</div>";
        }
        echo "</td>\n<td>";

        foreach ($row['destroy_witness'] as $value) {
            $value = $value != null ? $value : "N/A";
            echo "<div >" . text($value) . "</div>";
        }
        echo "</td>\n<td>";

        foreach ($row['destroy_notes'] as $value) {
            $value = $value != null ? $value : "N/A";
            echo "<div >" . text($value) . "</div>";
        }
        echo "</td>\n</tr>\n";
    }
}
?>
<html>
<head>
<title><?php echo xlt('Destroyed Drugs'); ?></title>

    <?php Header::setupHeader(['datetime-picker','datatables', 'datatables-dt', 'datatables-bs', 'report-helper']); ?>

<style>
/* TODO: Is the below code for links necessary? */
a, a:visited, a:hover {
  color: var(--primary);
}
#mymaintable thead .sorting::before,
#mymaintable thead .sorting_asc::before,
#mymaintable thead .sorting_asc::after,
#mymaintable thead .sorting_desc::before,
#mymaintable thead .sorting_desc::after,
#mymaintable thead .sorting::after {
  display: none;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
  padding: 0 !important;
  margin: 0 !important;
  border: 0 !important;
}

.paginate_button:hover{
  background: transparent !important;
}
</style>

<script>

// Process click on destroyed drug.
function doclick(id, lot) {
 dlgopen('../drugs/destroy_lot.php?drug=' + encodeURIComponent(id) + '&lot=' + encodeURIComponent(lot), '_blank', 600, 475);
}

$(function () {
    var win = top.printLogSetup ? top : opener.top;
    win.printLogSetup(document.getElementById('printbutton'));

    $('#mymaintable').DataTable({
            stripeClasses:['stripe1','stripe2'],
            orderClasses: false,
            <?php // Bring in the translations ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/datatables-net.js.php'); ?>
    });

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});

</script>
</head>

<body class="container-fluid text-center">


<h2><?php echo xlt('Destroyed Drugs'); ?></h2>

<form name='theform' method='post' action='destroyed_drugs_report.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div class="col-sm-12">
    <span class="font-weight-bold"><?php echo xlt('From'); ?>:</span>
    <input type='text' style="width: 200px" class='datepicker form-control d-inline' name='form_from_date' id='form_from_date'
    size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
    <span class="font-weight-bold"><?php echo xlt('To{{Range}}'); ?>:</span>
    <input type='text' style="width: 200px" class='datepicker form-control d-inline' name='form_to_date' id='form_to_date'
    size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>
    <input class="btn btn-primary" type='submit' name='form_refresh' value='<?php echo xla('Refresh'); ?>' />
    <input class="btn btn-secondary" type='button' value='<?php echo xla('Print'); ?>' id='printbutton' />
</div>

<!-- TODO: Why didn't we use the BS4 table class here? !-->
<table id='mymaintable' class="display table-striped">
 <thead>
 <tr>
  <th><?php echo xlt('Drug Name'); ?></th>
  <th><?php echo xlt('NDC'); ?></th>
  <th><?php echo xlt('Lot'); ?></th>
  <th><?php echo xlt('Qty'); ?></th>
  <th><?php echo xlt('Date Destroyed'); ?></th>
  <th><?php echo xlt('Method'); ?></th>
  <th><?php echo xlt('Witness'); ?></th>
  <th><?php echo xlt('Notes'); ?></th>
 </tr>
 </thead>
 <tbody>
<?php
if ($_POST['form_refresh']) {
    $where = "i.destroy_date >= ? AND " .
    "i.destroy_date <= ?";

    $query = "SELECT i.inventory_id, i.lot_number, i.on_hand, i.drug_id, " .
    "i.destroy_date, i.destroy_method, i.destroy_witness, i.destroy_notes, " .
    "d.name, d.ndc_number " .
    "FROM drug_inventory AS i " .
    "LEFT OUTER JOIN drugs AS d ON d.drug_id = i.drug_id " .
    "WHERE $where " .
    "ORDER BY d.name, i.drug_id, i.destroy_date, i.lot_number";

    $res = sqlStatement($query, array($form_from_date, $form_to_date));
    $prevRow = '';
    while ($row = sqlFetchArray($res)) {
        $row = processData($row);
        if ($prevRow == '') {
            $prevRow = $row;
            continue;
        }
        if ($prevRow['drug_id'] == $row['drug_id']) {
            $row = mergeData($prevRow, $row);
        } else {
            mapToTable($prevRow);
        }
        $prevRow = $row;
    }
    mapToTable($prevRow);
}
?>

 </tbody>
</table>
</form>

</body>
</html>
