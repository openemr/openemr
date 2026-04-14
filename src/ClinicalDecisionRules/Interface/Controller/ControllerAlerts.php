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
namespace OpenEMR\ClinicalDecisionRules\Interface\Controller;

use OpenEMR\ClinicalDecisionRules\Interface\BaseController;
use OpenEMR\ClinicalDecisionRules\Interface\RuleLibrary\CdrAlertManager;

class ControllerAlerts extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    function _action_listactmgr()
    {
        $c = new CdrAlertManager();
        // Instantiating object if does not exist to avoid
        //    "creating default object from empty value" warning.
        if (!isset($this->viewBean)) {
            $this->viewBean = new \stdClass();
        }

        $this->viewBean->rules = $c->populate();
        $this->set_view("list_actmgr.php");
    }


    function _action_submitactmgr()
    {


        $ids = filter_input(INPUT_POST, 'id', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?: [];
        $actives = filter_input(INPUT_POST, 'active', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?: [];
        $passives = filter_input(INPUT_POST, 'passive', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?: [];
        $reminders = filter_input(INPUT_POST, 'reminder', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?: [];
        $access_controls = filter_input(INPUT_POST, 'access_control', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY) ?: [];

        // CdrAlertManager::update() consumes id[] and access_control[]
        // by zero-based positional offset, so reindex both via
        // array_values() to defang sparse/non-sequential POST data
        // (e.g. id[3]=… without id[0..2]) and then require the two to
        // line up by length. The active/passive/reminder checkbox
        // arrays are sparse on purpose — only checked boxes get
        // submitted — and the loop below already collapses absent
        // indices to "0", so they intentionally aren't checked here.
        $ids = array_values($ids);
        $access_controls = array_values($access_controls);
        $numrows = count($ids);
        if (count($access_controls) !== $numrows) {
            http_response_code(400);
            die(xlt('Malformed request: access_control must be aligned with id.'));
        }

        // The array of check-boxes we get from the POST are only those of the checked ones with value 'on'.
        // So, we have to manually create the entitre arrays with right values.
        $actives_final = [];
        $passives_final = [];
        $reminders_final = [];


        for ($i = 0; $i < $numrows; ++$i) {
            $actives_final[] = !empty($actives[$i]) && $actives[$i] == "on" ? "1" : "0";

            $passives_final[] = !empty($passives[$i]) && $passives[$i] == "on" ? "1" : "0";

            $reminders_final[] = !empty($reminders[$i]) && $reminders[$i] == "on" ? "1" : "0";
        }

        // Reflect the changes to the database.
        $c = new CdrAlertManager();
        $c->update($ids, $actives_final, $passives_final, $reminders_final, $access_controls);
        // Instantiating object if does not exist to avoid
        //    "creating default object from empty value" warning.
        if (!isset($this->viewBean)) {
            $this->viewBean = new \stdClass();
        }

        $this->forward("listactmgr");
    }
}
