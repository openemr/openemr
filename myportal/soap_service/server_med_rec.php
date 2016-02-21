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

require_once("server_audit.php");
class Userforms extends UserAudit{



  public function issue_type($data){
	if(UserService::valid($data[0])=='existingpatient'){
	global $ISSUE_TYPES;
	require_once("../../library/lists.inc");
	return $ISSUE_TYPES;
	}
	else{
		throw new SoapFault("Server", "credentials failed");
	}
    }
      


  public function print_report($data){
	global $pid;
	if(UserService::valid($data[0])=='existingpatient'){
	$repArr = $data[1];
	$type = $data[3];
	global $ISSUE_TYPES;
	require_once("../../library/forms.inc");
	require_once("../../library/billing.inc");
	require_once("../../library/pnotes.inc");
	require_once("../../library/patient.inc");
	require_once("../../library/options.inc.php");
	require_once("../../library/acl.inc");
	require_once("../../library/lists.inc");
	require_once("../../library/report.inc");
	require_once("../../library/classes/Document.class.php");
	require_once("../../library/classes/Note.class.php");
	require_once("../../library/formatting.inc.php");
	require_once("../../custom/code_types.inc.php");
	     foreach($repArr as $value){
		    ob_start();
		    if($type=="profile"){
		    $this->getIncudes($value);
		    $out .= ob_get_clean();
		    }
		    else{
		    if($type=='issue')
		    $this->getIid($value);
		    if($type=='forms')
		    $this->getforms($value);
		    $out .= ob_get_clean();
		    }
		    
	     }
       return $out;
	}
	else{
		throw new SoapFault("Server", "credentials failed");
	}
    }
    
    


  public function print_ccr_report($data){
	if(UserService::valid($data[0])=='existingpatient'){
	$ccraction = $data[1];
	$raw = $data[2];
	require_once("../../ccr/createCCR.php");
	      ob_start();
	      createCCR($ccraction,$raw);
		      $html = ob_get_clean();
		      if($ccraction=='viewccd')
		      {
		      
		      $html = preg_replace('/<!DOCTYPE html PUBLIC "-\/\/W3C\/\/DTD HTML 4.01\/\/EN" "http:\/\/www.w3.org\/TR\/html4\/strict.dtd">/','',$html);
		      $pos1 = strpos($html,'body {');
		      $pos2 = strpos($html,'.h1center');
		      $tes = substr("$html",$pos1,($pos2-$pos1));
		      $html = str_replace($tes,'',$html);
		      $html = str_replace('h3>','h2>',$html);
		      $html = base64_encode($html);
		      }
		      else{
		      $pos1 = strpos($html,'*{');
		      $pos2 = strpos($html,'h1');
		      $tes = substr("$html",$pos1,($pos2-$pos1));
		      $html = str_replace($tes,'',$html);
		      }
	return $html;
	}
	else{
		throw new SoapFault("Server", "credentials failed");
	}
    }
    
    //Return the forms requested from Portal.
    
    private function getforms($fId){
	global $pid;
	$GLOBALS['pid'] = $pid;
	$inclookupres = sqlStatement("SELECT DISTINCT formdir FROM forms WHERE pid = ? AND deleted=0",array($pid));
	while($result = sqlFetchArray($inclookupres)) {
	    $formdir = $result['formdir'];
	    if (substr($formdir,0,3) == 'LBF')
	      include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");
	    else
	      include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
	}
	$N = 6;
	$inclookupres = sqlStatement("SELECT encounter,form_id,formdir,id FROM forms WHERE pid = ? AND deleted=0
				     AND id =? ",array($pid,$fId));
	while($result = sqlFetchArray($inclookupres)) {
	    $form_encounter=$result['encounter'];
	    $form_id=$result['form_id'];
	    $formdir = $result['formdir'];
	    $id=$result['id'];
	    ob_start();
	    if (substr($formdir,0,3) == 'LBF')
	      call_user_func("lbf_report", $pid, $form_encounter, $N, $form_id, $formdir);
	    else
	      call_user_func($formdir . "_report", $pid, $form_encounter, $N, $form_id);
	    $out=ob_get_clean();
	    ?>	<table>
		<tr class=text>
		    <th><?php echo htmlspecialchars($formdir,ENT_QUOTES);?></th>
		</tr>
		</table>
		    <?php echo $out;?>
	    <?php
	}
    }
    
    
    
    private function getIid($val){
	global $pid;
	global $ISSUE_TYPES;
	$inclookupres = sqlStatement("SELECT DISTINCT formdir FROM forms WHERE pid = ? AND deleted=?",array($pid,0));
	while($result = sqlFetchArray($inclookupres)) {
	    $formdir = $result['formdir'];
	    if (substr($formdir,0,3) == 'LBF')
	      include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");
	    else
	      include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
	}
	    ?>
	    <tr class=text>
		<td></td>
		<td>
	    <?php
	    $irow = sqlQuery("SELECT type, title, comments, diagnosis FROM lists WHERE id =? ",array($val));
	    $diagnosis = $irow['diagnosis'];
	    
	    if ($prevIssueType != $irow['type'])
	    {
		$disptype = $ISSUE_TYPES[$irow['type']][0];
		?>
		<div class='issue_type' style='font-weight: bold;'><?php echo htmlspecialchars($disptype,ENT_QUOTES);?>:</div>
		<?php
		$prevIssueType = $irow['type'];
	    }
	    ?>
	    <div class='text issue'>
	    <span class='issue_title'><?php echo htmlspecialchars($irow['title'],ENT_QUOTES);?>:</span>
	    <span class='issue_comments'><?php echo htmlspecialchars($irow['comments'],ENT_QUOTES);?></span>
	    <?php
	    if ($diagnosis)
	    {
		?>
		<div class='text issue_diag'>
		<span class='bold'>[<?php echo htmlspecialchars(xl('Diagnosis'),ENT_QUOTES);?>]</span><br>
		<?php
		$dcodes = explode(";", $diagnosis);
		foreach ($dcodes as $dcode)
		{
		    ?>
		    <span class='italic'><?php echo htmlspecialchars($dcode,ENT_QUOTES);?></span>:
		    <?php
		    echo htmlspecialchars(lookup_code_descriptions($dcode),ENT_QUOTES);
		    ?>
		    <br>
		    <?php
		}
		?>
		</div>
		<?php
	    }
	    if ($irow['type'] == 'ippf_gcac')
	    {
		?>
		<table>
		<?php
		display_layout_rows('GCA', sqlQuery("SELECT * FROM lists_ippf_gcac WHERE id = ?",array($rowid)));
		?>
    
		</table>
		<?php
	    }
	    else if ($irow['type'] == 'contraceptive')
	    {
		?>
		<table>
		    <?php
		display_layout_rows('CON', sqlQuery("SELECT * FROM lists_ippf_con WHERE id = ?",array($rowid)));
		?>
		</table>
		<?php
	    }                    
	   ?>
	    </div>
	    <?php
	    ?>                            
		</td>
	    <?php                        

    }
    
    
    
    private function getIncudes($val){
	global $pid;
	if ($val == "demographics")
	{
	    ?>
	    <hr />
	    <div class='text demographics' id='DEM'>
	    <?php
	    // printRecDataOne($patient_data_array, getRecPatientData ($pid), $N);
	    $result1 = getPatientData($pid);
	    $result2 = getEmployerData($pid);
	    ?>
	    <table>
	    <tr><td><h6><?php echo htmlspecialchars(xl('Patient Data').":",ENT_QUOTES);?></h6></td></tr>
	    <?php
	    display_layout_rows('DEM', $result1, $result2);
	    ?>
	    </table>
	    </div>
	    <?php
	}
	elseif ($val == "history")
	{
	    ?>
	    <hr />
	    <div class='text history' id='HIS'>
		<?php
		$result1 = getHistoryData($pid);
		?>
		<table>
		<tr><td><h6><?php echo htmlspecialchars(xl('History Data').":",ENT_QUOTES);?></h6></td></tr>
		<?php
		display_layout_rows('HIS', $result1);
		?>
		</table>
		</div>
	<?php
	}
	elseif ($val == "insurance")
	{
	    ?>
	    <hr />
	    <div class='text insurance'>";
	    <h6><?php echo htmlspecialchars(xl('Insurance Data').":",ENT_QUOTES);?></h6>
	    <br><span class=bold><?php echo htmlspecialchars(xl('Primary Insurance Data').":",ENT_QUOTES);?></span><br>
	    <?php
	    printRecDataOne($insurance_data_array, getRecInsuranceData ($pid,"primary"), $N);
	    ?>
	    <span class=bold><?php echo htmlspecialchars(xl('Secondary Insurance Data').":",ENT_QUOTES);?></span><br>
	    <?php
	    printRecDataOne($insurance_data_array, getRecInsuranceData ($pid,"secondary"), $N);
	    ?>
	    <span class=bold><?php echo htmlspecialchars(xl('Tertiary Insurance Data').":",ENT_QUOTES);?></span><br>
	    <?php
	    printRecDataOne($insurance_data_array, getRecInsuranceData ($pid,"tertiary"), $N);
	    ?>
	    </div>
	    <?php
	}
	elseif ($val == "billing")
	{
	    ?>
	    <hr />
	    <div class='text billing'>
	    <h6><?php echo htmlspecialchars(xl('Billing Information').":",ENT_QUOTES);?></h6>
	    <?php
	    if (count($ar['newpatient']) > 0) {
		$billings = array();
		?>
		<table>
		<tr><td width='400' class='bold'><?php echo htmlspecialchars(xl('Code'),ENT_QUOTES);?></td><td class='bold'><?php echo htmlspecialchars(xl('Fee'),ENT_QUOTES);?></td></tr>
		<?php
		$total = 0.00;
		$copays = 0.00;
		foreach ($ar['newpatient'] as $be) {
		    $ta = split(":",$be);
		    $billing = getPatientBillingEncounter($pid,$ta[1]);
		    $billings[] = $billing;
		    foreach ($billing as $b) {
			?>
			<tr>
			<td class=text>
			<?php
			echo htmlspecialchars($b['code_type'],ENT_QUOTES) . ":\t" .htmlspecialchars( $b['code'],ENT_QUOTES) . "&nbsp;". htmlspecialchars($b['modifier'],ENT_QUOTES) . "&nbsp;&nbsp;&nbsp;" . htmlspecialchars($b['code_text'],ENT_QUOTES) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			?>
			</td>
			<td class=text>
			<?php
			echo htmlspecialchars(oeFormatMoney($b['fee']),ENT_QUOTES);
			?>
			</td>
			</tr>
			<?php
			$total += $b['fee'];
			if ($b['code_type'] == "COPAY") {
			    $copays += $b['fee'];
			}
		    }
		}
		echo "<tr><td>&nbsp;</td></tr>";
		echo "<tr><td class=bold>".htmlspecialchars(xl('Sub-Total'),ENT_QUOTES)."</td><td class=text>" . htmlspecialchars(oeFormatMoney($total + abs($copays)),ENT_QUOTES) . "</td></tr>";
		echo "<tr><td class=bold>".htmlspecialchars(xl('Paid'),ENT_QUOTES)."</td><td class=text>" . htmlspecialchars(oeFormatMoney(abs($copays)),ENT_QUOTES) . "</td></tr>";
		echo "<tr><td class=bold>".htmlspecialchars(xl('Total'),ENT_QUOTES)."</td><td class=text>" .htmlspecialchars(oeFormatMoney($total),ENT_QUOTES) . "</td></tr>";
		echo "</table>";
		echo "<pre>";
		//print_r($billings);
		echo "</pre>";
	    } else {
		printPatientBilling($pid);
	    }
	    echo "</div>\n"; // end of billing DIV
	}
	elseif ($val == "immunizations")
	{
	   
		?>
		<hr />
		<div class='text immunizations'>
		<h6><?php echo htmlspecialchars(xl('Patient Immunization').":",ENT_QUOTES);?></h6>
		<?php
		$sql = "select i1.immunization_id as immunization_id, if(i1.administered_date,concat(i1.administered_date,' - ') ,substring(i1.note,1,20) ) as immunization_data from immunizations i1 where i1.patient_id = ? order by administered_date desc";
		$result = sqlStatement($sql,array($pid));
		while ($row=sqlFetchArray($result)) {
		    echo htmlspecialchars($row{'immunization_data'},ENT_QUOTES);
		    echo generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $row['immunization_id']);
		    ?>
		      <br>
		    <?php
		}
		?>
		</div>
		<?php
	   
	}
	elseif ($val == "batchcom")
	{
	    ?>
	    <hr />
	    <div class='text transactions'>
	    <h6><?php htmlspecialchars(xl('Patient Communication sent').":",ENT_QUOTES);?></h6>
	    <?php
	    $sql="SELECT concat( 'Messsage Type: ', batchcom.msg_type, ', Message Subject: ', batchcom.msg_subject, ', Sent on:', batchcom.msg_date_sent ) AS batchcom_data, batchcom.msg_text, concat( users.fname, users.lname ) AS user_name FROM `batchcom` JOIN `users` ON users.id = batchcom.sent_by WHERE batchcom.patient_id=?";
	    $result = sqlStatement($sql,array($pid));
	    while ($row=sqlFetchArray($result)) {
		echo htmlspecialchars($row{'batchcom_data'}.", ".xl('By').": ".$row{'user_name'},ENT_QUOTES);
		?>
		<br><?php echo htmlspecialchars(xl('Text'),ENT_QUOTES);?>:<br><?php echo htmlspecialchars($row{'msg_txt'},ENT_QUOTES);?><br>
		<?php
	    }
	    ?>
	    </div>
	    <?php
	}
	elseif ($val == "notes")
	{
	    ?>
	    <hr />
	    <div class='text notes'>
	    <h6><?php echo htmlspecialchars(xl('Patient Notes').":",ENT_QUOTES);?></h6>
	    <?php
	    printPatientNotes($pid);
	    ?>
	    </div>
	    <?php
	}
	elseif ($val == "transactions")
	{
	    ?>
	    <hr />
	    <div class='text transactions'>
	    <h6><?php echo htmlspecialchars(xl('Patient Transactions').":",ENT_QUOTES);?></h6>
	    <?php
	    printPatientTransactions($pid);
	    ?>
	    </div>
	    <?php
	}
    }
	
	/**
     * Method to fetch CCDA
     * @param type $data
     * @return type
     */
    public function ccdaFetching($data)
    {
	  global $pid;
	  global $server_url;
        
	  if (UserService::valid($data[0])=='existingpatient') {
		if ($this->checkModuleInstalled($moduleName = 'Carecoordination')) {
		  $site_id = $data[0][0];
		  try {
			  $ch = curl_init();
			  $url =  $server_url . "/interface/modules/zend_modules/public/encounterccdadispatch/index?cron=1&pid=$pid&site=$site_id";

			  curl_setopt($ch, CURLOPT_URL, $url);
			  curl_setopt($ch, CURLOPT_COOKIEFILE, "cookiefile");
			  curl_setopt($ch, CURLOPT_COOKIEJAR, "cookiefile");
			  curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
			  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			  $result = curl_exec($ch) or die(curl_error($ch));
			  curl_close($ch);
		  }
		  catch (Exception $e) {

		  }

		  try {
			  $event = isset ($data['event']) ? $data['event'] : 'patient-record';
			  $menu_item = isset($data['menu_item']) ? $data['menu_item'] : 'Dashboard';
			  newEvent($event, 1, '', 1, '', $pid,$log_from = 'patient-portal', $menu_item  );
		  }catch (Exception $e) {

		  }
		  return $result;
		}
		else {
		  return '<?xml version="1.0" encoding="UTF-8"?>
			<note>
			<heading>WARNING!</heading>
			<body>Unable to fetch CCDA Carecoordination module not installed!</body>
			</note>';
		}
	  }
	  else {
		return '<?xml version="1.0" encoding="UTF-8"?>
			<note>
			<heading>WARNING!</heading>
			<body>Existing patient checking failed!</body>
			</note>';
	  }
	  return '<?xml version="1.0" encoding="UTF-8"?>
		  <note>
		  <heading>WARNING!</heading>
		  <body>Un known error occured</body>
		  </note>';
    }
    
    public function checkModuleInstalled($moduleName  = 'Carecoordination')
    {
	  $sql = "SELECT mod_id FROM modules WHERE mod_name = ? AND mod_active = '1'";
	  $res = sqlStatement($sql, array($moduleName));
	  $row = sqlFetchArray($res);   
	  return !empty($row);
    }
    
	/**
    * @param mysql_resource - $inputArray - mysql query result
    * @param string - $rootElementName - root element name
    * @param string - $childElementName - child element name
    */
	public function arrayToXml($inputArray, $rootElementName = 'root', $childElementName = 'RowItem')
	{ 
	  $xmlData = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?>\n"; 
	  $xmlData .= "<" . $rootElementName . ">";
	  foreach ($inputArray as $rowItem) {
		$xmlData .= "<" . $childElementName . ">";
		foreach($rowItem as $fieldName => $fieldValue) {
		  $xmlData .= "<" . $fieldName . ">";
		  $xmlData .= !empty($fieldValue) ? $fieldValue : "null";
		  $xmlData .= "</" . $fieldName . ">";
		}
		$xmlData .= "</" . $childElementName . ">";
	  }
	  $xmlData .= "</" . $rootElementName . ">"; 

      return $xmlData; 
	}
   
	/**
    * 
    * @param type $data
    * @return type
    */
    public function getEventLog($data)
    {
      global $pid;
	  if (UserService::valid($data[0])=='existingpatient') {
		$date1 = $data['start_date'];
		$date2 = $data['end_date'];
		$keyword = $data['keyword'];
		$arrBinds = array();
		$cols = "DISTINCT log.date, event, user, groupname, patient_id, success, comments,checksum,crt_user";
		$sql = "SELECT $cols, CONCAT(fname, ' ', lname) as patient_ful_name, patient_portal_menu.`menu_name`, 
            patient_portal_menu_group.`menu_group_name`, ccda_doc_id FROM log 
			JOIN patient_data ON log.patient_id = patient_data.pid
			JOIN patient_access_offsite ON log.patient_id = patient_access_offsite.pid
			JOIN patient_portal_menu ON patient_portal_menu.`patient_portal_menu_id` = log.menu_item_id
			JOIN patient_portal_menu_group ON patient_portal_menu_group.`patient_portal_menu_group_id` = patient_portal_menu.`patient_portal_menu_group_id`
			WHERE log.date >= ? AND log.date <= ?";
		  
		$sql .= " AND log_from = 'patient-portal'";
		$sql .= " AND patient_id = ?";
		$arrBinds = array($date1  . ' 00:00:00', $date2 . ' 23:59:59', $pid);
		if(!empty($keyword)) {
		  $sql .= " AND (log.date LIKE ?
					  OR LOWER(event) LIKE ?
					  OR LOWER(user) LIKE ?
						  OR LOWER(CONCAT(fname, ' ', lname)) LIKE ? 
					  OR LOWER(groupname) LIKE ?  
					  OR LOWER(comments) LIKE ?
					  OR LOWER(user) LIKE ?
					  ) ";
		  $arrBinds[] = '%' . $keyword . '%' ;
		  $arrBinds[] = '%' . strtolower($keyword) . '%';
		  $arrBinds[] = '%' . strtolower($keyword) . '%';
		  $arrBinds[] = '%' . strtolower($keyword) . '%';
		  $arrBinds[] = '%' . strtolower($keyword) . '%';
		  $arrBinds[] = '%' . strtolower($keyword) . '%';
		  $arrBinds[] = '%' . strtolower($keyword) . '%';
		}
		$sql .= "  ORDER BY date DESC LIMIT 5000";
		
		$res = sqlStatement($sql, $arrBinds);                
		$all = array();
		for($iter=0; $row=sqlFetchArray($res); $iter++) {
		  $all[$iter] = $row;
		}

		$responseString = $this->arrayToXml($all );

		return $responseString;
	  }
    }
    
    /*
     * Connect to a phiMail Direct Messaging server and transmit
     * a CCD document to the specified recipient. If the message is accepted by the
     * server, the script will return "SUCCESS", otherwise it will return an error msg. 
     * @param DOMDocument ccd the xml data to transmit, a CCDA document is assumed
     * @param string recipient the Direct Address of the recipient
     * @param string requested_by user | patient
     * @return string result of operation
     */
    function transmitCCD($data  = array()) { 
        $ccd = $data['ccd'];
        $recipient =  $data['recipient'];
        $requested_by = $data['requested_by'];
        $xml_type = $data['xml_type'];
       
        if (UserService::valid($data[0])=='existingpatient') {
                        
        try {
            $_SESSION['authProvider'] = 1;
            global $pid;
            //get patient name in Last_First format (used for CCDA filename) and
            //First Last for the message text.
            $patientData = getPatientPID(array("pid"=>$pid));
            if (empty($patientData[0]['lname'])) {
               $att_filename = "";
               $patientName2 = "";
            } else {
               //spaces are the argument delimiter for the phiMail API calls and must be removed
               $extension = $xml_type == 'CCDA' ? 'xml' : strtolower($xml_type);
               $att_filename = " " . 
                  str_replace(" ", "_", $xml_type . "_" . $patientData[0]['lname'] 
                  . "_" . $patientData[0]['fname']) . "." . $extension;
               $patientName2 = $patientData[0]['fname'] . " " . $patientData[0]['lname'];
            }

            $config_err = xl("Direct messaging is currently unavailable.")." EC:";
            if ($GLOBALS['phimail_enable']==false) return("$config_err 1");

            $fp = phimail_connect($err);
            if ($fp===false) return("$config_err $err");

            $phimail_username = $GLOBALS['phimail_username'];
            $phimail_password = $GLOBALS['phimail_password'];
            $ret = phimail_write_expect_OK($fp,"AUTH $phimail_username $phimail_password\n");
            if($ret!==TRUE) return("$config_err 4");

            $ret = phimail_write_expect_OK($fp,"TO $recipient\n");
            if($ret!==TRUE) return( xl("Delivery is not allowed to the specified Direct Address.") );

            $ret=fgets($fp,1024); //ignore extra server data

            if($requested_by=="patient")
             $text_out = xl("Delivery of the attached clinical document was requested by the patient") . 
                     ($patientName2=="" ? "." : ", " . $patientName2 . ".");
            else
             $text_out = xl("A clinical document is attached") . 
                     ($patientName2=="" ? "." : " " . xl("for patient") . " " . $patientName2 . ".");

            $text_len=strlen($text_out);
            phimail_write($fp,"TEXT $text_len\n");
            $ret=@fgets($fp,256);
            
            if($ret!="BEGIN\n") {
                phimail_close($fp);
                return("$config_err 5");
            }
            $ret=phimail_write_expect_OK($fp,$text_out);
            if($ret!==TRUE) return("$config_err 6");

            if(in_array($xml_type, array('CCR', 'CCDA', 'CDA')))
            { 
                $ccd = simplexml_load_string($ccd);
                $ccd_out = $ccd->saveXml();
                $ccd_len = strlen($ccd_out);
                phimail_write($fp,"ADD " . ($xml_type=="CCR" ? $xml_type . ' ' : "CDA ") . $ccd_len . $att_filename . "\n");
                //phimail_write($fp,"ADD " . (isset($xml_type) ? $xml_type . ' ' : "CDA ") . $ccd_len . $att_filename . "\n");
            } else if(strtolower($xml_type) == 'html' || strtolower($xml_type) == 'pdf') {
                $ccd_out = base64_decode($ccd);
                $message_length = strlen($ccd_out);
                $add_type = (strtolower($xml_type) == 'html') ? 'TEXT' : 'RAW';
                phimail_write($fp, "ADD " . $add_type . " " . $message_length . "" . $att_filename . "\n");
            }
            

            $ret=fgets($fp,256);

            if($ret!="BEGIN\n") {
                phimail_close($fp);
                return("$config_err 7");
            }
            $ret=phimail_write_expect_OK($fp,$ccd_out);

            if($ret!==TRUE) return("$config_err 8");

            
            phimail_write($fp,"SEND\n");
            $ret=fgets($fp,256);
            phimail_close($fp);

            if($requested_by=="patient")  {
             $reqBy="portal-user";
             $sql = "SELECT id FROM users WHERE username='portal-user'";
             
             if (($r = sqlStatement($sql)) === FALSE ||
                 ($u = sqlFetchArray($r)) === FALSE) {
                 $reqID = 1; //default if we don't have a service user
             } else {
                 $reqID = $u['id'];
             }

            } else {
             $reqBy=$_SESSION['authUser'];
                 $reqID=$_SESSION['authUserID'];
            }

            if(substr($ret,5)=="ERROR") {
                //log the failure
                newEvent("transmit-ccd",$reqBy,$_SESSION['authProvider'],0,$ret,$pid);
                return( xl("The message could not be sent at this time."));
            }

            /**
             * If we get here, the message was successfully sent and the return
             * value $ret is of the form "QUEUED recipient message-id" which
             * is suitable for logging. 
             */
            $msg_id=explode(" ",trim($ret),4);
            if($msg_id[0]!="QUEUED" || !isset($msg_id[2])) { //unexpected response
             $ret = "UNEXPECTED RESPONSE: " . $ret;
             newEvent("transmit-ccd",$reqBy,$_SESSION['authProvider'],0,$ret,$pid);
             return( xl("There was a problem sending the message."));
            }
            newEvent("transmit-".$xml_type,$reqBy,$_SESSION['authProvider'],1,$ret,$pid);
            $adodb=$GLOBALS['adodb']['db'];
            
	//            $sql="INSERT INTO direct_message_log (msg_type,msg_id,sender,recipient,status,status_ts,patient_id,user_id) " .
	//             "VALUES ('S', ?, ?, ?, 'S', NOW(), ?, ?)";
	//            $res=@sqlStatement($sql,array($msg_id[2],$phimail_username,$recipient,$pid,$reqID));

            return("SUCCESS");
         }catch (Exception $e) {
             return 'Error: ' . $e->getMessage();
         }
        }
    }
}
?>