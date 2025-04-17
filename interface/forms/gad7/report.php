<?php

/**
 * gad-7 report.php
 * display a form's values in the encounter summary page
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ruth Moulton <moulton ruth@muswell.me.uk>
 * @copyright Copyright (c) 2021 ruth moulton <ruth@muswell.me.uk>
 *
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("gad7.inc.php");

$gad7_total = 0;
$pdf_as_string = '';
$data;
$exp = '';

$str_difficulty_values = [0 => xl('Not at all') . ' (0)',1 => xl('Somewhat difficult') . ' (1)', 2 => xl('Very difficult') . ' (2)', 3 => xl('Extremely difficult') . ' (3)', 'undef' => xl('not answered')];

function gad7_report($pid, $encounter, $cols, $id)
{
    global $str_test, $str_nervous,$gad7_total, $pdf_as_string, $str_values,$str_difficulty_values, $data, $exp, $file_name, $str_generate_pdf;

    $count = 0;
    $value = 0;
    $gad7_total = 0; /* initialise back to zero */

    $str_issues = ["nervous_score" => xl('Feeling nervous'),"control_worry_score" => xl('Not controlling worry'),"worry_score" => xl('Worrying'),"relax_score" => xl('Trouble relaxing'),"restless_score" => xl('Being restless'),"irritable_score" => xl('Being irritable'),"fear_score" => xl('Feeling afraid'), "difficulty" => xl('Difficulty working etc.'),"total" => xl('Total GAD-7 score')];

    $str_score_analysis = [0 => xl('No anxiety disorder'), 5 => xl('Mild anxiety disorder'), 15 => xl('Severe anxiety disorder')];

    $data = formFetch("form_gad7", $id);

    if ($data) {
        print "<table><tr>";
        foreach ($data as $key => $value) {
// include scores_array and total for backward compatibility
            if ($key == "id" || $key == "pid" || $key == "user" || $key == "groupname" || $key == "authorized" || $key ==  "activity" || $key == "date" || $value == "" || $key == "scores_array" || $key == "total" || $value == "0000-00-00 00:00:00") {
                continue;
            }
            if ($key == "difficulty") {
                print "<td><span class=bold>" . text($str_issues[$key]) . ": </span><span class=text>" . text($str_difficulty_values [$value]) . "</span></td>";
            } else {
                print "<td><span class=bold>" . text($str_issues[$key]) . ": </span><span class=text>" . text($str_values [$value]) . "</span></td>";
                if (is_numeric($value)) {
                    $gad7_total += $value;
                }
            }
            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
        // print the total
        switch (intdiv($gad7_total, 5)) {
            case 0:
                $exp = $str_score_analysis[0];
                break;
            case 1:
            case 2:
                $exp = $str_score_analysis[5];
                break;
            default:
                $exp = $str_score_analysis[15];
        }

          print "<td><span class=bold>" . text($str_issues["total"]) . ": </span><span class=text>" . text($gad7_total) . " - " . text($exp) . "</span></td>";
    }


    print "</tr></table>";
}
