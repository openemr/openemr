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

class OEMRUser{
    
    //this will return the query string along with the parameter array, according to the case case.
    //actual execution is done in the select_query function in Server_side
    
    private function getPid($id){
        $row = sqlQuery("SELECT pid FROM audit_master WHERE id=? AND approval_status=1",array($id));
        return $row['pid'];
    }
    
    public function query_formation($data){
        global $pid;
        switch($data[0]){
            case 'U1':
            $query="select * from  audit_master where  approval_status='1'   and  (type='1' or type='2' or type='3') ";
            return array($query);
            break;
            
            case 'U2':
            $query=" select * from audit_master where  approval_status='1'  and  (type='1' or type='2' or type='3')  order by id   limit  ?,1";
            return array($query,array($data[1][0]-1));
            break;
            
            case 'U3':
            $pid = $this->getPid($data[1]);
            $query="SELECT * FROM documents_legal_master AS dlm " .
            "LEFT OUTER JOIN documents_legal_detail as dld ON dlm_document_id=dld_master_docid WHERE " .
            " dlm_subcategory not in (SELECT dlc_id FROM `documents_legal_categories` where dlc_category_name='Layout Signed'".
            " and dlc_category_type=2) and dlm_effective_date <= now() AND dlm_effective_date<>'0000-00-00 00:00:00' " .
            "AND dld_id IS NOT NULL AND dld_pid=? and dld_signed='0' " .
            "ORDER BY dlm_effective_date DESC";
            return array($query,array($pid));
            break;
            
	    // Entries pending  for approval for Existing Patient and New Patient.
            case 'U4':
            $pid = $this->getPid($data[1]);
            $query=  "select * from  audit_master  where pid=? and  approval_status='1' and  (type='1' or type='2')";
            return array($query,array($pid));
            break;
            
            // Entries pending  for approval for  documents only (no demo change).            
            case 'U5':
            $pid = $this->getPid($data[1]);
            $query = " select * from  audit_master  where pid=? and  approval_status='1' and  type='3' ";
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
            $pid = $this->getPid($data[1]);
            $query="select  *, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD from patient_data where pid=? order by date DESC limit 0,1 ";
            return array($query,array($pid));
            break;
            
           //for building patient Demo   Employer Data
            case 'P4':
            $pid = $this->getPid($data[1]);
            $query="select  * from employer_data where pid=? order by date DESC limit 0,1 ";
            return array($query,array($pid));
            break;
            
           //for building patient Demo   Insurance company details for Patient 
            case 'P5':
            $data[1][0] = $this->getPid($data[1][0]);
            $query=" select insd.*, ic.name as provider_name from insurance_data as insd " .
                "left join insurance_companies as ic on ic.id = insd.provider " .
                "where pid = ? and type =? order by date DESC limit 1 ";
            return array($query,$data[1]);
            break;
            
			// Entries pending  for approval demo and documents.
            case 'P6':
            $pid = $this->getPid($data[1]);
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
            
            case 'B13':
            //patient appointment
            $query="select * from facility where service_location != 0 order by name";
            return array($query);
            break;
            
            case 'E1':
            //list of approvals
            $query="select *,audit_master.id as audit_master_id from audit_master,patient_data where audit_master.pid=patient_data.pid and
            audit_master.approval_status='1' order by audit_master.id";
            return array($query);
            break;
            
            case 'E2':
            //list of approvals
            $query="select * from facility";
            return array($query);
            break;
            
            case 'E3':
            //list of approvals
            $query="select id,fname,lname,mname from users where authorized=1";
            return array($query);
            break;
            
            case 'E4':
            //list of approvals
            $query="select * from audit_master,patient_data,audit_details where audit_master.pid=patient_data.pid and
            audit_master.approval_status='1' and audit_master.type = 10 and audit_master_id=audit_master.id order by audit_master.id";
            return array($query);
            break;
        
            case 'E5':
            //list of approvals
            $query="select * from audit_master where audit_master.id=?";
            $row = sqlQuery($query,$data[1]);
            return array("SELECT * FROM documents_legal_detail join documents_legal_master on dld_master_docid=dlm_document_id WHERE dld_pid=? and  dld_signed='0' 
		and dlm_document_id=dld_master_docid and  dlm_subcategory  not in (SELECT dlc_id FROM `documents_legal_categories` 
		where dlc_category_name='Layout Signed' and dlc_category_type=2)",array($row['pid']));
            break;
            
            case 'F4':
            //signing
            $query="select * from documents_legal_categories where dlc_category_name=? and dlc_category_type=2";
            return array($query,$data[1]);
            break;
            
            case 'F5':
            //signing
            $query="select * from documents_legal_master LEFT OUTER JOIN documents_legal_categories ON dlm_category=dlc_id WHERE
            dlm_subcategory <> ? and  dlm_filename<>'' and dlm_upload_type = 0";
            return array($query,$data[1]);
            break;
            
            case 'F8':
            //signing
            $pid = $this->getPid($data[1]);
            $query="select * from audit_master  where pid=? and  approval_status='1'  and  (type='1' or type='2' or type='3')";
            return array($query,array($pid));
            break;
        
            case 'F12':
            //Selection from master document for showing to patient
            $query="select * from documents_legal_master WHERE dlm_document_name=?";
            return array($query,$data[1]);
            break;
            
            case 'payment_settings_all':
            $query = "SELECT service_name,login_id,transaction_key,md5 FROM payment_gateway_details";
            return array($query);
            break;
            
            case 'payment_gateways_list':
            $query = "SELECT option_id, title FROM list_options WHERE list_id = 'payment_gateways' ORDER BY seq";
            return array($query);
            break;

	    case 'F13':
            $query = "SELECT pid ,fname,lname,mname,DOB FROM patient_data
		    where  fname like ? or lname like ? or mname like ? or 
		    CONCAT(lname,' ',fname,' ',mname) like ? or pid like ? ORDER BY lname";
            return array($query,array($data[1]."%",$data[1]."%",$data[1]."%",$data[1]."%",$data[1]."%"));
        }
    }
}
?>