<?php

namespace OpenEMR\OemrAd;

include_once("../interface/globals.php");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtstandard.inc");
include_once("{$GLOBALS['srcdir']}/wmt-v2/wmtpatient.class.php");
include_once($GLOBALS['fileroot'].'/mdEmailReader.class.php');
include_once($GLOBALS['fileroot'].'/mdUtility.class.php');
#include_once("$srcdir/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\EmailMessage;

class EmailMessage {

	/*Constructor*/
	public function __construct() {
	}

	/*Get Document List*/
	public static function getDocumentList($pid) {
		$queryarr = array($pid);
	    $query = "SELECT d.id, d.type, d.size, d.url, d.docdate, d.list_id, d.encounter_id, c.name " .
	    "FROM documents AS d, categories_to_documents AS cd, categories AS c WHERE " .
	    "d.foreign_id = ? AND cd.document_id = d.id AND c.id = cd.category_id ";

	    $query .= "ORDER BY d.docdate DESC, d.id DESC";
	    $dres = sqlStatement($query, $queryarr);

	    $list = array();
	    while ($drow = sqlFetchArray($dres)) {
		    $drow['baseName'] = basename($drow['url']) . ' (' . xl_document_category($drow['name']). ')';
		    $drow['baseFileName'] = ' (' . xl_document_category($drow['name']). ')' . basename($drow['url']);
		    $drow['issue'] = self::getAssociatedIssue($drow['list_id']);
		    $list[$drow['id']] = $drow;
	    }
	    return $list;
	}

	/*Get Internal Note List*/
	public static function getInternalNoteList($pid, $activity = 1) {
		$notes_list = array();
		$sql = "SELECT p.*, ";
		$sql .= "CONCAT(LEFT(u.`fname`,1), '. ',u.`lname`) AS 'user_name' ";	
		$sql .= "FROM `pnotes` p ";
		$sql .= "LEFT JOIN `users` u ON p.`assigned_to` LIKE u.`username` AND p.`assigned_to` != '' ";
		$sql .= "WHERE p.`pid` = ? AND p.`deleted` != 1 ";

	    if ($activity != "all") {
	        if ($activity == '0') {
	            // only return inactive
	            $sql .= " AND (activity = '0' OR message_status = 'Done') ";
	        } else { // $activity == '1'
	            // only return active
	            $sql .= " AND activity = '1' AND message_status != 'Done' ";
	        }
	    }

		$sql .= "ORDER BY p.`date` DESC";
		  
		$pres = self::getPatientData($pid, "lname, fname");
		$patientname = $pres['lname'] . ", " . $pres['fname'];

		$notes_result = sqlStatementNoLog($sql, array($pid));
		while ($notes_data = sqlFetchArray($notes_result)) {

			$body = $notes_data['body'];
			if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d\:\d\d /', $body)) {
				$body = nl2br(oeFormatPatientNote($body));
			} else {
				$body = htmlspecialchars(oeFormatSDFT(strtotime($notes_data['date'])) . date(' H:i', strtotime($notes_data['date'])), ENT_NOQUOTES) . ' (' . htmlspecialchars($notes_data['user'], ENT_NOQUOTES) . ') ' .'<br>'. nl2br(oeFormatPatientNote($body));
			}
			$body = preg_replace('/(\sto\s)-patient-(\))/', '${1}' . $patientname . '${2}', $body);

			$notes_data['raw_body'] = $body;
			$notes_list[$notes_data['id']] = $notes_data;
		}
		return $notes_list;
	}

	//get associated issue
	public static function getAssociatedIssue($list_id) {
		$irow = sqlQuery("SELECT type, title, begdate " .
        "FROM lists WHERE " .
        "id = ? " .
        "LIMIT 1", array($list_id));
        if ($irow) {
              $tcode = $irow['type'];
            if ($ISSUE_TYPES[$tcode]) {
                $tcode = $ISSUE_TYPES[$tcode][2];
            }
            return htmlspecialchars("$tcode: " . $irow['title'], ENT_NOQUOTES);
        }

        return '';
	}

	/*Get Patient Data*/
	public static function getPatientData($pid, $given = "*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS"){
	    $sql = "select $given from patient_data where pid=? order by date DESC limit 0,1";
	    return sqlQuery($sql, array($pid));
	}

	/*Get Patient Data*/
	public static function getPatientDataByEmail($value){
		$col = $GLOBALS['wmt::use_email_direct'] ? 'email_direct' : 'email';
	    $result = sqlStatementNoLog("select pid from patient_data where ".$col."=? OR FIND_IN_SET('".$value."',secondary_email) ", array($value));
	    
	    $pids = null;
	    if ($result) {
			while ($pat_data = sqlFetchArray($result)) {
				$pids[] = $pat_data['pid'];
			}
		}

	   	return $pids;
	}

	/*Get message by id log*/
	public function getMessageById($msgId = '') {
		$email_list = array();
		$binds = array();

		$sql = "SELECT ml.* ";
		$sql .= "FROM `message_log` ml ";
		$sql .= "WHERE ml.`type` IS NOT NULL ";

		if(!empty($msgId)) {
			$sql .= "AND ml.`id` = ? ";
			$binds[] = $msgId;
		}
		$sql .= "order by ml.`msg_time` DESC";

		$email_result = sqlStatementNoLog($sql, $binds);

		while ($email_data = sqlFetchArray($email_result)) {
			$email_list[] = $email_data;
		}
		return $email_list;
	}

	/*Get message last received email log*/
	public static function getLastEmailLog($pid = '') {
		$activity = 'all';
		$email_list = array();
		$binds = array();

		$sql = "SELECT ml.* ";
		$sql .= "FROM `message_log` ml ";
		$sql .= "WHERE ml.`type` LIKE 'EMAIL' AND ml.`direction` = 'in' ";
		if ($activity != 'all') $sql .= "AND ml.`activity` = '$activity' ";
		if(!empty($pid)) {
			$sql .= "AND ml.`pid` = ? ";
			$binds[] = $pid;
		}
		$sql .= "order by ml.`msg_time` DESC limit 0,1";

		$email_result = sqlStatementNoLog($sql, $binds);

		while ($email_data = sqlFetchArray($email_result)) {
			$email_list[] = $email_data;
		}
		return $email_list;
	}

	/*Get Attchment Document List*/
	public function getAttchmentDocumentList($id) {
		$queryarr = array($pid);
	    $query = "SELECT * " .
	    "FROM message_attachments AS ma WHERE " .
	    "ma.`message_id` = ? ";

	    $query .= "ORDER BY ma.`date` DESC";
	    $dres = sqlStatement($query, $queryarr);

	    $list = array();
	    while ($drow = sqlFetchArray($dres)) {
		    $list[] = $drow;
	    }
	    return $list;
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
                    "ORDER BY form_encounter.date desc, form_encounter.encounter desc, fdate ASC");

		// while ($result = sqlFetchArray($res)) {
		// 	$results[] = $result;
		// }
		// return $results;
		$preparedResultData = Utility::prepareEncounterReportListData($res);

		return $preparedResultData;
	}

	/*Styles for email list */
	public function headMessageContent($pid) {
		?>
		<style type="text/css">
			.attachmentContainer ul {
				padding-left: 20px!important;
			}
		</style>
		<?php
		self::emailScript($pid);
	}

	/*Help to Get file upload component*/
	public function getFileUploadEle($pid, $type = '') {
		?>
		<div class="containerEmail">
		<div class="files" id="filesDoc">
			<?php if($type == 'EMAIL') { ?>
			<div class="btnContainer uploadBtnContainer">
			<span class="btn btn-flie-b btn-file">
		        Upload File  <input type="file" name="files1" multiple />
		    </span>
			</div>
			<?php } ?>
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
			<div class="btnContainer">
				<button type="button" id="select_document" onClick="handleSelectOrder()">Order</button>
			</div>
			<div><ul class="fileList"></ul></div>
    	</div>
    	</div>
    	<?php
	}

	/*Doc file list container*/
	public function getFileContainer($pid) {
		?>
		<div class="containerFile">
		<div class="files" id="filesDoc">
			<div><ul class="fileList"></ul></div>
    	</div>
    	</div>
    	<?php
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
		    #send_email {
		    	float: right;
		    }
		    .containerEmail {
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
		    .includeDemoContainer {
		    	display: grid;
    			grid-template-columns: auto 1fr;
    			padding: 10px;
		    }
		    .include_demo {
		    	margin-left: 10px !important;
		    }
		</style>

		<?php
	}

	/*Bottom Script*/
	public static function emailScript($pid = '') {
		
		/*Set PID*/
		if(empty($pid)) {
			$pid = $_SESSION['pid'];
		}

		?>
		<script type="text/javascript">
			$(".docrow").click(function() { todocument(this.id); });

			$(document).ready(function(){
				$(".docrow").click(function() { todocument(this.id); });
			});

			function todocument(docid) {
			  h = '/controller.php?document&view&patient_id=<?php echo $pid ?>&doc_id=' + docid;
			  openPopUp(h);
			}

			function downloadDoc($url, $name) {
				h = '<?php echo $GLOBALS['webroot']; ?>/downloadDoc.php?path=<?php echo $url; ?>&name=<?php echo $name; ?>';
			  	openPopUp(h);
			}

			function openPopUp(url) {
				// in a popup so load the opener window if it still exists
				if ( (window.opener) && (window.opener.setPatient) ) {
					window.opener.loadFrame('RTop', 'RTop', url);
				// inside an OpenEMR frame so replace current frame
				} else if ( (parent.left_nav) && (parent.left_nav.loadFrame) ) {
					<?php if($GLOBALS['new_tabs_layout']) { ?>
            top.RTop.location = "<?php echo $GLOBALS['webroot']; ?>" + url;
					<?php } else { ?>
					  parent.left_nav.loadFrame('RTop', 'RTop', url);
					<?php } ?>
				// not in a frame and opener no longer exists, create a new window
				} else {
					var newwin = window.open('../main/main_screen.php?patientID=' + pid);
					newwin.focus();
				}
			}
		</script>
		<?php
	}

	public static function getAttachmentScript($pid, $requestStr, $docsList = array(), $baseDocList = array()) {
		?>
		<script type="text/javascript">
			var filesToUpload = [];
			var uploadFileList = {};
	    	var selectedDocuments = {};
	    	var selectedNotes = {};
	    	var selectedEncounterList = {};
	    	var selectedEncounterList1 = {};
	    	var selectedEncounterIns = {};
	    	var selectedOrder = {};
	    	var checkEncounterDemo = false;
	    	var checkEncounterInsDemo = false;

	    	<?php if(isset($baseDocList['uploadFileList']) && !empty($baseDocList['uploadFileList'])) { ?>
	    		uploadFileList = JSON.parse(atob("<?php echo base64_encode(json_encode($baseDocList['uploadFileList'])) ?>"));
	    		handleUploadFileListCallBack();
	    	<?php } ?>

	    	<?php if(isset($baseDocList['selectedDocuments']) && !empty($baseDocList['selectedDocuments'])) { ?>
	    		selectedDocuments = JSON.parse(atob("<?php echo base64_encode(json_encode($baseDocList['selectedDocuments'])) ?>"));
	    		handleDocumentCallBack();
	    	<?php } ?>

	    	<?php if(isset($baseDocList['selectedNotes']) && !empty($baseDocList['selectedNotes'])) { ?>
	    		selectedNotes = JSON.parse(atob("<?php echo base64_encode(json_encode($baseDocList['selectedNotes'])) ?>"));
	    		handleInternalNoteCallBack();
	    	<?php } ?>

	    	<?php if(isset($baseDocList['selectedEncounterList']) && !empty($baseDocList['selectedEncounterList'])) { ?>
	    		selectedEncounterList = JSON.parse(atob("<?php echo base64_encode(json_encode($baseDocList['selectedEncounterList'])) ?>"));
	    		checkEncounterDemo = "<?php echo $baseDocList['checkEncounterDemo']; ?>";
	    		handleEncountersCallBack();
	    	<?php } ?>

	    	<?php if(isset($baseDocList['selectedEncounterIns']) && !empty($baseDocList['selectedEncounterIns'])) { ?>
	    		selectedEncounterIns = JSON.parse(atob("<?php echo base64_encode(json_encode($baseDocList['selectedEncounterIns'])) ?>"));
	    		checkEncounterInsDemo = "<?php echo $baseDocList['checkEncounterInsDemo']; ?>";
	    		handleDemosInsurancesCallBack();
	    	<?php } ?>

	    	<?php if(isset($baseDocList['selectedOrder']) && !empty($baseDocList['selectedOrder'])) { ?>
	    		selectedOrder = JSON.parse(atob("<?php echo base64_encode(json_encode($baseDocList['selectedOrder'])) ?>"));
	    		handleOrderCallBack();
	    	<?php } ?>

	    	$docsList = <?php echo isset($docsList) ? json_encode($docsList) : '{}'; ?>;

	    	$.fn.fileUploader = function (filesToUpload, sectionIdentifier) {
			    var fileIdCounter = 0;

			    this.closest(".files").change(function (evt) {
			        var output = [];

			        for (var i = 0; i < evt.target.files.length; i++) {
			            fileIdCounter++;
			            var file = evt.target.files[i];
			            var fileId = sectionIdentifier + fileIdCounter;

			            filesToUpload.push({
			                id: fileId,
			                file: file
			            });

			            var removeLink = "<a class=\"removeFile\" href=\"#\" data-fileid=\"" + fileId + "\">Remove</a>";

			            output.push("<li><strong>", escape(file.name), "</strong> - ", file.size, " bytes. &nbsp; &nbsp; ", removeLink, "</li> ");
			        };

			        $(this).find("div .fileList")
			            .append(output.join(""));

			        //reset the input to null - nice little chrome bug!
			        evt.target.value = null;
			    });

			    $(this).on("click", ".removeFile", function (e) {
			        e.preventDefault();

			        var fileId = $(this).parent().children("a").data("fileid");

			        // loop through the files array and check if the name of that file matches FileName
			        // and get the index of the match
			        for (var i = 0; i < filesToUpload.length; ++i) {
			            if (filesToUpload[i].id === fileId)
			                filesToUpload.splice(i, 1);
			        }

			        $(this).parent().remove();
			    });

			    this.clear = function () {
			        for (var i = 0; i < filesToUpload.length; ++i) {
			            if (filesToUpload[i].id.indexOf(sectionIdentifier) >= 0)
			                filesToUpload.splice(i, 1);
			        }

			        $(this).children(".fileList").empty();
			    }

			    return this;
			};

			function arrayUnique(array) {
		    var a = array.concat();
		    for(var i=0; i<a.length; ++i) {
		        for(var j=i+1; j<a.length; ++j) {
		            if(a[i] === a[j])
		                a.splice(j--, 1);
		        }
		    }

		    return a;
		}

			function setLegalTeamEmails(caseItem) {
				let emailsAddress = caseItem['notes'];
				console.log(emailsAddress);
				if(emailsAddress != "") {
					let cEmailValue = $('#custom_email_id').val();
					let cEmailList = cEmailValue != "" ? cEmailValue.split(",").map(function(item) {return item.trim();}) : [];
					let em_list = emailsAddress.split(",").map(element => element.trim());
					//let mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
					let mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w+)+$/;
					let newEmList = [];

					em_list.forEach((eItem, ei) => {
						if(eItem != "" && eItem.match(mailformat)) {
							newEmList.push(eItem);
						}
					});

					//Merge two array
					cEmailList = arrayUnique(cEmailList.concat(newEmList));

					if(cEmailList.length > 0) {
						$('#custom_email_id').val(cEmailList.join(","));
					}
				} else {
					alert('Empty email address');
				}
			}

			function setMulticase(caseList = []) {
					if(Array.isArray(caseList) && caseList.length === 1) {
						let caseItem = caseList[0];
						setLegalTeamEmails(caseItem);
					}
			}

			async function getLegalTeamEmails(pid) {
				if(pid != "") {
					var bodyObj = { pid : pid };
					const result = await $.ajax({
						type: "GET",
						url: "<?php echo $GLOBALS['webroot'].'/library/OemrAD/interface/main/messages/ajax/get_pitype_insurance.php'; ?>",
						datatype: "json",
						data: bodyObj
					});

					if(result != '') {
						var resultObj = JSON.parse(result);
						if(Array.isArray(resultObj) && resultObj.length > 0) {
							if(resultObj.length === 1) {
								setLegalTeamEmails(resultObj[0]);
							} else {
								dlgopen('', '', 550, 300, '', 'Select Legal Team', {
				            //onClosed: 'refreshme',
				            allowResize: false,
				            allowDrag: true,
				            dialogId: '',
				            type: 'iframe',
				            url: '<?php echo $GLOBALS['webroot'].'/library/OemrAD/interface/main/messages/select_pitype_insurance.php?pid='; ?>'+pid
				        });
							}
						}
					}
				}
			} 

			/*Custom email check*/
			$("#email_id").change(function() {
				var isMessagingEnable = $('#messaging_enable_input').val();

				if($(this).val() == "legal_team") {
					getLegalTeamEmails($('#pid').val());
				}

				if($(this).val() == "custom_email" || $(this).val() == "legal_team") {
					$(".custom_email_container").attr('class', 'custom_email_container');
					if($(this).val() == "custom_email") {
						$(".custom_email_container").addClass('cemail');
					}
					$('.custom_email_container').show();

					if(isMessagingEnable == 0) {
						$('#actionBtnContainer1').addClass("hide")
						$('#actionBtnContainer2').removeClass("hide");
					}
				} else {
					$(".custom_email_container").attr('class', 'custom_email_container');
					$('.custom_email_container').hide();

					if(isMessagingEnable == 0) {
						$('#actionBtnContainer1').removeClass("hide")
						$('#actionBtnContainer2').addClass("hide");
					}
				}
			});

			/*Remove selected document from the list*/
			$('#filesDoc').on("click", ".removeDocumentFile", function (e) {
		        e.preventDefault();

		        var fileId = $(this).data("fileid");

		        delete selectedDocuments[fileId];

		        $(this).parent().remove();
		    });

		    /*Remove selected document from the list*/
			$('#filesDoc').on("click", ".removeOrderFile", function (e) {
		        e.preventDefault();

		        var fileId = $(this).data("fileid");

		        delete selectedOrder[fileId];

		        $(this).parent().remove();
		    });

			// (function () {
			//     var files1Uploader = $("#filesDoc").fileUploader(filesToUpload, "filesDoc");
			// })()

			/*Remove selected note from the list*/
			$('#filesDoc').on("click", ".removeNotesFile", function (e) {
		        e.preventDefault();

		        var fileId = $(this).data("fileid");

		        delete selectedNotes[fileId];

		        $(this).parent().remove();
		    });

		    /*Remove selected encounters from the list*/
			$('#filesDoc').on("click", ".removeEncountersFile", function (e) {
		        e.preventDefault();

		        var fileId = $(this).data("fileid");
		        var list = $(this).data("list");

		        $selectedEnc = window[list];
		        delete $selectedEnc[fileId];

		        $(this).parent().remove();
		    });

		    /*Remove selected encounters from the list*/
			$('#filesDoc').on("click", ".removeEncountersInsFile", function (e) {
		        e.preventDefault();

		        var fileId = $(this).data("fileid");

		        delete selectedEncounterIns[fileId];

		        $(this).parent().parent().remove();
		    });

			/*Init file uploader to upload file*/
			(function () {
			    var files1Uploader = $("#filesDoc").fileUploader(filesToUpload, "filesDoc");
			})()

			/*Handle Upload File List Callback*/
			function handleUploadFileListCallBack() {
				var output = [];
				$.each(uploadFileList, function(i, n){
				    //console.log(n['file_name']);
				    var removeLink = "<a class=\"removeUploadFile\" href=\"#\" data-fileid=\"" + i + "\">Remove</a>";
					output.push("<li><strong>", n['file_name'], "</strong> - ",n['size'], " bytes. &nbsp; &nbsp; ", removeLink, "</li> ");
				});

				$('.removeUploadFile').parent().remove();
				$('#filesDoc').find("div .fileList")
			            .append(output.join(""));
			}

			/*Handle Document Callback*/
			function handleDocumentCallBack() {
				var output = [];
				$.each(selectedDocuments, function(i, n){
				    var removeLink = "<a class=\"removeDocumentFile\" href=\"#\" data-fileid=\"" + i + "\">Remove</a>";
					output.push("<li><strong>", n['baseName'], "</strong> - ",n['size'], " bytes. &nbsp; &nbsp; ", removeLink, "</li> ");
				});

				$('.removeDocumentFile').parent().remove();
				$('#filesDoc').find("div .fileList")
			            .append(output.join(""));
			}

			/*Handle Document Callback*/
			function handleInternalNoteCallBack() {
				var output = [];
				$.each(selectedNotes, function(i, n){
				    var removeLink = "<a class=\"removeNotesFile\" href=\"#\" data-fileid=\"" + i + "\">Remove</a>";
					output.push("<li><strong>", n['raw_body'], "</strong> - ", removeLink, "</li> ");
				});

				$('.removeNotesFile').parent().remove();
				$('#filesDoc').find("div .fileList")
			            .append(output.join(""));
			}


			function childGenerate(id, finalVariableStr) {
				var output = [];
				$.each(window[finalVariableStr], function(i, n){
					if(n['parentId'] == id) {
						var removeLink = "<a class=\"removeEncountersFile\" data-list='" + finalVariableStr + "' href=\"#\" data-fileid=\"" + i + "\">Remove</a>";
						output.push("<li><strong>", n['title'], "</strong> - ", removeLink, "</li> ");
					}
				});

				return '<ul class="childContainer">'+output.join("")+'</ul>';
			}

			/*Handle Select Encounters*/
			function handleEncountersCallBack(list) {
				$finalVariableStr = 'selectedEncounterList';

				if(list && list != '') {
					$finalVariableStr = list;
				}

				var output = [];
				$.each(window[$finalVariableStr], function(i, n){
				    if(n['parentId'] == undefined) {
				    	var removeLink = "<a class=\"removeEncountersFile\" href=\"#\" data-list='" + $finalVariableStr + "' data-fileid=\"" + i + "\">Remove</a>";
						output.push("<li class='removeEncountersFileContainer_"+$finalVariableStr+"'><span><strong>", n['title'], "</strong> - ", removeLink, "</span>", childGenerate(i, $finalVariableStr), "</li> ");
					}
				});

				$('.removeEncountersFileContainer_'+$finalVariableStr).remove();
				$('#filesDoc').find("div .fileList")
			             .append(output.join(""));
			}

			/*Handle Select Order*/
			function handleOrderCallBack(list) {
			    var output = [];
				$.each(selectedOrder, function(i, n){
				    var removeLink = "<a class=\"removeOrderFile\" href=\"#\" data-fileid=\"" + i + "\">Remove</a>";
					output.push("<li><strong>", n['title'], "</strong> - ", removeLink, "</li> ");
				});

				$('.removeOrderFile').parent().remove();
				$('#filesDoc').find("div .fileList")
			            .append(output.join(""));
			}

			/*Handle Select Document*/
			function handleSelectDocument(pid, case_id, cnt) {
				var url = '<?php echo $GLOBALS['webroot']."/modules/ext_message/message/documentList.php?pid=". $pid; ?>';
				dlgopen(url,'selectDocPop', 800, 300, '', 'Documents', {
					buttons: [
						{text: '<?php echo xla('Select'); ?>', close: true, style: 'saveBtn btn-sm'},
		                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
		            ],
		            onClosed: '',
		            type: 'GET',
		            callBack: {call : 'handleDocumentCallBack'}
				});
			}

			/*Handle Select Notes*/
			function handleSelectNotes(pid, case_id, cnt) {
				var url = '<?php echo $GLOBALS['webroot']."/modules/ext_message/message/internalNoteList.php?pid=". $pid; ?>';
				dlgopen(url,'selectNotesPop', 800, 300, '', 'Internal Notes', {
					buttons: [
						{text: '<?php echo xla('Select'); ?>', close: true, style: 'notesaveBtn btn-sm'},
		                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
		            ],
		            onClosed: '',
		            type: 'GET',
		            callBack: {call : 'handleInternalNoteCallBack'}
				});
			}

			/*Handle Select Encounters*/
			function handleSelectEncounters(list = '') {
				var url = '<?php echo $GLOBALS['webroot']."/modules/ext_message/message/encouterList.php?pid=". $pid; ?>&list='+list;
				dlgopen(url,'selectNotesPop', 800, 300, '', 'Encounters & Forms', {
					buttons: [
						{text: '<?php echo xla('Select'); ?>', close: true, style: 'encountersaveBtn btn-sm'},
		                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
		            ],
		            onClosed: '',
		            type: 'GET',
		            callBack: {call : 'handleEncountersCallBack', args : list}
				});
			}

			/*Handle Select Order*/
			function handleSelectOrder(list = '') {
				var url = '<?php echo $GLOBALS['webroot']."/library/OemrAD/interface/main/messages/orderList.php?pid=". $pid; ?>&list='+list;
				dlgopen(url,'selectOrderPop', 800, 300, '', 'Orders', {
					buttons: [
						{text: '<?php echo xla('Select'); ?>', close: true, style: 'ordersaveBtn btn-sm'},
		                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
		            ],
		            onClosed: '',
		            type: 'GET',
		            callBack: {call : 'handleOrderCallBack', args : list}
				});
			}

			function childGenerate1(id, n) {
				var output = [];
				for($i=1; $i<=3;$i++) {
					if(n['cnt'+$i] != undefined) {
						$child = n['cnt'+$i];
						//var removeLink = "<a class=\"removeEncountersInsChildFileContainer\" href=\"#\" data-fileid=\"" + i + "\">Remove</a>";
						output.push("<li><strong>", $i ,". ", $child['name'], "</strong> ", "</li> ");
					}
				}

				return '<ul class="childContainer">'+output.join("")+'</ul>';
			}

			function handleDemosInsurancesCallBack(args) {
				var output = [];

				if(checkEncounterInsDemo == true) {
						var removeLink = "<a class=\"removeEncountersInsFile\" href=\"#\"  data-fileid='checkEncounterInsDemo'>Remove</a>";
						output.push("<li class='removeEncountersInsFileContainer'><span><strong>Report: Patient Demographics only. ", "</strong> - ", removeLink, "</span>", "</li> ");
				}

				$.each(selectedEncounterIns, function(i, n){
				    if(n['id'] != undefined) {
				    	var removeLink = "<a class=\"removeEncountersInsFile\" href=\"#\"  data-fileid=\"" + i + "\">Remove</a>";
						output.push("<li class='removeEncountersInsFileContainer'><span><strong>Case Id ", n['id'], "</strong> - ", removeLink, "</span>", childGenerate1(i, n), "</li> ");
					}
				});

				$('.removeEncountersInsFileContainer').remove();
				$('#filesDoc').find("div .fileList")
			             .append(output.join(""));
			}

			function handleDemosInsurances() {
				var url = '<?php echo $GLOBALS['webroot']."/library/OemrAD/interface/main/messages/caseInsList.php?pid=". $pid; ?>';
				dlgopen(url,'selectNotesPop', 800, 300, '', 'Demos & Insurances', {
					buttons: [
						{text: '<?php echo xla('Select'); ?>', close: true, style: 'demosinsurancessaveBtn btn-sm'},
		                {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
		            ],
		            onClosed: '',
		            type: 'GET',
		            callBack: {call : 'handleDemosInsurancesCallBack'}
				});
				//dlgopen(url, 'findIns', 1100, 500, '', "gf");
			}

			function appendDataToForm(formData) {
				// organize the file data
				formData.append("files_length", filesToUpload.length);
				for (var i = 0; i < filesToUpload.length; i++) {
		            formData.append("files["+i+"]", filesToUpload[i].file);
		        }

		        // organize the document data
		        if(selectedDocuments) {
		        	var tempfiles = [];
		        	$.each(selectedDocuments, function(i, n){
		        		tempfiles.push(n);
		        	});
		        	formData.append("documentFiles", JSON.stringify(tempfiles));
		        }

		        // organize the notes data
		        if(selectedNotes) {
		        	var tempnotes = [];
		        	$.each(selectedNotes, function(i, n){
		        		tempnotes.push(n);
		        	});
		        	formData.append("notes", JSON.stringify(tempnotes));
		        }

		        // organize the encounter data
		        if(selectedEncounterList) {
		        	var tempencounters = {};
		        	$.each(selectedEncounterList, function(i, n){
		        		if(n['parentId'] == undefined) {
		        			tempencounters[i] = { title : n['title'], value: n['value'], child : [], id : i, pid : n['pid']};
		        		} else {
		        			if(tempencounters[n['parentId']] != undefined) {
		        				tempencounters[n['parentId']]['child'].push({ title : n['title'], value: n['value'], id : i, pid : n['pid']});
		        			} else {
		        				tempencounters[i] = { title : n['title'], value: n['value'], id : i, pid : n['pid']};
		        			}
		        		}
		        	});
		        	formData.append("encounters", JSON.stringify(tempencounters));
		        }

		        // organize the encounter data
		        if(selectedEncounterList1) {
		        	var tempencounters = {};
		        	$.each(selectedEncounterList1, function(i, n){
		        		if(n['parentId'] == undefined) {
		        			tempencounters[i] = { title : n['title'], value: n['value'], child : [], id : i};
		        		} else {
		        			if(tempencounters[n['parentId']] != undefined) {
		        				tempencounters[n['parentId']]['child'].push({ title : n['title'], value: n['value'], id : i});
		        			} else {
		        				tempencounters[i] = { title : n['title'], value: n['value'], id : i};
		        			}
		        		}
		        	});
		        	formData.append("encounters1", JSON.stringify(tempencounters));
		        }

		        if(selectedEncounterIns) {
		        	formData.append("encounterIns", JSON.stringify(selectedEncounterIns));
		        }

		        if($docsList) {
		        	formData.append("docsList", JSON.stringify($docsList));
		        }

		        if(uploadFileList) {
		        	formData.append("uploadFileList", JSON.stringify(uploadFileList));
		        }

		        if(selectedOrder) {
		        	var tempOrderfiles = [];
		        	$.each(selectedOrder, function(i, n){
		        		tempOrderfiles.push(n);
		        	});
		        	formData.append("orderList", JSON.stringify(tempOrderfiles));
		        }

		      formData.append("isCheckEncounterDemo", checkEncounterDemo);
		      formData.append("isCheckEncounterInsDemo", checkEncounterInsDemo);

		    	formData.append("baseDocList", JSON.stringify({
		    		uploadFileList : uploadFileList,
		    		selectedDocuments : selectedDocuments,
		    		selectedEncounterList : selectedEncounterList,
		    		selectedEncounterList1 : selectedEncounterList1,
		    		selectedEncounterIns : selectedEncounterIns,
		    		selectedOrder: selectedOrder,
		    		checkEncounterDemo : checkEncounterDemo,
		    		checkEncounterInsDemo : checkEncounterInsDemo
		    	}));
			}

			//Open popup for patient selection
			function selectPatientButton() {
				dlgopen('../../main/calendar/find_patient_popup.php', '_blank', 800, 400);
			}

			// This is for callback by the find-patient popup.
			function setpatient(pid, lname, fname, dob) {
				var form_action = $('#re_form_action').val();
				if(form_action == "resend") {
					$('#re_pid').val(pid);
					$('#resend_form').submit();
				}
			}
		</script>
		<?php
	}

	/*Javascript related to attache file.*/
	public function getFileUploadScript($pid, $requestStr, $form_action, $readOnly = 0) {
		?>
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
				//if($('#message').val() == 'free_text') {
					var _msg = '';
					if($('#email_id').val() == '') _msg = 'Please select email address.';
					if($('#subject').val() == '') _msg = 'Please specify a subject.';
					if($('#content').val() == '') {
						if(_msg) _msg = '      ' + _msg + "\n";
						_msg += 'You must include text in the message.';
					}
					if(_msg) {
						alert(_msg);
						return false;
					}
				//}

				//Custom Email Id Check
				var emailId = $('#email_id').val();
				var isCustomEmailSelected = false;

				if(emailId == "custom_email" || emailId == "legal_team") {
					isCustomEmailSelected = emailId;
					emailId = $('#custom_email_id').val();
				}

				// show spinner
				$('#send_spinner_container').show();

				// organize the data
				var status = '';
				var formData = new FormData(); // Currently empty
				formData.append('mode', 'transmit');
				formData.append('message', $('#message').val());
				formData.append('pid', $('#pid').val());
				formData.append('email_id', emailId);
				formData.append('custom_email_id', $('#custom_email_id').val());
				formData.append('custom_email_check', isCustomEmailSelected);
				
				if($('#message').val() == 'free_text') {
					formData.append('subject', $('#subject').val());
					formData.append('content', tinymce.get('content').getContent());
				} else {
					formData.append('subject', $('#subject').val());
					formData.append('content', tinymce.get('content').getContent());
				}

				appendDataToForm(formData);

	   			// run request
	 			$.ajax ({
					type: "POST",
					url: "<?php echo $GLOBALS['webroot'].'/interface/main/messages/email_message.php?'.$requestStr; ?>",
					processData: false,
	            	contentType: false,
					data: formData,
					success: function(resultStr) {
						var result = JSON.parse(resultStr);

						$('#send_spinner_container').hide();

			 			if (result.status == '') {
			 				// Close window and refresh
			 				opener.doRefresh('<?php echo $form_action ?>');
							dlgclose();

			 			} else {
							// Display error condition
				 	 		alert(result.status);
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
				if($('#message').val() == 'free_text') {
					//$('#subject_tr').css("display", "table-row");
					//$('#content').attr("readonly", false);
					//return true;
				} else {
					//$('#subject_tr').css("display", "none");
					//$('#content').attr("readonly", true);
				}
				if($('#message').val() == '') return true;

				// organize the data
				var data = [];
				data.push({name: "mode", value: "retrieve"});
				data.push({name: "message", value: $('#message').val()});
				data.push({name: "pid", value: $('#pid').val()});			

	 			$.ajax ({
					type: "POST",
					url: "<?php echo $GLOBALS['webroot'].'/interface/main/messages/email_message.php?'.$requestStr; ?>",
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

							$('#subject').val(result.subject);
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

	/*Help to merge base upload list*/
	public static function mergeBaseUploadList($baseDocList, $baseUploadList) {
		if(!empty($baseDocList)) {
			$tmpBaseDocList = json_decode($baseDocList, true);
			$tmpBaseDocList = array_merge($tmpBaseDocList, $baseUploadList);
			return json_encode($tmpBaseDocList);
		}

		return $baseDocList;
	}

	public static function cleanStr($str) {
     // remove illegal file system characters https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
      $str = strip_tags($str); 
      $str = str_replace('&', 'and', $str);
      $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
      $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
      //$str = strtolower($str);
      $str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
      $str = htmlentities($str, ENT_QUOTES, "utf-8");
      $str = preg_replace("/([a-z])([a-z]+;)/i", '$2', $str);
      $str = str_replace(' ', '_', $str);
      $str = rawurlencode($str);
      $str = str_replace('%', '_', $str);
      return $str;

  }

	/*Get Upload file list for further use*/
	public static function generateUploadFileList($file_list) {
		$returnList = array('uploadFileList' => array());
		if(!empty($file_list)) {
			foreach ($file_list as $key => $item) {
				if($item['action'] == 'upload') {
					$returnList['uploadFileList'][] = $item;
				}
			}
		}

		return $returnList;
	}

	/*Attach file and notes to email content*/
	public static function AddAttachmentToEmail($pid, &$email, &$email_data, $request, $files) {
		$attachmentList = array();
		$reqContent = isset($_REQUEST['content']) ? $_REQUEST['content'] : "";
		$email_data['message_content'] = $reqContent;
		$email_data['html'] = $reqContent;
		$email_data['text'] = trim(strip_tags($reqContent));

		if(isset($files['files'])) {
			$email_data['files_length'] = $request['files_length'];
			$email_data['files'] = $files['files'];
		}

		if(isset($request['uploadFileList']) && !empty($request['uploadFileList'])) {
			$tempUploadFileList = json_decode($request['uploadFileList'], true);
			$email_data['uploadFileList'] = $tempUploadFileList;
		}

		if(isset($request['docsList']) && !empty($request['docsList'])) {
			$tempDocsList = json_decode($request['docsList'], true);
			$email_data['docsList'] = $tempDocsList;
		}

		if(isset($request['documentFiles']) && !empty($request['documentFiles'])) {
			$tempDocFiles = json_decode($request['documentFiles']);
			$email_data['documentFiles'] = $tempDocFiles;
		}

		if(isset($request['notes']) && !empty($request['notes'])) {
			$tempDocFiles = json_decode($request['notes']);
			$email_data['notes'] = $tempDocFiles;
		}

		if(isset($request['orderList']) && !empty($request['orderList'])) {
			$tempOrderList = json_decode($request['orderList'], true);
			$email_data['orderList'] = $tempOrderList;
		}

		if(isset($request['encounters']) && !empty($request['encounters'])) {
			$tempEncounters = (array)json_decode($request['encounters'], true);
			$email_data['encounters'] = $tempEncounters;

			$email_data['encounters_pid'] = $pid;
			if(is_array($email_data['encounters']) && count($email_data['encounters']) > 0) {
				$email_data['encounters_pid'] = reset($email_data['encounters'])['pid'];
			}
		}

		if(isset($request['encounterIns']) && !empty($request['encounterIns'])) {
			$tempEncounters = (array)json_decode($request['encounterIns'], true);
			$email_data['encounterIns'] = $tempEncounters;

			$email_data['encounterIns_pid'] = $pid;
			if(is_array($email_data['encounterIns']) && count($email_data['encounterIns']) > 0) {
				$email_data['encounterIns_pid'] = reset($email_data['encounterIns'])['pid'];
			}
		}

		if((isset($email_data['encounterIns']) && !empty($email_data['encounterIns'])) || ($request['isCheckEncounterInsDemo'] == "true")) {
			$encounterQtrData = array();
			$ins_html = self::generateCaseHTML($email_data['encounterIns_pid'], $email_data);
			$encounterPDF = self::getAndSaveEncounterPDF($email_data['encounterIns_pid'], $encounterQtrData, 'demos_and_ins', array(), $ins_html, $request['isCheckEncounterInsDemo']);
			$email->AddAttachment($encounterPDF['path'], $encounterPDF['name']);
			$attachmentList[] = array(
				'action' => 'stay',
				'url' => $encounterPDF['path'],
				'name' => $encounterPDF['name']
			);
		}

		if(isset($email_data['encounters']) && !empty($email_data['encounters'])) {
			$encounterQtrData = self::encounterQtrDataGenerator($email_data, 'encounters');
			$encounterPDF = self::getAndSaveEncounterPDF($email_data['encounters_pid'], $encounterQtrData, 'encounters_and_forms', array(), '', $request['isCheckEncounterDemo']);
			$email->AddAttachment($encounterPDF['path'], $encounterPDF['name']);
			$attachmentList[] = array(
				'action' => 'stay',
				'url' => $encounterPDF['path'],
				'name' => $encounterPDF['name']
			);
		}

		if(isset($email_data['orderList']) && !empty($email_data['orderList'])) {
			$orderData = self::generateOrderData($email_data['orderList'], $pid);
			$orderPDF = self::getOrderPDF($pid, $orderData, 'orders');
			$email->AddAttachment($orderPDF['path'], $orderPDF['name']);
			$attachmentList[] = array(
				'action' => 'stay',
				'url' => $orderPDF['path'],
				'name' => $orderPDF['name']
			);
		}

		if(isset($email_data['uploadFileList'])) {
			foreach ($email_data['uploadFileList'] as $key => $fileItem) {
				if($fileItem['type'] == "file_url") {
					$filePath = str_replace("file://","",$fileItem['path']);
					$email->AddAttachment($filePath, $fileItem['file_name']);
					
					$fileItem['ignore'] = true;
					$attachmentList[] = $fileItem;
				}
			}
		}

		if(isset($email_data['files']) && isset($email_data['files_length'])) {
			for ($i=0; $i < $email_data['files_length'] ; $i++) {
				$email->AddAttachment($email_data['files']['tmp_name'][$i],$email_data['files']['name'][$i]);

				$attachmentList[] = array(
					'action' => 'upload',
					'url' => $email_data['files']['tmp_name'][$i],
					'name' => $email_data['files']['name'][$i]
				);
			}
		}

		if(isset($email_data['documentFiles'])) {
			foreach ($email_data['documentFiles'] as $key => $doc) {
				$docObj = (array)$doc;
				if($docObj['type'] == "file_url") {
					$filePath = str_replace("file://","",$docObj['url']);
					$email->AddAttachment($filePath, $docObj['baseFileName']);

					$attachmentList[] = array(
						'action' => 'stay',
						'url' => $filePath,
						'name' => $docObj['baseFileName'],
						'id' => isset($docObj['id']) ? $docObj['id'] : '',
					);
				}
			}
		}

		if(isset($email_data['docsList']) && is_array($email_data['docsList'])) {
			foreach ($email_data['docsList'] as $key => $doc) {
				$email->AddAttachment($doc['url'], $doc['name']);
				$attachmentList[] = $doc;
			}
		}

		// generate mime boundry
		$outer_boundary = md5(time());
		$inner_boundary = md5(time()+100);

		if(isset($email_data['notes']) && !empty($email_data['notes'])) {
	    	$noteStr = "";
	    	$noteStr .= '<h1>Internal Notes</h1><ul style="padding-left:15px; font-size:16px;">';
	    	$nCounter = 1;

			foreach ($email_data['notes'] as $key => $note) {
				$noteObj = (array)$note;
				$noteStr .= "<li>".preg_replace("/[\r\n]/", "\n   ", strip_tags($noteObj['raw_body']))."</li>";
				$nCounter++;
			}
			$noteStr .= "</ul>";

			$notesPDF = self::generateAttachmentPDF($noteStr, 'internal_notes');
			$email->AddAttachment($notesPDF['path'], $notesPDF['name']);

			$attachmentList[] = array(
				'action' => 'stay',
				'url' => $notesPDF['path'],
				'name' => $notesPDF['name']
			);
		}

		return $attachmentList;
	}

	public static function getAllRTO($thisPid, $id = '') {
		$binds = array($thisPid);
		$whereIdsStr  = "";

		if(is_array($id)) {
			foreach ($id as $value) {
				if(!empty($value)) {
					if(!empty($whereIdsStr)) {
						$whereIdsStr .= "OR ";
					}

					$whereIdsStr .= "id = ? ";
					$binds[] = $value;
				}
			}

			if(!empty($whereIdsStr)) {
				$whereIdsStr = ' AND ('.$whereIdsStr.') ';
			}
		}

	  	$sql = "SELECT * FROM form_rto WHERE pid=? ".$whereIdsStr." ORDER BY rto_target_date, rto_status DESC";
		$all=array();
	  	$res = sqlStatement($sql, $binds);
	  	for($iter =0;$row = sqlFetchArray($res);$iter++) { 
			$links = self::LoadLinkedTriggers($row{'id'}, $thisPid);
			if($links) {
				$settings = explode('|', $links);
				foreach($settings as $test) {
					$tmp = explode('^',$test);
					$key = $tmp[0];
					$val = $tmp[1];
					$row[$key] = $val;
				}
			}
			$all[] = $row;
		}
	  return $all;
	}

	public static function LoadLinkedTriggers($thisId, $pid){
		// THIS FUNCTION CREATES KEYS FOR ANY JAVASCRIPT CHECKS THAT NEED
		// TO HAPPEN FROM THE RTO SCREEN
		$sql = "SHOW TABLES LIKE 'wmt_rto_links'";
		$tres = sqlStatement($sql);
		$trow = sqlFetchArray($tres);
		$frm = '';
		if(is_array($trow)) {
			if(count($trow)) $frm = array_shift($trow);
		}
		if($frm != 'wmt_rto_links') return false;
		$key = false;
	 	$sql = "SELECT * FROM wmt_rto_links WHERE rto_id=? AND pid=?";
	 	$lres = sqlStatement($sql, array($thisId, $pid));
		while($lrow = sqlFetchArray($lres)) {
			if($lrow{'form_name'} == 'surg1') {
				$tres = sqlStatement("SELECT id, pid, sc1_surg_date FROM form_surg1 ".
					"WHERE id=? AND pid=?",array($lrow{'form_id'},$pid)); 
				$trow = sqlFetchArray($tres);
				if($trow{'id'} == $lrow{'form_id'}) {
					if($key) $key .= '|';
					if($trow{'sc1_surg_date'}) $key = 'test_target_dt^'.$trow{'sc1_surg_date'};
				}
			}
		}
		return($key);
	}

	public static function generateOrderItemDetails($rto) {
			if(!empty($rto)) {
			?>
			<div>
				<div>
						<span><b>Ordered Type: </b></span>
						<span><?php echo ListLook($rto['rto_action'],'RTO_Action'); ?></span>
				</div>
				<div>
						<span><b>Ordered By: </b></span>
						<span><?php echo UserNameFromName($rto['rto_ordered_by']); ?></span>
				</div>
				<div>
						<span><b>Ordered Time: </b></span>
						<span><?php echo $rto['date']; ?></span>
				</div>
			</div>
			<br/>
			<?php
			}
	}

	public static function generateOrderData($orders, $pid) {
		global $doNotPrintField;

		$orderIds = array();

		foreach ($orders as $ok => $orderItem) {
			if(isset($orderItem['id']) && !empty($orderItem['id'])) {
				$orderIds[] = $orderItem['id'];
			}
		}

		$rtos = self::getAllRTO($pid, $orderIds);

		$orderLbfFormLib = new \oemrmd\OrderLbfForm();

		ob_start();
		?>
		<style type="text/css">
			.orderTable {
				border:1px solid #000!important;
				border-collapse: collapse;
				width: 100%;
			}

			.orderTable .cellHeader,
			.orderTable .cell,
			.orderTable .cell1 {
				padding:8px;
				text-align: left;
				border:1px solid #000!important;
			}

			.headerRow{
				background-color:#FFFBEB;
			}
			.insRow{
				background-color:#E0E0E0;
			}

		</style>
		<center><h1 style='font-size:15px;'><?php echo xl('Order Fulfillment') ?></h1></center>
		<table class="orderTable">
			<!-- <tr class="headerRow">
				<th class="cellHeader">Order</th>
				<th class="cellHeader">Order By</th>
				<th class="cellHeader">Status</th>
				<th class="cellHeader">Assigned To</th>
				<th class="cellHeader">Date</th>
			</tr> -->
			<?php
				foreach ($rtos as $key => $rto) {
					$rtoData = $orderLbfFormLib->getRtoLayoutFormData($pid, $rto['id']);
					$layoutData = $orderLbfFormLib->getLayoutForm($rto['rto_action']);
					?>
					<!-- <tr class="insRow">
						<td class="cell"><?php //echo ListLook($rto['rto_action'],'RTO_Action'); ?></td>
						<td class="cell"><?php //echo UserNameFromName($rto['rto_ordered_by']); ?></td>
						<td class="cell"><?php //echo ListLook($rto['rto_status'],'RTO_Status'); ?></td>
						<td class="cell"><?php //echo !empty($rto['rto_resp_user']) ? UserNameFromName($rto['rto_resp_user']) : ''; ?></td>
						<td class="cell"><?php //echo $rto['date']; ?></td>
					</tr> -->
					<?php
					if(!empty($layoutData) && !empty($layoutData['grp_form_id']) && !empty($rtoData)) {
						$formname = $layoutData['grp_form_id'];
						$form_id = $rtoData['form_id'];

						?>
						<tr class="insRow1" >
							<td colspan="5" class="cell1">
							<?php self::generateOrderItemDetails($rto); ?>
							<span><b>Summary:</b></span>
							<div class="lbfFormDetails">
							<?php 
								if (substr($formname, 0, 3) == 'LBF') {
									$doNotPrintField = true;
									include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");

									call_user_func("lbf_report", $pid, '', 2, $form_id, $formname, true);
									$doNotPrintField = false;
								} else {
									include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
									call_user_func($formname . "_report", $pid, '', 2, $form_id);
								}
							?>
							</div>
							</td>
						</tr>
					<?php
					} else {
					?>
						<tr class="insRow1" >
							<td colspan="5" class="cell1">
								<?php self::generateOrderItemDetails($rto); ?>
								<span><b>Notes:</b></span>
								<div><?php echo !empty($rto['rto_notes']) ?  htmlspecialchars($rto['rto_notes'], ENT_QUOTES, '', FALSE) : '---'; ?></div>
							</td>
						</tr>
					<?php
					}
				}
			?>
		</table>
		<?php
		$htmlStr = ob_get_clean(); 
		return $htmlStr;
	}

	public static function encDemographics($pid, $queryData, $options, $includeDemo = false) {
		global $srcdir, $web_root, $css_header, $doNotPrintField;

		$temp_pdf_output = $GLOBALS['pdf_output'];

		$temp_post = $_POST;
		unset($_POST);

		$temp_get = $_GET;
		unset($_GET);

		$_POST['pdf'] = "1";

		if($includeDemo == "true") {
			$_POST['include_demographics'] = "demographics";
		} else {
			$_POST['include_demographics'] = "demographics";

			foreach ($options as $oi => $option) {
				$_POST[$oi] = $option;
			}

			foreach ($queryData as $key => $value) {
				$_POST[$key] = $value;
			}
		}

		$GLOBALS['pdf_output'] = "S";

		//Change Dir
		$currentDir = getcwd();
		chdir($GLOBALS['fileroot'].'/interface/patient_file/report/');

		$doNotPrintField = true;

		ob_start();
		include $GLOBALS['fileroot'].'/interface/patient_file/report/custom_report.php' ;
		$f = ob_get_clean();

		$doNotPrintField = false;

		//Set Original Dir
		chdir($currentDir);
		
		$_POST = $temp_post;
		$_GET = $temp_get;

		$GLOBALS['pdf_output'] = $temp_pdf_output;

		return $content;
	}

	public static function encounterQtrDataGenerator($email_data, $name) {
		$encounterQtrData = [];
		foreach ((array)$email_data[$name] as $key => $encounter) {
			$encounterObj = (array)$encounter;
			$encounterQtrData[$encounterObj['id']] = $encounterObj['value'];
			foreach ($encounterObj['child'] as $ckey => $childencounter) {
				$childencounterObj = (array)$childencounter;
				$encounterQtrData[$childencounterObj['id']] = $childencounterObj['value'];
			}
		}

		return $encounterQtrData;
	}

	public static function generateCaseHTML($pid, $email_data) {
		$htmlStr = "<style>.ins_datatable{border:1px solid #000!important;border-collapse: collapse; margin-right:5px;} .insheadercell, .inscell {padding:8px;border:1px solid #000!important;} .childTable{width:100%} .insheadercell, .cinsheadercell{text-align: left;} .cinscell, .cinsheadercell { padding:5px;} .cinsheadercell {border-bottom:1px solid #fff;text-align:left;}.headerRow{background-color:#FFFBEB;}.insRow{background-color:#E0E0E0;}.cinscell{text-align:left;}.insurance{margin-top:20px;}.insuranceData, .subscriberData{padding-right:10px;}</style>";

		if(!empty($email_data['encounterIns'])) {
		$htmlStr .= "<div class='text insurance'>";
		$htmlStr .= "<h1 style='font-size:15px;'>".xl('Insurance Data').":</h1>";
		$htmlStr .= "<table class='ins_datatable' style='width:100%;'>";
			foreach ($email_data['encounterIns'] as $key => $ins) {
				$insArray = (array)$ins;
				if(isset($insArray['id'])) {
					$insData = self::getCaseList($pid, $insArray['id']);
			
					if(isset($insData)) {
						if(isset($insData[0]['cash']) && $insData[0]['cash'] == '1') {
							$htmlStr .= "<tr>";
							$htmlStr .= '<td colspan="7" class="cinscell" style="border: 1px solid #000;">';
							$htmlStr .= '<b>SelfPay</b>';
							$htmlStr .= '</td>';
							$htmlStr .= "</tr>";
							continue;
						}

						// $htmlStr .= "<tr class='headerRow'>";	
						// $htmlStr .= "<th class='insheadercell'>Case Date</th>";
						// $htmlStr .= "<th class='insheadercell'>Case Number</th>";
						// $htmlStr .= "<th class='insheadercell'>Case Description</th>";
						// $htmlStr .= "<th class='insheadercell'>Empl</th>";
						// $htmlStr .= "<th class='insheadercell'>Auto</th>";
						// $htmlStr .= "<th class='insheadercell'>Cash</th>";
						// $htmlStr .= "<th class='insheadercell'># Encs</th>";
						// $htmlStr .= "</tr>";

						// $htmlStr .= "<tr class='insRow'>";	
						// $htmlStr .= "<td class='inscell'>". $insData[0]['case_dt']."</td>";
						// $htmlStr .= "<td class='inscell'>". $insData[0]['id']."</td>";
						// $htmlStr .= "<td class='inscell'>". $insData[0]['case_description']."</td>";
						// $htmlStr .= "<td class='inscell'>". ($insData[0]['employment_related'] ? 'Yes' : 'No')."</td>";
						// $htmlStr .= "<td class='inscell'>". ($insData[0]['auto_accident'] ? 'Yes' : 'No')."</td>";
						// $htmlStr .= "<td class='inscell'>". ($insData[0]['cash'] ? 'Yes' : 'No')."</td>";
						// $htmlStr .= "<td class='inscell'>". $insData[0]['enc_count']."</td>";
						// $htmlStr .= "</tr>";

						$insObj = array();
						for($i =1;$i<=3;$i++) {
							if(isset($insArray['cnt'.$i])) {
								$cntObj = (array)$insArray['cnt'.$i];
								$cnt_id = $cntObj['id'];
								foreach ((array)$insData[0]['ins_data'] as $ki => $ins_data) {
									if($ins_data['id'] == $cnt_id) {
										$insObj[] = $ins_data;
										break;
									}
								}
							}
						}

						if(!empty($insObj)) {
							//$htmlStr .= "<tr>";	
							// $htmlStr .= "<tr><th colspan='2' class='cinsheadercell'>Company </th><th class='cinsheadercell'>Policy  </th><th colspan='2' class='cinsheadercell'>Group </th><th colspan='2' class='cinsheadercell'>Effective </th></tr>";
							foreach ($insObj as $key => $obj) {
								$subName = $obj['subscriber_fname']." ".$obj['subscriber_lname'];
								if(!empty($obj['subscriber_relationship'])) {
									$subName .= " (".$obj['subscriber_relationship'].")";
								}

								$sub_address = array();
								if(!empty($obj['subscriber_city'])) {
									$sub_address[] = $obj['subscriber_city'];
								}

								if(!empty($obj['subscriber_state'])) {
									$sub_address[] = $obj['subscriber_state'];
								}

								if(!empty($obj['subscriber_country'])) {
									$sub_address[] = $obj['subscriber_country'].' '.$obj['subscriber_postal_code'];
								}

								$htmlStr .= "<tr>";
								// $htmlStr .= "<td colspan='2' class='cinscell'>".$obj['name']."</td>";
								// $htmlStr .= "<td class='cinscell'>".$obj['policy_number']."</td>";
								// $htmlStr .= "<td colspan='2' class='cinscell'>".$obj['group_number']."</td>";
								// $htmlStr .= "<td colspan='2' class='cinscell'>".$obj['date']."</td>";
								$htmlStr .= '<td colspan="7" class="cinscell" style="border: 1px solid #000;">';
								$htmlStr .= '<table class="insContainer">';
								$htmlStr .= '<tr>';
									$htmlStr .= '<td class="insuranceData" valign="top" width="250">';
										$htmlStr .= '<div><b>Insurance</b></div>';
										$htmlStr .= '<span>'.$obj['name'].'</span><br/>';
										$htmlStr .= '<span>Policy Number: '.$obj['policy_number'].'</span><br/>';
										$htmlStr .= '<span>Plan Name: '.$obj['plan_name'].'</span><br/>';
										$htmlStr .= '<span>Group Number: '.$obj['group_number'].'</span><br/>';
										$htmlStr .= '<span>Effective Date: '.$obj['date'].'</span><br/>';
									$htmlStr .= '</td>';
									$htmlStr .= '<td class="subscriberData" valign="top" width="180">';
										$htmlStr .= '<div><b>Subscriber</b></div>';
										$htmlStr .= '<span>'.trim($subName).'</span><br/>';
									    $htmlStr .= '<span>S.S.: '.$obj['subscriber_ss'].'</span><br/>';
									    $htmlStr .= '<span>D.O.B.: '.$obj['subscriber_DOB'].'</span><br/>';
									    $htmlStr .= '<span>Phone: '.$obj['subscriber_phone'].'</span><br/>';
									$htmlStr .= '</td>';
									$htmlStr .= '<td class="subscriberAddrData" valign="top">';
										$htmlStr .= '<div><b>Subscriber Address</b></div>';
										$htmlStr .= '<span>'.$obj['subscriber_street'].'</span><br/>';
    									$htmlStr .= '<span>'.implode(", ", $sub_address).'</span><br/>';
									$htmlStr .= '</td>';
								$htmlStr .= '</tr>';
								$htmlStr .= '</table>';
								$htmlStr .= '</td>';
								$htmlStr .= "</tr>";
							}
						}	

						
					}
				}
			}

			$htmlStr .= "</table>";
			$htmlStr .= "</div>";
		}

		return $htmlStr;
	}

	public static function getOrderPDF($pid, $html = '', $filename = 'encounters_and_forms', $options = array()) {
		global $srcdir, $web_root, $css_header;

		$content = '';
		$pData = self::getPatientData($pid);

		if($html != "") {
			$content = $content . $html;
		}

		$pdfE = new \mPDF(
	        $GLOBALS['pdf_language'], // codepage or language/codepage or language - this can help auto determine many other options such as RTL
	        $GLOBALS['pdf_size'], // Globals default is 'letter'
	        '9', // default font size (pt)
	        '', // default_font. will set explicitly in script.
	        $GLOBALS['pdf_left_margin'],
	        $GLOBALS['pdf_right_margin'],
	        ($GLOBALS['pdf_top_margin'] + (!empty($GLOBALS['pdf_header_margin']) ? $GLOBALS['pdf_header_margin'] : 0) ),
	        ($GLOBALS['pdf_bottom_margin'] + (!empty($GLOBALS['pdf_footer_margin']) ? $GLOBALS['pdf_footer_margin'] : 0) ),
	        $GLOBALS['pdf_header_margin'], // default header margin
	        $GLOBALS['pdf_footer_margin'], // default footer margin
	        $GLOBALS['pdf_layout']
	    ); // Globals default is 'P'

      	$pdfE->shrink_tables_to_fit = 1;
      	$keep_table_proportions = true;
      	$pdfE->use_kwt = true;
       	$pdfE->setDefaultFont('dejavusans');
       	$pdfE->autoScriptToLang = true;

       	$pdfE->setAutoTopMargin = "stretch";
       	$pdfE->setAutoBottomMargin = "stretch";

       	$col = $GLOBALS['wmt::use_email_direct'] ? 'email_direct' : 'email';
       	$pdfE->SetHTMLHeader('<table style="width: 100%;"><tr><td style="text-align:left;">Patient ID: '.$pData['pubpid'].'</td><td style="text-align:center;">DOB: '.$pData['DOB'].'</td><td style="text-align:right;">Name: '.($pData['fname'].' '.$pData['lname']).'</td></tr></table>');


		$tmpc = $content;
		$tmpc = self::replaceHTMLTags($tmpc, Array("html","head","body"));
		$tmpc = self::removePatientData($tmpc);

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

	public static function getAndSaveEncounterPDF($pid, $queryData, $filename = '', $options = array(), $html = '', $includeDemo = false) {
		global $srcdir, $web_root, $css_header;

		$content = self::encDemographics($pid, $queryData, $options, $includeDemo);
		$pData = self::getPatientData($pid);

		if($html != "" && $includeDemo != "true") {
			$content = $content . $html;
			//$pdfE->AddPage();
			//$pdfE->writeHTML($html);
		}

		$pdfE = new \mPDF(
	        $GLOBALS['pdf_language'], // codepage or language/codepage or language - this can help auto determine many other options such as RTL
	        $GLOBALS['pdf_size'], // Globals default is 'letter'
	        '9', // default font size (pt)
	        '', // default_font. will set explicitly in script.
	        $GLOBALS['pdf_left_margin'],
	        $GLOBALS['pdf_right_margin'],
	        ($GLOBALS['pdf_top_margin'] + (!empty($GLOBALS['pdf_header_margin']) ? $GLOBALS['pdf_header_margin'] : 0) ),
	        ($GLOBALS['pdf_bottom_margin'] + (!empty($GLOBALS['pdf_footer_margin']) ? $GLOBALS['pdf_footer_margin'] : 0) ),
	        $GLOBALS['pdf_header_margin'], // default header margin
	        $GLOBALS['pdf_footer_margin'], // default footer margin
	        $GLOBALS['pdf_layout']
	    ); // Globals default is 'P'

      	$pdfE->shrink_tables_to_fit = 1;
      	$keep_table_proportions = true;
      	$pdfE->use_kwt = true;
       	$pdfE->setDefaultFont('dejavusans');
       	$pdfE->autoScriptToLang = true;

       	$pdfE->setAutoTopMargin = "stretch";
       	$pdfE->setAutoBottomMargin = "stretch";

       	$col = $GLOBALS['wmt::use_email_direct'] ? 'email_direct' : 'email';
       	$pdfE->SetHTMLHeader('<table style="width: 100%;"><tr><td style="text-align:left;">Patient ID: '.$pData['pubpid'].'</td><td style="text-align:center;">DOB: '.$pData['DOB'].'</td><td style="text-align:right;">Name: '.($pData['fname'].' '.$pData['lname']).'</td></tr></table>');


		$tmpc = $content;
		$tmpc = self::replaceHTMLTags($tmpc, Array("html","head","body"));
		//$tmpc = self::removePatientData($tmpc);

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

	public static function removePatientData($html_str) {
		$doc = new \DOMDocument();
		$doc->formatOutput = true;
		$doc->loadHTML($html_str);
		$xpath = new \DOMXPath($doc);
		$row = $xpath->query("//div[@id='DEM']//table//tr");

		$i = 0;
		foreach($row as $element){
			if($i > 8) {
				$element->parentNode->removeChild($element);
			}
			$i++;
		}

		$newText = $doc->saveHTML();

		return $newText;
	}

	public static function replaceHTMLTags($string, $tags) {
		$tags_to_strip = $tags;
		foreach ($tags_to_strip as $tag){
		    $string = preg_replace("/<\\/?" . $tag . "(.|\\s)*?>/","",$string);
		}

		return $string;
	}


	public static function generateAttachmentPDF($content, $filename) {
		$file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/message_attachments/";
		$fullfilename = $file_location . strtotime(date('Y-m-d H:i:s')) . "_" . $filename . ".pdf";

		$pdf = new \mPDF();

        $pdf->writeHTML($content, false);
        $content_pdf = $pdf->Output($fullfilename, 'F');

        return array(
        	'path' => $fullfilename,
        	'name' => $filename . ".pdf"
        );
	}

	/**
	 * The 'logEmail' method stores a copy of the messages which are exchanged
	 * along with any result parameters which may be returned.
	 */
	public function logEmail($event, $toNumber, $fromNumber, $pid, $msgId, $timestamp, $msg_status, $message, $direction='in', $active=true, $receivers_name = "", $message_subject = "", $raw = null) {

		// Create log entry
		$binds = array();
		$binds[] = $event;
		$binds[] = ($active)? '1' : '0';
		$binds[] = $direction;
		$binds[] = '';
		$binds[] = isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : 1;
		$binds[] = $pid;
		$binds[] = null;
		$binds[] = $toNumber;
		$binds[] = $fromNumber;
		$binds[] = $receivers_name;
		$binds[] = $msgId; // message id of current message
		$binds[] = (empty($timestamp)) ? date('Y-m-d H:i:s') : $timestamp;
		$binds[] = $message_subject	;
		$binds[] = $msg_status;
		$binds[] = $message;
		$binds[] = $raw;

		// Store log record
		$sql = "INSERT INTO `message_log` SET ";
		$sql .= "type='EMAIL', event=?, activity=?, direction=?, gateway=?, userid=?, pid=?, eid=?, msg_to=?, msg_from=?, receivers_name=?, msg_newid=?, msg_time=?, message_subject=?, msg_status=?, message=?, raw_data=?";
		return sqlInsert($sql, $binds);
	}

	/*Merge message with attachment document*/
	public static function mergeMessageContent($message, $files) {
		$messageStr = $message;
		/*if(!empty($files) && is_array($files) && count($files) > 0) {
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
		}*/
		return $messageStr;
	}

	public function filterMsg(&$data) {
		if(preg_match('#<div class="attachmentContainer">(.*?)</div>#', $data['message'])) {
			if($data['direction'] == "in" && $data['type'] == "EMAIL") {
				$data['message'] = preg_replace('#<div class="attachmentContainer">(.*?)</div>#', '', $data['message']);;
			}
		}
	}

	public function displayAttachment($type, $id, $data) {
		global $webserver_root;

		if(preg_match('#<div class="attachmentContainer">(.*?)</div>#', $data['message'])) {
			return false;
		}

		$docsList = self::getMsgDocs($id);
		$downloadLink = $GLOBALS['webroot']."/library/OemrAD/interface/main/messages/downloadDoc.php";

		if((!empty($docsList) && is_array($docsList) && count($docsList) > 0) || !empty($data['url'])) {
			?>
			<div class="attachmentContainer">
				<br/>
				<ul>
					<?php
						foreach ($docsList as $key => $doc) {
							if($data['direction'] == "in" && $data['type'] == "EMAIL") {
								$doc['url'] = self::getSaveURL($doc['url']);
							}

							$file_url = $webserver_root . $doc['url'];
							if(isset($doc['doc_id']) && !empty($doc['doc_id'])) {
								?>
								<li>
									<a href="<?php echo $downloadLink.'?path='.$file_url.'&name='.$doc['file_name']; ?>"><?php echo $doc['file_name']; ?></a> - <a style="cursor: pointer;" class="docrow" id="<?php echo $doc['doc_id']; ?>" >(View)</a>
								</li>
								 <?php
							} else {
								?>
								<li>
									<a href="<?php echo $downloadLink.'?path='.$file_url.'&name='.$doc['file_name']; ?>"><?php echo $doc['file_name']; ?></a>
								</li>
								<?php
							}
						}

						if($type == "P_LETTER") {
							if(!empty($data['file_name']) && !empty($data['url'])) {
								$file_url = $webserver_root . $data['url'];
								?>
								<li>
									<a href="<?php echo $downloadLink.'?path='.$file_url.'&name='.$data['file_name']; ?>"><?php echo $data['file_name']; ?></a>
								</li>
								<?php
							}
						}

					?>
				</ul>
			</div>
			<?php
		}
	}

	/*Write attached document log*/
	public static function writeMessageDocumentLog($id, $type, $file_name, $url, $attachid = '') {
		// Create log entry
		$binds = array();
		$binds[] = $id;
		$binds[] = $type;
		$binds[] = $file_name;
		$binds[] = $url;

		// Store log record
		$sql = "INSERT INTO `message_attachments` SET ";
		$sql .= "message_id=?, type=?, 	file_name=?, url=? ";

		if(!empty($attachid)) {
			$sql .= ", doc_id=? ";
			$binds[] = $attachid;
		}

		return sqlInsert($sql, $binds);
	}

	/*Get Attachments from email*/
	public function getAttachments($connection, $emailIdent, $structure) {
		$attachments = array();
		if(isset($structure->parts) && count($structure->parts)) {

			for($i = 0; $i < count($structure->parts); $i++) {

				$attachments[$i] = array(
					'is_attachment' => false,
					'filename' => '',
					'name' => '',
					'attachment' => ''
				);
				
				if($structure->parts[$i]->ifdparameters) {
					foreach($structure->parts[$i]->dparameters as $object) {
						if(strtolower($object->attribute) == 'filename') {
							$attachments[$i]['is_attachment'] = true;
							$attachments[$i]['filename'] = $object->value;
						}
					}
				}
				
				if($structure->parts[$i]->ifparameters) {
					foreach($structure->parts[$i]->parameters as $object) {
						if(strtolower($object->attribute) == 'name') {
							$attachments[$i]['is_attachment'] = true;
							$attachments[$i]['name'] = $object->value;
						}
					}
				}
				
				if($attachments[$i]['is_attachment']) {
					$attachments[$i]['attachment'] = imap_fetchbody($connection, $emailIdent, $i+1);
					if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
						$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
					}
					elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
						$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
					}
				}
			}
		}

		return $attachments;
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

	/*Save email attachment file on to server*/
	public static function saveAttachmentFile($attachments, $move = true) {
		$attachmentDetails = array();
		$file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/message_attachments/";

		if (!file_exists($file_location)) {
		    mkdir($file_location, 0777, true);
		}

		foreach ($attachments as $key => $attachment) {
			$filename = $attachment['name'];
			$fullfilename = $file_location . strtotime(date('Y-m-d H:i:s')) . "_" . self::cleanStr($filename);

			if(isset($attachment['ignore']) && $attachment['ignore'] === true) {
				$attachmentDetails[] = $attachment;
			} else if(isset($attachment['action']) && $attachment['action'] == "save") {
				if (!file_exists($fullfilename)) {
	                $fp = fopen($fullfilename, "w+");
	                fwrite($fp, $attachment['attachment']);
	                fclose($fp);

	                $attachmentDetails[] = array(
	                	'type' => 'file_url',
	                	'path' => $fullfilename,
	                	'url' => self::getSaveURL($fullfilename),
	                	'file_name' => $filename
	                );
	        	}
        	} else if($attachment['action'] == "upload") {
        		if($move === true) {
        			move_uploaded_file($attachment['url'], $fullfilename);
        		}
        		$attachmentDetails[] = array(
                	'action' => 'upload',
                	'type' => 'file_url',
                	'path' => $fullfilename,
                	'url' => self::getSaveURL($fullfilename),
                	'file_name' => $filename
                );
        	} else if($attachment['action'] == "stay") {
        		$attachmentDetails[] = array(
                	'type' => 'file_url',
                	'path' => $attachment['url'],
                	'url' => self::getSaveURL($attachment['url']),
                	'file_name' => $filename,
                	'id' => isset($attachment['id']) ? $attachment['id'] : ''
                );
        	}
		}

		return $attachmentDetails;
	}

	/*Save file for incoming email*/
	public static function writeFile($attachments, $emailIdent) {
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

	/*Help to search email*/
	public static function imapSearch($connection, $email = '', $sinceDate = '') {
		$searchStr = '';

		if(!empty($email)) {
			$searchStr .= 'FROM "'.$emailId.'" ';
		}

		if(!empty($sinceDate)) {
			$searchStr .= "SINCE \"$sinceDate\" ";
		}

		return imap_search($connection, !empty($searchStr) ? $searchStr : 'ALL');
	}

	/*Imap Connection*/
	public static function getImapConnection() {
		try {
			return imap_open($GLOBALS['IMAP_SERVER_URL'], $GLOBALS['IMAP_USER'], $GLOBALS['IMAP_PASS']);
		} catch (Exception $e) {
			//die('Cannot connect to Gmail: ' . imap_last_error());
			return false;
		}
	}

	public static function removeElementsByTagName($tagName, $document) {
	  $nodeList = $document->getElementsByTagName($tagName);
	  for ($nodeIdx = $nodeList->length; --$nodeIdx >= 0; ) {
	    $node = $nodeList->item($nodeIdx);
	    $node->parentNode->removeChild($node);
	  }
	}

	public static function is_html($string) {
		//return preg_match( "/\/[a-z]*>/i", $string ) != 0;
		return preg_match( "/^<.*>(.*)/", $string ) != 0;
	}

	public static function formateMessageContent($content_str) {
			$content = html_entity_decode($content_str, ENT_COMPAT);
			$htmlContent = "";

			$isHTML = self::is_html($content);

			if($isHTML === false) {
				$htmlContent .= "<div id=\"textContainer\" class=\"plainText\">";
				$htmlContent .= $content;
				$htmlContent .= "</div>";
			} else {
				$htmlContent = $content;
			}

			return $htmlContent;
	}

	//Formate HTML
	public static function displayMessageContent($content, $break = true, $utf_encoding = false) {
		$content = html_entity_decode($content, ENT_COMPAT);

		// create a new DomDocument object
		$doc = new \DOMDocument();

		// load the HTML into the DomDocument object (this would be your source HTML)
		$doc->loadHTML($content);

		self::removeElementsByTagName('script', $doc);
		self::removeElementsByTagName('style', $doc);
		self::removeElementsByTagName('link', $doc);

		$doc->saveHtml();

		//$html = '';
		// $body = $doc->getElementsByTagName('body');
		// if ( $body && 0<$body->length ) {
		//     $body = $body->item(0);
		//     $html = $doc->savehtml($body);
		// }

		$xpath = new \DOMXpath($doc);
		$result = '';
		foreach ($xpath->evaluate('//body/node()') as $node) {
		  $result .= $doc->saveHtml($node);
		}

		if($break === true) {
			$result = str_replace(chr(10), "<br>", $result);
		}

		if($utf_encoding === true) {
			$result = mb_convert_encoding($result, 'UTF-8', 'UTF-8');
		} else {
			$result = utf8_decode($result);
		}

		return $result;
	}

	//Format HTML
	public static function format_html($str) {
	    $str = htmlentities($str, ENT_COMPAT, "UTF-8");
	    //$str = str_replace(chr(10), "<br>", $str);
	    return $str;
	}

	public static function getEmailMessage($connection, $emailIdent, $obj_structure) {
		$obj_section = $obj_structure;
		$section = "1";
		for ($i = 0 ; $i < 10 ; $i++) {
		    if ($obj_section->type == 0) {
		        break;
		    } else {
		        $obj_section = $obj_section->parts[0];
		        $section.= ($i > 0 ? ".1" : "");
		    }
		}

		$text = imap_fetchbody($connection, $emailIdent, $section);

		if ($obj_section->encoding == 3) {
		    $text = imap_base64($text);
		} else if ($obj_section->encoding == 4) {
		    $text = imap_qprint($text);
		}

		foreach ($obj_section->parameters as $obj_param) {
		    if (($obj_param->attribute == "charset") && (mb_strtoupper($obj_param->value) != "UTF-8")) {
		        $text = utf8_encode($text);
		        break;
		    }
		}

		$message = self::format_html($text);
		return $message;
	}

	/*Imap help to fetch incoming email*/
	public static function fetchNewIncomingEmail($pid = '') {
		if (!function_exists('imap_open')) {
        	return array(
        		'status' => 'false',
				'error' => "IMAP is not configured."
        	);
    	} else {
			try {
				$lastLog = self::getLastEmailLog($pid);
				$connection = self::getImapConnection();

				if($connection == false) {
			    	return array(
		        		'status' => 'false',
						'error' => "Cannot connect to email server"
		        	);
			    }

			    $ulastDate = count($lastLog) > 0 ? strtotime($lastLog[0]['msg_time']) : strtotime(date('Y-m-d'));
			    $lastDate = date('d M Y', $ulastDate);
			    $emailData = self::imapSearch($connection, '', $lastDate);
			    $syncEmailList = array();

			    if (!empty($emailData)) {
					foreach ($emailData as $emailIndex => $emailIdent) {
						/* get information specific to this email */
						$overview = imap_fetch_overview($connection, $emailIdent, 0);
						$udate = isset($overview) ? $overview[0]->udate : '';
						$subject = isset($overview) ? $overview[0]->subject : '';
						$msgUID = isset($overview) ? $overview[0]->uid : '';
						$timestamp = date("Y-m-d H:i:s", $udate);

						if($udate > $ulastDate) {
							/* get mail header */
	        		$header = imap_headerinfo($connection, $emailIdent);
	        		$header1 = imap_rfc822_parse_headers(imap_fetchheader($connection, $emailIdent));

	        		/* get mail structure */
	        		$structure = imap_fetchstructure($connection, $emailIdent);

	        		//$message = self::getEmailMessage($connection, $emailIdent, $structure);
	        		$mail = EmailReader::_fetchHeader($header, $emailIdent);
	        		$mail = EmailReader::_fetchHeader1($header1, $mail);
							$mail = EmailReader::_fetch($connection, $emailIdent, $mail, $structure);
							$message = EmailReader::geMessagesContent($mail);
							$messageRawData = (!empty($mail) && isset($mail->content)) ? $mail->content : "";
							$mailJSON = json_encode(array('mail' => $mail));

			        /*Info*/
							$toaddr = $header->to[0]->mailbox . "@" . $header->to[0]->host;
							$fromaddr = $header->from[0]->mailbox . "@" . $header->from[0]->host;
							$fromPerson = isset($header->fromaddress) && $header->fromaddress != $fromaddr ? strip_tags($header->fromaddress) : "";
							$email_subject = isset($header->Subject) ? strip_tags($header->Subject) : "";
							$formattedfromaddr = str_replace( array( '\''), '', $fromaddr);
							$pids = self::getPatientDataByEmail($formattedfromaddr);

							$current_pid = false;
							if($pids != null && count($pids) == 1) {
								$current_pid = $pids[0];

							} else if($pids != null && count($pids) > 1) {
								$current_pid = '';
							} else {
								if(isset($GLOBALS['SYNC_EXIST_USER_EMAIL']) && $GLOBALS['SYNC_EXIST_USER_EMAIL'] == "true") {
									$current_pid = '';
								}
							}

							if(isset($current_pid) && $current_pid !== false) {
								$attachmentList = self::getAttachments($connection, $emailIdent, $structure);
								$emailfileData = self::writeFile($attachmentList, $emailIdent);

		        		//$message = quoted_printable_decode($message);
		        		//$message_content = $this->mergeMessageContent(nl2br($message), $emailfileData);

		        		$message_content = self::mergeMessageContent($message, $emailfileData);

		        		$logid = self::logEmail($subject, $toaddr, $fromaddr, $current_pid, "", $timestamp, "EMAIL_RECEIVED", $message_content, $direction='in', $active=true, $fromPerson, $email_subject, $mailJSON);

								if(isset($logid) && !empty($logid) && isset($emailfileData)) {
									foreach ($emailfileData as $key => $item) {
										self::writeMessageDocumentLog($logid, "file_url", $item['file_name'], $item['url']);
									}
								}

								if(isset($logid) && !empty($logid)) {
									if(isset($GLOBALS["IMAP_DELETE_AFTER_SYNC"]) && $GLOBALS["IMAP_DELETE_AFTER_SYNC"] === "true") {

							      if(!empty($msgUID) && $msgUID > 0) {
							      	$syncEmailList[] = $overview;
							    	}
									}
								}
							}
						}
					}

					if(isset($GLOBALS["IMAP_DELETE_AFTER_SYNC"]) && $GLOBALS["IMAP_DELETE_AFTER_SYNC"] === "true") {
						foreach ($syncEmailList as $syncInx => $syncIdent) {
							$msg_UID = isset($syncIdent) ? $syncIdent[0]->uid : '';

				      if(!empty($msg_UID) && $msg_UID > 0) {
				      	//Delete mail
				      	imap_delete($connection, $msg_UID, FT_UID);
				    	}
						}
					}
				}


				if(!empty($syncEmailList)) {
					//Remove mail marked for delete.
			  	imap_expunge($connection);
				}

			  //Close connection
				imap_close($connection);

			} catch (Exception $e) {
			    return array(
	        		'status' => 'false',
					'error' => 'Caught exception: ',  $e->getMessage(), "\n"
	        	);
			}
		}
	}

	/*Execute to get emails*/
	public function getIncomingEmail($pid = '', $sync = "false") {
		$onPageSync = ($sync == "true") ? $sync : $GLOBALS['IMAP_ON_PAGE_SYNC'];
		if($onPageSync == "true") {
			$responce = self::fetchNewIncomingEmail();
			if($responce['status'] == "false") {
				?>
				<script type="text/javascript">
					alert('<?php echo $responce['error'] ?>');
				</script>
				<?php
			}
		}
	}

	public function fetchEmailOnMessageBoard($pid = '', $sync = "false") {
		$onPageSync = ($sync == "true") ? $sync : $GLOBALS['IMAP_ON_MESSAGE_BOARD_PAGE_SYNC'];
		if($onPageSync == "true") {
			$responce = self::fetchNewIncomingEmail();
			if($responce['status'] == "false") {
				?>
				<script type="text/javascript">
					alert('<?php echo $responce['error'] ?>');
				</script>
				<?php
			}
		}
	}

	public static function getEncouterList($pid, $list = '') {
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

		foreach ($registry_form_name as $var) {
		    if ($toprint = $html_strings[$var]) {
		        foreach ($toprint as $var) {
		            print $var;
		        }
		    }
		}

		?>
		<script type="text/javascript">
			$listType = '<?php echo $list; ?>';
			$finalVariableStr = 'selectedEncounterList';

			if($listType != '') {
				$finalVariableStr = $listType;
			}

			$( ".counterListContainer input[type=checkbox]" ).each(function( index ) {
				var eleid = $(this).attr('id');
				if(window[$finalVariableStr].hasOwnProperty(eleid)) {
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
				  	tempSelected[parentId] = { "title" : parentTitleAttr, "value" : parentVal, "pid" : '<?php echo $pid; ?>' };
				  }

				  var childCheckbox = $(childContainer).find('input[type=checkbox]');
				  $(childCheckbox).each(function( index ) {
				  	var childTitleAttr = $(this).data('title');
				  	var childId = $(this).attr('id');
				  	var childVal = $(this).val();

				  	if($(this).prop("checked") == true) {
				  		tempSelected[childId] = { "title" : childTitleAttr, "value" : childVal, "pid" : '<?php echo $pid; ?>' };

				  		if(parentId != null) {
				  			tempSelected[childId]['parentId'] = parentId;
				  		}
				  	}
				  });
				});

				window[$finalVariableStr] = tempSelected;
			});
		</script>
		<?php
	}

	/*public function setupGlobalField(&$GLOBALS_METADATA, &$USER_SPECIFIC_TABS, &$USER_SPECIFIC_GLOBALS) {
		$GLOBALS_METADATA['Notifications']['IMAP_SERVER_URL'] = array(
			xl('IMAP Server URL'),
            'text',                           // data type
            '',                      // default
            xl('IMAP Server URL used for get incoming emails')
		);
		
		$GLOBALS_METADATA['Notifications']['IMAP_USER'] = array(
            xl('IMAP User for Authentication'),
            'text',                           // data type
            '',                               // default
            xl('Must be empty if IMAP authentication is not used.')
        );

        $GLOBALS_METADATA['Notifications']['IMAP_PASS'] = array(
            xl('IMAP Password for Authentication'),
            'text',                           // data type
            '',                               // default
            xl('Must be empty if IMAP authentication is not used.')
        );

        $GLOBALS_METADATA['Notifications']['IMAP_ON_MESSAGE_BOARD_PAGE_SYNC'] = array(
						xl('Sync Emails On Message Board Page'),
            array(
                'true' => 'True',
                'false' => 'False'
            ),                          // data type
            'false',                      // default
            xl('Sync email on message board page')
				);

        $GLOBALS_METADATA['Notifications']['IMAP_ON_PAGE_SYNC'] = array(
						xl('IMAP On Page Sync Run'),
            array(
                'true' => 'True',
                'false' => 'False'
            ),                          // data type
            'true',                      // default
            xl('Used to run sync email on page')
				);

				$GLOBALS_METADATA['Notifications']['IMAP_DELETE_AFTER_SYNC'] = array(
						xl('IMAP Delete Email After Sync'),
            array(
                'true' => 'Yes',
                'false' => 'No'
            ),                          // data type
            'false',                      // default
            xl('Used for delete email after sync.')
				);

				$GLOBALS_METADATA['Notifications']['SYNC_EXIST_USER_EMAIL'] = array(
            xl('Sync Emails For NonExisting Email Addresses'),
            array(
                'true' => 'True',
                'false' => 'False'
            ),                          // data type
            'false',                                  // default
            xl('Sync Emails For NonExisting Email Addresses')
        );

        $GLOBALS_METADATA['Notifications']['EMAIL_MAX_ATTACHMENT_SIZE'] = array(
           xl('Email Max Attachment Size in MB'),
            'text',                           // data type
            '10',                               // default
            xl('Email Max Attachment Size in MB')
        );

        $GLOBALS_METADATA['Notifications']['EMAIL_FROM_NAME'] = array(
           xl('Email From name'),
            'text',                           // data type
            '',                               // default
            xl('Email From name')
        );

        $GLOBALS_METADATA['PDF']['pdf_header_margin'] = array(
           xl('Header margin (mm)'),
            'text',                           // data type
            '0',                               // default
            xl('Header margin (mm)')
        );

        $GLOBALS_METADATA['PDF']['pdf_footer_margin'] = array(
           xl('Footer margin (mm)'),
            'text',                           // data type
            '0',                               // default
            xl('Header margin (mm)')
        );
	}*/

	public static function getMessageContent($message, $version = 1) {
			$messageHTML = html_entity_decode($message);
			$messageResponce = $messageHTML;

			if(!empty($messageHTML)) {
					if(!self::is_html($messageResponce)) { 
							if($version === 1) {
								$messageResponce = "<div class=\"plainTextContainer\">";
								$messageResponce .= $messageHTML;
								$messageResponce = "</div>";
							} else if($version === 2) {
								$msgTmp = str_replace("<br />", "\n", str_replace("<br/>", "\n", $messageHTML));
								$messageResponce = nl2br(strip_tags($msgTmp));
							} else if($version === 3) {
								$msgTmp = str_replace("<br />", "\n", str_replace("<br/>", "\n", $messageHTML));
								$msgTmp = str_replace("\n", "\n > ", "> ". str_replace("\r", "\r", str_replace("\t", "\t", $msgTmp)));
								$messageResponce = nl2br(strip_tags($msgTmp));
							} else {
								$messageResponce = nl2br(strip_tags($messageHTML));
							}
					} else {
						$messageResponce = $messageHTML;
					}
			}

			return $messageResponce;
	}

	public function getMsgLogHTML($id) {
		$messageData = self::getMessageById($id);
		if(count($messageData) > 0) {
			$message = isset($messageData[0]['message']) ? html_entity_decode($messageData[0]['message']) : "";

			$replyLine = "\n\n\n<span> ------------------------------ Reply ------------------------------ </span>\n\n";
			$temp = preg_replace('#<div class="attachmentContainer">(.*?)</div>#', '', $message);
			$messagehtml = str_replace("\n", '\n', str_replace("\r", '\r', str_replace("\t", '\t', $temp)));

			$fistLine = "On ". date("D, M d, Y at g:i A ", strtotime($messageData[0]['msg_time'])) . $messageData[0]['event'] ." <\n" . $messageData[0]['msg_from'] . "> wrote: \n\n";
			//$messagehtml2 = str_replace("<br />", "\n", str_replace("<br/>", "\n", $temp));
			//$messagehtml2 = str_replace("\n", "\n > ", "> ". str_replace("\r", "\r", str_replace("\t", "\t", $messagehtml2)));

			$messagehtml2 = nl2br("\n\n\n");
			$messagehtml2 .= nl2br($fistLine);
			$messagehtml2 .= self::getMessageContent($temp, 3);

			return array(
				'content' => nl2br(strip_tags($fistLine . $messagehtml)),
				'content_html' =>  $messagehtml2
			);
		}
		return false;
	}

	public function getMsgContent($message) {
		$msgText = '';
		$msgHtml = '';

		if(!empty($message)) {
			$temp = preg_replace('#<div class="attachmentContainer">(.*?)</div>#', '', $message);
			$msgText = str_replace("\n", '\n', str_replace("\r", '\r', str_replace("\t", '\t', $temp)));
			
			$msgHtml = self::getMessageContent($temp, 2);
		}

		return array(
			'content' => nl2br(strip_tags($msgText)),
			'content_html' => $msgHtml
		);
	}

	public function getSMSMsgContent($message) {
		$msgText = '';
		$msgHtml = '';

		if(!empty($message)) {
			//$msgText = str_replace("\n", '\n', str_replace("\r", '\r', str_replace("\t", '\t', $message)));
			$msgHtml = $message;
		}

		return array(
			'content' => strip_tags($msgHtml),
			'content_html' => $msgHtml
		);
	}

	public static function updateStatusOfMsg($id, $all = false) {
		if(!empty($id)) {
			$query = "SELECT * FROM `message_log` WHERE `id` = $id AND `direction` = 'in' ";
			$row = sqlQuery($query);

			if(isset($row['id'])) {
				sqlStatementNoLog("UPDATE `message_log` SET activity = '0' WHERE id = ?", array($id));
				if(isset($row['msg_from']) && !empty($row['msg_from']) && $all == true) {
					sqlStatementNoLog("UPDATE `message_log` SET activity = '0' WHERE `direction` = 'in' AND `activity` = '1' AND `msg_from` = ?", array($row['msg_from']));
				}
			}
		}
	}

	function getPnotesByDate(
    	$date,
	    $activity = "1",
	    $cols = "*",
	    $pid = "%",
	    $limit = "all",
	    $start = 0,
	    $username = '',
	    $docid = 0,
	    $status = "",
	    $orderid = 0,
	    $n_orderby = ''
	) {

	    $sqlParameterArray = array();
	    if ($docid) {
	        $sql = "SELECT $cols FROM pnotes AS p, gprelations AS r " .
	        "WHERE p.date LIKE ? AND r.type1 = 1 AND " .
	        "r.id1 = ? AND r.type2 = 6 AND p.id = r.id2 AND p.pid != p.user";
	        array_push($sqlParameterArray, '%'.$date.'%', $docid);
	    } else if ($orderid) {
	        $sql = "SELECT $cols FROM pnotes AS p, gprelations AS r " .
	        "WHERE p.date LIKE ? AND r.type1 = 2 AND " .
	        "r.id1 = ? AND r.type2 = 6 AND p.id = r.id2 AND p.pid != p.user";
	        array_push($sqlParameterArray, '%'.$date.'%', $orderid);
	    } else {
	        $sql = "SELECT $cols FROM pnotes AS p " .
	        "WHERE date LIKE ? AND pid LIKE ? AND p.pid != p.user";
	        array_push($sqlParameterArray, '%'.$date.'%', $pid);
	    }

	    $sql .= " AND deleted != 1"; // exclude ALL deleted notes
	    if ($activity != "all") {
	        if ($activity == '0') {
	            // only return inactive
	            $sql .= " AND (activity = '0' OR message_status = 'Done') ";
	        } else { // $activity == '1'
	            // only return active
	            $sql .= " AND activity = '1' AND message_status != 'Done' ";
	        }
	    }

	    if ($username) {
	        $sql .= " AND assigned_to LIKE ?";
	        array_push($sqlParameterArray, $username);
	    }

	    if ($status) {
	        $sql .= " AND message_status IN ('".str_replace(",", "','", add_escape_custom($status))."')";
	    }

	    if(!empty($n_orderby)) {
	    	$sql .= " ORDER BY " . $n_orderby;
		} else {
			$sql .= " ORDER BY date DESC";
		}
	    
	    if ($limit != "all") {
	        $sql .= " LIMIT ".escape_limit($start).", ".escape_limit($limit);
	    }

	    $res = sqlStatement($sql, $sqlParameterArray);

	    $all=array();
	    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
	        $all[$iter] = $row;
	    }

	    return $all;
	}

	public function checkIsExistOrNot($msg_from) {

		if(empty($msg_from)) {
			return "";
		}

		$fromNumber = preg_replace("/[^0-9]/", "", $msg_from);

		// Find patient(s) by phone
		$test_number = (strlen($fromNumber) > 10) ? substr($fromNumber,1,10) : $fromNumber;
		$test_area = substr($test_number,0,3);
		$test_prefix = substr($test_number,3,3);
		$test_local = substr($test_number,6,4);
		$test_string = $test_area .'.?'. $test_prefix .'.?'. $test_local;

		$binds = array();

		$binds[] = trim($msg_from);
		//$binds[] = trim($msg_from);


		$test_string = '';
		if(!empty($test_area) && !empty($test_prefix) && !empty($test_local)) {
			$test_string = $test_area .'.?'. $test_prefix .'.?'. $test_local;
			$binds[] = $test_string;
			//$phoneSQl = " OR  `phone_cell` REGEXP ?  ";
			$phoneSQl = " OR  replace(replace(replace(replace(replace(replace(phone_cell,' ',''),'(','') ,')',''),'-',''),'/',''),'+','') REGEXP ?  ";
		}

		if(!empty($test_string)) {
		   //$phoneSQl .= " OR `secondary_phone_cell` REGEXP ? ";
		   $phoneSQl .= " OR replace(replace(replace(replace(replace(replace(secondary_phone_cell,' ',''),'(','') ,')',''),'-',''),'/',''),'+','') REGEXP ? ";
		   $binds[] = "(^|,)(1)?($test_string)(,|$)";
		}


		$col = $GLOBALS['wmt::use_email_direct'] ? 'email_direct' : 'email';
		$formattedfromaddr = str_replace( array( '\''), '', $msg_from);
    $result = sqlQuery("SELECT COUNT(pid) as count from patient_data WHERE (".$col."=? OR FIND_IN_SET('".$formattedfromaddr."',secondary_email) ".$phoneSQl." )", $binds);
		
		if(isset($result) && $result['count'] > 1) {
			return "<span style='color:red'>More than 1 user has same email/phone number.</span>";
		} else if(isset($result) && $result['count'] == 0) {
			return "<span style='color:red'>No user found with this email/phone number</span>";
		}

		return "";
	}

	public static function getMaxSize() {
		return isset($GLOBALS['EMAIL_MAX_ATTACHMENT_SIZE']) ? $GLOBALS['EMAIL_MAX_ATTACHMENT_SIZE'] : '10';
	}

	public static function getMaxSizeErrorMsg() {
		return "Can't send attachment more than ".self::getMaxSize()." mb. ";
	}

	public static function calAttachmentSize($attachmentList) {
		$totalFileSize = 0;
		$maxAttachmentSize = self::getMaxSize();
		$attachmentStatus = true;

		if(isset($attachmentList)) {
			foreach ($attachmentList as $iIndex => $item) {
				if(file_exists($item['url'])) {
					$filesize = filesize($item['url']);
					$filesize = number_format($filesize / 1048576, 2); // megabytes with 1 digit
					$totalFileSize += $filesize;
				}
			}
			if($maxAttachmentSize < $totalFileSize) {
				$attachmentStatus = self::getMaxSizeErrorMsg();
			}
		}

		return $attachmentStatus;
	}

	/*Get Case list by pid*/
	public static function getCaseList($pid, $id = '') {
		$cases = array();
		$sql = 'SELECT form_cases.*, (SELECT COUNT(*) FROM case_appointment_link AS ' .
			'ca LEFT JOIN openemr_postcalendar_events AS oe ON (ca.pc_eid = oe.pc_eid) ' .
			'WHERE pid = ? AND oe.pc_case = form_cases.id) AS enc_count FROM '.
			'form_cases WHERE pid = ? AND ';
		if($type == 'active') $sql .= 'closed = 0 AND ';

		if(!empty($id)) {
			$sql .= "id = $id AND ";
		}

		$sql .= 'activity > 0 ORDER BY id DESC';
		$res = sqlStatement($sql, array($pid, $pid));
		while($row = sqlFetchArray($res)) {
			
			for($i=1; $i<=3; $i++) {
				if(isset($row['ins_data_id'.$i]) && !empty($row['ins_data_id'.$i])) {
					$insObj = self::getInsuranceDataById($pid, $row['ins_data_id'.$i]);
					$row['ins_data'][] = isset($insObj) ? $insObj[0] : array();
				}
			}

			$cases[] = $row;
		}

		return $cases;
	}

	/* Get Insurance related data from datatable by passing different parameters */
	public static function getInsuranceDataById($pid, $ins_id, $provider_id = '', $order_by = '`date` DESC', $type = '') {
			if(!$pid || !$ins_id) {
			 	return false;
			}
			$binds = array();
			$query = 'SELECT ins.*, ic.`id` AS ic_id, ic.`name`, ic.`attn`, ic.`cms_id`, ic.`alt_cms_id`, ic.`ins_type_code`, ad.`line1`, ad.`line2`,  '.
				'ad.`city`, ad.`state`, ad.`zip`, ad.`plus_four`, ph.`area_code`, '.
				'ph.`prefix`, ph.`number`';
			if($provider_id) {
				$query .= ', us.`id` AS pr_id, us.`fname` AS pr_fname, us.`lname` AS pr_lname, us.`federaltaxid` AS pr_federaltaxid, us.`upin` AS pr_upin, us.`npi` AS pr_npi, us.`facility_id` AS pr_facility_id';
			}	

			$query .= ' FROM insurance_data AS ins '.
				'LEFT JOIN insurance_companies AS ic ON ins.`provider` = ic.`id` '.
				'LEFT JOIN phone_numbers AS ph ON '.
				'(ic.`id` = ph.`foreign_id` AND ph.`type` = 2) '.
				'LEFT JOIN addresses AS ad ON (ic.`id` = ad.`foreign_id`) ';
			
			if($provider_id) {
				$query .= 'LEFT JOIN users AS us ON us.`id` = ? ';
				$binds[] = $provider_id;
			}

			$query .= '	WHERE ins.`id` = ? AND ins.`pid` = ? ';	
			$binds[] = $ins_id;
			$binds[] = $pid;
			if($type) {
				$query .= ' AND ins.`type` = ? ';
				$binds[] = $type;
			}
			$query .= 'AND ins.`provider` IS NOT NULL AND ins.`provider` > 0 '.
				'AND ins.`date` IS NOT NULL AND ins.`date` != "0000-00-00" '.
				'AND ins.`date` != "" ';
			$query .= 'ORDER BY ' . $order_by;
			
			
			$fres = sqlStatement($query, $binds);
			$data = array();
			while($row = sqlFetchArray($fres)) {
				$data[] = $row;
			}
			return $data;		
	}

	public static function setTimeZone() {
		$glres = sqlQuery(
        "SELECT gl_name, gl_index, gl_value FROM globals WHERE gl_name = 'gbl_time_zone'" .
	        "ORDER BY gl_name, gl_index"
	    );

	    if (!empty($glres['gl_value'])) {
            date_default_timezone_set($glres['gl_value']);
        }
	}

	public static function getMsgDocs($id) {
		$docs_list = array();
		$binds = array($id);

		$sql = "SELECT ma.* ";
		$sql .= "FROM `message_attachments` ma ";
		$sql .= "WHERE ma.`message_id` = ? ";

		$docs_result = sqlStatementNoLog($sql, $binds);

		while ($docs_data = sqlFetchArray($docs_result)) {
			$docs_list[] = $docs_data;
		}
		return $docs_list;
	}

	public function generateDocList($docList) {
		$docs = array();
		if(!empty($docList)) {
			foreach ($docList as $key => $docItem) {
				if(isset($docItem['doc_id']) && !empty($docItem['doc_id'])) {
					$filePath = str_replace("file://","",$docItem['url']);
					$docs[] = array(
						'action' => 'stay',
						'url' => $filePath,
						'name' => $docItem['file_name'],
						'id' => $docItem['doc_id'],
					);
				} else {
					$fileURL = $GLOBALS["fileroot"] . $docItem['url'];
					$docs[] = array(
						'action' => 'stay',
						'url' => $fileURL,
						'name' => $docItem['file_name']
					);
				}
			}
		}

		return $docs;
	}

	public function htmlDocFileList($docList = array(), $docsData = array(), $msgData = array()) {
		global $webserver_root;

		$downloadLink = $GLOBALS['webroot']."/library/OemrAD/interface/main/messages/downloadDoc.php";

		?>
			<div class="containerFile">
			<div class="files" id="filesDoc">
				<div>
					<ul class="fileList">
					<?php 
						foreach ($docsData as $key => $docItem) { 
							if($msgData['direction'] == "in" && $msgData['type'] == "EMAIL") {
								$docItem['url'] = self::getSaveURL($docItem['url']);
							}

							$file_url = $webserver_root . $docItem['url'];
							if(isset($docItem['doc_id']) && !empty($docItem['doc_id'])) {
							?>
								<li>
									<a href="<?php echo $downloadLink.'?path='.$file_url.'&name='.$doc['file_name']; ?>"><?php echo $docItem['file_name']; ?></a> <a style="cursor: pointer; display: none;" class="docrow" id="<?php echo $docItem['doc_id']; ?>" >(View)</a>
								</li>
								 <?php
							} else {
								?>
								<li>
									<a href="<?php echo $downloadLink.'?path='.$file_url.'&name='.$docItem['file_name']; ?>"><?php echo $docItem['file_name']; ?></a>
								</li>
								<?php
							}
						}

						if(isset($msgData['type']) && $msgData['type'] == "P_LETTER") {
							$pData = sqlQuery('SELECT * FROM `postal_letters` WHERE `message_id` = ?', $msgData['id']);
							if(!empty($pData['file_name']) && !empty($pData['url'])) {
								$file_url = $webserver_root . $pData['url'];
								?>
								<li>
									<a href="<?php echo $downloadLink.'?path='.$file_url.'&name='.$pData['file_name']; ?>"><?php echo $pData['file_name']; ?></a>
								</li>
								<?php
							}
						}
					?>
					</ul>
				</div>
			</div>
			</div>
			<?php
	}

	public function isFailedToSend($data) {
		$isFailed = false;
		if(!empty($data) && $data['activity'] == "1" && $data['direction'] == "out" && $data['msg_status'] != "EMAIL_SENT") {
			$isFailed = true;
		}
		return $isFailed;
	}

	public static function isActive($data) {
		if(!empty($data)) {
			return true;
		}
		return false;
	}

	public function isFailedToSMSSend($data) {
		$isFailed = false;
		if(!empty($data) && $data['activity'] == "1" && $data['direction'] == "out" && $data['msg_status'] != "MESSAGE_SENT") {
			$isFailed = true;
		}
		return $isFailed;
	}

	public static function assignUserToMSG($msgId) {
		if(!empty($msgId)) {
			sqlStatementNoLog("UPDATE `message_log` SET assigned = userid WHERE id = ?", array($msgId));
		}
	}

	public function extractVariable($data, $list) {
		$varList = array();

		if(!empty($data) && is_array($data)) {
			foreach ($list as $varName => $colName) {
				if(isset($data[$colName])) {
					$varList[$varName] = $data[$colName];
				}
			}
		}
		return $varList;
	}

	public static function includeRequest($data, $list) {
		$requestList = array();

		if(!empty($data) && is_array($data)) {
			foreach ($list as $varName => $colName) {
				if(isset($data[$colName])) {
					$requestList[$colName] = $data[$colName];
				}
			}
		}

		return $requestList;
	}

	public static function logEmailData($status, $data, $form_action = '', $form_msgId = '') {
		
		//Store email attachment
		if(!empty($data['attachmentList'])) {
			$attchFiles = self::saveAttachmentFile($attachmentList);
		}

		$isActive = self::isActive($status);

		$binds = array();
		$binds[] = $isActive === true ? '1' : '0';
		$binds[] = $data['pid'];
		$binds[] = $data['subject'];
		$binds[] = isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : '';
		$binds[] = $data['email'];
		$binds[] = '';
		$binds[] = date('Y-m-d H:i:s');
		$binds[] = $isActive === true ? $status : 'EMAIL_SENT';
		$binds[] = $data['message_content'];
		$binds[] = json_encode(self::includeRequest($data['request'], array(
							'message',
							'pid',
							'email_id', 
							'subject',
							'baseDocList'
						)));

		// Write new record
		$sql = "INSERT INTO `message_log` SET ";
		$sql .= "`activity`= ?, `type`='EMAIL', `pid`=?, `event`=?, `direction`='out', `userid`=?, `msg_to`=?, `msg_from`=?, `msg_time`=?, `msg_status`=?, `message`=?, `raw_data`=? ";
			
		$msgLogId = sqlInsert($sql, $binds);

		/*Assign User to Msg*/
		if($isActive === true) {
			self::assignUserToMSG($msgLogId);
		}

		/*Write log and file*/
		if(!empty($msgLogId) && !empty($attchFiles)) {
			foreach ($attchFiles as $key => $attachItem) {
				$attachId = isset($attachItem['id']) ? $attachItem['id'] : '';
				self::writeMessageDocumentLog($msgLogId, "file_url", $attachItem['file_name'], $attachItem['url'], $attachId);
			}
		}

		if($form_action == "reply" && !empty($form_msgId) && !empty($msgLogId)) {
			self::updateStatusOfMsg($form_msgId, false);
		}
		
		return $msgLogId;
	}

	/*Get message by ids log*/
	public static function getMessageByIds($msgIds = array()) {
		$email_list = array();
		$binds = array();

		$sql = "SELECT ml.* ";
		$sql .= "FROM `message_log` ml ";
		$sql .= "WHERE ml.`type` IS NOT NULL ";

		if(!empty($msgIds)) {
			$sql .= "AND ml.`id` IN(".implode(",", $msgIds).") ";
		}
		$sql .= "order by ml.`msg_time` DESC";

		$email_result = sqlStatementNoLog($sql, $binds);

		while ($email_data = sqlFetchArray($email_result)) {
			$email_list[] = $email_data;
		}
		return $email_list;
	}

	/*Get Email Attachments*/
	public static function getEmailAttachmentList($id) {
		$bind = array($id);
	    $query = "SELECT * " .
	    "FROM message_attachments AS ma WHERE " .
	    "ma.`message_id` = ? ";

	    $query .= "ORDER BY ma.`date` DESC";
	    $dres = sqlStatement($query, $bind);

	    $list = array();
	    while ($drow = sqlFetchArray($dres)) {
		    $list[] = $drow;
	    }
	    return $list;
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
		$fullfilename = $file_location . strtotime(date('Y-m-d H:i:s')) . "_temdoc.pdf";

		$final_file = $file;

		if($pdfversion > "1.4"){
			shell_exec('gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -sOutputFile="'.$fullfilename.'" "'.$file.'"'); 
			$final_file = $fullfilename;
		}
		else{
			$final_file = $file;
		}

		return $final_file;
	}

	public static function generateEmailHTML($type, $data) {
		$html = '';

		if(!empty($data)) {
			$field_to_label = 'To';
			$field_to_val = '';

			$raw_data = array();
			if(isset($data) && !empty($data['raw_data'])) {
				$raw_data = json_decode($data['raw_data'], true);
			}
			
			if($type == "email") {
				if(isset($data['direction']) && $data['direction'] == 'out') {
					$field_to_label = 'Email To:';
					$field_to_val =  $data['msg_to'];

					$field_to_label_1 = 'Subject:';
					$field_to_val_1 = isset($raw_data['subject']) ? $raw_data['subject'] : '';
				} else if(isset($data['direction']) && $data['direction'] == 'in') {
					$field_to_label = 'Email From:';
					$field_to_val =  $data['msg_from'];

					$field_to_label_1 = 'Subject:';
					$field_to_val_1 = $data['message_subject'];
				}
			} else if($type == "sms") {
				if(isset($data['direction']) && $data['direction'] == 'out') {
					$field_to_label = 'SMS To:';
					$field_to_val =  $data['msg_to'];
				} else if(isset($data['direction']) && $data['direction'] == 'in') {
					$field_to_label = 'SMS From:';
					$field_to_val =  $data['msg_from'];
				}

				$field_to_label_1 = 'Message Time:';
				$field_to_val_1 = $data['msg_time'];
			} else if($type == "fax") {
				if(isset($data['direction']) && $data['direction'] == 'out') {
					$field_to_label = 'Fax To:';
					$field_to_val =  $data['msg_to'];
				} else if(isset($data['direction']) && $data['direction'] == 'in') {
					$field_to_label = 'Fax From:';
					$field_to_val =  $data['msg_from'];
				}
			} else if($type == "postal_letter") {
				if(isset($data['direction']) && $data['direction'] == 'out') {
					$field_to_label = 'Address To:';
					$field_to_val =  $data['msg_to'];
				} else if(isset($data['direction']) && $data['direction'] == 'in') {
					$field_to_label = 'Address From:';
					$field_to_val =  $data['msg_from'];
				}
			}

			$field_message = isset($data['message']) ? self::displayMessageContent($data['message'], false, true) : "";

			ob_start();
			?>
				<table style="overflow:wrap;">
					<tr>
						<td width="150"><b><?php echo $field_to_label; ?></b></td>
						<td><?php echo $field_to_val; ?></td>
					</tr>
					<?php if(isset($field_to_label_1) && !empty($field_to_label_1)) {?>
					<tr>
						<td width="100"><b><?php echo $field_to_label_1; ?></b></td>
						<td><?php echo $field_to_val_1; ?></td>
					</tr>
					<?php } ?>
					<tr>
						<td colspan="2"><b>Message:</b></td>
					</tr>
					<tr>
						<td colspan="2"><?php echo $field_message; ?></td>
					</tr>
				</table>
			<?php
			$html = ob_get_clean();
		}
		return $html;
	}

	/*Email Attachment doc*/
	public static function generateFinalDoc($type, $data, $file_name, $pid, $category_id, $doc_date = '') {
		global $webserver_root;

		try {

			$pdf = new \mPDF('utf-8',array(215.9, 279.4));

			$pdf->allow_charset_conversion=true;
			$pdf->charset_in='UTF-8';

			$pdf->AddPageByArray([
				'margin-left' => '12.6mm',
				'margin-right' => '12.6mm',
				'margin-top' => '13.5mm',
				'margin-bottom' => '13.5mm',
			]);

			$allowedImage = array('jpg','jpeg','jpe','png');
			$allowedFile = array('pdf');

			foreach($data as $dE => $data_item) {
				if($dE > 0) {
					$pdf->AddPage();
				}

				$imageFiles = array();
				$dataFiles = array();

				$emailHtml = self::generateEmailHTML($type, $data_item);
				$pdf->writeHTML(utf8_decode($emailHtml), false);	

				foreach($data_item['attachments'] as $item) {
					$fileExt = end(explode(".", $item['url']));
					if(in_array($fileExt, $allowedImage) == true) {
						$imageFiles[] = array(
							'file_name' => $item['file_name'],
							'path' => $webserver_root . $item['url']
						);
					} else if(in_array($fileExt, $allowedFile) == true) {
						$dataFiles[] = $webserver_root . $item['url'];
					} else {
						$messages[] = "Skipped file becuase of UnSupported File Type: ".$item['filename'];
					}
				}

				foreach($dataFiles as $file){
					$fileExt = end(explode(".", $file));
						$pdf->SetImportUse();
						$rsponceFile = self::checkFPDI($file);
						if($rsponceFile != false && !empty($rsponceFile)) {
							$pagecount = $pdf->SetSourceFile($rsponceFile);
							for ($i=1; $i<=($pagecount); $i++) {
								$pdf->AddPage();
								$import_page = $pdf->ImportPage($i);
								$pdf->UseTemplate($import_page);
							}
						}
				}

				if(!empty($imageFiles)) {
					$pdf->AddPage();
					foreach($imageFiles as $file){
						$htmlStr = '<div><img src="'.$file['path'].'"/><div style="margin-top:5px;margin-bottom:25px;"><span><b>'.$file['file_name'].'</b></span></div></div>';
						$pdf->writeHTML($htmlStr, false);
					}
				}
			}

			$pagecount = $pdf->page;
			
			$file_location = $GLOBALS["OE_SITES_BASE"]."/".$_SESSION['site_id']."/documents/message_attachments/";
			$fullfilename = $file_location . strtotime(date('Y-m-d H:i:s')) . "_adddpc.pdf";
			
			$content_pdf = $pdf->Output($fullfilename, 'S');

			//Generate tmp file of documents
			$tmpfname = tempnam(sys_get_temp_dir(), 'POST');
			rename($tmpfname, $tmpfname .= '.tmp');
			file_put_contents($tmpfname, $content_pdf);

			$docResponce = self::doAddToDocument($tmpfname, $file_name, $pid, $category_id, $doc_date);

			return array(
				'status' => true,
				'error' => isset($docResponce['error']) ? $docResponce['error'] : "",
				'message' => isset($docResponce['message']) ? $docResponce['message'] : "",
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

	public static function doAddToDocument($file_path, $file_name, $pid, $category_id, $doc_date = '') {
		$fname = $file_name.'.pdf';
		$filesize = filesize($file_path);
		$tmpfile = fopen($file_path, "r");
        $filetext = fread($tmpfile, $filesize);
        fclose($tmpfile);

        // set mime, test for single DICOM and assign extension if missing.
        $mimetype = mime_content_type($file_path);

        if (strpos($filetext, 'DICM') !== false) {
	        $mimetype = 'application/dicom';
	        $parts = pathinfo($fname);
	        if (!$parts['extension']) {
	            $fname .= '.dcm';
	        }
	    }

	    $d = new \Document();

	    if(!empty($doc_date)) {
	    	$d->set_docdate($doc_date);
		}

        $rc = $d->createDocument(
            $pid,
            $category_id,
            $fname,
            $mimetype,
            $filetext,
            '',
            1,
            0,
            $file_path
        );
        if ($rc) {
            $error = $rc;
        } else {
            $message = "Success";
        }

        return array(
			'status' => false,
			'error' => isset($error) ? $error : "",
			'message' => isset($message) ? $message : ""
		);
	}

	function addDocuments($type, $list, $file_name, $pid, $category_id, $doc_date = '') {
		if(!empty($list)) {
			$messagesList = self::getMessageByIds($list);

			$emailItem = array();
			foreach ($messagesList as $key => $item) {
				$attachmentList = self::getEmailAttachmentList($item['id']);
				$emailItem[] = array_merge($item, array('attachments' => $attachmentList));
			}
			return self::generateFinalDoc($type, $emailItem, $file_name, $pid, $category_id, $doc_date);
		}

		return false;
	}

	// TransmitEmail
  public static function TransmitEmail($eData = array(), $opts = array()) {
    global $username;

    $pid = isset($opts['pid']) ? $opts['pid'] : "";
    $request_data = isset($opts['request_data']) ? $opts['request_data'] : array();
    $files = isset($opts['files']) ? $opts['files'] : array();
    $logMsg = isset($opts['logMsg']) ? $opts['logMsg'] : true;
    $responceData = array();
    
    try {

        try {
            // Attache Files
            $attachmentList = Attachment::prepareAttachment($pid, $request_data, $files);
            $attachmentList = Attachment::saveAttachmentFile($attachmentList);

            // Check and calculate attachments size
            $attachmentStatus = self::calculateAttachmentSize($attachmentList);

            // Added Attachment to email
            //self::AddAttachmentToEmailObj($attachmentList, $email);
        } catch(\Throwable $e) {
            // Clear Generated files.
            $attachmentList = Attachment::clearAttachmentFile($attachmentList);                
            throw new \Exception($e->getMessage()); 
        }

        // Email data.
        foreach ($eData as $ek => $emailData) {
            $responceData[$ek] = array(
                'status' => true, 
                'to' => isset($emailData['email']) ? $emailData['email'] : '', 
                'errors' => array()
            );

            try {
                //if(empty($emailData['from'])) $emailData['from'] = "PATIENT SUPPORT";
            		if(empty($emailData['from'])) $emailData['from'] = "devlog1@zohomail.in";
                if(empty($emailData['from_name'])) $emailData['from_name'] = $GLOBALS['EMAIL_FROM_NAME'];

                if(!isset($emailData['from']) || empty($emailData['from'])) {
                    throw new \Exception('Empty from email.');
                }
                
                if(!isset($emailData['email']) || empty($emailData['email'])) {
                    throw new \Exception('Empty email.');
                }

                $email = new \wmt\Email(TRUE);
                $email->FromName = $emailData['from_name'];

                // Prepare email data
                $email_data = array();
                $email_data['patient'] = $emailData['patient'];
                $email_data['from'] = $emailData['from'];
                $email_data['subject'] = $emailData['subject'];
                $email_data['email'] = $emailData['email'];
                $email_data['html'] = $emailData['html'];
                $email_data['text'] = $emailData['text'];

                $reqContent = isset($emailData['content']) ? $emailData['content'] : "";
                if(!empty($reqContent)) {
                    $email_data['message_content'] = $reqContent;
                    $email_data['html'] = $reqContent;
                    $email_data['text'] = trim(strip_tags($reqContent));
                }

                // Added Attachment to email
                self::AddAttachmentToEmailObj($attachmentList, $email);
                
                
                // Send email
                $emailStatus = $email->TransmitEmail($email_data);

                if($emailStatus == "552") {
                    throw new \Exception(self::getMaxSizeErrorMsg()); 
                }

            } catch(\Throwable $e) {
                $status = $e->getMessage();
                $responceData[$ek]['status'] = false;
                $responceData[$ek]['errors'][] = $e->getMessage(); 
            }

            if(isset($logMsg) && $logMsg === false) {
                //Skip iteration
                continue;
            }

            // Log message process.
            $emailItems = $email_data['email'];
            if(!is_array($email_data['email'])) {
                $emailItems = array($email_data['email']);
            }
            $emailIsActive = (!empty($emailStatus) || !empty($status)) ? true : false;

            //Itrate over email.
            for ($ei=0; $ei < count($emailItems); $ei++) {
                try {
                    $cemail = $emailItems[$ei];

                    // Prepare Message log data
                    $extrainfo = array();

                    if(!empty($emailData['template'])) $extrainfo['message'] = $emailData['template'];
                    if(!empty($cemail)) $extrainfo['email_id'] = $cemail;
                    if(!empty($email_data['subject'])) $extrainfo['subject'] = $email_data['subject'];
                    if(!empty($email_data['custom_email_id'])) $extrainfo['custom_email_id'] = $email_data['custom_email_id'];
                    if(!empty($email_data['custom_email_check'])) $extrainfo['custom_email_check'] = $email_data['custom_email_check'];

                    $msgAttachmentList = array();
                    if(!empty($emailData['request_data'])) {
                        $arrayTypeList = array("local_files", "documents", "notes", "orders", "encounter_forms", "demos_insurances");
                        foreach ($arrayTypeList as $typeKey => $typeItem) {
                            if(isset($emailData['request_data'][$typeItem])) {
                                $msgAttachmentList[$typeItem] = json_decode($emailData['request_data'][$typeItem], true);
                            }
                        }
                    }
                    if(!empty($msgAttachmentList)) $extrainfo['attachments'] = $msgAttachmentList;

                    $msgData = array(
                        $emailIsActive === true ? '1' : '0',
                        $pid,
                        $email_data['subject'],
                        isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : "",
                        $cemail,
                        isset($username) ? $username : "",
                        date('Y-m-d H:i:s'),
                        $emailIsActive === true ? $status : 'EMAIL_SENT',
                        $email_data['html'],
                        json_encode($extrainfo)
                    );

                    
                    // Write new record
                    $sql = "INSERT INTO `message_log` SET ";
                    $sql .= "`activity`= ?, `type`='EMAIL', `pid`=?, `event`=?, `direction`='out', `userid`=?, `msg_to`=?, `msg_from`=?, `msg_time`=?, `msg_status`=?, `message`=?, `raw_data`=? ";
                        
                    $msgLogId = sqlInsert($sql, $msgData);

                    // Assign User to Msg
                    if($emailIsActive === true) {
                        Attachment::assignUserToMSG($msgLogId);
                    }
                    

                    // Write log and file
                    if(!empty($msgLogId) && !empty($attachmentList)) {
                        foreach ($attachmentList as $key => $attachItem) {
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
                    if(!isset($responceData[($ek)]['data'])) {
                        $responceData[$ek]['data'] = array();
                    }
                    $responceData[$ek]['data'][] = array('to' => $cemail, 'msgid' => $msgLogId);

                } catch(\Throwable $e) {
                    $responceData[$ek]['status'] = false;
                    $responceData[$ek]['errors'][] = $e->getMessage();
                }
            }
        }
    } catch(\Throwable $e) {
        foreach ($eData as $ek => $emailData) {
            $responceData[$ek] = array(
                'status' => false, 
                'to' => isset($emailData['email']) ? $emailData['email'] : array(), 
                'errors' => array($e->getMessage())
            );
        }
    }

    return $responceData;
  }

  // Add prepared attachment file to email (Attachment).
  public static function AddAttachmentToEmailObj($attachmentList = array(), &$email) {
      try {
          if(!empty($email)) {
              foreach ($attachmentList as $aKey => $aItem) {
                  if(isset($aItem['type']) && !empty($aItem['type'])) {
                      if((isset($aItem['path']) && !empty($aItem['path'])) && (isset($aItem['name']) && !empty($aItem['name']))) {
                          $email->AddAttachment($aItem['path'], $aItem['name']);
                      }
                  }
              }
          }
      } catch(\Throwable $e) {
          throw new \Exception($e->getMessage()); 
      }
  }

  public static function calculateAttachmentSize($attachmentList) {
      try {
          $totalFileSize = 0;
          $maxAttachmentSize = self::getMaxSize();
          $attachmentStatus = true;

          if(isset($attachmentList)) {
              foreach ($attachmentList as $iIndex => $item) {
                  if(!isset($item['path'])) {
                      continue;
                  }    

                  if(file_exists($item['path'])) {
                      $filesize = filesize($item['path']);
                      $filesize = number_format($filesize / 1048576, 2); // megabytes with 1 digit
                      $totalFileSize += $filesize;
                  } else {
                      throw new \Exception("File not exists for attachment.");
                  }
              }
              
              if($maxAttachmentSize < $totalFileSize) {
                  throw new \Exception(self::getMaxSizeErrorMsg());
              }
          }

          return $attachmentStatus;

      } catch(\Throwable $e) {
          throw new \Exception($e->getMessage()); 
      }
  }
}