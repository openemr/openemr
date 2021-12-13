<?php

// Copyright (C) 2011 Ensoftek, Inc
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//

require_once("CdrHelper.class.php");
require_once($GLOBALS['fileroot'] . "/library/clinical_rules.php");

/**
 * class CdrAlertManager
 *
 */
class CdrAlertManager
{
        /**
         * Constructor
         */
    function CdrActivationManager($id = "", $prefix = "")
    {
    }


    function populate()
    {
        $cdra = array();

        $rules = resolve_rules_sql('', 0, true);

        foreach ($rules as $rowRule) {
            $rule_id = $rowRule['id'];
            $cdra[] = new CdrResults($rule_id, $rowRule['active_alert_flag'], $rowRule['passive_alert_flag'], $rowRule['patient_reminder_flag'], $rowRule['access_control']);
        }

        return $cdra;
    }

    function update($rule_ids, $active_alert_flags, $passive_alert_flags, $patient_reminder_flags, $access_controls)
    {

        for ($index = 0; $index < count($rule_ids); $index++) {
            $rule_id = $rule_ids[$index];
            $active_alert_flag = $active_alert_flags[$index];
            $passive_alert_flag = $passive_alert_flags[$index];
            $patient_reminder_flag = $patient_reminder_flags[$index];
                  $access_control = $access_controls[$index];
            $cdra = new CdrResults($rule_id, $active_alert_flag, $passive_alert_flag, $patient_reminder_flag, $access_control);
            $cdra->update_table();
        }
    }
} // end of CdrAlertManager
