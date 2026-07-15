<?php

/**
 * physical_exam edit_diagnoses.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2006 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Forms\PhysicalExam\DiagnosisHelper;

$line_id = DiagnosisHelper::normalizeLineId($_REQUEST['lineid'] ?? null);
if ($line_id === null) {
    http_response_code(400);
    die(xlt('Invalid physical exam diagnosis line id'));
}

$info_msg = "";

if (!AclMain::aclCheckCore('patients', 'med', '', 'write')) {
    AccessDeniedHelper::denyWithTemplate(
        'Editing physical exam diagnoses is not authorized',
        xl('Access Denied'),
    );
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();
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
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

    $form_diagnoses = $_POST['form_diagnosis'] ?? [];
    $form_orderings = $_POST['form_ordering'] ?? [];
    DiagnosisHelper::save(
        $line_id,
        is_array($form_diagnoses) ? $form_diagnoses : [],
        is_array($form_orderings) ? $form_orderings : []
    );

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

$existingDiagnosisRows = [];
$dres = sqlStatement(
    "SELECT ordering, diagnosis FROM form_physical_exam_diagnoses WHERE " .
    "line_id = ? ORDER BY ordering, diagnosis",
    [$line_id]
);
while ($drow = sqlFetchArray($dres)) {
    /** @var array{ordering: int|string|null, diagnosis: string|null} $drow */
    $existingDiagnosisRows[] = $drow;
}

/** @var list<array{ordering: int|string|null, diagnosis: string|null}> $diagnosisRows */
$diagnosisRows = array_pad(
    $existingDiagnosisRows,
    count($existingDiagnosisRows) + 5,
    ['ordering' => null, 'diagnosis' => '']
);
?>
<form method='post' name='theform' action='edit_diagnoses.php?lineid=<?php echo attr_url($line_id); ?>'
 onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>" />

<center>

<table border='0' width='100%'>

 <tr>
  <td width='5%'><?php echo xlt('Order'); ?></td>
  <td width='95%'><?php echo xlt('Diagnosis'); ?></td>
 </tr>

<?php foreach ($diagnosisRows as $idx => $drow) {
    $i = $idx + 1;
    // Synthetic blank rows use null ordering so they default to the row number.
    $ordering = (string) ($drow['ordering'] ?? $i);
    $diagnosis = (string) ($drow['diagnosis'] ?? '');
    ?>
 <tr>
  <td><input type='text' size='3' maxlength='5' name='form_ordering[<?php echo $i; ?>]' value='<?php echo attr($ordering); ?>' /></td>
  <td><input type='text' size='20' maxlength='250' name='form_diagnosis[<?php echo $i; ?>]' value='<?php echo attr($diagnosis); ?>' style='width:100%' /></td>
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
