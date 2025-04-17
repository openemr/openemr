<?php

/**
 * physical_exam edit_diagnoses.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$line_id = $_REQUEST['lineid'];
$info_msg = "";

if ($issue && !AclMain::aclCheckCore('patients', 'med', '', 'write')) {
    die("Edit is not authorized!");
}
?>
<html>
<head>
<title><?php echo xlt('Edit Diagnoses for');?><?php echo text($line_id); ?></title>

<?php Header::setupHeader('opener'); ?>

</head>

<body class="body_top">
<?php
 // If we are saving, then save and close the window.
 //
if ($_POST['form_save']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $query = "DELETE FROM form_physical_exam_diagnoses WHERE line_id = ?";
    sqlStatement($query, array($line_id));

    $form_diagnoses = $_POST['form_diagnosis'];
    $form_orderings = $_POST['form_ordering'];
    foreach ($form_diagnoses as $i => $diagnosis) {
        if ($diagnosis) {
            $ordering = $form_orderings[$i];
            $query = "INSERT INTO form_physical_exam_diagnoses (
            line_id, ordering, diagnosis
            ) VALUES (
            ?, ?, ?
            )";
            sqlStatement($query, array($line_id, $ordering, $diagnosis));
        }
    }

  // Close this window and redisplay the updated encounter form.
  //
    echo "<script>\n";
    if ($info_msg) {
        echo " alert(" . js_escape($info_msg) . ");\n";
    }

    echo " window.close();\n";
  // echo " opener.location.reload();\n";
    echo " if (opener.refreshme) opener.refreshme();\n";
    echo "</script></body></html>\n";
    exit();
}

 $dres = sqlStatement(
     "SELECT * FROM form_physical_exam_diagnoses WHERE " .
     "line_id = ? ORDER BY ordering, diagnosis",
     array($line_id)
 );
    ?>
<form method='post' name='theform' action='edit_diagnoses.php?lineid=<?php echo attr_url($line_id); ?>'
 onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<center>

<table border='0' width='100%'>

 <tr>
  <td width='5%'><?php echo xlt('Order'); ?></td>
  <td width='95%'><?php echo xlt('Diagnosis'); ?></td>
 </tr>

<?php for ($i = 1; $drow = sqlFetchArray($dres); ++$i) { ?>
 <tr>
  <td><input type='text' size='3' maxlength='5' name='form_ordering[<?php echo attr($i); ?>]' value='<?php echo attr($i); ?>' /></td>
  <td><input type='text' size='20' maxlength='250' name='form_diagnosis[<?php echo attr($i); ?>]' value='<?php echo attr($drow['diagnosis']); ?>' style='width:100%' /></td>
 </tr>
<?php } ?>

<?php for ($j = 0; $j < 5; ++$j, ++$i) { ?>
 <tr>
  <td><input type='text' size='3' name='form_ordering[<?php echo attr($i); ?>]' value='<?php echo $i?>' /></td>
  <td><input type='text' size='20' name='form_diagnosis[<?php echo attr($i); ?>]' style='width:100%' /></td>
 </tr>
<?php } ?>

</table>

<p>
<input type='submit' name='form_save' value='<?php echo xla('Save'); ?>' />

&nbsp;
<input type='button' value='<?php echo xla('Cancel'); ?>' onclick='window.close()' />
</p>

</center>
</form>
</body>
</html>
