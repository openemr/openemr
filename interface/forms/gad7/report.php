<?php
/**
 * gad-7 report.php
 * display a form's values in the encounter summary page
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    ruth moulton
 *
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once(dirname(__FILE__).'/../../globals.php');
require_once($GLOBALS["srcdir"]."/api.inc");


function gad7_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $gad7_total = 0;

    $str_values = [0=>'Not at all (0)',1=>'Several days (1)',2=>'More than half of days (2)',3=>'Nearly every day (3)'];

    $str_difficulty_values = [0=>'Not at all (0)',1=>"Somewhat difficult (1)", 2=>"Very difficult (2)", 3=>"Extremely difficult (3)", 'undef'=> 'not answered'];

    $str_issues = ["nervous_score"=>"Feeling nervous","control_worry_score"=>"Not controlling worry","worry_score"=>"Worrying","relax_score"=>"Trouble relaxing","restless_score"=>"Being restless","irritable_score"=>"Being irritable","fear_score"=>"Feeling afraid", "difficulty"=>"Difficulty working etc.","total"=>"Total GAD-7 score"];

    $str_score_analysis = [0=>"No anxiety disorder", 5=>"Mild anxiety disorder", 15=>"Severe anxiety disorder"];

    $data = formFetch("form_gad7", $id);

    if ($data) {
        print "<table><tr>";
        foreach ($data as $key => $value) {
// include scores_array and total for backward compatibility
            if ($key == "id" || $key == "pid" || $key == "user" || $key == "groupname" || $key == "authorized" || $key == "activity" || $key == "date" || $value == "" ||
            $key == "scores_array" || $key =="total" || $value == "0000-00-00 00:00:00")
            {
                continue;
            }
//         $key=ucwords(str_replace("_", " ", $key));
//          print "<td><span class=bold>" . xlt($key) . ": </span><span class=text>" . text($value) . "</span></td>";

          if ($key == "difficulty"){

            print "<td><span class=bold>" . xlt($str_issues[$key]) . ": </span><span class=text>" . text($str_difficulty_values [$value]) . "</span></td>";
            }
         else {
                print "<td><span class=bold>" . xlt($str_issues[$key]) . ": </span><span class=text>" . text($str_values [$value]) . "</span></td>";

                $gad7_total += $value;
                }

            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
        // print the total
        switch(intdiv($gad7_total,5)){
       case 0:
            $exp = $str_score_analysis [0];
            break;
       case 1:
       case 2:
            $exp = $str_score_analysis [5];
            break;

       default:
              $exp = $str_score_analysis [15];
        }

          print "<td><span class=bold>" . xlt($str_issues["total"]) . ": </span><span class=text>" . text($gad7_total) ." - ".$exp."</span></td>";
    }

    print "</tr></table>";
}
