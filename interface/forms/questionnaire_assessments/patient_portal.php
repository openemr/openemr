<?php

/**
 * Questionnaire form
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Char Miller <charjmiller@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Char Miller <charjmiller@gmail.com>
 * @copyright Copyright (c) 2022 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once(__DIR__ . "/report.php");

$formid = $_GET['formid'] ?? null;
if (empty($formid)) {
    exit;
}

$formMetaData = sqlQuery("SELECT `date` FROM `forms` WHERE `form_id` = ? AND `formdir` = ?", [$formid, 'questionnaire_assessments']);

ob_start();
echo "<h2>" . xlt("Questionnaire Report") . "</h2>";
echo text(oeFormatShortDate($formMetaData['date'])) . "<br><br>";
questionnaire_assessments_report('', '', '', $formid);
echo json_encode(ob_get_clean());
