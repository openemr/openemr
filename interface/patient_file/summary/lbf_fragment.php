<?php

/**
 * Functions to globally validate and prepare data for sql database insertion.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2010-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Rod Roark <rod@sunsetsystems.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$lbf_form_id = $_GET['formname'];
?>
<div id='<?php echo attr($lbf_form_id); ?>' style='margin-top: 3px; margin-left: 10px; margin-right: 10px'>
    <br />
    <?php
    // Retrieve most recent instance of this form for this patient.
    $result = sqlQuery(
        "SELECT f.form_id, f.form_name, fe.date " .
        "FROM forms AS f, form_encounter AS fe WHERE " .
        "f.pid = ? AND f.formdir = ? AND " .
        "f.deleted = 0 AND " .
        "fe.pid = f.pid AND fe.encounter = f.encounter " .
        "ORDER BY fe.date DESC, f.encounter DESC, f.date DESC " .
        "LIMIT 1",
        array($pid, $lbf_form_id)
    );

    if (!$result) { //If there are none
        ?>
        <span class='text'> <?php echo xlt("None have been documented"); ?>
  </span>
    <?php } else { ?>
        <span class='text'><b>
        <?php
        echo text(xl('Most recent from') . ": " .
        oeFormatShortDate(substr($result['date'], 0, 10)));
        ?>
  </b></span>
        <br />
        <br />
        <?php
        include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");
        lbf_report('', '', 2, $result['form_id'], $lbf_form_id);
        ?>
        <span class='text'>
  <br />
  <a href='../encounter/trend_form.php?formname=<?php echo attr_url($lbf_form_id); ?>' onclick='top.restoreSession()'>
        <?php echo xlt('Click here to view and graph'); ?>
  </a>
  </span>
    <?php } ?>
    <br />
    <br />
</div>
