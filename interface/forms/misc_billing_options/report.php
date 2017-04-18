<?php
/*
 * report.php used by the misc_billing_form
 *
 * This program is used by the misc_billing_form
 *
 * Copyright (C) 2007 Bo Huynh
 * Copyright (C) 2016 Terry Hill <terry@lillysystems.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-license.php.
 *
 * @package OpenEMR
 * @author Terry Hill <terry@lilysystems.com>
 * @author Brady Miller <brady.g.miller@gmail.com>
 * @link http://www.open-emr.org
 */
include_once(dirname(__FILE__).'/../../globals.php');
include_once($GLOBALS["srcdir"]."/api.inc");
require_once("date_qualifier_options.php");
function misc_billing_options_report( $pid, $encounter, $cols, $id) {
    $count = 0;
    $data = formFetch("form_misc_billing_options", $id);
    if ($data) {
    print "<table><tr>";
        foreach($data as $key => $value) {
            if ($key == "id" || $key == "pid" || $key == "user" || $key == "groupname" || $key == "authorized" || $key == "activity" || $key == "date" || $value == "" || $value == "0" || $value == "0000-00-00 00:00:00" || $value =="0000-00-00") {
                continue;
            }
            if ($value == "1") {
                $value = "yes";
            }
            if(($key==='box_14_date_qual')||$key==='box_15_date_qual')
            {
                $value=text(qual_id_to_description($key,$value));
            }
            if($key==='provider_id')
            {

                $trow = sqlQuery("SELECT id, lname, fname FROM users WHERE ".
                         "id = ? ",array($value));
                $value=$trow['fname'] . ' ' . $trow['lname'];

            }
            $key=ucwords(str_replace("_"," ",$key));
            print "<td><span class=bold>$key: </span><span class=text>" . text($value) . "</span></td>";
            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
    }
    print "</tr></table>";
}
?>
