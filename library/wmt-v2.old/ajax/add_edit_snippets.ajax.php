<?php //Templates List
require_once("../../../interface/globals.php");
require_once("$srcdir/options.inc.php");
	
$edit_id = 0;

if(!isset($_POST['snippet_id'])) $_POST['snippet_id'] = '';

function snipExists($cat, $mod) {
	$erow = sqlQuery('SELECT `id` FROM `wmt_snippets` WHERE `category` = ? AND `module` = ? AND `user_id` = ?', array($cat, $mod, $_SESSION['authUserID']));
	if(!isset($erow{'id'})) $erow{'id'} = '';
	return $erow{'id'};
}

function addSnip($cat, $mod, $snip, $global_user, $sub = '') {
	$new_id = '';
	// echo "ADDING ($cat)  [$mod]<br>\n";
	if(!snipExists($cat, $mod)) {
		$new_id = sqlInsert('INSERT INTO `wmt_snippets` (`date`, `user`, `category`, `module`, `user_id`, `snippet`, `global_user`) VALUES (NOW(), ?, ?, ?, ?, ?, ?)', array($_SESSION['authUserID'], $cat, $mod, $_SESSION['authUserID'], $snip, $global_user));
		// echo "Did not exist ($new_id)<br>\n";
	} else {
		// echo "Existed!!<br>\n";
	}
	return $new_id;
}

function getSnippetModules($category, $user='', $global_mod=1, $global_user=1, $mode='array') {
	$all = array();
	$tmp = array();
	$list = '';
	if(!$user) $user = $_SESSION['authUserID'];
	$sql = 'SELECT `wmt_snippets`.*, lo.`title`, lo.`seq` FROM `wmt_snippets` ' .
	'LEFT JOIN `list_options` AS lo ON (`module` = `option_id` AND ' .
	'`list_id` = "Templated_Fields") ' .
	'WHERE `active` = 1 AND `category` = ?';
	if($global_mod) $sql .= ' AND `global_module` = 1';
	$sql .= ' AND (`user_id` = ?';
	if($global_user) $sql .= ' OR `global_user` = 1';
	$sql .= ') ORDER BY `seq` ASC';
	$res = sqlStatement($sql,  array($category, $user));
	while($row = sqlFetchArray($res)) {
		$all[] = $row;
		$tmp[] = $row{'module'};
	}
	if($mode == 'string') $all = implode(',', $tmp);
	return $all;
}
	
if($_POST['save_templates']){
	$edit_id = $_POST['snippet_id'];
	$category = $_POST['category'];
	$modules = $_POST['modules'];
	// error_log(print_r($modules));
	$snippet = $_POST['snippet'];
	$calling_module = $_POST['caller'];
	$from_form = isset($_POST['from_form']) ? $_POST['from_form'] : 0;
	$global_user = isset($_POST['global_user']) ? $_POST['global_user'] : 0;
	$from_form = true;
	// echo "Caller [$calling_module]<br>\n";
	
	if($edit_id > 0) {
		// echo "Edit Mode<br>\n";
		// FIX!  ADD A CATEGORY (INDEX) CHECK IF THE CATEGORY IS CHANGED!
		sqlStatement("UPDATE `wmt_snippets` SET `category` = ?, `module` = ?, `snippet` = ?, `global_user` = ? WHERE `id` = ? AND `user_id` = ?", array($category, $calling_module, $snippet, $global_user, $edit_id, $_SESSION['authUserID']));
		// THE ITEM CAN BE ASSOCIATED WITH MULTIPLE MODULE, WE WON'T CHANGE THE
		// TEXT ON THE OTHERS, BUT WE CAN ADD ASSOCIATIONS IF THEY ARE MISSING
		if(is_array($modules)) {
			// echo "We did get an array<br>\n";
			foreach($modules as $module) {
				// echo "Module ($module) saving<br>\n";
				if($module != $calling_module) {
					if(!snipExists($category, $module)) {
						addSnip($category, $module, $snippet, $global_user);
					} else {
						// I DON'T THINK WE NEED TO TO ANYTHING HERE
					}
				}
			}
		} else {
			if($modules != $calling_module) {
				if(!snipExists($category, $module)) {
					addSnip($category, $module, $snippet, $global_user);
				} else {
					// I DON'T THINK WE NEED TO TO ANYTHING HERE
				}
			}
		}
	} else {
		// echo "Trying To Add<br>\n";
		if(is_array($modules)) {
			// echo "Array: ";
			// print_r($modules);
			// echo "<br>\n";
			foreach($modules as $module) {
				$_id = addSnip($category, $module, $snippet, $global_user);
				if($module == $calling_module) $edit_id = $_id;
			}
		} else {
			// echo "Not an array($modules)<br>\n";
			$edit_id = addSnip($category, $modules, $snippet, $global_user);
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
			$returnData = sqlQuery("SELECT * FROM `wmt_snippets` WHERE `id` = ?", array($edit_id));
			$tmp = getSnippetModules($returnData['category'],'',1,1,'string');
			$return_data['id'] = $edit_id;
			$returnData['modules'] = $tmp;
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

if($_POST['delete_snippet']) {
}
	
/* Bot even sure what this section does
	if($edit_id > 0){
		$edit_data = sqlFetchArray(sqlStatement("SELECT * FROM systems_template WHERE template_id = ?", array($edit_id)));
	}
*/
?>
<html>
	<head>
		<TITLE><?php echo xl('System Templates'); ?></TITLE>
		<?php html_header_show();?>
		<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
		<link rel="stylesheet" href="../../library/css/templates.css" type="text/css">
		<!-- <script type="text/javascript" src="<?php //echo $GLOBALS['assets_static_relative']; ?>/jquery-min-1-9-1/index.js"></script> -->
	</head>
	<body class="body_top">
		<div class="system_template_list_wrap">
			<span class="title"><?php echo $edit_id > 0 ? xl('Edit Templates') : xl('Add Templates'); ?></span> 
			
			<form action="<?php isset($_GET['option_id']) && $_GET['option_id'] != "" ? '../patient_file_summary/demographics_form_option.php' : 'add_edit_templates.php'; ?>" method="post"><br/>
			<div style="text-align:right;max-width:800px;">
				<button type="submit" class="css_button" id="save_templates" name="save_templates" value="<?php echo $edit_id > 0 ? $edit_id : 'add'; ?>">
					<?php echo $edit_id > 0 ? xl('Save Template') : xl('Add Template'); ?>
				</button>
			</div>
			<table class="system_template_table">
				<tbody>
					<tr>
						<td colspan="2" rowspan="2" style="height:300px;background:#feffca;">
							<label><?php echo xl('Template Location'); ?></label><br/>
							<select name="template_location[]"class="template_location" multiple="multiple">
								<?php 
									$templateLocSt = sqlStatement("SELECT * FROM list_options WHERE list_id = ?", array('template_location'));
									$template_location_selected = [];
									if(isset($edit_data['template_location']) && $edit_data['template_location'] != "") $template_location_selected = explode(',', $edit_data['template_location']);
									while ($tmpRow = sqlFetchArray($templateLocSt)) {
										$selected = '';
										if(in_array($tmpRow['title'], $template_location_selected))	$selected = 'selected = "selected"';								
										echo '<option value="'.$tmpRow['title'].'" '.$selected.'>'.$tmpRow['title'].'</option>';
									}
								?>
							</select>
						</td>
						<td>
							<label><?php echo xl('Template Name'); ?></label><br/>
							<input type="text" name="template_name" value="<?php echo isset($edit_data['template_name']) ? $edit_data['template_name'] : ""; ?>" />
						</td>
					</tr>
					<tr>
						<td>
							<label><?php echo xl('Field Description'); ?></label><br/>
							<textarea class="field_description" name="field_description"><?php echo isset($edit_data['field_description']) ? $edit_data['field_description'] : ""; ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
			</form>
		</div>
		
		<script type="text/javascript">
				<?php //if($_POST['save_templates']){ ?>
					//window.close();
					//parent.location.reload();
				<?php //} ?>
		</script>
	</body>
</html>
