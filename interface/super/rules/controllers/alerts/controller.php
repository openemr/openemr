<?php
require_once( "model/CdrActivationManager.class.php");

class Controller_alerts extends BaseController {

    function _action_default() {
        $c = new CdrActivationManager();
        $this->viewBean->rules = $c->populate();
        $this->set_view("list.php");
    }

    function _action_submit() {

        //$this->viewBean->name = _post("name");
        //$this->viewBean->age = _post("age");

		$ids = _post("id");
		$actives = _post("active");
		$passives =  _post("passive");
		$reminders =  _post("reminder");
		
			
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
		//print_r($actives_final);
		//print_r($passives_final);
		//print_r($reminders_final);

		// Reflect the changes to the database.
         $c = new CdrActivationManager();
         $c->update($ids, $actives_final, $passives_final, $reminders_final);
         
         $this->forward("default");
    }

}
?>
