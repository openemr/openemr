<?php
/**
 * CCR Script.
 *
 * Copyright (C) 2010 Garden State Health Systems <http://www.gshsys.com/>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Garden State Health Systems <http://www.gshsys.com/>
 * @link    http://www.open-emr.org
 */


$result = getResultData();
$row = sqlFetchArray($result);

do {

	$e_Result = $ccr->createElement('Result');
	$e_Results->appendChild($e_Result);

	$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());//, $row['immunization_id']);
	$e_Result->appendChild($e_CCRDataObjectID);

	$e_DateTime = $ccr->createElement('DateTime');
	$e_Result->appendChild($e_DateTime);

	$date = date_create($row['date']);
	
	$e_ExactDateTime = $ccr->createElement('ExactDateTime', $date->format('Y-m-d\TH:i:s\Z'));
	$e_DateTime->appendChild($e_ExactDateTime);
	
	$e_IDs = $ccr->createElement('IDs');
	$e_Result->appendChild($e_IDs);

	$e_ID = $ccr->createElement('ID');
	$e_IDs->appendChild($e_ID);
  
  $e_IDs->appendChild(sourceType($ccr, $authorID));
  
  $e_Source = $ccr->createElement('Source');
	$e_Result->appendChild($e_Source);
  
  $e_Actor = $ccr->createElement('Actor');
  $e_Source->appendChild($e_Actor);

  $e_ActorID = $ccr->createElement('ActorID',$uuid); 
  //$e_ActorID = $ccr->createElement('ActorID',${"labID{$row['lab']}"});
  $e_Actor->appendChild($e_ActorID);
	
	$e_Test = $ccr->createElement('Test');
	$e_Result->appendChild($e_Test);

	$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
	$e_Test->appendChild($e_CCRDataObjectID);

	$e_DateTime = $ccr->createElement('DateTime');
	$e_Test->appendChild($e_DateTime);

	$e_ExactDateTime = $ccr->createElement('ExactDateTime', $date->format('Y-m-d\TH:i:s\Z'));
	$e_DateTime->appendChild($e_ExactDateTime);

	$e_Type = $ccr->createElement('Type');
	$e_Test->appendChild($e_Type);

	$e_Text = $ccr->createElement('Text', 'Observation');
	$e_Type->appendChild($e_Text);

	$e_Description = $ccr->createElement('Description' );
	$e_Test->appendChild($e_Description);

	$e_Text = $ccr->createElement('Text', $row['name']);
	$e_Description->appendChild($e_Text);

	$e_Code = $ccr->createElement('Code');
	$e_Description->appendChild($e_Code);
	
	$e_Value = $ccr->createElement('Value', 'Value');
	$e_Code->appendChild($e_Value);
  
  $e_Source = $ccr->createElement('Source');
	$e_Test->appendChild($e_Source);
  
  $e_Actor = $ccr->createElement('Actor');
  $e_Source->appendChild($e_Actor);
  
  $e_ActorID = $ccr->createElement('ActorID',$uuid);
  $e_Actor->appendChild($e_ActorID);
	
	$e_TestResult = $ccr->createElement('TestResult' );
	$e_Test->appendChild($e_TestResult);

	$e_Value = $ccr->createElement('Value', $row['result']);
	$e_TestResult->appendChild($e_Value);

	$e_Code = $ccr->createElement('Code' );
	$e_TestResult->appendChild($e_Code);

	$e_Value = $ccr->createElement('Value', 'Value');
	$e_Code->appendChild($e_Value);
	
	$e_Description = $ccr->createElement('Description' );
	$e_TestResult->appendChild($e_Description);
	
	$e_Text = $ccr->createElement('Text', $row['result']);
	$e_Description->appendChild( $e_Text);
	
	//if($row['abnormal'] == '' ) {
		$e_NormalResult = $ccr->createElement('NormalResult');
		$e_Test->appendChild($e_NormalResult);
    
    $e_Normal = $ccr->createElement('Normal');
		$e_NormalResult->appendChild($e_Normal);
    
    $e_Value = $ccr->createElement('Value', $row['range']);
    $e_Normal->appendChild($e_Value);
    
    $e_Units = $ccr->createElement('Units');
    $e_Normal->appendChild($e_Units);
    
    $e_Unit = $ccr->createElement('Unit', 'Test Unit');
    $e_Units->appendChild($e_Unit);
    
    $e_Source = $ccr->createElement('Source');
    $e_Normal->appendChild($e_Source);
		
		$e_Actor = $ccr->createElement('Actor');
		$e_Source->appendChild($e_Actor);
		
		$e_ActorID = $ccr->createElement('ActorID',$uuid);
		$e_Actor->appendChild($e_ActorID);
    
	//} else {
		$e_Flag = $ccr->createElement('Flag');
		$e_Test->appendChild($e_Flag);

		$e_Text = $ccr->createElement('Text', $row['abnormal']);
		$e_Flag->appendChild($e_Text);
	//}
	
	//$e_Test = $ccr->createElement('Test');
	//$e_Result->appendChild($e_Test);
	//
	//$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
	//$e_Test->appendChild($e_CCRDataObjectID);
	//
	//$e_DateTime = $ccr->createElement('DateTime');
	//$e_Test->appendChild($e_DateTime);
	//
	//$e_ExactDateTime = $ccr->createElement('ExactDateTime', $date->format('Y-m-d\TH:i:s\Z'));
	//$e_DateTime->appendChild($e_ExactDateTime);
	//
	//$e_Type = $ccr->createElement('Type');
	//$e_Test->appendChild($e_Type);
	//
	//$e_Text = $ccr->createElement('Text', 'Observation');
	//$e_Type->appendChild($e_Text);
	//
	//
	//$e_Description = $ccr->createElement('Description' );
	//$e_Test->appendChild($e_Description);
	//
	//$e_Text = $ccr->createElement('Text', 'Range');
	//$e_Description->appendChild($e_Text);
	//
	//$e_Code = $ccr->createElement('Code');
	//$e_Description->appendChild($e_Code);
	//
	//$e_Value = $ccr->createElement('Value', 'None');
	//$e_Code->appendChild($e_Value);
	//
	//$e_Test->appendChild(sourceType($ccr, $authorID));
	//
	//$e_TestResult = $ccr->createElement('TestResult' );
	//$e_Test->appendChild($e_TestResult);
	//
	//$e_Value = $ccr->createElement('Value', '1.0');
	//$e_TestResult->appendChild($e_Value);
	//
	//$e_Code = $ccr->createElement('Code' );
	//$e_TestResult->appendChild($e_Code);
	//
	//$e_Value = $ccr->createElement('Value', 'Test 01 Code');
	//$e_Code->appendChild($e_Value);
	//
	//$e_Description = $ccr->createElement('Description' );
	//$e_TestResult->appendChild($e_Description);
	//
	//$e_Text = $ccr->createElement('Text', $row['range']);
	//$e_Description->appendChild($e_Text);
	//
	//
	//if($row['abnormal'] == '' ) {
	//	$e_NormalResult = $ccr->createElement('NormalResult');
	//	$e_Test->appendChild($e_NormalResult);
	//} else {
	//	$e_Flag = $ccr->createElement('Flag');
	//	$e_Test->appendChild($e_Flag);
	//
	//	$e_Text = $ccr->createElement('Text');
	//	$e_Flag->appendChild($e_Text);
	//	
	//}
	
} while ($row = sqlFetchArray($result));

?>
