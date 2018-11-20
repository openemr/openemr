<?php
/**
 * This allos entry and editing of a "billing note" for the patient.
 *
 * Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Roberto Vasquez <robertogagliotta@gmail.com>
 * @link    http://www.open-emr.org
 */


require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("../../library/forms.inc");

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
    if (!verifyCsrfToken($_POST["csrf_token_form"])) {
        csrfNotVerified();
    }

    $thevalue = trim($_POST['form_note']);

    sqlStatement("UPDATE patient_data SET " .
    "billing_note = ? " .
    "WHERE pid = ? ", array($thevalue, $patient_id));

    echo "<script language='JavaScript'>\n";
    if ($info_msg) {
        echo " alert('" . addslashes($info_msg) . "');\n";
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
        <form method='post' action='sl_eob_patient_note.php?patient_id=<?php echo attr($patient_id); ?>'>
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(collectCsrfToken()); ?>" />
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
