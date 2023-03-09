<?php

/** **************************************************************************
 *	MESSAGE_TRACKER.PHP
 *
 *	Copyright (c)2019 - Medical Technology Services
 *
 *	This program is free software: you can redistribute it and/or modify it 
 *	under the terms of the GNU General Public License as published by the Free 
 *	Software Foundation, either version 3 of the License, or (at your option) 
 *	any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
 *	FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for 
 *	more details.
 *
 *	You should have received a copy of the GNU General Public License along with 
 *	this program.  If not, see <http://www.gnu.org/licenses/>.	This program is 
 *	free software; you can redistribute it and/or modify it under the terms of 
 *	the GNU Library General Public License as published by the Free Software 
 *	Foundation; either version 2 of the License, or (at your option) any 
 *	later version.
 *
 *  @package sms
 *  @version 1.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <info@mdtechsvcs.com>
 * 
 *************************************************************************** */

// Sanitize escapes
$sanitize_all_escapes = true;

// Stop fake global registration
$fake_register_globals = false;

require_once("../globals.php");
require_once($GLOBALS['srcdir']."/options.inc.php");
require_once($GLOBALS['srcdir']."/wmt-v3/wmt.globals.php");
require_once($GLOBALS['srcdir']."/pnotes.inc");

//Included EXT_Message File
include_once($GLOBALS['srcdir']."/OemrAD/oemrad.globals.php");

use OpenEMR\Core\Header;
use OpenEMR\OemrAd\EmailMessage;
use OpenEMR\OemrAd\Attachment;
use OpenEMR\OemrAd\MessagesLib;
use OpenEMR\OemrAd\Smslib;
use OpenEMR\Common\Acl\AclMain;


$page_action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = $_POST['search']['value']; // Search value

$filterVal = isset($_POST['filterVal']) ? $_POST['filterVal'] : array(); // Filter value
$colList = isset($_POST['columnList']) ? $_POST['columnList'] : array(); // Column List value

$searchArray = array();
$columnList = array(
	array(
		"name" => "dt_control",
		"title" => "",
		"data" => array(
            "className" => 'dt-control text',
            "orderable" => false,
            "data" => '',
            "defaultContent" => '',
            "width" => "20"
		) 
	),
	array(
		"name" => "id",
		"title" => "Id",
		"data" => array(
			"width" => "20"
		)
	),
	array(
		"name" => "rec_date_time",
		"title" => "Received Date/Time",
		"data" => array(
			"defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
			"width" => "120"
		)
	),
	array(
		"name" => "msg_type",
		"title" => "Type",
		"data" => array(
			"defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
			"width" => "40"
		)
	),
	array(
		"name" => "direction",
		"title" => "Direction",
		"data" => array(
			"defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
			"width" => "40"
		)
	),
	array(
		"name" => "to",
		"title" => "To",
		"data" => array(
			"defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
			"width" => "50",
			"orderable" => false,
			"visible" => true
		)
	),
	array(
		"name" => "from",
		"title" => "From",
		"data" => array(
			"defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
			"width" => "50",
			"orderable" => false,
			"visible" => true
		)
	),
	array(
		"name" => "patient_name",
		"title" => "Patient Name(PID)",
		"data" => array(
			"defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
			"width" => "140"
		)
	),
	array(
		"name" => "status",
		"title" => "Status",
		"data" => array(
			"defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
			"orderable" => false,
			"width" => "40"
		)
	),
	array(
		"name" => "assignment",
		"title" => "Assignment",
		"data" => array(
			"defaultValue" => getHtmlString('<i class="defaultValueText">Empty</i>'),
			"orderable" => false,
			"width" => "40"
		)
	),
	array(
		"name" => "action",
		"title" => "Actions",
		"data" => array(
			"orderable" => false,
			"width" => "80"
		)
	),
	array(
		"name" => "to_list",
		"title" => "To List",
		"data" => array(
			"width" => "0",
			"orderable" => false,
			"visible" => false
		)
	),
	array(
		"name" => "cc_list",
		"title" => "CC List",
		"data" => array(
			"width" => "0",
			"orderable" => false,
			"visible" => false
		)
	),
	array(
		"name" => "msg_action",
		"title" => "Msg Actions",
		"data" => array(
			"orderable" => false,
			"visible" => false,
			"width" => "0"
		)
	),
	array(
		"name" => "email_subject",
		"title" => "Email Subject",
		"data" => array(
			"width" => "0",
			"orderable" => false,
			"visible" => false
		)
	),
	array(
		"name" => "msg_status_dec",
		"title" => "Msg Status",
		"data" => array(
			"width" => "0",
			"orderable" => false,
			"visible" => false
		)
	),
	array(
		"name" => "msg_content",
		"title" => "Msg Content",
		"data" => array(
			"width" => "0",
			"orderable" => false,
			"visible" => false
		)
	),
	array(
		"name" => "msg_content1",
		"title" => "Msg Content",
		"data" => array(
			"width" => "0",
			"orderable" => false,
			"visible" => false
		)
	),
	array(
		"name" => "msg_content2",
		"title" => "Msg Content",
		"data" => array(
			"width" => "0",
			"orderable" => false,
			"visible" => false
		)
	),
	array(
		"name" => "msg_attach",
		"title" => "Msg Attach",
		"data" => array(
			"width" => "0",
			"orderable" => false,
			"visible" => false
		)
	)
);

function getHtmlString($text) {
	return addslashes(htmlspecialchars($text));
}

//Filter Query Data
function generateFilterQuery($filterData = array()) {
	$filterQryList = array();
	$filterQry = "";

	if(!empty($filterData)) {
		if(isset($filterData['form_type']) && !empty($filterData['form_type'])) {
			$bFormType = array();
			if(in_array('all', $filterData['form_type'])) {
				$bFormType = array('email','sms','fax','p_letter');
			} else {
				$bFormType = $filterData['form_type'];
			}
			$filterQryList[] = "m.type IN('" . implode("','", $bFormType) . "')";
		}

		if(isset($filterData['form_direction']) && !empty($filterData['form_direction'])) {
			if($filterData['form_direction'] != "all") {
				$filterQryList[] = "m.direction = '" . $filterData['form_direction'] ."'";
			}
		}

		if(isset($filterData['form_status']) && !empty($filterData['form_status'])) {
			if($filterData['form_status'] != "all") {
				$bFormStatus = ($filterData['form_status'] == 'active')? '1' : '0';
				$filterQryList[] = "m.`activity` = '" . $bFormStatus ."'";
			}
		}

		if(isset($filterData['form_linked']) && !empty($filterData['form_linked'])) {
			if($filterData['form_linked'] != "all") {
				if ($filterData['form_linked'] == 'linked') $filterQryList[] = "( m.`pid` IS NOT NULL AND m.`pid` != '' ) ";
				if ($filterData['form_linked'] == 'unknown') $filterQryList[] = "( m.`pid` IS NULL OR m.`pid` = '' ) ";
			}
		}

		if(isset($filterData['form_assigned']) && !empty($filterData['form_assigned'])) {
			if($filterData['form_assigned'] != "all") {
				if ($filterData['form_assigned'] == 'assigned') {
					$filterQryList[] = "( m.`assigned` IS NOT NULL AND m.`assigned` != '' ) ";
				} else if ($filterData['form_assigned'] == 'unassigned') {
					$filterQryList[] = "( m.`assigned` IS NULL OR m.`assigned` = '' ) ";
				} else {
					$filterQryList[] = "( m.`assigned` = '".$filterData['form_assigned']."' ) ";
				}
			}
		}

		if(isset($filterData['search_phone_email']) && !empty($filterData['search_phone_email'])) {
			$filterQryList[] = "(( m.`type` = 'EMAIL' AND m.`direction` = 'in' AND m.`msg_from` LIKE '%".$filterData['search_phone_email']."%' ) OR ( m.`type` = 'SMS' AND m.`direction` = 'in' AND m.`msg_from` LIKE '%".$filterData['search_phone_email']."%' ))";
		}

		if(!empty($filterQryList)) {
			$filterQry = implode(" and ", $filterQryList);
		}
	}

	return $filterQry;
}

//Prepare Data Table Data
function prepareDataTableData($row_item = array(), $columns = array()) {
	$rowData = array();

	foreach ($columns as $clk => $cItem) {
		if(isset($cItem['name'])) {
			if($cItem['name'] == "rec_date_time") {
				$msg_date_val = strtotime($row_item['msg_time']);
				$datestr = date('Y-m-d', $msg_date_val);
				$timestr = date('h:iA', $msg_date_val);

				$fieldHtml1 = $datestr . ' ' .$timestr;
				if(!empty($fieldHtml1)) {
					$fieldHtml = "<div class='noneBreakText'>$fieldHtml1</div>";
				}

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "msg_type") {
				$msg_type = '[UNKNOWN]';
				if ($row_item['type'] == 'SMS') $msg_type = 'SMS';
				if ($row_item['type'] == 'PHONE') $msg_type = 'Phone';
				if ($row_item['type'] == 'PORTAL') $msg_type = 'Portal';
				if ($row_item['type'] == 'EMAIL') $msg_type = 'Email';
				if ($row_item['type'] == 'FAX') $msg_type = 'Fax';
				if ($row_item['type'] == 'P_LETTER') $msg_type = 'Postal Letter';
				if ($row_item['type'] == 'NOTES') $msg_type = 'Internal Notes';

				if(!empty($msg_type)) {
					$fieldHtml = "<div class='noneBreakText'>$msg_type</div>";
				}
				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "direction") {
				$msg_direction = 'Inbound';
				if ($row_item['direction'] == 'out') $msg_direction = "Outbound";

				if(!empty($msg_direction)) {
					$fieldHtml = "<div class='noneBreakText'>$msg_direction</div>";
				}
				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "status") {
				$msg_status = 'Completed';
				if ($row_item['activity'] == '1') $msg_status = "<span style='color:red'>Active</span>";

				if(!empty($msg_status)) {
					$fieldHtml = "<div class='noneBreakText'>$msg_status</div>";
				}

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "patient_name") {
				$fieldHtml = "";

				if ($row_item['pid']) {
					$fieldHtml = "<a href=\"#!\" onclick=\"goParentPid('".$row_item['pid']."');\">". $row_item[$cItem['name']] . " (". $row_item['pid'] .")" . "</a>";
				} else {
					$fieldHtml = "<span style='color:red'>Not Linked</span>";
					if($row_item['type'] == "EMAIL" || $row_item['type'] == "SMS") {
						$checkCol = $row_item['direction'] == "in" ? 'msg_from' : 'msg_to';
						$checkMsg = EmailMessage::checkIsExistOrNot($row_item[$checkCol]);
						$fieldHtml .= " ".!empty($checkMsg) ? " - ".$checkMsg : "";
					}
				}
				
				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "assignment") {
				$fieldHtml = "";

				if ($row_item['assigned']) {
					$fieldHtml = $row_item['user_name'] ."&nbsp;";
				} else if ($row_item['group_name']) {
					$fieldHtml = "<span style='color:red'>" . $row_item['group_name'] . "</span>";
				} else {
					$fieldHtml = "<span style='color:red'>Unassigned</span>";
				}

				if(!empty($fieldHtml)) {
					$fieldHtml = "<div class='noneBreakText'>$fieldHtml</div>";
				}
				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "to") {
				$fieldHtml = "";

				if($row_item['direction'] == "out") {
					if($row_item['type'] == "FAX") {
						$fieldHtml = !empty($row_item['fm_receivers_name']) ? $row_item['fm_receivers_name'] : "";
						$fieldHtml .= isset($row_item['msg_to']) ? " (" . nl2br($row_item['msg_to']) . ")" : "";
					} else if($row_item['type'] == "P_LETTER") {
						$fieldHtml = !empty($row_item['receivers_name']) ? $row_item['receivers_name'] : "";
						$fieldHtml .= isset($row_item['msg_to']) ? " (" . nl2br($row_item['msg_to']) . ")" : "";
					} else {
						$fieldHtml = isset($row_item['msg_to']) ? nl2br($row_item['msg_to']) : "";
					}
				}
				
				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "from") {
				$fieldHtml = "";

				if($row_item['direction'] == "in") {
					if($row_item['type'] == "EMAIL" || $row_item['type'] == "SMS") { 
						$fieldHtml .= isset($row_item['receivers_name']) && !empty($row_item['receivers_name']) ? $row_item['receivers_name'] . "<br/>" : "";
					}
					
					$fieldHtml = nl2br($row_item['msg_from']);
					
					// if($row_item['type'] == "EMAIL" && !empty($row_item['message_subject'])) {
					// 	$fieldHtml .= isset($row_item['message_subject']) && !empty($row_item['message_subject']) ? "\n\nSubject: ".$row_item['message_subject'] : "";
					// }
				}
				
				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "to_list") { 
				$fieldHtml = "";

				if($row_item['direction'] == "in") {
					if($row_item['type'] == "EMAIL") {
						$email_raw_data = isset($row_item['raw_data']) && !empty($row_item['raw_data']) ? json_decode($row_item['raw_data'], true) : array();
						$email_to_list = isset($email_raw_data['mail']) && isset($email_raw_data['mail']['to_list']) && !empty($email_raw_data['mail']['to_list']) ? $email_raw_data['mail']['to_list'] : array();
						if(empty($email_to_list)) {
							$fieldHtml = isset($row_item['msg_to']) ? $row_item['msg_to'] : "";
						} else {
							$fieldHtml = implode(", ", $email_to_list);
						}
					}
				} else if($row_item['direction'] == "out") {
					if($row_item['type'] == "FAX") {
						$fieldHtml = !empty($row_item['fm_receivers_name']) ? $row_item['fm_receivers_name'] : "";
						$fieldHtml .= isset($row_item['msg_to']) ? " (" . nl2br($row_item['msg_to']) . ")" : "";
					} else if($row_item['type'] == "P_LETTER") {
						$fieldHtml = !empty($row_item['receivers_name']) ? $row_item['receivers_name'] : "";
						$fieldHtml .= isset($row_item['msg_to']) ? " (" . nl2br($row_item['msg_to']) . ")" : "";
					} else {
						$fieldHtml = isset($row_item['msg_to']) ? nl2br($row_item['msg_to']) : "";
					}
				}
				
				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "cc_list") {
				$fieldHtml = "";

				if($row_item['direction'] == "in") {
					if($row_item['type'] == "EMAIL") {
						$email_raw_data = isset($row_item['raw_data']) && !empty($row_item['raw_data']) ? json_decode($row_item['raw_data'], true) : array();
						$email_cc_list = isset($email_raw_data['mail']) && isset($email_raw_data['mail']['cc_list']) && !empty($email_raw_data['mail']['cc_list']) ? $email_raw_data['mail']['cc_list'] : array();

						if(!empty($email_cc_list)) {
							$fieldHtml = implode(", ", $email_cc_list);
						}
					}
				}
				
				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "email_subject") {
				$fieldHtml = "";

				$raw_data = !empty($row_item['raw_data'])  ? json_decode($row_item['raw_data'], true) : array();

				if($row_item['type'] == "EMAIL" && !empty($row_item['message_subject'])) {
					$fieldHtml = isset($row_item['message_subject']) && !empty($row_item['message_subject']) ? $row_item['message_subject'] : "";
				} else if($row_item['type'] == "EMAIL" && !empty($raw_data['subject'])) {
					$fieldHtml = isset($raw_data['subject']) && !empty($raw_data['subject']) ? $raw_data['subject'] : "";
				}

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "msg_status_dec") {
				$fieldHtml = "";

				if($row_item['type'] == "EMAIL" || $row_item['type'] == "SMS") {
					$fieldHtml = $row_item['msg_status'];
				} else if($row_item['type'] == "P_LETTER") {
					$fieldHtml = $row_item['pl_description'];
				} else if($row_item['type'] == "FAX") {
					$fieldHtml = $row_item['fm_description'];
				}

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "action") {
				$fieldHtml = "";

				if (empty($row_item['pid'])) {
					$action1 = "actionType('dolink', this, '" . $row_item['id'] . "', '" . $row_item['type'] . "')";
					$actionHtml1 = "<button type='button' data-direction='".$row_item['direction']."' data-msg_to='".$row_item['msg_to']."' data-msg_from='".$row_item['msg_from']."' id='linkinput".$row_item['id']."' onClick=\"$action1\">".xlt('Link')."</button>";
				} else {
					$action1 = "actionType('dounlink', this, '" . $row_item['id'] . "', '" . $row_item['type'] . "')";
					$actionHtml1 = "<button type='button' data-direction='".$row_item['direction']."' data-msg_to='".$row_item['msg_to']."' data-msg_from='".$row_item['msg_from']."' id='unlinkinput".$row_item['id']."' onClick=\"$action1\">".xlt('Unlink')."</button>";
				}

				if (!empty($row_item['pid'])) {
					if (empty($row_item['assigned']) && empty($row_item['assign_group']) && ($row_item['direction'] == "in" || ($row_item['direction'] == "out" && $row_item['activity'] == "1")) && (AclMain::aclCheckCore('admin', 'super', '', 'write') || AclMain::aclCheckCore('patients', 'assignmsg', '', 'write'))) {
						$action2 = "actionType('doassign', this, '" . $row_item['id'] . "', '" . $row_item['type'] . "')";
						$actionHtml2 = "<button type='button' data-pid='".$row_item['pid']."' onClick=\"$action2\">".xlt('Assign')."</button>";
					} else if(($row_item['direction'] == "in" || ($row_item['direction'] == "out" && $row_item['activity'] == "1")) && (AclMain::aclCheckCore('admin', 'super', '', 'write') || AclMain::aclCheckCore('patients', 'unassignmsg', '', 'write'))) { 
						$action2 = "actionType('dounassign', this, '" . $row_item['id'] . "', '" . $row_item['type'] . "')";
						$actionHtml2 = "<button type='button' data-pid='".$row_item['pid']."' onClick=\"$action2\">".xlt('Unassign')."</button>";
					}
				}

				if($row_item['type'] == 'NOTES') {
					if ($row_item['activity'] == '1') { 
						$action3 = "actionType('donoteinactive', this, '" . $row_item['id'] . "', '" . $row_item['type'] . "')";
						$actionHtml3 = "<button type='button' onClick=\"$action3\">".xlt('Mark Inactive')."</button>";
					} else { 
						$action3 = "actionType('donoteactive', this, '" . $row_item['id'] . "', '" . $row_item['type'] . "')";
						$actionHtml3 = "<button type='button' onClick=\"$action3\">".xlt('Mark Active')."</button>";
					}
				} else {
					if($row_item['activity'] == '1') {
						$action3 = "actionType('doinactive', this, '" . $row_item['id'] . "', '" . $row_item['type'] . "')";
						$actionHtml3 = "<button type='button' onClick=\"$action3\">".xlt('Mark Inactive')."</button>";
					} else {
						$action3 = "actionType('doactive', this, '" . $row_item['id'] . "', '" . $row_item['type'] . "')";
						$actionHtml3 = "<button type='button' onClick=\"$action3\">".xlt('Mark Active')."</button>";
					}
				}

				if($row_item['type'] == "EMAIL") {
					$action4 = "actionType('doreplyemail', this, '" . $row_item['id'] . "', '" . $row_item['type'] . "')";
					$actionHtml4 = "<button type='button' data-direction='".$row_item['direction']."' data-msg_to='".$row_item['msg_to']."' data-msg_from='".$row_item['msg_from']."' data-pid='".$row_item['pid']."' id='replyinput".$row_item['id']."' onClick=\"$action4\">".xlt('Reply')."</button>";
				}
				if($row_item['type'] == "SMS") {
					$action4 = "actionType('doreplysms', this, '" . $row_item['id'] . "', '" . $row_item['type'] . "')";
					$actionHtml4 = "<button type='button' data-direction='".$row_item['direction']."' data-msg_to='".$row_item['msg_to']."' data-msg_from='".$row_item['msg_from']."' data-pid='".$row_item['pid']."' id='replyinput".$row_item['id']."' onClick=\"$action4\">".xlt('Reply')."</button>";
				}
				if($row_item['type'] != "NOTES" && $row_item['type'] != "SMS" && !empty($row_item['pid'])) {
					$action41 = "actionType('doopen', this, '" . $row_item['id'] . "', '" . $row_item['type'] . "')";
					$actionHtml41 = "<button type='button' data-pid='".$row_item['pid']."' data-type='".$row_item['type']."' onClick=\"$action41\">".xlt('Open')."</button>";
				}

				if($row_item['direction'] == "out") {	
					$action5 = "actionType('doresend', this, '" . $row_item['id'] . "', '" . $row_item['type'] . "')";
					$actionHtml5 = "<button type='button' data-pid='".$row_item['pid']."' data-type='".$row_item['type']."' onClick=\"$action5\">".xlt('Resend')."</button>";
				}

				$actionHtml6 = '<div class="actiondp dropdown"><div class="labelContainer"><button onclick="dpToggle(this)" class="dropbtn btn btn-secondary btn-sm">'.xlt('More Actions').'</button></div><div class="dropdown-content dpContainer">'.$actionHtml1 . $actionHtml2 . $actionHtml3 . $actionHtml6.'</div></div>';

				//$fieldHtml = $actionHtml1 . $actionHtml2 . $actionHtml3;
				$fieldHtml = $actionHtml6;

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "msg_action") {
				if($row_item['type'] == "EMAIL") {
					$action4 = "actionType('doreplyemail', this, '" . $row_item['id'] . "', '" . $row_item['type'] . "')";
					$actionHtml4 = "<button type='button' class='btn btn-secondary btn-sm' data-direction='".$row_item['direction']."' data-msg_to='".$row_item['msg_to']."' data-msg_from='".$row_item['msg_from']."' data-pid='".$row_item['pid']."' id='replyinput".$row_item['id']."' onClick=\"$action4\">".xlt('Reply')."</button>";
				}
				if($row_item['type'] == "SMS") {
					$action4 = "actionType('doreplysms', this, '" . $row_item['id'] . "', '" . $row_item['type'] . "')";
					$actionHtml4 = "<button type='button' class='btn btn-secondary btn-sm' data-direction='".$row_item['direction']."' data-msg_to='".$row_item['msg_to']."' data-msg_from='".$row_item['msg_from']."' data-pid='".$row_item['pid']."' id='replyinput".$row_item['id']."' onClick=\"$action4\">".xlt('Reply')."</button>";
				}
				if($row_item['type'] != "NOTES" && $row_item['type'] != "SMS" && !empty($row_item['pid'])) {
					$action41 = "actionType('doopen', this, '" . $row_item['id'] . "', '" . $row_item['type'] . "')";
					$actionHtml41 = "<button type='button' class='btn btn-secondary btn-sm' data-pid='".$row_item['pid']."' data-type='".$row_item['type']."' onClick=\"$action41\">".xlt('Open')."</button>";
				}

				if($row_item['direction'] == "out") {	
					$action5 = "actionType('doresend', this, '" . $row_item['id'] . "', '" . $row_item['type'] . "')";
					$actionHtml5 = "<button type='button' class='btn btn-secondary btn-sm' data-pid='".$row_item['pid']."' data-type='".$row_item['type']."' onClick=\"$action5\">".xlt('Resend')."</button>";
				}

				$fieldHtml = '<div class="btn-group">' . $actionHtml4 . $actionHtml5 . $actionHtml41 . '</div>';

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "msg_content") {
				$fieldHtml = "";
				$formatedMessage = MessagesLib::displayIframeMsg($row_item['message'], 'text');

				ob_start();
				?>
				<iframe scrolling="no" id="msgContent<?php echo $row_item['id']; ?>" data-id="<?php echo $row_item['id']; ?>" class="contentiFrame" srcdoc="<?php echo htmlentities($formatedMessage) ?>"></iframe>
				<script>$(document).ready(function(){$("#msgContent<?php echo $row_item['id']; ?>").iframereadmoretext();});</script>
				<?php echo MessagesLib::displayAttachment($row_item['type'], $row_item['id'], $row_item); ?>
				<?php
				$fieldHtml = ob_get_clean();

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				continue;
			} else if($cItem['name'] == "msg_attach") {
				/*
				ob_start();
				MessagesLib::displayAttachment($row_item['type'], $row_item['id'], $row_item);
				$fieldHtml = ob_get_clean();

				$rowData[$cItem['name']] = (isset($fieldHtml) && $fieldHtml != "") ? getHtmlString($fieldHtml) : "";
				*/
				continue;
			} 

			$rowData[$cItem['name']] = (isset($row_item[$cItem['name']]) && !empty($row_item[$cItem['name']])) ? getHtmlString($row_item[$cItem['name']]) : "";
		}
	}

	return $rowData;
}

//Generate Query
function generateQuery($data = array(), $isSearch = false) {
	$select_qry = isset($data['select']) ? $data['select'] : "*";
	$where_qry = isset($data['where']) ? $data['where'] : "";
	$order_qry = isset($data['order']) ? $data['order'] : "m.id"; 
	$order_type_qry = isset($data['order_type']) ? $data['order_type'] : "desc";

	if($order_qry == "rec_date_time") {
		$order_qry = "m.msg_time";
	} else if($order_qry == "msg_type") {
		$order_qry = "m.type";
	} else if($order_qry == "direction") {
		$order_qry = "m.direction";
	} else if($order_qry == "patient_name") {
		$order_qry = "CONCAT(CONCAT_WS(' ', IF(LENGTH(p.fname),p.fname,NULL), IF(LENGTH(p.lname),p.lname,NULL)), ' (', p.pubpid ,')')";
	} 

	$limit_qry = isset($data['limit']) ? $data['limit'] : ""; 
	$offset_qry = isset($data['offset']) ? $data['offset'] : "asc";

	$sql = "SELECT $select_qry from message_log m ";
	
	if($isSearch === true) {
		//$sql .= " LEFT JOIN `users` u ON m.`assigned` IS NOT NULL AND m.`assigned` = u.`id` LEFT JOIN `patient_data` p ON m.`pid` IS NOT NULL AND m.`pid` = p.`pid` LEFT JOIN `list_options` l ON l.`option_id` = m.`assign_group` AND l.`list_id` LIKE 'Messaging_Groups' LEFT JOIN `postal_letters` pl ON m.`id` = pl.`message_id` LEFT JOIN `fax_messages` fm ON m.`id` = fm.`message_id`";
	} else {
		$sql .= " LEFT JOIN `users` u ON m.`assigned` IS NOT NULL AND m.`assigned` = u.`id` LEFT JOIN `patient_data` p ON m.`pid` IS NOT NULL AND m.`pid` = p.`pid` LEFT JOIN `list_options` l ON l.`option_id` = m.`assign_group` AND l.`list_id` LIKE 'Messaging_Groups' LEFT JOIN `postal_letters` pl ON m.`id` = pl.`message_id` LEFT JOIN `fax_messages` fm ON m.`id` = fm.`message_id`";
	}

	if(!empty($where_qry)) {
		$sql .= " WHERE $where_qry";
	}

	if(!empty($order_qry)) {
		$sql .= " ORDER BY $order_qry $order_type_qry";
	}

	if($limit_qry != '' && $offset_qry != '') {
		$sql .= " LIMIT $limit_qry , $offset_qry";
	}

	return $sql;
}

//Get DataTable Data
function getDataTableData($data = array(), $columns = array(), $filterVal = array()) {
	extract($data);

	if(isset($msg_id) && !empty($msg_id)) {
		$result = sqlStatement(generateQuery(array(
			"select" => "m.`id`, m.`type`, m.`event`, m.`pid`, m.`eid`, m.`assigned`, m.`msg_time`, m.`activity`, m.`direction`, m.`message_subject`, m.`msg_status`, m.`message`, m.`assign_group`, l.`title` AS 'group_name', CONCAT(LEFT(u.`fname`,1), '. ',u.`lname`) AS 'user_name', m.`msg_from`, m.`msg_to`, m.`receivers_name`, pl.`status_code` AS pl_status_code, pl.`description` AS pl_description, pl.`file_name` AS pl_file_name, pl.`url` AS pl_url, fm.`status_code` AS fm_status_code, fm.`description` AS fm_description, fm.`file_name` AS fm_file_name, fm.`url` AS fm_url, fm.`receivers_name` AS fm_receivers_name, CONCAT(p.`fname`, ' ',p.`lname`) AS 'patient_name', p.DOB, p.pubpid, m.raw_data ",
			"where" => "m.id = $msg_id"
		)));

		$dataSet = array();
		while ($row_item = sqlFetchArray($result)) {
			$dataSet[] = prepareDataTableData($row_item, $columns);
		}

		return count($dataSet) === 1 ? $dataSet[0] : $dataSet;
	}


	// Search 
	$searchQuery = "";
	if($searchValue != ''){
	   //$searchQuery = " AND (emp_name LIKE ? or email LIKE ? ) ";
	   // $searchArray = array( 
	   //      'emp_name'=>"%$searchValue%", 
	   //      'email'=>"%$searchValue%",
	   //      'city'=>"%$searchValue%"
	   // );
	}

	//Filter Value
	$filterQuery .= generateFilterQuery($filterVal);

	if(!empty($filterQuery)) {
		$searchQuery .= " " . $filterQuery;
	}

	//$sql_data_query = generateQuery("COUNT(*) AS allcount");
	$bindArray = array();

	$records = sqlQuery(generateQuery(array(
		"select" => "COUNT(*) AS allcount",
		"filter_data" => array()
	), true));
	$totalRecords = $records['allcount'];

	$records = sqlQuery(generateQuery(array(
		"select" => "COUNT(*) AS allcount",
		"where" => $searchQuery,
		"filter_data" => $filterVal
	), true));

	$totalRecordwithFilter  = $records['allcount'];

	$result = sqlStatement(generateQuery(array(
		"select" => "m.`id`, m.`type`, m.`event`, m.`pid`, m.`eid`, m.`assigned`, m.`msg_time`, m.`activity`, m.`direction`, m.`message_subject`, m.`msg_status`, m.`message`, m.`assign_group`, l.`title` AS 'group_name', CONCAT(LEFT(u.`fname`,1), '. ',u.`lname`) AS 'user_name', m.`msg_from`, m.`msg_to`, m.`receivers_name`, pl.`status_code` AS pl_status_code, pl.`description` AS pl_description, pl.`file_name` AS pl_file_name, pl.`url` AS pl_url, fm.`status_code` AS fm_status_code, fm.`description` AS fm_description, fm.`file_name` AS fm_file_name, fm.`url` AS fm_url, fm.`receivers_name` AS fm_receivers_name, CONCAT(p.`fname`, ' ',p.`lname`) AS 'patient_name', p.DOB, p.pubpid, m.raw_data ",
		"where" => $searchQuery,
		"order" => $columnName,
		"order_type" => $columnSortOrder,
		"limit" => $row,
		"offset" => $rowperpage
	)));

	$dataSet = array();
	while ($row_item = sqlFetchArray($result)) {
		$dataSet[] = prepareDataTableData($row_item, $columns);
	}

	return array(
		"recordsTotal" => $totalRecords,
		"recordsFiltered" => $totalRecordwithFilter,
		"data" => $dataSet
	);
}

if(!empty($page_action)) {
	if($page_action === "fetch_data") {
		$response_data = array();

		$datatableDataSet = getDataTableData(array(
			'searchValue' => $searchValue,
			'columnName' => $columnName,
			'columnSortOrder' => $columnSortOrder,
			'row' => $row,
			'rowperpage' => $rowperpage
		), $colList, $filterVal);

		$response_data = array(
			"draw" => intval($draw),
		  	"recordsTotal" => $datatableDataSet['recordsTotal'],
		  	"recordsFiltered" => $datatableDataSet['recordsFiltered'],
		  	"data" => $datatableDataSet['data']
		);

		echo json_encode($response_data);
	} else if($page_action == "action_handler") {
		$set_id			= strip_tags($_REQUEST['set_id']);
		$set_pid		= strip_tags($_REQUEST['set_pid']);
		$set_uid		= strip_tags($_REQUEST['set_uid']);
		$set_group		= strip_tags($_REQUEST['set_group']);
		$set_status 	= strip_tags($_REQUEST['set_status']);
		$set_action 	= strip_tags($_REQUEST['set_action']);
		$set_update_action 	= strip_tags($_REQUEST['set_update_action']);
		$set_doUpdate 	= strip_tags($_REQUEST['doUpdate']);
		$set_assign_pid 	= strip_tags($_REQUEST['set_assign_pid']);
		$set_username 	= strip_tags($_REQUEST['set_username']);
		$set_note_type 	= strip_tags($_REQUEST['set_note_type']);

		$actionRes = array();
		$msgData = array();

		if(!empty($set_id)) $msgData = sqlQuery("SELECT * FROM `message_log` WHERE `id` = ?", array($set_id));
		$mType = isset($msgData['type']) ? $msgData['type'] : "";

		// Set patient link
		if ($set_id && $set_pid && ($set_action == 'link' || $set_action == 'link_all')) {
			$binds = array();
			$binds[] = $set_pid;
			$binds[] = $set_id;
			
			if(($set_action == 'link' || $set_action == 'link_all')) {
				sqlStatementNoLog("UPDATE `message_log` SET `pid` = ? WHERE `id` = ?", $binds);
			}

			//if($mType == "EMAIL" || $mType == "SMS") {
				linkMultipleMsg($set_id, $set_pid, $set_action, $set_update_action);
			//}
		}

		//Set patient link
		if($set_id && $set_action == 'unlink') {
			sqlStatementNoLog("UPDATE `message_log` SET `pid` = ? WHERE `id` = ?", array(0, $set_id));
		}

		// Change a status
		if ($set_id && $set_action == 'assign') {
			$binds = array();
			$binds[] = ($set_action == 'assign')? $set_uid : '';
			$binds[] = ($set_action == 'assign' && !empty($set_group))? $set_group : '';
			$binds[] = $set_id;
			sqlStatementNoLog("UPDATE `message_log` SET `assigned` = ?, `assign_group` = ? WHERE `id` = ?", $binds);
			$assignNoteId = addPNoteAfterAssing($set_id, $set_assign_pid, $set_uid, $set_username, $set_group, $set_note_type);

    		MessagesLib::after_msg_assign($pid);
		}

		// Change a status
		if ($set_id && $set_action == 'status') {
			$binds = array();
			$binds[] = ($set_status =='inactive')? '0' : '1';
			$binds[] = $set_id;
			sqlStatementNoLog("UPDATE `message_log` SET `activity` = ? WHERE `id` = ?", $binds);
		}

		// Change Internal Notes status
		if ($set_id && $set_action == 'internal_notes_status') {
			$binds = array();
			$binds[] = ($set_status =='inactive')? '0' : '1';
			$binds[] = $set_id;
			sqlStatementNoLog("UPDATE `pnotes` SET `activity` = ?, `message_status` = 'Done' WHERE `id` = ?", $binds);
		}

		//Mark all inactive
		if($set_id && $set_action == 'makeallinactive') {
			EmailMessage::updateStatusOfMsg($set_id, true);
		}

		if(!empty($set_id) && $set_doUpdate == 'true') {
			$datatableDataSet = getDataTableData(array('msg_id' => $set_id), $colList);

			if(!empty($datatableDataSet)) {
				$actionRes['dataset'] = $datatableDataSet;
			}
		}

		echo json_encode($actionRes);
	}

	exit();
}

//Messages function
function linkMultipleMsg($id, $pid, $set_action, $set_update_action = "0") {
	$query = "SELECT * FROM `message_log` WHERE `id` = $id";
	$row = sqlQuery($query);

	$colName = "";

	if($row['direction'] == "in") {
		$colName = 'msg_from';
	}

	if($row['direction'] == "out") {
		$colName = 'msg_to';
	}

	if($set_action == 'link_all') {
		if(!empty($colName) && isset($row[$colName]) && !empty($row[$colName])) {
			$binds = array(
				$pid,
				trim($row[$colName]),
				trim($row[$colName])
			);
			sqlStatementNoLog("UPDATE `message_log` SET `pid` = ? WHERE (`pid` IS NULL OR `pid` = '') AND (( `direction` = 'in' AND `msg_from` = ? ) OR ( `direction` = 'out' AND `msg_to` = ? ))", $binds);
		}
	}

	if($set_update_action == "1") {
		updatePatEmailPhone($pid, $row['type'], $row[$colName]);
	}
}

function updatePatEmailPhone($pid, $type, $value) {
	if(!empty($pid) && !empty($type) && !empty($value)) {
		$patData = sqlQuery("SELECT pid, secondary_email, secondary_phone_cell, email_direct, phone_cell FROM `patient_data` WHERE pid = $pid");
		
		if(!empty($patData)) {
			if($type == "SMS") {
				//Values 
				$newPhoneValue = MessagesLib::getPhoneNumbers($value);
				$phoneVal = MessagesLib::getPhoneNumbers($patData['phone_cell']);
				$secondaryPhoneVal = !empty(trim($patData['secondary_phone_cell'])) ? array_filter(array_map('trim', explode(",",$patData['secondary_phone_cell']))) : array();
				$secondaryPhoneVal = array_map(function($item) { return MessagesLib::getPhoneNumbers($item); }, $secondaryPhoneVal);

				//Raw
				$rnPhoneValue = isset($newPhoneValue['raw_phone']) ? $newPhoneValue['raw_phone'] : "";
				$rPhoneValue = isset($phoneVal['raw_phone']) ? $phoneVal['raw_phone'] : "";
				$rSecondaryPhoneVal =  array_filter(array_map(function($item) { return $item['raw_phone']; }, $secondaryPhoneVal));

				//Update
				if(!in_array($rnPhoneValue, array_merge(array_filter(array($rPhoneValue)),$rSecondaryPhoneVal))) {
					if(empty($rPhoneValue)) {
						sqlStatementNoLog("UPDATE `patient_data` SET `phone_cell` = ? WHERE pid = ? ", array($rnPhoneValue, $pid));
					} else {
						$newSecondaryPhoneVal = !empty($rSecondaryPhoneVal) ? $rSecondaryPhoneVal : array();
						if(!empty($rnPhoneValue)) $newSecondaryPhoneVal[] = $rnPhoneValue;

						sqlStatementNoLog("UPDATE `patient_data` SET `secondary_phone_cell` = ? WHERE pid = ? ", array(implode(",", $newSecondaryPhoneVal), $pid));
					}
				}
			} else if($type == "EMAIL") {
				$emailDirectVal = !empty(trim($patData['email_direct'])) ? trim($patData['email_direct']) : "";
				$secondaryEmailVal = !empty(trim($patData['secondary_email'])) ? array_filter(array_map('trim', explode(",",$patData['secondary_email']))) : array();

				if(!in_array($value, array_merge(array_filter(array($emailDirectVal)),$secondaryEmailVal))) {
					if(empty($emailDirectVal)) {
						sqlStatementNoLog("UPDATE `patient_data` SET `email_direct` = ? WHERE pid = ? ", array($value, $pid));
					} else {
						$newSecondaryEmailVal = !empty($secondaryEmailVal) ? $secondaryEmailVal : array();
						if(!empty($value)) $newSecondaryEmailVal[] = $value;

						sqlStatementNoLog("UPDATE `patient_data` SET `secondary_email` = ? WHERE pid = ? ",array(implode(",", $newSecondaryEmailVal), $pid));
					}
				}
			}
		}	
	}
}

function addPNoteAfterAssing($set_id, $set_assign_pid, $set_uid, $set_username, $set_group, $set_note_type) {
	$query = "SELECT * FROM `message_log` WHERE `id` = $set_id";
	$row = sqlQuery($query);

	$type = '[UNKNOWN]';
	if ($row['type'] == 'SMS') $type = 'SMS';
	if ($row['type'] == 'PHONE') $type = 'Phone';
	if ($row['type'] == 'PORTAL') $type = 'Portal';
	if ($row['type'] == 'EMAIL') $type = 'Email';
	if ($row['type'] == 'FAX') $type = 'Fax';
	if ($row['type'] == 'P_LETTER') $type = 'Postal Letter';
	if ($row['type'] == 'NOTES') $type = 'Internal Notes';

	$msg_note_receiver_name = !empty($row['receivers_name']) ? "(".$row['receivers_name'].")" : "";
	$msg_note_from = !empty($row['msg_from']) ? nl2br($row['msg_from']) : "";

	$note_msg = "The following message has been assigned to you in the Message Board\nMessage Type: $type\nReceived From: $msg_note_from $msg_note_receiver_name";

	$note = preg_replace('#<div class="attachmentContainer">(.*?)</div>#', '', $row['message']);
	$note = str_replace("<br />", "\n", $note);
	$note = strip_tags($note);

	$note = "\n$note_msg \n\n" . $note;

	$assigned_to = $set_username;
	if(isset($set_group) && !empty($set_group)) {
		$assigned_to = 'GRP:'.$set_group;
	}

	if(!empty($assigned_to) && !empty($set_note_type)) {
		return addPnote($set_assign_pid, $note, '1', '1', $set_note_type, $assigned_to, '', "New");
	}

	return false;
}

?>
<html>
<head>
    <title><?php echo xlt('Patient Message Board'); ?></title>
    <link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
	<?php Header::setupHeader(['opener', 'dialog', 'jquery', 'jquery-ui', 'datatables', 'datatables-colreorder', 'datatables-dt', 'datatables-bs', 'datetime-picker', 'oemr_ad']); ?>

	<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/interface/main/messages/js/messages.js"></script>

	<style type="text/css">
		table.table td.no-padding {
			padding: 0px !important;
		}

		.row-details-table.table tr:first-child td{
			border-top: 1px solid #fff!important;
		}

		/*Dropdown*/
		.actiondp .dropbtn {
		    /*background-color: #4CAF50;
		    color: white;
		    padding: 16px;
		    font-size: 16px;
		    border: none;
		    cursor: pointer;*/
		    margin: 0px!important;
		}

		.actiondp .dropbtn:hover, .actiondp .dropbtn:focus {
		    /*background-color: #3e8e41;*/
		}

		.actiondp.dropdown {
		    position: relative;
		    display: inline-block;
		}

		.actiondp.dropdown .labelContainer {
			width: 100%;
		}

		.actiondp .dropdown-content {
		    display: none;
		    position: absolute;
		    background-color: #f9f9f9;
		    min-width: 160px;
		    overflow: auto;
		    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
		    position: absolute;
		    right: 0;
		    top: 26px;
    		border: 1px solid #dddddd;
    		background-color: #fff;
    		z-index: 1;
		}

		.actiondp .dropdown-content a {
		    color: black;
		    padding: 8px 16px;
		    text-decoration: none;
		    display: block;
		    border-bottom: 1px solid #dddddd;
		}

		.actiondp .dropdown-content button {
			width: 100%;
		    background: transparent;
		    color: #000 !important;
		    text-align: left;
		    padding: 8px 16px;
		    border: 0px;
		    margin: 0px;
		    cursor: pointer;
		}

		.actiondp .dropdown-content a:last-child,
		.actiondp .dropdown-content button:last-child {
			border-bottom: 0px solid #fff;
		}

		.actiondp.dropdown a:hover, .actiondp.dropdown-content button:hover {background-color: #f1f1f1}

		.actiondp .show {display:block;}
	</style>

	<script type="text/javascript">
		function resizeIframe(id, action = 0) {
			if(id) {
				var iframeEle = document.querySelector("#messagebox_"+id+" iframe");
				if(iframeEle && iframeEle != null) {
					if(action == 0) {
						iframeEle.style.height = (iframeEle.contentWindow.document.documentElement.scrollHeight + 15) + "px";
						iframeEle.scrolling="yes";
					} else {
						var newHeight = iframeEle.contentWindow.document.documentElement.scrollHeight;
						if(newHeight > 85) {
							newHeight = 85;
						}
			    		iframeEle.style.height = newHeight + 'px';
			    		iframeEle.scrolling="no";
					}
				}
			}
		}

		function handleReadMore(obj, id) {
			if (obj.checked == true){
				resizeIframe(id, 0);
			} else {
				resizeIframe(id, 1);
			}
		}

		function checkReadMoreAction(obj, id) {
			var eleHeight = obj.contentWindow.document.documentElement.scrollHeight;
			if(eleHeight > 85 && id != "") {
				document.getElementById("messagebox_"+id).classList.remove("hidebox");
			}
		}

		function handleIframe(obj, id) {
			var iframeEle = document.querySelector("#messagebox_"+id+" iframe");

			var newHeight = obj.contentWindow.document.documentElement.scrollHeight;
			if(newHeight > 85) {
				newHeight = 85;
			}

   			obj.style.height = newHeight + 'px';
   			checkReadMoreAction(obj, id);

   			document.querySelector("#messagebox_"+id+" .innerBox").classList.remove("hidemsgbox");
   			document.querySelector("#messagebox_"+id+" .loadingBox").classList.add("hideContainer");
  		}

		$(document).ready(function(){
			$('.date_field').datetimepicker({
	      		<?php $datetimepicker_timepicker = false; ?>
	      		<?php $datetimepicker_showseconds = false; ?>
	      		<?php $datetimepicker_formatInput = true; ?>
	    		<?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
			});
		});

		/* When the user clicks on the button, 
		toggle between hiding and showing the dropdown content */
		function dpToggle(e) {
			$(e).parent().parent().find('.dpContainer').toggleClass("show");
		    //document.getElementById("myDropdown").classList.toggle("show");
		}

		// Close the dropdown if the user clicks outside of it
		window.onclick = function(event) {
		  if (!event.target.matches('.dropbtn')) {
		    var dropdowns = document.getElementsByClassName("dropdown-content");
		    var i;
		    for (i = 0; i < dropdowns.length; i++) {
		      var openDropdown = dropdowns[i];
		      if (openDropdown.classList.contains('show')) {
		        openDropdown.classList.remove('show');
		      }
		    }
		  }
		}
	</script>
</head>
<body>
	<div class="container-fluid">
	<div class="page-title">
	    <h2><?php echo xlt('Patient Message Board'); ?></h2>
	</div>

	<div class="dataTables_wrapper datatable_filter mb-4">
		<form id="page_report_filter">
			<div>
				<div class="row">
  					<div class="col-3">
				      <div class="form-group">
					    <label><?php xl('Type','e') ?></label>
					    <select name="form_type" multiple class="form-control form-control-sm" style="min-height: 110px;">
							<option value='all' selected><?php xl('All Message Types','e') ?></option>
							<option value='email'><?php xl('Email Messages','e') ?></option>
							<option value='sms'><?php xl('SMS/Text Messages','e') ?></option>
							<option value='fax'><?php xl('Fax Messages','e') ?></option>
							<option value='p_letter'><?php xl('Postal Letters','e') ?></option>
						</select>
					  </div>
				    </div>

  					<div class="col-9">
  						<div class="row">
						    <div class="col-4">
						      <div class="form-group">
							    <label><?php xl('Direction','e') ?></label>
							    <select name="form_direction" class="form-control form-control-sm">
									<option value='all' selected><?php xl('Any Direction','e') ?></option>
									<option value='in'><?php xl('Inbound','e') ?></option>
									<option value='out'><?php xl('Outbound','e') ?></option>
								</select>
							  </div>
						    </div>
						    <div class="col-4">
						      <div class="form-group">
							    <label><?php xl('Status','e') ?></label>
							    <select name="form_status" class="form-control form-control-sm">
									<option value='all'><?php echo xl('Any Message Status','e') ?></option>
									<option value='active' selected><?php xl('Active Messages','e') ?></option>
									<option value='inactive'><?php echo xl('Inactive Messages','e') ?></option>
								</select>
							  </div>
						    </div>
						    <div class="col-4">
						      <div class="form-group">
							    <label><?php xl('Patient','e') ?></label>
							    <select name="form_linked" class="form-control form-control-sm">
									<option value='all' selected><?php echo xl('All Link Statuses','e') ?></option>
									<option value='linked'><?php xl('Patient Identified','e') ?></option>
									<option value='unknown'><?php echo xl('Patient Unknown','e') ?></option>
								</select>
							  </div>
						    </div>
		  				</div>

		  				<div class="row">
		  					<div class="col-4">
		  						<div class="form-group">
								    <label><?php xl('Assignment','e') ?></label>
								    <select name="form_assigned" class="form-control form-control-sm">
										<option value='all'><?php xl('Any Assignment','e') ?></option>
										<option value='assigned'><?php  xl('Assigned Messages','e') ?></option>
										<option value='unassigned'><?php xl('Unassigned Messages','e') ?></option>;
										<?php
											// Build a drop-down list of assigned users.
											$query = "SELECT DISTINCT u.`id` AS `assigned`, "; 
											$query .= "CONCAT(IFNULL(SUBSTR(u.`fname`,1,1),''), '. ', u.`lname`) AS 'user_name' ";
											$query .= "FROM `users` u WHERE ";
											$query .= "u.`username` IS NOT NULL AND u.`username` != '' AND u.`active` = 1";
											$ares = sqlStatement($query);

											while ($arow = sqlFetchArray($ares)) {
												$assigned = $arow['assigned'];
												echo "<option value='$assigned'";
												echo ">" . $arow['user_name'] . "\n";
											}
										?>
									</select>
								</div>
		  					</div>
		  					<div class="col-4">
		  						<div class="form-group">
								    <label><?php xl('Phone Number Or Email Address From','e') ?></label>
								    <input type="text" name="search_phone_email" class="form-control form-control-sm" value="">
								</div>
		  					</div>
		  				</div>
  					</div>
  				</div>

  				<div class="row">
  					<div class="col-12">
  						<button type="submit" id="filter_submit" class="btn btn-primary btn-sm"><?php xl('Submit','e'); ?></button>
						<button type="button" class="btn btn-secondary btn-sm" onclick="doCallInternalNotes()"><?php xl('Create Message','e'); ?></button>
					</div>
				</div>
  			</div>
			
		</form>
	</div>
	<div id="page_report_container" class="datatable_container table-responsive">
		<table id='page_report' class="text table table-sm msg-table tableRowHighLight" style="width:100%">
		  <thead>
		    <tr>
		      <?php
		      	foreach ($columnList as $clk => $cItem) {
		      		if($cItem["name"] == "dt_control") {
		      		?> <th><div class="dt-control text"></div></th> <?php
		      		} else {
		      		?> <th><?php echo $cItem["title"] ?></th> <?php
		      		}
		      	}
		      ?>
		    </tr>
		  </thead>
		</table>
	</div>
	</div>

<script type='text/javascript'>
	<?php include($GLOBALS['srcdir'].'/wmt-v2/report_tools.inc.js'); ?>

	function setPatientData(pid, pubpid, pname, dobstr) {
		//parent.left_nav.setPatient(pname, pid, pubpid, '',dobstr);
		goParentPid(pid);
	}
</script>

<script type="text/javascript">

	var dataTable = "";
	var colummsData = [];

	function format(d, columnList = []) {
		var defaultVal = '<i class="defaultValueText">Empty</i>';
		var msg_id_val = decodeHtmlString(d.id);
		var to_val = decodeHtmlString(d.to);
		var to_list_val = decodeHtmlString(d.to_list);
		var cc_list_val = decodeHtmlString(d.cc_list);
		var from_val = decodeHtmlString(d.from);
		var msg_status_dec_val = decodeHtmlString(d.msg_status_dec);
		var msg_content_val = decodeHtmlString(d.msg_content);
		var msg_attach_val = decodeHtmlString(d.msg_attach);
		var email_subject_val = decodeHtmlString(d.email_subject);
		var msg_action_val = decodeHtmlString(d.msg_action);

		var attachHTML = (msg_attach_val != "") ? '<tr>'+'<td colspan="6">'+'<div>'+(msg_attach_val != "" ? msg_attach_val : defaultVal) +'</div>'+'</td>'+'</tr>' : "";

		var subjectSec = (email_subject_val != "") ? '<tr>'+'<td colspan="6">'+'<div>Subject: '+(email_subject_val != "" ? email_subject_val : defaultVal) +'</div>'+'</td>'+'</tr>' : "";

		var sec1 = (cc_list_val != "") ? '<tr>'+'<td colspan="6">'+'<div>Cc: '+(cc_list_val != "" ? cc_list_val : defaultVal) +'</div>'+'</td>'+'</tr>' : "";

		return '<div class="text"><table class="table text row-details-table mb-0"><tbody>'+
					'<tr>'+
						'<td width="10">'+
							'<span><?php echo xla('To'); ?>:</span>'+
						'</td>'+
						'<td width="350">'+
							'<div>'+(to_list_val != "" ? to_list_val : defaultVal)+'</div>'+
						'</td>'+
						'<td width="25">'+
							'<span><?php echo xla('From'); ?>:</span>'+
						'</td>'+
						'<td width="350">'+
							'<div>'+(from_val != "" ? from_val : defaultVal) +'</div>'+
						'</td>'+
						'<td width="120">'+
							'<span><?php echo xla('Status Description'); ?>:</span>'+
						'</td>'+
						'<td>'+
							'<div><span class="breakText"><i>'+(msg_status_dec_val != "" ? msg_status_dec_val : defaultVal) +'</i></span></div>'+
						'</td>'+
					'</tr>'+
					sec1+
					subjectSec+
					'<tr>'+
						'<td colspan="6">'+ msg_content_val +'</td>'+
					'</tr>'+
					'<tr>'+
						'<td colspan="6">'+
							msg_action_val+
						'</td>'+
					'</tr>'+
			   '</tbody></table></div>';
	}

	function initDataTable(id, ajax_url = '', data = {}, columnList = []) {
		colummsData = JSON.parse(columnList);
		var columns = []; 
		colummsData.forEach((item, index) => {
			if(item["name"]) {
				var item_data = item["data"] ? item["data"] : {};

				if(item["name"] == "dt_control") { 
					columns.push({ 
						"data" : "",
						...item_data
					});
				} else {
					columns.push({ 
						"data" : item["name"],
						...item_data,
						"render" : function(data, type, row ) {
							var defaultVal = item_data['defaultValue'] ? decodeHtmlString(item_data['defaultValue']) : "";
							var colValue = decodeHtmlString(data);

							return (colValue && colValue != "") ? colValue : defaultVal;
						} 
					});
				}
			}
		});

		data["columnList"] = colummsData;

		if(id && id != "" && ajax_url != '' && data) {
			var dTable = $(id).DataTable({
					"processing": true,
			       	"serverSide": true,
			         "ajax":{
			             url: ajax_url, // json datasource
			             data: function(adata) {

			             		for (let key in data) {
			             			adata[key] = data[key];
			             		}

			             		//Append Filter Value
			             		adata['filterVal'] = getFilterValues(id + "_filter");
			             },
			             type: "POST",   // connection method (default: GET)
			             
			        },
			        "initComplete": function(settings){
		                let rowD = $(this).rowDetails({
		                    format : format,
		                    api: this.api()
		                });

		                rowD.expandAllRow(true);
		            },
		            "drawCallback": function (settings) {
		            	setTimeout(function(){
				    		//$(id).find('.contentiFrame').iframereadmoretext();
				    	}, 0);
		            },
			        "columns": columns,
			        "columnDefs": [
				        { 
				        	"targets": '_all', 
				        	"render" : function ( data, type, row ) {
				        		return data;
			                },
			                
				        },
				    ],
				    "autoWidth": false,
			        "searching" : false,
			        "order": [[ 1, "desc" ]],
			        "iDisplayLength" : 10,
			        
			});

			$(id).on( 'processing.dt', function ( e, settings, processing ) {
				if(processing === true) {
					$('#filter_submit').prop('disabled', true);
				} else if(processing === false) {
					$('#filter_submit').prop('disabled', false);
				}
			});

			return dTable;
		}

		return false;
	}

	function getFilterValues(id = '') {
		var form_val_array = {};

		if(id != '') {
			var unindexed_array = $(id).serializeArray();
			var indexed_array = {};
		    $.map(unindexed_array, function(n, i){
		        if((n['name'] == "form_type") && n['value'] != "") {
		        	if(!indexed_array[n['name']]) {
		        		indexed_array[n['name']] = [];
		        	}

		        	indexed_array[n['name']].push(n['value']);
		        } else {
		        	indexed_array[n['name']] = n['value'];
		    	}
		    });

		    $.map(indexed_array, function(ni, ii){
		    	if(ni != "") {
		    		form_val_array[ii] = ni;
		    	}
		    });
		}

		return form_val_array;
	}

	function validateForm() {
		return true;
	}

	$(function () {
		var dataTableId = "#page_report";
		var dataTableFilterId = "#page_report_filter";

		//$('#filter_submit').prop('disabled', true);
		dataTable = initDataTable(
			dataTableId, 
			'message_tracker_new.php', 
			{ action: 'fetch_data' },
			'<?php echo json_encode($columnList); ?>'
		);

		$(dataTableFilterId).submit(function(e){
            e.preventDefault();

            var vStatus = validateForm();

            if(vStatus === true) {
            	dataTable.draw();
        	}
        });

        setTimeout(function(){
			$(dataTableFilterId).submit();
		}, 500000);
	});

	var tmp_set_id = null;
	var tmp_set_ele = null;
	var tmp_set_assign_pid = null;
	var tmp_set_pid = null;
	var tmp_set_mtype = null;

	function actionType(type, e, id, mType = '') {
		tmp_set_ele = e;
		tmp_set_mtype = mType;

		if(type == "dolink") {
			findPatient(id);
		} else if(type == "dounlink") {
			doUnlink(id);
		} else if(type == "doassign") {
			var pid = $(e).data('pid');
			doAssign('assign', id, pid);
		} else if(type == "dounassign") {
			var pid = $(e).data('pid');
			doAssign('release', id, pid);
		} else if(type == "donoteinactive") {
			doInternalNotesStatus('inactive', id);
		} else if(type == "donoteactive") {
			doInternalNotesStatus('active', id);
		} else if(type == "doinactive") {
			doStatus('inactive', id);
		} else if(type == "doactive") {
			doStatus('active', id);
		} else if(type == "doreplyemail") {
			var pid = $(e).data('pid');
			replyButton("EMAIL", pid, id);
		} else if(type == "doreplysms") {
			var pid = $(e).data('pid');
			replyButton("SMS", pid, id);
		} else if(type == "doopen") {
			var msg_type = $(e).data('type');
			var pid = $(e).data('pid');
			loadMessage(msg_type, id, pid);
		} else if (type == "doresend") {
			var msg_type = $(e).data('type');
			var pid = $(e).data('pid');
			resendButton(e, msg_type, id);
		}
	}

	function setAllRowData(e) {
		if(e == undefined || e == null) {
			return false;
		}

		var row = $(e).closest('tr');
		dataTable.row(row).draw(false);
	}

	function setRowData(e, nData) {
		if(e == undefined || e == null) {
			return false;
		}

		var row = $(e).closest('tr');
		var currDataRow = dataTable.row(row).data();

		if(colummsData.length > 0 && nData) {
			$.each(colummsData, function(colI, colItem) {
				var colName = colItem.hasOwnProperty('name') ? colItem['name'] : '';
				if(nData[colName] != undefined) {
					currDataRow[colName] = nData[colName];
				}
			});
		}
	
		//dataTable.row(row).data(currDataRow).draw();
		//dataTable.row(row).data(currDataRow).draw(false);
		dataTable.row(row).data(currDataRow);

		//Set Child row details
		var childTrClass = row.hasClass('even') ? 'even' : 'odd';
		trow = dataTable.row(row);
		trow.child(format(trow.data()), 'no-padding row-details-tr '+childTrClass);
	}

	async function actionHandleCall(data, doUpdate = false, doUpdateAll = false) {
		var res = await $.ajax({
		    url: 'message_tracker_new.php',
		    type: 'POST',
		    data: { 'columnList' : colummsData, ...data, 'doUpdate' : doUpdate }
		});

		//Parse JSON Data.
		if(res != undefined) {
			res = JSON.parse(res);
		}

		if(doUpdateAll === true) {
			setAllRowData(tmp_set_ele);
		}

		if(doUpdate === true) {
			var dataSet = res.hasOwnProperty('dataset') ? res['dataset'] : {}; 
			setRowData(tmp_set_ele, dataSet);
		}

		return res;
	}

	function doCallInternalNotes() {
		openInternalNotesPopup('?pid=<?php echo $pid ?>');
	}

	function resendButton(e, type, messageId) {
		resendMsgPopup(type, '<?php echo $pid ?>', messageId);
	}

	function replyButton(type, pid, messageId) {
		tmp_set_id = messageId; // store for later
		replyMsgPopup(type, pid, messageId);
	}

	async function doRefresh(paging) {
		if(paging == "reply") {
			$getId = tmp_set_id;
			$count = 0;
			$direction = $("#replyinput"+$getId).data('direction');
			$msg_to = $("#replyinput"+$getId).data('msg_to');
			$msg_from = $("#replyinput"+$getId).data('msg_from');
			var set_action = '';

			$responce = await fetchLinkCount($direction, $msg_from, $msg_to, $getId, "similer_active_records");

			if($responce && $responce['count']) {
				$count = $responce['count'];
			}

			if($count > 0) {
				var confirmRes = confirm("Do you want to inactive "+$count+" messages which are from the similar (email or phone)?");
				if(confirmRes == true) {
					set_action = 'makeallinactive';
				}
			}

			var aResponce = await actionHandleCall({
				'action' : 'action_handler',
				'set_id' : $getId,
				'set_action' : set_action
			}, false, true);
		} else if(paging == "resend") {
			setAllRowData(tmp_set_ele);
		}
	}

	async function doStatus(status, id) {
		var aResponce = await actionHandleCall({
			'action' : 'action_handler',
			'set_id' : id,
			'set_status' : status,
			'set_action' : 'status'
		}, true);
	}

	async function doInternalNotesStatus(status, id) {
		var aResponce = await actionHandleCall({
			'action' : 'action_handler',
			'set_id' : id,
			'set_status' : status,
			'set_action' : 'internal_notes_status'
		}, true);
	}

	function doAssign(assign, id, pid) {
		tmp_set_id = id; // store for later
		tmp_set_assign_pid = pid;

		if (assign == 'release') {
			setuser(0,'','','');
		} else {
			dlgopen('../main/calendar/find_user_popup.php?page_action=assign', '_blank', 500, 400);
		}
	}

	// This is for callback by the find-user popup.
	async function setuser(uid, uname, username, status, noteType = '') {
		var t_grp_val = '';
		if (uid == 0) t_grp_val = username;

		var aResponce = await actionHandleCall({
			'action' : 'action_handler',
			'set_id' : tmp_set_id,
			'set_assign_pid' : tmp_set_assign_pid,
			'set_note_type' : noteType,
			'set_uid' : uid,
			'set_group' : t_grp_val,
			'set_username' : username,
			'set_action' : 'assign',
		}, true);
	}

	// This invokes do unlink
	async function doUnlink(id) {
		var aResponce = await actionHandleCall({
			'action' : 'action_handler',
			'set_id' : id,
			'set_action' : 'unlink',
		}, true);
	}

	// This invokes the find-patient popup.
	async function findPatient(set_id) {
		tmp_set_id = set_id; // store for later
		dlgopen('../main/calendar/find_patient_popup.php', '_blank', 800, 400);
	}

	// This is for callback by the find-patient popup.
	function setpatient(pid, lname, fname, dob) {
		setTimeout(async function(){
			$getId = tmp_set_id;
			$count = 0;
			$direction = $("#linkinput"+$getId).data('direction');
			$msg_to = $("#linkinput"+$getId).data('msg_to');
			$msg_from = $("#linkinput"+$getId).data('msg_from');

			$updateAction = "0";
			$actionType = "link";
			var doUpdateAll = false;
			var doUpdate = true;

			var mStatusType = '';
			if(tmp_set_mtype == 'EMAIL') mStatusType = 'email address';
			if(tmp_set_mtype == 'SMS') mStatusType = 'phone number';
			if(tmp_set_mtype == 'FAX') mStatusType = 'fax number';
			if(tmp_set_mtype == 'P_LETTER') mStatusType = 'postal letter address';

			if(tmp_set_mtype == 'EMAIL' || tmp_set_mtype == 'SMS') {
				var uConfirmRes = confirm("Press \"Ok\" to PERMANENTLY add this "+mStatusType+" to patient's chart; otherwise, press \"Cancel\"");
				if(uConfirmRes == true) {
					$updateAction = "1";
				}
			}

			$responce = await fetchLinkCount($direction, $msg_from, $msg_to, $getId, "similer_records");
			if($responce && $responce['count']) {
				$count = $responce['count'];
			}
				
			if($count > 0) {
				var confirmRes = confirm("Do you want to link other "+$count+" records with the similar "+mStatusType+"?");
				if(confirmRes == true) {
					$actionType = "link_all"
				}
			}

			if($actionType == "link_all") {
				doUpdateAll = true;
				doUpdate = false;
			}

			var aResponce = await actionHandleCall({
				'action' : 'action_handler',
				'set_id' : tmp_set_id,
				'set_pid' : pid,
				'set_action' : $actionType,
				'set_update_action' : $updateAction
			}, doUpdate, doUpdateAll);
		}, 100);
	}
</script>

</body>
</html>