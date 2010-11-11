<?php
// Copyright (C) 2010 MMF Systems, Inc>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// SEGMENT FUNCTION START 

// ISA Segment  - EDI-270 format 

function create_ISA($row,$X12info,$segTer,$compEleSep) {

	$ISA	 =	array();

	$ISA[0] = "ISA";							// Interchange Control Header Segment ID 
	
	$ISA[1] = "00";								// Author Info Qualifier 
	
	$ISA[2] = str_pad("0000000",10," ");		// Author Information 
	
	$ISA[3] = "00";								//   Security Information Qualifier
												//   MEDI-CAL NOTE: For Leased-Line & Dial-Up use '01', 
												//   for BATCH use '00'.
												//   '00' No Security Information Present 
												//   (No Meaningful Information in I04)

	$ISA[4] = str_pad("0000000000",10," ");		// Security Information 
	
	$ISA[5] = str_pad("ZZ",2," ");				// Interchange ID Qualifier
	
	$ISA[6] = str_pad($X12info[2],15," ");		// INTERCHANGE SENDER ID 
	
	$ISA[7] = str_pad("ZZ",2," ");				// Interchange ID Qualifier 
	
	$ISA[8] = str_pad($X12info[3],15," ");		// INTERCHANGE RECEIVER ID  
	
	$ISA[9] = str_pad(date('ymd'),6," ");		// Interchange Date (YYMMDD) 
	
	$ISA[10] = str_pad(date('Hi'),4," ");		// Interchange Time (HHMM) 
	
	$ISA[11] = "U";								// Interchange Control Standards Identifier 
	
	$ISA[12] = str_pad("00401",5," ");			// Interchange Control Version Number 
	
	$ISA[13] = str_pad("000000001",9," ");		// INTERCHANGE CONTROL NUMBER   
	
	$ISA[14] = str_pad("1",1," ");				// Acknowledgment Request [0= not requested, 1= requested]  
	
	$ISA[15] =  str_pad("P",1," ");				// Usage Indicator [ P = Production Data, T = Test Data ]  
	
	$ISA['Created'] = implode('*', $ISA);		// Data Element Separator 

	$ISA['Created'] = $ISA['Created'] ."*";

	$ISA['Created'] = $ISA ['Created'] . $segTer . $compEleSep; 
	
	return trim($ISA['Created']);
	
}

// GS Segment  - EDI-270 format 

function create_GS($row,$X12info,$segTer,$compEleSep) {

	$GS	   = array();

	$GS[0] = "GS";						// Functional Group Header Segment ID 
	
	$GS[1] = "HS";						// Functional ID Code [ HS = Eligibility, Coverage or Benefit Inquiry (270) ] 
	
	$GS[2] =  $X12info[2];				// Application Sender’s ID 
	
	$GS[3] =  $X12info[3];				// Application Receiver’s ID 
	
	$GS[4] = date('Ymd');				// Date [CCYYMMDD] 
	
	$GS[5] = date('His');				// Time [HHMM] – Group Creation Time  
	
	$GS[6] = "000000002";				// Group Control Number 
	
	$GS[7] = "X";					// Responsible Agency Code Accredited Standards Committee X12 ] 
	
	$GS[8] = "004010X092A1";			// Version –Release / Industry[ Identifier Code Query 

	$GS['Created'] = implode('*', $GS);		// Data Element Separator 

	$GS['Created'] = $GS ['Created'] . $compEleSep; 
	 
	return trim($GS['Created']);
	
}

// ST Segment  - EDI-270 format 

function create_ST($row,$X12info,$segTer,$compEleSep) {

	$ST	   =	array();

	$ST[0] = "ST";								// Transaction Set Header Segment ID 
	
	$ST[1] = "270";								// Transaction Set Identifier Code (Inquiry Request) 
	
	$ST[2] = "000000003";						// Transaction Set Control Number - Must match SE's 
	
	$ST['Created'] = implode('*', $ST);			// Data Element Separator 

	$ST['Created'] = $ST ['Created'] . $compEleSep; 
	 
	return trim($ST['Created']);
			
}

// BHT Segment  - EDI-270 format 

function create_BHT($row,$X12info,$segTer,$compEleSep) {

	$BHT	=	array();
	
	$BHT[0] = "BHT";						// Beginning of Hierarchical Transaction Segment ID 

	$BHT[1] = "0022";						// Subscriber Structure Code   

	$BHT[2] = "13";							// Purpose Code - This is a Request   

	$BHT[3] = "PROVTest600";				//  Submitter Transaction Identifier  
											//This information is required by the information Receiver 
											//when using Real Time transactions. 
											//For BATCH this can be used for optional information.

	$BHT[4] = str_pad(date('Ymd'),8," ");			// Date Transaction Set Created 
	
	$BHT[5] = str_pad(date('His'),8," ");			// Time Transaction Set Created 

	$BHT['Created'] = implode('*', $BHT);			// Data Element Separator 

	$BHT['Created'] = $BHT ['Created'] . $compEleSep; 
	 
	return trim($BHT['Created']);
	
}

// HL Segment  - EDI-270 format 

function create_HL($row, $nHlCounter,$X12info,$segTer,$compEleSep) {

	$HL		= array();

	$HL[0]		= "HL";			// Hierarchical Level Segment ID 
	$HL_LEN[0]	=  2;

	$HL[1] = $nHlCounter;		// Hierarchical ID No. 
	
	if($nHlCounter == 1)
	{ 
		$HL[2] = ""; 
		$HL[3] = 20;			// Description: Identifies the payor, maintainer, or source of the information.
		$HL[4] = 1;				// 1 Additional Subordinate HL Data Segment in This Hierarchical Structure. 
	}
	else if($nHlCounter == 2)
	{
		$HL[2] = 1;				// Hierarchical Parent ID Number 
		$HL[3] = 21;			// Hierarchical Level Code. '21' Information Receiver
		$HL[4] = 1;				// 1 Additional Subordinate HL Data Segment in This Hierarchical Structure. 
	}
	else
	{
		$HL[2] = 2;
		$HL[3] = 22;			// Hierarchical Level Code.'22' Subscriber 
		$HL[4] = 0;				// 0 no Additional Subordinate in the Hierarchical Structure. 
	}
	
	$HL['Created'] = implode('*', $HL);		// Data Element Separator 

	$HL['Created'] = $HL ['Created'] . $compEleSep; 
	 
	return trim($HL['Created']);

}

// NM1 Segment  - EDI-270 format 

function create_NM1($row,$nm1Cast,$X12info,$segTer,$compEleSep) {

	$NM1		= array();
	
	$NM1[0]		= "NM1";					// Subscriber Name Segment ID 
	
	if($nm1Cast == 'PR')
	{
		$NM1[1] = "PR";						// Entity ID Code - Payer [PR Payer] 
		$NM1[2] = "2";						// Entity Type - Non-Person 
		$NM1[3] = $row["payer_name"];		// Organizational Name 
		$NM1[4] = "";						// Data Element not required.
		$NM1[5] = "";						// Data Element not required.
		$NM1[6] = "";						// Data Element not required.
		$NM1[7] = "";						// Data Element not required.
		$NM1[8] = "46";						// 46 - Electronic Transmitter Identification Number (ETIN) 
		$NM1[9] = $X12info[3];				// Application Sender’s ID 
	}
	else if($nm1Cast == '1P')
	{
		$NM1[1] = "IP";						// Entity ID Code - Provider [1P Provider]
		$NM1[2] = "1";						// Entity Type - Person 
		$NM1[3] = $row['facility_name'];			// Organizational Name 
		$NM1[4] = $row['provider_lname'];			// Data Element not required.
		$NM1[5] = $row['provider_fname'];			// Data Element not required.
		$NM1[6] = "";						// Data Element not required.
		$NM1[7] = "";						// Data Element not required.
		$NM1[8] = "XX";						
		$NM1[9] = $row['provider_npi'];		
	}
	else if($nm1Cast == 'IL')
	{
		$NM1[1] = "IL";						// Insured or Subscriber 
		$NM1[2] = "1";						// Entity Type - Person 
		$NM1[3] = $row['lname'];				// last Name	
		$NM1[4] = $row['fname'];				// first Name	
		$NM1[5] = $row['mname'];				// middle Name	
		$NM1[6] = "";						// data element 
		$NM1[7] = "";						// data element 
		$NM1[8] = "MI";						// Identification Code Qualifier 
		$NM1[9] = $row['subscriber_ss'];			// Identification Code 
	}
	
	$NM1['Created'] = implode('*', $NM1);				// Data Element Separator 

	$NM1['Created'] = $NM1['Created'] . $compEleSep; 
	 
	return trim($NM1['Created']);

}

// REF Segment  - EDI-270 format 

function create_REF($row,$ref,$X12info,$segTer,$compEleSep) {

	$REF	=	array();

	$REF[0] = "REF";						// Subscriber Additional Identification 

	if($ref == '1P')
	{
		$REF[1] = "4A";						// Reference Identification Qualifier 
		$REF[2] = $row['provider_pin'];				// Provider Pin. 
	}
	else
	{
		$REF[1] = "EJ";						// 'EJ' for Patient Account Number 
		$REF[2] = $row['pid'];					// Patient Account No. 
	}
	$REF['Created'] = implode('*', $REF);				// Data Element Separator 

	$REF['Created'] = $REF['Created'] . $compEleSep; 
	 
	return trim($REF['Created']);
  
}

// TRN Segment - EDI-270 format 

function create_TRN($row,$tracno,$refiden,$X12info,$segTer,$compEleSep) {

	$TRN	=	array();

	$TRN[0] = "TRN";						// Subscriber Trace Number Segment ID 

	$TRN[1] = "1";							// Trace Type Code – Current Transaction Trace Numbers 

	$TRN[2] = $tracno;						// Trace Number 

	$TRN[3] = "9000000000";						// Originating Company ID – must be 10 positions in length 

	$TRN[4] = $refiden;						// Additional Entity Identifier (i.e. Subdivision) 

	$TRN['Created'] = implode('*', $TRN);				// Data Element Separator 

	$TRN['Created'] = $TRN['Created'] . $compEleSep; 
	 
	return trim($TRN['Created']);
  
}

// DMG Segment - EDI-270 format 

function create_DMG($row,$X12info,$segTer,$compEleSep) {

	$DMG	=	array();
	
	$DMG[0] = "DMG";							// Date or Time or Period Segment ID 

	$DMG[1] = "D8";								// Date Format Qualifier - (D8 means CCYYMMDD) 

	$DMG[2] = $row['dob'];						// Subscriber's Birth date 

	$DMG['Created'] = implode('*', $DMG);		// Data Element Separator 

	$DMG['Created'] = $DMG['Created'] .  $compEleSep; 
	 
	return trim($DMG['Created']);			
}

// DTP Segment - EDI-270 format 

function create_DTP($row,$qual,$X12info,$segTer,$compEleSep) {

	$DTP	=	array();
	
	$DTP[0] = "DTP";						// Date or Time or Period Segment ID 
	
	$DTP[1] = $qual;						// Qualifier - Date of Service 
	
	$DTP[2] = "D8";							// Date Format Qualifier - (D8 means CCYYMMDD) 
	
	if($qual == '102'){
		$DTP[3] = $row['date'];				// Date 
	}else{
		$DTP[3] = $row['pc_eventDate'];		// Date of Service 
	}
	$DTP['Created'] = implode('*', $DTP);	// Data Element Separator 

	$DTP['Created'] = $DTP['Created'] .  $compEleSep; 
	 
	return trim($DTP['Created']);
}

// EQ Segment - EDI-270 format 

function create_EQ($row,$X12info,$segTer,$compEleSep) {

	$EQ		=	array();
	
	$EQ[0]	= "EQ";									// Subscriber Eligibility or Benefit Inquiry Information 
	
	$EQ[1]	= "30";									// Service Type Code 
	
	$EQ['Created'] = implode('*', $EQ);				// Data Element Separator 

	$EQ['Created'] = $EQ['Created'] . $compEleSep; 
	 
	return trim($EQ['Created']);
}

// SE Segment - EDI-270 format 

function create_SE($row,$segmentcount,$X12info,$segTer,$compEleSep) {

	$SE	=	array();
	
	$SE[0] = "SE";								// Transaction Set Trailer Segment ID 

	$SE[1] = $segmentcount;						// Segment Count 

	$SE[2] = "000000003";						// Transaction Set Control Number - Must match ST's 

	$SE['Created'] = implode('*', $SE);			// Data Element Separator 

	$SE['Created'] = $SE['Created'] . $compEleSep; 
	 
	return trim($SE['Created']);
}

// GE Segment - EDI-270 format 

function create_GE($row,$X12info,$segTer,$compEleSep) {

	$GE	=	array();
	
	$GE[0]	= "GE";							// Functional Group Trailer Segment ID 

	$GE[1]	= "1";							// Number of included Transaction Sets 

	$GE[2]	= "000000002";						// Group Control Number 

	$GE['Created'] = implode('*', $GE);				// Data Element Separator 

	$GE['Created'] = $GE['Created'] . $compEleSep; 
	 
	return trim($GE['Created']);
}

// IEA Segment - EDI-270 format 

function create_IEA($row,$X12info,$segTer,$compEleSep) {

	$IEA	=	array();
	
	$IEA[0] = "IEA";						// Interchange Control Trailer Segment ID 

	$IEA[1] = "1";							// Number of included Functional Groups 

	$IEA[2] = "000000001";						// Interchange Control Number 

	$IEA['Created'] = implode('*', $IEA);

	$IEA['Created'] = $IEA['Created'] .  $compEleSep; 
	 
	return trim($IEA['Created']);
}

function translate_relationship($relationship) {
	switch ($relationship) {
		case "spouse":
			return "01";
			break;
		case "child":
			return "19";
			break;
		case "self":
		default:
			return "S";
	}
}

// EDI-270 Batch file Generation 

function print_elig($res,$X12info,$segTer,$compEleSep){
		
	$i=1;

	$PATEDI	   = "";

	// For Header Segment 

	$nHlCounter = 1;
	$rowCount	= 0;
	$trcNo		= 1234501;
	$refiden	= 5432101;
	
	while ($row = sqlFetchArray($res)) 
	{
		
		if($nHlCounter == 1)
		{
			// create ISA 
			$PATEDI	   = create_ISA($row,$X12info,$segTer,$compEleSep);
			
			// create GS 
			$PATEDI	  .= create_GS($row,$X12info,$segTer,$compEleSep);

			// create ST 
			$PATEDI	  .= create_ST($row,$X12info,$segTer,$compEleSep);
			
			// create BHT 
			$PATEDI	  .= create_BHT($row,$X12info,$segTer,$compEleSep);
			
			// For Payer Segment 
				
			$PATEDI  .= create_HL($row,1,$X12info,$segTer,$compEleSep);
			$PATEDI  .= create_NM1($row,'PR',$X12info,$segTer,$compEleSep);

			// For Provider Segment 				
					
			$PATEDI  .= create_HL($row,2,$X12info,$segTer,$compEleSep);
			$PATEDI  .= create_NM1($row,'1P',$X12info,$segTer,$compEleSep);
			$PATEDI  .= create_REF($row,'1P',$X12info,$segTer,$compEleSep);

			$nHlCounter = $nHlCounter + 2;	
			$segmentcount = 7; // segement counts - start from ST 
		}

		// For Subscriber Segment 				
		
		$PATEDI  .= create_HL($row,$nHlCounter,$X12info,$segTer,$compEleSep);
		$PATEDI  .= create_TRN($row,$trcNo,$refiden,$X12info,$segTer,$compEleSep);
		$PATEDI  .= create_NM1($row,'IL',$X12info,$segTer,$compEleSep);
		$PATEDI  .= create_REF($row,'IL',$X12info,$segTer,$compEleSep);
		$PATEDI  .= create_DMG($row,$X12info,$segTer,$compEleSep);
		
		//	$PATEDI  .= create_DTP($row,'102',$X12info,$segTer,$compEleSep);
		
		$PATEDI  .= create_DTP($row,'472',$X12info,$segTer,$compEleSep);
		$PATEDI  .= create_EQ($row,$X12info,$segTer,$compEleSep);
								
		$segmentcount	= $segmentcount + 7;
		$nHlCounter	= $nHlCounter + 1;
		$rowCount	= $rowCount + 1;
		$trcNo		= $trcNo + 1;
		$refiden	= $refiden + 1;
		

		if($rowCount == sqlNumRows($res))
		{
			$segmentcount = $segmentcount + 1;
			$PATEDI	  .= create_SE($row,$segmentcount,$X12info,$segTer,$compEleSep);
			$PATEDI	  .= create_GE($row,$X12info,$segTer,$compEleSep);
			$PATEDI	  .= create_IEA($row,$X12info,$segTer,$compEleSep);
		}
	}

	echo $PATEDI;
}

// Report Generation 

function show_elig($res,$X12info,$segTer,$compEleSep){
		
	$i=0;
	echo "	<div id='report_results'>
			<table>
				<thead>
				
					<th style='width:12%;'>	". htmlspecialchars( xl('Facility Name'), ENT_NOQUOTES) ."</th>
					<th style='width:9%;' >	". htmlspecialchars( xl('Facility NPI'), ENT_NOQUOTES) ."</th>
					<th style='width:15%;'>	". htmlspecialchars( xl('Insurance Comp'), ENT_NOQUOTES) ."</th>
					<th style='width:8%;' >	". htmlspecialchars( xl('Policy No'), ENT_NOQUOTES) ."</th>
					<th style='width:16%;'>	". htmlspecialchars( xl('Patient Name'), ENT_NOQUOTES) ."</th>
					<th style='width:7%;' >	". htmlspecialchars( xl('DOB'), ENT_NOQUOTES) ."</th>
					<th style='width:6%;' >	". htmlspecialchars( xl('Gender'), ENT_NOQUOTES) ."</th>
					<th style='width:9%;' >	". htmlspecialchars( xl('SSN'), ENT_NOQUOTES) ."</th>
					<th style='width:2%;' >	&nbsp;			  </th>
				</thead>

				<tbody>
					
		";

	while ($row = sqlFetchArray($res)) { 
		
						
		$i= $i+1;

		if($i%2 == 0){
			$background = '#FFF';
		}else{
			$background = '#FFF';
		}

		$elig	  = array();
		$elig[0]  = $row['facility_name'];				// Inquiring Provider Name  calendadr 
		$elig[1]  = $row['facility_npi'];				// Inquiring Provider NPI 
		$elig[2]  = $row['payer_name'];					// Payer Name  our insurance co name
		$elig[3]  = $row['policy_number'];				// Subscriber ID   
		$elig[4]  = $row['subscriber_lname'];				// Subscriber Last Name 
		$elig[5]  = $row['subscriber_fname'];				// Subscriber First Name 
		$elig[6]  = $row['subscriber_mname'];				// Subscriber Middle Initial 
		$elig[7]  = $row['subscriber_dob'];				// Subscriber Date of Birth 
		$elig[8]  = substr($row['subscriber_sex'], 0, 1);		// Subscriber Sex 
		$elig[9]  = $row['subscriber_ss'];				// Subscriber SSN 
		$elig[10] = translate_relationship($row['subscriber_relationship']);    // Pt Relationship to insured 
		$elig[11] = $row['lname'];					// Dependent Last Name 
		$elig[12] = $row['fname'];					// Dependent First Name 
		$elig[13] = $row['mname'];					// Dependent Middle Initial 
		$elig[14] = $row['dob'];					// Dependent Date of Birth 
		$elig[15] = substr($row['sex'], 0, 1);				// Dependent Sex 
		$elig[16] = $row['pc_eventDate'];				// Date of service 
		$elig[17] = "30";						// Service Type 
		$elig[18] = $row['pubpid'];					// Patient Account Number pubpid  

		echo "	<tr id='PR".$i."_". htmlspecialchars( $row['policy_number'], ENT_QUOTES)."'>
				<td class ='detail' style='width:12%;'>". htmlspecialchars( $row['facility_name'], ENT_NOQUOTES) ."</td>
				<td class ='detail' style='width:9%;'>".  htmlspecialchars( $row['facility_npi'], ENT_NOQUOTES) ."</td>
				<td class ='detail' style='width:15%;'>". htmlspecialchars( $row['payer_name'], ENT_NOQUOTES) ."</td>
				<td class ='detail' style='width:8%;'>".  htmlspecialchars( $row['policy_number'], ENT_NOQUOTES) ."</td>
				<td class ='detail' style='width:16%;'>". htmlspecialchars( $row['subscriber_lname']." ".$row['subscriber_fname'], ENT_NOQUOTES) ."</td>
				<td class ='detail' style='width:7%;'>".  htmlspecialchars( $row['subscriber_dob'], ENT_NOQUOTES) ."</td>
				<td class ='detail' style='width:6%;'>".  htmlspecialchars( $row['subscriber_sex'], ENT_NOQUOTES) ."</td>
				<td class ='detail' style='width:9%;'>".  htmlspecialchars( $row['subscriber_ss'], ENT_NOQUOTES) ."</td>
				<td class ='detail' style='width:2%;'>
					<img src='../../images/deleteBtn.png' title=' .htmlspecialchars( xl('Delete Row'), ENT_QUOTES) . ' style='cursor:pointer;cursor:hand;' onclick='deletetherow(\"" . $i."_". htmlspecialchars( $row['policy_number'], ENT_QUOTES) . "\")'>
				</td>
			</tr>			
		";

				
		unset($elig);
	}
	
	if($i==0){

		echo "	<tr>
				<td class='norecord' colspan=9>
					<div style='padding:5px;font-family:arial;font-size:13px;text-align:center;'>". htmlspecialchars( xl('No records found'), ENT_NOQUOTES) . "</div>
				</td>
			</tr>	";
	}
		echo "	</tbody>
			</table>";
}

// To Show Eligibility Verification data 

function show_eligibility_information($pid)
{

	$query = "	SELECT 		eligr.response_description as ResponseMessage, 
					DATE_FORMAT(eligv.eligibility_check_date, '%d %M %Y') as VerificationDate, 
					eligv.copay, eligv.deductible, eligv.deductiblemet, 
					if(eligr.response_status = 'A','Active','Inactive') as Status, 
					insd.pid, insc.name 
			FROM 		eligibility_verification eligv 
			INNER JOIN	eligibility_response eligr on eligr.response_id = eligv.response_id 
			INNER JOIN	insurance_data insd on insd.id = eligv.insurance_id 
			INNER JOIN 	insurance_companies insc on insc.id = insd.provider
			WHERE 		insd.pid = ?
			AND			eligr.response_status = 'A'
			AND	 		eligv.eligibility_check_date = (SELECT max(eligibility_check_date) 
										FROM eligibility_verification 
										WHERE	insurance_id = eligv.insurance_id)";
	$result		= sqlStatement($query, array($pid) );
		
	$row = sqlFetchArray($result);

	$showString .= 	"<br><div class='text'>" .
			"<b>" .
			htmlspecialchars( xl('Insurance Provider'), ENT_NOQUOTES) . ":</b> " .
			(!empty($row['name']) ? htmlspecialchars( $row['name'], ENT_NOQUOTES) : htmlspecialchars( xl('n/a'), ENT_NOQUOTES)) .
			"<br>\n" .
			"<b>" .
			htmlspecialchars( xl('Status'), ENT_NOQUOTES) . ":</b> " .
			(!empty($row['ResponseMessage']) ? htmlspecialchars( $row['ResponseMessage'], ENT_NOQUOTES) : htmlspecialchars( xl('n/a'), ENT_NOQUOTES)) . 
			"<br>\n" .
			"<b>" .
			htmlspecialchars( xl('Last Verified On'), ENT_NOQUOTES) . ":</b> " .
			(!empty($row['VerificationDate']) ? htmlspecialchars( $row['VerificationDate'], ENT_NOQUOTES) : htmlspecialchars( xl('n/a'), ENT_NOQUOTES)) .
			"<br>" .
			"<b>" . htmlspecialchars( xl('Copay'), ENT_NOQUOTES) . ":</b> " .
			(!empty($row['copay']) ? htmlspecialchars( $row['copay'], ENT_NOQUOTES) : htmlspecialchars( xl('n/a'), ENT_NOQUOTES)) .
			"<br><b>" . htmlspecialchars( xl('Deductible'), ENT_NOQUOTES) . ":</b> " .
			(!empty($row['deductible']) ? htmlspecialchars( $row['deductible'], ENT_NOQUOTES) : htmlspecialchars( xl('n/a'), ENT_NOQUOTES)) .
			"<br><b>" . htmlspecialchars( xl('Deductible Met'), ENT_NOQUOTES) . ":</b> " .
			(!empty($row['deductiblemet']) ? ($row['deductiblemet'] == 'Y' ? htmlspecialchars( xl('Yes'), ENT_NOQUOTES) : htmlspecialchars( xl('No'), ENT_NOQUOTES)) : htmlspecialchars( xl('n/a'), ENT_NOQUOTES)) .
			"</div>";
	
	echo $showString;
}

// For EDI 271 


// Function to save the values in eligibility_response table 

function eligibility_response_save($segmentVal,$vendorID)
{

	$resCount = 0;

	$query = "  SELECT   count(*) as RecordsCount	
				FROM	 eligibility_response 
				WHERE	 response_description = ? and 
					 response_vendor_id	  = ?";
										
	$resCount = sqlStatement($query, array($segmentVal, $vendorID) );
	
	
	if(isset($resCount))
	{

		$row		= sqlFetchArray($resCount);

		$resCount	= $row['RecordsCount'];
				
	}

	if($resCount == 0)
	{

		$query = "INSERT into eligibility_response SET	response_description =?,
								response_vendor_id	 = ?,
								response_create_date = now(),
								response_modify_date = now()";
		$res	= sqlStatement($query, array($segmentVal, $vendorID) );
	}

}

// Function to save the values in eligibility_verification table

function eligibility_verification_save($segmentVal,$x12PartnerId,$patientId)
{

	$resCount = 0;

	// For fetching the response Id

	$query = "  SELECT	 response_id 	
				FROM	 eligibility_response 
				WHERE	 response_description = ? and 
						 response_vendor_id	  = ?";
										
	$resId	= sqlStatement($query, array($segmentVal, $x12PartnerId) );
	
	// For fetching the insuarace data Id 

	$query = "  SELECT	 id,copay 	
				FROM	 insurance_data 
				WHERE	 type = 'primary' and
						 pid = ?";
										
	$insId	= sqlStatement($query, array($patientId) );
	
	if(isset($resId))
	{

		$row		= sqlFetchArray($resId);

		$responseId	= $row['response_id'];
				
	}
	if(isset($insId))
	{

		$row		= sqlFetchArray($insId);

		$insuranceId	= $row['id'];
		$copay		= $row['copay'];
				
	}

	if($resCount == 0)
	{

		if(isset($insuranceId) && !empty($insuranceId)){

			//Set up the sql variable binding array (this prevents sql-injection attacks)
			$sqlBindArray = array();
			$query = "INSERT into eligibility_verification SET	response_id	= ? ,
										insurance_id	= ?,";
			array_push($sqlBindArray, $responseId, $insuranceId);	

			if(!empty($copay))
			{
				$query .= "copay = ?,";
				array_push($sqlBindArray, $copay);
			}
			$query .= "eligibility_check_date	= now(),
				   create_date			= now()";
			$res	= sqlStatement($query, $sqlBindArray);
		}
	}
}

// Function to fetch the Patient information - eligibility

function eligibility_information($insuranceId)
{
	$insuranceId = 1;

	$query = "	SELECT		* 	
			FROM		eligibility_verification
			WHERE	    insuranceid = ?";
										
	$result		= sqlStatement($query, array($insuranceId) );
	$row		= sqlFetchArray($result);
	return $row;

}
// return array of X12 partners 

function getX12Partner() {
	$rez = sqlStatement("select * from x12_partners");
	for($iter=0; $row=sqlFetchArray($rez); $iter++)
		$returnval[$iter]=$row;

	return $returnval;
}

// return array of provider usernames 
function getUsernames() {
	$rez = sqlStatement("select distinct username, lname, fname,id from users " .
		"where authorized = 1 and username != ''");
	for($iter=0; $row=sqlFetchArray($rez); $iter++)
		$returnval[$iter]=$row;

	return $returnval;
}

// return formated array 

function arrFormated(&$item, $key){
	$item = strstr($item, '_');
	$item = substr($item,1,strlen($item)-1);
	$item = "'".$item;
}	
?>
