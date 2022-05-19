<?php

require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Services\CodeTypesService;

class C_DocumentCategory extends Controller
{
    var $template_mod;
    var $document_categories;
    var $tree;
    var $link;
    var $_last_node;

    function __construct($template_mod = "general")
    {
        parent::__construct();
        $this->document_categories = array();
        $this->template_mod = $template_mod;
        $this->assign("FORM_ACTION", $GLOBALS['webroot'] . "/controller.php?" . attr($_SERVER['QUERY_STRING']));
        $this->assign("CURRENT_ACTION", $GLOBALS['webroot'] . "/controller.php?" . "practice_settings&document_category&");
        $this->link = $GLOBALS['webroot'] . "/controller.php?" . "document_category&";
        $this->assign("STYLE", $GLOBALS['style']);
        $this->assign("V_JS_INCLUDES", $GLOBALS['v_js_includes']);

        $t = new CategoryTree(1);
        //print_r($t->tree);
        $this->tree = $t;
    }

    function default_action()
    {
        return $this->list_action();
    }

    function list_action()
    {
        //$this->tree->rebuild_tree(1,1);

        $icon         = 'folder.gif';
        $expandedIcon = 'folder-expanded.gif';
        $menu  = new HTML_TreeMenu();
        $this->_last_node = null;
        $rnode = $this->_array_recurse($this->tree->tree);

        $menu->addItem($rnode);
        $treeMenu = new HTML_TreeMenu_DHTML($menu, array('images' => 'public/images', 'defaultClass' => 'treeMenuDefault'));
        $this->assign("tree_html", $treeMenu->toHTML());

        $this->_tpl_vars['add_node'] = ($this->_tpl_vars['add_node'] ?? false) == true;
        $this->_tpl_vars['edit_node'] = ($this->_tpl_vars['edit_node'] ?? false) == true;

        $twig = new TwigContainer(null, $GLOBALS['kernel']);
        return $twig->getTwig()->render("document_categories/" . $this->template_mod . "_list.html.twig", $this->_tpl_vars);
    }

    function add_node_action($parent_is)
    {
        //echo $parent_is ."<br />";
        //echo $this->tree->get_node_name($parent_is);
        $info = $this->tree->get_node_info($parent_is);
        $this->assign("parent_name", $this->tree->get_node_name($parent_is));
        $this->assign("parent_is", $parent_is);
        $this->assign("add_node", true);
        $this->assign("edit_node", false);
        $this->assign("VALUE", '');
    // Access control defaults to that of the parent.
        $this->assign("ACO_OPTIONS", "<option value=''></option>" . AclExtended::genAcoHtmlOptions($info['aco_spec']));
        $this->assign("CODES", ""); // empty value here
        $this->assign("CODE_TEXT", ""); // empty value here
        return $this->list_action();
    }

    function add_node_action_process()
    {
        if ($_POST['process'] != "true") {
            return;
        }

        $name = $_POST['name'];
        $parent_is = $_POST['parent_is'];
        $parent_name = $this->tree->get_node_name($parent_is);
        $this->tree->add_node($parent_is, $name, $_POST['value'], $_POST['aco_spec'], $_POST['codes']);
        $trans_message = xlt('Sub-category') . " '" . text(xl_document_category($name)) . "' " . xlt('successfully added to category,') . " '" . text($parent_name) . "'";
        $this->assign("message", $trans_message);
        $this->_state = false;
        return $this->list_action();
    }

    function edit_node_action($parent_is)
    {
        $info = $this->tree->get_node_info($parent_is);
        $this->assign("parent_is", $parent_is);
        $this->assign("NAME", $this->tree->get_node_name($parent_is));
        $this->assign("VALUE", $info['value']);
        $this->assign("ACO_OPTIONS", "<option value=''></option>" . AclExtended::genAcoHtmlOptions($info['aco_spec']));
        $this->assign("add_node", false);
        $this->assign("edit_node", true);
        $this->assign("CODES", $info['codes'] ?? '');

        if (!empty($info['codes'])) {
            $codeTypeService = new CodeTypesService();
            $description = $codeTypeService->lookup_code_description($info['codes']);
            $this->assign('CODE_TEXT', $description ?? "");
        } else {
            $this->assign('CODE_TEXT', "");
        }
        return $this->list_action();
    }

    function edit_node_action_process()
    {
        if ($_POST['process'] != "true") {
            return;
        }

        $parent_is = $_POST['parent_is'];
        $this->tree->edit_node($parent_is, $_POST['name'], $_POST['value'], $_POST['aco_spec'], $_POST['codes']);
        $trans_message = xlt('Category changed.');
        $this->assign("message", $trans_message);
        $this->_state = false;
        return $this->list_action();
    }

    function delete_node_action_process($id)
    {
        if ($_POST['process'] != "true") {
            return;
        }

        $category_name = $this->tree->get_node_name($id);
        $category_info = $this->tree->get_node_info($id);
        $parent_name = $this->tree->get_node_name($category_info['parent']);

        if ($parent_name != false && $parent_name != '') {
            $this->tree->delete_node($id);
                $trans_message = xlt('Category') . " '" . text($category_name) . "' " . xlt('had been successfully deleted. Any sub-categories if present were moved below') . " '" . text($parent_name) . "'<br />";
            $this->assign("message", $trans_message);

            if (is_numeric($id)) {
                $sql = "UPDATE categories_to_documents set category_id = '" . $category_info['parent'] . "' where category_id = '" . $id . "'";
                $this->tree->_db->Execute($sql);
            }
        } else {
                $trans_message = xlt('Category') . " '" . text($category_name) . "' " . xlt('is a root node and can not be deleted.') . "<br />";
            $this->assign("message", $trans_message);
        }

        $this->_state = false;

        return $this->list_action();
    }

    function &_array_recurse($array)
    {
        if (!is_array($array)) {
            $array = array();
        }

        $node = &$this->_last_node;
        $icon = 'folder.gif';
        $expandedIcon = 'folder-expanded.gif';
        foreach ($array as $id => $ar) {
            if (is_array($ar) || !empty($id)) {
                if ($node == null) {
                    //echo "r:" . $this->tree->get_node_name($id) . "<br />";
                    $rnode = new HTML_TreeNode(array('text' => $this->tree->get_node_name($id), 'link' => $this->_link("add_node", true) . "parent_id=" . urlencode($id) . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon, 'expanded' => false));
                    $this->_last_node = &$rnode;
                    $node = &$rnode;
                } else {
                    //echo "p:" . $this->tree->get_node_name($id) . "<br />";
                    $this->_last_node = &$node->addItem(new HTML_TreeNode(array('text' => $this->tree->get_node_name($id), 'link' => $this->_link("add_node", true) . "parent_id=" . urlencode($id) . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
                }

                if (is_array($ar)) {
                    $this->_array_recurse($ar);
                }
            } else {
                if ($id === 0 && !empty($ar)) {
                    $info = $this->tree->get_node_info($id);
                  //echo "b:" . $this->tree->get_node_name($id) . "<br />";
                    $node->addItem(new HTML_TreeNode(array('text' => $info['value'], 'link' => $this->_link("add_node", true) . "parent_id=" . urlencode($id) . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
                } else {
                    //there is a third case that is implicit here when title === 0 and $ar is empty, in that case we do not want to do anything
                    //this conditional tree could be more efficient but working with trees makes my head hurt, TODO
                    if ($id !== 0 && is_object($node)) {
                      //echo "n:" . $this->tree->get_node_name($id) . "<br />";
                        $node->addItem(new HTML_TreeNode(array('text' => $this->tree->get_node_name($id), 'link' => $this->_link("add_node", true) . "parent_id=" . urlencode($id) . "&", 'icon' => $icon, 'expandedIcon' => $expandedIcon)));
                    }
                }
            }
        }

        return $node;
    }
}
