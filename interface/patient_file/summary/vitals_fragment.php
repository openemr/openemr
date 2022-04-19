<?php

/**
 * vitals_fragment.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 *  @author   Daniel Pflieger <daniel@growlingflea.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Daniel Pflieger <daniel@growlingflea.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once($GLOBALS['fileroot'] . "/library/patient.inc");
include_once($GLOBALS['incdir'] . "/forms/vitals/report.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$result = sqlQuery("SELECT FORM_VITALS.*, pd.DOB FROM form_vitals AS FORM_VITALS
    JOIN forms AS FORMS ON FORM_VITALS.id = FORMS.form_id
    JOIN patient_data pd on pd.pid = FORM_VITALS.pid
    WHERE FORM_VITALS.pid= ? AND FORMS.deleted != '1' ORDER BY FORM_VITALS.date DESC", array($pid));

$age = getPatientAgeYMD($result['DOB'], $result['date'])
?>

<div>
    <?php
    if (!$result) {
        echo "No vitals taken for this patient";
    } else {
        $report = vitals_report('', '', 2, $result['id'], false);
        echo $report;
    }
    ?>
</div>
<span class='text'>
    <br>
    <a href='../encounter/trend_form.php?formname=vitals' onclick='top.restoreSession()'><?php echo xlt('Click here to view and graph all vitals.');?></a>

</span>



