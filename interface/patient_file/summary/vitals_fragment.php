<?php

/**
 * vitals_fragment.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

?>
<div id='vitals''><!--outer div-->
<?php
//retrieve most recent set of vitals.
$result = sqlQuery("SELECT FORM_VITALS.date, FORM_VITALS.id FROM form_vitals AS FORM_VITALS LEFT JOIN forms AS FORMS ON FORM_VITALS.id = FORMS.form_id WHERE FORM_VITALS.pid=? AND FORMS.deleted != '1' ORDER BY FORM_VITALS.date DESC", [$pid]);

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
    <?php include_once(OEGlobalsBag::getInstance()->getKernel()->getIncludeRoot() . "/forms/vitals/report.php");
    vitals_report('', '', 1, $result['id']);
    ?>  <span class='text'>
  <br />
  <a href='../encounter/trend_form.php?formname=vitals' onclick='top.restoreSession()'><?php echo xlt('Click here to view and graph all vitals.');?></a>
  </span><?php
} ?>
</div>
