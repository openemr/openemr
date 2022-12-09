<?php

namespace OpenEMR\OemrAd;

include_once("../interface/globals.php");
include_once($GLOBALS['srcdir']."/wmt-v3/wmt.globals.php");
include_once($GLOBALS['fileroot'].'/mdEmailMessage.class.php');

use OpenEMR\OemrAd\EmailMessage;
use Mpdf\Mpdf;

class PostalLetter {

	/*Constructor*/
	public function __construct() {
	}

	public static function getConfigVars() {
		$returnList = new \stdClass();
		$returnList->postal_letter_user = isset($GLOBALS['POSTAL_LETTER_USER']) ? $GLOBALS['POSTAL_LETTER_USER'] : "";
		$returnList->postal_letter_pass = isset($GLOBALS['POSTAL_LETTER_PASS']) ? $GLOBALS['POSTAL_LETTER_PASS'] : "";
		$returnList->postal_letter_secretkey = isset($GLOBALS['POSTAL_LETTER_SECRETKEY']) ? $GLOBALS['POSTAL_LETTER_SECRETKEY'] : "";

		return $returnList;
	}

    /*Page head scripts*/
	public function pageHead() {
		?>
		<!-- DataTable -->
	  	<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-dt-1-10-13/css/jquery.dataTables.min.css" type="text/css">
	    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-colreorder-dt-1-3-2/css/colReorder.dataTables.min.css" type="text/css">
	    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-1-10-13/js/jquery.dataTables.min.js"></script>
	    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/datatables.net-colreorder-1-3-2/js/dataTables.colReorder.min.js"></script>
	    <link rel="stylesheet" href="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/css/dataTables.checkboxes.css">
	    <script type="text/javascript" src="//gyrocode.github.io/jquery-datatables-checkboxes/1.2.12/js/dataTables.checkboxes.min.js"></script>
	    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/tiny-mce-nwt/tinymce.min.js"></script>

	    <style type="text/css">
			body {
				overflow-y: auto!important;
			}
			.btn-file {
			    position: relative;
			    overflow: hidden;
			}
		    .btn-file input[type=file] {
		       	background: #1050b6;
			    color: #ffffff !important;
			    display: block;
			    float: left;
			    font-weight: 400;
		        position: absolute;
		        top: 0;
		        right: 0;
		        min-width: 100%;
		        min-height: 100%;
		        text-align: right;
		        filter: alpha(opacity=0);
		        opacity: 0;
		        outline: none;
		        cursor: inherit;
		        border-radius: 0px!important; 
		    }
		    .uploadBtnContainer {
		    	width: 95px;
    			height: 30px;
		    }
		    .btn-flie-b {
		    	background: #2672ec;
			    color: #ffffff !important;
			    display: block;
			    font-weight: 400;
			    margin-right: 3px;
			    padding-right: 10px;
			    text-decoration: none;
			    padding: 5px 12px 5px;
			    border: none;
			    border-radius: 0px!important; 
		    }
		    .files input[type="button"], input[type="submit"], .files button {
		    	float: none!important;
		    }
		    .files .btnContainer {
		    	display: inline-block;
		    	vertical-align: top;
		    }
		    .files .fileList {
		    	padding-left: 20px;
    			margin-top: 15px;
    			margin-bottom: 20px;
    			float: left;
		    }
		    body {
		    	/*min-height: 600px;*/
		    }
		    textarea.form-control {
		    	height: 100px!important;
		    }
		    main {
		    	flex-grow: unset!important;
		    }
		    #send_postal_letter {
		    	float: right;
		    }
		    .containerPostalLetter {
		    	order: 3;
		    	width: 100%;
		    	margin-top: 15px;
		    }
		    .containerFile {
		    	order: 3;
		    	width: 100%;
		    	margin-top: 15px;
		    }
		    .fileList li {
		    	border-bottom: 1px solid;
		    }
		    .childContainer li:last-child {
		    	border-bottom: 0px solid;
		    }
		    .counterListContainer {
		    	padding: 10px;
		    	margin-bottom: 10px;
		    }
		    .encounter_data input[type=checkbox] {
		    	margin-right: 8px;
		    }
		    .encounter_data .encounter_forms {
		    	padding-left: 20px;
		    }
		    .hideContainer {
		    	display: none;
		    }
		    .readonlyInput {
		    	background-color: #fff!important;
		    }
			.textareaAddress {
				max-height:80px;
			}
		</style>

		<?php
    }

    /*Help to Get file upload component*/
	public function getFileUploadEle($pid) {
		?>
		<div class="containerPostalLetter">
		<div class="files" id="filesDoc">
			<!-- <div class="btnContainer uploadBtnContainer">
			<span class="btn btn-flie-b btn-file">
		        Upload File  <input type="file" name="files1" multiple />
		    </span>
			</div> -->
			<div class="btnContainer">
				<button type="button" id="select_document" onClick="handleSelectDocument()">Select Documents</button>
			</div>
			<!-- <div class="btnContainer">
				<button type="button" id="select_notes" onClick="handleSelectNotes()">Select Internal Notes</button>
			</div> -->
			<div class="btnContainer">
				<button type="button" id="select_encounters" onClick="handleSelectEncounters()">Select Encounters & Forms</button>
			</div>
			<div class="btnContainer">
				<button type="button" id="select_encounters_1" onClick="handleDemosInsurances()">Demos and Ins</button>
			</div>
			<div><ul class="fileList"></ul></div>
    	</div>
    	</div>
    	<?php
    }
    
    /*Javascript related to attache file.*/
	public function getFileUploadScript($pid, $pat_data, $requestStr = '', $form_action, $readOnly = 0) {
		?>
		<script type="text/javascript">
			$('#address_from').change(function(){
				var selectedaddressTypeVal = $(this).children("option:selected").val();
				$('.searchContainer').hide();
				$('#edit_address_link').hide();
				$('#rec_name').attr("disabled", true);
				
				if(selectedaddressTypeVal && selectedaddressTypeVal !="") {
					$('#'+selectedaddressTypeVal+'_container').css('display', 'table-row');
				}
				
				$address_val = "";
				$address_json_val = "";

				var isDisabled = true;
				var $rec_val = "";
				if(selectedaddressTypeVal == "patient") {
					$address_val_tmp = <?php echo $pat_data->fullAddress; ?>;

					if($address_val_tmp['status'] == true) {
						$address_val = $address_val_tmp['address'];
						$address_json_val = $address_val_tmp['address_json'];
						$('#address').val($address_val).attr("disabled", true);
						$('#address_json').val(JSON.stringify($address_json_val));
					} else {
						alert($address_val_tmp['errors']);
					}
					
					$rec_val = decodeHTMLEntities('<?php echo htmlspecialchars($pat_data->format_name, ENT_QUOTES); ?>');
					$('#rec_name').val($rec_val);
				} else if(selectedaddressTypeVal == "custom") {
					//isDisabled = false;
					$('#edit_address_link').show();
					$('#address').attr("disabled", isDisabled);
					$('#rec_name').attr("disabled", false);
				} else {
					$('#address').val($address_val).attr("disabled", isDisabled);
					$('#rec_name').val($rec_val);
					$('#address_json').val(JSON.stringify($address_json_val));
				}

				if(selectedaddressTypeVal && selectedaddressTypeVal !="") {
					$('#'+selectedaddressTypeVal).val("");
				}
				//$('#rec_name').val($rec_val);
			});

			//Help to decode HTML entities 
			function decodeHTMLEntities (str) {
				var element = document.createElement('div');
			    if(str && typeof str === 'string') {
			      // strip script/html tags
			      str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
			      str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
			      element.innerHTML = str;
			      str = element.textContent;
			      element.textContent = '';
			    }

			    return str;
			}

			// This is for callback by the find-addressbook popup.
			function setAddressBook(id, name, address) {
				addressObj = JSON.parse(atob(address));
				if(addressObj['status'] == true) {
					$('#address_book').val(name);
					$('#rec_name').val(name);
					$('#address').val(addressObj['address']);
					$('#address_json').val(JSON.stringify(addressObj['address_json']));
				} else {
					alert(addressObj['errors']);
				}
			}

			// This invokes the find-addressbook popup.
			function sel_addressbook_address() {
				var url = '<?php echo $GLOBALS['webroot']."/library/OemrAD/interface/main/messages/find_addressbook_popup.php?pid=". $pid; ?>&pagetype=postal_letter';
			  	let title = '<?php echo xlt('Address Book Search'); ?>';
			  	dlgopen(url, 'findAddressbook', 1100, 500, '', title);
			}

			// This is for callback by the find-insurance_companies popup.
			function setInsurancecompanies(id, name, address) {
				addressObj = JSON.parse(atob(address));
				if(addressObj['status'] == true) {
					$('#insurance_companies').val(name);
					$('#rec_name').val(name);
					$('#address').val(addressObj['address']);
					$('#address_json').val(JSON.stringify(addressObj['address_json']));
				} else {
					alert(addressObj['errors']);
				}
			}

			// This invokes the find-insurance_companies popup.
			function sel_insurancecompanies_fax() {
				var url = '<?php echo $GLOBALS['webroot']."/library/OemrAD/interface/main/messages/find_insurancecompanies_popup.php?pid=". $pid; ?>&pagetype=postal_letter';
			  	let title = '<?php echo xlt('Insurance Companies Search'); ?>';
			  	dlgopen(url, 'findInsurancecompanies', 1100, 500, '', title);
			}

			// This is for callback by the find-addressbook popup.
			function setFacility(id, name, address) {
				addressObj = JSON.parse(atob(address));
				if(addressObj['status'] == true) {
					$('#select_reply_address').val(name);
					$('#reply_address').val(addressObj['address']);
				} else {
					alert(addressObj['errors']);
				}
			}

			// This invokes the find-facilities popup.
			function sel_facilities_address() {
				var url = '<?php echo $GLOBALS['webroot']."/library/OemrAD/interface/main/messages/find_facilities_popup.php?pid=". $pid; ?>&pagetype=postal_letter';
			  	let title = '<?php echo xlt('Facilities Search'); ?>';
			  	dlgopen(url, 'findFacilities', 1100, '', '', title);
			}

			// This invokes the edit address popup.
			function edit_address() {
				var address_json_val = $('#address_json').val();
				var address_json = "";

				if(IsJsonString(address_json_val) === true) {
					var address_json_str = JSON.parse($('#address_json').val());
					address_json = objectToQueryString(address_json_str);
				}

				var url = '<?php echo $GLOBALS['webroot']."/library/OemrAD/interface/main/messages/custom_address.php?pid=". $pid; ?>&'+address_json;
			  	let title = '<?php echo xlt('Edit Address'); ?>';
			  	dlgopen(url, 'customAddress', 600, 300, '', title);
			}

			/*Check is json string valid is not*/
			function IsJsonString(str) {
			    try {
			        JSON.parse(str);
			    } catch (e) {
			        return false;
			    }
			    return true;
			}

			// This is for callback function by the custom-address popup.
			function setCustomAddress(addressObj) {
				if(addressObj['status'] == true) {
					$('#address').val(addressObj['address']);
					$('#address_json').val(JSON.stringify(addressObj['address_json']));
				} else {
					alert(addressObj['errors']);
				}
			}

			//Generate Query String From Json
			function objectToQueryString(obj) {
			  	var str = [];
			  	for (var p in obj)
		    	if (obj.hasOwnProperty(p)) {
		      		str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
		    	}
			  	return str.join("&");
			}
		</script>
		<script type="text/javascript">
			var tinymceReadOnly = <?php echo $readOnly; ?>;
	    	var tinyMCE = tinymce.init({
				entity_encoding : "raw",
				selector: "#content",
				setup: function (editor) {
			        editor.on('change', function () {
			            editor.save();
			        });
			    },
			    readonly : tinymceReadOnly,
				theme : "modern",
				mode : "exact",
				br_in_pre : false,
				force_br_newlines : true,
				force_p_newlines : false,
				forced_root_block : false,
				relative_urls : false,
				document_base_url : "<?php echo $GLOBALS['web_root'] ?>/",
				plugins  : "visualblocks visualchars image link media template code codesample table hr pagebreak nonbreaking anchor toc insertdatetime advlist lists textcolor wordcount imagetools contextmenu colorpicker textpattern",
				toolbar1 : "bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect",
				toolbar2 : "cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image media code | insertdatetime preview | forecolor backcolor",
				toolbar3 : "table | hr removeformat | subscript superscript | charmap emoticons | print fullscreen | visualchars visualblocks nonbreaking template pagebreak restoredraft | code",
	//			toolbar1 : "formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat",
				toolbar_items_size : "small",
				templates : [
					{ title: 'PDF Document', description: 'Default layout for PDF documents', url: 'pdf_template.html' }
				],
				menubar : false
			});

			// background update process
			function ajaxTransmitWithFile(type) {
				top.restoreSession();
				
				var _msg = '';
				if($('#content').val() == '') {
					if(_msg) _msg = '' + _msg + "\n";
					_msg += 'You must include text in the message.';
				}
				if($('#address').val() == '') {
					if(_msg) _msg = '' + _msg + "\n";
					_msg += 'You must enter address.';
				}
				if($('#reply_address').val() == '') {
					if(_msg) _msg = '' + _msg + "\n";
					_msg += 'You must enter reply address.';
				}
				if($('#rec_name').val() == '') {
					if(_msg) _msg = '' + _msg + "\n";
					_msg += 'You must enter receiver name';
				}
				if(_msg) {
					alert(_msg);
					return false;
				}
				

				// show spinner
				$('#send_spinner_container').show();

				// organize the data
				var status = '';
				var formData = new FormData(); // Currently empty
				formData.append('mode', 'transmit');
				formData.append('message', $('#message').val());
				formData.append('pid', $('#pid').val());
				formData.append('address', $('#address').val());
				formData.append('address_json', $('#address_json').val());
				formData.append('rec_name', $('#rec_name').val());
				formData.append('reply_address', $('#reply_address').val());
				formData.append('reply_address_json', $('#reply_address_json').val());
				
				formData.append('content', tinymce.get('content').getContent());

				//Extra param
				formData.append('address_from', $('#address_from').val());
				formData.append('address_book', $('#address_book').val());
				formData.append('insurance_companies', $('#insurance_companies').val());

				appendDataToForm(formData);

	   			// run request
	 			$.ajax ({
					type: "POST",
					url: "<?php echo $GLOBALS['webroot'].'/interface/main/messages/postal_letter.php?submod=check'.$requestStr; ?>",
					processData: false,
	            	contentType: false,
					data: formData,
					success: function(resultStr) {
						//console.log(resultStr);
						var result = JSON.parse(resultStr);

						if (result.status == true) {

			 				if(result.data.cost_data != undefined && result.data.cost_data.alert) {
			 					alert(result.data.cost_data.alert);
			 					ajaxTransmitConfirm(formData, result.data);
			 				}

			 				if(result.data.cost_data != undefined && result.data.cost_data.confirm) {
			 					var confirmResult = confirm(result.data.cost_data.confirm);

			 					if(confirmResult == true) {
			 						ajaxTransmitConfirm(formData, result.data);
			 					} else {
			 						$('#send_spinner_container').hide();
			 					}
			 				}

			 				if(result.data.cost_data != undefined && result.data.cost_data.noalert) {
			 					ajaxTransmitConfirm(formData, result.data);
			 				}

			 				if(result.data.cost_data != undefined && result.data.cost_data.error) {
			 					$('#send_spinner_container').hide();

								// Display error condition
					 	 		alert(result.data.cost_data.error);
			 				}

			 			} else if (result.status == false) {
			 				$('#send_spinner_container').hide();

							// Display error condition
				 	 		alert(result.error);
			 			}		
					},
					error: function() {
						$('#send_spinner_container').hide();
						alert('Send Failed...')
					}, 	 					

					async:   true
				});
			}

			// background update process confirm
			function ajaxTransmitConfirm(formData, prevData) {

				//Added prevData
				formData.append("prevData", JSON.stringify(prevData));

				// run request
	 			$.ajax ({
					type: "POST",
					url: "<?php echo $GLOBALS['webroot'].'/interface/main/messages/postal_letter.php?submod=confirm'.$requestStr; ?>",
					processData: false,
	            	contentType: false,
					data: formData,
					success: function(resultStr) {
						//console.log(resultStr);  
						var result = JSON.parse(resultStr);

						$('#send_spinner_container').hide();

			 			if (result.status == true) {
							if(result.message) {
								alert(result.message);
							}

			 				// Close window and refresh
			 				opener.doRefresh();
							dlgclose();

			 			} else if (result.status == false) {
							// Display error condition
				 	 		alert(result.error);
			 			} 	                		
					},
					error: function() {
						$('#send_spinner_container').hide();
						alert('Send Failed...')
					}, 	 					

					async:   true
				});
			}

			// background refresh process
			function ajaxRetrieveWithHTML() {
				top.restoreSession();

				if($('#message').val() == '') return true;

				// organize the data
				var data = [];
				data.push({name: "mode", value: "retrieve"});
				data.push({name: "message", value: $('#message').val()});			

	 			$.ajax ({
					type: "POST",
					url: "<?php echo $GLOBALS['webroot'].'/interface/main/messages/postal_letter.php'; ?>",
					dataType: "json",
					data: $.param(data),
					success: function(result) {
			 			if (result.content == 'error') {
		 	 				alert('Retrieve Failed...');
		 				} else {
	 	 					$('#content').val(result.content_html);
	 	 					tinymce.get('content').setContent(result.content_html);

							$('#content').animate({
								scrollTop: 0
							});
		 				}
					},
					error: function() {
						alert('Retrieve Failed...');
					}, 	 					

					async:   true
				});
			}
	    </script>
		<?php
	}
    
    public function getOtherElement($pid) {
		?>
		<tr>
			<td style='text-align:right;padding-bottom: 5px;'>
				<b><?php echo xlt('Select Address From'); ?>:&nbsp;</b>
			</td>
			<td style="padding-bottom: 4px;">
				<select id="address_from" name="address_from" class="form-control">
					<option value="">Please Select</option>
					<option value="address_book">Address Book</option>
					<option value="insurance_companies">Insurance Companies</option>
					<option value="patient">Patient</option>
					<option value="custom">Custom Address</option>
				</select>
			</td>
		</tr>
		<tr id="address_book_container" class="hideContainer searchContainer">
			<td style='text-align:right;padding-bottom: 5px;'>
				<b><?php echo xlt('Address Book'); ?>:&nbsp;</b>
			</td>
			<td style="padding-bottom: 5px;">
				<input type='text' id="address_book" name="address_book" onClick='sel_addressbook_address()' class='form-control readonlyInput' value='' readonly/>
			</td>
		</tr>
		<tr id="insurance_companies_container" class="hideContainer searchContainer">
			<td style='text-align:right;padding-bottom: 5px;'>
				<b><?php echo xlt('Insurance Companies'); ?>:&nbsp;</b>
			</td>
			<td style="padding-bottom: 5px;">
				<input type='text' id="insurance_companies" name="insurance_companies" onClick="sel_insurancecompanies_fax()" class='form-control readonlyInput' value='' readonly/>
			</td>
		</tr>
		<tr style="display: none;">
			<td style='text-align:right;padding-bottom: 5px;'>
				<b><?php echo xlt('Select Reply Address'); ?>:&nbsp;</b>
			</td>
			<td style="padding-bottom: 5px;">
				<input type='text' id="select_reply_address" name="select_reply_address" onClick='sel_facilities_address()' class='form-control readonlyInput' value='' readonly/>
			</td>
		</tr>
		<?php
    }
    
    public function getAndSaveEncounterPDF($pid, $queryData, $filename = 'postal_letter_encounters_and_forms') {
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

		$pdfE = new mPDF(
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
		$content_pdf = $pdfE->Output($fullfilename, 'F');

		return array(
        	'path' => $fullfilename,
        	'name' => $filename . ".pdf"
        );
    }

    public static function replaceHTMLTags($string, $tags) {
		$tags_to_strip = $tags;
		foreach ($tags_to_strip as $tag){
		    $string = preg_replace("/<\\/?" . $tag . "(.|\\s)*?>/","",$string);
		}

		return $string;
    }
    
    /*Help to get save url*/
	public static function getSaveURL($url) {
		global $webserver_root;
		$site_url = self::getWebsiteURL();
		return str_replace($webserver_root, "", $url);
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

	public static function generateAttachmentPDF($content, $filename, $isFile = true) {
		$file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/message_attachments/";
		$fullfilename = $file_location . strtotime(date('Y-m-d H:i:s')) . "_" . $filename . ".pdf";

		$pdf = new mPDF();

        $pdf->writeHTML($content, false);
        
        if($isFile == true) {
	        $content_pdf = $pdf->Output($fullfilename, 'F');
	        return array(
	        	'path' => $fullfilename,
	        	'name' => $filename . ".pdf"
	        );
    	} else {
    		$content_pdf = $pdf->Output($fullfilename, 'S');
    		return array(
	        	'base64_content' => base64_encode($content_pdf),
	        	'name' => $filename . ".pdf"
	        );
    	}
	}
    
    public static function generateLetterContent($data, $filename) {
        //$returnAddress = nl2br($data['reply_address']);
        //$recipientAddress = nl2br($data['address']);
        $returnAddress = "";
        $recipientAddress = "";
        $bodyContent = $data['html'];

        ob_start();
		include_once(dirname( __FILE__, 2 )."/templates/tmp_letter.php");
		$letter_content = ob_get_clean();

		return array(
			'base64_content' => $letter_content,
			'name' => $filename . ".pdf"
		);
	}

    public static function AddAttachmentToMsg($pid, &$postal_letter_data, $request, $files) {
		$attachmentList = array();
		$postal_letter_data['message_content'] = $postal_letter_data['html'];
		$files = array();

		if(isset($files['files'])) {
			$postal_letter_data['files_length'] = $request['files_length'];
			$postal_letter_data['files'] = $files['files'];
		}

		if(isset($request['docsList']) && !empty($request['docsList'])) {
			$tempDocsList = json_decode($request['docsList'], true);
			$postal_letter_data['docsList'] = $tempDocsList;
		}

		if(isset($request['documentFiles']) && !empty($request['documentFiles'])) {
			$tempDocFiles = json_decode($request['documentFiles']);
			$postal_letter_data['documentFiles'] = $tempDocFiles;
		}

		if(isset($request['notes']) && !empty($request['notes'])) {
			$tempDocFiles = json_decode($request['notes']);
			$postal_letter_data['notes'] = $tempDocFiles;
		}

		if(isset($request['encounters']) && !empty($request['encounters'])) {
			$tempEncounters = (array)json_decode($request['encounters'], true);
			$postal_letter_data['encounters'] = $tempEncounters;

			$postal_letter_data['encounters_pid'] = $pid;
			if(is_array($postal_letter_data['encounters']) && count($postal_letter_data['encounters']) > 0) {
				$postal_letter_data['encounters_pid'] = reset($postal_letter_data['encounters'])['pid'];
			}
		}

		if(isset($request['encounterIns']) && !empty($request['encounterIns'])) {
			$tempEncounters = (array)json_decode($request['encounterIns'], true);
			$postal_letter_data['encounterIns'] = $tempEncounters;

			$postal_letter_data['encounterIns_pid'] = $pid;
			if(is_array($postal_letter_data['encounterIns']) && count($postal_letter_data['encounterIns']) > 0) {
				$postal_letter_data['encounterIns_pid'] = reset($postal_letter_data['encounterIns'])['pid'];
			}
		}

		if((isset($postal_letter_data['encounterIns']) && !empty($postal_letter_data['encounterIns'])) || ($request['isCheckEncounterInsDemo'] == "true")) {
			$encounterQtrData = array();
			$ins_html = EmailMessage::generateCaseHTML($postal_letter_data['encounterIns_pid'], $postal_letter_data);
			$encounterPDF = EmailMessage::getAndSaveEncounterPDF($postal_letter_data['encounterIns_pid'], $encounterQtrData, 'postal_letter_demos_and_ins', array(), $ins_html, $request['isCheckEncounterInsDemo']);
			$attachmentList[] = array(
				'action' => 'stay',
				'url' => $encounterPDF['path'],
				'name' => $encounterPDF['name']
			);
		}

		if(isset($postal_letter_data['encounters']) && !empty($postal_letter_data['encounters'])) {
			$encounterQtrData = EmailMessage::encounterQtrDataGenerator($postal_letter_data, 'encounters');
			$encounterPDF = EmailMessage::getAndSaveEncounterPDF($postal_letter_data['encounters_pid'], $encounterQtrData, 'postal_letter_encounters_and_forms', array(), '', $request['isCheckEncounterDemo']);
			$attachmentList[] = array(
				'action' => 'stay',
				'url' => $encounterPDF['path'],
				'name' => $encounterPDF['name']
			);
		}


		if(isset($postal_letter_data['notes']) && !empty($postal_letter_data['notes'])) {
	    	$noteStr = "";
	    	$noteStr .= '<h1>Internal Notes</h1><ul style="padding-left:15px; font-size:16px;">';
	    	$nCounter = 1;

			foreach ($postal_letter_data['notes'] as $key => $note) {
				$noteObj = (array)$note;
				$noteStr .= "<li>".preg_replace("/[\r\n]/", "\n   ", strip_tags($noteObj['raw_body']))."</li>";
				$nCounter++;
			}
			$noteStr .= "</ul>";

			$notesPDF = self::generateAttachmentPDF($noteStr, 'postal_letter_internal_notes');

			$attachmentList[] = array(
				'action' => 'stay',
				'url' => $notesPDF['path'],
				'name' => $notesPDF['name']
			);
		}

		if(isset($postal_letter_data['documentFiles'])) {
			foreach ($postal_letter_data['documentFiles'] as $key => $doc) {
				$docObj = (array)$doc;
				if($docObj['type'] == "file_url") {
					$filePath = str_replace("file://","",$docObj['url']);

					$attachmentList[] = array(
						'action' => 'stay',
						'url' => $filePath,
						'name' => $docObj['baseFileName'],
						'id' => isset($docObj['id']) ? $docObj['id'] : '',
					);
				}
			}
		}

		if(isset($postal_letter_data['docsList']) && is_array($postal_letter_data['docsList'])) {
			foreach ($postal_letter_data['docsList'] as $key => $doc) {
				$ext = end(explode('.', $doc['name']));
				$attachmentList[] = $doc;
			}
		}

		$postal_letterDataCount = 1;
		$postal_letterDataList = array();
		if(isset($postal_letter_data['html']) && !empty($postal_letter_data['html'])) {
			$notesPDF = self::generateLetterContent($postal_letter_data, 'postal_letter');
			if(!empty($notesPDF)) {
				$postal_letter_data['filename'] = $notesPDF['name'];
				$postal_letter_data['filedata'] = $notesPDF['base64_content'];
			}
		}

		//Store postal letter attachment
		$attchFiles = self::saveAttachmentFile($attachmentList);
		foreach ($attchFiles as $key => $attachmentItem) {
			if(isset($attachmentItem['file_name'])) {
				$postal_letterDataList[] = array('file_name' => $attachmentItem['file_name'], 'path' => $attachmentItem['path']);
				$postal_letterDataCount++;

				if(isset($attchFiles[$key]['base64_content'])) {
					unset($attchFiles[$key]['base64_content']);
				}
			}
		}

		$postal_letter_data['data'] = $postal_letterDataList;
		$postal_letter_data['attchFiles'] = $attchFiles;

		$letterData = self::generateFinalLetter($postal_letter_data);

		$postal_letter_data['cost_data'] = self::calcCost(isset($letterData['page_count']) ? $letterData['page_count'] : 0);

		return $attchFiles;
    }

    public function gePDF($path) {
		$pdftext = file_get_contents($path);
  		$num = preg_match_all("/\/Page\W/", $pdftext, $dummy);
  		return $num;
	}

    /*Calculate page cost*/
    public static function calcCost($pageCount) {
		if(empty($GLOBALS['POSTAL_LETTER_INITIAL_COST']) || empty($GLOBALS['POSTAL_LETTER_ADDITIONAL_COST']) || empty($GLOBALS['POSTAL_LETTER_LIMIT_COST'])) {
			return array(
				'status' => true,
				'noalert' => true
			);

		}

		$gbl_currency_symbol = $GLOBALS['gbl_currency_symbol'];

		if($pageCount > 0) {
			$initialCost = $GLOBALS['POSTAL_LETTER_INITIAL_COST'];
			$additionalPageCost = (($pageCount-1)*$GLOBALS['POSTAL_LETTER_ADDITIONAL_COST']);
			$totalCost = ($initialCost + $additionalPageCost);
			$limitCost = $GLOBALS['POSTAL_LETTER_LIMIT_COST'];

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

    /*Save postal letter attachment file on to server*/
	public static function saveAttachmentFile($attachments) {
		$attachmentDetails = array();
		$file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/message_attachments/";

		if (!file_exists($file_location)) {
		    mkdir($file_location, 0777, true);
		}

		foreach ($attachments as $key => $attachment) {
			$filename = $attachment['name'];
			$fullfilename = $file_location . strtotime(date('Y-m-d H:i:s')) . "_" . $filename;

			if($attachment['action'] == "save") {
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

	/* Get Letter data from datatable by passing different parameters */
	public static function getPostalLetter($letterId = '', $messageId= '', $pid = '', $forUpdate = false) {
		$binds = array();
		$where = '';
		if(!empty($letterId)) {
			if(empty($where)) {
				$where .= 'WHERE letter_id = ? ';
			} else {
				$where .= 'AND letter_id = ? ';
			}
			$binds[] = $letterId;
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

		if($forUpdate === true) {
			if(empty($where)) {
				$where .= 'WHERE pl.status_code IN ("-900", "-910", "-990", "-995", "0", "1", "2", "") ';
			} else {
				$where .= 'AND pl.status_code IN ("-900", "-910", "-990", "-995", "0", "1", "2", "") ';
			}
		}

		$query = 'SELECT pl.*, mu.* FROM `postal_letters` AS pl ';
		$query .= 'LEFT JOIN `message_log` AS mu ON pl.message_id = mu.id ';
		$query .= $where;
		$res = sqlStatement($query, $binds);
		$data = array();
		while($row = sqlFetchArray($res)) {
			$data[] = $row;
		}
		return $data;
	}

	/*Save Postal Letter*/
	public static function savePostalLetter($messageId, $data) {
		
		if(isset($messageId) && !empty($messageId)) {
			if(isset($data)) {
				$postLetterData = isset($data['id']) ? self::getPostalLetter($data['id']) : array();

				$postLetterStatus = (isset($data) && isset($data['statusData']) && isset($data['statusData']['jobStatusId'])) ? self::getStatusDescription($data['statusData']['jobStatusId']) : '';
				$postLetterStatus .= (isset($data) && isset($data['statusData']) && isset($data['statusData']['description'])) ? ' - '.$data['statusData']['description'] : '';

				$postLetterUpdateTime = '';
				if(isset($data) && isset($data['statusData']) && !empty($data['statusData']['lastUpdateTime'])) {
					$datetimeObj = \DateTime::createFromFormat('m-d-Y', $data['statusData']['lastUpdateTime']);
					$postLetterUpdateTime = $datetimeObj->format('Y-m-d');
				}

				if(isset($postLetterData) && count($postLetterData) > 0) {
					$binds = array();
					$binds[] = (isset($data) && isset($data['statusData']) && isset($data['statusData']['jobStatusId'])) ? $data['statusData']['jobStatusId'] : '';
					$binds[] = trim($postLetterStatus, ' - ');
					$binds[] = $postLetterUpdateTime;
					$binds[] = (isset($data) && isset($data['attachment']) && isset($data['attachment']['file_name'])) ? $data['attachment']['file_name'] : '';
					$binds[] = (isset($data) && isset($data['attachment']) && isset($data['attachment']['url'])) ? $data['attachment']['url'] : '';
					$binds[] = date("Y-m-d H:i:s");
					$binds[] = isset($data['id']) ? $data['id'] : '';

					$sql = "UPDATE `postal_letters` SET ";
					$sql .= "status_code=?, description=?, last_update_time=?, file_name=?, url=?, update_date=? ";
					$sql .= "WHERE letter_id = ? ";
					return sqlInsert($sql, $binds);
				} else {
					$binds = array();
					$binds[] = isset($data['id']) ? $data['id'] : '';
					$binds[] = $messageId;
					$binds[] = (isset($data) && isset($data['statusData']) && isset($data['statusData']['jobStatusId'])) ? $data['statusData']['jobStatusId'] : '';
					$binds[] = trim($postLetterStatus, ' - ');
					$binds[] = $postLetterUpdateTime;
					$binds[] = (isset($data) && isset($data['attachment']) && isset($data['attachment']['file_name'])) ? $data['attachment']['file_name'] : '';
					$binds[] = (isset($data) && isset($data['attachment']) && isset($data['attachment']['url'])) ? $data['attachment']['url'] : '';
					
					$sql = "INSERT INTO `postal_letters` SET ";
					$sql .= "letter_id=?, message_id=?, status_code=?, description=?, last_update_time=?, file_name=?, url=?";
					return sqlInsert($sql, $binds);
				}
			}
		}

		return false;
	}

	public static function getLatestLetterStatus($letterId = '', $pid = '') {
		$postLetterData = self::getPostalLetter($letterId, '', $pid, true);

		if(isset($postLetterData)) {
			$ids = array();
			$msg_ids = array();

			foreach ($postLetterData as $key => $item) {
				$plDateTime = strtotime($item['create_date']);
				$cDateTime = strtotime(date("Y-m-d H:i:s"));
				$timeDiff = ($cDateTime - $plDateTime);

				if($timeDiff > 30) {
					if(($item['status_code'] == '-900' || $item['status_code'] == '-910' || $item['status_code'] == '-990' || $item['status_code'] == '-995') || (empty($item['status_code']) || $item['status_code'] == '0' || $item['status_code'] == '1' || $item['status_code'] == '2')) {

						if(isset($item['letter_id']) && !empty($item['letter_id'])) {
							$ids[] = $item['letter_id'];
							$msg_ids[$item['letter_id']] = $item['message_id'];
						}				
					}
				}
			}

			if(!empty($ids)) {
				$idsStr = implode(",", $ids);
				
				$finalData = array();
				$finalMessages = array();

				foreach ($ids as $ikey => $l_id) {
					$afterResponceData = self::getMultipleAfterResponce($l_id);

					if($afterResponceData['status'] == true && isset($afterResponceData['prepareData']) && empty($afterResponceData['message'])) {
						foreach ($afterResponceData['prepareData'] as $key => $preData) {
							if(isset($preData['id']) && isset($msg_ids[$preData['id']])) {
								self::savePostalLetter($msg_ids[$preData['id']], $preData);
								$activity = self::checkIsError($preData);
								self::updateActivity($msg_ids[$preData['id']], $activity);

								/*Assign User to Msg*/
								if($activity == "1") {
									EmailMessage::assignUserToMSG($msg_ids[$preData['id']]);
								}
							}	
						}
					} else {
						$ldesc = !empty($afterResponceData['message']) ? $afterResponceData['message'][0] : 'Not Found';
						$usql = "UPDATE `postal_letters` SET status_code=?, description=? WHERE letter_id = ? ";
						sqlInsert($usql, array('10', $ldesc, $afterResponceData['prepareData'][0]['id']));
					}

					if(isset($afterResponceData['prepareData']) && !empty($afterResponceData['prepareData'])) {
						$finalData[] = $afterResponceData['prepareData'][0];
					}

					if(isset($afterResponceData['message']) && !empty($afterResponceData['message'])) {
						$finalMessages[] = $afterResponceData['message'][0];
					}
				}

				//return $afterResponceData;
				return array(
					'status' => true,
					'prepareData' => $finalData,
					'message' => $finalMessages
				);
			}
		}

		return array(
			'status' => false,
			'prepareData' => array(),
			'message' => array()
		);
	}

	public static function updateActivity($msg_id, $activity) {
		if($msg_id) {
			$sql = "UPDATE `message_log` SET ";
			$sql .= "activity=? ";
			$sql .= "WHERE id = ? ";
			return sqlInsert($sql, array($activity, $msg_id));
		}
	}

	public static function checkFPDI($file) {
		$filepdf = fopen($file,"r");
		if ($filepdf) {
		$line_first = fgets($filepdf);
			fclose($filepdf);
		} else{
			return false;
			//echo "error opening the file.";
		}

		// extract number such as 1.4 ,1.5 from first read line of pdf file
		preg_match_all('!\d+!', $line_first, $matches);	
		// save that number in a variable
		$pdfversion = implode('.', $matches[0]);

		$file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/message_attachments/";
		$fullfilename = $file_location . strtotime(date('Y-m-d H:i:s')) . rand() . "_temdoc.pdf";

		$final_file = $file;
		$update_status = false;

		if($pdfversion > "1.4"){
			shell_exec('gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -sOutputFile="'.$fullfilename.'" "'.$file.'"'); 
			$final_file = $fullfilename;
			$update_status = true;
		}
		else{
			$final_file = $file;
		}

		return array(
			'file' => $final_file,
			'status' => $update_status
		);
	}

	public static function generateFinalLetter($postal_letterData) {
		try {

			$config_mpdf = array(
	            'tempDir' => $GLOBALS['MPDF_WRITE_DIR'],
	            'mode' => 'utf-8',
	            'format' => array(215.9, 279.4),
	            'margin-left' => '12.6mm',
				'margin-right' => '12.6mm',
				'margin-top' => '13.5mm',
				'margin-bottom' => '13.5mm'
	        );
	        $pdf = new mPDF($config_mpdf);
			$pdf->AddPageByArray([
				'margin-left' => '12.6mm',
				'margin-right' => '12.6mm',
				'margin-top' => '13.5mm',
				'margin-bottom' => '13.5mm',
			]);

			//Removed
			//$pdf->writeHTML($postal_letterData['filedata'], true);
			$pdf->writeHTML($postal_letterData['filedata'], \Mpdf\HTMLParserMode::DEFAULT_MODE);
			
			$imageFiles = array();
			$dataFiles = array();	

			$allowedImage = array('jpg','jpeg','jpe','png');
			$allowedFile = array('pdf');

			foreach($postal_letterData['data'] as $item) {
				$fileExt = end(explode(".", $item['path']));
				if(in_array($fileExt, $allowedImage) == true) {
					$imageFiles[] = $item;
				} else if(in_array($fileExt, $allowedFile) == true) {
					$dataFiles[] = $item['path'];
				} else {
					$messages[] = "Skipped file becuase of UnSupported File Type: ".$item['filename'];
				}
			}

			foreach($dataFiles as $file){
				$fileExt = end(explode(".", $file));
					//$pdf->SetImportUse();
					$rsponceFile = self::checkFPDI($file);
					if(!empty($rsponceFile)) {
						$pagecount = $pdf->SetSourceFile($rsponceFile['file']);
						for ($i=1; $i<=($pagecount); $i++) {
							$pdf->AddPage();
							$import_page = $pdf->ImportPage($i);
							$pdf->UseTemplate($import_page);
						}

						if(file_exists($rsponceFile['file']) && $rsponceFile['status'] === true) {
							unlink($rsponceFile['file']);
						}
					}
			}

			if(!empty($imageFiles)) {
				$pdf->AddPage();
				foreach($imageFiles as $file){
					$htmlStr = '<div><img src="'.$file['path'].'"/><div style="margin-top:5px;margin-bottom:25px;"><span><b>'.$file['file_name'].'</b></span></div></div>';
					//Removed
					//$pdf->writeHTML($htmlStr, false);
					$pdf->writeHTML($htmlStr, \Mpdf\HTMLParserMode::DEFAULT_MODE);
				}
			}

			$pagecount = $pdf->page;
			
			$file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/message_attachments/";
			$fullfilename = $file_location . strtotime(date('Y-m-d H:i:s')) . "_postal_letter.pdf";
			
			$content_pdf = $pdf->Output($fullfilename, 'S');

			return array(
				'status' => true,
				'content' => $content_pdf,
				'page_count' => $pagecount 
			);

		} catch (Exception $e) {

			$status = $e->getMessage();

			return array(
				'status' => false,
				'error' => $status
			);
		
		}
	}
    
    public static function Transmit($postal_letterData) {
    	$configList = self::getConfigVars();

		if(empty($configList->postal_letter_secretkey)) {
				return array(
				'status' => false,
				'error' => "Enter valid postal letter secret key."
			);
		}

		$messages = array();
		$letterData = self::generateFinalLetter($postal_letterData);

		if($letterData['status'] == true) {

			$content_pdf = $letterData['content'];
			
			//Address Json Data
			$address_json = isset($postal_letterData['address_json']) ? json_decode($postal_letterData['address_json'], true) : array();
			$reply_address_json = isset($postal_letterData['reply_address_json']) ? json_decode($postal_letterData['reply_address_json'], true) : array();
			
			//Generate tmp file of documents
			$tmpfname = tempnam(sys_get_temp_dir(), 'POST');
			rename($tmpfname, $tmpfname .= '.pdf');
			file_put_contents($tmpfname, $content_pdf);

			$qtrParams = array(
				'perforation' => 'false',
				'replyOnEnvelope' => 'false',
				'isReturnAddressAppended' => 'false',
				'returnAddress.Company' => isset($reply_address_json['name']) ? $reply_address_json['name'] : "",
				'returnAddress.AddressLine1' => isset($reply_address_json['street']) ? $reply_address_json['street'] : "",
				'returnAddress.AddressLine2' => isset($reply_address_json['street1']) ? $reply_address_json['street1'] : "",
				'returnAddress.City' => isset($reply_address_json['city']) ? $reply_address_json['city'] : "",
				'returnAddress.State' => isset($reply_address_json['state']) ? $reply_address_json['state'] : "",
				'returnAddress.Zipcode' => isset($reply_address_json['postal_code']) ? $reply_address_json['postal_code'] : "",
				'returnAddress.Country' => isset($reply_address_json['country']) ? $reply_address_json['country'] : "",
				'sendToAddress.State' => isset($address_json['state']) ? $address_json['state'] : "",
				'sendToAddress.City' => isset($address_json['city']) ? $address_json['city'] : "",
				'sendToAddress.Zipcode' => isset($address_json['postal_code']) ? $address_json['postal_code'] : "",
				'sendToAddress.AddressLine1' => isset($address_json['street']) ? $address_json['street'] : "",
				'sendToAddress.AddressLine2' => isset($address_json['street1']) ? $address_json['street1'] : "",
				'sendToAddress.Country' => isset($address_json['country']) ? $address_json['country'] : "",
				'sendToAddress.Company' => $postal_letterData['receiver_name'],
				'isDoubleSided' => 'false',
				'isColored' => 'false',
				'File'=> new \CURLFILE($tmpfname)
			);

			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_URL => "https://api.secure.postalmethods.com/v1/Letter/sendWithAddress",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => $qtrParams,
				CURLOPT_HTTPHEADER => array(
					"Secret-Key:".$configList->postal_letter_secretkey
				),
			));

			$serviceResponse = curl_exec($curl);
			$response = json_decode($serviceResponse, true);
			curl_close($curl);

			if(isset($response) && $response['success'] !== true) {
				$errorMsg = "Something went wrong with pdf";
				
				if(isset($response['error']) && isset($response['error']['message'])) {
					$errorMsg = $response['error']['message'];
				}

				return array(
					'status' => false,
					'error' => $errorMsg
				);
			} else if(!isset($response) || empty($response)){
				return array(
					'status' => false,
					'error' => "Something went wrong with service"
				);
			}

			if(isset($response) && $response['success'] === true && isset($response['result']) && $response['result'] != null && isset($response['result']['id'])) {
				/*$afterResponceData = self::getMultipleAfterResponce($response['result']['id']);
				if(isset($afterResponceData) && isset($afterResponceData['message'])) {
					$messages = array_merge($messages, $afterResponceData['message']);
				}

				return array(
					'status' => true,
					'data' => isset($afterResponceData) && isset($afterResponceData['prepareData']) && count($afterResponceData['prepareData']) > 0 ? $afterResponceData['prepareData'][0] : array(),
					'message' => implode($messages, "\n")
				);*/

				$res_status = $response['result']['status'] ." - ".$response['result']['message'];

				return array(
					'status' => true,
					'data' => array(
						'id' => $response['result']['id'],
						'statusData' => array(
							'id' => $response['result']['id'],
							'jobStatusId' => '',
							'description' => $res_status,
							'lastUpdateTime' => date('m-d-Y')
						)
					),
					'message' => ''
				);
			} else {
				return array(
					'status' => false,
					'error' => json_encode($result)."\n".implode($messages, "\n"),
				);
			}

		} else {
			return array(
				'status' => false,
				'error' => "Something went wrong with pdf"
			);
		}
	}

	public function isAssoc(array $arr){
    	if (array() === $arr) return false;
    	return array_keys($arr) !== range(0, count($arr) - 1);
	}

	public static function getMultipleAfterResponce($letterId = '') {
		$messages = array();
		$finalData = array();

		$letterId = $letterId;
		$statusData = self::getPostalLetterStatus(explode(",", $letterId));

		if(isset($statusData['data'])) {
			foreach ($statusData['data'] as $key => $item) {
				$prepareData = array(
					'id' => $item['id'],
					'statusData' => isset($item) ? $item : ''
				);

				$postalLetterPdf = self::getPostalLetterPDF($item['id']);

				if(isset($postalLetterPdf) && $postalLetterPdf != false) {
					$prepareData['attachment'] = count($postalLetterPdf) > 0 ? $postalLetterPdf[0] : array();
					unset($prepareData['attachment']['base64_content']);
				}

				$finalData[] = $prepareData;
			}
		}

		if($statusData['status'] == false) {
			foreach (explode(",", $letterId) as $key => $value) {
				$finalData[] = array(
					'id' => $value,
				);
			}

			if(isset($statusData['error'])) {
				$messages[] = $statusData['error'];
			}
		}

		return array(
			'status' => true,
			'prepareData' => $finalData,
			'message' => $messages
		);
	}

	public function getAfterResponce($letterId) {
		$messages = array();
		$prepareData = array(
			'id' => $letterId
		);

		$i = 0;
		$checkStatus = true;
		do {

			//sleep(6);
			$statusData = self::getPostalLetterStatus($letterId);
			$postalLetterPdf = self::getPostalLetterPDF($letterId);

			if(isset($statusData['data']) && ($statusData['data']['Status'] == '-1002' || $statusData['data']['Status'] =='-1000')) {
				$checkStatus = false;
			}

			$i++;
		} while ($i < 1 && $checkStatus === true);

		if(isset($statusData)) {
			if($statusData['status'] == true) {
				$prepareData = array(
					'id' => $letterId,
					'statusData' => isset($statusData['data']) ? $statusData['data'] : ''
				);

				if(isset($statusData['data']) && ($statusData['data']['Status'] !='-1002' && $statusData['data']['Status'] !='-1000')) {
					$messages[] = "Current Status: ". $statusData['data']['Description'];
				}
			} else if($statusData['status'] == false) {
				$prepareData = array(
					'id' => $letterId,
				);

				if(isset($statusData['error'])) {
					$messages[] = $statusData['error'];
				}
			}
		}

		if(isset($postalLetterPdf) && $postalLetterPdf != false) {
			$prepareData['attachment'] = count($postalLetterPdf) > 0 ? $postalLetterPdf[0] : array();
			unset($prepareData['attachment']['base64_content']);
		}

		return array(
			'status' => true,
			'prepareData' => $prepareData,
			'message' => $messages
		);
	}

	public static function getPostalLetterPDF($id) {
		$configList = self::getConfigVars();

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.secure.postalmethods.com/v1/Letter/".$id."/pdf",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"Secret-Key:".$configList->postal_letter_secretkey
			),
		));

		$serviceResponse = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
			return array(
				'status' => false,
				'error' => json_encode($err)
			);
		} else {
			if(isset($serviceResponse) && !empty($serviceResponse)) {
				$responce = self::saveAttachmentFile(
					array(
						array(
							'action' => 'save',
							'attachment' => $serviceResponse,
							'name' => 'Postal_Letter.pdf'
						)
					)
				);
				return $responce;
			}
		}

		return false;
	}

	public static function getPostalLetterStatus($id) {
		$configList = self::getConfigVars();
		$qtrStr = array('Id' => $id);

		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.secure.postalmethods.com/v1/Letter/status?".http_build_query($qtrStr),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"Secret-Key:".$configList->postal_letter_secretkey
			),
		));

		$serviceResponse = curl_exec($curl);
		$err = curl_error($curl);

		$response = json_decode($serviceResponse, true);
		curl_close($curl);

		if ($err) {
			return array(
				'status' => false,
				'error' => json_encode($err)
			);
		} else {
			if(isset($response) && $response['success'] !== true) {
				$errorMsg = "Something went wrong with pdf";
				
				if(isset($response['error']) && isset($response['error']['message'])) {
					$errorMsg = $response['error']['message'];
				}

				return array(
					'status' => false,
					'error' => $errorMsg
				);
			} else if(isset($response) && $response['success'] === true && isset($response['result']) && $response['result'] != null && isset($response['result']['status']) && isset($response['result']['status'][0]) && isset($response['result']['status'])) {
				return array(
					'status' => true,
					'data' => $response['result']['status']
				);
			}
		}
	}
    
    public static function generatePostalAddress($address, $separator = '') {
        $addrStr = array();
        $error = array();
        $addressJson = array(
        	'name' => '',
        	'street' => '',
        	'street1' => '',
        	'city' => '',
        	'state' => '',
        	'postal_code' => '',
        	'country' => ''
        );

        if(isset($address['name']) && !empty(trim($address['name']))) {
            $addrStr[] = $address['name'];
            $addressJson['name'] = $address['name'];
        } else {
			//$error[] = 'Invalid name.';
		}

        if(isset($address['street']) && !empty(trim($address['street']))) {
            $addrStr[] = $address['street'];
            $addressJson['street'] = $address['street'];
        } else {
			$error[] = 'Invalid street address.';
		}

        if(isset($address['street1']) && !empty(trim($address['street1']))) {
            $addrStr[] = $address['street1'];
            $addressJson['street1'] = $address['street1'];
        }

        $thirdLine = '';
        if(isset($address['city']) && !empty(trim($address['city']))) {
            $thirdLine .= $address['city'].", ";
            $addressJson['city'] = $address['city'];
        } else {
			$error[] = 'Invalid city.';
		}

        if(isset($address['state']) && !empty(trim($address['state']))) {
            $state = self::getStateByCode($address['state']);
            if($state) {
                $thirdLine .= $state." ";
            } else {
				$thirdLine .= $address['state']." ";
			}
			$addressJson['state'] = $address['state'];
        } else {
			$error[] = 'Invalid state.';
		}

        if(isset($address['postal_code']) && !empty(trim($address['postal_code']))) {
            $thirdLine .= $address['postal_code'];
            $addressJson['postal_code'] = $address['postal_code'];
        } else {
			$error[] = 'Invalid zip code.';
		}

        if(!empty($thirdLine)) {
            $addrStr[] = trim($thirdLine,", ");
        }

        if(isset($address['country']) && !empty(trim($address['country']))) {
            $addrStr[] = $address['country'] == "USA" ? "United States" : $address['country'];
            $addressJson['country'] = $address['country'] == "USA" ? "United States" : $address['country'];
        } else {
            $addrStr[] = "United States";
            $addressJson['country'] = "United States";
        }

        return array(
			'status' => empty($error) ? true : false,
			'address' => implode($separator,$addrStr),
			'address_json' => $addressJson, 
			'errors' => implode($separator,$error)
        );
    }

    public static function getStateByCode($code) {
        $state = new \wmt\Options('State', $code);

        if(isset($state) && isset($state->entry) && !empty($state->entry)) {
            return $state->entry['title'];
        }

        return false;
	}
	
	/*Styles for email list */
	public function headMessageContent($pid) {
		?>
		<style type="text/css">
			div#postal_letter {
				width: 1000px!important;
			}
			div#fax {
				width: 900px!important;
			}
		</style>
		<?php
	}

	/*Display Postal Letter PDF*/
	public function displayPostalLetterPDF($data) {
		if(!empty($data['file_name']) && !empty($data['url'])) {
			$filePath = self::getSaveURL($data['url']);
			$messageStr .= '<div class="attachmentContainer"><br/><ul>';
			$fileURL = self::getWebsiteURL() . $filePath;
			$messageStr .= '<li><a href="'.$fileURL.'" download="'.$data['file_name'].'">'.$data['file_name'].'</a>'.$viewLink.'</li>';
			$messageStr .= '</ul></div>';
			return $messageStr;
		}
		return '';
	}

	public function setupGlobalFieldScript(){
		?>
		<script type="text/javascript">
			$(document).ready(function(){
				$("div[title|='REPlY_ADDRESS_JSON']").hide();
				$("div[title|='Select Reply Address'] input").prop("readonly", true);
				$("div[title|='Select Reply Address'] input").click(function(){
					sel_facilities_address();
				});

				$("div[title|='Reply Address'] textarea").prop("readonly", true);
				$("div[title|='Reply Address'] textarea").click(function(){
					edit_reply_address();
				});
			});

			// This invokes the edit address popup.
			function edit_reply_address() {
				var address_json_str = JSON.parse($("div[title|='REPlY_ADDRESS_JSON'] textarea").val());
				var address_json = objectToQueryString(address_json_str);

				var url = '<?php echo $GLOBALS['webroot']."/library/OemrAD/interface/main/messages/custom_address.php?reply=true&pid=". $pid; ?>&'+address_json;
			  	let title = '<?php echo xlt('Edit Address'); ?>';
			  	dlgopen(url, 'customAddress', 600, 300, '', title);
			}

			// This is for callback function by the custom-address popup.
			function setCustomAddress(addressObj) {
				if(addressObj['status'] == true) {
					$("div[title|='Reply Address'] textarea").val(addressObj['address']);
					$("div[title|='REPlY_ADDRESS_JSON'] textarea").val(JSON.stringify(addressObj['address_json']));
				} else {
					alert(addressObj['errors']);
				}
			}

			//Generate Query String From Json
			function objectToQueryString(obj) {
			  	var str = [];
			  	for (var p in obj)
		    	if (obj.hasOwnProperty(p)) {
		      		str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
		    	}
			  	return str.join("&");
			}

			// This is for callback by the find-addressbook popup.
			function setFacility(id, name, address) {
				addressObj = JSON.parse(atob(address));

				if(addressObj['status'] == true) {
					$("div[title|='Select Reply Address'] input").val(name);
					$("div[title|='Reply Address'] textarea").val(addressObj['address']);
					$("div[title|='REPlY_ADDRESS_JSON'] textarea").val(JSON.stringify(addressObj['address_json']));
				} else {
					alert(addressObj['errors']);
				}
			}

			// This invokes the find-facilities popup.
			function sel_facilities_address() {
				var url = '<?php echo $GLOBALS['webroot']."/library/OemrAD/interface/main/messages/find_facilities_popup.php?pid=". $pid; ?>&pagetype=postal_letter';
			  	let title = '<?php echo xlt('Facilities Search'); ?>';
			  	dlgopen(url, 'findFacilities', 1100, 500, '', title);
			}
		</script>
		<?php
	}

	/*Get Postal Letter Address*/
	public function getPostalLetterAddr($data) {
		echo isset($data['receivers_name']) ? nl2br($data['receivers_name']."\n") : "";
		echo isset($data['msg_to']) ? nl2br($data['msg_to']) : "";
	}

	/*Check Error Status*/
	public static function checkIsError($data) {
		if(isset($data) && isset($data['statusData']) && isset($data['statusData']['jobStatusId']) && ($data['statusData']['jobStatusId'] == '4' || $data['statusData']['jobStatusId'] == '5')) {
			return '1';
		}

		return '0';
	}

	public function checkIsError1($data) {
		if(isset($data) && isset($data['statusData']) && isset($data['statusData']['Status']) && (($data['statusData']['Status'] < '-1000' && $data['statusData']['Status'] != '-1000' && $data['statusData']['Status'] != '-1002') || $data['statusData']['Status'] == '-990')) {
			return '1';
		}

		return '0';
	}

	/*Check is Faild or Not*/
	public function isFailedToSend($data) {
		$isFailed = false;
		if(!empty($data) && $data['activity'] == "1" && $data['direction'] == "out" && (($data['status_code'] < '-1000' && $data['status_code'] != '-1000' && $data['status_code'] != '-1002') || $data['status_code'] == '-990') || ($data['status_code'] == '4' || $data['status_code'] == '5')) {
			$isFailed = true;
		}
		return $isFailed;
	}

	public function isFailedToSend1($data) {
		$isFailed = false;
		if(!empty($data) && $data['activity'] == "1" && $data['direction'] == "out" && (($data['status_code'] < '-1000' && $data['status_code'] != '-1000' && $data['status_code'] != '-1002') || $data['status_code'] == '-990')) {
			$isFailed = true;
		}
		return $isFailed;
	}

	public static function getStatusDescription($code) {
		$status = array('Draft', 'Pending', 'InProcess', 'Completed', 'Error', 'Cancelled');
		return isset($status[$code]) ? $status[$code] : "";
	}

	public function getErrorDescription($errorCode) {
		$errorList = array (
		  'e-900' => 'Received',
		  'e-910' => 'In Process',
		  'e-990' => 'Not enough funds available',
		  'e-995' => 'Waiting For Delivery',
		  'e-1000' => 'Completed: successfully delivered to the postal agency',
		  'e-1002' => 'Completed: successfully completed in “Development” work mode',
		  'e-1005' => 'Actively canceled by user',
		  'e-1010' => 'Failed: no funds available to process the letter',
		  'e-1018' => 'Failed: Provided US address cannot be verified',
		  'e-1021' => 'Failed: Invalid page size',
		  'e-1025' => 'Failed: A document could not be processed',
		  'e-1045' => 'Failed: Recipient postal address could not be extracted from the document',
		  'e-1065' => 'Failed: Too many sheets of paper',
		  'e-1099' => 'Failed: Internal Error',
		  'e(Number larger than zero)' => 'ID',
		  'e-3000' => 'OK',
		  'e-3001' => 'This user is not authorized to access the specified item',
		  'e-3002' => 'User was actively blocked',
		  'e-3003' => 'Not Authenticated',
		  'e-3004' => 'The specified file extension is not supported',
		  'e-3010' => 'Rejected: no funds available to the account',
		  'e-3020' => 'Rejected: file specified is currently unavailable',
		  'e-3022' => 'Cancellation Denied: The letter was physically processed and cannot be cancelled or is already cancelled',
		  'e-3113' => 'Rejected: city field contains more than 30 characters',
		  'e-3114' => 'Rejected: state field contains more than 30 characters',
		  'e-3115' => 'Warning: no data was returned for you query',
		  'e-3116' => 'Warning: the specified letter is unavailable',
		  'e-3117' => 'Rejected: Company field contains more than 45 characters',
		  'e-3118' => 'Rejected: Address1 field contains more than 45 characters',
		  'e-3119' => 'Rejected: Address2 field contains more than 45 characters',
		  'e-3120' => 'Rejected: AttentionLine1 field contains more than 45 characters',
		  'e-3121' => 'Rejected: AttentionLine2 field contains more than 45 characters',
		  'e-3122' => 'Rejected: AttentionLine3 field contains more than 45 characters',
		  'e-3123' => 'Rejected: PostalCode/ZIP field contains more than 15 characters',
		  'e-3124' => 'Rejected: Country field contains more than 30 characters',
		  'e-3125' => 'Only account administrators are allowed access to this information',
		  'e-3126' => 'Invalid file name',
		  'e-3127' => 'File name already exists',
		  'e-3128' => 'The ImageSideFileType field is empty or missing',
		  'e-3129' => 'The AddressSideFileType field is empty or missing',
		  'e-3130' => 'Unsupported file extension in ImageSideFileType',
		  'e-3131' => 'Unsupported file extension in AddressSideFileType',
		  'e-3132' => 'The ImageSideBinaryData field is empty or missing',
		  'e-3133' => 'The AddressSideBinaryData field is empty or missing',
		  'e-3134' => 'File name provided in ImageSideFileType does not exist for this user',
		  'e-3135' => 'File name provided in AddressSideFileType does not exist for this user',
		  'e-3136' => 'Image side: One or more of the fields is missing from the template',
		  'e-3137' => 'Address side: One or more of the fields is missing from the template',
		  'e-3138' => 'Image side: The XML merge data is invalid',
		  'e-3139' => 'Address side: The XML merge data is invalid',
		  'e-3142' => 'Image side: This file cannot be used as a template',
		  'e-3143' => 'Address side: This file cannot be used as a template',
		  'e-3144' => 'The XML merge data is invalid',
		  'e-3145' => 'One or more of the fields in the XML merge data is missing from the selected template',
		  'e-3146' => 'Specified pre-uploaded document does not exist',
		  'e-3147' => 'Uploading a file and a template in the same request is not allowed',
		  'e' => '',
		  'e-3209' => 'No more users allowed',
		  'e-3210' => 'Last administrator for account',
		  'e-3211' => 'User does not exist for this account',
		  'e-3212' => 'One or more of the parameters are invalid',
		  'e-3213' => 'Invalid value: General_Username',
		  'e-3214' => 'Invalid value: General_Description',
		  'e-3215' => 'Invalid value: General_Timezone',
		  'e-3216' => 'Invalid value: General_WordMode',
		  'e-3217' => 'Invalid value: Security_Password',
		  'e-3218' => 'Invalid value: Security_AdministrativeEmail',
		  'e-3219' => 'Invalid value: Security_KeepContentOnServer',
		  'e-3220' => 'Invalid value: Letters_PrintColor',
		  'e-3221' => 'Invalid value: Letters_PrintSides',
		  'e-3222' => 'Invalid value: Postcards_DefaultScaling',
		  'e-3223' => 'Invalid value: Feedback_FeedbackType',
		  'e-3224' => 'Invalid value: Feedback_Email_WhenToSend_EmailReceived',
		  'e-3225' => 'Invalid value: Feedback_Email_WhenToSend_Completed',
		  'e-3226' => 'Invalid value: Feedback_Email_WhenToSend_Error',
		  'e-3227' => 'Invalid value: Feedback_Email_WhenToSend_BatchErrors',
		  'e-3228' => 'Invalid value: Feedback_Email_DefaultFeedbackEmail',
		  'e-3229' => 'Invalid value: Feedback_Email_Authentication',
		  'e-3230' => 'Invalid value: Feedback_Post_WhenToSend_Completed',
		  'e-3231' => 'Invalid value: Feedback_Post_WhenToSend_Error',
		  'e-3232' => 'Invalid value: Feedback_Post_WhenToSend_BatchErrors',
		  'e-3233' => 'Invalid value: Feedback_Post_FeedbackURL',
		  'e-3234' => 'Invalid value: Feedback_Post_Authentication',
		  'e-3235' => 'Invalid value: Feedback_Soap_WhenToSend_Completed',
		  'e-3236' => 'Invalid value: Feedback_Soap_WhenToSend_Error',
		  'e-3237' => 'Invalid value: Feedback_Soap_WhenToSend_BatchErrors',
		  'e-3238' => 'Invalid value: Feedback_Soap_FeedbackURL',
		  'e-3239' => 'Invalid value: Feedback_Soap_Authentication',
		  'e-3240' => 'Invalid parameters array',
		  'e-3150' => 'General System Error',
		  'e-3160' => 'File does not exist',
		  'e-3161' => 'Insufficient Permissions',
		  'e-3162' => 'Too many uploaded files',
		  'e-3163' => 'No files for the account',
		  'e-3164' => 'Only Administrator can upload file as account',
		  'e-3165' => 'User does not have an API key assigned',
		  'e-3500' => 'Warning: too many attempts were made for this method',
		  'e-4001' => 'The Username field is empty or missing',
		  'e-4002' => 'The Password field is empty or missing',
		  'e-4003' => 'The MyDescription field is empty or missing',
		  'e-4004' => 'The FileExtension field is empty or missing',
		  'e-4005' => 'The FileBinaryData field is empty or missing',
		  'e-4006' => 'The Address1 field is empty or missing',
		  'e-4007' => 'The City field is empty or missing',
		  'e-4008' => 'The Attention1 or Company fields are empty or missing',
		  'e-4009' => 'The ID field is empty or missing',
		  'e-4010' => 'The MinID field is empty or missing',
		  'e-4011' => 'The MaxID field is empty or missing',
		  'e-4013' => 'Invalid ID or IDs',
		  'e-4014' => 'The MergeData field is empty or missing',
		  'e-4015' => 'Missing field: APIKey',
		);
		return isset($errorList['e'.$errorCode]) ? $errorList['e'.$errorCode] : "Failed";
	}

	public static function logPostalLetterData($responce, $data) {
		if(isset($responce) && isset($responce['status']) && $responce['status'] == true) {
			$activity = self::checkIsError($responce['data']);

			// Store message record
			$binds = array();
			$binds[] = $data['pid'];
			$binds[] = 'POSTAL_LETTER';
			$binds[] = isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : '';
			$binds[] = $data['base_address'];
			$binds[] = '';
			$binds[] = $data['receiver_name'];
			$binds[] = $responce['data']['id'];
			$binds[] = date('Y-m-d H:i:s');
			$binds[] = '';
			$binds[] = $data['message_content'];
			$binds[] = json_encode(EmailMessage::includeRequest($data['request'], array(
						'pid',
						'message', 
						'address', 
						'address_json',
						'rec_name', 
						'reply_address',
						'reply_address_json',  
						'address_from', 
						'address_book', 
						'insurance_companies', 
						'files_length', 
						'baseDocList'
					)));

			//Write new record
			$sql = "INSERT INTO `message_log` SET ";
			$sql .= "`activity`= $activity, `type`='P_LETTER', `pid`=?, `event`=?, `direction`='out', `userid`=?, `msg_to`=?, `msg_from`=?, receivers_name=?, `msg_newid`=?, `msg_time`=?, `msg_status`=?, `message`=?, `raw_data`=? ";
				
			$msgLogId = sqlInsert($sql, $binds);
			$responce['msg_log_id'] = isset($msgLogId) ? $msgLogId : "";

			/*Assign User to Msg*/
			if($activity == "1") {
				EmailMessage::assignUserToMSG($msgLogId);
			}

			if(!empty($msgLogId) && isset($responce['data'])) {
				self::savePostalLetter($msgLogId, $responce['data']);
			}

			/*Write log and file*/
			if(!empty($msgLogId) && !empty($attchFiles)) {
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
	}

	// TransmitPostalLetter - Send Postal letter.
	public static function TransmitPostalLetter($pData = array(), $opts = array()) {
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

                foreach ($attachmentList as $iIndex => $item) {
                    if(!isset($item['path'])) continue;
                    if(!file_exists($item['path'])) {
                        throw new \Exception("File not exists for attachment.");
                    }
                }

                // Generare pdf file from postal message content.
                foreach ($pData as $pk => $postalData) {
                	$postalHtmlPdf = self::generateLetterContent(array('html' => $postalData['html']), 'postal_letter');

                	$attachmentItem = $attachmentList;

                	$postal_letter_data = array();
                    $postal_letter_data['filename'] = $postalHtmlPdf['name'];
                    $postal_letter_data['filedata'] = $postalHtmlPdf['base64_content'];
                    $postal_letter_data['data'] = $attachmentItem;

                    // If calculate cost
                    if($calculateCost === true) { 
	                    $letterData = self::generateFinalLetter($postal_letter_data);  

	        			// Calculate cost
	                    $costStatus = self::calculateCost(isset($letterData['page_count']) ? $letterData['page_count'] : 0);
	                    $pData[$pk]['cost_data'] = $costStatus;
                	}
                    $pData[$pk]['attachments'] = $attachmentItem;
                    $pData[$pk]['attachments_data'] = $postal_letter_data;     
                }

                // If only to calculatecost
                if($calculateCost === true) { 
                    foreach ($pData as $pk => $postalData) {
                        $responceData[$pk] = array(
                            'status' => true, 
                            'address_json' => isset($postalData['address_json']) ? $postalData['address_json'] : '',
                            'cost_data' => isset($postalData['cost_data']) ? $postalData['cost_data'] : array()
                        );

                        // Clear Generated files.
                        Attachment::clearAttachmentFile($postalData['attachments']);
                    }
                    return $responceData;
                }

            } catch(\Throwable $e) {
                // Clear Generated files.
                $attachmentList = Attachment::clearAttachmentFile($attachmentList);                
                throw new \Exception($e->getMessage()); 
            }

            // Postal Letter data.
            foreach ($pData as $pk => $postalData) {
            	$responceData[$pk] = array(
                    'status' => true, 
                    'address_json' => isset($postalData['address_json']) ? $postalData['address_json'] : '', 
                    'errors' => array()
                );

                try {
                	// Prepare postal letter data
                    $postal_letter_data = array();
					//$postal_letter_data['dec'] = isset($postalData['dec']) ? $postalData['dec'] : '';
					$postal_letter_data['html'] = isset($postalData['html']) ? $postalData['html'] : '';
	        		$postal_letter_data['text'] = isset($postalData['text']) ? $postalData['text'] : '';
					$postal_letter_data['address'] = isset($postalData['address']) ? $postalData['address'] : '';
					$postal_letter_data['address_json'] = isset($postalData['address_json']) ? $postalData['address_json'] : '';
					$postal_letter_data['reply_address'] = isset($postalData['reply_address']) ? $postalData['reply_address'] : '';
					$postal_letter_data['reply_address_json'] = isset($postalData['reply_address_json']) ? $postalData['reply_address_json'] : '';
					$postal_letter_data['receiver_name'] = isset($postalData['receiver_name']) ? $postalData['receiver_name'] : '';
					$postal_letter_data['base_address'] = isset($postalData['base_address']) ? $postalData['base_address'] : '';

					$reqContent = isset($postalData['content']) ? $postalData['content'] : "";
                    if(!empty($reqContent)) {
                        $postal_letter_data['message_content'] = $reqContent;
                        $postal_letter_data['html'] = $reqContent;
                        $postal_letter_data['text'] = trim(strip_tags($reqContent));
                    }

                    if(isset($postalData['attachments_data'])) {
                    	$postal_letter_data['filename']  = $postalData['attachments_data']['filename'];
                    	$postal_letter_data['filedata']  = $postalData['attachments_data']['filedata'];
                    	$postal_letter_data['data']  = $postalData['attachments_data']['data'];
                    }
 
                    // Send postal letter
					$responce = self::Transmit($postal_letter_data);

					if(isset($responce) && isset($responce['status']) && $responce['status'] == false) {
						throw new \Exception($responce['error']);
					}

                } catch(\Throwable $e) {
                    $status = $e->getMessage();
                    $responceData[$pk]['status'] = false;
                    $responceData[$pk]['errors'][] = $e->getMessage();
                }

                if(isset($logMsg) && $logMsg === false) {
                    //Skip iteration
                    continue;
                }

                // Log message process.
            	if(isset($responce) && isset($responce['status']) && $responce['status'] == true) {
            		$activity = self::checkIsError($responce['data']);

            		// Prepare Message log data
                    $extrainfo = array();

                    if(!empty($pid)) $extrainfo['pid'] = $pid;
                    if(!empty($postalData['template'])) $extrainfo['message'] = $postalData['template'];
                    if(!empty($postalData['address'])) $extrainfo['address'] = $postalData['address'];
                    if(!empty($postalData['address_json'])) $extrainfo['address_json'] = $postalData['address_json'];
                    if(!empty($postalData['reply_address'])) $extrainfo['reply_address'] = $postalData['reply_address'];
                    if(!empty($postalData['reply_address_json'])) $extrainfo['reply_address_json'] = $postalData['reply_address_json'];
                    if(!empty($postalData['address_from_type'])) $extrainfo['address_from'] = $postalData['address_from_type'];
                    if(!empty($postalData['address_book'])) $extrainfo['address_book'] = $postalData['address_book'];
                    if(!empty($postalData['insurance_companies'])) $extrainfo['insurance_companies'] = $postalData['insurance_companies'];
                    if(!empty($postalData['receiver_name'])) $extrainfo['rec_name'] = $postalData['receiver_name'];

                    $msgAttachmentList = array();
                    if(!empty($postalData['request_data'])) {
                        $arrayTypeList = array("local_files", "documents", "notes", "orders", "encounter_forms", "demos_insurances");
                        foreach ($arrayTypeList as $typeKey => $typeItem) {
                            if(isset($postalData['request_data'][$typeItem])) {
                                $msgAttachmentList[$typeItem] = json_decode($postalData['request_data'][$typeItem], true);
                            }
                        }
                    }
                    if(!empty($msgAttachmentList)) $extrainfo['attachments'] = $msgAttachmentList;

                    $msgData = array(
                            $activity,
                            $pid,
                            'POSTAL_LETTER',
                            isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : "",
                            $postal_letter_data['base_address'],
                            isset($username) ? $username : "",
                            $postalData['receiver_name'],
                            $responce['data']['id'],
                            date('Y-m-d H:i:s'),
                            '',
                            $postal_letter_data['html'],
                            json_encode($extrainfo)
                        );

                    //Write new record
					$sql = "INSERT INTO `message_log` SET `activity`= ?, `type`='P_LETTER', `pid`=?, `event`=?, `direction`='out', `userid`=?, `msg_to`=?, `msg_from`=?, receivers_name=?, `msg_newid`=?, `msg_time`=?, `msg_status`=?, `message`=?, `raw_data`=? ";
					$msgLogId = sqlInsert($sql, $msgData);


					// Assign User to Msg
					if($activity == "1") {
						Attachment::assignUserToMSG($msgLogId);
					}

					if(!empty($msgLogId) && isset($responce['data'])) {
						self::savePostalLetter($msgLogId, $responce['data']);
					}

					// Write log and file
                    if(!empty($msgLogId) && !empty($postalData['attachments'])) {
                        foreach ($postalData['attachments'] as $key => $attachItem) {
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
                    if(!isset($responceData[($pk)]['data'])) {
                        $responceData[$pk]['data'] = array();
                    }
                    $responceData[$pk]['data'][] = array('address_json' => isset($postalData['address_json']) ? $postalData['address_json'] : '', 'msgid' => $msgLogId);
            	}
            }

        } catch(\Throwable $e) {
            foreach ($pData as $pk => $postalData) {
                $responceData[$pk] = array(
                    'status' => false, 
                    'address_json' => isset($postalData['address_json']) ? $postalData['address_json'] : '',
                    'errors' => array($e->getMessage())
                );
            }
        }

        return $responceData;
	}

	/*Calculate page cost*/
    public static function calculateCost($pageCount) {
		if(empty($GLOBALS['POSTAL_LETTER_INITIAL_COST']) || empty($GLOBALS['POSTAL_LETTER_ADDITIONAL_COST']) || empty($GLOBALS['POSTAL_LETTER_LIMIT_COST'])) {
			return array(
				'status' => true,
				'noalert' => true
			);

		}

		$gbl_currency_symbol = $GLOBALS['gbl_currency_symbol'];

		if($pageCount > 0) {
			$initialCost = $GLOBALS['POSTAL_LETTER_INITIAL_COST'];
			$additionalPageCost = (($pageCount-1)*$GLOBALS['POSTAL_LETTER_ADDITIONAL_COST']);
			$totalCost = ($initialCost + $additionalPageCost);
			$limitCost = $GLOBALS['POSTAL_LETTER_LIMIT_COST'];

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
}