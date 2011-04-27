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
class Controller_alerts extends BaseController {

    function _action_listactmgr() {
        $c = new CdrAlertManager();
        $this->viewBean->rules = $c->populate();
        $this->set_view("list_actmgr.php");
    }

    
    function _action_submitactmgr() {

		
		$ids = $_POST["id"];
		$actives = $_POST["active"];
		$passives =  $_POST["passive"];
		$reminders =  $_POST["reminder"];
		
			
		// The array of check-boxes we get from the POST are only those of the checked ones with value 'on'.
		// So, we have to manually create the entitre arrays with right values.
		$actives_final = array();
		$passives_final = array();
		$reminders_final = array();
			
		$numrows = count($ids);
		for ($i = 0; $i < $numrows; ++$i) {
				
		        if ( $actives[$i] == "on") {
		        	$actives_final[] = "1";
		        }
		        else {
		        	$actives_final[] = "0";;
		        }
		        
		        if ( $passives[$i] == "on") {
		        	$passives_final[] = "1";
		        }
		        else {
		        	$passives_final[] = "0";;
		        }
		        
		        if ( $reminders[$i] == "on") {
		        	$reminders_final[] = "1";
		        }
		        else {
		        	$reminders_final[] = "0";;
		        }
		        
		        
		}

		// Reflect the changes to the database.
         $c = new CdrAlertManager();
         $c->update($ids, $actives_final, $passives_final, $reminders_final);
         
         $this->forward("listactmgr");
    }

}
?>
