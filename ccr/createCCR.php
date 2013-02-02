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


//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

// check if using the patient portal
//(if so, then use the portal authorization)
if (isset($_GET['portal_auth'])) {
  $landingpage = "../patients/index.php";
  session_start();
  if ( isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite']) ) {
    $pid = $_SESSION['pid'];
    $ignoreAuth=true;
    global $ignoreAuth;
  }
  else {
    session_destroy();
    header('Location: '.$landingpage.'?w');
    exit;
  }
}

require_once(dirname(__FILE__) . "/../interface/globals.php");
require_once(dirname(__FILE__) . "/../library/sql-ccr.inc");
require_once(dirname(__FILE__) . "/../library/classes/class.phpmailer.php");
require_once(dirname(__FILE__) . "/uuid.php");
require_once(dirname(__FILE__) . "/transmitCCD.php");
require_once(dirname(__FILE__) . "/../custom/code_types.inc.php");

function createCCR($action,$raw="no",$requested_by=""){

  $authorID = getUuid();
  $patientID = getUuid();
  $sourceID = getUuid();
  $oemrID = getUuid();
  
  $result = getActorData();
  while($res = sqlFetchArray($result[2])){
    ${"labID{$res['id']}"} = getUuid();
  }

	   $ccr = new DOMDocument('1.0','UTF-8');
	   $e_styleSheet = $ccr->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="stylesheet/ccr.xsl"');
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

	   //$e_Procedures = $ccr->createElement('Procedures');
	   //require_once("createCCRProcedure.php");
	   //$e_Body->appendChild($e_Procedures);

	   //////////////////// Footer

	   // $e_VitalSigns = $ccr->createElement('VitalSigns');
	   // $e_Body->appendChild($e_VitalSigns);

	   /////////////// Actors

	   $e_Actors = $ccr->createElement('Actors');
	   require_once("createCCRActor.php");
	   $e_ccr->appendChild($e_Actors);
	   
	   if ($action=="generate"){
	   	gnrtCCR($ccr,$raw);
	   }
	   
	   if($action == "viewccd"){
	   	viewCCD($ccr,$raw,$requested_by);
	   }
	}
	
	function gnrtCCR($ccr,$raw="no"){
		global $pid;

		$ccr->preserveWhiteSpace = false;
		$ccr->formatOutput = true;

		if ($raw == "yes") {
			// simply send the xml to a textarea (nice debugging tool)
			echo "<textarea rows='35' cols='500' style='width:95%' readonly>";
			echo $ccr->saveXml();
			echo "</textarea>";
			return;
                }	

                else if ($raw == "hybrid") {
			// send a file that contains a hybrid file of the raw xml and the xsl stylesheet
			createHybridXML($ccr);
		}

                else if ($raw == "pure") {
			// send a zip file that contains a separate xml data file and xsl stylesheet
			if (! (class_exists('ZipArchive')) ) {
                                displayError(xl("ERROR: Missing ZipArchive PHP Module"));
				return;
			}
			$tempDir = $GLOBALS['temporary_files_dir'];
			$zipName = $tempDir . "/" . getReportFilename() . "-ccr.zip";
			if (file_exists($zipName)) {
				unlink($zipName);
			}	
			$zip = new ZipArchive();
			if (!($zip)) {
				displayError(xl("ERROR: Unable to Create Zip Archive."));
				return;
			}
                        if ( $zip->open($zipName, ZIPARCHIVE::CREATE) ) {
				$zip->addFile("stylesheet/ccr.xsl", "stylesheet/ccr.xsl");
				$xmlName = $tempDir . "/" . getReportFilename() . "-ccr.xml";
				if (file_exists($xmlName)) {
					unlink($xmlName);
				}
				$ccr->save($xmlName);
				$zip->addFile($xmlName, basename($xmlName) );
				$zip->close();
				header("Pragma: public");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Content-Type: application/force-download");
				header("Content-Length: " . filesize($zipName));
				header("Content-Disposition: attachment; filename=" . basename($zipName) . ";");
				header("Content-Description: File Transfer");
				readfile($zipName);
				unlink($zipName);
				unlink($xmlName);
				exit(0);
			}
			else {
				displayError(xl("ERROR: Unable to Create Zip Archive."));
				return;
			}
                }

		else {
			header("Content-type: application/xml");
                	echo $ccr->saveXml();
		}
		
	}
	
	function viewCCD($ccr,$raw="no",$requested_by=""){
		global $pid;
		
		$ccr->preserveWhiteSpace = false;
		$ccr->formatOutput = true;
		
		$ccr->save(dirname(__FILE__) .'/generatedXml/ccrForCCD.xml');

		$xmlDom = new DOMDocument();
		$xmlDom->loadXML($ccr->saveXML());
		
		$ccr_ccd = new DOMDocument();
		$ccr_ccd->load(dirname(__FILE__) .'/ccd/ccr_ccd.xsl');

		$xslt = new XSLTProcessor();
		$xslt->importStylesheet($ccr_ccd);
		
		$ccd = new DOMDocument();
		$ccd->preserveWhiteSpace = false;
		$ccd->formatOutput = true;
		
		$ccd->loadXML($xslt->transformToXML($xmlDom));
		
		$ccd->save(dirname(__FILE__) .'/generatedXml/ccdDebug.xml');
		
                if ($raw == "yes") {
                  // simply send the xml to a textarea (nice debugging tool)
                  echo "<textarea rows='35' cols='500' style='width:95%' readonly>";
                  echo $ccd->saveXml();
                  echo "</textarea>";
                  return;
                }

                if ($raw == "pure") {
                        // send a zip file that contains a separate xml data file and xsl stylesheet
                        if (! (class_exists('ZipArchive')) ) {
                                displayError(xl("ERROR: Missing ZipArchive PHP Module"));
                                return;
                        }
                        $tempDir = $GLOBALS['temporary_files_dir'];
                        $zipName = $tempDir . "/" . getReportFilename() . "-ccd.zip";
                        if (file_exists($zipName)) {
                                unlink($zipName);
                        }
                        $zip = new ZipArchive();
                        if (!($zip)) {
                                displayError(xl("ERROR: Unable to Create Zip Archive."));
                                return;
                        }
                        if ( $zip->open($zipName, ZIPARCHIVE::CREATE) ) {
                                $zip->addFile("stylesheet/cda.xsl", "stylesheet/cda.xsl");
                                $xmlName = $tempDir . "/" . getReportFilename() . "-ccd.xml";
                                if (file_exists($xmlName)) {
                                        unlink($xmlName);
                                }
				$e_styleSheet = $ccd->createProcessingInstruction('xml-stylesheet', 
					'type="text/xsl" href="stylesheet/cda.xsl"');
				$ccd->insertBefore($e_styleSheet,$ccd->firstChild);
                                $ccd->save($xmlName);
                                $zip->addFile($xmlName, basename($xmlName) );
                                $zip->close();
                                header("Pragma: public");
                                header("Expires: 0");
                                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                                header("Content-Type: application/force-download");
                                header("Content-Length: " . filesize($zipName));
                                header("Content-Disposition: attachment; filename=" . basename($zipName) . ";");
                                header("Content-Description: File Transfer");
                                readfile($zipName);
                                unlink($zipName);
                                unlink($xmlName);
                                exit(0);
                        }
                        else {
                                displayError(xl("ERROR: Unable to Create Zip Archive."));
                                return;
                        }
                }

                if (substr($raw,0,4)=="send") {
                   $recipient = trim(stripslashes(substr($raw,5)));
		   $result=transmitCCD($ccd,$recipient,$requested_by);
		   echo htmlspecialchars($result,ENT_NOQUOTES);
		   return;
                }

		$ss = new DOMDocument();
		$ss->load(dirname(__FILE__) ."/stylesheet/cda.xsl");
				
		$xslt->importStyleSheet($ss);

		$html = $xslt->transformToXML($ccd);

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


	function displayError($message) {
		echo '<script type="text/javascript">alert("' . addslashes($message) . '");</script>';
	}


	function createHybridXML($ccr) {

		// save the raw xml
		$main_xml = $ccr->saveXml();

		// save the stylesheet
		$main_stylesheet = file_get_contents('stylesheet/ccr.xsl');

		// replace stylesheet link in raw xml file
		$substitute_string = '<?xml-stylesheet type="text/xsl" href="#style1"?>
<!DOCTYPE ContinuityOfCareRecord [
<!ATTLIST xsl:stylesheet id ID #REQUIRED>
]>
';
		$replace_string = '<?xml-stylesheet type="text/xsl" href="stylesheet/ccr.xsl"?>';
		$main_xml = str_replace($replace_string,$substitute_string,$main_xml);

		// remove redundant xml declaration from stylesheet
		$replace_string = '<?xml version="1.0" encoding="UTF-8"?>';
		$main_stylesheet = str_replace($replace_string,'',$main_stylesheet);

		// embed the stylesheet in the raw xml file
		$replace_string ='<ContinuityOfCareRecord xmlns="urn:astm-org:CCR">';
		$main_stylesheet = $replace_string.$main_stylesheet;
		$main_xml = str_replace($replace_string,$main_stylesheet,$main_xml);

		// insert style1 id into the stylesheet parameter
		$substitute_string = 'xsl:stylesheet id="style1" exclude-result-prefixes';
		$replace_string = 'xsl:stylesheet exclude-result-prefixes';
		$main_xml = str_replace($replace_string,$substitute_string,$main_xml);

		// prepare the filename to use
		//   LASTNAME-FIRSTNAME-PID-DATESTAMP-ccr.xml
		$main_filename = getReportFilename()."-ccr.xml";

		// send the output as a file to the user
		header("Content-type: text/xml");
		header("Content-Disposition: attachment; filename=" . $main_filename . "");
		echo $main_xml;
	}
	
if($_POST['ccrAction']) {
  $raw=$_POST['raw'];
  /* If transmit requested, fail fast if the recipient address fails basic validation */
  if (substr($raw,0,4)=="send") {
    $send_to = trim(stripslashes(substr($raw,5)));
    if (!PHPMailer::ValidateAddress($send_to)) {
      echo(htmlspecialchars( xl('Invalid recipient address. Please try again.'), ENT_QUOTES));
      return;
    }
    createCCR($_POST['ccrAction'],$raw,$_POST['requested_by']);
  } else {
    createCCR($_POST['ccrAction'],$raw);
  }
}

?>
