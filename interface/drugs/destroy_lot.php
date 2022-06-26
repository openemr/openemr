<?php

/**
 * destroy lot
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("drugs.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

$drug_id = $_REQUEST['drug'];
$lot_id  = $_REQUEST['lot'];
$info_msg = "";

if (!AclMain::aclCheckCore('admin', 'drugs')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Destroy Lot")]);
    exit;
}

if (!$drug_id) {
    die(xlt('Drug ID missing!'));
}

if (!$lot_id) {
    die(xlt('Lot ID missing!'));
}
?>
<html>
<head>
<title><?php echo xlt('Destroy Lot') ?></title>

<?php Header::setupHeader(['datetime-picker', 'opener']); ?>

<style>
    td {
        font-size: 0.8125rem;
    }
</style>

<script>
    $(function () {
        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = false; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });

    function validate(f) {
        if (!confirm(<?php echo xlj('Really destroy this lot?'); ?>)) {
            return false;
        }
        top.restoreSession();
        return true;
    }

</script>

</head>

<body class="body_top">
<?php
 // If we are saving, then save and close the window.
 //
if ($_POST['form_save']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    sqlStatement(
        "UPDATE drug_inventory SET " .
        "destroy_date = ?, "  .
        "destroy_method = ?, " .
        "destroy_witness = ?, " .
        "destroy_notes = ? "  .
        "WHERE drug_id = ? AND inventory_id = ?",
        array(
            (empty($_POST['form_date']) ? "NULL" : $_POST['form_date']),
            $_POST['form_method'],
            $_POST['form_witness'],
            $_POST['form_notes'],
            $drug_id,
            $lot_id
        )
    );

  // Close this window and redisplay the updated list of drugs.
  //
    echo "<script>\n";
    if ($info_msg) {
        echo " alert('" . addslashes($info_msg) . "');\n";
    }

    echo " window.close();\n";
    echo " if (opener.refreshme) opener.refreshme();\n";
    echo "</script></body></html>\n";
    exit();
}

 $row = sqlQuery("SELECT * FROM drug_inventory WHERE drug_id = ? " .
  "AND inventory_id = ?", array($drug_id,$lot_id));
    ?>

<form method='post' name='theform' onsubmit='return validate(this);'
 action='destroy_lot.php?drug=<?php echo attr_url($drug_id) ?>&lot=<?php echo attr_url($lot_id) ?>'>

<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<center>

<table class='table-borderless w-100'>

 <tr>
  <td class="text-nowrap align-top font-weight-bold" width='1%'><?php echo xlt('Lot Number'); ?>:</td>
  <td>
    <?php echo text($row['lot_number']) ?>
  </td>
 </tr>

 <tr>
  <td class="text-nowrap align-top font-weight-bold"><?php echo xlt('Manufacturer'); ?>:</td>
  <td>
    <?php echo text($row['manufacturer']) ?>
  </td>
 </tr>

 <tr>
  <td class="text-nowrap align-top font-weight-bold"><?php echo xlt('Quantity On Hand'); ?>:</td>
  <td>
    <?php echo text($row['on_hand']) ?>
  </td>
 </tr>

 <tr>
  <td class="text-nowrap align-top font-weight-bold"><?php echo xlt('Expiration Date'); ?>:</td>
  <td>
    <?php echo text($row['expiration']) ?>
  </td>
 </tr>

 <tr>
  <td class="text-nowrap align-top font-weight-bold"><?php echo xlt('Date Destroyed'); ?>:</td>
  <td>
   <input type='text' size='10' class='datepicker' name='form_date' id='form_date' value='<?php echo $row['destroy_date'] ? attr($row['destroy_date']) : date("Y-m-d"); ?>' title='<?php echo xla('yyyy-mm-dd date destroyed'); ?>' />
  </td>
 </tr>

 <tr>
  <td class="text-nowrap align-top font-weight-bold"><?php echo xlt('Method of Destruction'); ?>:</td>
  <td>
   <input type='text' class='w-100' size='40' name='form_method' maxlength='250'
    value='<?php echo attr($row['destroy_method']) ?>' />
  </td>
 </tr>

 <tr>
  <td class="text-nowrap align-top font-weight-bold"><?php echo xlt('Witness'); ?>:</td>
  <td>
   <input type='text' class='w-100' size='40' name='form_witness' maxlength='250'
    value='<?php echo attr($row['destroy_witness']) ?>' />
  </td>
 </tr>

 <tr>
  <td class="text-nowrap align-top font-weight-bold"><?php echo xlt('Notes'); ?>:</td>
  <td>
   <input type='text' class='w-100' size='40' name='form_notes' maxlength='250'
    value='<?php echo attr($row['destroy_notes']) ?>' />
  </td>
 </tr>

</table>

<div class="btn-group">
<input type='submit' class="btn btn-primary" name='form_save' value='<?php echo xla('Submit') ;?>' />
<input type='button' class="btn btn-secondary" value='<?php echo xla('Cancel'); ?>' onclick='window.close()' />
</div>

</center>
</form>
</body>
</html>
