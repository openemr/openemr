<?php

/**
 * Encounter form report function.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @author    Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @copyright Copyright (c) 2016 Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__file__) . "/../../globals.php");
require_once("$srcdir/group.inc.php");

use OpenEMR\Common\Acl\AclMain;

function newGroupEncounter_report($group_id, $encounter, $cols, $id)
{
    $res = sqlStatement("select * from form_groups_encounter where group_id=? and id=?", array($group_id,$id));
    print "<table><tr><td>\n";
    while ($result = sqlFetchArray($res)) {
        print "<span class='font-weight-bold'>" . xlt('Facility') . ": </span><span class='text'>" . text($result["facility"]) . "</span><br />\n";
        if (AclMain::aclCheckCore('sensitivities', $result['sensitivity'])) {
            print "<span class='font-weight-bold'>" . xlt('Reason') . ": </span><span class='text'>" . nl2br(text($result["reason"])) . "</span><br />\n";
            $counselors = '';
            foreach (explode(',', $result["counselors"]) as $userId) {
                $counselors .= getUserNameById($userId) . ', ';
            }

            $counselors = rtrim($counselors, ", ");
            print "<span class='font-weight-bold'>" . xlt('Counselors') . ": </span><span class='text'>" . nl2br(text($counselors)) . "</span><br />\n";
        }
    }

    print "</td></tr></table>\n";
}
