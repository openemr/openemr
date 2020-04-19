<?php

/**
 * Encounter form report function.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__file__) . "/../../globals.php");

use OpenEMR\Common\Acl\AclMain;

function newpatient_report($pid, $encounter, $cols, $id)
{
    $res = sqlStatement("select e.*, f.name as facility_name from form_encounter as e join facility as f on f.id = e.facility_id where e.pid=? and e.id=?", array($pid,$id));
    print "<table><tr><td>\n";
    while ($result = sqlFetchArray($res)) {
        print "<span class=bold>" . xlt('Facility') . ": </span><span class=text>" . text($result["facility_name"]) . "</span><br />\n";
        if (empty($result['sensitivity']) || AclMain::aclCheckCore('sensitivities', $result['sensitivity'])) {
            print "<span class=bold>" . xlt('Reason') . ": </span><span class=text>" . nl2br(text($result["reason"])) . "</span><br />\n";
        }
    }

    print "</td></tr></table>\n";
}
