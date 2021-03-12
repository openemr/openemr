<?php

/**
 * Patient matching and selection dialog.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013-2015 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$form_key = $_REQUEST['key'];
$args = unserialize($form_key, ['allowed_classes' => false]);
$form_ss = preg_replace('/[^0-9]/', '', $args['ss']);
$form_fname = $args['fname'];
$form_lname = $args['lname'];
$form_DOB = $args['DOB'];
?>
<!DOCTYPE html>
<html>
<head>
<?php Header::setupHeader(['opener']); ?>
<style>
    .oneResult {
    }
</style>
<script>

    $(function () {
        $(".oneresult").mouseover(function () {
            $(this).addClass("highlight");
        });
        $(".oneresult").mouseout(function () {
            $(this).removeClass("highlight");
        });
    });

    function myRestoreSession() {
        if (top.restoreSession) top.restoreSession(); else opener.top.restoreSession();
        return true;
    }

    function openPatient(ptid) {
        var f = opener.document.forms[0];
        var ename = <?php echo js_escape("select[$form_key]"); ?>;
        if (f[ename]) {
            f[ename].value = ptid;
            window.close();
        }
        else {
            alert(<?php echo xlj('Form element not found'); ?> + ': ' + ename);
        }
    }

</script>
</head>

<body class="body_top">
<form method='post' action='patient_select.php' onsubmit='return myRestoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<?php
if ($form_key) {
    $clarr = array();
    $clsql = "0";
// First name.
    if ($form_fname !== '') {
        $clsql .= " + ((fname IS NOT NULL AND fname = ?) * 5)";
        $clarr[] = $form_fname;
    }

// Last name.
    if ($form_lname !== '') {
        $clsql .= " + ((lname IS NOT NULL AND lname = ?) * 5)";
        $clarr[] = $form_lname;
    }

// Birth date.
    if ($form_DOB !== '') {
        $clsql .= " + ((DOB IS NOT NULL AND DOB = ?) * 5)";
        $clarr[] = $form_DOB;
    }

// SSN match is worth a lot and we allow for matching on last 4 digits.
    if (strlen($form_ss) > 3) {
        $clsql .= " + ((ss IS NOT NULL AND ss LIKE ?) * 10)";
        $clarr[] = "%$form_ss";
    }

    $sql = "SELECT $clsql AS closeness, " .
        "pid, pubpid, fname, lname, mname, DOB, ss, postal_code, street, " .
        "phone_biz, phone_home, phone_cell, phone_contact " .
        "FROM patient_data " .
        "ORDER BY closeness DESC, lname, fname LIMIT 10";
    $res = sqlStatement($sql, $clarr);
    ?>

    <div id="searchResults">

        <table class="table table-striped table-sm">
            <h5>
                <?php
                echo xlt('Matching for Patient') . ": " .
                    text("$form_lname, $form_fname") . text(" Dob = $form_DOB") .
                    " SS = " . text(($form_ss ? $form_ss : "unk"))
                ?>
            </h5>
            <tr>
                <th><?php echo xlt('Name'); ?></th>
                <th><?php echo xlt('Phone'); ?></th>
                <th><?php echo xlt('SS'); ?></th>
                <th><?php echo xlt('DOB'); ?></th>
                <th><?php echo xlt('Address'); ?></th>
            </tr>

            <?php
            while ($row = sqlFetchArray($res)) {
                if ($row['closeness'] == 0) {
                    continue;
                }

                $phone = $row['phone_biz'];
                if (empty($phone)) {
                    $phone = $row['phone_home'];
                }

                if (empty($phone)) {
                    $phone = $row['phone_cell'];
                }

                if (empty($phone)) {
                    $phone = $row['phone_contact'];
                }

                echo "  <tr class='oneresult'";
                echo " onclick=\"openPatient(" . attr_js($row['pid']) . ")\">\n";
                echo "   <td>" . text($row['lname'] . ", " . $row['fname']) . "</td>\n";
                echo "   <td>" . text($phone) . "</td>\n";
                echo "   <td>" . text($row['ss']) . "</td>\n";
                echo "   <td>" . text($row['DOB']) . "</td>\n";
                echo "   <td>" . text($row['street'] . ' ' . $row['postal_code']) . "</td>\n";
                echo "  </tr>\n";
            }
            ?>
        </table>
    </div>
    <?php
}
?>

<p>
    <input type='button' value='<?php echo xla('Add New Patient'); ?>' onclick="openPatient(0)"/>
    <input type='button' value='<?php echo xla('Cancel'); ?>' onclick="window.close()"/>
</p>

</form>
</center>
</body>
</html>
