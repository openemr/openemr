<?php
/* +-----------------------------------------------------------------------------+
*    OpenEMR - Open Source Electronic Medical Record
*    Copyright (C) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU Affero General Public License as
*    published by the Free Software Foundation, either version 3 of the
*    License, or (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU Affero General Public License for more details.
*
*    You should have received a copy of the GNU Affero General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
*    @author  Vinish K <vinish@zhservices.com>
*    @author  Riju K P <rijukp@zhservices.com>
* +------------------------------------------------------------------------------+
*/

namespace Carecoordination\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Application\Model\ApplicationTable;
use Zend\Db\Adapter\Driver\Pdo\Result;
use ZipArchive;

use CouchDB;
use DOMPDF;
require_once(dirname(__FILE__) . "/../../../../../../../../library/classes/CouchDB.class.php");

class EncountermanagerTable extends AbstractTableGateway
{
    public function getEncounters($data,$getCount=null)
    {
		$query_data = array();
        $query  = 	"SELECT pd.fname, pd.lname, pd.mname, date(fe.date) as date, fe.pid, fe.encounter,
                        u.fname as doc_fname, u.mname as doc_mname, u.lname as doc_lname, (select count(encounter) from form_encounter where pid=fe.pid) as enc_count,
                        (SELECT DATE(date) FROM form_encounter WHERE pid=fe.pid ORDER BY date DESC LIMIT 1) as last_visit_date,
						(select count(*) from ccda where pid=fe.pid and transfer=1) as ccda_transfer_count,
						(select count(*) from ccda where pid=fe.pid and transfer=1 and status=1) as ccda_successfull_transfer_count
                        FROM form_encounter AS fe
                        JOIN patient_data AS pd ON pd.pid=fe.pid
                        LEFT JOIN users AS u ON u.id=fe.provider_id ";
				if($data['status']) {
						$query  .= " LEFT JOIN combination_form AS cf ON cf.encounter = fe.encounter ";
				}
				
				$query  .= " WHERE 1=1 ";
				
				if($data['status'] == "signed") {
						$query  .= " AND cf.encounter IS NOT NULL AND cf.encounter !=''";
				}
				
				if($data['status'] == "unsigned") {
						$query  .= " AND (cf.encounter IS  NULL OR cf.encounter ='')";
				}
				
				if($data['from_date'] && $data['to_date']) {
						$query .= " AND fe.date BETWEEN ? AND ? ";
						$query_data[] = $data['from_date'];
						$query_data[] = $data['to_date'];
				}
				
				if($data['pid']) {
						$query .= " AND (fe.pid = ? OR pd.fname like ? OR pd.mname like ? OR pd.lname like ? OR CONCAT_WS(' ',pd.fname,pd.lname) like ?) ";
						$query_data[] = $data['pid'];
						$query_data[] = "%".$data['pid']."%";
						$query_data[] = "%".$data['pid']."%";
						$query_data[] = "%".$data['pid']."%";
						$query_data[] = "%".$data['pid']."%";
				}
				
				if($data['encounter']) {
						$query .= " AND fe.encounter = ? ";
						$query_data[] = $data['encounter'];
				}
				
				$query .= " GROUP BY fe.pid ";
				
				$query .= " ORDER BY fe.pid, fe.date ";

				$appTable   = new ApplicationTable();
				
				if($getCount){
						$res        = $appTable->zQuery($query, $query_data);
						$resCount 	= $res->count();
						return $resCount;
				}
						
				$query 		 .= " LIMIT " . \Application\Plugin\CommonPlugin::escapeLimit($data['limit_start']) . "," . \Application\Plugin\CommonPlugin::escapeLimit($data['results']);
				$resDetails = $appTable->zQuery($query, $query_data);
        return $resDetails;
    }
    
    public function getStatus($data)
    {		
		foreach($data as $row){
			if($pid) $pid .= ',';
			$pid 	.= $row['pid'];
		}
		if(!$pid) $pid = "''";
		$query 	    = "SELECT cc.*, DATE(fe.date) AS dos, CONCAT_WS(' ',u.fname, u.mname, u.lname) AS user_name FROM ccda AS cc
				LEFT JOIN form_encounter AS fe ON fe. pid = cc.pid AND fe.encounter = cc.encounter
				LEFT JOIN users AS u ON u.id = cc.user_id
				WHERE cc.pid in (?) ORDER BY cc.pid, cc.time desc";
		$appTable   = new ApplicationTable();
		$result     = $appTable->zQuery($query, array($pid));
		return $result;
    }
    
    public function convert_to_yyyymmdd($date)
    {
        $date = str_replace('/','-',$date);
        $arr = explode('-',$date);
        $formatted_date = $arr[2]."-".$arr[0]."-".$arr[1];
        return $formatted_date;
    }
    
    /*
    * Convert date from database format to required format
    *
    * @param	String		$date		Date from database (format: YYYY-MM-DD)
    * @param	String		$format		Required date format
    *
    * @return	String		$formatted_date	New formatted date
    */
    public function date_format($date, $format)
    {
	if(!$date) return;
	$format = $format ? $format : 'm/d/y';	
	$temp = explode(' ',$date); //split using space and consider the first portion, incase of date with time
	$date = $temp[0];
        $date = str_replace('/','-',$date);
        $arr = explode('-',$date);
	
	if($format == 'm/d/y'){
	    $formatted_date = $arr[1]."/".$arr[2]."/".$arr[0];
	}
	$formatted_date = $temp[1] ? $formatted_date." ".$temp[1] : $formatted_date; //append the time, if exists, with the new formatted date
        return $formatted_date;
    }
    
    public function getFile($id)
    {
		$query 	    = "select couch_docid, couch_revid, ccda_data from ccda where id=?";
		$appTable   = new ApplicationTable();
		$result     = $appTable->zQuery($query, array($id));
		foreach($result as $row){
			if($row['couch_docid'] != ''){
				$couch 	 = new CouchDB();
				$data 	 = array($GLOBALS['couchdb_dbase'], $row['couch_docid']);
				$resp 	 = $couch->retrieve_doc($data);
				$content = base64_decode($resp->data);
			}
			else if(!$row['couch_docid']){
				$fccda 	 = fopen($row['ccda_data'], "r");		
				$content = fread($fccda, filesize($row['ccda_data']));
				fclose($fccda);
			}
			else{
				$content = $row['ccda_data'];
			}
			return $content;
		}
    }
	
	/*
     * Connect to a phiMail Direct Messaging server and transmit
     * a CCDA document to the specified recipient. If the message is accepted by the
     * server, the script will return "SUCCESS", otherwise it will return an error msg. 
     * @param DOMDocument ccd the xml data to transmit, a CCDA document is assumed
     * @param string recipient the Direct Address of the recipient
     * @param string requested_by user | patient
     * @return string result of operation
     */
    public function transmitCCD($data  = array()) 
    {   
        $appTable         = new ApplicationTable();
        $ccda_combination = $data['ccda_combination'];
        $recipients       = $data['recipients'];
        $xml_type         = $data['xml_type'];
        $rec_arr          = explode(";",$recipients);
        $d_Address        = '';
        foreach($rec_arr as $recipient) {
          $config_err = "Direct messaging is currently unavailable."." EC:";
          if ($GLOBALS['phimail_enable']==false) return("$config_err 1");
          $fp = \Application\Plugin\Phimail::phimail_connect($err);
          if ($fp===false) return("$config_err $err");
          $phimail_username = $GLOBALS['phimail_username'];
          $phimail_password = $GLOBALS['phimail_password'];
          $ret = \Application\Plugin\Phimail::phimail_write_expect_OK($fp,"AUTH $phimail_username $phimail_password\n");
          if($ret!==TRUE) return("$config_err 4");
            $ret = \Application\Plugin\Phimail::phimail_write_expect_OK($fp,"TO $recipient\n");
            if($ret!==TRUE) {//return("Delivery is not allowed to the specified Direct Address.") ;
              $d_Address.= ' '.$recipient;
              continue;
            }
            $ret=fgets($fp,1024); //ignore extra server data
            if($requested_by=="patient") {
              $text_out = "Delivery of the attached clinical document was requested by the patient";
            }
            else {
              if(strpos($ccda_combination,'|') !== false) {
                $text_out = "Clinical documents are attached.";
              }
              else {
                $text_out = "A clinical document is attached";
              }
            }
            $text_len=strlen($text_out);
            \Application\Plugin\Phimail::phimail_write($fp,"TEXT $text_len\n");
            $ret=@fgets($fp,256);
            if($ret!="BEGIN\n") {
              \Application\Plugin\Phimail::phimail_close($fp);
              //return("$config_err 5");
              $d_Address.= ' '.$recipient;
              continue;
            }
            $ret=\Application\Plugin\Phimail::phimail_write_expect_OK($fp,$text_out);
            if($ret!==TRUE) {
              //return("$config_err 6");
              $d_Address.= $recipient;
              continue;
            }
            $arr = explode('|',$ccda_combination);
            foreach($arr as $value) {
              $query  = "SELECT id FROM  ccda WHERE pid = ? ORDER BY id DESC LIMIT 1";
              $result = $appTable->zQuery($query, array($value));
              foreach($result as $val) {
                $ccda_id = $val['id'];
              }
              $ccda = $this->getFile($ccda_id);

              $xml = simplexml_load_string($ccda);
              $xsl = new \DOMDocument;
              $xsl->load(dirname(__FILE__).'/../../../../../public/xsl/ccda.xsl');
              $proc = new \XSLTProcessor;
              $proc->importStyleSheet($xsl); // attach the xsl rules
              $outputFile = sys_get_temp_dir() . '/out_'.time().'.html';
              $proc->transformToURI($xml, $outputFile);
              $htmlContent = file_get_contents($outputFile);
              if($xml_type == 'html') {
                $ccda_file =  htmlspecialchars_decode($htmlContent);
              }
              elseif($xml_type == 'pdf') {
                $dompdf = new DOMPDF();
                $dompdf->load_html($htmlContent);
                $dompdf->render();
                //$dompdf->stream();
                $ccda_file = $dompdf->output();
              }
              elseif($xml_type == 'xml') {
                $ccda_file = $ccda;
              }
               //get patient name in Last_First format (used for CCDA filename)
               $sql    = "SELECT pid, id, lname, fname, mname, providerID, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS FROM patient_data WHERE pid = ?";
               $result = $appTable->zQuery($sql, array($value));
               foreach($result as $val) {
                 $patientData[0] = $val;
               }
               if (empty($patientData[0]['lname'])) {
                  $att_filename = "";
                  $patientName2 = "";
               } 
               else {
                  //spaces are the argument delimiter for the phiMail API calls and must be removed
                  $extension = $xml_type == 'CCDA' ? 'xml' : strtolower($xml_type);
                  $att_filename = " " . str_replace(" ", "_", $xml_type . "_" . $patientData[0]['lname']  . "_" . $patientData[0]['fname']) . "." . $extension;
                  $patientName2 = $patientData[0]['fname'] . " " . $patientData[0]['lname'];
               }
               if(strtolower($xml_type) == 'xml') {  
                   $ccda     = simplexml_load_string($ccda_file);
                   $ccda_out = $ccda->saveXml();
                   $ccda_len = strlen($ccda_out);
                   \Application\Plugin\Phimail::phimail_write($fp,"ADD " . ($xml_type=="CCR" ? $xml_type . ' ' : "CDA ") . $ccda_len . $att_filename . "\n");
               } 
               else if(strtolower($xml_type) == 'html' || strtolower($xml_type) == 'pdf') {
                   $ccda_out = $ccda_file;
                   $message_length = strlen($ccda_out);
                   $add_type = (strtolower($xml_type) == 'html') ? 'TEXT' : 'RAW';
                   \Application\Plugin\Phimail::phimail_write($fp, "ADD " . $add_type . " " . $message_length . "" . $att_filename . "\n");
               }
               $ret=fgets($fp,256);
               if($ret!="BEGIN\n") {
                   \Application\Plugin\Phimail::phimail_close($fp);
                   //return("$config_err 7");
                   $d_Address.= ' '.$recipient;
                   continue;
               }
               $ret=\Application\Plugin\Phimail::phimail_write_expect_OK($fp,$ccda_out);
              }
            if($ret!==TRUE) {
//              return("$config_err 8");
              $d_Address.= ' '.$recipient;
              continue;
            }
            \Application\Plugin\Phimail::phimail_write($fp,"SEND\n");
            $ret=fgets($fp,256);
	    //"INSERT INTO `amc_misc_data` (`amc_id`,`pid`,`map_category`,`map_id`,`date_created`) VALUES(?,?,?,?,NOW())"
            \Application\Plugin\Phimail::phimail_close($fp);
           }
           if($d_Address == '') {
            return("Successfully Sent");
           }
           else {
             return("Delivery is not allowed to:".$d_Address);
           }
     } 
    public function getFileID($pid)
    {
      $appTable = new ApplicationTable();
      $query    = "SELECT id FROM ccda
                   WHERE pid=?
                   ORDER BY id DESC LIMIT 1";
      $res      = $appTable->zQuery($query,array($pid));
      $res_cur  = $res->current();
      return $res_cur['id'];
    }
    
    /*
    * Save new user with abook type emr_direct
    *
    * @param	String		first name
    * @param	String		last name
    * @param	String		direct address
    *
    */
    public function AddNewUSer($data  = array()) 
    {
      $fname          = $data['fname'];
      $lname          = $data['lname'];
      $direct_address = $data['direct_address'];
      $appTable       = new ApplicationTable();
      $query          = "INSERT INTO users SET username = ? ,password = ? ,authorized = ?,fname = ?,lname = ?,email = ?,active = ?,abook_type = ?";
      $appTable->zQuery($query,array('','',0,$fname,$lname,$direct_address,1,'emr_direct'));
    }
}
?>