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
//           Vinish K     <vinish@zhservices.com>
//
// +------------------------------------------------------------------------------+

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

global $ISSUE_TYPES;
$ignoreAuth=true;
ob_start();

require_once("../../interface/globals.php");
require_once(dirname(__FILE__)."/../../controllers/C_Document.class.php");
$err = '';
if(!extension_loaded("soap")){
  dl("php_soap.dll");
}
require_once("server_med_rec.php");
require_once("factory_class.php");
class UserService extends Userforms
{

/**  
* To display the patient uploaded files/pdf patient wise
*/
  public function patientuploadedfiles($data){
    if($this->valid($data[0])){
      ob_start();
      $query   = "
        SELECT
          am.id,
          am.pid,
          ad.field_value AS doc_name,
          pd.fname,
          pd.lname,
          pd.mname,
          ad2.field_value AS file_name,
          ad3.field_value AS pat_comments
        FROM
          audit_details AS ad 
          JOIN audit_master AS am 
            ON am.id = ad.audit_master_id 
          LEFT JOIN patient_data AS pd 
            ON am.pid = pd.pid
          JOIN audit_details AS ad2 
            ON am.id = ad2.audit_master_id
            AND ad2.field_name = 'dlm_filename'
          JOIN audit_details AS ad3 
            ON am.id = ad3.audit_master_id
            AND ad3.field_name = 'dld_patient_comments'                                                
        WHERE ad.field_name = 'dlm_document_name'   
          AND approval_status = '1' 
          AND am.type = '4'
          ORDER BY am.pid ASC
      ";        
      if(!empty($data[1])){
        $query .= " AND am.id = ?";
        $res = sqlStatement($query,array($data[1]));
      }else{
        $res = sqlStatement($query);
      }		  
      if ($res) {
        for($iter=0; $row=sqlFetchArray($res); $iter++) {
          $all[$iter] = $row;
        }
      }
      $v = ob_get_clean();
      return $all;
    }
  }  
    
  public function createandstoretodirectory($data){
    global $pid;
    if($this->valid($data[0])){
      $file_name=$data[1];
      $data=$data[2];
      $savedpath=$GLOBALS['OE_SITE_DIR']."/documents/myportal/patientuploads/".$pid;
      if(is_dir($savedpath));
      else
      {
        mkdir($savedpath,0777,true);
        chmod($savedpath, 0777);
      }
      $handler = fopen($savedpath."/".$file_name,"w");
      fwrite($handler, base64_decode($data));
      fclose($handler);
      chmod($savedpath."/".$file_name,0777);
    }
    else{
      throw new SoapFault("Server", "credentials failed");
    }    
  }
  
/**  
* To move category,rename filename,input note and to move to new patient#
*/
  public function documents_update($data){
    if($this->valid($data[0])){
      $_POST['process'] = true;
      $_POST['new_category_id'] = $data[1];
      $_POST['new_patient_id']  = $data[4];
      $file_path = '';
      if($data[9] == 2)
	$file_path = $GLOBALS['OE_SITE_DIR']."/documents/myportal/unsigned/".$data[6];
      elseif($data[9] == 1)
	$file_path = $GLOBALS['OE_SITE_DIR']."/documents/myportal/signed/".$data[6];
      elseif($data[9] == 4)
	$file_path = $GLOBALS['OE_SITE_DIR']."/documents/myportal/patientuploads/".$data[5]."/".$data[6];        
      $mime_types = array(
	      "pdf"=>"application/pdf"
	      ,"exe"=>"application/octet-stream"
	      ,"zip"=>"application/zip"
	      ,"docx"=>"application/msword"
	      ,"doc"=>"application/msword"
	      ,"xls"=>"application/vnd.ms-excel"
	      ,"ppt"=>"application/vnd.ms-powerpoint"
	      ,"gif"=>"image/gif"
	      ,"png"=>"image/png"
	      ,"jpeg"=>"image/jpg"
	      ,"jpg"=>"image/jpg"
	      ,"mp3"=>"audio/mpeg"
	      ,"wav"=>"audio/x-wav"
	      ,"mpeg"=>"video/mpeg"
	      ,"mpg"=>"video/mpeg"
	      ,"mpe"=>"video/mpeg"
	      ,"mov"=>"video/quicktime"
	      ,"avi"=>"video/x-msvideo"
	      ,"3gp"=>"video/3gpp"
	      ,"css"=>"text/css"
	      ,"jsc"=>"application/javascript"
	      ,"js"=>"application/javascript"
	      ,"php"=>"text/html"
	      ,"htm"=>"text/html"
	      ,"html"=>"text/html"
      );
  
      $extension = strtolower(end(explode('.',$file_path)));
      $mime_types = $mime_types[$extension];
      $_FILES['file']['name'][0]     = $data[6];
      $_FILES['file']['type'][0]     = $mime_types;
      $_FILES['file']['tmp_name'][0] = $file_path;
      $_FILES['file']['error'][0]    = 0;
      $_FILES['file']['size'][0]     = filesize($file_path);
      $_POST['category_id']          = $_POST['new_category_id'];
      $_POST['patient_id']           = $_POST['new_patient_id'];
      $_GET['patient_id']            = $_POST['patient_id'];
      $_POST['destination']          = $data[3];

      $cdoc = new C_Document();      
      $cdoc->upload_action_process();
      if($GLOBALS['document_storage_method']==0){
	if($data[3])
	  copy($file_path,$cdoc->file_path.$data[3]);
	else
	  copy($file_path,$cdoc->file_path.$data[6]);
      }
      $foreign_id = sqlQuery("select id from documents where foreign_id = ? order by id desc limit 1",array($_POST['new_patient_id']));
      unset($_POST);
      $_POST['encrypted']  = '';
      $_POST['passphrase'] = '';
      $_POST['process']    = true;
      $_POST['foreign_id'] = $foreign_id['id'];
      $_POST['note']       = $data[7];
      $cdoc->note_action_process($_GET['patient_id']);
      $sql_patient_no = "UPDATE documents_legal_detail SET dld_moved = '1' WHERE dld_master_docid = ? AND dld_id = ?";
      sqlQuery($sql_patient_no,array($data[2],$data[8]));
      unset($_POST);      
    }
  }  
 
/** 
* To display the files/pdfforms patient wise
*/
  public function userslistportal($data){
    if($this->valid($data[0])){
      ob_start();
      $query   = "SELECT
                    dlm.dlm_upload_type,
                    dld.dld_id,
                    dld.dld_pid,
                    dlm.dlm_document_name,
                    dlm.dlm_document_id,
                    dlm.dlm_filename,
                    dld.dld_filename,
                    dld.dld_signed,
                    dlm.dlm_filename,
                    dld.dld_master_docid,
                    dld.dld_signed,
                    dld.dld_patient_comments,
                    dld.dld_moved,  
                    pd.fname,
                    pd.lname,
                    pd.mname
                  FROM
                    documents_legal_master AS dlm 
                    LEFT OUTER JOIN documents_legal_detail AS dld 
                      ON dlm.dlm_document_id = dld_master_docid 
                    JOIN patient_data AS pd 
                      ON dld.dld_pid = pd.pid 
                  WHERE dlm.dlm_effective_date <= NOW() 
                    AND dlm.dlm_effective_date <> '0000-00-00 00:00:00' 
                    AND dld.dld_id IS NOT NULL 
                    AND dld.dld_signed IN (1,2,4) 
                    AND dld.dld_moved = 0 
                  ORDER BY dld.dld_pid ASC ";
		  
      $res = sqlStatement($query);
      if ($res) {
	for($iter=0; $row=sqlFetchArray($res); $iter++) {
	    $all[$iter] = $row;
	}
      } 
      $v = ob_get_clean();
      return $all;
    }
  }

/**  
* To display the category list in Move To Category option
*/
  public function category_list($data){
    if($this->valid($data[0])){
      ob_start();
	$query = "SELECT * FROM categories";
	$res = sqlStatement($query);
      if ($res) {
	for($iter=0; $row=sqlFetchArray($res); $iter++) {
	    $all[$iter] = $row;
	}
      }       
      $v = ob_get_clean();
      return $all;
    }
  }   
    
//Converts a text to xml format.Format is as follows
  public function text_to_xml($data){
	if($this->valid($data[0])){
	 $text = $data[1];	
	 $doc = new DOMDocument();
	 $doc->formatOutput = true;
	 
	 $root = $doc->createElement( "root" );
	 $doc->appendChild( $root );
	
	 $level = $doc->createElement( "level" );
	 $root->appendChild( $level );
	 
	 $element = $doc->createElement( "text" );
	 $element->appendChild(
	   $doc->createTextNode( $text )
	   );
	 $level->appendChild( $element );
	 return $doc->saveXML();
	}
	else{
		throw new SoapFault("Server", "credentials failed");
	}
    }

//Accepts an array and returns the result in xml format.Format is as follows
 

  public function function_return_to_xml($var=array()){
	
	  $doc = new DOMDocument();
	  $doc->formatOutput = true;
	 
	  $root = $doc->createElement( "root" );
	  $doc->appendChild( $root );
	
	 
	   $level = $doc->createElement( "level" );
	   $root->appendChild( $level );
	   foreach($var as $key=>$value){
	   $element = $doc->createElement( "$key" );
	   $element->appendChild(
	       $doc->createTextNode( $value )
	   );
	   $level->appendChild( $element );
	       }
	   
	 return $doc->saveXML();
	
    }
    
   //When a filled PDf is rejected During audit , the file is deleted 


  public function delete_file($data){
	if($this->valid($data[0])){
	 $file_name_with_path=$data[1];
	 @unlink($file_name_with_path);
	}
	else{
		throw new SoapFault("Server", "credentials failed");
	}
    }
        

//Accepts a file path.Fetches the file in xml format.
//Transfer the file to portal in XML format
  public function file_to_xml($data){
	if($this->valid($data[0])){
	   $file_name_with_path=$data[1];
	   $path_parts = pathinfo($file_name_with_path);
	   $handler = fopen($file_name_with_path,"rb");
	   $returnData = fread($handler,filesize($file_name_with_path));
	   fclose($handler);
	   $doc = new DOMDocument();
	   $doc->formatOutput = true;
	   
	   $root = $doc->createElement( "root" );
	   $doc->appendChild( $root );
	
	       $level = $doc->createElement( "level" );
	       $root->appendChild( $level );
	 
	   $filename = $doc->createElement( "name" );
	   $filename->appendChild(
	   $doc->createTextNode( $path_parts['basename'] )
	   );
	   $level->appendChild( $filename );
	   
	   $type = $doc->createElement( "type" );
	   $type->appendChild(
	   $doc->createTextNode( $path_parts['extension'] )
	   );
	   $level->appendChild( $type );
	   $content = $doc->createElement( "file" );
	   $content->appendChild(
	   $doc->createTextNode( base64_encode($returnData) )
	   );
	   $level->appendChild( $content );
	   return $doc->saveXML();
	}
	else{
		throw new SoapFault("Server", "credentials failed");
	}
    }
    
 
 //File teceived from the portal  side is saved to OpenEMR

  public function store_to_file($data){
	if($this->valid($data[0])){
	       $file_name_with_path=$data[1];
	       $data=$data[2];
	       $savedpath=$GLOBALS['OE_SITE_DIR']."/documents/myportal/";
	       if(is_dir($savedpath));
	       else
	       {
		       mkdir($savedpath,0777);
		       chmod($savedpath, 0777);
	       }
	       $savedpath=$GLOBALS['OE_SITE_DIR']."/documents/myportal/unsigned/";
	       if(is_dir($savedpath));
	       else
	       {
		       mkdir($savedpath,0777);
		       chmod($savedpath, 0777);
	       }
	       $savedpath=$GLOBALS['OE_SITE_DIR']."/documents/myportal/signed/";
	       if(is_dir($savedpath));
	       else
	       {
		       mkdir($savedpath,0777);
		       chmod($savedpath, 0777);
	       }
	       $savedpath=$GLOBALS['OE_SITE_DIR']."/documents/myportal/upload/";
	       if(is_dir($savedpath));
	       else
	       {
		       mkdir($savedpath,0777);
		       chmod($savedpath, 0777);
	       }
	   $handler = fopen($file_name_with_path,"w");
	   fwrite($handler, base64_decode($data));
	   fclose($handler);
	       chmod($file_name_with_path,0777);
	}
	else{
		throw new SoapFault("Server", "credentials failed");
	}
	}
	
	
//receive a batch of function calls received from Portal, execute it and return the results to the portal
//The results will be stored in the key, which is received from portal.  

 
static  public function batch_despatch($var,$func,$data_credentials){
	global $pid;
	if(UserService::valid($data_credentials)){
	require_once("../../library/invoice_summary.inc.php");
	require_once("../../library/options.inc.php");
	require_once("../../library/acl.inc");
	require_once("../../library/patient.inc");
	if($func=='ar_responsible_party')
	 {
		$patient_id=$pid;
		$encounter_id=$var['encounter'];
		$x['ar_responsible_party']=ar_responsible_party($patient_id,$encounter_id);
		return UserService::function_return_to_xml($x);
	 }
	elseif($func=='getInsuranceData')
	 {
		$type=$var['type'];
		$given=$var['given'];
		$x=getInsuranceData($pid,$type,$given);
		return UserService::function_return_to_xml($x);
	 }
	elseif($func=='generate_select_list')
	 {
		$tag_name=$var['tag_name'];
		$list_id=$var['list_id'];
		$currvalue=$var['currvalue'];
		$title=$var['title'];
		$empty_name=$var['empty_name'];
		$class=$var['class'];
		$onchange=$var['onchange'];
	        $x['generate_select_list']=generate_select_list($tag_name,$list_id,$currvalue,$title,$empty_name,$class,$onchange);
		return UserService::function_return_to_xml($x);
	 }
	elseif($func=='xl_layout_label')
	 {
		$constant=$var['constant'];
	        $x['xl_layout_label']=xl_layout_label($constant);
		return UserService::function_return_to_xml($x);
	 }
	elseif($func=='generate_form_field')
	 {
		$frow=$var['frow'];
		$currvalue=$var['currvalue'];
	        ob_start();
		generate_form_field($frow,$currvalue);
		$x['generate_form_field']=ob_get_contents();
		ob_end_clean();
		return UserService::function_return_to_xml($x);
	 }
	elseif($func=='getInsuranceProviders')
	 {
		$i=$var['i'];
		$provider=$var['provider'];
		$insurancei=getInsuranceProviders();
	        $x=$insurancei;
		return $x;
	 }
	elseif($func=='get_layout_form_value')
	 {
		$frow=$var['frow'];
		$_POST=$var['post_array'];
		$x['get_layout_form_value']=get_layout_form_value($frow);
		return UserService::function_return_to_xml($x);
	 }
	elseif($func=='updatePatientData')
	 {
		$patient_data=$var['patient_data'];
		$create=$var['create'];
		updatePatientData($pid,$patient_data,$create);
		$x['ok']='ok';
		return UserService::function_return_to_xml($x);
	 }
	elseif($func=='updateEmployerData')
	 {
		$employer_data=$var['employer_data'];
		$create=$var['create'];
		updateEmployerData($pid,$employer_data,$create);
		$x['ok']='ok';
		return UserService::function_return_to_xml($x);
	 }
	elseif($func=='newHistoryData')
	 {
		newHistoryData($pid);
		$x['ok']='ok';
		return UserService::function_return_to_xml($x);
	 }
	elseif($func=='newInsuranceData')
	 {
		$_POST=$var[0];
		foreach($var as $key=>$value)
		 {
			if($key>=3)//first 3 need to be skipped.
			 {
			  $var[$key]=formData($value);
			 }
			if($key>=1)
			 {
			  $parameters[$key]=$var[$key];
			 }
		 }
		$parameters[12]=fixDate($parameters[12]);
		$parameters[27]=fixDate($parameters[27]);
		call_user_func_array('newInsuranceData',$parameters);
		$x['ok']='ok';
		return UserService::function_return_to_xml($x);
	 }
	
	}
	else{
		throw new SoapFault("Server", "credentials failed");
	}
    }
    
  //Writing patient credentials to table
  public function insert_login_details($var)
       {
	global $pid;
	$data_credentials=$var[0];
	if(UserService::valid($data_credentials))
		 {
			$username=$var['username'];
			$authPass=$var['authPass'];
			$query="insert into patient_access_offsite(pid,portal_username,portal_pwd) values (?,?,?)";
			sqlInsert($query,array($pid,$username,$authPass));
		 }
		else
		 {
			throw new SoapFault("Server", "credentials failed");
		 }
	}
   
   

//Updating the password on a password change  

  public function update_password($var){
	      $data_credentials=$var[0];
	      global $pid;
       if(UserService::valid($data_credentials)=='existingpatient' || UserService::valid($data_credentials)=='newpatient'){
	       $status = $var['new_pwd_status'];
	       $pwd=$var['new_pwd'];
	       $oldpwd = $var['old_pwd'];
	       $set = '';
	       $setarray = '';
	       $where = '';
	       if($status)
	       {
	       $where = "portal_pwd_status=? and pid=?";
	       $set= "portal_pwd=?,portal_pwd_status=?";
	       $setarray[]=$pwd;
	       $setarray[]=$status;
	       $setarray[]=0;
	       $setarray[]=$pid;
	       }
	       
	       else
	       { 
		$set= "portal_pwd=? ";
	       $setarray[]=$pwd;
	       $where = " pid=?";
	        $setarray[]=$pid;
	       }
	       $qry = "select * from  patient_access_offsite  WHERE pid=?  AND portal_pwd=?";
	       $res=sqlStatement($qry,array($pid,$oldpwd));
		   if(sqlNumRows($res)>0)
		       {
			   $qry = "UPDATE  patient_access_offsite SET $set WHERE $where";
			   sqlStatement($qry,$setarray);
			   return 'ok';
			}
			else
			 {
			   return 'notok';
			 }
       }
       else{
	       throw new SoapFault("Server", "credentials failed");
       }
    }
    
    //appointment update
    

  public function update_openemr_appointment($var)
       {
	      $data_credentials=$var[0];
	      if(UserService::valid($data_credentials)=='existingpatient' || UserService::valid($data_credentials)=='newpatient'){
		     foreach($var[1] as $key=>$value)
		     {
			    $eid=explode('_',$var[1][$key]);
			    if($eid[0]=='calendar')
			    {
				   sqlQuery("update openemr_postcalendar_events set pc_apptstatus='x' where pc_eid=?",array($eid[1]));
			    }
			    elseif($eid[0]=='audit')
			    {
				   sqlQuery("update audit_master set approval_status='5' where id=?",array($eid[1]));
			    }
		     }
	      }
	      else{
		     throw new SoapFault("Server", "credentials failed");
	      }
       }
       
       
   //Marking the Documents as ready to be signed  

  public function update_dlm_dld($var)
    {
	$data_credentials=$var[0];
       if(UserService::valid($data_credentials)){
	            
	        $qry=" UPDATE  documents_legal_detail set dld_signed=2 where dld_id=?";
	            sqlStatement($qry,array($var['dld_id']));
       }
       else{
	       throw new SoapFault("Server", "credentials failed");
       }
    }
    

//Setting PDF documets approve /denial status

  public function update_dld_approve_deny($data){
      if($this->valid($data[0])){
	$qry = "UPDATE documents_legal_detail SET dld_signed=?,dld_denial_reason=? WHERE dld_id=?";
	sqlStatement($qry,$data[1]);
      }
      else{
	throw new SoapFault("Server", "credentials failed");
      }
    }
    
    //Marking PDF documets as signed

  public function update_dld_signed($data){
      if($this->valid($data[0])){
	$qry = "UPDATE documents_legal_detail SET dld_signed=1,dld_filepath=?,dld_filename=? WHERE dld_id=?";
	sqlStatement($qry,$data[1]);
      }
      else{
	throw new SoapFault("Server", "credentials failed");
      }
    }
    
    //Marking PDF documets for audit.
 
  public function update_dld_pending($data){
      if($this->valid($data[0])){
	$qry = "UPDATE documents_legal_detail SET dld_signed=0,dld_filepath=?,dld_filename=?, dld_file_for_pdf_generation=? WHERE dld_id=?";
	sqlStatement($qry,$data[1]);
      }
      else{
	throw new SoapFault("Server", "credentials failed");
      }
  }
    
  

  public function insert_dld($data){
       global $pid;
       if(UserService::valid($data[0])=='existingpatient' || UserService::valid($data[0])=='newpatient'){
	       sqlInsert("INSERT INTO documents_legal_detail (dld_pid,dld_signed,dld_filepath,dld_master_docid,dld_filename,dld_encounter,dld_file_for_pdf_generation) ".
	       " VALUES (?,?,?,?,?,?,?)",array($pid,$data[2],$data[3],$data[4],$data[5],$data[6],$data[7]));
       }
       else{
	       throw new SoapFault("Server", "credentials failed");
       }
    }
    
    
  //Inserting the entries for Master PDF documents uploaded

  public function insert_dlm($data){
       if($this->valid($data[0])=='oemruser'){
	       sqlStatement("INSERT INTO documents_legal_master(dlm_category, dlm_subcategory,dlm_document_name,dlm_facility,dlm_provider,
	       dlm_filename,dlm_filepath,dlm_effective_date,content) values (?,?,?,?,?,?,?,?,?)",array($data[1],$data[2],$data[3],$data[4],$data[5],$data[6],$data[7],$data[8],$data[9]));
       }
       else{
	       throw new SoapFault("Server", "credentials failed");
       }
    }
    
 
//REceive an array of Select cases from portal execute it and return
// it in the keys received from portal. A batch of queries execute and returns it in one batch.

  public function batch_select($data){
	if($this->valid($data[0])){
		$batch = $data[1];
		foreach($batch as $key=>$value)
		{
		$batchkey=$value['batchkey'];
		$case=$value['case'];
		$param=$value['param'];
		$arrproc[] = $case;
		$arrproc[] = $param;
		$return_array[$batchkey]=$this->selectquery(array($data[0],$arrproc));
		$arrproc=null;
		}
		return $return_array;
	}
	else{
		throw new SoapFault("Server", "credentials failed");
	}
    }
    
  
//Receive a batch of function calls from portal and execute it through batch despatch Function
//Any  OpenEmr function can be executed this way, if necessary if clause is written in batch_despatch.

  public function batch_function($data){
	if($this->valid($data[0])){
		$batch = $data[1];
		foreach($batch as $key=>$value)
		{
		$batchkey=$value['batchkey'];
		$function=$value['funcname'];
		$param=$value['param'];
		$param[]=$data[0];
		$res=call_user_func_array("UserService::$function",$param);
		$return_array[$batchkey]=$res;
		}
		return $return_array;
	}
	else{
		throw new SoapFault("Server", "credentials failed");
	}
    }
 
//Execute a batch of functions received from portal. But this function is limited to
// the functions written in the myportal module. 

  public function multiplecall($data){
         $batch = $data[1];
	 foreach($batch as $key=>$value)
	 {
	 $batchkey=$value['batchkey'];
	 $function=$value['funcname'];
	 $param=$value['param'];
	 if(is_array($param))
	 array_unshift($param,$data[0]);
	 else
	 $param[]=$data[0];
	 $res= UserService::$function($param);
	 $return_array[$batchkey]=$res;
	 }
	 return $return_array;
    }
    
     


  public function getversion($data){
         return '1.2';
    }
    
    
  public function loginchecking($data){
      if($this->valid($data[0])=='existingpatient' || $this->valid($data[0])=='newpatient'){
	$res = sqlStatement("SELECT portal_pwd_status FROM patient_access_offsite WHERE BINARY portal_username=? AND  BINARY portal_pwd=?",$data[1]);
	return $this->resourcetoxml($res);
      }
      
      return false;
    }
    
  //Execute a query and return its results.

  public function selectquery($data){
      //global $pid;
      $sql_result_set='';
      $utype = $this->valid($data[0]);
      if($utype){
      $newobj = factoryclass::dynamic_class_factory($utype);
      $sql_result_setarr = $newobj->query_formation($data[1]);
      $sql_result_set = sqlStatement($sql_result_setarr[0],$sql_result_setarr[1]);
      return $this->resourcetoxml($sql_result_set);
      }
    }
       
//Return an SQL resultset as an XML


  public function resourcetoxml($sql_result_set){
	 $doc = new DOMDocument();
	 $doc->formatOutput = true;
	 
	 $root = $doc->createElement( "root" );
	 $doc->appendChild( $root );
      while($row = sqlFetchArray($sql_result_set))
	 {
	   $level = $doc->createElement( "level" );
	   $root->appendChild( $level );
	   foreach($row as $key=>$value){
	   $element = $doc->createElement( "$key" );
	   $element->appendChild(
	       $doc->createTextNode( $value )
	   );
	   $level->appendChild( $element );
	   }
	 }
	 return $doc->saveXML();
    }
	
  //Writing facility payment configuration to table
  public function save_payment_configuration($var){
	$data_credentials=$var[0];
	if(UserService::valid($data_credentials))
		 {
			if($var['service'] == 'paypal'){
				if($var['paypal'] != ''){
					$update_sql = "UPDATE payment_gateway_details SET login_id = ? WHERE service_name = 'paypal'";
					sqlStatement($update_sql,array($var['login_id']));
				}else{
					$save_sql = "INSERT INTO payment_gateway_details (service_name,login_id) VALUES (?,?)";
					sqlStatement($save_sql,array($var['service'],$var['login_id']));
				}
			}elseif($var['service'] == 'authorize_net'){
				if($var['authorize_net'] != ''){
					$update_sql = "UPDATE payment_gateway_details SET login_id = ?, transaction_key = ?, md5= ? WHERE service_name = 'authorize_net'";
					sqlStatement($update_sql,array($var['login_id'],$var['transaction_key'],$var['md5']));
				}else{
					$save_sql = "INSERT INTO payment_gateway_details (service_name,login_id,transaction_key,md5) VALUES (?,?,?,?)";
					sqlStatement($save_sql,array($var['service'],$var['login_id'],$var['transaction_key'],$var['md5']));
				}
			}
		 }
		else
		 {
			throw new SoapFault("Server", "credentials failed");
		 }
	}
    
 //Writing patient's authorizenet profile id to table
  public function insert_authorizenet_details($var){
	global $pid;
	$data_credentials=$var[0];
	if(UserService::valid($data_credentials))
		 {
			$authorizenetid=$var['authorizenetid'];
			$query="UPDATE patient_access_offsite SET authorize_net_id = ? WHERE pid = ?";
			sqlInsert($query,array($authorizenetid,$pid));
		 }
		else
		 {
			throw new SoapFault("Server", "credentials failed");
		 }
	}

  public function valid($credentials){
	$timminus = date("Y-m-d H:m",(strtotime(date("Y-m-d H:m"))-7200)).":00";
	sqlStatement("DELETE FROM audit_details WHERE audit_master_id IN(SELECT id FROM audit_master WHERE type=5 AND created_time<=?)",array($timminus));
	sqlStatement("DELETE FROM audit_master WHERE type=5 AND created_time<=?",array($timminus));
	global $pid;
	$ok=0;
	$okE=0;
	$okN=0;
	$okO=0;
	$okP=0;
	$tim = strtotime(gmdate("Y-m-d H:m"));
	$res = sqlStatement("SELECT * FROM audit_details WHERE field_value=?",array($credentials[3]));
	if(sqlNumRows($res)){
		if($GLOBALS['validated_offsite_portal'] !=true){
		return false;
		}
	}
	else{
	      $grpID = sqlInsert("INSERT INTO audit_master SET type=5");
	      sqlStatement("INSERT INTO audit_details SET field_value=? , audit_master_id=? ",array($credentials[3],$grpID));
	}
	if(sha1($GLOBALS['portal_offsite_password'].date("Y-m-d H",$tim).$credentials[3])==$credentials[2]){
	      $ok =1;
	}
	elseif(sha1($GLOBALS['portal_offsite_password'].date("Y-m-d H",($tim-3600)).$credentials[3])==$credentials[2]){
	      $ok =1;
	}
	elseif(sha1($GLOBALS['portal_offsite_password'].date("Y-m-d H",($tim+3600)).$credentials[3])==$credentials[2]){
	      $ok =1;
	}
	if(($credentials[1]==$GLOBALS['portal_offsite_username'] && $ok==1 && $GLOBALS['portal_offsite_enable']==1)||$GLOBALS['validated_offsite_portal']==true){
	  $prow = sqlQuery("SELECT * FROM patient_access_offsite WHERE portal_username=?",array($credentials[6]));
		if($credentials[4] == 'existingpatient'){
		  if(UserService::validcredential($credentials)){
		    $okE = 1;
		  }
		  else{
		    return false;
		  }
		}
		elseif($credentials[4] == 'oemruser'){
		  if($credentials[9])
		  $prow = sqlQuery("SELECT pid FROM audit_master WHERE id=?",array($credentials[9]));
		  $okO = 1;
		}
		elseif($credentials[4] == 'newpatient'){
		  if(UserService::validcredential($credentials)){
		    $okN = 2;
		  }
		  else{
		    $okN = 1;
		    $prow = sqlQuery("SELECT IFNULL(MAX(pid)+1,1) AS pid FROM patient_data");
		  }
		}
		if($okE==1 || $okN == 2 || $okN == 1 || $okO == 1){
		  $pid = $prow['pid'];
		  $GLOBALS['pid'] = $prow['pid'];
		}
		$_GET['site'] = $credentials[0];
		if($okE==1){
		  $portal = sqlQuery("SELECT allow_patient_portal FROM patient_data WHERE pid=?",array($pid));
		  if(strtolower($portal['allow_patient_portal'])!='yes')
		  return false;
		}
		$GLOBALS['validated_offsite_portal'] = true;
		if($okO){
		  return 'oemruser';
		}
		elseif($okE){
		  return 'existingpatient';
		}
		elseif($okN){
		  return 'newpatient';
		}
		return false;
	}
	else{
		return false;
	}
    }
    
    
    

  public function validcredential($credentials){
      $tim = strtotime(gmdate("Y-m-d H:m"));
      if($credentials[6]){
      $prow = sqlQuery("SELECT * FROM patient_access_offsite WHERE portal_username=?",array($credentials[6]));
      	if(sha1($prow['portal_pwd'].date("Y-m-d H",$tim).$credentials[8])==$credentials[7]){
	  return true;
	}
	elseif(sha1($prow['portal_pwd'].date("Y-m-d H",($tim-3600)).$credentials[8])==$credentials[7]){
	  return true;
	}
	elseif(sha1($prow['portal_pwd'].date("Y-m-d H",($tim+3600)).$credentials[8])==$credentials[7]){
	  return true;
	}
      }
	return false;
    }
    
       
    //for checking the connection



  public function check_connection($data){
       if($this->valid($data[0])){
	   return 'ok';
       }
       else{
	   return 'notok';
       }
    }
}
$server = new SoapServer(null,array('uri' => "urn://portal/res"));
$server->setClass('UserService');
$server->setPersistence(SOAP_PERSISTENCE_SESSION);
$server->handle();
?>