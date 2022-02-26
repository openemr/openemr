<?php

/*
 * Work/School Note Form report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Nikolai Vitsyn
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Daniel Pflieger <daniel@growlingflea.com>
 * @copyright Copyright (c) 2004-2005 Nikolai Vitsyn
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * $copyright Copyright (c) 2022 Daniel Pflieger <daniel@growlingflea.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */



require_once(dirname(__FILE__) . '/../../globals.php');
require_once($GLOBALS["srcdir"] . "/api.inc");

function note_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $data = formFetch("form_note", $id);
    if ($data) {
        print "<table><tr>";
        foreach ($data as $key => $value) {
            if (
                $key == "id" ||
                $key == "pid" ||
                $key == "user" ||
                $key == "groupname" ||
                $key == "authorized" ||
                $key == "activity" ||
                $key == "date" ||
                $value == "" ||
                $value == "0000-00-00 00:00:00"
            ) {
                continue;
            }

            if ($value == "on") {
                $value = "yes";
            }

            $key = ucwords(str_replace("_", " ", $key));
            print("<tr>\n");
            print("<tr>\n");
            if ($key == "Note Type") {            //*** oe-ca-pediatric-printout-menu ADD
                print "<td><span class=bold>" . xl($key) . ": </span><span class=text>" . xl($value) . "</span></td>";
                if ($value == "REFERRAL") {
                    $title = "Referral";
                } elseif ($value == "PROVIDER COMMUNICATION") {
                    $title = "Provider Communication";
                }
                if (isset($title)) {
                    print "<script>";
                    print "$('.work_note_marker" . $id . "').parent().children('h1').text('" . $title . "');";
                    print "</script>";
                }
            } elseif ($key == 'Message') {
                $value = preg_replace('/\v+|\\\[rn]/', "<br>", $value);
                print "<td><span class=bold>" . xlt($key) . ": </span><span class=text>" . "<br>" . $value . "</span></td>";
            } elseif ($key == 'Doctor' || $key == "Date Of Signature") { //***SANTI ADD highlight doctors signature
                print "<td><span class=bold><mark>" . xlt($key) . ":</mark> </span><span class=text><mark>" . text($value) . "</mark></span></td>";
            } else {
                print "<td><span class=bold>" . xlt($key) . ": </span><span class=text>" . text($value) . "</span></td>";
            }

            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
            //*** oe-ca-pediatric-printout-menu ADD
            if ($key == "Date Of Signature" && empty($_POST)) {
                print "<tr><td><br/></td></tr><tr><td><br/></td></tr>";
                print "<tr><td><span class=text>" .  xlt('Signature') . ": _______________________________</span></td></tr>";
            }
            //*** oe-ca-pediatric-printout-menu ADD END
        }
    }

    print "</tr></table>";
}
