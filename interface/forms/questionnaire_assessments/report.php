<?php

require_once(dirname(__FILE__) . '/../../globals.php');
require_once($GLOBALS["srcdir"] . "/api.inc");

function questionnaire_assessments_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $data = formFetch("form_questionnaire_assessments", $id);
    if ($data) {
    }
}
