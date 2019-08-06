<?php
/**
 * This allows entry and editing of a "billing note" for the patient.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("../../library/forms.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$info_msg = "";
?>
<html>
<head>
<?php Header::setupHeader();?>
<title><?php echo xlt('EOB Posting - Patient Note'); ?></title>
</head>
<body>
<?php
  $patient_id = $_GET['patient_id'];
if (! $patient_id) {
    die(xlt("You cannot access this page directly."));
}

if ($_POST['form_save']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $thevalue = trim($_POST['form_note']);

    sqlStatement("UPDATE patient_data SET " .
    "billing_note = ? " .
    "WHERE pid = ? ", array($thevalue, $patient_id));

    echo "<script language='JavaScript'>\n";
    if ($info_msg) {
        echo " alert(" . js_escape($info_msg) . ");\n";
    }
    echo " window.close();\n";
    echo "</script></body></html>\n";
    exit();
}

  $row = sqlQuery("select fname, lname, billing_note " .
    "from patient_data where pid = ? limit 1", array($patient_id));
    ?>
<div class="container">
    <div class = "row">
        <div class="page-header">
                <h2><?php echo xlt('Billing Note for '). text($row['fname']) . " " . text($row['lname']); ?></h2>
            </div>
    </div>
    <div class = "row">
        <form method='post' action='sl_eob_patient_note.php?patient_id=<?php echo attr_url($patient_id); ?>'>
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <div class="col-xs-12" style="padding-bottom:5px">

            </div>
            <div class="col-xs-12" style="padding-bottom:5px">
                <div class="col-xs-12">
                    <input type='text' name='form_note' class='form-control' value='<?php echo attr($row['billing_note']) ?>' placeholder ='<?php echo xla('Max 255 characters')?>' />
                </div>
            </div>
            <?php //can change position of buttons by creating a class 'position-override' and adding rule text-alig:center or right as the case may be in individual stylesheets ?>
            <div class="form-group clearfix">
                <div class="col-sm-12 text-left position-override" id="search-btn">
                    <div class="btn-group" role="group">
                        <button type='submit' class="btn btn-default btn-save" name='form_save' id="btn-save" ><?php echo xlt("Save"); ?></button>
                        <button type='submit' class="btn btn-link btn-cancel btn-separate-left" name='form_cancel' id="btn-cancel"  onclick='window.close();'><?php echo xlt("Cancel"); ?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div><!--end of container div-->

</body>
</html>
