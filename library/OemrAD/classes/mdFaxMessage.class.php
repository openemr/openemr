<?php

namespace OpenEMR\OemrAd;

@include_once(__DIR__ . "/../interface/globals.php");
@include_once(__DIR__ . '/mdEmailMessage.class.php');
@include_once(__DIR__ . "/mdAttachment.class.php");

use OpenEMR\OemrAd\EmailMessage;
use OpenEMR\OemrAd\Attachment;
use Mpdf\Mpdf;

class FaxMessage {

	/*Constructor*/
	public function __construct() {
	}

	public static function getConfigVars() {
		$returnList = new \stdClass();
		$returnList->fax_user = isset($GLOBALS['FAX_USER']) ? $GLOBALS['FAX_USER'] : "";
		$returnList->fax_pass = isset($GLOBALS['FAX_PASS']) ? $GLOBALS['FAX_PASS'] : "";

		return $returnList;
	}

	/*Merge message with attachment document*/
	public function mergeMessageContent($message, $files) {
		$messageStr = $message;
		if(!empty($files) && is_array($files) && count($files) > 0) {
			$messageStr .= '<div class="attachmentContainer"><br/><br/><ul>';
				foreach ($files as $key => $file) {
					$viewLink = "";
					if(isset($file['id']) && !empty($file['id'])) {
						$viewLink = ' - <a style="cursor: pointer;" class="docrow" id="'.$file['id'].'" >(View)</a>';
					}

					$fileURL = self::getWebsiteURL() . $file['url'];
					$messageStr .= '<li><a href="'.$fileURL.'" download="'.$file['file_name'].'">'.$file['file_name'].'</a>'.$viewLink.'</li>';
				}
			$messageStr .= '</ul></div>';
		}
		return $messageStr;
	}

	public static function getFormEncounters($pid) {
		$results = array();
		$res = sqlStatement("SELECT forms.encounter, forms.form_id, forms.form_name, " .
                    "forms.formdir, forms.date AS fdate, form_encounter.date " .
                    ",form_encounter.reason, u.lname, u.fname, ".
		    "CONCAT(fname, ' ', lname) AS drname ".
		    "FROM forms, form_encounter LEFT JOIN users AS u ON ".
		    "(form_encounter.provider_id = u.id) WHERE " .
                    "forms.pid = '$pid' AND form_encounter.pid = '$pid' AND " .
                    "form_encounter.encounter = forms.encounter " .
                    " AND forms.deleted=0 ".
                    "ORDER BY form_encounter.date $pat_rpt_order, form_encounter.encounter $pat_rpt_order, fdate ASC");

		while ($result = sqlFetchArray($res)) {
			$results[] = $result;
		}
		return $results;
	}

	public function getEncouterList($pid) {
		$enc = self::getFormEncounters($pid);

		$res2 = sqlStatement("SELECT name FROM registry ORDER BY priority");
		$html_strings = array();
		$registry_form_name = array();
		while ($result2 = sqlFetchArray($res2)) {
		    array_push($registry_form_name, trim($result2['name']));
		}

		$isfirst = 1;
		foreach ($enc as $key => $result) {
			if ($result{"form_name"} == "New Patient Encounter") {
				if ($isfirst == 0) {
		            foreach ($registry_form_name as $var) {
		                if ($toprint = $html_strings[$var]) {
		                    foreach ($toprint as $var) {
		                        print $var;
		                    }
		                }
		            }

		            $html_strings = array();
		             echo "</div>\n"; // end DIV encounter_forms
		             echo "</div>\n\n";  //end DIV encounter_data
		             echo "<br>";
		        }

		        $result['raw_text'] = $result{"reason"}.
		                " (" . date("Y-m-d", strtotime($result{"date"})) .
		                ") ". $result['drname'];

		        $isfirst = 0;
		        echo "<div class='encounter_data'>\n";
		        echo "<input type=checkbox ".
		        		" data-title='" . $result['raw_text'] . "'".
		                " name='" . $result{"formdir"} . "_" .  $result{"form_id"} . "'".
		                " id='" . $result{"formdir"} . "_" .  $result{"form_id"} . "'".
		                " value='" . $result{"encounter"} . "'" .
		                " class='encounter'".
		                " >";

		        echo $result['raw_text'] . "\n";
		        echo "<div class='encounter_forms'>\n";
			} else {
				$form_name = trim($result{"form_name"});
		        //if form name is not in registry, look for the closest match by
		        // finding a registry name which is  at the start of the form name.
		        //this is to allow for forms to put additional helpful information
		        //in the database in the same string as their form name after the name
		        $form_name_found_flag = 0;
		        foreach ($registry_form_name as $var) {
		            if ($var == $form_name) {
		                $form_name_found_flag = 1;
		            }
		        }

		        // if the form does not match precisely with any names in the registry, now see if any front partial matches
		        // and change $form_name appropriately so it will print above in $toprint = $html_strings[$var]
		        if (!$form_name_found_flag) {
		            foreach ($registry_form_name as $var) {
		                if (strpos($form_name, $var) == 0) {
		                    $form_name = $var;
		                }
		            }
		        }

		        if (!is_array($html_strings[$form_name])) {
		            $html_strings[$form_name] = array();
		        }

		        array_push($html_strings[$form_name], "<input type='checkbox' ".
		        										" data-title='" . xl_form_title($result{"form_name"}) . "'".
		                                                " name='" . $result{"formdir"} . "_" . $result{"form_id"} . "'".
		                                                " id='" . $result{"formdir"} . "_" . $result{"form_id"} . "'".
		                                                " value='" . $result{"encounter"} . "'" .
		                                                " class='encounter_form' ".
		                                                ">" . xl_form_title($result{"form_name"}) . "<br>\n");
			}
		}
		?>
		<script type="text/javascript">
			$( ".counterListContainer input[type=checkbox]" ).each(function( index ) {
				var eleid = $(this).attr('id');
				if(selectedEncounterList.hasOwnProperty(eleid)) {
					$(this).prop('checked', true);
				} 
			});


			$('.encounter').on("click", function(e) {
				var isChecked = $(this).prop("checked");
				var childContainer = $(this).parent().find('.encounter_forms input[type=checkbox]');

				$(childContainer).each(function( index ) {
					$(this).prop('checked', isChecked);
				});
			});

			$('.btn-encountersaveBtn').on("click", function (e) {
				var tempSelected = {};

				$( ".encounter_data" ).each(function( index ) {
				  var parentCheckbox = $(this).find('input[type=checkbox]');
				  var parentTitleAttr = $(parentCheckbox).data('title');
				  var childContainer = $(this).find('.encounter_forms');
				  var tempEle = {};

				  var isParentChecked = false;
				  var parentId = null;

				  if($(parentCheckbox).prop("checked") == true) {
				  	parentId = $(parentCheckbox).attr('id');
				  	parentVal = $(parentCheckbox).val();
				  	tempSelected[parentId] = { "title" : parentTitleAttr, "value" : parentVal };
				  }

				  var childCheckbox = $(childContainer).find('input[type=checkbox]');
				  $(childCheckbox).each(function( index ) {
				  	var childTitleAttr = $(this).data('title');
				  	var childId = $(this).attr('id');
				  	var childVal = $(this).val();

				  	if($(this).prop("checked") == true) {
				  		tempSelected[childId] = { "title" : childTitleAttr, "value" : childVal };

				  		if(parentId != null) {
				  			tempSelected[childId]['parentId'] = parentId;
				  		}
				  	}
				  });
				});
				selectedEncounterList = tempSelected;
			});
		</script>
		<?php
	}

	/*
	public static function AddAttachmentToFax($pid, &$fax_data, $request, $files = array()) {
		$attachmentList = array();
		$fax_data['message_content'] = $fax_data['html'];
		$attachmentCount = 0;

		if(isset($files['files'])) {
			$fax_data['files_length'] = $request['files_length'];
			$fax_data['files'] = $files['files'];
		}

		if(isset($request['uploadFileList']) && !empty($request['uploadFileList'])) {
			$tempUploadFileList = json_decode($request['uploadFileList'], true);
			$fax_data['uploadFileList'] = $tempUploadFileList;
		}

		if(isset($request['docsList']) && !empty($request['docsList'])) {
			$tempDocsList = json_decode($request['docsList'], true);
			$fax_data['docsList'] = $tempDocsList;
		}

		if(isset($request['documentFiles']) && !empty($request['documentFiles'])) {
			$tempDocFiles = json_decode($request['documentFiles']);
			$fax_data['documentFiles'] = $tempDocFiles;
		}

		if(isset($request['notes']) && !empty($request['notes'])) {
			$tempDocFiles = json_decode($request['notes']);
			$fax_data['notes'] = $tempDocFiles;
		}

		if(isset($request['orderList']) && !empty($request['orderList'])) {
			$tempOrderList = json_decode($request['orderList'], true);
			$fax_data['orderList'] = $tempOrderList;
		}

		if(isset($request['encounters']) && !empty($request['encounters'])) {
			$tempEncounters = (array)json_decode($request['encounters'], true);
			$fax_data['encounters'] = $tempEncounters;

			$fax_data['encounters_pid'] = $pid;
			if(is_array($fax_data['encounters']) && count($fax_data['encounters']) > 0) {
				$fax_data['encounters_pid'] = reset($fax_data['encounters'])['pid'];
			}
		}

		if(isset($request['encounterIns']) && !empty($request['encounterIns'])) {
			$tempEncounters = (array)json_decode($request['encounterIns'], true);
			$fax_data['encounterIns'] = $tempEncounters;

			$fax_data['encounterIns_pid'] = $pid;
			if(is_array($fax_data['encounterIns']) && count($fax_data['encounterIns']) > 0) {
				$fax_data['encounterIns_pid'] = reset($fax_data['encounterIns'])['pid'];
			}
		}

		if((isset($fax_data['encounterIns']) && !empty($fax_data['encounterIns'])) || ($request['isCheckEncounterInsDemo'] == "true") ) {
			$encounterQtrData = array();
			$ins_html = EmailMessage::generateCaseHTML($fax_data['encounterIns_pid'], $fax_data);
			$encounterPDF = EmailMessage::getAndSaveEncounterPDF($fax_data['encounterIns_pid'], $encounterQtrData, 'fax_encounters_demos_and_ins', array(), $ins_html, $request['isCheckEncounterInsDemo']);

			if(isset($encounterPDF['page_count'])) {
				$attachmentCount += $encounterPDF['page_count'];
			}

			$attachmentList[] = array(
				'action' => 'stay',
				'url' => $encounterPDF['path'],
				'name' => $encounterPDF['name']
			);
		}

		if(isset($fax_data['encounters']) && !empty($fax_data['encounters'])) {
			$encounterQtrData = EmailMessage::encounterQtrDataGenerator($fax_data, 'encounters');
			$encounterPDF = EmailMessage::getAndSaveEncounterPDF($fax_data['encounters_pid'], $encounterQtrData, 'fax_encounters_and_forms', array(), '', $request['isCheckEncounterDemo']);

			if(isset($encounterPDF['page_count'])) {
				$attachmentCount += $encounterPDF['page_count'];
			}

			$attachmentList[] = array(
				'action' => 'stay',
				'url' => $encounterPDF['path'],
				'name' => $encounterPDF['name']
			);
		}

		if(isset($fax_data['orderList']) && !empty($fax_data['orderList'])) {
			$orderData = EmailMessage::generateOrderData($fax_data['orderList'], $pid);
			$orderPDF = EmailMessage::getOrderPDF($pid, $orderData, 'orders');
			
			if(isset($orderPDF['page_count'])) {
				$attachmentCount += $orderPDF['page_count'];
			}

			$attachmentList[] = array(
				'action' => 'stay',
				'url' => $orderPDF['path'],
				'name' => $orderPDF['name']
			);
		}


		if(isset($fax_data['notes']) && !empty($fax_data['notes'])) {
	    	$noteStr = "";
	    	$noteStr .= '<h1>Internal Notes</h1><ul style="padding-left:15px; font-size:16px;">';
	    	$nCounter = 1;

			foreach ($fax_data['notes'] as $key => $note) {
				$noteObj = (array)$note;
				$noteStr .= "<li>".preg_replace("/[\r\n]/", "\n   ", strip_tags($noteObj['raw_body']))."</li>";
				$nCounter++;
			}
			$noteStr .= "</ul>";

			$notesPDF = self::generateAttachmentPDF($noteStr, 'fax_internal_notes');

			if(isset($notesPDF['page_count'])) {
				$attachmentCount += $notesPDF['page_count'];
			}

			$attachmentList[] = array(
				'action' => 'stay',
				'url' => $notesPDF['path'],
				'name' => $notesPDF['name']
			);
		}

		if(isset($fax_data['files']) && isset($fax_data['files_length'])) {
			for ($i=0; $i < $fax_data['files_length'] ; $i++) {
				$ext = end(explode('.', $fax_data['files']['name'][$i]));

				if($ext == "pdf") {
					$pdf_count = self::gePDF($fax_data['files']['tmp_name'][$i]);
					$attachmentCount += $pdf_count;
				} else {
					$attachmentCount++;	
				}

				$attachmentList[] = array(
					'action' => 'upload',
					'url' => $fax_data['files']['tmp_name'][$i],
					'name' => $fax_data['files']['name'][$i]
				);
			}
		}

		if(isset($fax_data['uploadFileList'])) {
			foreach ($fax_data['uploadFileList'] as $key => $fileItem) {
				if($fileItem['type'] == "file_url") {
					$filePath = str_replace("file://","",$fileItem['path']);
					$ext = end(explode('.', $fileItem['file_name']));

					if($ext == "pdf") {
						$pdf_count = self::gePDF($filePath);
						$attachmentCount += $pdf_count;
					} else {
						$attachmentCount++;	
					}
					
					$fileItem['ignore'] = true;
					$attachmentList[] = $fileItem;
				}
			}
		}

		if(isset($fax_data['documentFiles'])) {
			foreach ($fax_data['documentFiles'] as $key => $doc) {
				$docObj = (array)$doc;
				if($docObj['type'] == "file_url") {
					$filePath = str_replace("file://","",$docObj['url']);

					$attachmentList[] = array(
						'action' => 'stay',
						'url' => $filePath,
						'name' => $docObj['baseFileName'],
						'id' => isset($docObj['id']) ? $docObj['id'] : '',
					);

					$attachmentCount++;
				}
			}
		}

		if(isset($fax_data['docsList']) && is_array($fax_data['docsList'])) {
			foreach ($fax_data['docsList'] as $key => $doc) {
				$ext = end(explode('.', $doc['name']));
				$attachmentList[] = $doc;

				if($ext == "pdf") {
					$pdf_count = self::gePDF($doc['url']);
					$attachmentCount += $pdf_count;
				} else {
					$attachmentCount++;	
				}
			}
		}

		//Store fax attachment
		$attchFiles = self::saveAttachmentFile($attachmentList);

		//Upload file list process
		$baseUploadList = EmailMessage::generateUploadFileList($attchFiles);
		$fax_data['baseDocList'] = EmailMessage::mergeBaseUploadList($request['baseDocList'], $baseUploadList);

		$faxFiles = array();
		if(isset($fax_data['html']) && !empty($fax_data['html'])) {
			$notesPDF = self::generateAttachmentPDF($fax_data['html'], 'fax_message');

			if(isset($notesPDF['page_count'])) {
				$attachmentCount += $notesPDF['page_count'];
			}

			if(!empty($notesPDF)) {
				$faxFiles[] = array(
					'name' => $notesPDF['name'],
					'path' => $notesPDF['path'],
				);
			}
		}

		foreach ($attchFiles as $key => $attachmentItem) {
			if(isset($attachmentItem['file_name'])) {
				$faxFiles[] = array(
					'name' => $attachmentItem['file_name'],
					'path' => $attachmentItem['path'],
				);

				if(isset($attchFiles[$key]['base64_content'])) {
					unset($attchFiles[$key]['base64_content']);
				}
			}
		}

		$fax_data['files'] = $faxFiles;
		$fax_data['attchFiles'] = $attchFiles;

		$fax_data['cost_data'] = self::calculateCost($attachmentCount);

		return $attchFiles;
	}
	*/

	public static function gePDF($path) {
		$pdftext = file_get_contents($path);
  		$num = preg_match_all("/\/Page\W/", $pdftext, $dummy);
  		return $num;
	}

	/*Calculate page cost*/
	public static function calculateCost($pageCount) {
		if(empty($GLOBALS['FAX_INITIAL_COST']) || empty($GLOBALS['FAX_ADDITIONAL_COST']) || empty($GLOBALS['FAX_LIMIT_COST'])) {
			return array(
				'status' => true,
				'noalert' => true
			);

		}

		$gbl_currency_symbol = $GLOBALS['gbl_currency_symbol'];

		if($pageCount > 0) {
			$initialCost = $GLOBALS['FAX_INITIAL_COST'];
			$additionalPageCost = (($pageCount-1)*$GLOBALS['FAX_ADDITIONAL_COST']);
			$totalCost = ($initialCost + $additionalPageCost);
			$limitCost = $GLOBALS['FAX_LIMIT_COST'];

			if($totalCost >  $limitCost) {
				return array(
					'status' => true,
					'confirm' => "Total page count: $pageCount\nTotal page cost($gbl_currency_symbol$totalCost) is greater than page limit cost($gbl_currency_symbol$limitCost)\nDo you want to continue?",
				);
			} else {
				return array(
					'status' => true,
					'alert' => "Total page count: $pageCount\nTotal page cost($gbl_currency_symbol$totalCost) is less than page limit cost($gbl_currency_symbol$limitCost).",
				);
			}
		}

		return array(
			'status' => false,
			'error' => 'No PDF pages found',
		);
	}

	/*Help you to get base64content*/
	/*
	public static function getFilesContent($fax_data) {
		$faxDataCount = 1;
		$faxDataList = array();
		
		foreach ($fax_data['files'] as $key => $attachmentItem) {
			$faxDataList['file'.$faxDataCount] = $attachmentItem['name'];
			$faxDataList['data'.$faxDataCount] = base64_encode(file_get_contents($attachmentItem['path']));
			$faxDataCount++;
		}

		return $faxDataList;
	}*/

	public function getAndSaveEncounterPDF($pid, $queryData, $filename = 'fax_encounters_and_forms') {
		global $srcdir, $web_root, $css_header;

		$temp_post = $_POST;
		unset($_POST);

		$temp_get = $_GET;
		unset($_GET);

		$_POST['include_demographics'] = "demographics";
		$_POST['pdf'] = "1";
		//$_GET['printable'] = "1";

		foreach ($queryData as $key => $value) {
			$_POST[$key] = $value;
		}

		ob_start();
		include_once($GLOBALS['fileroot'].'/interface/patient_file/report/custom_report.php');
		ob_get_clean();
		
		$_POST = $temp_post;
		$_GET = $temp_get;

		$pdfE = new \mPDF(
	        $GLOBALS['pdf_language'], // codepage or language/codepage or language - this can help auto determine many other options such as RTL
	        $GLOBALS['pdf_size'], // Globals default is 'letter'
	        '9', // default font size (pt)
	        '', // default_font. will set explicitly in script.
	        $GLOBALS['pdf_left_margin'],
	        $GLOBALS['pdf_right_margin'],
	        $GLOBALS['pdf_top_margin'],
	        $GLOBALS['pdf_bottom_margin'],
	        '', // default header margin
	        '', // default footer margin
	        $GLOBALS['pdf_layout']
	    ); // Globals default is 'P'

      	$pdfE->shrink_tables_to_fit = 1;
      	$keep_table_proportions = true;
      	$pdfE->use_kwt = true;
       	$pdfE->setDefaultFont('dejavusans');
       	$pdfE->autoScriptToLang = true;

		$tmpc = $content;
		$tmpc = self::replaceHTMLTags($tmpc, Array("html","head","body"));

		/*Added CSS File*/
		$tmpc .= '<link rel="stylesheet" href="'.$web_root.'/interface/themes/style_pdf.css" type="text/css">';
		$tmpc .= '<link rel="stylesheet" type="text/css" href="'.$web_root.'/library/ESign/css/esign_report.css" />';

		/*Save File*/
		$file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/message_attachments/";
		$fullfilename = $file_location . strtotime(date('Y-m-d H:i:s')) . "_". $filename . ".pdf";
		$pdfE->writeHTML($tmpc);

		$pageCount = $pdfE->page;

		$content_pdf = $pdfE->Output($fullfilename, 'F');

		return array(
        	'path' => $fullfilename,
        	'name' => $filename . ".pdf",
        	'page_count' => $pageCount
        );
	}

	public static function replaceHTMLTags($string, $tags) {
		$tags_to_strip = $tags;
		foreach ($tags_to_strip as $tag){
		    $string = preg_replace("/<\\/?" . $tag . "(.|\\s)*?>/","",$string);
		}

		return $string;
	}

	public static function generateAttachmentPDF($content, $filename, $isFile = true) {
		$file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/message_attachments/";
		$fullfilename = $file_location . strtotime(date('Y-m-d H:i:s')) . "_" . $filename . ".pdf";

		$pdf = new \mPDF();
        $pdf->writeHTML($content, false);
        
        if($isFile == true) {
	        $content_pdf = $pdf->Output($fullfilename, 'F');
	        return array(
	        	'path' => $fullfilename,
	        	'name' => $filename . ".pdf",
	        	'page_count' => $pdf->page,
	        );
    	} else {
    		$content_pdf = $pdf->Output($fullfilename, 'S');
    		return array(
	        	'base64_content' => base64_encode($content_pdf),
	        	'name' => $filename . ".pdf",
	        	'page_count' => $pdf->page,
	        );
    	}
	}

	public static function Transmit($faxData) {
		$qtrParams = array(
			'login' => $GLOBALS['FAX_USER'],
			'pass' => $GLOBALS['FAX_PASS'],
			'faxsrc' => $GLOBALS['FAX_SRC'],
			'cmd' => 'sendfax',
			'faxnum' => $faxData['fax_number'],
			'recname' => $faxData['receiver_name'],
			'xml' => 'yes'
		);

		if(empty($GLOBALS['FAX_USER']) || empty($GLOBALS['FAX_PASS'])) {
			return array(
				'status' => false,
				'error' => "Enter valid fax api credentials."
			);
		}

		if(empty($GLOBALS['FAX_SRC'])) {
			return array(
				'status' => false,
				'error' => "Enter valid fax DID to send fax from."
			);
		}

		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://api.vitelity.net/fax.php",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => http_build_query(array_merge($qtrParams, $faxData['data'])),
		  CURLOPT_HTTPHEADER => array(
		    "accept: application/json",
		    "content-type: application/x-www-form-urlencoded"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  	return array(
				'status' => false,
				'error' => json_encode($err)
			);
		} else {
			$xml = new \SimpleXMLElement($response);
			$arrayData = json_decode(json_encode((array)$xml), TRUE);

			if(!empty($arrayData) && $arrayData['status'] == "fail") {
				return array(
					'status' => true,
					'message' => implode("\n", array(self::getErrorMsg('sendfax', $arrayData['error']))),
					'data' => array(
						'status_code' => $arrayData['error'],
						'description' => self::getErrorMsg('sendfax', $arrayData['error'])
					)
				);
			} else if(!empty($arrayData) && $arrayData['status'] == "ok" && $arrayData['response'] == "ok") {
				$messages = array();
				$status_code = 'success';
				$description = 'Success';

				$faxStatusData = self::getStatus($arrayData['jobid']);

				if(isset($faxStatusData) && $faxStatusData['status'] == true) {
					$cData = isset($faxStatusData['data']['jobid_'.$arrayData['jobid']]) ? $faxStatusData['data']['jobid_'.$arrayData['jobid']] : array();

					if(!empty($cData)) {
						$status_code = $cData['status_code'];
						$description = $cData['description'];
						$messages[] = $faxStatusData['message'];

					} else {
						$status_code = 'unknown';
						$description = 'Unknown';
						$messages[] = 'Unknown';
					}

				} else {
					$status_code = 'error';
					$description = $faxStatusData['error'];
					$messages[] = $faxStatusData['error'];
				}

				if(isset($arrayData['error'])) {
					$messages[] = $arrayData['error'];
				}


				return array(
					'status' => true,
					'data' => array(
						'jobid' => $arrayData['jobid'],
						'status_code' => $status_code,
						'description' => $description
					),
					'message' => implode($messages, "\n")
				);
			}

			return array(
				'status' => false,
				'error' => 'Something Went Wrong'
			);
		}
	}

	public static function getStatus($faxIds) {
		$qtrParams = array(
			'login' => $GLOBALS['FAX_USER'],
			'pass' => $GLOBALS['FAX_PASS'],
			'cmd' => 'sentfaxstatus',
			'faxid' => $faxIds,
			'xml' => 'yes'
		);

		if(empty($GLOBALS['FAX_USER']) || empty($GLOBALS['FAX_PASS'])) {
			return array(
				'status' => false,
				'error' => "Enter valid fax api credentials."
			);
		}

		$qtrStr = http_build_query($qtrParams);

		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://api.vitelity.net/fax.php",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $qtrStr,
		  CURLOPT_HTTPHEADER => array(
		    "accept: application/json",
		    "content-type: application/x-www-form-urlencoded"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  	return array(
				'status' => false,
				'error' => json_encode($err)
			);
		} else {
			$xml = new \SimpleXMLElement($response);
			$arrayData = json_decode(json_encode((array)$xml), TRUE);

			if(!empty($arrayData) && $arrayData['status'] == "fail") {
				return array(
					'status' => true,
					'message' => implode("\n", array(self::getErrorMsg('sendfax', $arrayData['error']))),
					'data' => array(
						array(
							'status_code' => $arrayData['error'],
							'description' => self::getErrorMsg('sendfax', $arrayData['error'])
						)
					)
				);
			} else if(!empty($arrayData) && $arrayData['status'] == "ok") {
				$messages = array();
				$data = array();

				if(isset($arrayData['sentfaxes']) && isset($arrayData['sentfaxes']['fax'])) {
					$arrayData['sentfaxes']['fax'] = self::isAssoc($arrayData['sentfaxes']['fax']) == false ? $arrayData['sentfaxes']['fax'] : array($arrayData['sentfaxes']['fax']);

					foreach ($arrayData['sentfaxes']['fax'] as $key => $sentfax) {
						$status_code = '';
						$description = '';
						$jobid = '';

						if(isset($sentfax['status']) && strpos($sentfax['status'], "Success") !== false) {
							$status_code = "success";
							$description = $sentfax['status'];
						} else if(isset($sentfax['status']) && strpos($sentfax['status'], "Processing") !== false) {
							$status_code = "processing";
							$description = $sentfax['status'];
						} else if(isset($sentfax['status']) && strpos($sentfax['status'], "Failed") !== false) {
							$status_code = "failed";
							$description = $sentfax['status'];
							$messages[] = $sentfax['status'];
						} else {
							$status_code = 'unknown';
							$description = 'Unknown';
							$messages[] = 'Status Unknown';
						}

						$jobid = isset($sentfax['jobid']) ? $sentfax['jobid'] : '';

						if(!empty($jobid)) {
							$data['jobid_'.$jobid] = array(
								'jobid' => $jobid,
								'status_code' => $status_code,
								'description' => $description
							);
						}
					}
				}

				return array(
					'status' => true,
					'data' => $data,
					'message' => implode($messages, "\n")
				);
			}
		}

		return array(
			'status' => false,
			'error' => 'Something Went Wrong'
		);
	}

	public static function isAssoc(array $arr){
    	if (array() === $arr) return false;
    	return array_keys($arr) !== range(0, count($arr) - 1);
	}

	public static function getErrorMsg($type, $error) {
		$errorList = array(
			'sendfax' => array(
				'invalidsourcenumber' => 'Failed - Invalid faxsrc (sent from)',
				'invalidnumber' => 'Failed - Invalid faxnum (sent to)',
				'missingdata' => 'Failed - Missing Data',
				'invalidfiletypes' => 'Failed - Invalid File Types',
				'invalid' => 'Failed - Invalid, Reason Unknown',
			),
			'sentfaxstatus' => array(
				'none' => 'Failed - No Faxes Found',
			),
			'getfax' => array(
				'missing' => 'Failed - Fax File Missing',
				'invalid' => 'Failed - Invalid Faxid'
			)
		);

		if($error == "invalidauth") {
			return "Failed - Invalid Authentication";
		}

		return isset($errorList[$type]) && isset($errorList[$type][$error]) ? $errorList[$type][$error] : $error;
	}

	/* Get Letter data from datatable by passing different parameters */
	public static function getFaxMsg($faxId = '', $messageId= '', $pid = '') {
		$binds = array();
		$where = '';
		if(!empty($faxId)) {
			if(empty($where)) {
				$where .= 'WHERE fax_id = ? ';
			} else {
				$where .= 'AND fax_id = ? ';
			}
			$binds[] = $faxId;
		}

		if(!empty($messageId)) {
			if(empty($where)) {
				$where .= 'WHERE message_id = ? ';
			} else {
				$where .= 'AND message_id = ? ';
			}
			$binds[] = $messageId;
		}

		if(!empty($pid)) {
			if(empty($where)) {
				$where .= 'WHERE mu.pid = ? ';
			} else {
				$where .= 'AND mu.pid = ? ';
			}
			$binds[] = $pid;
		}

		$query = 'SELECT pl.*, mu.* FROM `fax_messages` AS pl ';
		$query .= 'LEFT JOIN `message_log` AS mu ON pl.message_id = mu.id ';
		$query .= $where;
		$res = sqlStatement($query, $binds);
		$data = array();
		while($row = sqlFetchArray($res)) {
			$data[] = $row;
		}
		return $data;
	}

	/*Save Fax*/
	public static function saveFax($messageId, $data) {
		
		if(isset($messageId) && !empty($messageId)) {
			$faxData = isset($data['jobid']) ? self::getFaxMsg($data['jobid']) : array();

			if(isset($faxData) && count($faxData) > 0 && isset($data['jobid'])) {
				$binds = array();
				$binds[] = (isset($data) && isset($data['status_code'])) ? $data['status_code'] : '';
				$binds[] = (isset($data) && isset($data['description'])) ? $data['description'] : '';
				$binds[] = (isset($data) && isset($data['file_name'])) ? $data['file_name'] : '';
				$binds[] = (isset($data) && isset($data['path'])) ? $data['path'] : '';
				$binds[] = date("Y-m-d H:i:s");
				$binds[] = (isset($data) && isset($data['receivers_name'])) ? $data['receivers_name'] : '';
				$binds[] = isset($data['jobid']) ? $data['jobid'] : '';

				$sql = "UPDATE `fax_messages` SET ";
				$sql .= "status_code=?, description=?, file_name=?, url=?, update_date=?, receivers_name=? ";
				$sql .= "WHERE fax_id = ? ";
				return sqlInsert($sql, $binds);
			} else {
				$binds = array();
				$binds[] = isset($data['jobid']) ? $data['jobid'] : null;
				$binds[] = $messageId;
				$binds[] = (isset($data) && isset($data['status_code'])) ? $data['status_code'] : '';
				$binds[] = (isset($data) && isset($data['description'])) ? $data['description'] : '';
				$binds[] = (isset($data) && isset($data['file_name'])) ? $data['file_name'] : '';
				$binds[] = (isset($data) && isset($data['path'])) ? $data['path'] : '';
				$binds[] = (isset($data) && isset($data['receivers_name'])) ? $data['receivers_name'] : '';
				
				$sql = "INSERT INTO `fax_messages` SET ";
				$sql .= "fax_id=?, message_id=?, status_code=?, description=?, file_name=?, url=?, receivers_name=? ";
				return sqlInsert($sql, $binds);
			}
		}

		return false;
	}

	public function getFaxMessageList($pid, $activity = 1) {
		// Retrieve all fax messages
		$fax_list = array();
		$sql = "SELECT ml.*, ";
		$sql .= "CONCAT(IFNULL(SUBSTR(us.`fname`,1,1),''), ' ', us.`lname`) AS 'user_name' ";
		$sql .= "FROM `message_log` ml ";
		$sql .= "LEFT JOIN `users` us ON ml.`userid` = us.`id` ";
		$sql .= "WHERE `pid`=? AND `type` LIKE 'FAX' ";
		if ($activity != 'all') $sql .= "AND ml.`activity` = '$activity' ";
		$sql .= " ORDER BY ml.`id`";

		$fax_result = sqlStatementNoLog($sql, array($pid));
		while ($fax_data = sqlFetchArray($fax_result)) {
			$fax_list[] = $fax_data;
		}

		return $fax_list;
	}

	/*Get website url*/
	public static function getWebsiteURL() {
		global $web_root, $webserver_root;
		$prefix = 'http://';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $prefix = "https://";
        }
        return $prefix . $_SERVER['HTTP_HOST'] . $web_root;
	}

	/*Help to get save url*/
	public static function getSaveURL($url) {
		global $webserver_root;
		$site_url = self::getWebsiteURL();
		$sUrl = str_replace($webserver_root, "", $url);
		$sUrl = substr($sUrl, strpos($sUrl, "/sites/"));
		return $sUrl;
	}

	/*Save fax attachment file on to server*/
	public static function saveAttachmentFile($attachments) {
		$attachmentDetails = array();
		$file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/message_attachments/";

		if (!file_exists($file_location)) {
		    mkdir($file_location, 0777, true);
		}

		foreach ($attachments as $key => $attachment) {
			$filename = $attachment['name'];
			$fullfilename = $file_location . strtotime(date('Y-m-d H:i:s')) . "_" . EmailMessage::cleanStr($filename);

			if($attachment['ignore'] === true) {
				$attachmentDetails[] = $attachment;
			} else if($attachment['action'] == "save") {
				if (!file_exists($fullfilename)) {
	                $fp = fopen($fullfilename, "w+");
	                fwrite($fp, $attachment['attachment']);
	                fclose($fp);

	                $attachmentDetails[] = array(
	                	'type' => 'file_url',
	                	'path' => $fullfilename,
	                	'url' => self::getSaveURL($fullfilename),
	                	'file_name' => $filename,
	                	//'base64_content' => base64_encode(file_get_contents($fullfilename))
	                );
	        	}
        	} else if($attachment['action'] == "upload") {
        		move_uploaded_file($attachment['url'], $fullfilename);
        		$attachmentDetails[] = array(
        			'action' => 'upload',
                	'type' => 'file_url',
                	'path' => $fullfilename,
                	'url' => self::getSaveURL($fullfilename),
                	'file_name' => $filename,
                	//'base64_content' => base64_encode(file_get_contents($fullfilename))
                );
        	} else if($attachment['action'] == "stay") {
        		$attachmentDetails[] = array(
                	'type' => 'file_url',
                	'path' => $attachment['url'],
                	'url' => self::getSaveURL($attachment['url']),
                	'file_name' => $filename,
                	'id' => isset($attachment['id']) ? $attachment['id'] : '',
                	//'base64_content' => base64_encode(file_get_contents($attachment['url']))
                );
        	}
		}

		return $attachmentDetails;
	}

	/*Save file for incoming save*/
	public function writeFile($attachments, $emailIdent) {
		$attachmentDetails = array();

		/* iterate through each attachment and save it */
        foreach($attachments as $attachment)
        {
            if($attachment['is_attachment'] == 1)
            {
            	$attachment['action'] = 'save'; 
                $responce = self::saveAttachmentFile(array($attachment));
                if(!empty($responce)) {
                	$attachmentDetails = array_merge($attachmentDetails, $responce);
                }
            }
        }
        return $attachmentDetails;
	}

	/* Get Fax data from datatable by passing different parameters */
	public static function getFax($fax_id = '', $messageId= '', $pid = '') {
		$binds = array();
		$where = '';
		if(!empty($fax_id)) {
			if(empty($where)) {
				$where .= 'WHERE fax_id = ? ';
			} else {
				$where .= 'AND fax_id = ? ';
			}
			$binds[] = $fax_id;
		}

		if(!empty($messageId)) {
			if(empty($where)) {
				$where .= 'WHERE message_id = ? ';
			} else {
				$where .= 'AND message_id = ? ';
			}
			$binds[] = $messageId;
		}

		if(!empty($pid)) {
			if(empty($where)) {
				$where .= 'WHERE mu.pid = ? ';
			} else {
				$where .= 'AND mu.pid = ? ';
			}
			$binds[] = $pid;
		}

		$query = 'SELECT pl.*,pl.`receivers_name` as rec_name , mu.* FROM `fax_messages` AS pl ';
		$query .= 'LEFT JOIN `message_log` AS mu ON pl.message_id = mu.id ';
		$query .= $where;
		$res = sqlStatement($query, $binds);
		$data = array();
		while($row = sqlFetchArray($res)) {
			$data[] = $row;
		}
		return $data;
	}

	public static function getLatestFaxStatus($fax_id = '', $pid = '') {
		$faxData = self::getFax($fax_id, '', $pid);
		$checkStatusAfter = isset($GLOBALS['FAX_CHECK_STATUS_AFTER']) && !empty($GLOBALS['FAX_CHECK_STATUS_AFTER']) ? $GLOBALS['FAX_CHECK_STATUS_AFTER'] : 0;

		if(isset($faxData)) {
			$ids = array();
			$msg_ids = array();

			foreach ($faxData as $key => $faxItem) {
				$aftermin = strtotime($faxItem['create_date'] . '+'.$checkStatusAfter.' minute');
				$current = strtotime("now");

				if(isset($faxItem) && !empty($faxItem['fax_id']) && ($faxItem['status_code'] == "unknown" || $faxItem['status_code'] == "processing") ) {

					if($aftermin <= $current) {
						$ids[] = $faxItem['fax_id'];
						$msg_ids[$faxItem['fax_id']] = $faxItem['message_id'];
					}
				}
			}
		}

		if(!empty($ids)) {
			$idsStr = implode(",", $ids);
			$faxStatusData = self::getStatus($idsStr);

			if(isset($faxStatusData) && $faxStatusData['status'] == true) {

				foreach ($faxStatusData['data'] as $item => $data) {
					self::updateFaxStatus($data);

					$isActive = self::isActive($data) === true;
					$activity = $isActive === true ? "1" : "0";

					/*Assign User to Msg*/
					if($isActive === true) {
						EmailMessage::assignUserToMSG($msg_ids[$data['jobid']]);
					}

					self::updateActivity($msg_ids[$data['jobid']], $activity);
				}
			}
		}

		return array(
			'status' => true,
			'message' => ''
		);
	}

	public static function updateFaxStatus($data) {
		if(!empty($data) && !empty($data['jobid'])) {
			$binds[] = isset($data['status_code']) ? $data['status_code'] : '';
			$binds[] = isset($data['description']) ? $data['description'] : '';
			$binds[] = date("Y-m-d H:i:s");
			$binds[] = isset($data['jobid']) ? $data['jobid'] : '';

			$sql = "UPDATE `fax_messages` SET ";
			$sql .= "status_code=?, description=?, update_date=? ";
			$sql .= "WHERE fax_id = ? ";
			return sqlInsert($sql, $binds);
		}
	}

	public static function updateActivity($msg_id, $activity) {
		if($msg_id) {
			$sql = "UPDATE `message_log` SET ";
			$sql .= "activity=? ";
			$sql .= "WHERE id = ? ";
			return sqlInsert($sql, array($activity, $msg_id));
		}
	}

	public function isFailedToSend($data) {
		$isFailed = false;

		if(!empty($data) && $data['activity'] == "1" && $data['direction'] == "out" && ($data['status_code'] != "success" && $data['status_code'] != "processing")) {
			$isFailed = true;
		}
		return $isFailed;
	}

	public static function isActive($data) {
		if(isset($data) && ($data['status_code'] == "success" || $data['status_code'] == "processing")) {
			return false;
		} else {
			return true;
		}
	}

	/*
	public static function logFaxData($responce, $data, $assignStatus = true) {
		if(isset($responce) && isset($responce['status']) && $responce['status'] == true) {
			// Store message record
			$binds = array();

			$isActive = self::isActive($responce['data']);
			$activity = $isActive === true ? "1" : "0";

			$binds[] = $activity;
			$binds[] = $data['pid'];
			$binds[] = 'FAX_MESSAGE';
			$binds[] = isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : '';
			$binds[] = $data['fax_number'];
			$binds[] = $data['fax_from_number'];
			$binds[] = date('Y-m-d H:i:s');
			$binds[] = $data['status'];
			$binds[] = $data['message_content'];
			$binds[] = json_encode(EmailMessage::includeRequest($data['request'], array(
						'pid',
						'message', 
						'rec_name', 
						'fax_number',
						'fax_from', 
						'address_book', 
						'insurance_companies',   
						'files_length', 
						'baseDocList'
					)));

			// Write new record
			$sql = "INSERT INTO `message_log` SET ";
			$sql .= "`activity`= ?, `type`='FAX', `pid`=?, `event`=?, `direction`='out', `userid`=?, `msg_to`=?, `msg_from`=?, `msg_time`=?, `msg_status`=?, `message`=?, `raw_data`=? ";

			$msgLogId = sqlInsert($sql, $binds);
			$responce['msg_log_id'] = isset($msgLogId) ? $msgLogId : "";

			//Assign User to Msg
			if($isActive === true && $assignStatus === true) {
				EmailMessage::assignUserToMSG($msgLogId);
			}

			if(!empty($msgLogId) && isset($responce['data'])) {
				$responce['data']['receivers_name'] = isset($data['receiver_name']) ? $data['receiver_name'] : "";
				self::saveFax($msgLogId, $responce['data']);
			}

			//Write log and file
			if(!empty($msgLogId) && !empty($data['attchFiles'])) {
				foreach ($data['attchFiles'] as $key => $attachItem) {
					$attachId = isset($attachItem['id']) ? $attachItem['id'] : '';
					EmailMessage::writeMessageDocumentLog($msgLogId, "file_url", $attachItem['file_name'], $attachItem['url'], $attachId);
				}
			}

			$responData = $responce;

		} else if(isset($responce) && isset($responce['status']) && $responce['status'] == false) {
			$responData = array(
				'status' => false,
				'error' => $responce['error']
			);
		}

		return $responData;
	}*/

	// TransmitFax - Send fax.
	public static function TransmitFax($fData = array(), $opts = array()) {
        $pid = isset($opts['pid']) ? $opts['pid'] : "";
        $request_data = isset($opts['request_data']) ? $opts['request_data'] : array();
        $files = isset($opts['files']) ? $opts['files'] : array();
        $logMsg = isset($opts['logMsg']) ? $opts['logMsg'] : true;
        $calculateCost = isset($opts['calculate_cost']) ? $opts['calculate_cost'] : false;
        $responceData = array();

        try {
            try {
                // Attache Files
                $attachmentList = Attachment::prepareAttachment($pid, $request_data, $files);
                $attachmentList = Attachment::saveAttachmentFile($attachmentList);
                $request_data = Attachment::updateRequest($attachmentList, $request_data);

                foreach ($attachmentList as $iIndex => $item) {
                    if(!isset($item['path'])) continue;
                    if(!file_exists($item['path'])) {
                        throw new \Exception("File not exists for attachment.");
                    }
                }

                // Generare pdf file from fax message content.
                foreach ($fData as $fk => $faxData) {
                    $faxHtmlPdf = Attachment::generateAttachmentPDF($faxData['html'], 'fax_message', true);

                    $faxHtmlPdf['type'] = "fax";

                    $attachmentItem = $attachmentList;
                    array_unshift($attachmentItem , $faxHtmlPdf); 
                    //$attachmentItem = array_merge($attachmentList, array($faxHtmlPdf));
                    
                    $totalPageCount = 0;

                    foreach ($attachmentItem as $aik => $attachItem) {
                        if(isset($attachItem['page_count'])) {
                            $totalPageCount += $attachItem['page_count'];
                        }
                    }

                    // Calculate cost
                    $costStatus = self::calculateCost($totalPageCount);
                    $fData[$fk]['cost_data'] = $costStatus;
                    $fData[$fk]['attachments'] = $attachmentItem;
                    $fData[$fk]['request_data'] = $request_data;
                }

                // If only to calculatecost
                if($calculateCost === true) { 
                    foreach ($fData as $fk => $faxData) {
                        $responceData[$fk] = array(
                            'status' => true, 
                            'fax_number' => isset($faxData['fax_number']) ? $faxData['fax_number'] : '',
                            'cost_data' => isset($faxData['cost_data']) ? $faxData['cost_data'] : array()
                        );

                        // Clear Generated files.
                        Attachment::clearAttachmentFile($faxData['attachments']);
                    }
                    return $responceData;
                }
                

            } catch(\Throwable $e) {
                // Clear Generated files.
                $attachmentList = Attachment::clearAttachmentFile($attachmentList);                
                throw new \Exception($e->getMessage()); 
            }


            // Fax data.
            foreach ($fData as $fk => $faxData) {
                $responceData[$fk] = array(
                    'status' => true, 
                    'fax_number' => isset($faxData['fax_number']) ? $faxData['fax_number'] : '', 
                    'errors' => array()
                );

                try {

					// Prepare fax data
                    $fax_data = array();
                    $fax_data['template'] = $faxData['template'];
                    $fax_data['fax_number'] = $faxData['fax_number'];
                    $fax_data['receiver_name'] = $faxData['receiver_name'];
                    $fax_data['html'] = $faxData['html'];
                    $fax_data['text'] = $faxData['text'];
                    $fax_data['fax_from_type'] = $faxData['fax_from_type'];

                	$reqContent = isset($faxData['content']) ? $faxData['content'] : "";
                    if(!empty($reqContent)) {
                        $fax_data['message_content'] = $reqContent;
                        $fax_data['html'] = $reqContent;
                        $fax_data['text'] = trim(strip_tags($reqContent));
                    }

                    // Help you to get base64content
                    $faxDataCount = 1;
                    $faxDataList = array();
                    
                    foreach ($faxData['attachments'] as $key => $attachmentItem) {
                        $faxDataList['file'.$faxDataCount] = $attachmentItem['name'];
                        $faxDataList['data'.$faxDataCount] = base64_encode(file_get_contents($attachmentItem['path']));
                        $faxDataCount++;
                    }

                    $faxData['data'] = $faxDataList;

                    // Send fax
					$responce = self::Transmit($faxData);

					if(isset($responce) && isset($responce['status']) && $responce['status'] == false) {
						if(isset($responce['error'])) {
							throw new \Exception($responce['error']);
						}
					}

					if(isset($responce['message'])) {
						$responceData[$fk]['errors'][] = $responce['message']; 
					}

                } catch(\Throwable $e) {
                    $status = $e->getMessage();
                    $responceData[$fk]['status'] = false;
                    $responceData[$fk]['errors'][] = $e->getMessage();
                }

                if(isset($logMsg) && $logMsg === false) {
                	// Clear Generated files.
                	$faxData['attachments'] = Attachment::clearAttachmentFile($faxData['attachments']);

                    //Skip iteration
                    continue;
                }

                // Log message process.
                if(isset($responce) && isset($responce['status']) && $responce['status'] == true) {
                	$isActive = self::isActive($responce['data']);
					$activity = $isActive === true ? "1" : "0";

					// Prepare Message log data
                    $extrainfo = array();

                    if(!empty($pid)) $extrainfo['pid'] = $pid;
                    if(!empty($faxData['template'])) $extrainfo['message'] = $faxData['template'];
                    if(!empty($faxData['receiver_name'])) $extrainfo['rec_name'] = $faxData['receiver_name'];
                    if(!empty($faxData['fax_number'])) $extrainfo['fax_number'] = $faxData['fax_number'];
                    if(!empty($faxData['fax_from_type'])) $extrainfo['fax_from'] = $faxData['fax_from_type'];
                    if(!empty($faxData['address_book'])) $extrainfo['address_book'] = $faxData['address_book'];
                    if(!empty($faxData['insurance_companies'])) $extrainfo['insurance_companies'] = $faxData['insurance_companies'];

                    $msgAttachmentList = array();
                    if(!empty($faxData['request_data'])) {
                        $arrayTypeList = array("local_files", "documents", "notes", "orders", "encounter_forms", "demos_insurances");
                        foreach ($arrayTypeList as $typeKey => $typeItem) {
                            if(isset($faxData['request_data'][$typeItem])) {
                                $msgAttachmentList[$typeItem] = json_decode($faxData['request_data'][$typeItem], true);
                            }
                        }
                    }

                    if(!empty($msgAttachmentList)) $extrainfo['attachments'] = $msgAttachmentList;

                    $msgData = array(
                            $activity,
                            $pid,
                            'FAX_MESSAGE',
                            isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : "",
                            $faxData['fax_number'],
                            isset($username) ? $username : "",
                            $faxData['receiver_name'],
                            date('Y-m-d H:i:s'),
                            '',
                            $fax_data['html'],
                            json_encode($extrainfo)
                        );

                    // Write new record
					$sql = "INSERT INTO `message_log` SET ";
					$sql .= "`activity`= ?, `type`='FAX', `pid`=?, `event`=?, `direction`='out', `userid`=?, `msg_to`=?, `msg_from`=?, `receivers_name`=?, `msg_time`=?, `msg_status`=?, `message`=?, `raw_data`=? ";
						
					$msgLogId = sqlInsert($sql, $msgData);

					/*Assign User to Msg*/
					if($isActive === true) {
						Attachment::assignUserToMSG($msgLogId);
					}

					//Log data
					if(!empty($msgLogId) && isset($responce['data'])) {
						$responce['data']['receivers_name'] = isset($faxData['receiver_name']) ? $faxData['receiver_name'] : "";
						self::saveFax($msgLogId, $responce['data']);
					}
                    
                    // Write log and file
                    if(!empty($msgLogId) && !empty($faxData['attachments'])) {
                        foreach ($faxData['attachments'] as $key => $attachItem) {
                        	if($attachItem['name'] == "fax_message.pdf") {
                        		unlink($attachItem['path']);
                        		continue;
                        	}

                            $sUrl = str_replace($GLOBALS["webserver_root"], "", $attachItem['path']);
                            $file_url = substr($sUrl, strpos($sUrl, "/sites/"));
                            $file_name = isset($attachItem['name']) ? $attachItem['name'] : "";
                            $attachId = isset($attachItem['id']) ? $attachItem['id'] : "";

                            if(!empty($file_url)) {
                                Attachment::writeMessageDocumentLog($msgLogId, "file_url", $file_name, $file_url, $attachId);
                            }
                        }
                    }

                    // Set Responce data
                    $resItem = array();
                    if(!isset($responceData[($fk)]['data'])) {
                        $responceData[$fk]['data'] = array();
                    }
                    $responceData[$fk]['data'][] = array('to' => $faxData['fax_number'], 'msgid' => $msgLogId);
                } else {
                	$faxData['attachments'] = Attachment::clearAttachmentFile($faxData['attachments']);
                }
            }

        } catch(\Throwable $e) {
            foreach ($fData as $fk => $faxData) {
                $responceData[$fk] = array(
                    'status' => false, 
                    'fax_number' => isset($faxData['fax_number']) ? $faxData['fax_number'] : '',
                    'errors' => array($e->getMessage())
                );
            }
        }

        return $responceData;
    }
}