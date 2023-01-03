<?php

/**
 * EncounterSessionUtil refactored from encounter.inc.php handles setting the encounter in the session
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    <Unknown> Authorship was not listed in encounter.inc.php
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Session;

use OpenEMR\Services\FormService;

class EncounterSessionUtil
{
    public static function setEncounter($enc)
    {

        // Escape $enc by forcing it to an integer to protect from sql injection
        $enc = intval($enc);

        $return_val = 1;
        global $encounter;
        global $pid;
        global $attendant_type;

        $formsService = new FormService();

        $attendant_id = $attendant_type == 'pid' ? $pid : $_SESSION['therapy_group'];
        if ($enc == "") {
            $enc = date("Ymd");
            if ($formsService->getFormByEncounter($attendant_id, $enc)) {
                //there is an encounter entered for today
            } else {
                //addForm($enc, "New Patient Encounter", 0, $pid, 1);
                $return_val = 0;
            }
        }

        SessionUtil::setSession('encounter', $enc);
        $encounter = $enc;

        //returns 1 on successful global set, or 0 if there was no
        //current encounter, signifying that the interface should load
        //the screen for a new encounter
        return $return_val;
    }
}
