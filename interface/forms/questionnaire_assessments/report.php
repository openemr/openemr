<?php

/**
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once($GLOBALS["srcdir"] . "/api.inc.php");

use OpenEMR\Services\QuestionnaireResponseService;
use OpenEMR\Services\QuestionnaireService;

/**
 * @throws Exception
 */
function questionnaire_assessments_report($pid, $encounter, $cols, $id)
{
    $form = formFetch("form_questionnaire_assessments", $id);
    if (!$form) {
        echo xlt('Nothing to report.');
        return;
    }
    $responseService = new QuestionnaireResponseService();
    try {
        $qr = json_decode($form['questionnaire_response'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            die(xlt('Nothing to report. Parse error.'));
        }
        $html = $responseService->buildQuestionnaireResponseHtml($qr);
        echo $html;
    } catch (Exception $e) {
        echo xlt("Error") . " " . text($e->getMessage());
    }
}
