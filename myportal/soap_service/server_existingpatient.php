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

class existingpatient {
    

//this will return the query string along with the parameter array, according to the case case.
//actual execution is done in the select_query function in Server_side

  public function query_formation($data){
        global $pid;
        switch($data[0]){
            case 'A1':
            //Select list of encounters for the patients 
            $query="select f.id, f.date, f.pid, f.encounter, f.stmt_count, f.last_stmt_date, f.facility_id,f.billing_facility, " .
                  "p.fname, p.mname, p.lname, p.street, p.city, p.state, p.postal_code " .
                      " ,u.fname as dfname, u.mname as dmname, u.lname as dlname".
               " from ((form_encounter AS f, patient_data AS p) " .
                      " left join users as u on f.provider_id =u.id) ".
                      " left join facility as fa on fa.id =f.billing_facility ".
                  "WHERE ( f.pid=? ) AND " .
                  "p.pid = f.pid " .
                  "ORDER BY f.pid,f.billing_facility, f.date desc, f.encounter desc";
            return array($query,array($pid));
            break;
            //Select list of providers
            case 'A2':
              $query="Select distinct u.fname, u.mname, u.lname".
               " from (form_encounter AS f, billing AS b) " .
                      " left join users as u on f.provider_id =u.id ".
                  " WHERE f.pid = b.pid and  f.encounter = b.encounter " .
                      " and f.encounter=? and f.pid=? ".
                  " ORDER BY u.fname, u.lname";
            array_push($data[1],$pid);
            return array($query,array($data[1]));
            break;
            //Select list of encounters for the patients 
            case 'A3':
            //ledger
            $where = '';
            $wherearray=array();
            foreach($data[1][0] as $k=>$v)
            {
            $where .= " OR f.id = ?";
            $wherearray[]=$v;
            }
            $where = substr($where, 4);
            if(!$where)
            {
            $where='?';
            $wherearray[]=0;
            }
            $wherearray[]=$pid;
		           $query= "Select f.id, f.date, f.pid, f.encounter, f.stmt_count, f.last_stmt_date, f.facility_id,f.billing_facility, " .
                  "p.fname, p.mname, p.lname, p.street, p.city, p.state, p.postal_code " .
                      " ,u.fname as dfname, u.mname as dmname, u.lname as dlname".
                " from ((form_encounter AS f, patient_data AS p) " .
                      " left join users as u on f.provider_id =u.id) ".
                      " left join facility as fa on fa.id =f.billing_facility ".
                  "WHERE ( $where) AND " .
                  "p.pid = f.pid  and f.pid=?" .
                  "ORDER BY f.pid,f.billing_facility, f.date desc, f.encounter desc";
            return array($query,$wherearray);
            break;
            
            case 'A4':
            $query="select * from form_encounter where pid=? limit 1 ";
            return array($query,array($pid));
            break;
            
            case 'A5':
            include_once('../../library/formdata.inc.php');
            $enc_set_array=array();
            $enc_set_array[]=$pid;
            if($data[1][1]=='' && $data[1][2]>0)
            {
            $enc_set= " and encounter=? " ;
            $enc_set_array[]=$data[1][2];
            }
            $provider="";
            $provider  =add_escape_custom($data[1][0]);  
            $query="select fe.id,fe.pid,encounter,date_format(fe.date,'%Y-%m-%d') 
            as date,concat(pd.lname,' ',pd.fname) as patname,concat(u.lname,', ',u.fname) 
            as provname,".$provider." from form_encounter fe left outer join users u
             on u.id =fe.".$provider." join patient_data pd on pd.pid=fe.pid where 
             fe.pid=?".  $enc_set ." order by fe.date desc";
            return array($query, $enc_set_array);
            break;
            
            case 'A6':
            $enc_set_array=array();
            $enc_set_array[]=$pid;
            if($data[1][0]=='' && $data[1][1]>0)
            {
            $enc_set= " and encounter=? ";
            $enc_set_array[]=$data[1][1];
            }
            $query="select encounter,sum(fee) as copay ".
            " from billing where code_type='copay' and pid=? $enc_set group by encounter";
            return array($query,$enc_set_array);
            break;
            //DEtails of CPT, Diagnosis etc of an encounter
            case 'A7':
            $enc_set_array=array();
            $enc_set_array[]=$pid;
            if($data[1][0]=='' && $data[1][1]>0)
            {
            $enc_set= " and encounter=? ";
            $enc_set_array[]=$data[1][1];

            }
            $query="select concat(encounter,code,modifier) as ecm,encounter,code,
            modifier,units,fee,code_text,justify from billing where activity=1 and fee>0 and code_type not in('ICD9','copay') and pid=? $enc_set";
            return array($query,$enc_set_array);
            break;
            //Payment details  of an encounter
            case 'A8':
            $enc_set_array=array();
            $enc_set_array[]=$pid;
            if($data[1][0]=='' && $data[1][1]>0)
            {
            $enc_set= " and encounter=? ";
            $enc_set_array[]=$data[1][1];
            }
            $query="select concat(encounter,code,modifier) as pecm,encounter,code,
            modifier,pay_amount,adj_amount,payer_type,post_time,account_code,
            follow_up_note,memo,date_format(post_time,'%Y-%m-%d') as dtfrom from ar_activity where pid=? $enc_set";
            return array($query,$enc_set_array);
            break;
            case 'A9':
                $query = "SELECT sum(pay_total)  as pay_total FROM ar_session WHERE patient_id=? AND adjustment_code=?";
                return array($query,array($pid,'pre_payment'));
            break;
            case 'A10':
                $query = "SELECT sum(pay_amount)  as pay_amount FROM ar_session,ar_activity WHERE patient_id=? AND adjustment_code=?
                          AND pid=? AND ar_session.session_id=ar_activity.session_id  and pay_amount>0";
                return array($query,array($pid,'pre_payment',$pid));
            break;
            case 'A11':
                $query = "SELECT sum(pay_total)  as pay_total FROM ar_session WHERE patient_id=? AND adjustment_code!=?";
                return array($query,array($pid,'pre_payment'));
            break;
            case 'A12':
                $query = "SELECT sum(pay_amount)  as pay_amount FROM ar_session,ar_activity WHERE patient_id=? AND adjustment_code!=?
                          AND pid=? AND ar_session.session_id=ar_activity.session_id  and pay_amount>0";
                return array($query,array($pid,'pre_payment',$pid));
            break;
            
	    // Entries pending  for approval for Existing Patient and New Patient.
            case 'U4':
            $query=  "select * from  audit_master  where pid=? and  approval_status='1' and  (type='1' or type='2')";
            return array($query,array($pid));
            break;
            // Entries pending  for approval for  documents only (no demo change).            
            case 'U5':
            $query = " select * from  audit_master  where pid=? and  approval_status='1' and  type='3' ";
            return array($query,$data[1]);
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
                "ORDER BY group_name, seq";
            
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
            //details of COpay and Fees
            case 'P10':
            array_push($data[1],$pid);
            $query= "select date, code_type, code, modifier, code_text, fee , units, justify  from billing WHERE  encounter =? AND pid = ? AND " .
                " activity = 1 AND fee != 0.00 ORDER BY  fee desc,code,modifier";
            return array($query,$data[1]);
            break;
        
            case 'P11':	
            $query = "select code_text from codes WHERE  code = ? ";
            return array($query,$data[1]);
            break;
			//Details of drug sales
            case 'P12':	
            array_push($data[1],$pid);
             $query = "select s.drug_id, s.sale_date, s.fee, s.quantity from drug_sales AS s " .
                "WHERE  s.encounter = ? and s.pid = ? AND s.fee != 0 " .
                "ORDER BY s.sale_id";
            return array($query,$data[1]);
            break;
			//Details of Payments
            case 'P14':	
            array_push($data[1],$pid);
                    $query = "Select a.code, a.modifier, a.memo, a.payer_type, a.adj_amount, a.pay_amount, " .
                "a.post_time, a.session_id, a.sequence_no,a.follow_up, a.follow_up_note, " .
                "s.payer_id, s.reference, s.check_date, s.deposit_date " .
                ",i.name from ar_activity AS a " .
                "LEFT OUTER JOIN ar_session AS s ON s.session_id = a.session_id " .
                "LEFT OUTER JOIN insurance_companies AS i ON i.id = s.payer_id " .
                "WHERE  a.encounter = ? and a.pid = ? " .
                "ORDER BY s.check_date, a.sequence_no";
            return array($query,$data[1]);
            break;
			//Address of Billing Facility
            case 'P15':	
            $query = "SELECT f.name,f.street,f.city,f.state,f.postal_code,f.phone from facility f " .
            " where  id=?";
            return array($query,$data[1]);
            break;
            //Encounter status primary,secondary Etc
            case 'P16':	
            array_push($data[1],$pid);
            $query = "select last_level_closed from form_encounter where encounter= ? and pid =? ";
            return array($query,$data[1]);
            break;
        
            case 'P17':	
            $query = "select COUNT( DISTINCT TYPE ) NumberOfInsurance from insurance_data where pid =? and provider>0 ";
            return array($query,array($pid));
            break;
        
            case 'P19':	
            $query = "select  date,encounter from form_encounter where pid =? ORDER BY encounter";
            return array($query,array($pid));
            break;
        
            case 'P20':	
            if($pid) 
             {
              $string_query=" and pid !=?";
             }
             if($string_query)
             {
             $x=array($data[1][0],$pid);
             }
             else
             {
             $x=array($data[1][0]);
             }
            $query="select count(*) AS count from patient_data where pubpid = ? $string_query";
            return array($query,$x);
            break;
        
			//getting DOB and SSN for verifying the duplicate patient existance
            case 'P21':	
            if($pid) 
             {
              $string_query=" and pid !=?";
             }
             if($string_query)
             {
             $x=array($data[1][0],$pid);
             }
             else
             {
             $x=array($data[1][0]);
             }
            $query="select  ss,DOB  from patient_data where DOB=? $string_query  ";
            return array($query,$x);
            break;
        
			//master data for calendar from Globals
            case 'B1':    
            //patient appointment
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
            include_once('../../library/formdata.inc.php');
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
            
            case 'B13':
            //patient appointment
            $query="select * from facility where service_location != 0 order by name";
            return array($query);
            break;
            
            //C series for mailbox.
            case 'C1':
                    $query = "SELECT username, fname, lname FROM users WHERE username != '' AND active = 1 AND ( info IS NULL OR info NOT LIKE
                    '%Inactive%' ) ORDER BY lname, fname";
                            return array($query);
                            break;
                        
            case 'C2':
                    $query = "SELECT option_id, title FROM list_options WHERE list_id = ? ORDER BY seq";
                            return array($query,$data[1]);
                            break;
            
            //D series for patient.
            case 'D1':
                    $query = "SELECT forms.encounter, forms.form_id, forms.id, forms.form_name, forms.formdir,forms.date AS fdate,
                    form_encounter.date ,form_encounter.reason FROM forms LEFT OUTER JOIN form_encounter ON  forms.pid=form_encounter.pid
                    WHERE forms.pid = ? AND forms.deleted=0 AND forms.formdir<>? GROUP BY id ORDER BY forms.encounter,fdate  ASC";
                    array_unshift($data[1],$pid);
                            return array($query,$data[1]);
                            break;
                        
            case 'D2':
                    $query = "SELECT name FROM registry ORDER BY priority";
                            return array($query);
                            break;
                        
            case 'D3':
                    $query = "select * from lists WHERE pid =? ORDER BY type, begdate";
                            return array($query,array($pid));
                            break;
                        
            case 'D4':
                    $query = "select encounter from issue_encounter WHERE pid = ? AND list_id = ?";
                            array_unshift($data[1],$pid);
                            return array($query,$data[1]);
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
                    
            case 'J1':
            $query = "SELECT fname FROM patient_data WHERE pid=?";
            return array($query,array($pid));
            break;
            //Checking whether a new patient entry is pending in the audit master
            case 'J2':
            $query = "SELECT pid FROM audit_master WHERE approval_status=1 and type=1 and pid=?";
            return array($query,array($pid));
            break;
            
            case 'payment_settings':
            $query = "SELECT login_id,transaction_key,md5 FROM payment_gateway_details WHERE service_name=?";
            return array($query,$data[1]);
            break;
            
            case 'authorizenet_id':
            $query = "SELECT authorize_net_id FROM patient_access_offsite WHERE pid=?";
            return array($query,array($pid));
            break;
        }
    }
}
?>