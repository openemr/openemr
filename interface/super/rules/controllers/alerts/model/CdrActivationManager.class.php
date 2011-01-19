<?php
/************************************************************************
                        CdrActivationManager.php - Copyright Ensoftek

**************************************************************************/

require_once( "CdrHelper.class.php");
require_once( $GLOBALS['fileroot'] . "/library/clinical_rules.php" );

/**
 * class CdrActivationManager
 *
 */
class CdrActivationManager{


        /**
         * Constructor 
         */
        function CdrActivationManager ($id = "", $prefix = "") {
        }
        
        
        function populate() {
        	    $cdra = array();
        	
        	    $rules = resolve_rules_sql(NULL, 0, TRUE);
        	    
        	    foreach( $rules as $rowRule ) {
		              $rule_id = $rowRule['id'];
		              $cdra[] = new CdrResults($rule_id, $rowRule['active_alert_flag'], $rowRule['passive_alert_flag'], $rowRule['patient_reminder_flag']);
        	    }
        	    
		        return $cdra;
        }
        
        
        function getrulenamefromid($rule_id) {
		    	$rez = sqlStatement("SELECT `title` FROM `list_options` " .
		                "WHERE option_id=?", array($rule_id) );
		        
		    	
		    	for($iter=0; $row=sqlFetchArray($rez); $iter++) {
		           return $row['title'];
		        }
		    	  	
	    }
        
        function update($rule_ids, $active_alert_flags, $passive_alert_flags, $patient_reminder_flags) {
        	
        	    for($index=0; $index < count($rule_ids); $index++) { 
		              $rule_id = $rule_ids[$index];
		              $active_alert_flag = $active_alert_flags[$index];
		              $passive_alert_flag = $passive_alert_flags[$index];
        	          $patient_reminder_flag = $patient_reminder_flags[$index];
		              $cdra = new CdrResults($rule_id, $active_alert_flag, $passive_alert_flag, $patient_reminder_flag);
		              $cdra->update_table();
        	    }  

        	    
        }
	    
} // end of CdrActivationManager
?>
