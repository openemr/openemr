<?php

namespace OpenEMR\OemrAd;

@include_once(__DIR__ . "/../interface/globals.php");
@include_once($GLOBALS['srcdir']."/patient.inc");
@include_once($GLOBALS['srcdir']."/wmt-v2/wmtstandard.inc");
@include_once($GLOBALS['srcdir']."/wmt-v3/wmt.globals.php");
@include_once($GLOBALS['srcdir']."/OemrAD/oemrad.globals.php");

use Mpdf\Mpdf;
use OpenEMR\OemrAd\Attachment;

/**
 * Messages Class
 */
class MessagesLib {
	
	function __construct(){
	}

	public static function add_files_list() {
		global $new_noteid;

		$filesDocList = $_REQUEST['filesDocList'] && !empty($_REQUEST['filesDocList']) ? json_decode($_REQUEST['filesDocList'], true) : array();
		self::saveGPrelations($new_noteid, $filesDocList);
	}

	public static function update_files_list() {
		global $noteid;

		$filesDocList = $_REQUEST['filesDocList'] && !empty($_REQUEST['filesDocList']) ? json_decode($_REQUEST['filesDocList'], true) : array();
		self::saveGPrelations($noteid, $filesDocList);
	}

	public static function saveGPrelations($note_id, $filesDocList) {
		if(!empty($note_id)) {
			$bindArrayList = array();
			foreach ($filesDocList as $key => $item) {
				if($key == "encounters") {
					foreach ($item as $ei => $encounterItem) {
						$bindArray = array(100, $encounterItem['id'], 6, $note_id);
						$bindArrayList[] = $bindArray;
					}
				} else if($key == "documents"){
					foreach ($item as $di => $docItem) {
						$bindArray = array(101, $docItem['id'], 6, $note_id);
						$bindArrayList[] = $bindArray;
					}
				} else if($key == "messages"){
					foreach ($item as $di => $msgItem) {
						$bindArray = array(102, $msgItem['id'], 6, $note_id);
						$bindArrayList[] = $bindArray;
					}
				} else if($key == "orders"){
					foreach ($item as $di => $msgItem) {
						$bindArray = array(103, $msgItem['id'], 6, $note_id);
						$bindArrayList[] = $bindArray;
					}
				}
			}

			foreach ($bindArrayList as $bi => $bItem) {
				if(!empty($bItem)) {
					sqlInsert("INSERT INTO `gprelations` ( type1, id1, type2, id2 ) VALUES (?, ?, ?, ?) ", $bItem);
				}
			}
		}
	}

	public static function internal_message() {
		?>
		<div class="ext_button_container">
			<textarea id="filesDocList" name="filesDocList" style="display: none;"></textarea>
			<button type="button" class="btn btn-primary" id="encounterSelect" value="<?php echo xla('Encounter'); ?>" onClick="messagelib.handleSelectEncounters()">
				<i class="fa-regular fa-file-lines"></i>
				<?php echo xla('Encounter'); ?>
			</button>
			<button type="button" class="btn btn-primary" id="documentsSelect" value="<?php echo xla('Documents'); ?>" onClick="messagelib.handleDocuments()">
				<i class="fa-regular fa-file-lines"></i>
				<?php echo xla('Documents'); ?>
			</button>
			<button type="button" class="btn btn-primary" id="messagesSelect" value="<?php echo xla('Messages'); ?>" onClick="messagelib.handleMessages({ 'assigned_to' : '<?php echo $_SESSION['authUser'] ?>' })">
				<i class="fa-regular fa-envelope"></i>
				<?php echo xla('Messages'); ?>
			</button>
			<button type="button" class="btn btn-primary" id="ordersSelect" value="<?php echo xla('Orders'); ?>" onClick="messagelib.handleOrders()">
				<i class="fa-regular fa-file-lines"></i>
				<?php echo xla('Orders'); ?>
			</button>
		</div>
		<?php
	}


	public static function item_list_container() {
		?>
		<!-- <div class="containerFile">
			<div class="files" id="filesDoc">
				<div>
					<ul class="fileList">
					</ul>
				</div>
			</div>
		</div> -->
		<?php
	}

	public static function linked_doc_list() {
		global $noteid;

		$intData = self::getInternalNote($noteid);
		if(!empty($intData)) {
			$orderData = self::fetchOrderById($intData['rto_id']);
        	$clickFun = "handleGoToOrder('".$intData['rto_id']."', '".$orderData['pid']."')";

			echo " <tr>\n";
            echo "  <td class='text'><br/><b>";
            echo xlt('Order reference') . ":</b><br/>\n";
            echo '<a href="javascript:void(0)" onclick="'.$clickFun.'">';
            echo text($orderData['link_title']);
            echo "</a><br/>\n";
            echo "  </td>\n";
            echo " </tr>\n";
		}

		// Get the related procedure order IDs if any.
        $tmp = sqlStatement(
            "SELECT id1 FROM gprelations WHERE " .
            "type1 = ? AND type2 = ? AND id2 = ?",
            array('104', '6', $noteid)
        );
        if (sqlNumRows($tmp)) {
            echo " <tr>\n";
            echo "  <td class='text'><br/><b>";
            echo xlt('Message reference') . ":</b><br/>\n";
            while ($gprow = sqlFetchArray($tmp)) {
            	$msgData = self::fetchMsgById($gprow['id1']);
            	if(!empty($msgData)) {
            		$msgType = $msgData['type'] == "SMS" ? $msgData['type'] . "_REPLY" : $msgData['type'];
	            	$clickFun = "loadMessage('".$msgType."', '".$msgData['id']."', '".$msgData['pid']."')";
	                echo '<a href="javascript:void(0)" onclick="'.$clickFun.'">';
	                echo text($msgData['link_title']);
	                echo "</a><br/>\n";
            	}		
            }
            echo "  </td>\n";
            echo " </tr>\n";
        }

		// Get the related procedure order IDs if any.
        $tmp = sqlStatement(
            "SELECT id1 FROM gprelations WHERE " .
            "type1 = ? AND type2 = ? AND id2 = ?",
            array('100', '6', $noteid)
        );
        if (sqlNumRows($tmp)) {
            echo " <tr>\n";
            echo "  <td class='text'><br/><b>";
            echo xlt('Linked encounter') . ":</b><br/>\n";
            while ($gprow = sqlFetchArray($tmp)) {
            	$encounterData = self::getEncounterData($gprow['id1']);
            	if(!empty($encounterData)) {
	            	$clickFun = "handleGoToEncounter('".$encounterData['pid']."', '".$encounterData['pubpid']."', '".$encounterData['patient_name']."', '".$encounterData['encounter']."', '".$encounterData['patient_DOB']."')";

	                echo '<a href="javascript:void(0)" onclick="'.$clickFun.'">';
	                echo text($encounterData['titleLink']);
	                echo "</a><br/>\n";
            	}		
            }
            echo "  </td>\n";
            echo " </tr>\n";
        }

        // Get the related procedure order IDs if any.
        $tmp = sqlStatement(
            "SELECT id1 FROM gprelations WHERE " .
            "type1 = ? AND type2 = ? AND id2 = ?",
            array('101', '6', $noteid)
        );
        if (sqlNumRows($tmp)) {
            echo " <tr>\n";
            echo "  <td class='text'><br/><b>";
            echo xlt('Linked documents') . ":</b><br/>\n";
            while ($gprow = sqlFetchArray($tmp)) {
            	$docData = self::getDocumentData($gprow['id1']);
            	if(!empty($docData)) {
	            	$clickFun = "handlegotoReport('".$docData['id']."', '".$docData['pid']."')";
	                echo '<a href="javascript:void(0)" onclick="'.$clickFun.'">';
	                echo text($docData['baseName']);
	                echo "</a><br/>\n";
            	}
            }
            echo "  </td>\n";
            echo " </tr>\n";
        }

        // Get the related procedure order IDs if any.
        $tmp = sqlStatement(
            "SELECT id1 FROM gprelations WHERE " .
            "type1 = ? AND type2 = ? AND id2 = ?",
            array('102', '6', $noteid)
        );
        if (sqlNumRows($tmp)) {
            echo " <tr>\n";
            echo "  <td class='text'><br/><b>";
            echo xlt('Linked messages') . ":</b><br/>\n";
            while ($gprow = sqlFetchArray($tmp)) {
            	$msgData = self::fetchMessageById($gprow['id1']);
            	if(!empty($msgData)) {
	            	$clickFun = "handleGoToMessage('".$msgData['id']."', '".$msgData['pid']."')";
	                echo '<a href="javascript:void(0)" onclick="'.$clickFun.'">';
	                echo text($msgData['link_title']);
	                echo "</a><br/>\n";
            	}
            }
            echo "  </td>\n";
            echo " </tr>\n";
        }

        // Get the related procedure order IDs if any.
        $tmp = sqlStatement(
            "SELECT id1 FROM gprelations WHERE " .
            "type1 = ? AND type2 = ? AND id2 = ?",
            array('103', '6', $noteid)
        );

        if (sqlNumRows($tmp)) {
            echo " <tr>\n";
            echo "  <td class='text'><br/><b>";
            echo xlt('Linked orders') . ":</b><br/>\n";
            while ($gprow = sqlFetchArray($tmp)) {
            	$orderData = self::fetchOrderById($gprow['id1']);
            	if(!empty($orderData)) {
	            	$clickFun = "handleGoToOrder('".$orderData['id']."', '".$orderData['pid']."')";
	                echo '<a href="javascript:void(0)" onclick="'.$clickFun.'">';
	                echo text($orderData['link_title']);
	                echo "</a><br/>\n";
            	}
            }
            echo "  </td>\n";
            echo " </tr>\n";
        }
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

	public static function getDocumentData($docId) {
		$queryarr = array($docId);
	    $query = "SELECT d.id, d.type, d.size, d.url, d.docdate, d.list_id, d.encounter_id, d.foreign_id, c.name " .
	    "FROM documents AS d, categories_to_documents AS cd, categories AS c WHERE " .
	    "d.id = ? AND cd.document_id = d.id AND c.id = cd.category_id ";

	    $query .= "ORDER BY d.docdate DESC, d.id DESC";
	    $dres = sqlQuery($query, $queryarr);

	    if(!empty($dres)) {
		    $dres['baseName'] = basename($dres['url']) . ' (' . xl_document_category($dres['name']). ')';
		    $dres['baseFileName'] = ' (' . xl_document_category($dres['name']). ')' . basename($dres['url']);
		    $dres['issue'] = self::getAssociatedIssue($dres['list_id']);

		    $patient_id = $dres['foreign_id'];
			$patientData = getPatientData($patient_id, "fname, mname, lname, pubpid, billing_note, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");


			$patientName = addslashes(htmlspecialchars($patientData['fname'] . ' ' . $patientData['lname']));

		    $patientDOB = xl('DOB') . ': ' . addslashes(oeFormatShortDate($patientData['DOB_YMD'])) . ' ' . xl('Age') . ': ' . getPatientAge($patientData['DOB_YMD']);

		    $dres['patient_name'] = $patientName;
		    $dres['patient_DOB'] = $patientDOB;
		    $dres['pubpid'] = $patientData['pubpid'];
		    $dres['pid'] = $patient_id;

		    return $dres;
	    }
	    return false;
	}

	public static function getEncounterData($encounterId) {
		$rowresult4 = sqlQuery("SELECT fe.encounter, fe.pid, fe.date,openemr_postcalendar_categories.pc_catname, us.fname, us.mname, us.lname FROM form_encounter AS fe left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid left join users AS us on fe.	provider_id = us.id  WHERE fe.encounter = ? order by fe.date desc", array($encounterId));

		if(!empty($rowresult4)) {
			$encounter = isset($rowresult4['encounter']) ? $rowresult4['encounter'] : '';
			
			$patient_id = $rowresult4['pid'];
			$patientData = getPatientData($patient_id, "fname, mname, lname, pubpid, billing_note, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");

			$eData = self::fetch_appt_signatures_data_byId($encounter);
		    if($eData !== false && isset($eData['is_lock']) && $eData['is_lock'] == '1') {
		        $rowresult4['signed'] = true;
		    } else {
		    	$rowresult4['signed'] = false;
		    }

		    $patientName = addslashes(htmlspecialchars($patientData['fname'] . ' ' . $patientData['lname']));

		    $patientDOB = xl('DOB') . ': ' . addslashes(oeFormatShortDate($patientData['DOB_YMD'])) . ' ' . xl('Age') . ': ' . getPatientAge($patientData['DOB_YMD']);

		    $dateFormat = self::getCurrentDateFormat();

		    $edate = isset($rowresult4['date']) ? date($dateFormat, strtotime($rowresult4['date'])) : '';
		    $cCat = isset($rowresult4['pc_catname']) ? $rowresult4['pc_catname'] : '';
			$pName = trim($rowresult4['fname'].' '.$rowresult4['mname'].' '.$rowresult4['lname']);
			if(!empty($pName)) {
				$pName = ' - '.$pName;
			}

			$signed = $item['signed'] === true ? 'Signed' : 'Unsigned';
			if(!empty($signed)) {
				$signed = ' - '.$signed.'';
			}

			$titleLink = trim($edate .' '. $cCat.$pName.$signed);

		    $rowresult4['patient_name'] = $patientName;
		    $rowresult4['patient_DOB'] = $patientDOB;
		    $rowresult4['pubpid'] = $patientData['pubpid'];
		    $rowresult4['titleLink'] = $titleLink;

		    return $rowresult4;
		}

		return false;
	}

	public static function fetch_appt_signatures_data_byId($eid) {
	    if(!empty($eid)) {
	        $eSql = "SELECT FE.encounter, E.id, E.tid, E.table, E.uid, U.fname, U.lname, E.datetime, E.is_lock, E.amendment, E.hash, E.signature_hash 
	                FROM form_encounter FE 
	                LEFT JOIN esign_signatures E ON (case when E.`table` ='form_encounter' then FE.encounter = E.tid else  FE.id = E.tid END)
	                LEFT JOIN users U ON E.uid = U.id 
	                WHERE FE.encounter = ? 
	                ORDER BY E.datetime ASC";
	        $result = sqlQuery($eSql, array($eid));
	        return $result;
	    }
	    return false;
	}

	public static function getCurrentDateFormat() {
		if ($GLOBALS['date_display_format'] == 1) {
		    $format = "m/d/Y";
		} elseif ($GLOBALS['date_display_format'] == 2) {
		    $format = "d/m/Y";
		} else {
		    $format = "Y-m-d";
		}

		return $format;
	}

	public static function fetchMessageList1($pid) {

		if(empty($pid)) {
			return array();
		}

		$binds = array(1);
		$wherePidStr  = "";

		if(is_array($pid)) {
			foreach ($pid as $value) {
				if(!empty($value)) {
					if(!empty($wherePidStr)) {
						$wherePidStr .= "OR ";
					}

					$wherePidStr .= "pnotes.pid = ? ";
					$binds[] = $value;
				}
			}

			if(!empty($wherePidStr)) {
				$wherePidStr = ' ('.$wherePidStr.') ';
			}
		} else {
			$wherePidStr  = "pnotes.pid = ? ";
			$binds[] = $pid;
		}

		$sql = 'SELECT pnotes.id, pnotes.user, pnotes.pid, pnotes.title, '.
		'pnotes.date, pnotes.message_status, pnotes.assigned_to, '.
		'list_options.option_id, '.
		'IF(pnotes.user != pnotes.pid, u.fname, patient_data.fname) AS '.
		'users_fname, '.
		'IF(pnotes.user != pnotes.pid, u.lname, patient_data.lname) AS '.
		'users_lname, '.
		'IF((pnotes.assigned_to != "" AND pnotes.assigned_to NOT LIKE "%-patient-%"), '.
			'IF(SUBSTRING(pnotes.assigned_to,1,3) = "GRP", '.
			'list_options.title, msg_to.lname), patient_data.lname) '.
			'AS msg_to_lname, '.
		'IF((pnotes.assigned_to != "" AND pnotes.assigned_to NOT LIKE "%-patient-%"), '.
			'IF(SUBSTRING(pnotes.assigned_to,1,3) = "GRP", '.
			'list_options.notes, msg_to.fname), patient_data.fname) '.
			'AS msg_to_fname, '.
		'patient_data.fname AS patient_data_fname, '.
		'patient_data.lname AS patient_data_lname '.
		'FROM pnotes '.
		'LEFT JOIN users AS u ON pnotes.user = u.username '.
		'LEFT JOIN users AS msg_to ON pnotes.assigned_to = msg_to.username '.
		'LEFT JOIN list_options ON '.
		'(SUBSTRING(pnotes.assigned_to,5) = list_options.option_id '.
		'AND list_options.list_id = "Messaging_Groups") '.
		'LEFT JOIN patient_data ON pnotes.pid = patient_data.pid '.
		'WHERE pnotes.deleted != ? AND '.$wherePidStr.' order by pnotes.date desc';

		$result = sqlStatement($sql, $binds);

		$messageList = array();
		while ($row = sqlFetchArray($result)) {
			$patient_id = $row['pid'];
			$patientData = getPatientData($patient_id, "fname, mname, lname, pubpid, billing_note, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");


			$patientName = addslashes(htmlspecialchars($patientData['fname'] . ' ' . $patientData['lname']));

		    $patientDOB = xl('DOB') . ': ' . addslashes(oeFormatShortDate($patientData['DOB_YMD'])) . ' ' . xl('Age') . ': ' . getPatientAge($patientData['DOB_YMD']);

		    $row['patient_name'] = $patientName;
		    $row['patient_DOB'] = $patientDOB;
		    $row['pubpid'] = $patientData['pubpid'];
		    $row['pid'] = $patient_id;

		    $name = $row['user'];
            $name = $row['users_lname'];
            if ($row['users_fname']) {
                $name .= ", " . $row['users_fname'];
            }
            if(empty($name)) $name = $row['user'];
				        $msg_to = $row['msg_to_lname'];
            if ($row['msg_to_fname']) {
                $msg_to .= ", " . $row['msg_to_fname'];
            }

            $patient = $row['pid'];
            if ($patient > 0) {
                $patient = $row['patient_data_lname'];
                if ($row['patient_data_fname']) {
                    $patient .= ", " . $row['patient_data_fname'];
                }
            } else {
                $patient = "* " . xlt('Patient must be set manually') . " *";
            }

            $row['user_fullname'] = $name;
            $row['msg_to'] = $msg_to;
            $row['patient_fullname'] = $patient;
            $row['link_title'] = '('.$row['id'].') '.$row['user_fullname'].' - '.$row['msg_to'].' - '.$row['patient_fullname'].' - '.$row['message_status'].' - '.text(oeFormatShortDate(substr($row['date'], 0, strpos($row['date'], " "))));

		    $messageList['doc_'.$row['id']] = $row;
		}

		return $messageList;
	}

	public static function fetchMessageList($pid, $user, $selectCol = '*', $columnName = '', $columnSortOrder = '', $limit = '', $rowperpage = '') {

		if(empty($pid)) {
			return array();
		}

		$binds = array(1);
		$wherePidStr  = "";

		if(is_array($pid)) {
			foreach ($pid as $value) {
				if(!empty($value)) {
					if(!empty($wherePidStr)) {
						$wherePidStr .= "OR ";
					}

					$wherePidStr .= "pnotes.pid = ? ";
					$binds[] = $value;
				}
			}

			if(!empty($wherePidStr)) {
				$wherePidStr = ' ('.$wherePidStr.') ';
			}
		} else {
			$wherePidStr  = "pnotes.pid = ? ";
			$binds[] = $pid;
		}

		if(!empty($user)) {
			$wherePidStr .= " AND pnotes.user = ? ";
			$binds[] = $user;
		}

		$query = 'SELECT '.$selectCol.' FROM pnotes '.
		'LEFT JOIN users AS u ON pnotes.user = u.username '.
		'LEFT JOIN users AS msg_to ON msg_to.username = pnotes.assigned_to AND pnotes.assigned_to != "" '.
		'LEFT JOIN list_options ON '.
		'(SUBSTRING(pnotes.assigned_to,5) = list_options.option_id '.
		'AND list_options.list_id = "Messaging_Groups") '.
		'LEFT JOIN patient_data ON pnotes.pid = patient_data.pid '.
		'WHERE pnotes.deleted != ? AND '.$wherePidStr.'';

		//$query = "SELECT ".$selectCol." FROM form_rto as fr WHERE ".$wherePidStr;

	    if(!empty($columnName) && !empty($columnSortOrder)) {
	    	$query .= " ORDER BY ".$columnName." ".$columnSortOrder;
	    }

	    if((!empty($limit) || $limit >= 0) && !empty($rowperpage)) {
	    	$query .= " LIMIT ".$limit." , ".$rowperpage;
	    }

		$result = sqlStatement($query, $binds);

		$messageList = array();
		while ($row = sqlFetchArray($result)) {
		    $messageList[] = $row;
		}

		return $messageList;
	}

	public static function fetchMessageById($id) {
		$sql = 'SELECT pnotes.id, pnotes.user, pnotes.pid, pnotes.title, '.
		'pnotes.date, pnotes.message_status, pnotes.assigned_to, '.
		'list_options.option_id, '.
		'IF(pnotes.user != pnotes.pid, u.fname, patient_data.fname) AS '.
		'users_fname, '.
		'IF(pnotes.user != pnotes.pid, u.lname, patient_data.lname) AS '.
		'users_lname, '.
		'IF((pnotes.assigned_to != "" AND pnotes.assigned_to NOT LIKE "%-patient-%"), '.
			'IF(SUBSTRING(pnotes.assigned_to,1,3) = "GRP", '.
			'list_options.title, msg_to.lname), patient_data.lname) '.
			'AS msg_to_lname, '.
		'IF((pnotes.assigned_to != "" AND pnotes.assigned_to NOT LIKE "%-patient-%"), '.
			'IF(SUBSTRING(pnotes.assigned_to,1,3) = "GRP", '.
			'list_options.notes, msg_to.fname), patient_data.fname) '.
			'AS msg_to_fname, '.
		'patient_data.fname AS patient_data_fname, '.
		'patient_data.lname AS patient_data_lname '.
		'FROM pnotes '.
		'LEFT JOIN users AS u ON pnotes.user = u.username '.
		'LEFT JOIN users AS msg_to ON pnotes.assigned_to = msg_to.username '.
		'LEFT JOIN list_options ON '.
		'(SUBSTRING(pnotes.assigned_to,5) = list_options.option_id '.
		'AND list_options.list_id = "Messaging_Groups") '.
		'LEFT JOIN patient_data ON pnotes.pid = patient_data.pid '.
		'WHERE pnotes.deleted != ? AND pnotes.id = ? order by pnotes.date desc';

		$binds = array(1, $id);

		$row = sqlQuery($sql, $binds);

		$messageList = false;
		if(!empty($row)) {
			$patient_id = $row['pid'];
			$patientData = getPatientData($patient_id, "fname, mname, lname, pubpid, billing_note, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");


			$patientName = addslashes(htmlspecialchars($patientData['fname'] . ' ' . $patientData['lname']));

		    $patientDOB = xl('DOB') . ': ' . addslashes(oeFormatShortDate($patientData['DOB_YMD'])) . ' ' . xl('Age') . ': ' . getPatientAge($patientData['DOB_YMD']);

		    $row['patient_name'] = $patientName;
		    $row['patient_DOB'] = $patientDOB;
		    $row['pubpid'] = $patientData['pubpid'];
		    $row['pid'] = $patient_id;

		    $name = $row['user'];
            $name = $row['users_lname'];
            if ($row['users_fname']) {
                $name .= ", " . $row['users_fname'];
            }
            if(empty($name)) $name = $row['user'];
				        $msg_to = $row['msg_to_lname'];
            if ($row['msg_to_fname']) {
                $msg_to .= ", " . $row['msg_to_fname'];
            }

            $patient = $row['pid'];
            if ($patient > 0) {
                $patient = $row['patient_data_lname'];
                if ($row['patient_data_fname']) {
                    $patient .= ", " . $row['patient_data_fname'];
                }
            } else {
                $patient = "* " . xlt('Patient must be set manually') . " *";
            }

            $row['user_fullname'] = $name;
            $row['msg_to'] = $msg_to;
            $row['patient_fullname'] = $patient;
            $row['link_title'] = '('.$row['id'].') '.$row['user_fullname'].' - '.$row['msg_to'].' - '.$row['patient_fullname'].' - '.$row['message_status'].' - '.text(oeFormatShortDate(substr($row['date'], 0, strpos($row['date'], " "))));

		    $messageList = $row;
		}

		return $messageList;
	}

	/*Get Document List*/
	public static function getDocumentList($pid) {
		if(empty($pid)) {
			return array();
		}

		$binds = array();
		$wherePidStr  = "";

		if(is_array($pid)) {
			foreach ($pid as $value) {
				if(!empty($value)) {
					if(!empty($wherePidStr)) {
						$wherePidStr .= "OR ";
					}

					$wherePidStr .= "d.foreign_id = ? ";
					$binds[] = $value;
				}
			}

			if(!empty($wherePidStr)) {
				$wherePidStr = ' ('.$wherePidStr.') ';
			}
		} else {
			$wherePidStr  = "d.foreign_id = ? ";
			$binds[] = $pid;
		}


	    $query = "SELECT d.id, d.type, d.size, d.url, d.docdate, d.list_id, d.encounter_id, d.foreign_id, c.name " .
	    "FROM documents AS d, categories_to_documents AS cd, categories AS c WHERE " .
	    "".$wherePidStr." AND cd.document_id = d.id AND c.id = cd.category_id ";

	    $query .= "ORDER BY d.docdate DESC, d.id DESC";
	    $dres = sqlStatement($query, $binds);

	    $list = array();
	    while ($drow = sqlFetchArray($dres)) {
		    $drow['baseName'] = basename($drow['url']) . ' (' . xl_document_category($drow['name']). ')';
		    $drow['baseFileName'] = ' (' . xl_document_category($drow['name']). ')' . basename($drow['url']);
		    $drow['issue'] = self::getAssociatedIssue($drow['list_id']);
		    $list[$drow['id']] = $drow;
	    }
	    return $list;
	}

	/*Get Order List*/
	public static function getOrderList($pid, $selectCol = '*', $columnName = '', $columnSortOrder = '', $limit = '', $rowperpage = '') {
		if(empty($pid)) {
			return array();
		}

		$binds = array();
		$wherePidStr  = "";

		if(is_array($pid)) {
			foreach ($pid as $value) {
				if(!empty($value)) {
					if(!empty($wherePidStr)) {
						$wherePidStr .= "OR ";
					}

					$wherePidStr .= "fr.pid = ? ";
					$binds[] = $value;
				}
			}

			if(!empty($wherePidStr)) {
				$wherePidStr = ' ('.$wherePidStr.') ';
			}
		} else {
			$wherePidStr  = "fr.pid = ? ";
			$binds[] = $pid;
		}


	    $query = "SELECT ".$selectCol." FROM form_rto as fr WHERE ".$wherePidStr;

	    if(!empty($columnName) && !empty($columnSortOrder)) {
	    	$query .= " ORDER BY ".$columnName." ".$columnSortOrder;
	    }

	    if((!empty($limit) || $limit >= 0) && !empty($rowperpage)) {
	    	$query .= " LIMIT ".$limit." , ".$rowperpage;
	    }

	    $dres = sqlStatement($query, $binds);

	    $list = array();
	    while ($drow = sqlFetchArray($dres)) {
	    	$list[] = $drow;
	    }
	    return $list;
	}

	public static function fetchOrderById($id) {
		$sql = 'SELECT * FROM form_rto as fr WHERE fr.id = ?';

		$binds = array($id);

		$row = sqlQuery($sql, $binds);

		$orderList = false;
		if(!empty($row)) {
			$patient_id = $row['pid'];
			$patientData = getPatientData($patient_id, "fname, mname, lname, pubpid, billing_note, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");


			$patientName = addslashes(htmlspecialchars($patientData['fname'] . ' ' . $patientData['lname']));

		    $patientDOB = xl('DOB') . ': ' . addslashes(oeFormatShortDate($patientData['DOB_YMD'])) . ' ' . xl('Age') . ': ' . getPatientAge($patientData['DOB_YMD']);

		    $row['patient_name'] = $patientName;
		    $row['patient_DOB'] = $patientDOB;
		    $row['pubpid'] = $patientData['pubpid'];


			$itemTitle = self::ListLook($row['rto_action'],'RTO_Action');

			if(!empty($ritem['rto_status'])) {
				$itemTitle .= ' - '.self::ListLook($row['rto_status'],'RTO_Status');
			}
			$row['link_title'] = $itemTitle;

		    $orderList = $row;
		}

		return $orderList;
	}

	public static function ListLook($thisData, $thisList) {
	  if($thisData == '') return ''; 
	  $rret=sqlQuery("SELECT * FROM list_options WHERE list_id=? ".
	        "AND option_id=?", array($thisList, $thisData));
		if($rret{'title'}) {
	    $dispValue= $rret{'title'};
	  } else {
	    $dispValue= '* Not Found *';
	  }
	  return $dispValue;
	}

	public static function getInternalNote($noteId) {
		$result = sqlQuery("SELECT * FROM `rto_action_logs` WHERE type = ? AND foreign_id = ? ORDER BY created_date DESC LIMIT 1", array("INTERNAL_NOTE", $noteId));
		return $result;
	}


	function saveMsgLog($type = '', $msgId = '', $relationId = '', $sentTo = '', $pid = '', $operation = '', $createdBy = '') {
		$sql = "INSERT INTO `rto_action_logs` ( type, msg_id, foreign_id, sent_to, pid, operation, created_by ) VALUES (?, ?, ?, ?, ?, ?, ?) ";
		$responce = sqlInsert($sql, array(
			$type,
			$msgId,
			$relationId,
			$sentTo,
			$pid,
			$operation,
			$createdBy
		));

		return $responce;
	}

	public static function saveMsgGprelation($type1 = '', $id1 = '', $type2 = '', $id2 = '') {
		if(!empty($type1) && !empty($type2)) {
			$bind = array($type1, $id1, $type2, $id2);
			sqlInsert("INSERT INTO `gprelations` ( type1, id1, type2, id2 ) VALUES (?, ?, ?, ?) ", $bind);
		}
	}

	public static function after_msg_assign() {
		global $assignNoteId, $set_id, $set_action;

		if($set_action == 'assign' && !empty($assignNoteId) && !empty($set_id)) {
			self::saveMsgGprelation('104', $set_id, '6', $assignNoteId);
		}
	}

	public static function fetchMsgById($id) {
		$sql = 'SELECT * FROM message_log as ml WHERE ml.id = ?';
		$binds = array($id);
		$row = sqlQuery($sql, $binds);

		$mType = array(
			'EMAIL' => "Email",
			'FAX' => "Fax",
			"SMS" => "Sms",
			"P_LETTER" => "Postal Letter"
		);

		$msgList = false;
		if(!empty($row)) {
			$patient_id = $row['pid'];
			$patientData = getPatientData($patient_id, "fname, mname, lname, pubpid, billing_note, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");

			$rawData = json_decode($row['raw_data'], true);
			$patientName = addslashes(htmlspecialchars($patientData['fname'] . ' ' . $patientData['lname']));

		    $patientDOB = xl('DOB') . ': ' . addslashes(oeFormatShortDate($patientData['DOB_YMD'])) . ' ' . xl('Age') . ': ' . getPatientAge($patientData['DOB_YMD']);

		    $row['patient_name'] = $patientName;
		    $row['patient_DOB'] = $patientDOB;
		    $row['pubpid'] = $patientData['pubpid'];



		    $linkTitle = $mType[$row['type']].' - ';
		    if($row['type'] == "EMAIL") {
				if(!empty($row['event'])) {
					$linkTitle .= $row['event'];
				}

				if(!empty($row['msg_to'])) {
					$linkTitle .= " (".$row['msg_to'].") ";
				}
			} else if($row['type'] == "FAX") {
				if(!empty($rawData['rec_name'])) {
					$linkTitle .= $rawData['rec_name'];
				}

				if(!empty($row['msg_to'])) {
					$linkTitle .= " (".$row['msg_to'].") ";
				}
			} else if($row['type'] == "P_LETTER") {
				if(!empty($rawData['rec_name'])) {
					$linkTitle .= $rawData['rec_name'];
				}

				if(!empty($row['msg_to'])) {
					$linkTitle .= " (".$row['msg_to'].") ";
				}
			} else if($row['type'] == "SMS") {
				if(!empty($row['patient_name'])) {
					$linkTitle .= stripslashes($row['patient_name']);
				}

				// if(!empty($row['msg_to'])) {
				// 	$linkTitle .= " (".$row['msg_to'].") ";
				// }

				if($row['direction'] == "in") {
					$linkTitle .= " (".$row['msg_from'].") ";
				} else if($row['direction'] == "out") {
					$linkTitle .= " (".$row['msg_to'].") ";
				}
			}

			$row['link_title'] = $linkTitle;

		    $msgList = $row;
		}

		return $msgList;
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

	public static function handleAttachment_Js() {
		global $pid;
		?>
		<script type="text/javascript">
			var attachClass = function(objname = '', elementContainer = '', filesContainer = '', docsList = {}, baseDocList = []){
				this.objname = objname;
				this.elementContainer = elementContainer;
				this.filesContainer = filesContainer;
				this.docsList = docsList;
				this.baseDocList = baseDocList;
			};
			attachClass.prototype = {
				filesToUpload : [],
				uploadFileList : {},
			    selectedEncounterList : {},
				selectedDocuments : {},
				selectedMessages : {},
				selectedOrders : {},
				selectedNotes : {},
	    		selectedEncounterList1 : {},
	    		selectedEncounterIns : {},
	    		checkEncounterDemo : false,
	    		checkEncounterInsDemo : false,
	    		docsList : {},
	    		baseDocList : [],
	    		init : function() {
	    			if(this.baseDocList.includes('uploadFileList')) {
	    				this.uploadFileList = JSON.stringify(this.baseDocList['uploadFileList']);
	    				//this.handleUploadFileListCallBack();
	    			}

	    			if(this.baseDocList.includes('selectedDocuments')) {
	    				this.selectedDocuments = JSON.stringify(this.baseDocList['selectedDocuments']);
	    				this.handleDocumentsCallBack();
	    			}

	    			if(this.baseDocList.includes('selectedNotes')) {
	    				this.selectedNotes = JSON.stringify(this.baseDocList['selectedNotes']);
	    				//this.handleInternalNoteCallBack();
	    			}

	    			if(this.baseDocList.includes('selectedEncounterList')) {
	    				this.selectedEncounterList = JSON.stringify(this.baseDocList['selectedEncounterList']);
	    				this.checkEncounterDemo = this.baseDocList['checkEncounterDemo'];
	    				this.handleEncountersCallBack();
	    			}

	    			if(this.baseDocList.includes('selectedEncounterIns')) {
	    				this.selectedEncounterIns = JSON.stringify(this.baseDocList['selectedEncounterIns']);
	    				this.checkEncounterInsDemo = this.baseDocList['checkEncounterInsDemo'];
	    				//this.handleDemosInsurancesCallBack();
	    			}

	    			if(this.baseDocList.includes('selectedOrder')) {
	    				this.selectedOrder = JSON.stringify(this.baseDocList['selectedOrder']);
	    				//this.handleOrderCallBack();
	    			}
	    		},
				getSelectEncountersElement : function(pid) {
					if(this.objname != '' && this.elementContainer != '') {
						var eleContainer = $('#'+this.elementContainer);
						var funClick = this.objname+'.handleSelectEncounters("'+pid+'")';

						if(eleContainer.length > 0) {
							$('#'+this.elementContainer).append("<div class='btnContainer'><input type='button' class='form-control btn btn-primary' id='encounterSelect_"+this.objname+"' value='<?php echo xla('Select Encounters & Forms'); ?>'' onClick='"+funClick+"'></div>");
						}
					}
				},
				getSelectDocumentElement : function(pid) {
					if(this.objname != '' && this.elementContainer != '') {
						var eleContainer = $('#'+this.elementContainer);
						var funClick = this.objname+'.handleSelectDocuments("'+pid+'")';

						if(eleContainer.length > 0) {
							$('#'+this.elementContainer).append("<div class='btnContainer'><input type='button' class='form-control btn btn-primary' id='documentSelect_"+this.objname+"' value='<?php echo xla('Select Documents'); ?>'' onClick='"+funClick+"'></div>");
						}
					}
				},
			    handleSelectEncounters : function(pid, list = '',) {
					if(pid == "") {
						return false;
					}
					var url = '<?php echo $GLOBALS['webroot']."/interface/main/attachment/encouter.php"; ?>?pid='+pid+'&list='+list+'&mid='+this.objname;
					dlgopen(url,this.objname+'-selectEncountersPop', 800, 300, '', 'Encounters & Forms');
				},
				handleSelectDocuments : function(pid, list = '',) {
					if(pid == "") {
						return false;
					}
					var url = '<?php echo $GLOBALS['webroot']."/interface/main/attachment/documents.php"; ?>?pid='+pid+'&list='+list+'&mid='+this.objname;
					dlgopen(url,this.objname+'-selectDocumentsPop', 800, 400, '', 'Documents');
				},
				handleEncountersCallBack : function(selectedlist = [], list = '') {
					$finalVariableStr = this.objname;

					if(list && list != '') {
						$finalVariableStr = list;
					}

					var funRemove = this.objname+'.handleReomveEncounter(this)';

					var output = [];
					var currentObj = this;
					$.each(this.selectedEncounterList, function(i, n){
					    if(n['parentId'] == undefined) {
					    	var removeLink = "<a class=\"removeEncountersFile\" href=\"javascript:void(0)\" data-list='" + $finalVariableStr + "_selectedEncounterList' data-fileid=\"" + i + "\" onclick=\""+funRemove+"\">Remove</a>";
							output.push("<li class='removeEncountersFileContainer_"+$finalVariableStr+"_selectedEncounterList'><span><strong>", n['title'], "</strong> - ", removeLink, "</span>", currentObj.childGenerate(i, $finalVariableStr), "</li> ");
						}
					});

					$('.removeEncountersFileContainer_'+$finalVariableStr+'_selectedEncounterList').remove();
					$('#'+this.filesContainer).find("div .fileList")
				             .append(output.join(""));
				},
				handleDocumentsCallBack : function(selectedlist = []) {
					var output = [];
					var funRemove = this.objname+'.handleReomveDocument(this)';

					$.each(this.selectedDocuments, function(i, n){
					    var removeLink = "<a class=\"removeDocumentFile\" href=\"javascript:void(0)\" data-fileid=\"" + i + "\" onclick=\""+funRemove+"\">Remove</a>";
						output.push("<li><strong>", n['baseName'], "</strong> - ",n['size'], " bytes. &nbsp; &nbsp; ", removeLink, "</li> ");
					});

					$('#'+this.filesContainer+' .removeDocumentFile').parent().remove();
					$('#'+this.filesContainer).find("div .fileList")
				            .append(output.join(""));
				},
				childGenerate : function(id, finalVariableStr) {
					var output = [];
					var funRemove = this.objname+'.handleReomveEncounter(this)';

					$.each(this.selectedEncounterList, function(i, n){
						if(n['parentId'] == id) {
							var removeLink = "<a class=\"removeEncountersFile\" data-list='" + finalVariableStr + "_selectedEncounterList' href=\"javascript:void(0)\" data-fileid=\"" + i + "\" onclick=\""+funRemove+"\">Remove</a>";
							output.push("<li><strong>", n['title'], "</strong> - ", removeLink, "</li> ");
						}
					});

					return '<ul class="childContainer">'+output.join("")+'</ul>';
				},
				handleReomveEncounter : function(e) {
					var fileId = $(e).data("fileid");

			        delete this.selectedEncounterList[fileId];
			        $(e).parent().remove();
				},
				handleReomveDocument : function(e) {
					var fileId = $(e).data("fileid");

			        delete this.selectedEncounterList[fileId];
			        $(e).parent().remove();
				},
				appendDataToForm : function(formData) {
					//organize the file data
					formData.append("files_length", this.filesToUpload.length);
					for (var i = 0; i < this.filesToUpload.length; i++) {
						formData.append("files["+i+"]", this.filesToUpload[i].file);
					}

			        //organize the document data
			        if(this.selectedDocuments) {
			        	var tempfiles = [];
			        	$.each(this.selectedDocuments, function(i, n){
			        		tempfiles.push(n);
			        	});
			        	formData.append("documentFiles", JSON.stringify(tempfiles));
			        }

			        //organize the notes data
			        if(this.selectedNotes) {
			        	var tempnotes = [];
			        	$.each(this.selectedNotes, function(i, n){
			        		tempnotes.push(n);
			        	});
			        	formData.append("notes", JSON.stringify(tempnotes));
			        }

			        // organize the encounter data
			        if(this.selectedEncounterList) {
			        	var tempencounters = {};
			        	$.each(this.selectedEncounterList, function(i, n){
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

			        //organize the encounter data
			        if(this.selectedEncounterList1) {
			        	var tempencounters = {};
			        	$.each(this.selectedEncounterList1, function(i, n){
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

			        if(this.selectedEncounterIns) {
			        	formData.append("encounterIns", JSON.stringify(this.selectedEncounterIns));
			        }

			        if(this.docsList) {
			        	formData.append("docsList", JSON.stringify(this.docsList));
			        }

			        if(this.uploadFileList) {
			        	formData.append("uploadFileList", JSON.stringify(this.uploadFileList));
			        }

			        if(this.selectedOrder) {
			        	var tempOrderfiles = [];
			        	$.each(this.selectedOrder, function(i, n){
			        		tempOrderfiles.push(n);
			        	});
			        	formData.append("orderList", JSON.stringify(tempOrderfiles));
			        }

			        formData.append("isCheckEncounterDemo", this.checkEncounterDemo);
			        formData.append("isCheckEncounterInsDemo", this.checkEncounterInsDemo);

			    	formData.append("baseDocList", JSON.stringify({
			    		selectedDocuments : this.selectedDocuments,
		    			selectedEncounterList : this.selectedEncounterList,
		    			selectedEncounterList1 : this.selectedEncounterList1,
		    			selectedEncounterIns : this.selectedEncounterIns,
		    			selectedOrder: this.selectedOrder,
		    			checkEncounterDemo : this.checkEncounterDemo,
		    			checkEncounterInsDemo : this.checkEncounterInsDemo
			    	}));
				}
			};
		</script>
		<?php
	}

	public static function getPhoneNumbers($pat_phone) {
		if(empty($pat_phone)) {
			return array('msg_phone' => $pat_phone, 'pat_phone' => $pat_phone, 'raw_phone' => $pat_phone);
		}

		// Get phone numbers
		$pat_phone = preg_replace('/[^0-9]/', '', trim($pat_phone));
		$raw_phone = $pat_phone;

		// Get phone numbers
		$msg_phone = $pat_phone;
		if(strlen($msg_phone) != 12) {
		  $msg_phone = self::formattedPhoneNo($msg_phone);
		  
		  $pat_phone = self::getPhoneNoText($pat_phone);
		  if (strlen($pat_phone) > 10) $pat_phone = substr($pat_phone,0,10);
		  $pat_phone = substr($pat_phone,0,3) ."-". substr($pat_phone,3,3) ."-". substr($pat_phone,6,4);
		}
		return array('msg_phone' => $msg_phone, 'pat_phone' => $pat_phone, 'raw_phone' => $raw_phone);
	}

	public static function formattedPhoneNo($pat_phone) {
		// Get phone numbers
		$msg_phone = $pat_phone;
		if(strlen($msg_phone) <= 10) {
			if (substr($msg_phone,0,1) != '1') $msg_phone = "1" . $msg_phone;
		}
		return $msg_phone;
	}

	public static function getPhoneNoText($pat_phone) {
		// Get phone numbers
		$msg_phone = $pat_phone;
		if(strlen($msg_phone) > 10 && strlen($msg_phone) == 12) {
			$msg_phone = substr($msg_phone,2,12);
		} else if(strlen($msg_phone) > 10 && strlen($msg_phone) == 11) {
			$msg_phone = substr($msg_phone,1,11);
		}
		return $msg_phone;
	}

	public static function getDocumentListByQuery($pid, $selectCol = '*', $columnName = '', $columnSortOrder = '', $limit = '', $rowperpage = '') {
		if(empty($pid)) {
			return array();
		}

		$binds = array();
		$whereStr  = "";

		if(is_array($pid)) {
			foreach ($pid as $value) {
				if(!empty($value)) {
					if(!empty($whereStr)) {
						$whereStr .= "OR ";
					}

					$whereStr .= "d.foreign_id = ? ";
					$binds[] = $value;
				}
			}

			if(!empty($whereStr)) {
				$whereStr = ' ('.$whereStr.') ';
			}
		} else {
			$whereStr  = "d.foreign_id = ? ";
			$binds[] = $pid;
		}


	    $query = "SELECT ".$selectCol." FROM documents as d, categories_to_documents AS cd, categories AS c WHERE ".$whereStr . " AND cd.document_id = d.id AND c.id = cd.category_id";

	    if(!empty($columnName) && !empty($columnSortOrder)) {
	    	$query .= " ORDER BY ".$columnName." ".$columnSortOrder;
	    }

	    if((!empty($limit) || $limit >= 0) && !empty($rowperpage)) {
	    	$query .= " LIMIT ".$limit." , ".$rowperpage;
	    }

	    $dres = sqlStatement($query, $binds);

	    $list = array();
	    while ($drow = sqlFetchArray($dres)) {
	    	$list[] = $drow;
	    }
	    return $list;
	}

	public static function getFormEncountersById($pid, $encounter = '') {
		$results = array();

		$sql = '';
		if(!empty($encounter)) {
			$sql = ' AND forms.encounter = '. $encounter . ' ';
		}

		$res = sqlStatement("SELECT forms.encounter, forms.form_id, forms.form_name, " .
                    "forms.formdir, forms.date AS fdate, form_encounter.date " .
                    ",form_encounter.reason, u.lname, u.fname, ".
		    "CONCAT(fname, ' ', lname) AS drname ".
		    "FROM forms, form_encounter LEFT JOIN users AS u ON ".
		    "(form_encounter.provider_id = u.id) WHERE " .
                    "forms.pid = '$pid' AND form_encounter.pid = '$pid' AND " .
                    "form_encounter.encounter = forms.encounter " .
                    " AND forms.deleted=0 ". $sql .
                    "ORDER BY form_encounter.date $pat_rpt_order, form_encounter.encounter $pat_rpt_order, fdate ASC");

		while ($result = sqlFetchArray($res)) {
			$results[] = $result;
		}
		return $results;
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

	public static function removeElementsByTagName($tagName, $document) {
	  $nodeList = $document->getElementsByTagName($tagName);
	  for ($nodeIdx = $nodeList->length; --$nodeIdx >= 0; ) {
	    $node = $nodeList->item($nodeIdx);
	    $node->parentNode->removeChild($node);
	  }
	}

	public function filterMsg(&$data) {
		if(preg_match('#<div class="attachmentContainer">(.*?)</div>#', $data['message'])) {
			if($data['direction'] == "in" && $data['type'] == "EMAIL") {
				$data['message'] = preg_replace('#<div class="attachmentContainer">(.*?)</div>#', '', $data['message']);;
			}
		}
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

	public static function getTextTitle($strText = '', $replaceStr = "\r\n") {
		$breaks = array("<br />","<br>","<br/>");  
    	$str = str_ireplace($breaks, $replaceStr, $strText); 

		return '<span title="'.strip_tags($str).'">'.$strText.'</span>';
	}

	public static function is_html($string) {
		//return preg_match( "/\/[a-z]*>/i", $string ) != 0;
		return preg_match( "/^<.*>(.*)/", $string ) != 0;
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

	public static function displayAttachment($type, $id, $data) {
		global $webserver_root;

		if(preg_match('#<div class="attachmentContainer">(.*?)</div>#', $data['message'])) {
			return false;
		}

		$docsList = self::getMsgDocs($id);
		$downloadLink = $GLOBALS['webroot']."/interface/main/attachment/downloadDoc.php";

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

	public function addDocuments($type, $list, $file_name, $pid, $category_id, $doc_date = '') {
        if(!empty($list)) {
            $messagesList = self::getMessageByIds($list);

            $emailItem = array();
            foreach ($messagesList as $key => $item) {
                $attachmentList = Attachment::getAttachmentList($item['id']);
                $emailItem[] = array_merge($item, array('attachments' => $attachmentList));
            }

            return Attachment::generateFinalDoc($type, $emailItem, $file_name, $pid, $category_id, $doc_date);
        }

        return false;
    }

    public static function displayIframeMsg($msg = '', $body_class = '') {
    	$formatedMessage = MessagesLib::formateMessageContent($msg);

    	if(preg_match('#<div class="attachmentContainer">(.*?)</div>#', $formatedMessage)) {
			//if($data['direction'] == "in" && $data['type'] == "EMAIL") {
				$formatedMessage = preg_replace('#<div class="attachmentContainer">(.*?)</div>#', '', $formatedMessage);
			//}
		}

		$formatedMessage = str_replace("\r\n","",$formatedMessage);
		$formatedMessage = '<html><body class="'.$body_class.'">'.$formatedMessage.'</body></html>';
		
		$styleCss = '<link rel="stylesheet" href="'.$GLOBALS['webroot'].'/public/themes/style_light.css?v=71" type="text/css">';
		//$styleCss .= '<style type="text/css">.plainText {white-space: pre;font-family:  -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji; font-size: 12px;} body { font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"; font-size: 1rem; font-weight: 400; line-height: 1.5;}</style>';
		$formatedMessage = $styleCss . $formatedMessage;

		return $formatedMessage;
    }

    public static function addMessageOrderLog($pid, $type = '', $orderList = array(), $msgLogId = '', $to = '') {
		if(!empty($orderList)) {
			foreach ($orderList as $oi => $orderItem) {
				$orderId = isset($orderItem['order_id']) ? $orderItem['order_id'] : "";
				if(!empty($orderId)) {
					$type = $type;
					$createdBy = $_SESSION['authUserID'];
					$relation_id = isset($msgLogId) && !empty($msgLogId) ? $msgLogId : NULL;
					$operationType = 'Sent';

					saveOrderLog($type, $orderId, $relation_id, $to, $pid, $operationType, $createdBy);
				}
			}
		}
	}
}
