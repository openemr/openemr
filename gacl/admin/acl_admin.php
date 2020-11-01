<?php
//First make sure user has access
require_once("../../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

//ensure user has proper access
if (!AclMain::aclCheckCore('admin', 'acl')) {
        echo xlt('ACL Administration Not Authorized');
        exit;
}

require_once('gacl_admin.inc.php');

if (!isset($_POST['action']) ) {
	$_POST['action'] = FALSE;
}

if (!isset($_GET['action']) ) {
	$_GET['action'] = FALSE;
}

switch ($_POST['action']) {
	case 'Delete':
		break;
	case 'Submit':
		$gacl_api->debug_text('Submit!!');
		//showarray($_POST['selected_aco']);
		//showarray($_POST['selected_aro']);

		//Parse the form values
		foreach (array('aco','aro','axo') as $type) {
			$type_array = 'selected_'. $type .'_array';
			$$type_array = array();
			if (!empty($_POST['selected_'. $type]) && is_array($_POST['selected_'. $type])) {
				foreach ($_POST['selected_'. $type] as $value) {
					$split_value = explode('^', $value);
					${$type_array}[$split_value[0]][] = $split_value[1];
				}
			}
			//showarray($$type_array);
		}

		//Some sanity checks.
		if (empty($selected_aco_array)) {
			echo 'Must select at least one Access Control Object<br />' . "\n";
			exit;
		}

		if (empty($selected_aro_array) AND empty($_POST['aro_groups'])) {
			echo 'Must select at least one Access Request Object or Group<br />' . "\n";
			exit;
		}

		$enabled = $_POST['enabled'];
		if (empty($enabled)) {
			$enabled = 0;
		}

		//function add_acl($aco_array, $aro_array, $aro_group_ids=NULL, $axo_array=NULL, $axo_group_ids=NULL, $allow=1, $enabled=1, $acl_id=FALSE ) {
		if (!empty($_POST['acl_id'])) {
			//Update existing ACL
			$acl_id = $_POST['acl_id'];
			if ($gacl_api->edit_acl($acl_id, $selected_aco_array, $selected_aro_array, $_POST['aro_groups'], $selected_axo_array, ($_POST['axo_groups'] ?? null), $_POST['allow'], $enabled, $_POST['return_value'], $_POST['note'], $_POST['acl_section']) == FALSE) {
				echo 'ERROR editing ACL, possible conflict or error found...<br />' . "\n";
				exit;
			}
		} else {
			//Insert new ACL.
			if ($gacl_api->add_acl($selected_aco_array, $selected_aro_array, $_POST['aro_groups'], $selected_axo_array, ($_POST['axo_groups'] ?? null), $_POST['allow'], $enabled, $_POST['return_value'], $_POST['note'], $_POST['acl_section']) == FALSE) {
				echo 'ERROR adding ACL, possible conflict or error found...<br />' . "\n";
				exit;
			}
		}

		$gacl_api->return_page($_POST['return_page']);
		break;
	default:
		//showarray($_GET);
		if ($_GET['action'] == 'edit' AND !empty($_GET['acl_id'])) {
			$gacl_api->debug_text('EDITING ACL');

			//CSRF prevent
            if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
                CsrfUtils::csrfNotVerified();
            }

			//Grab ACL information
			$query = '
				SELECT id,section_value,allow,enabled,return_value,note
				FROM '. $gacl_api->_db_table_prefix .'acl
				WHERE id='. $db->qstr($_GET['acl_id']);
			$acl_row = $db->GetRow($query);
			list($acl_id, $acl_section_value, $allow, $enabled, $return_value, $note) = $acl_row;

			//Grab selected objects
			foreach (array('aco','aro','axo') as $type) {
				$type_array = 'options_selected_'. $type;
				$$type_array = array();

				$query = '
					SELECT a.section_value,a.value,c.name,b.name
					FROM '. $gacl_api->_db_table_prefix . $type .'_map a
					INNER JOIN '. $gacl_api->_db_table_prefix . $type .' b ON b.section_value=a.section_value AND b.value=a.value
					INNER JOIN '. $gacl_api->_db_table_prefix . $type .'_sections c ON c.value=a.section_value
					WHERE a.acl_id='. $db->qstr($acl_id);
				$rs = $db->Execute($query);

				if (is_object($rs)) {
					while ($row = $rs->FetchRow()) {
						list($section_value, $value, $section, $obj) = $row;
						$gacl_api->debug_text("Section Value: $section_value Value: $value Section: $section ACO: " . ($aco ?? ''));
						${$type_array}[$section_value.'^'.$value] = $section.' > '.$obj;
					}
				}
				//showarray($$type_array);
			}

			//Grab selected groups.
			foreach (array('aro','axo') as $type) {
				$type_array = 'selected_'. $type .'_groups';

				$query = '
					SELECT group_id
					FROM '. $gacl_api->_db_table_prefix . $type .'_groups_map
					WHERE acl_id='. $db->qstr($acl_id);
				$$type_array = $db->GetCol($query);
				//showarray($$type_array);
			}

			$show_axo = (!empty($selected_axo_groups) OR !empty($options_selected_axo));
		} else {
			$gacl_api->debug_text('NOT EDITING ACL');
			$allow=1;
			$enabled=1;
			$acl_section_value='user';

			$show_axo = isset($_COOKIE['show_axo']) && $_COOKIE['show_axo'] == '1';
		}

		//Grab sections for select boxes
		foreach (array('acl','aco','aro','axo') as $type) {
			$type_array = 'options_'. $type .'_sections';
			$$type_array = array();

			$query = '
				SELECT value,name
				FROM '. $gacl_api->_db_table_prefix . $type .'_sections
				WHERE hidden=0
				ORDER BY order_value,name';
			$rs = $db->Execute($query);

			if (is_object($rs)) {
				while ($row = $rs->FetchRow()) {
					${$type_array}[$row[0]] = $row[1];
				}
			}

			${$type .'_section_id'} = reset($$type_array);
		}

		//Init the main js array
		$js_array = 'var options = new Array();' . "\n";

		//Grab objects for select boxes
		foreach (array('aco','aro','axo') as $type) {
			//Init the main object js array.
			$js_array .= 'options[\''. $type .'\'] = new Array();' . "\n";

			unset($tmp_section_value);

			$query = '
				SELECT section_value,value,name
				FROM '. $gacl_api->_db_table_prefix . $type .'
				WHERE hidden=0
				ORDER BY section_value,order_value,name';
			$rs = $db->SelectLimit($query,$gacl_api->_max_select_box_items);

			if (is_object($rs)) {
				while ($row = $rs->FetchRow()) {
					$section_value = addslashes($row[0]);
					$value = addslashes($row[1]);
					$name = addslashes($row[2]);

					//Prepare javascript code for dynamic select box.
					//Init the javascript sub-array.
					if (!isset($tmp_section_value) OR $section_value != $tmp_section_value) {
						$i = 0;
						$js_array .= 'options[\''. $type .'\'][\''. $section_value . '\'] = new Array();' . "\n";
						$tmp_section_value = $section_value;
					}

					//Add each select option for the section
					$js_array .= 'options[\''. $type .'\'][\''. $section_value .'\']['. $i .'] = new Array(\''. $value . '\', \''. $name . "');\n";
					$i++;
				}
			}
		}

		//echo "Section ID: $section_id<br />\n";
		//echo "Section Value: ". $acl_section_value ."<br />\n";

		$smarty->assign('options_acl_sections', $options_acl_sections);
		$smarty->assign('acl_section_value', $acl_section_value);

		$smarty->assign('options_axo_sections', $options_axo_sections);
		$smarty->assign('axo_section_value', ($axo_section_value ?? null));

		$smarty->assign('options_aro_sections', $options_aro_sections);
		$smarty->assign('aro_section_value', ($aro_section_value ?? null));

		$smarty->assign('options_aco_sections', $options_aco_sections);
		$smarty->assign('aco_section_value', ($aco_section_value ?? null));

		$smarty->assign('js_array', $js_array);

		$smarty->assign('js_aco_array_name', 'aco');
		$smarty->assign('js_aro_array_name', 'aro');
		$smarty->assign('js_axo_array_name', 'axo');

		//Grab formatted ARO Groups for select box
		$smarty->assign('options_aro_groups', $gacl_api->format_groups($gacl_api->sort_groups('ARO')) );
		$smarty->assign('selected_aro_groups', ($selected_aro_groups ?? null));

		//Grab formatted AXO Groups for select box
		$smarty->assign('options_axo_groups', $gacl_api->format_groups($gacl_api->sort_groups('AXO')) );
		$smarty->assign('selected_axo_groups', ($selected_axo_groups ?? null));

		$smarty->assign('allow', $allow);
		$smarty->assign('enabled', $enabled);
		$smarty->assign('return_value', ($return_value ?? null));
		$smarty->assign('note', ($note ?? null));

		if (isset($options_selected_aco)) {
			$smarty->assign('options_selected_aco', $options_selected_aco);
		}
		$smarty->assign('selected_aco', array_keys($options_selected_aco ?? []));

		if (isset($options_selected_aro)) {
			$smarty->assign('options_selected_aro', $options_selected_aro);
		}
		$smarty->assign('selected_aro', array_keys($options_selected_aro ?? []));

		if (isset($options_selected_axo)) {
			$smarty->assign('options_selected_axo', $options_selected_axo);
		}
		$selected_axo = array_keys($options_selected_axo ?? []);

		$smarty->assign('selected_axo', $selected_axo);

		//Show AXO layer if AXO's are selected.
		$smarty->assign('show_axo', $show_axo);

		if (isset($_GET['acl_id'])) {
			$smarty->assign('acl_id', $_GET['acl_id'] );
		}

		break;
}

//$smarty->assign('return_page', urlencode($_SERVER[REQUEST_URI]) );
if (isset($_GET['return_page'])) {
	$smarty->assign('return_page', $_GET['return_page']);
}
if (isset($_GET['action'])) {
	$smarty->assign('action', $_GET['action']);
}

$smarty->assign('current','acl_admin');
$smarty->assign('page_title', 'ACL Admin');

$smarty->assign('phpgacl_version', $gacl_api->get_version() );
$smarty->assign('phpgacl_schema_version', $gacl_api->get_schema_version() );

$smarty->assign("CSRF_TOKEN_FORM", CsrfUtils::collectCsrfToken());

$smarty->display('phpgacl/acl_admin.tpl');
?>
