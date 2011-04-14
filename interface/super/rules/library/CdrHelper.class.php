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

class CdrResults{
	    var $id;
        var $rule;
        var $passive_flag;
        var $active_flag;
        var $reminder_flag;
        
	  function CdrResults($rule_id = "", $active_alert_flag = "", $passive_alert_flag = "", $patient_reminder_flag = "") {
	  	    $this->id = $rule_id;
                    $this->rule = getLabel($this->id,'clinical_rules');
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
                return $this->rule;
      }

      function get_id() {
                return $this->id;
      }
      
      function patient_reminder_flag() {
                return $this->reminder_flag;
      }
      
      function update_table() {
      	
    			$query = "UPDATE clinical_rules SET active_alert_flag = ?" .
    			                  ", passive_alert_flag = ?" .
    			                  ", patient_reminder_flag = ?" .
					  		  " WHERE id = ?";
    
			   sqlStatement($query, array($this->active_flag,$this->passive_flag,$this->reminder_flag,$this->id) );
    
      }
          
}
?>
