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
}
?>