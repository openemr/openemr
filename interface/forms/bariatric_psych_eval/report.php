<?php

/**
 * Bariatric Psych Eval report.php
 * display a form's values in the encounter summary page
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Barbara Rix <admin@starbirdrisingwellness.com>
 * @copyright Copyright (c) 2026 Barbara Rix <admin@starbirdrisingwellness.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\BC\Utilities;

require_once(__DIR__ . '/../../../library/api.inc.php');
require_once(__DIR__ . '/bariatric_psych_eval.inc.php');

function bariatric_psych_eval_report($pid, $encounter, $cols, $id): void
{
    global $str_sections, $str_yes, $str_no, $str_substance_use_options, $str_recommendation_options;

    $data = formFetch("form_bariatric_psych_eval", $id);
    if (!$data) {
        return;
    }

    // flatten [fieldName => [label, type]] across all sections for lookup
    $fieldMeta = [];
    foreach ($str_sections as $fields) {
        foreach ($fields as $fieldName => $field) {
            $fieldMeta[$fieldName] = $field;
        }
    }

    print "<table><tr>";
    $count = 0;
    foreach ($data as $key => $value) {
        if (in_array($key, ["id", "pid", "user", "groupname", "authorized", "activity", "date"]) || Utilities::isDateEmpty($value)) {
            continue;
        }
        if (!isset($fieldMeta[$key]) || $value === '' || $value === null) {
            continue;
        }
        [$label, $type] = $fieldMeta[$key];
        $displayValue = match ($type) {
            'yesno' => $value === 'yes' ? $str_yes : ($value === 'no' ? $str_no : $value),
            'select_substance' => $str_substance_use_options[$value] ?? $value,
            'select_recommendation' => $str_recommendation_options[$value] ?? $value,
            default => $value,
        };
        print "<td><span class=bold>" . text($label) . ": </span><span class=text>" . text($displayValue) . "</span></td>";
        $count++;
        if ($count == $cols) {
            $count = 0;
            print "</tr><tr>\n";
        }
    }
    print "</tr></table>";
}
