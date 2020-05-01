<?php

/*
 * report.php displays the misc_billing_form in the encounter view
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Terry Hill <terry@lilysystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2007 Bo Huynh
 * @copyright Copyright (C) 2016 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (C) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once(dirname(__FILE__) . '/../../globals.php');
require_once($GLOBALS["srcdir"] . "/api.inc");
require_once("date_qualifier_options.php");

function misc_billing_options_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $data = formFetch("form_misc_billing_options", $id);
    if ($data) {
        print "<table><tr>";
        foreach ($data as $key => $value) {
            if (
                $key == "id" || $key == "pid" || $key == "user" || $key == "groupname" ||
                $key == "authorized" || $key == "activity" || $key == "date" || $value == "" ||
                $value == "0" || $value == "0000-00-00 00:00:00" || $value == "0000-00-00" ||
                ($key == "box_14_date_qual" && ($data['onset_date'] == 0)) ||
                ($key == "box_15_date_qual" && ($data['date_initial_treatment'] == 0))
            ) {
                continue;
            }

            if (($key === 'box_14_date_qual') || $key === 'box_15_date_qual') {
                $value = qual_id_to_description($key, $value);
            }

            if ($key === 'provider_qualifier_code') {
                $pqe = $data['provider_qualifier_code'];
                if (!empty($pqe)) {
                    switch ($pqe) {
                        case ($pqe == "DN"):
                            $value = "Referring";
                            break;
                        case ($pqe == "DK"):
                            $value = "Ordering";
                            break;
                        case ($pqe == "DQ"):
                            $value = "Supervising";
                            break;
                    }

                    $key = 'Box 17 Qualifier';
                }
            }

            if ($key === 'provider_id') {
                $trow = sqlQuery("SELECT id, lname, fname FROM users WHERE " .
                         "id = ? ", array($value));
                $value = $trow['fname'] . ' ' . $trow['lname'];
                $key = 'Box 17 Provider';
            }

            if ($value == "1") {
                $value = "Yes";
            }

            $key = ucwords(str_replace("_", " ", $key));
            print "<td><span class=bold>" . xlt($key) . ": </span><span class=text>" . text($value) . "</span></td>";
            $count++;

            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
    }

    print "</tr></table>";
}
