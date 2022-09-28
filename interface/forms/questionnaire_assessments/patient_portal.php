<?php

/**
 * Questionnaire form
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once(__DIR__ . "/report.php");

$formid = $_GET['formid'] ?? null;
if (empty($formid)) {
    exit;
}

$formMetaData = sqlQuery("SELECT `date`, `form_name` FROM `forms` WHERE `form_id` = ? AND `formdir` = ?", [$formid, 'questionnaire_assessments']);

ob_start();
echo "<h3>" . text($formMetaData['form_name']) . "</h3>";
echo xlt("Dated") . ' ' . text(oeFormatShortDate($formMetaData['date'])) . "<br><br>";
try {
    questionnaire_assessments_report('', '', '', $formid);
} catch (Exception $e) {
    echo xlt("An error was encountered.") . "<br>\n" . text($e->getMessage());
}
echo json_encode(ob_get_clean());
