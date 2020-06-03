<?php

/**
 * LBF form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2009-2019 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . '/../../globals.php');
require_once($GLOBALS["srcdir"] . "/api.inc");

use OpenEMR\Common\Acl\AclMain;

// This function is invoked from printPatientForms in report.inc
// when viewing a "comprehensive patient report".  Also from
// interface/patient_file/encounter/forms.php.

function lbf_report($pid, $encounter, $cols, $id, $formname, $no_wrap = false)
{
    global $CPR;
    require_once($GLOBALS["srcdir"] . "/options.inc.php");

    $grparr = array();
    getLayoutProperties($formname, $grparr, '*');
    // Check access control.
    if (!empty($grparr['']['grp_aco_spec'])) {
        $LBF_ACO = explode('|', $grparr['']['grp_aco_spec']);
    }
    if (!AclMain::aclCheckCore('admin', 'super') && !empty($LBF_ACO)) {
        if (!AclMain::aclCheckCore($LBF_ACO[0], $LBF_ACO[1])) {
            die(xlt('Access denied'));
        }
    }

    $arr = array();
    $shrow = getHistoryData($pid);
    $fres = sqlStatement("SELECT * FROM layout_options " .
    "WHERE form_id = ? AND uor > 0 " .
    "ORDER BY group_id, seq", array($formname));
    while ($frow = sqlFetchArray($fres)) {
        $field_id  = $frow['field_id'];
        $currvalue = '';
        if (isOption($frow['edit_options'], 'H') !== false) {
            if (isset($shrow[$field_id])) {
                $currvalue = $shrow[$field_id];
            }
        } else {
            $currvalue = lbf_current_value($frow, $id, $encounter);
            if ($currvalue === false) {
                continue; // should not happen
            }
        }

        // For brevity, skip fields without a value.
        if ($currvalue === '') {
            continue;
        }

        // $arr[$field_id] = $currvalue;
        // A previous change did this instead of the above, not sure if desirable? -- Rod
        // $arr[$field_id] = wordwrap($currvalue, 30, "\n", true);
        // Hi Rod content width issue in Encounter Summary - epsdky
        // Also had it not wordwrap nation notes which breaks it since it splits
        //  html tags apart - brady
        if ($no_wrap || ($frow['data_type'] == 34 || $frow['data_type'] == 25)) {
            $arr[$field_id] = $currvalue;
        } else {
            $arr[$field_id] = wordwrap($currvalue, 30, "\n", true);
        }
    }

    echo "<table>\n";
    display_layout_rows($formname, $arr);
    echo "</table>\n";
}
