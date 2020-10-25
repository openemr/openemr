<?php
//First make sure user has access
require_once("../../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

//ensure user has proper access
if (!AclMain::aclCheckCore('admin', 'acl')) {
            echo xlt('ACL Administration Not Authorized');
            exit;
}

require_once('gacl_admin.inc.php');

$getAction = $_GET['action'] ?? null;
switch ($getAction) {
	case 'Delete':

	    //CSRF prevent
        if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
            CsrfUtils::csrfNotVerified();
        }

		$gacl_api->debug_text('Delete!');

		if (is_array ($_GET['delete_acl']) AND !empty($_GET['delete_acl'])) {
			foreach($_GET['delete_acl'] as $id) {
				$gacl_api->del_acl($id);
			}
		}

		//Return page.
		$gacl_api->return_page($_GET['return_page']);
		break;
	case 'Submit':
		$gacl_api->debug_text('Submit!!');
		break;
	default:
		/*
		 * When the user requests to filter the list, run the filter and get just the matching IDs.
		 * Use these IDs to get the entire ACL information in the second query.
		 *
		 * If we just put the LIKE statements in the second query, it will match the correct ACLs
		 * but will only return the matching rows, so it won't show the entire ACL information.
		 *
		 */
		if (isset($getAction) AND $getAction == 'Filter') {
			$gacl_api->debug_text('Filtering...');

			$query = '
				SELECT		DISTINCT a.id
				FROM		'. $gacl_api->_db_table_prefix .'acl a
				LEFT JOIN	'. $gacl_api->_db_table_prefix .'aco_map ac ON ac.acl_id=a.id
				LEFT JOIN	'. $gacl_api->_db_table_prefix .'aro_map ar ON ar.acl_id=a.id
				LEFT JOIN	'. $gacl_api->_db_table_prefix .'axo_map ax ON ax.acl_id=a.id';

			if ( isset($_GET['filter_aco_section']) AND $_GET['filter_aco_section'] != '-1') {
				$filter_query[] = 'ac.section_value='. $db->qstr(strtolower($_GET['filter_aco_section']));
			}
			if ( isset($_GET['filter_aco']) AND $_GET['filter_aco'] != '') {
				$query .= '
				LEFT JOIN	'. $gacl_api->_db_table_prefix .'aco c ON (c.section_value=ac.section_value AND c.value=ac.value)';

				$name = $db->qstr(strtolower($_GET['filter_aco']));
				$filter_query[] = '(lower(c.value) LIKE '. $name .' OR lower(c.name) LIKE '. $name .')';
			}

			if ( isset($_GET['filter_aro_section']) AND $_GET['filter_aro_section'] != '-1') {
				$filter_query[] = 'ar.section_value='. $db->qstr(strtolower($_GET['filter_aro_section']));
			}
			if ( isset($_GET['filter_aro']) AND $_GET['filter_aro'] != '') {
				$query .= '
				LEFT JOIN	'. $gacl_api->_db_table_prefix .'aro r ON (r.section_value=ar.section_value AND r.value=ar.value)';

				$name = $db->qstr(strtolower($_GET['filter_aro']));
				$filter_query[] = '(lower(r.value) LIKE '. $name .' OR lower(r.name) LIKE '. $name .')';
			}
			if ( isset($_GET['filter_aro_group']) AND $_GET['filter_aro_group'] != '') {
				$query .= '
				LEFT JOIN	'. $gacl_api->_db_table_prefix .'aro_groups_map arg ON arg.acl_id=a.id
				LEFT JOIN	'. $gacl_api->_db_table_prefix .'aro_groups rg ON rg.id=arg.group_id';

				$filter_query[] = '(lower(rg.name) LIKE '. $db->qstr(strtolower($_GET['filter_aro_group'])) .')';
			}

			if ( isset($_GET['filter_axo_section']) AND $_GET['filter_axo_section'] != '-1') {
				$filter_query[] = 'ax.section_value='. $db->qstr(strtolower($_GET['filter_axo_section']));
			}
			if ( isset($_GET['filter_axo']) AND $_GET['filter_axo'] != '') {
				$query .= '
				LEFT JOIN	'. $gacl_api->_db_table_prefix .'axo x ON (x.section_value=ax.section_value AND x.value=ax.value)';

				$name = $db->qstr(strtolower($_GET['filter_axo']));
				$filter_query[] = '(lower(x.value) LIKE '. $name .' OR lower(x.name) LIKE '. $name .')';
			}
			if ( isset($_GET['filter_axo_group']) AND $_GET['filter_axo_group'] != '') {
				$query .= '
				LEFT JOIN	'. $gacl_api->_db_table_prefix .'axo_groups_map axg ON axg.acl_id=a.id
				LEFT JOIN	'. $gacl_api->_db_table_prefix .'axo_groups xg ON xg.id=axg.group_id';

				$filter_query[] = '(lower(xg.name) LIKE '. $db->qstr(strtolower($_GET['filter_axo_group'])) .')';
			}

			if ( isset($_GET['filter_acl_section']) AND $_GET['filter_acl_section'] != '-1') {
				$filter_query[] = 'a.section_value='. $db->qstr(strtolower($_GET['filter_acl_section']));
			}
			if ( isset($_GET['filter_return_value']) AND $_GET['filter_return_value'] != '') {
				$filter_query[] = '(lower(a.return_value) LIKE '. $db->qstr(strtolower($_GET['filter_return_value'])) .')';
			}
			if ( isset($_GET['filter_allow']) AND $_GET['filter_allow'] != '-1') {
				$filter_query[] = '(a.allow LIKE '. $db->qstr($_GET['filter_allow']) .')';
			}
			if ( isset($_GET['filter_enabled']) AND $_GET['filter_enabled'] != '-1') {
				$filter_query[] = '(a.enabled LIKE '. $db->qstr($_GET['filter_enabled']) .')';
			}

			if (isset($filter_query) AND is_array($filter_query)) {
				$query .= '
				WHERE '. implode(' AND ', $filter_query);
			}
		} else {
			$query  = '
				SELECT a.id FROM ' . $gacl_api->_db_table_prefix . 'acl a';
		}

		$query .= '
				ORDER BY a.id ASC';

		$acl_ids = array();

		$rs = $db->PageExecute($query, $gacl_api->_items_per_page, ($_GET['page'] ?? null));
		if ( is_object($rs) ) {
			$smarty->assign('paging_data', $gacl_api->get_paging_data($rs));

			while ( $row = $rs->FetchRow() ) {
				$acl_ids[] = $row[0];
			}

			$rs->Close();
		}

		if ( !empty($acl_ids) ) {
			$acl_ids_sql = implode(',', $acl_ids);
		} else {
			//This shouldn't match any ACLs, returning 0 rows.
			$acl_ids_sql = -1;
		}

		$acls = array();

		//If the user is searching, and there are no results, don't run the query at all
		if ( !($getAction == 'Filter' AND $acl_ids_sql == -1) ) {

			// grab acl details
			$query = '
				SELECT	a.id,x.name,a.allow,a.enabled,a.return_value,a.note,a.updated_date
				FROM	'. $gacl_api->_db_table_prefix .'acl a
				INNER JOIN 	'. $gacl_api->_db_table_prefix .'acl_sections x ON x.value=a.section_value
				WHERE	a.id IN ('. $acl_ids_sql . ')';
			$rs = $db->Execute($query);

			if ( is_object($rs) ) {
				while ( $row = $rs->FetchRow() ) {
					$acls[$row[0]] = array(
						'id' => $row[0],
						// 'section_id' => $section_id,
						'section_name' => $row[1],
						'allow' => (bool)$row[2],
						'enabled' => (bool)$row[3],
						'return_value' => $row[4],
						'note' => $row[5],
						'updated_date' => $row[6],

						'aco' => array(),
						'aro' => array(),
						'aro_groups' => array(),
						'axo' => array(),
						'axo_groups' => array()
					);
				}
			}

			// grab ACO, ARO and AXOs
			foreach ( array('aco', 'aro', 'axo') as $type ) {
				$query = '
					SELECT	a.acl_id,o.name,s.name
					FROM	'. $gacl_api->_db_table_prefix . $type .'_map a
					INNER JOIN	'. $gacl_api->_db_table_prefix . $type .' o ON (o.section_value=a.section_value AND o.value=a.value)
					INNER JOIN	'. $gacl_api->_db_table_prefix . $type . '_sections s ON s.value=a.section_value
					WHERE	a.acl_id IN ('. $acl_ids_sql . ')';
				$rs = $db->Execute($query);

				if ( is_object($rs) ) {
					while ( $row = $rs->FetchRow() ) {
						list($acl_id, $name, $section_name) = $row;

						if ( isset($acls[$acl_id]) ) {
							$acls[$acl_id][$type][$section_name][] = $name;
						}
					}
				}
			}

			// grab ARO and AXO groups
			foreach ( array('aro', 'axo') as $type )
			{
				$query = '
					SELECT	a.acl_id,g.name
					FROM	'. $gacl_api->_db_table_prefix . $type .'_groups_map a
					INNER JOIN	'. $gacl_api->_db_table_prefix . $type .'_groups g ON g.id=a.group_id
					WHERE	a.acl_id IN ('. $acl_ids_sql . ')';
				$rs = $db->Execute($query);

				if ( is_object($rs) ) {
					while ( $row = $rs->FetchRow () ) {
						list($acl_id, $name) = $row;

						if ( isset($acls[$acl_id]) ) {
							$acls[$acl_id][$type .'_groups'][] = $name;
						}
					}
				}
			}
		}

		$smarty->assign('acls', $acls);

		$smarty->assign('filter_aco', ($_GET['filter_aco'] ?? null));
        $smarty->assign('filter_aco_escaped', attr($_GET['filter_aco'] ?? null));

		$smarty->assign('filter_aro', ($_GET['filter_aro'] ?? null));
        $smarty->assign('filter_aro_escaped', attr($_GET['filter_aro'] ?? null));

		$smarty->assign('filter_aro_group', ($_GET['filter_aro_group'] ?? null));
        $smarty->assign('filter_aro_group_escaped', attr($_GET['filter_aro_group'] ?? null));

		$smarty->assign('filter_axo', ($_GET['filter_axo'] ?? null));
        $smarty->assign('filter_axo_escaped', attr($_GET['filter_axo'] ?? null));

		$smarty->assign('filter_axo_group', ($_GET['filter_axo_group'] ?? null));
        $smarty->assign('filter_axo_group_escaped', attr($_GET['filter_axo_group'] ?? null));

		$smarty->assign('filter_return_value', ($_GET['filter_return_value'] ?? null));
        $smarty->assign('filter_return_value_escaped', attr($_GET['filter_return_value'] ?? null));

		foreach(array('aco','aro','axo','acl') as $type) {
			//
			//Grab all sections for select box
			//
			$options = array (
				-1 => 'Any'
			);

			$query = '
				SELECT value,name
				FROM '. $gacl_api->_db_table_prefix .$type .'_sections
				WHERE hidden=0
				ORDER BY order_value,name';
			$rs = $db->Execute($query);

			if ( is_object($rs) ) {
				while ($row = $rs->FetchRow()) {
					$options[attr($row[0])] = attr($row[1]);
				}
			}

			$smarty->assign('options_filter_'. $type . '_sections',  $options);

			if (!isset($_GET['filter_' . $type . '_section']) OR $_GET['filter_' . $type . '_section'] == '') {
				$_GET['filter_' . $type . '_section'] = '-1';
			}

			$smarty->assign('filter_' . $type . '_section', $_GET['filter_' . $type .'_section']);
            $smarty->assign('filter_' . $type . '_section_escaped', attr($_GET['filter_' . $type .'_section']));
		}

		$smarty->assign('options_filter_allow', array('-1' => 'Any', 1 => 'Allow', 0 => 'Deny'));
		$smarty->assign('options_filter_enabled', array('-1' => 'Any', 1 => 'Yes', 0 => 'No'));

		if (!isset($_GET['filter_allow']) OR $_GET['filter_allow'] == '') {
			$_GET['filter_allow'] = '-1';
		}
		if (!isset($_GET['filter_enabled']) OR $_GET['filter_enabled'] == '') {
			$_GET['filter_enabled'] = '-1';
		}

		$smarty->assign('filter_allow', $_GET['filter_allow']);
        $smarty->assign('filter_allow_escaped', attr($_GET['filter_allow']));

		$smarty->assign('filter_enabled', $_GET['filter_enabled']);
        $smarty->assign('filter_enabled_escaped', attr($_GET['filter_enabled']));
}

$smarty->assign('action', $getAction);
$smarty->assign('action_escaped', attr($getAction));

$smarty->assign('return_page', $_SERVER['PHP_SELF']);

$smarty->assign('current','acl_list');
$smarty->assign('page_title', 'ACL List');

$smarty->assign('phpgacl_version', $gacl_api->get_version());
$smarty->assign('phpgacl_schema_version', $gacl_api->get_schema_version());

$smarty->assign("CSRF_TOKEN_FORM", CsrfUtils::collectCsrfToken());

$smarty->display('phpgacl/acl_list.tpl');
?>
