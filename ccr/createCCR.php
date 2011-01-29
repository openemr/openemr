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

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//
 
require_once(dirname(__FILE__) . "/../interface/globals.php");
require_once(dirname(__FILE__) . "/../library/sql-ccr.inc");
require_once(dirname(__FILE__) . "/../library/sql.inc");
require_once(dirname(__FILE__) . "/uuid.php");
?>

<?php

function createCCR($action){

	$authorID = getUuid();
	
	echo '<!--';

	   $ccr = new DOMDocument('1.0','UTF-8');
	   $e_styleSheet = $ccr->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="ccr.xsl"');
	   $ccr->appendChild($e_styleSheet);

	   $e_ccr = $ccr->createElementNS('urn:astm-org:CCR', 'ContinuityOfCareRecord');
	   $ccr->appendChild($e_ccr);

	   /////////////// Header

	   require_once("createCCRHeader.php");
	   $e_Body = $ccr->createElement('Body');
	   $e_ccr->appendChild($e_Body);
	   
	   /////////////// Problems

	   $e_Problems = $ccr->createElement('Problems');
	   require_once("createCCRProblem.php");
	   $e_Body->appendChild($e_Problems);

	   /////////////// Alerts

	   $e_Alerts = $ccr->createElement('Alerts');
	   require_once("createCCRAlerts.php");
	   $e_Body->appendChild($e_Alerts);

	   ////////////////// Medication

	   $e_Medications = $ccr->createElement('Medications');
	   require_once("createCCRMedication.php");
	   $e_Body->appendChild($e_Medications);

	   ///////////////// Immunization

	   $e_Immunizations = $ccr->createElement('Immunizations');
	   require_once("createCCRImmunization.php");
	   $e_Body->appendChild($e_Immunizations);


	   /////////////////// Results

	   $e_Results = $ccr->createElement('Results');
	   require_once("createCCRResult.php");
	   $e_Body->appendChild($e_Results);


	   /////////////////// Procedures

	   $e_Procedures = $ccr->createElement('Procedures');
	   require_once("createCCRProcedure.php");
	   $e_Body->appendChild($e_Procedures);

	   //////////////////// Footer

	   // $e_VitalSigns = $ccr->createElement('VitalSigns');
	   // $e_Body->appendChild($e_VitalSigns);

	   /////////////// Actors

	   $e_Actors = $ccr->createElement('Actors');
	   require_once("createCCRActor.php");
	   $e_ccr->appendChild($e_Actors);


	   // save created CCR in file
	   
	   echo " \n action=".$action;
	   
	   
	   if ($action=="generate"){
	   	gnrtCCR($ccr);
	   }
	   
	   if($action == "viewccd"){
	   	viewCCD($ccr);
	   }
	}
	
	function gnrtCCR($ccr){
		global $css_header;
		echo "\n css_header=$css_header";
		$ccr->preserveWhiteSpace = false;
		$ccr->formatOutput = true;
		$ccr->save('generatedXml/ccrDebug.xml');
		
		$xmlDom = new DOMDocument();
		$xmlDom->loadXML($ccr->saveXML());
		
		$ss = new DOMDocument();
		$ss->load('ccr.xsl');
		
		$proc = new XSLTProcessor();
		
		$proc->importStylesheet($ss);
		$s_html = $proc->transformToXML($xmlDom);
		
		echo '-->';
		echo $s_html;
		
	}
	
	function viewCCD($ccr){
		
		$ccr->preserveWhiteSpace = false;
		$ccr->formatOutput = true;
		
		$ccr->save('generatedXml/ccrForCCD.xml');
		
		$xmlDom = new DOMDocument();
		$xmlDom->loadXML($ccr->saveXML());
		
		$ccr_ccd = new DOMDocument();
		$ccr_ccd->load('ccd/ccr_ccd.xsl');

		$xslt = new XSLTProcessor();
		$xslt->importStylesheet($ccr_ccd);
		
		$ccd = new DOMDocument();
		$ccd->preserveWhiteSpace = false;
		$ccd->formatOutput = true;
		
		$ccd->loadXML($xslt->transformToXML($xmlDom));
		
		$ccd->save('generatedXml/ccdDebug.xml');
		

		$ss = new DOMDocument();
		$ss->load("ccd/cda.xsl");
				
		$xslt->importStyleSheet($ss);

		$html = $xslt->transformToXML($ccd);

		echo '-->';
		echo $html;
		
	
	}

	
	function sourceType($ccr, $uuid){
		
		$e_Source = $ccr->createElement('Source');
		
		$e_Actor = $ccr->createElement('Actor');
		$e_Source->appendChild($e_Actor);
		
		$e_ActorID = $ccr->createElement('ActorID',$uuid);
		$e_Actor->appendChild($e_ActorID);
		
		return $e_Source;
	}

	
createCCR($_POST['ccrAction']);

?>
