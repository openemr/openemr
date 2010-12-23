<?php
//  ------------------------------------------------------------------------ //
//                     Garden State Health Systems                           //
//                    Copyright (c) 2010 gshsys.com                          //
//                      <http://www.gshsys.com/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //

//require_once(dirname(__FILE__) . "/../library/sql-ccr.inc");

$result = getResultData();
$row = sqlFetchArray($result);

do {

	$e_Result = $ccr->createElement('Result');
	$e_Results->appendChild($e_Result);

	$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());//, $row['immunization_id']);
	$e_Immunization->appendChild($e_CCRDataObjectID);

	$e_DateTime = $ccr->createElement('DateTime');
	$e_Result->appendChild($e_DateTime);

	$date = date_create($row['date']);
	
	$e_ExactDateTime = $ccr->createElement('ExactDateTime', $date->format('Y-m-d\TH:i:s\Z'));
	$e_DateTime->appendChild($e_ExactDateTime);
	
	$e_IDs = $ccr->createElement('IDs');
	$e_Result->appendChild($e_IDs);

	$e_ID = $ccr->createElement('ID', $row['pid']);
	$e_IDs->appendChild($e_ID);

	$e_IDs->appendChild(sourceType($ccr, $authorID));

	$e_Result->appendChild(sourceType($ccr, $authorID));
	
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

	$e_Text = $ccr->createElement('Text', $row['ankle_able_to_bear_weight_steps']);
	$e_Description->appendChild($e_Text);

	$e_Code = $ccr->createElement('Code');
	$e_Description->appendChild($e_Code);
	
	$e_Value = $ccr->createElement('Value', 'None');
	$e_Code->appendChild($e_Value);
	
	$e_Test->appendChild(sourceType($ccr, $authorID));

	$e_TestResult = $ccr->createElement('TestResult' );
	$e_Test->appendChild($e_TestResult);

	$e_Value = $ccr->createElement('Value', '1.0');
	$e_TestResult->appendChild($e_Value);

	$e_Code = $ccr->createElement('Code' );
	$e_TestResult->appendChild($e_Code);

	$e_Value = $ccr->createElement('Value', 'Test 01 Code');
	$e_Code->appendChild($e_Value);
	
	$e_Description = $ccr->createElement('Description' );
	$e_TestResult->appendChild($e_Description);
	
	$e_Text = $ccr->createElement('Text', $row['ankle_able_to_bear_weight_steps']);
	$e_Description->appendChild( $e_Text);
	
	
	if('high' == 'normal' ) {
		$e_NormalResult = $ccr->createElement('NormalResult','normal');
		$e_Test->appendChild($e_NormalResult);
	} else {
		$e_Flag = $ccr->createElement('Flag', 'high' );
		$e_Test->appendChild($e_Flag);

		$e_Text = $ccr->createElement('Text', $row['high']);
		$e_Flag->appendChild($e_Text);
	}
	
	$e_Test = $ccr->createElement('Test');
	$e_Result->appendChild($e_Test);

	$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
	$e_Test->appendChild($e_CCRDataObjectID);

	$e_DateTime = $ccr->createElement('DateTime');
	$e_Test->appendChild($e_DateTime);

	$e_ExactDateTime = $ccr->createElement('ExactDateTime', $date->format('Y-m-d\TH:i:s\Z'));
	$e_DateTime->appendChild($e_ExactDateTime);

	$e_Type = $ccr->createElement('Type', 'x ray');
	$e_Test->appendChild($e_Type);

	$e_Text = $ccr->createElement('Text', 'Observation');
	$e_Type->appendChild($e_Text);


	$e_Description = $ccr->createElement('Description' );
	$e_Test->appendChild($e_Description);

	$e_Text = $ccr->createElement('Text', $row['ankle_x_ray_interpretation']);
	$e_Description->appendChild($e_Text);

	$e_Code = $ccr->createElement('Code');
	$e_Description->appendChild($e_Code);
	
	$e_Value = $ccr->createElement('Value', 'None');
	$e_Code->appendChild($e_Value);

	$e_Test->appendChild(sourceType($ccr, $authorID));

	$e_TestResult = $ccr->createElement('TestResult' );
	$e_Test->appendChild($e_TestResult);

	$e_Value = $ccr->createElement('Value', '1.0');
	$e_TestResult->appendChild($e_Value);

	$e_Code = $ccr->createElement('Code' );
	$e_TestResult->appendChild($e_Code);

	$e_Value = $ccr->createElement('Value', 'Test 01 Code');
	$e_Code->appendChild($e_Value);
	
	$e_Description = $ccr->createElement('Description' );
	$e_TestResult->appendChild($e_Description);
	
	$e_Text = $ccr->createElement('Text', $row['ankle_x_ray_interpretation']);
	$e_Description->appendChild($e_Text);
	

	if('normal' == 'normal' ) {
		$e_NormalResult = $ccr->createElement('NormalResult','normal');
		$e_Test->appendChild($e_NormalResult);
	} else {
		$e_Flag = $ccr->createElement('Flag');
		$e_Test->appendChild($e_Flag);

		$e_Text = $ccr->createElement('Text', $row['high']);
		$e_Flag->appendChild($e_Text);
		
	}
	
} while ($row = sqlFetchArray($result));

?>
