<?php
// +-----------------------------------------------------------------------------+ 
// Copyright (C) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// 
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Jacob T Paul <jacob@zhservices.com>
//
// +------------------------------------------------------------------------------+

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//


class newpatient{
	
//this will return the query string along with the parameter array, according to the case case.
//actual execution is done in the select_query function in Server_side
	
	
    public function query_formation($data){
        global $pid;
        switch($data[0]){
	    // Entries pending  for approval for Existing Patient and New Patient.
            case 'U4':
            $query=  "select * from  audit_master  where pid=? and  approval_status='1' and  (type='1' or type='2')";
            return array($query,array($pid));
            break;
             // Entries pending  for approval for  documents only (no demo change).
            case 'U5':
            $query = " select * from  audit_master  where pid=? and  approval_status='1' and  type='3' ";
            return array($query,array($pid));
            break;
            case 'J1':
            $query = "SELECT fname FROM patient_data WHERE pid=?";
            return array($query,array($pid));
            break;
            case 'P1':
            $query= "select MAX(pid)+1 AS pid  from patient_data ";
            
            return array($query);
            break;
            //for building patient Demo
            case 'P2':
            $query="select * from  layout_options " .
                "WHERE form_id = 'DEM' AND uor > 0 AND field_id != '' " .
                      "ORDER BY group_name, seq";
            return array($query);
            break;
            //for building patient Demo   Date of Birth
            case 'P3':
            $query="select  *, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD from patient_data where pid=? order by date DESC limit 0,1 ";
            return array($query,array($pid));
            break;
            //for building patient Demo   Employer Data
            case 'P4':
            $query="select  * from employer_data where pid=? order by date DESC limit 0,1 ";
            return array($query,array($pid));
            break;
             //for building patient Demo   Insurance company details for Patient 
            case 'P5':
            $query=" select insd.*, ic.name as provider_name from insurance_data as insd " .
                "left join insurance_companies as ic on ic.id = insd.provider " .
                "where pid = ? and type =? order by date DESC limit 1 ";
            array_unshift($data[1],$pid);
            return array($query,$data[1]);
            break;
	    // Entries pending  for approval demo and documents.
            case 'P6':
            $query=" select * from audit_master as am,audit_details as ad WHERE am.id=ad.audit_master_id and am.pid=? and am.approval_status='1'  
                            and  (am.type='1' or am.type='2' or am.type='3')  order by ad.id";
            return array($query,array($pid));
            break;
            // Demo building from layout options.
            case 'P7': 
            
            $query=" select * from layout_options WHERE form_id = 'DEM' AND uor > 0 AND field_id != '' " .
                " ORDER BY group_name, seq";
            
            return array($query);
            break;
	    //Global specific application for building demo.
            case 'P8':
            $query="select * from  globals where gl_name ='specific_application' ";
            return array($query);
            break;
	    //Global omit employers for building demo.
            case 'P9':
            $query=" select * from globals where gl_name ='omit_employers' ";
            return array($query);
            break;
            //getting the password
            case 'P18':	
            $query = "select  portal_username from patient_access_offsite where portal_username =? ";
            return array($query,$data[1]);
            break;
			
            case 'P20':	
            $x=array($data[1][0]);
            $query="select count(*) AS count from patient_data where pubpid = ?";
            return array($query,$x);
            break;
	    //getting DOB and SSN for verifying the duplicate patient existance
            case 'P21':	
            $x=array($data[1][0]);
            $query="select  ss,DOB  from patient_data where DOB=?";
            return array($query,$x);
            break;
	    //master data for calendar from Globals
            case 'B1':    
            
            if($data[1][0]=='calendar_interval'||$data[1][0]=='schedule_start'||$data[1][0]=='schedule_end')
            {
            $query="select gl_value from globals where gl_name=?";
            return array($query,$data[1]);
            }
            else
            return 0;
            break;
            
            case 'B4':
            //Check whether an entry exist in the form Encounter to decide whether patient is an existing patient.
            $query="select COUNT(*) AS cnt from form_encounter WHERE pid=?";
            return array($query,array($pid));
            break;
            
            case 'B5':
            //Existing appointments for a patient 
            array_unshift($data[1],$pid);
            $query="select pc_eid,pc_eventDate,pc_startTime,pc_endTime,fname,lname,name,pc_apptstatus from openemr_postcalendar_events AS c,
            users AS u,facility AS f WHERE pc_pid=? AND pc_aid=u.id AND pc_facility=f.id AND pc_apptstatus!=? order by pc_eventDate desc";
            return array($query,$data[1]);
            break;
            
            case 'B6':
            //Appointments pending for approval 
            array_push($data[1],$pid);
            $query="select am.id,am.approval_status,ad.audit_master_id,ad.field_name,ad.field_value,u.fname,u.lname,f.name from audit_master AS am,
            audit_details AS ad LEFT JOIN users AS u ON ad.field_value=u.id AND ad.field_name=? LEFT JOIN facility AS f ON ad.field_value=f.id AND
            ad.field_name=? WHERE am.pid=? AND am.id=ad.audit_master_id AND am.type='10' AND am.approval_status NOT IN ('2','4')
            ORDER BY approval_status, am.id desc,ad.id desc";
            return array($query,$data[1]);
            break;
            
            case 'B7':
            //patient appointment history  
            array_unshift($data[1],$pid);
            $query="select pc_eid,pc_eventDate,pc_startTime,pc_endTime,fname,lname,name,pc_apptstatus from openemr_postcalendar_events AS c,
            users AS u,facility AS f WHERE pc_pid=? AND pc_aid=u.id AND pc_facility=f.id AND pc_apptstatus=? order by pc_eventDate desc";
            return array($query,$data[1]);
            break;
            
            case 'B8':
            //List of Service Facility
            $query="select * from facility where service_location != 0 and id in (".add_escape_custom($data[1][0]).") order by name";
            return array($query);
            break;
            
            case 'B9':
            //Providers list 
            $query="select id, lname, fname from users WHERE authorized = 1 AND username != '' AND username NOT LIKE '%Admin%' AND active = 1
            AND ( info IS NULL OR info NOT LIKE '%Inactive%' ) ORDER BY lname, fname";
            return array($query);
            break;
            
            case 'B10':
            //Calendar default visit time for visit category. value for Admin--->others-->calendar
            $query="select pc_duration from openemr_postcalendar_categories WHERE pc_catid = ?";
            return array($query,$data[1]);
            break;
            
            case 'B11';
            //patient appointment
            $query="select pc_eventDate, pc_endDate, pc_startTime, pc_duration, pc_recurrtype, pc_recurrspec, pc_alldayevent, pc_catid,
            pc_prefcatid from openemr_postcalendar_events WHERE pc_aid = ? AND ((pc_endDate >= ? AND pc_eventDate < ?) OR
            (pc_endDate = '0000-00-00' AND pc_eventDate >= ? AND pc_eventDate < ?)) AND pc_facility = ?";
            return array($query,$data[1]);
            break;
            
            case 'B12':
            //Appointments pending for approval
            $query="select * from audit_master WHERE pid =? AND type='10'";
            return array($query,array($pid));
            break;
            
            //G series for form menu inc
            case 'G1':
            $query = "SELECT * FROM `documents_legal_categories` where dlc_category_name=? and dlc_category_type=2";
            return array($query,$data[1]);
            break;
                        
            case 'G2':
            $query = "SELECT * FROM documents_legal_master AS dlm WHERE dlm_subcategory <> ? and dlm_effective_date <= now() AND
            dlm_effective_date<>? AND dlm_document_id Not IN (SELECT distinct(dld_master_docid) FROM documents_legal_detail WHERE
            dld_id IS NOT NULL AND dld_pid=?)";
            array_push($data[1],$pid);
            return array($query,$data[1]);
            break;
                        
            case 'G3':
            $query = "SELECT * FROM documents_legal_master AS dlm LEFT OUTER JOIN documents_legal_detail as dld ON
            dlm_document_id=dld_master_docid WHERE dlm_subcategory <> ? and dlm_effective_date <= now() AND dlm_effective_date<>?
            AND dld_id IS NOT NULL AND dld_signed=? AND dld_pid=? ORDER BY dlm_effective_date DESC";
            array_push($data[1],$pid);
            return array($query,$data[1]);
            break;
                        
            case 'G4':
            $query = "SELECT * FROM documents_legal_master AS dlm JOIN documents_legal_detail as dld ON dlm_document_id=dld_master_docid
            JOIN form_encounter as fe ON encounter=dld_encounter WHERE dlm_subcategory = ? AND dlm_effective_date <= now() AND
            dlm_effective_date<>? AND dld_id IS NOT NULL AND dld_signed=? AND dld_signing_person=? AND dld_pid=?
            ORDER BY dlm_effective_date DESC";
            array_push($data[1],$pid);
            return array($query,$data[1]);
            break;
                        
            case 'G5':
            $query = "SELECT * FROM documents_legal_master AS dlm JOIN documents_legal_detail as dld ON dlm_document_id=dld_master_docid
            JOIN form_encounter as fe ON encounter=dld_encounter WHERE dlm_subcategory = ? and dlm_effective_date <= now() AND
            dlm_effective_date<>? AND dld_id IS NOT NULL AND dld_filename != '' AND dld_pid=? GROUP BY dld_encounter,dlm_document_id
            ORDER BY dld_id DESC";
            array_push($data[1],$pid);
            return array($query,$data[1]);
            break;
                        
            case 'F1':
            //Patient details . 
            $query="select * from patient_data where pid=?";
            return array($query,array($pid));
            break;
            
            case 'F2':
            //PDF forms detail selected 
            $query="select * from documents_legal_master where dlm_document_id=?";
            return array($query,$data[1]);
            break;
            
            case 'F3':
            //signing
            array_unshift($data[1],$pid);
            $query="select * from documents_legal_detail where dld_pid = ? and dld_signed='3' and dld_master_docid = ?";
            return array($query,$data[1]);
            break;
            
            case 'F6':
            //signing
            $query="select * from documents_legal_master where dlm_document_id=?";
            return array($query,$data[1]);
            break;
                    
            case 'F8':
            // Entries to be approved demo  for new patient, existing patient and only documents
            $query="select * from audit_master  where pid=? and  approval_status='1'  and  (type='1' or type='2' or type='3')";
            return array($query,array($pid));
            break;
            
            case 'F9':
            //signing
            $query="select * from documents_legal_master WHERE dlm_document_id=?";
            return array($query,$data[1]);
            break;
            
            case 'F10':
            //Documents ready to be signed and documents unsigned
            array_unshift($data[1],$pid);
            $query="select * from documents_legal_detail where dld_pid=? and (dld_signed='2' or dld_signed='0') and dld_master_docid=?";
            return array($query,$data[1]);
            break;
      
            case 'F12':
            //Selection from master document for showing to patient
            $query="select * from documents_legal_master WHERE dlm_document_name=?";
            return array($query,$data[1]);
            break;
        }
    }
}
?>