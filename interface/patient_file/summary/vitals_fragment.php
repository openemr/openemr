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
use OpenEMR\Core\Header;

Header::setupAssets(['dygraphs', 'jquery']);

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$result = sqlQuery("SELECT FORM_VITALS.*, pd.DOB FROM form_vitals AS FORM_VITALS
    JOIN forms AS FORMS ON FORM_VITALS.id = FORMS.form_id
    JOIN patient_data pd on pd.pid = FORM_VITALS.pid
    WHERE FORM_VITALS.pid= ? AND FORMS.deleted != '1' ORDER BY FORM_VITALS.date DESC", array($pid));

$age = getPatientAgeYMD($result['DOB'], $result['date'])
?>

<style>
    #header_frag {
        display: grid;
        grid-template-columns:120px 120px;
        column-gap: 10px;
        row-gap: 5px;
        margin-top: 20px;
    }

    #measurement_frag {
        display: grid;
        grid-template-columns:120px 150px 100px 100px;
        column-gap: 10px;
        row-gap: 5px;
        margin-top: 3px;
        margin-left: 10px;
        margin-right: 10px;


    }

    .measurement-2_frag {
        display: grid;
        grid-template-columns:120px 150px 120px 80px;
        column-gap: 10px;
        row-gap: 5px;
        margin-left: 10px;
        margin-right: 10px;
        margin-top: 20px;

    }

    .label {
        test-align:left;
        grid-template-columns:120px repeat(2, 90px) 100px;
        grid-row:1;
        font-weight: bold;
        font-size:11pt;

    }

    .label-1 {
        test-align:left;
        font-weight: bold;
        font-size:10pt;

    }

    #label-wht4height {
        test-align:left;
        font-weight: bold;
        grid-column-start:1;
        grid-column-end:3;
    }

    .data-1 {
        test-align:left;
        font-size:10pt;


    }
    .header-title {
        grid-column: 1;
        text-align:left;
        font-weight: bold;


    }

    .header-data {
        grid-column: 2;
        text-align:left;

    }

    .vf {
        display: grid;
        grid-template-columns: repeat(6, 140px);
        grid-auto-rows: minmax(100px, auto);
    }

    .note {
        grid-column: 2/ 6;
        grid-row: 1 / 4;

    }

</style>


<div>
    <?php
    if (!$result) {
        echo "No vitals taken for this patient";
    }

    $report = vitals_report('', '', 2, $result['id'], false);

    echo $report;

    ?>
<span class='text'>



<!--  <button id="show_growth_chart" onclick='showGrowthChart()'>--><?php //echo xlt('Show Growth Chart')?><!--</button>-->
  <br /><br /><br />
  <button  onclick='top.restoreSession(); location.href="../encounter/trend_form.php?formname=vitals";'><?php echo htmlspecialchars(xl('Click here to view and graph all vitals.'), ENT_NOQUOTES);?></button>
  </span>




