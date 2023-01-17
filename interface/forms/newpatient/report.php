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
// OEMR - A
require_once($GLOBALS['srcdir']."/wmt-v2/wmtstandard.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Services\AppointmentService;
use OpenEMR\Services\UserService;
use OpenEMR\Billing\BillingUtilities;

// OEMR - Added param
function newpatient_report($pid, $encounter, $cols, $id, $suppress_reason = FALSE)
{
    $res = sqlStatement("select e.*, f.name as facility_name from form_encounter as e join facility as f on f.id = e.facility_id where e.pid=? and e.id=?", array($pid,$id));
    // OEMR - A
    $billing = BillingUtilities::getBillingByEncounter($pid, $encounter, "*");
    print "<table><tr><td>\n";
    while ($result = sqlFetchArray($res)) {
        /* OEMR - A */
        $desc = 'No Case Attached';
        $case_link = sqlQuery('SELECT * FROM case_appointment_link WHERE encounter = ?', array($encounter));
        if(!isset($case_link{'pc_eid'})) $case_link{'pc_eid'} = '';
        if(!isset($case_link{'enc_case'})) $case_link{'enc_case'} = '';
        if($case_link{'pc_eid'}) {
            $sql = 'SELECT oe.pc_case, c.*, c.id AS case_id, users.* FROM ' .
            'openemr_postcalendar_events AS oe LEFT JOIN form_cases AS c ' .
            'ON (oe.pc_case = c.id) LEFT JOIN users ON (c.employer = users.id) ' .
            'WHERE oe.pc_eid = ?';
            $case = sqlQuery($sql, array($case_link{'pc_eid'}));
        } else if($case_link{'enc_case'}) {
            $sql = 'SELECT c.*, c.id AS case_id, users.* FROM ' .
            'form_cases AS c LEFT JOIN users ON (c.employer = users.id) ' .
            'WHERE c.id = ?';
            $case = sqlQuery($sql, array($case_link{'enc_case'}));
        }
        if(!isset($case{'case_id'})) $case{'case_id'} = '';
        $result{'form_case'} = $case{'case_id'};
        if($case{'case_id'}) $case_desc = $case{'case_id'};
        if($case{'case_id'}) $desc = $case{'case_description'};
        if(!$suppress_reason) $case_desc .= ' - '. $desc;
        //  . ' dated  ' . oeFormatShortDate($case{'form_dt'}) .  ' - [ ' . $case{'case_description'} . ' ]';
        /* End */

        $userService = new UserService();
        $provider = $userService->getUser($result["provider_id"]);
        $referringProvider = $userService->getUser($result["referring_provider_id"]);
        $calendar_category = (new AppointmentService())->getOneCalendarCategory($result['pc_catid']);
        print "<span class=bold>" . xlt('Facility') . ": </span><span class=text>" . text($result["facility_name"]) . "</span><br />\n";
        if (empty($result['sensitivity']) || AclMain::aclCheckCore('sensitivities', $result['sensitivity'])) {
            print "<span class=bold>" . xlt('Category') . ": </span><span class=text>" . text($calendar_category[0]['pc_catname']) . "</span><br />\n";
            print "<span class=bold>" . xlt('Reason') . ": </span><span class=text>" . nl2br(text($result["reason"])) . "</span><br />\n";
            print "<span class=bold>" . xlt('Provider') . ": </span><span class=text>" . text($provider['lname'] . ", " . $provider['fname']) . "</span><br />\n";
            print "<span class=bold>" . xlt('Referring Provider') . ": </span><span class=text>" . text(($referringProvider['lname'] ?? '') . ", " . ($referringProvider['fname'] ?? '')) . "</span><br />\n";
            print "<span class=bold>" . xlt('POS Code') . ": </span><span class=text>" . text(sprintf('%02d', trim($result['pos_code'] ?? ''))) . "</span><br />\n";
        }

        /* OEMR - A */
        if($result{'supervisor_id'}) {
            print "<span class=bold>" . xlt('Supervisor') . ": </span><span class=text>" . text(UserNameFromID($result{'supervisor_id'})) . "</span><br>\n";
        }
        print "<span class=bold>" . xlt('Case') . ": </span><span class=text>" . text($case_desc) . "</span><br>\n";

        print "<span class='bold'>" . xlt('Billing') . ': </span><span class="text">';
        $first = TRUE;
        foreach($billing as $item) {
            if(!$first) echo ', ';
            echo $item{'code_type'} . ':' . $item{'code'};
            if($item['units']) echo ' (' . $item['units'] . ')';
            $first = FALSE;
        }
        print "</span>";
        /* End */

    }

    print "</td></tr></table>\n";
}
