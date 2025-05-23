<?php

/**
 * interface/modules/zend_modules/module/Acl/src/Acl/Controller/AclController.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jacob T.Paul <jacob@zhservices.com>
 * @author    Basil PT <basil@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Acl\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Application\Listener\Listener;

class AclController extends AbstractActionController
{
    /**
     * @var \Acl\Model\AclTable
     */
    protected $aclTable;

    protected $listenerObject;
    private $htmlEscaper;

    public function __construct(\Laminas\View\Helper\HelperInterface $htmlEscaper, \Acl\Model\AclTable $aclTable)
    {
        $this->htmlEscaper = $htmlEscaper;
        // TODO: we should probably inject the Listener object as well so we can mock it in unit tests or at least make the dependency explicit.
        $this->listenerObject = new Listener();
        $this->aclTable = $aclTable;
    }

    public function indexAction()
    {
        $module_id = $this->params()->fromQuery('module_id');
                $result = $this->getAclTable()->aclSections($module_id);

        $arrayCategories = array();
        foreach ($result as $row) {
            $arrayCategories[$row['section_id']] = array("parent_id" => $row['parent_section'], "name" =>
            $row['section_name'],"id" => $row['section_id']);
        }

        ob_start();
        $this->createTreeView($arrayCategories, 0);
        $sections = ob_get_clean();

        $user_group_main     = $this->createUserGroups("user_group_", "", "draggable2");
        $user_group_allowed  = $this->createUserGroups("user_group_allowed_", "display:none;", "draggable3", "class='class_li'");
        $user_group_denied   = $this->createUserGroups("user_group_denied_", "display:none;", "draggable4", "class='class_li'");

        $result = $this->getAclTable()->getActiveModules();
        foreach ($result as $row) {
            $array_active_modules[$row['mod_id']] = $row['mod_name'];
        }

        $index = new ViewModel(array(
                        'user_group_main'       => $user_group_main,
            'user_group_allowed'    => $user_group_allowed,
            'user_group_denied'     => $user_group_denied,
            'sections'              => $sections,
            'component_id'          => "0-" . $module_id,
            'module_id'             => $module_id,
            'listenerObject'            => $this->listenerObject,
            'active_modules'        => $array_active_modules,
                ));
                return $index;
    }

    public function acltabAction()
    {
        $module_id = $this->params()->fromQuery('module_id');
        $this->layout('layout/layout_tabs');
        $index = new ViewModel(array(
            'mod_id' => $module_id,
                ));
                return $index;
    }

    public function aclAction()
    {
        $module_id = $this->params()->fromQuery('module_id');
        $data = $this->getAclTable()->getGroups();

        $user_groups = array();
        foreach ($data as $row) {
            $user_groups[$row['id']] = $row['name'];
        }

        $data = $this->getAclTable()->aclSections($module_id);
        $module_data = array();
        $module_data['module_components'] = array();
        foreach ($data as $row) {
            if ($row['parent_section'] == 0) {
                $module_data['module_name'] = array(
                                                                            'id'    => $row['section_id'],
                                                                            'name'  => $row['section_name']
                                                                    );
            } else {
                $module_data['module_components'][$row['section_id']] = $row['section_name'];
            }
        }

                $data           = $this->getAclTable()->getGroupAcl($module_id);
                $saved_ACL  = array();
        foreach ($data as $row) {
            if (empty($saved_ACL[$row['section_id']])) {
                $saved_ACL[$row['section_id']] = array();
            }

            array_push($saved_ACL[$row['section_id']], $row['group_id']);
        }

        $acl_view = new ViewModel(
            array(
                                        'user_groups'  => $user_groups,
                                        'listenerObject' => $this->listenerObject,
                                        'module_data'  => $module_data,
                                        'module_id'    => $module_id,
                                        'acl_data'     => $saved_ACL
                                    )
        );
        return $acl_view;
    }

    public function ajaxAction()
    {
        $ajax_mode  = $this->getRequest()->getPost('ajax_mode', null);
        if ($ajax_mode == "save_acl") {
            $selected_componet = $this->getRequest()->getPost('selected_module', null);
            $selected_componet_arr = explode("-", $selected_componet);
            if ($selected_componet_arr[0] == 0) {
                $selected_componet_arr[0] = $selected_componet_arr[1];
            }

            $allowed_users = json_decode($this->getRequest()->getPost('allowed_users', null));
            $denied_users = json_decode($this->getRequest()->getPost('denied_users', null));

            $allowed_users = array_unique($allowed_users);
            $denied_users = array_unique($denied_users);

                        // Delete Saved ACL Data
                        $data   = $this->getAclTable()->deleteGroupACL($selected_componet_arr[0], $selected_componet_arr[1]);
                        $data   = $this->getAclTable()->deleteUserACL($selected_componet_arr[0], $selected_componet_arr[1]);

                        // Allowed
            foreach ($allowed_users as $allowed_user) {
                $id = str_replace("li_user_group_allowed_", "", $allowed_user);
                $arr_id = explode("-", $id);

                if ($arr_id[1] == 0) {
                    $data   = $this->getAclTable()->insertGroupACL($selected_componet_arr[0], $arr_id[0], $selected_componet_arr[1], 1);
                } else {
                                        $data   = $this->getAclTable()->insertUserACL($selected_componet_arr[0], $arr_id[1], $selected_componet_arr[1], 1);
                }
            }

            // Denied
            foreach ($denied_users as $denied_user) {
                $id = str_replace("li_user_group_denied_", "", $denied_user);
                $arr_id = explode("-", $id);

                if ($arr_id[1] == 0) {
                                        $data   = $this->getAclTable()->insertGroupACL($selected_componet_arr[0], $arr_id[0], $selected_componet_arr[1], 0);
                } else {
                                        $data   = $this->getAclTable()->insertuserACL($selected_componet_arr[0], $arr_id[1], $selected_componet_arr[1], 0);
                }
            }
        } elseif ($ajax_mode == "rebuild") {
            $selected_componet = $_REQUEST['selected_module'];
            $selected_componet_arr = explode("-", $selected_componet);
            if ($selected_componet_arr[0] == 0) {
                $selected_componet_arr[0] = $selected_componet_arr[1];
            }

            $array_users_allowed = array();
            $array_users_denied = array();
            $array_groups_allowed = array();
            $array_groups_denied = array();

                      $res_users   = $this->getAclTable()->getAclDataUsers($selected_componet_arr[1]);
            foreach ($res_users as $row) {
                if ($row['allowed'] == 1) {
                    if (!$array_users_allowed[$row['group_id']]) {
                        $array_users_allowed[$row['group_id']] = array();
                    }

                    array_push($array_users_allowed[$row['group_id']], $row['user_id']);
                } else {
                    if (!$array_users_denied[$row['group_id']]) {
                        $array_users_denied[$row['group_id']] = array();
                    }

                    array_push($array_users_denied[$row['group_id']], $row['user_id']);
                }
            }

                        $res_group   = $this->getAclTable()->getAclDataGroups($selected_componet_arr[1]);
            foreach ($res_group as $row) {
                if ($row['allowed'] == 1) {
                    array_push($array_groups_allowed, $row['group_id']);
                } else {
                    array_push($array_groups_denied, $row['group_id']);
                }
            }

                        $arr_return = array();
                        $arr_return['group_allowed'] = $array_groups_allowed;
                        $arr_return['group_denied'] = $array_groups_denied;
                        $arr_return['user_allowed'] = $array_users_allowed;
                        $arr_return['user_denied'] = $array_users_denied;
                        echo json_encode($arr_return);
        } elseif ($ajax_mode == "save_acl_advanced") {
            $ACL_DATA  = json_decode($this->getRequest()->getPost('acl_data', null), true);
            $module_id = $this->getRequest()->getPost('module_id', null);
                        $this->getAclTable()->deleteModuleGroupACL($module_id);

            foreach ($ACL_DATA['allowed'] as $section_id => $sections) {
                foreach ($sections as $group_id) {
                                        $this->getAclTable()->deleteUserACL($module_id, $section_id);
                                        $this->getAclTable()->insertGroupACL($module_id, $group_id, $section_id, 1);
                }
            }

            foreach ($ACL_DATA['denied'] as $section_id => $sections) {
                foreach ($sections as $group_id) {
                                        $this->getAclTable()->deleteUserACL($module_id, $section_id);
                    $this->getAclTable()->insertGroupACL($module_id, $group_id, $section_id, 0);
                }
            }
        } elseif ($ajax_mode == "get_sections_by_module") {
            $module_id = $this->getRequest()->getPost('module_id', null);
                        $result = $this->getAclTable()->getModuleSections($module_id);

            $array_sections = array();
            foreach ($result as $row) {
                $array_sections[$row['section_id']] = $row['section_name'];
            }

            echo json_encode($array_sections);
        } elseif ($ajax_mode == "save_sections_by_module") {
            $module_id          = $this->getRequest()->getPost('mod_id', null);
            $parent_id          = $this->getRequest()->getPost('parent_id', null);
            $section_identifier = $this->getRequest()->getPost('section_identifier', null);
            $section_name       = $this->getRequest()->getPost('section_name', null);

            if (!$parent_id) {
                $parent_id = $module_id;
            }

            $current_section_id = $this->getAclTable()->getSectionsInsertId();
                        $this->getAclTable()->saveACLSections($module_id, $parent_id, $section_identifier, $section_name, $current_section_id);
        }

        exit();
    }


    /**
     *
     * Function to Print Componets Tree Structure
     * @param String $currentParent Root Node of Tree
     * @param String $currLevel Current Depth of Tree
     * @param String $prevLevel Prev Depth of Tree
     *
     **/
    private function createTreeView($array, $currentParent, $currLevel = 0, $prevLevel = -1)
    {
      /** Html Escape Function */
        $escapeHtml         = $this->htmlEscaper;

        foreach ($array as $categoryId => $category) {
            if ($category['name'] == '') {
                continue;
            }

            if ($currentParent == $category['parent_id']) {
                if ($currLevel > $prevLevel) {
                    echo " <ul> ";
                }

                if ($currLevel == $prevLevel) {
                    echo " </li> ";
                }

                $class = "";
                echo '<li id="' . $category['parent_id'] . "-" . $category['id'] . '" value="' . $escapeHtml($category['name']) . '" ' . $escapeHtml($class) . ' ><div onclick="selectThis(\'' . $escapeHtml($category['parent_id']) . '-' . $escapeHtml($category['id']) . '\');rebuild();" class="list">' . $escapeHtml($category['name']) . "</div>";
                if ($currLevel > $prevLevel) {
                    $prevLevel = $currLevel;
                }

                $currLevel++;
                $this->createTreeView($array, $categoryId, $currLevel, $prevLevel);
                $currLevel--;
            }
        }

        if ($currLevel == $prevLevel) {
            echo "</li></ul> ";
        }
    }

    /**
     *
     * Function to Print User group Tree Structure
     * @param String $id String to Prepend with <li> Id
     * @param String $visibility <li> Visibility
     * @param String $dragabble Class to Make <li> Title Draggable
     * @param String $li_class <li> Class Name
     *
     **/
    private function createUserGroups($id = "user_group_", $visibility = "", $dragabble = "draggable", $li_class = "")
    {
        /** Html Escape Function */
        $escapeHtml         = $this->htmlEscaper;

        $output_string = "";
        $res_users = $this->getAclTable()->aclUserGroupMapping();

        $tempList  = array();
        foreach ($res_users as $row) {
            $tempList[$row['group_id']]['group_name'] = $row['group_name'];
            $tempList[$row['group_id']]['group_id'] = $row['group_id'];
            $tempList[$row['group_id']]['items'][] = $row;
        }

        $output_string .= '<ul>';
        foreach ($tempList as $groupID => $tempListRow) {
            $output_string .= '<li ' . $li_class . ' id="li_' . $id . $tempListRow['group_id'] . '-0" style="' . $visibility . '"><div class="' . $escapeHtml($dragabble) . '" id="' . $id . $tempListRow['group_id'] . '-0" >' . $escapeHtml($tempListRow['group_name']) . '</div>';
            if (!empty($tempListRow['items'])) {
                $output_string .= '<ul>';
                foreach ($tempListRow['items'] as $key => $itemRow) {
                     $output_string .= '<li ' . $li_class . ' id="li_' . $id . $itemRow['group_id'] . '-' . $itemRow['user_id'] . '" style="' . $visibility . '"><div class="' . $escapeHtml($dragabble) . '" id="' . $id . $itemRow['group_id'] . '-' . $itemRow['user_id'] . '">' . $escapeHtml($itemRow['display_name']) . '</div></li>';
                }

                $output_string .= '</ul>';
            }

            $output_string .= '</li>';
        }

        $output_string .= '</ul>';
        return $output_string;
    }

    /**
     * Table Gateway
     *
     * @return \Acl\Model\AclTable
     */
    public function getAclTable()
    {
        return $this->aclTable;
    }
}
