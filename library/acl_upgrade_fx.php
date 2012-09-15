<?php
/**
* Upgrading functions of access controls.
*
* Functions to allow safe access control modifications
* during upgrading.
*
* Copyright (C) 2012 Brady Miller <brady@sparmy.com>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package   OpenEMR
* @author    Brady Miller <brady@sparmy.com>
* @link      http://www.open-emr.org
*/

/**
 * Returns the current access control version.
 *
 * @return  integer  The current access control version.
 */
function get_acl_version() {
  $acl_version = sqlQuery("SELECT `v_acl` FROM `version`");
  return $acl_version['v_acl'];
}

/**
 * Records the access control version.
 *
 * @param  integer  $acl_version  access control version
 */
function set_acl_version($acl_version) {
  sqlStatement("UPDATE `version` SET `v_acl` = ?", array($acl_version) );
}

/**
 * Function will return an array that contains the ACL ID number. It will also check to ensure
 * the ACL exist and is not duplicated.
 *
 * @param  string  $title         Title of group.
 * @param  string  $return_value  What the acl returns), usually 'write' or 'addonly'
 * @return array                  An array that contains the ACL ID number.
 */
function getAclIdNumber($title, $return_value) {
        global $gacl;
        $temp_acl_id_array  = $gacl->search_acl(FALSE, FALSE, FALSE, FALSE, $title, FALSE, FALSE, FALSE, $return_value);
        switch (count($temp_acl_id_array)) {
                case 0:
                        echo "<B>ERROR</B>, '$title' group '$return_value' ACL does not exist.</BR>";
                        break;
                case 1:
                        echo "'$title' group '$return_value' ACL is present.</BR>";
                        break;
                default:
                        echo "<B>ERROR</B>, Multiple '$title' group '$return_value' ACLs are present.</BR>";
                        break;
        }
        return $temp_acl_id_array;
}

/**
 * Function will add an ACL (if doesn't already exist).
 * It will also place the acl in the group, or will CREATE a new group.
 * It will return the ID number of the acl (created or old)
 *
 * @param   string  $title         Title of group.
 * @param   string  $name          name of acl
 * @param   string  $return_value  What the acl returns, usually 'write' or 'addonly'
 * @param   string  $note          description of acl
 * @return  array                  ID number of the acl (created or old)
 */
function addNewACL($title, $name, $return_value, $note) {
        global $gacl;
        $temp_acl_id_array  = $gacl->search_acl(FALSE, FALSE, FALSE, FALSE, $title, FALSE, FALSE, FALSE, $return_value);
        switch (count($temp_acl_id_array)) {
                case 0:
                        $group_id = $gacl->get_group_id($name, $title, 'ARO');
                        if ($group_id) {
                            //group already exist, so just create acl
                            $temp_acl_id = $gacl->add_acl(array("placeholder"=>array("filler")), NULL, array($group_id), NULL, NULL, 1, 1, $return_value, $note);
                            if ($temp_acl_id) {
                                 echo "The '$title' group already exist.</BR>";
                                 echo "The '$title' group '$return_value' ACL has been successfully added.</BR>";
                                 $temp_acl_id_array = array($temp_acl_id);
                            }
                            else {
                                 echo "The '$title' group already exist.</BR>";
                                 echo "<B>ERROR</B>, Unable to create the '$title' group '$return_value' ACL.</BR>";
                            }
                        }
                        else {
                            //create group, then create acl
                            $parent_id = $gacl->get_root_group_id();
                            $aro_id = $gacl->add_group($name, $title, $parent_id, 'ARO');
                            $temp_acl_id = $gacl->add_acl(array("placeholder"=>array("filler")), NULL, array($aro_id), NULL, NULL, 1, 1, $return_value, $note);
                            if ($aro_id ) {
                                echo "The '$title' group has been successfully added.</BR>";
                            }
                            else {
                                echo "<B>ERROR</B>, Unable to create the '$title' group.</BR>";
                            }
                            if ($temp_acl_id) {
                                echo "The '$title' group '$return_value' ACL has been successfully added.</BR>";
                                $temp_acl_id_array = array($temp_acl_id);
                            }
                            else {
                                echo "<B>ERROR</B>, Unable to create the '$title' group '$return_value' ACL.</BR>";
                            }
                        }
                        break;
                case 1:
                        echo "'$title' group '$return_value' ACL already exist.</BR>";
                        break;

                default:
                        echo "<B>ERROR</B>, Multiple '$title' group '$return_value' ACLs are present.</BR>";
                        break;
        }
        return $temp_acl_id_array;
}

/**
 * Function to add an object section.
 * It will check to ensure the object section doesn't already exist.
 *
 * @param  string  $name   identifier of section
 * @param  string  $title  Title o object.
 */
function addObjectSectionAcl($name, $title) {
        global $gacl;
        if ($gacl->get_object_section_section_id($title, $name, 'ACO')) {
                echo "The '$title' object section already exist.</BR>";
        }
        else {
                $tmp_boolean = $gacl->add_object_section($title , $name, 10, 0, 'ACO');
                if ($tmp_boolean) {
                        echo "The '$title' object section has been successfully added.</BR>";
                }
                else {
                        echo "<B>ERROR</B>,unable to create the '$title' object section.</BR>";
                }
        }
        return;
}


/**
 * Function to add an object.
 * It will check to ensure the object doesn't already exist.
 *
 * @param  string  $section_name   Identifier of section
 * @param  string  $section_title  Title of section
 * @param  string  $object_name    Identifier of object
 * @param  string  $object_title   Title of object
 */
function addObjectAcl($section_name, $section_title, $object_name, $object_title) {
        global $gacl;
        if ($gacl->get_object_id($section_name, $object_name, 'ACO')) {
                echo "The '$object_title' object in the '$section_title' section already exist.</BR>";
        }
        else {
                $tmp_boolean = $gacl->add_object($section_name, $object_title, $object_name, 10, 0, 'ACO');
                if ($tmp_boolean) {
                        echo "The '$object_title' object in the '$section_title' section has been successfully added.</BR>";
                }
                else {
                        echo "<B>ERROR</B>,unable to create the '$object_title' object in the '$section_title' section.</BR>";
                }
        }
        return;
}

/**
 * Function to add an object and set the 'order' variable.
 * It will check to ensure the object doesn't already exist.
 *
 * @param  string  $section_name   Identifier of section
 * @param  string  $section_title  Title of section
 * @param  string  $object_name    Identifier of object
 * @param  string  $object_title   Title of object
 * @param  string  $order_number   number to determine order in list. used in sensitivities to order the choices in openemr
 */
function addObjectAclWithOrder($section_name, $section_title, $object_name, $object_title, $order_number) {
        global $gacl;
        if ($gacl->get_object_id($section_name, $object_name, 'ACO')) {
                echo "The '$object_title' object in the '$section_title' section already exist.</BR>";
        }
        else {
                $tmp_boolean = $gacl->add_object($section_name, $object_title, $object_name, $order_number, 0, 'ACO');
                if ($tmp_boolean) {
                        echo "The '$object_title' object in the '$section_title' section has been successfully added.</BR>";
                }
                else {
                        echo "<B>ERROR</B>,unable to create the '$object_title' object in the '$section_title' section.</BR>";
                }
        }
        return;
}

/**
 * Function to edit an object and set the 'order' variable.
 * It will check to ensure the object doesn't already exist, and hasn't been upgraded yet.
 *
 * @param  string  $section_name   Identifier of section
 * @param  string  $section_title  Title of section
 * @param  string  $object_name    Identifier of object
 * @param  string  $object_title   Title of object
 * @param  string  $order_number   number to determine order in list. used in sensitivities to order the choices in openemr
 */
function editObjectAcl($section_name, $section_title, $object_name, $object_title, $order_number) {
        global $gacl;
        $tmp_objectID = $gacl->get_object_id($section_name, $object_name, 'ACO');
        if ($tmp_objectID) {
                $tmp_object = $gacl->get_object_data($tmp_objectID, 'ACO');
                if ($tmp_object[0][2] ==  $order_number &&
                    $tmp_object[0][0] ==  $section_name &&
                    $tmp_object[0][1] ==  $object_name &&
                    $tmp_object[0][3] ==  $object_title) {
                        echo "The '$object_title' object in the '$section_title' section has already been updated.</BR>";
                }
                else {
                        $tmp_boolean = $gacl->edit_object($tmp_objectID, $section_name, $object_title, $object_name, $order_number, 0, 'ACO');
                        if ($tmp_boolean) {
                                echo "The '$object_title' object in the '$section_title' section has been successfully updated.</BR>";
                        }
                        else {
                                echo "<B>ERROR</B>,unable to update the '$object_title' object in the '$section_title' section.</BR>";
                        }
                }
        }
        else {
                echo "<B>ERROR</B>, the '$object_title' object in the '$section_title' section does not exist.</BR>";
        }
        return;
}

/**
 * Update the ACL.
 * It will check to ensure the ACL hasn't already been updated.
 *
 * @param  array   $array_acl_id_number   Array containing hopefully one element, which is an integer, and is identifier of acl to be updated.
 * @param  string  $group_title           Title of group.
 * @param  string  $object_section_name   Identifier of section
 * @param  string  $object_section_title  Title of section
 * @param  string  $object_name           Identifier of object
 * @param  string  $object_title          Title of object
 * @param  string  $acl_return_value      What the acl returns (string), usually 'write', 'addonly', 'wsome' or 'view'
 */
function updateAcl($array_acl_id_number, $group_title, $section_name, $section_title, $object_name, $object_title, $return_value) {
        global $gacl;
        $tmp_array = $gacl->search_acl($section_name, $object_name, FALSE, FALSE, $group_title, FALSE, FALSE, FALSE, $return_value);
        switch (count($tmp_array)) {
                case 0:
                        $tmp_boolean = @$gacl->append_acl($array_acl_id_number[0], NULL, NULL, NULL, NULL, array($section_name=>array($object_name)));
                        if ($tmp_boolean){
                                echo "Successfully placed the '$object_title' object of the '$section_title' section into the '$group_title' group '$return_value' ACL.</BR>";
                        }
                        else {
                                echo "<B>ERROR</B>,unable to place the '$object_title' object of the '$section_title' section into the '$group_title' group '$return_value' ACL.</BR>";
                        }
                        break;
                case 1:
                        echo "The '$object_title' object of the '$section_title' section is already found in the '$group_title' group '$return_value' ACL.</BR>";
                        break;
                default:
                        echo "<B>ERROR</B>, Multiple '$group_title' group '$return_value' ACLs with the '$object_title' object of the '$section_title' section are present.</BR>";
                        break;
        }
        return;
}
?>
