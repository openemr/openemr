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
class Controller_alerts extends BaseController
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
                $this->viewBean = new stdClass();
        }

        $this->viewBean->rules = $c->populate();
        $this->set_view("list_actmgr.php");
    }


    function _action_submitactmgr()
    {


        $ids = $_POST["id"];
        $actives = $_POST["active"] ?? null;
        $passives =  $_POST["passive"];
        $reminders =  $_POST["reminder"] ?? null;
                $access_controls = $_POST["access_control"];


        // The array of check-boxes we get from the POST are only those of the checked ones with value 'on'.
        // So, we have to manually create the entitre arrays with right values.
        $actives_final = array();
        $passives_final = array();
        $reminders_final = array();


        $numrows = count($ids);
        for ($i = 0; $i < $numrows; ++$i) {
            if (!empty($actives[$i]) && ($actives[$i] == "on")) {
                $actives_final[] = "1";
            } else {
                $actives_final[] = "0";
                ;
            }

            if (!empty($passives[$i]) && ($passives[$i] == "on")) {
                $passives_final[] = "1";
            } else {
                $passives_final[] = "0";
                ;
            }

            if (!empty($reminders[$i]) && ($reminders[$i] == "on")) {
                $reminders_final[] = "1";
            } else {
                $reminders_final[] = "0";
                ;
            }
        }

        // Reflect the changes to the database.
         $c = new CdrAlertManager();
         $c->update($ids, $actives_final, $passives_final, $reminders_final, $access_controls);
         // Instantiating object if does not exist to avoid
         //    "creating default object from empty value" warning.
        if (!isset($this->viewBean)) {
              $this->viewBean = new stdClass();
        }

         $this->forward("listactmgr");
    }
}
