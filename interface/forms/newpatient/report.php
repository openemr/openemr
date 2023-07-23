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
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\UserService;

function newpatient_report($pid, $encounter, $cols, $id)
{
    $res = sqlStatement("select e.*, f.name as facility_name from form_encounter as e join facility as f on f.id = e.facility_id where e.pid=? and e.id=?", array($pid,$id));
    print "<table><tr><td>\n";
    while ($result = sqlFetchArray($res)) {
        $userService = new UserService();
        $provider = $userService->getUser($result["provider_id"]);
        $referringProvider = $userService->getUser($result["referring_provider_id"]);
        $calendar_category = (new AppointmentService())->getOneCalendarCategory($result['pc_catid']);
        print "<span class=bold>" . xlt('Facility') . ": </span><span class=text>" . text($result["facility_name"]) . "</span><br />\n";
        if (empty($result['sensitivity']) || AclMain::aclCheckCore('sensitivities', $result['sensitivity'])) {
            print "<span class=bold>" . xlt('Category') . ": </span><span class=text>" . text($calendar_category[0]['pc_catname']) . "</span><br />\n";
            print "<span class=bold>" . xlt('Reason') . ": </span><span class=text>" . nl2br(text($result["reason"])) . "</span><br />\n";
            print "<span>" . xlt('Provider') . ": </span><span class=text>" . text($provider['lname'] . ", " . $provider['fname']) . "</span><br />\n";
            print "<span>" . xlt('Referring Provider') . ": </span><span class=text>" . text(($referringProvider['lname'] ?? '') . ", " . ($referringProvider['fname'] ?? '')) . "</span><br />\n";
            print "<span>" . xlt('POS Code') . ": </span><span class=text>" . text(sprintf('%02d', trim($result['pos_code'] ?? ''))) . "</span><br />\n";
        }
    }

    print "</td></tr></table>\n";
}
