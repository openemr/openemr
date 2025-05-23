<?php

/**
 * PHQ-9 report.php
 * display a form's values in the encounter summary page
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ruth Moulton <moulton ruth@muswell.me.uk>
 * @copyright Copyright (c) 2021 ruth moulton <ruth@muswell.me.uk>
 *
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . '/../../../library/api.inc.php');


function phq9_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $phq9_total = 0;
    $value = 0;

    $str_values = [0 => xl('Not at all') . ' (0)',1 => xl('Several days') . ' (1)',2 => xl('More than half of days') . ' (2)',3 => xl('Nearly every day') . ' (3)'];

    $str_difficulty_values = [0 => xl('Not at all') . ' (0)',1 => xl('Somewhat difficult') . ' (1)', 2 => xl('Very difficult') . ' (2)', 3 => xl('Extremely difficult') . ' (3)', 'undef' => xl('not answered')];

    $str_issues = ["interest_score" => xl('Loss of Interest'),"hopeless_score" => xl('Feeling Hopeless'),"sleep_score" => xl('Sleep Disturbance'),"fatigue_score" => xl('Fatigue'),"appetite_score" => xl('Change in Appetite'),"failure_score" => xl('Feel like a Falure'),"focus_score" => xl('Poor Focus'),"psychomotor_score" => xl('Psychomotor Retardation'),"suicide_score" => xl('Suicidal Thoughts'),"difficulty" => xl('Difficulty working etc.'),"total" => xl('Total PHQ-9 score')];

    $str_score_analysis = [0 => xl('No depressive disorder'), 5 => xl('Mild Depression'), 10 => xl('Moderate Depression'), 15 => xl('Moderately Severe Depression'), 20 => xl('Severe Depression'), 25 => xl('Severe Depression')];

    $data = formFetch("form_phq9", $id);

    if ($data) {
        print "<table><tr>";
        foreach ($data as $key => $value) {
// include scores_array and total for backward compatibility
            if ($key == "id" || $key == "pid" || $key == "user" || $key == "groupname" || $key == "authorized" || $key ==  "activity" || $key == "date" || $value == "" || $key == "scores_array" || $key == "total" || $value == "0000-00-00 00:00:00") {
                continue;
            }
            if ($key == "difficulty") {
                print "<td><span class=bold>" . text($str_issues[$key]) . ": </span><span class=text>" . text($str_difficulty_values[$value]) . "</span></td>";
            } else {
                print "<td><span class=bold>" . text($str_issues[$key]) . ": </span><span class=text>" . text($str_values[$value]) . "</span></td>";
                if (is_numeric($value)) {
                    $phq9_total += $value;
                }
            }
            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
        // print the total
        switch (intdiv($phq9_total, 5)) {
            case 0:
                $exp = $str_score_analysis[0];
                break;
            case 1:
                $exp = $str_score_analysis[5];
                break;
            case 2:
                $exp = $str_score_analysis[10];
                break;
            case 3:
                $exp = $str_score_analysis[15];
                break;
            case 4:
                $exp = $str_score_analysis[20];
                break;
            default:
                $exp = $str_score_analysis[20];
        }

          print "<td><span class=bold>" . text($str_issues["total"]) . ": </span><span class=text>" . text($phq9_total) . " - " . text($exp) . "</span></td>";
    }

    print "</tr></table>";
}
