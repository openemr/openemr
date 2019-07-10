<?php
/**
 * This report lists destroyed drug lots within a specified date range.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("../drugs/drugs.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$form_from_date = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01'); // From date filter
$form_to_date = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');   // To date filter
?>
<html>
<head>
<title><?php echo xlt('Destroyed Drugs'); ?></title>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker/build/jquery.datetimepicker.min.css">

<style>
table.mymaintable, table.mymaintable td, table.mymaintable th {
 border: 1px solid #aaaaaa;
 border-collapse: collapse;
}
table.mymaintable td, table.mymaintable th {
 padding: 1pt 4pt 1pt 4pt;
}
</style>

<script type="text/javascript" src="../../library/textformat.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="../../library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-1-9-1/jquery.min.js"></script>
<script type="text/javascript" src="../../library/js/report_helper.js?v=<?php echo $v_js_includes; ?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-datetimepicker/build/jquery.datetimepicker.full.min.js"></script>

<script language="JavaScript">

$(function() {
    oeFixedHeaderSetup(document.getElementById('mymaintable'));
    var win = top.printLogSetup ? top : opener.top;
    win.printLogSetup(document.getElementById('printbutton'));

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

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<center>

<h2><?php echo xlt('Destroyed Drugs'); ?></h2>

<form name='theform' method='post' action='destroyed_drugs_report.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<table border='0' cellpadding='3'>

 <tr>
  <td>
    <?php echo xlt('From'); ?>:
   <input type='text' class='datepicker' name='form_from_date' id='form_from_date'
    size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>

   &nbsp;<?php echo xlt('To'); ?>:
   <input type='text' class='datepicker' name='form_to_date' id='form_to_date'
    size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>

   &nbsp;
   <input type='submit' name='form_refresh' value='<?php echo xla('Refresh'); ?>'>
   &nbsp;
   <input type='button' value='<?php echo xla('Print'); ?>' id='printbutton' />
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<table width='98%' id='mymaintable' class='mymaintable'>
 <thead>
 <tr bgcolor="#dddddd">
  <td class='dehead'>
    <?php echo xlt('Drug Name'); ?>
  </td>
  <td class='dehead'>
    <?php echo xlt('NDC'); ?>
  </td>
  <td class='dehead'>
    <?php echo xlt('Lot'); ?>
  </td>
  <td class='dehead'>
    <?php echo xlt('Qty'); ?>
  </td>
  <td class='dehead'>
    <?php echo xlt('Date Destroyed'); ?>
  </td>
  <td class='dehead'>
    <?php echo xlt('Method'); ?>
  </td>
  <td class='dehead'>
    <?php echo xlt('Witness'); ?>
  </td>
  <td class='dehead'>
    <?php echo xlt('Notes'); ?>
  </td>
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

  // echo "<!-- $query -->\n"; // debugging
    $res = sqlStatement($query, array($form_from_date, $form_to_date));

    $last_drug_id = 0;
    while ($row = sqlFetchArray($res)) {
        $drug_name       = $row['name'];
        $ndc_number      = $row['ndc_number'];
        if ($row['drug_id'] == $last_drug_id) {
            $drug_name  = '&nbsp;';
            $ndc_number = '&nbsp;';
        }
        ?>
   <tr>
    <td class='detail'>
        <?php echo text($drug_name); ?>
  </td>
  <td class='detail'>
        <?php echo text($ndc_number); ?>
  </td>
  <td class='detail'>
     <a href='../drugs/destroy_lot.php?drug=<?php echo attr_url($row['drug_id']); ?>&lot=<?php echo attr_url($row['inventory_id']); ?>'
    style='color:#0000ff' target='_blank'>
        <?php echo text($row['lot_number']); ?>
   </a>
  </td>
  <td class='detail'>
        <?php echo text($row['on_hand']); ?>
  </td>
  <td class='detail'>
        <?php echo text(oeFormatShortDate($row['destroy_date'])); ?>
  </td>
  <td class='detail'>
        <?php echo text($row['destroy_method']); ?>
  </td>
  <td class='detail'>
        <?php echo text($row['destroy_witness']); ?>
  </td>
  <td class='detail'>
        <?php echo text($row['destroy_notes']); ?>
  </td>
 </tr>
        <?php
        $last_drug_id = $row['drug_id'];
    } // end while
} // end if
?>

 </tbody>
</table>
</form>
</center>
</body>
</html>
