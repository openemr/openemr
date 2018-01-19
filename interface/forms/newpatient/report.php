<?php
/**
 * Encounter form report function.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */

include_once(dirname(__file__)."/../../globals.php");

function newpatient_report($pid, $encounter, $cols, $id)
{
    $res = sqlStatement("select e.*, f.name as facility_name from form_encounter as e join facility as f on f.id = e.facility_id where e.pid=? and e.id=?", array($pid,$id));
    print "<table><tr><td>\n";
    while ($result = sqlFetchArray($res)) {
        print "<span class=bold>" . xlt('Facility') . ": </span><span class=text>" . text($result{"facility_name"}) . "</span><br>\n";
        if (acl_check('sensitivities', $result['sensitivity'])) {
            print "<span class=bold>" . xlt('Reason') . ": </span><span class=text>" . nl2br(text($result{"reason"})) . "</span><br>\n";
        }
    }

    print "</td></tr></table>\n";
}
