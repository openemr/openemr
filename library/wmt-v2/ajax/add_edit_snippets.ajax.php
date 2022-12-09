<?php //Templates List
require_once("../../../interface/globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/wmt-v2/list_tools.inc");
	
$edit_id = 0;

if(!isset($_POST['snippet_id'])) $_POST['snippet_id'] = '';
if(!isset($_POST['category'])) $_POST['category'] = '';
if(!isset($_POST['sub'])) $_POST['sub'] = '';
if(!isset($_POST['types'])) $_POST['types'] = '';
if(!isset($_POST['snippet'])) $_POST['snippet'] = '';
if(!isset($_POST['caller'])) $_POST['caller'] = '';

function snipExists($cat, $type, $sub = '') {
	$old = array();
	$erow = sqlQuery('SELECT `id` FROM `wmt_snippets` WHERE `category` = ? ' .
		'AND `sub_category` = ? AND `type` = ?', array($cat, $sub, $type));
	if(!isset($erow{'id'})) $erow{'id'} = '';
	if($erow['id']) $old[] = $erow;
	return $old;
}

function addSnip($cat, $type, $snip, $global_user, $sub = '') {
	$new_id = '';
	// echo "ADDING ($cat)  [$mod]<br>\n";
	if(!snipExists($cat, $type, $sub)) {
		$new_id = sqlInsert('INSERT INTO `wmt_snippets` (`date`, `user`, ' .
			'`category`, `sub_category`, `type`, `user_id`, `snippet`,  ' .
			'`global_user`) VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?)', 
			array($_SESSION['authUserID'], $cat, $sub, $type, 
			$_SESSION['authUserID'], $snip, $global_user));
		// echo "Did not exist ($new_id)<br>\n";
	} else {
		// echo "Existed!!<br>\n";
	}
	return $new_id;
}

function deleteSnip($id = '') {
	$sql = 'DELETE FROM `wmt_snippets` WHERE id = ?';
	sqlStatement($sql, array($id));
	return true;
}

function getSnippetTypes($category, $user='', $global_snip=1, $global_user=1, $mode='array') {
	$all = array();
	$tmp = array();
	$list = '';
	if(!$user) $user = $_SESSION['authUserID'];
	$sql = 'SELECT `wmt_snippets`.`type`, lo.`notes`, lo.`seq` FROM ' .
		'`wmt_snippets` LEFT JOIN `list_options` AS lo ON (`type` = `notes` AND ' .
		'`list_id` = "Templated_Fields") WHERE `active` = 1 AND `category` = ?';
// 	if($global_mod) $sql .= ' AND `global_snippet` = 1';
	$sql .= ' AND (`user_id` = ?';
	if($global_user) $sql .= ' OR `global_user` = 1';
	$sql .= ') GROUP BY `type` ORDER BY `seq` ASC';
	$res = sqlStatement($sql,  array($category, $user));
	while($row = sqlFetchArray($res)) {
		$all[] = $row;
		$tmp[] = $row{'type'};
	}
	if($mode == 'string') $all = implode(',', $tmp);
	return $all;
}

$edit_id = $_POST['snippet_id'];
$category = $_POST['category'];
$sub = $_POST['sub'];
$types = $_POST['types'];
$snippet = $_POST['snippet'];
$calling_field = $_POST['caller'];
$from_form = isset($_POST['from_form']) ? $_POST['from_form'] : 0;
$global_user = isset($_POST['global_user']) ? $_POST['global_user'] : 0;
$ret = "";

if($_POST['delete_snippet'] == 'delete') {
	if($edit_id > 0) {
		$ret = 'Deleted: ';
		if(is_array($types)) {
			foreach($types as $type) {
				$old = snipExists($category, $type, $sub);
				if(count($old) > 0) {
					foreach($old as $del) {
						$ret .= $del['id'] . '|from array|~';
						deleteSnip($del['id']);
					}
				}
			}
		} else {
			$old = snipExists($category, $types, $sub);
			if(count($old) > 0) {
				foreach($old as $del) {
					$ret .= $del['id'] . '|from string|~';
					deleteSnip($del['id']);
				}
			}
		}
		$returnData['id'] = $edit_id;
		$returnData['dtl'] = $ret;
	} else $returnData[] = 'Nothing to do';
	echo json_encode($returnData, true);
	exit;
}
	
if($_POST['save_templates']) {
	$from_form = true;

	$calling_type = GetListNoteByKey($calling_field, 'Templated_Fields');
	$update_by_id  = 'UPDATE `wmt_snippets` SET `category` = ?, `type` = ?, ' .
		'`sub_category` = ?, `snippet` = ?, `global_user` = ? WHERE `id` = ?';
	$update_by_index  = 'UPDATE `wmt_snippets` SET `category` = ?, `type` = ?, ' .
		'`sub_category` = ?,`snippet` = ?, `global_user` = ? WHERE ' .
		'`type` = ? AND `category` = ? AND `sub_category` = ?';
	
	if($edit_id > 0) {
		// FIX!  ADD A CATEGORY (INDEX) CHECK IF THE CATEGORY IS CHANGED!
		sqlStatement($update_by_id, array($category, $calling_type, $sub, $snippet, 
			$global_user, $edit_id));
		// THE ITEM CAN BE ASSOCIATED WITH MULTIPLE MODULE, WE WON'T CHANGE THE
		// TEXT ON THE OTHERS, BUT WE CAN ADD ASSOCIATIONS IF THEY ARE MISSING
		if(is_array($types)) {
			foreach($types as $type) {
				$old = snipExists($category, $type, $sub);
				if(count($old) < 1) {
					addSnip($category, $type, $snippet, $global_user, $sub);
				} else {
					$ret .= "Updated ($category) [$type] - $global_user<r>\n";
					foreach($old as $iter) {
						sqlStatement($update_by_index, array($category, $type, $sub, 
							$snippet, $global_user, $iter['type'],
							$iter['category'], $iter['sub']));
					}
				}
			}
		} else {
			$old = snipExists($category, $types, $sub);
			if(count($old) < 1) {
				addSnip($category, $types, $snippet, $global_user, $sub);
			} else {
				foreach($old as $iter) {
					sqlStatement($update_by_index, array($category, $type, $sub, 
						$snippet, $global_user, $iter['type'],
						$iter['category'], $iter['sub']));
				}
			}
		}
	} else {
		if(is_array($types)) {
			foreach($types as $type) {
				$_id = addSnip($category, $type, $snippet, $global_user, $sub);
				if($calling_type == $type) $edit_id = $_id;
			}
		} else {
			$edit_id = addSnip($category, $types, $snippet, $global_user, $sub);
		}
	}
	// echo "Edit Id After It All ($edit_id)<br>\n";
	if($from_form == 0) {
		echo "<script type='text/javascript'>";
		echo "window.close();";
		echo "parent.location.reload();";
		echo "</script>";
	} else if($from_form == 1) {
		if($edit_id) {
			$returnData = sqlQuery("SELECT * FROM `wmt_snippets` WHERE `id` = ?", 
				array($edit_id));
			$tmp = getSnippetTypes($returnData['category'],'',1,1,'string');
			$return_data['id'] = $edit_id;
			$returnData['types'] = $tmp;
		} else {
			$returnData = array();
			$return_data['id'] = '';
		}
		$returnData['type'] = 'add';
		if($_POST['save_templates'] != 'add') $returnData['type'] = 'edit';
		echo json_encode($returnData, true);
		exit;
	}
}
?>
