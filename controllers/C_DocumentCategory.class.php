<?php

require_once (dirname(__FILE__) . "/../library/classes/Controller.class.php");
require_once(dirname(__FILE__) . "/../library/classes/CategoryTree.class.php");
require_once(dirname(__FILE__) . "/../library/classes/TreeMenu.php");

class C_DocumentCategory extends Controller {

	var $template_mod;
	var $document_categories;
	var $tree;
	var $link;

	function C_DocumentCategory($template_mod = "general") {
		parent::Controller();
		$this->document_categories = array();
		$this->template_mod = $template_mod;
		$this->assign("FORM_ACTION", $GLOBALS['webroot']."/controller.php?" . $_SERVER['QUERY_STRING']);
		$this->assign("CURRENT_ACTION", $GLOBALS['webroot']."/controller.php?" . "practice_settings&document_category&");
		$this->link = $GLOBALS['webroot']."/controller.php?" . "document_category&";
		$this->assign("STYLE", $GLOBALS['style']);
		
		$t = new CategoryTree(1);
		//print_r($t->tree);
		$this->tree = $t;

	}

	function default_action() {
		return $this->list_action();
	}

	function list_action() {
	 	//$this->tree->rebuild_tree(1,1);
		
		$icon         = 'folder.gif';
		$expandedIcon = 'folder-expanded.gif';
		$menu  = new HTML_TreeMenu();
 		$this->_last_node = null;
 		$rnode = $this->_array_recurse($this->tree->tree);

		$menu->addItem($rnode);
		$treeMenu = &new HTML_TreeMenu_DHTML($menu, array('images' => 'images', 'defaultClass' => 'treeMenuDefault'));
		$this->assign("tree_html",$treeMenu->toHTML());
		
		return $this->fetch($GLOBALS['template_dir'] . "document_categories/" . $this->template_mod . "_list.html");
	}
	
	function add_node_action($parent_is) {
		//echo $parent_is ."<br>";
		//echo $this->tree->get_node_name($parent_is);
		$this->assign("parent_name",$this->tree->get_node_name($parent_is));
		$this->assign("parent_is",$parent_is);
		$this->assign("add_node",true);
		return $this->list_action();	
	}
	
	function add_node_action_process() {
		if ($_POST['process'] != "true")
			return;
		$name = $_POST['name'];
		$parent_is = $_POST['parent_is'];
		$parent_name = $this->tree->get_node_name($parent_is);
		$this->tree->add_node($parent_is,$name);
		$this->assign("message", "Sub-category $name successfully added to category $parent_name");
		$this->_state = false;
		return $this->list_action();	
	}
	
	function delete_node_action_process($id) {
		if ($_POST['process'] != "true")
			return;
		$category_name = $this->tree->get_node_name($id);
		$category_info = $this->tree->get_node_info($id);
		$parent_name = $this->tree->get_node_name($category_info['parent']);
		
		if($parent_name != false && $parent_name != '')
		{
			$this->tree->delete_node($id);	
			$this->assign("message", "Category '$category_name' had been successfully deleted. Any sub-categories if present were moved below '$parent_name'.<br>");
			
			if (is_numeric($id)) {
				$sql = "UPDATE categories_to_documents set category_id = '" . $category_info['parent'] . "' where category_id = '" . $id ."'";
				$this->tree->_db->Execute($sql);
			}
		}
		else
		{
			$this->assign("message", "Category '$category_name' is a root node and can not be deleted.<br>");
		}
		$this->_state = false;
		
		return $this->list_action();
	}

	function edit_action_process() {
		if ($_POST['process'] != "true")
			return;
		//print_r($_POST);
		if (is_numeric($_POST['id'])) {
			$this->document_categories[0] = new Pharmacy($_POST['id']);
		}
		else {
			$this->document_categories[0] = new Pharmacy();
		}
  		parent::populate_object($this->document_categories[0]);
		//print_r($this->document_categories[0]);
		//echo $this->document_categories[0]->toString(true);
		$this->document_categories[0]->persist();
		//echo "action processeed";
		$_POST['process'] = "";
	}
	
	function &_array_recurse($array) {
		if (!is_array($array)) {
			$array = array();	
		}
 		$node = &$this->_last_node;
 		$icon = 'folder.gif';
		$expandedIcon = 'folder-expanded.gif';
 		foreach($array as $id => $ar) {
 			if (is_array($ar) || !empty($id)) {
 			  if ($node == null) {
 			  	
 			  	//echo "r:" . $this->tree->get_node_name($id) . "<br>";
			    $rnode = new HTML_TreeNode(array('text' => $this->tree->get_node_name($id), 'link' => $this->_link("add_node",true) . "parent_id=" . ($id) . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon, 'expanded' => false));
			    $this->_last_node = &$rnode;
 			  	$node = &$rnode;
			  }
			  else {
			  	//echo "p:" . $this->tree->get_node_name($id) . "<br>";
 			    $this->_last_node = &$node->addItem(new HTML_TreeNode(array('text' => $this->tree->get_node_name($id), 'link' => $this->_link("add_node",true) . "parent_id=" . ($id) . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
			  }
 			  if (is_array($ar)) {
 			    $this->_array_recurse($ar);
 			  }
 			}
 			else {
 				if ($id === 0 && !empty($ar)) {
 				  $info = $this->tree->get_node_info($id);
 				  //echo "b:" . $this->tree->get_node_name($id) . "<br>";
 				  $node->addItem(new HTML_TreeNode(array('text' => $info['value'], 'link' => $this->_link("add_node",true) . "parent_id=" . ($id) . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
 				}
 				else {
 					//there is a third case that is implicit here when title === 0 and $ar is empty, in that case we do not want to do anything
 					//this conditional tree could be more efficient but working with trees makes my head hurt, TODO
 					if ($id !== 0 && is_object($node)) {
 					  //echo "n:" . $this->tree->get_node_name($id) . "<br>";
 				  	  $node->addItem(new HTML_TreeNode(array('text' => $this->tree->get_node_name($id), 'link' => $this->_link("add_node",true) . "parent_id=" . ($id) . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
 					}
 				}
 			}	
 		}
 		return $node;
 	}
 	
}

?>
