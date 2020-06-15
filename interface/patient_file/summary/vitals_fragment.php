<?php

/**
 * vitals_fragment.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

?>
<div id='vitals' style='margin-top: 3px; margin-left: 10px; margin-right: 10px'><!--outer div-->
<br />
<?php
//retrieve most recent set of vitals.
$result = sqlQuery("SELECT FORM_VITALS.date, FORM_VITALS.id FROM form_vitals AS FORM_VITALS LEFT JOIN forms AS FORMS ON FORM_VITALS.id = FORMS.form_id WHERE FORM_VITALS.pid=? AND FORMS.deleted != '1' ORDER BY FORM_VITALS.date DESC", array($pid));

if (!$result) { //If there are no disclosures recorded
    ?>
  <span class='text'> <?php echo xlt("No vitals have been documented.");
    ?>
  </span>
    <?php
} else {
    ?>
  <span class='text'><b>
    <?php echo xlt('Most recent vitals from:') . " " . text($result['date']); ?>
  </b></span>
  <br />
  <br />
    <?php include_once($GLOBALS['incdir'] . "/forms/vitals/report.php");
    vitals_report('', '', 2, $result['id']);
    ?>  <span class='text'>
  <br />
  <a href='../encounter/trend_form.php?formname=vitals' onclick='top.restoreSession()'><?php echo xlt('Click here to view and graph all vitals.');?></a>
  </span><?php
} ?>
<br />
<br />
</div>
