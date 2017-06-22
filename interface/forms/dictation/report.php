<?php
/** 
 *  Dictation report for display 
 * 
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @copyright Copyright (c) 2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * 
 */

include_once(dirname(__FILE__).'/../../globals.php');
include_once($GLOBALS["srcdir"]."/api.inc");


/**
 *  Retrieve data from the dictation table
 * 
 * @param int $pid
 * @param int $encounter
 * @param int $cols
 * @param int $id
 * 
 */

function dictation_report( $pid, $encounter, $cols, $id) {
  $count = 0;
  $data = formFetch("form_dictation", $id);
  if ($data) {
    print "<table><tr>";
    foreach($data as $key => $value) {
      if ($key == "id" || $key == "pid" || $key == "user" ||
        $key == "groupname" || $key == "authorized" || $key == "activity" ||
        $key == "date" || $value == "" || $value == "0000-00-00 00:00:00")
      {
        continue;
      }
      if ($value == "on") {
        $value = "yes";
      }
      $key=ucwords(str_replace("_"," ",$key));
      $config = HTMLPurifier_Config::createDefault();
      $purifier = new HTMLPurifier($config);
      $clean_html = $purifier->purify($value);
      print "<td><span class='bold'>" . xlt($key) . ": </span><span class='text'>" .
             $clean_html . "</span></td>";
      $count++;
      if ($count == $cols) {
        $count = 0;
        print "</tr><tr>\n";
      }
    }
  }
  print "</tr></table>";
}
