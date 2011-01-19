
<?php 
/************************************************************************
                        CdrHelper.php - Copyright Ensoftek

**************************************************************************/

class CdrResults{
	    var $id;
	    var $pid;
        var $rule;
        var $passive_flag;
        var $active_flag;
        var $reminder_flag;
        
	  function CdrResults($rule_id = "", $active_alert_flag = "", $passive_alert_flag = "", $patient_reminder_flag = "", $pid = "-1" ) {
	  	    $this->id = $rule_id;
	  	    $this->pid = $pid;
			$this->active_flag = $active_alert_flag;
			$this->passive_flag = $passive_alert_flag;
			$this->reminder_flag = $patient_reminder_flag;
      }
      
      function active_alert_flag() {
                return $this->active_flag;
      }

      function passive_alert_flag() {
                return $this->passive_flag;
      }

      function get_rule() {
      	        $this->rule = $this->getrulenamefromid();
                return $this->rule;
      }

      function get_id() {
                return $this->id;
      }
      
      function patient_reminder_flag() {
                return $this->reminder_flag;
      }
      
      function get_default_practice_setting_status() {
      	
      	   		if ( $this->pid == "0") {
      	   			return "ON";
      	   		}
      	   		else {
      	   			return "OFF";
      	   		}
      }
      
      function update_table() {
      	
    			$query = "UPDATE clinical_rules SET active_alert_flag = " . $this->active_flag  .
    			                  ", passive_alert_flag = " . $this->passive_flag .
    			                  ", patient_reminder_flag = " . $this->reminder_flag .
					    		  " WHERE id = " . "'" . $this->id . "'" ;
    
			   //echo $query . "<br>\n";
			   sqlStatement($query);
    
      }
      
      function update_patient_reminder_alert($pid, $id, $patient_reminder_flag) {
      	
    			$query = "UPDATE clinical_rules SET patient_reminder_flag = " . $patient_reminder_flag .
					    		  " WHERE id = " . "'" . $id . "'" . " AND pid = " . "'" . $pid . "'";
    
			   //echo $query . "<br>\n";
			   sqlStatement($query);
    
      }

      function delete_patient_reminder_alert($pid, $id) {
      	
    			$query = "DELETE FROM clinical_rules WHERE id = " . "'" . $id . "'" . " AND pid = " . "'" . $pid . "'";
    
			   //echo $query . "<br>\n";
			   sqlStatement($query);
    
      }
      
      function add_patient_reminder_alert($pid, $id, $row, $patient_reminder_flag) {
      	
		   		$query = "INSERT INTO clinical_rules (id, pid, active_alert_flag, passive_alert_flag, cqm_flag, cqm_code, amc_flag, amc_code, patient_reminder_flag ) ".
	    			         "VALUES (". "'" . $row['id'] . "', " .
	    			         			 $pid . ", " .
	    								 $row['active_alert_flag'] . ", " .
	    								 $row['passive_alert_flag'] . ", " .
	    								 $row['cqm_flag'] . ", " .
	    								 "'" . $row['cqm_code'] . "', " .
	    								 $row['amc_flag'] . ", " .
	    								 "'". $row['amc_code'] . "', " .
	    								 $patient_reminder_flag . ")";
	    		   

			   //echo $query . "<br>\n";
			   sqlStatement($query);
      }
      
      
      function add_edit_del_patient_specific_alert($pid, $id, $patient_reminder_flag) {
      	
      			// Check if a patient specific row already exists. If it does, update accordingly.
		    	$query = "SELECT * FROM clinical_rules WHERE id = " . "'" . $id . "'" . " AND pid = " . "'" . $pid . "'";
			    //echo $query . "<br>\n";
			    $rez = sqlStatement($query);
		    	
		    	if ( sqlFetchArray($rez) > 0) {
		    		if ( $patient_reminder_flag == '2' ) { // DEFAULT radio button selected
		    			$this->delete_patient_reminder_alert($pid, $id);
   			   		}
   			   		else { // ON/OFF radio button selected 
						$this->update_patient_reminder_alert($pid, $id, $patient_reminder_flag);
					}
	    		   return;
		    	}
		    	else {
		    		if ( $patient_reminder_flag == '2' ) { // DEFAULT radio button selected
		    			return;
   			   		}
		    		
		    	}
      	
      			// No patient specic row exists. Create one.
		    	$query = "SELECT * FROM clinical_rules WHERE id=" . "'" . $id . "'" . " AND pid='0'";
			    //echo $query . "<br>\n";
			    $rez = sqlStatement($query);
		    	for($iter=0; $row=sqlFetchArray($rez); $iter++) {
		    		$this->add_patient_reminder_alert($pid, $id, $row, $patient_reminder_flag);
		    		return;
		        }
		        
      }
      
      
      function getrulenamefromid() {
		    	$rez = sqlStatement("SELECT `title` FROM `list_options` " .
		                "WHERE option_id=?", array($this->id) );
		        
		    	
		    	for($iter=0; $row=sqlFetchArray($rez); $iter++) {
		           return $row['title'];
		        }
		    	  	
	  }
      
          
}
?>