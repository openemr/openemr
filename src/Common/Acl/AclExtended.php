<?php

/**
 * AclExtended class.
 *
 *   Provides Acl functions that are above and beyond the standard acl checks.
 *
 *   Note that it stores a static GaclApi object to improve performance (this avoids doing
 *    separate database connection for every call to GaclApi)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Acl;

use OpenEMR\Gacl\GaclApi;
use OpenEMR\Services\VersionService;

class AclExtended
{
    // Holds the static GaclApi object
    private static $gaclApiObject;

    // Collect the stored GaclApi object (create it if it doesn't yet exist)
    //  Sharing one object will prevent opening a database connection for every call to GaclApi.
    private static function collectGaclApiObject()
    {
        if (!is_object(self::$gaclApiObject)) {
            // GaclApi object does not yet exist, so create it
            self::$gaclApiObject = new GaclApi();
        }
        return self::$gaclApiObject;
    }

    // Return an array keyed on squad ACO names.
    // This is only applicable for sports team use.
    //
    public static function aclGetSquads()
    {
        $squads = self::aclGetSectionAcos('squads');
        uasort($squads, "self::aclSquadCompare");
        return $squads;
    }

    // Return an array keyed on encounter sensitivity level ACO names.
    // Sensitivities are useful when some encounter notes are not
    // medically sensitive (e.g. a physical fitness test), and/or if
    // some will be "for doctor's eyes only" (e.g. STD treatment).
    //
    // When a non-blank sensitivity value exists in the new encounter
    // form, it names an additional ACO required for access to all forms
    // in the encounter.  If you want some encounters to be non-sensitive,
    // then you also need some default nonblank sensitivity for normal
    // encounters, as well as greater encounter notes permissions for
    // those allowed to view non-sensitive encounters.
    //
    public static function aclGetSensitivities()
    {
        return self::aclGetSectionAcos('sensitivities');
    }

    // Get the ACO name/value pairs for a designated section.  Each value
    // is an array (section_value, value, order_value, name, hidden).
    //
    private static function aclGetSectionAcos($section)
    {
        $gacl = self::collectGaclApiObject();
        $arr1 = $gacl->get_objects($section, 1, 'ACO');
        $arr = array();
        if (!empty($arr1[$section])) {
            foreach ($arr1[$section] as $value) {
                $odata = $gacl->get_object_data($gacl->get_object_id($section, $value, 'ACO'), 'ACO');
                $arr[$value] = $odata[0];
            }
        }
        return $arr;
    }

    // Sort squads by their order value.  Used only by aclGetSquads().
    private static function aclSquadCompare($a, $b)
    {
        if ($a[2] == $b[2]) {
            // If order value is the same, sort by squad name.
            if ($a[3] == $b[3]) {
                return 0;
            }
            return ($a[3] < $b[3]) ? -1 : 1;
        }
        return ($a[2] < $b[2]) ? -1 : 1;
    }

    //
    // Returns true if aco exist
    // Returns false if aco doesn't exist
    //    $section_name = name of section (string)
    //    $aco_name = name of aco (string)
    //
    public static function acoExist($section_name, $aco_name)
    {
        $gacl = self::collectGaclApiObject();
        $aco_id = $gacl->get_object_id($section_name, $aco_name, 'ACO');
        if ($aco_id) {
            return true;
        }
    }

    //
    // Returns a sorted array of all available Group Titles.
    //
    public static function aclGetGroupTitleList($include_superusers = true)
    {
        $gacl = self::collectGaclApiObject();
        $parent_id = $gacl->get_root_group_id();
        $arr_group_ids = $gacl->get_group_children($parent_id, 'ARO', 'RECURSE');
        $arr_group_titles = array();
        foreach ($arr_group_ids as $value) {
            $arr_group_data = $gacl->get_group_data($value, 'ARO');
            // add if $include_superusers is true or group not include admin|super rule.
            if ($include_superusers || !self::isGroupIncludeSuperuser($arr_group_data[3])) {
                $arr_group_titles[$value] = $arr_group_data[3];
            }
        }
        sort($arr_group_titles);
        return $arr_group_titles;
    }

    //
    // Returns a sorted array of group Titles that a user belongs to.
    // Returns 0 if does not belong to any group yet.
    //   $user_name = Username, which is login name.
    //
    public static function aclGetGroupTitles($user_name)
    {
        $gacl = self::collectGaclApiObject();
        $user_aro_id = $gacl->get_object_id('users', $user_name, 'ARO');
        if ($user_aro_id) {
            $arr_group_id = $gacl->get_object_groups($user_aro_id, 'ARO', 'NO_RECURSE');
            if ($arr_group_id) {
                foreach ($arr_group_id as $key => $value) {
                    $arr_group_data = $gacl->get_group_data($value, 'ARO');
                    $arr_group_titles[$key] = $arr_group_data[3];
                }
                sort($arr_group_titles);
                return $arr_group_titles;
            }
        }
    }

    //
    // This will place the user aro object into selected group(s)
    // It uses the setUserAro() function
    //   $username = username (string)
    //   $group = title of group(s) (string or array)
    //
    public static function addUserAros($username, $group)
    {
        $current_user_groups = self::aclGetGroupTitles($username);
        if (!$current_user_groups) {
            $current_user_groups = array();
        }
        if (is_array($group)) {
            foreach ($group as $value) {
                if (!in_array($value, $current_user_groups)) {
                    array_push($current_user_groups, $value);
                }
            }
        } else {
            if (!in_array($group, $current_user_groups)) {
                array_push($current_user_groups, $group);
            }
        }
        $user_data = sqlFetchArray(sqlStatement("SELECT * FROM users WHERE username = ?", array($username)));
        self::setUserAro(
            $current_user_groups,
            $username,
            $user_data["fname"],
            $user_data["mname"],
            $user_data["lname"]
        );
        return;
    }

    //
    // This will remove the user aro object from the selected group(s)
    // It uses the setUserAro() function
    //   $username = username (string)
    //   $group = title of group(s) (string or array)
    //
    public static function removeUserAros($username, $group)
    {
        $current_user_groups = self::aclGetGroupTitles($username);
        $new_user_groups = array();
        if (is_array($group)) {
            foreach ($current_user_groups as $value) {
                if (!in_array($value, $group)) {
                    array_push($new_user_groups, $value);
                }
            }
        } else {
            foreach ($current_user_groups as $value) {
                if ($value != $group) {
                    array_push($new_user_groups, $value);
                }
            }
        }
        $user_data = sqlFetchArray(sqlStatement("SELECT * FROM users WHERE username = ?", array($username)));
        self::setUserAro(
            $new_user_groups,
            $username,
            $user_data["fname"],
            $user_data["mname"],
            $user_data["lname"]
        );
        return;
    }

    //
    // This will either create or edit a user aro object, and then place it
    // in the requested groups. It will not allow removal of the 'admin'
    // user or gacl_protected users from the 'admin' group.
    //   $arr_group_titles = titles of the groups that user will be added to.
    //   $user_name = username, which is login name.
    //   $first_name = first name
    //   $middle_name = middle name
    //   $last_name = last name
    //
    public static function setUserAro($arr_group_titles, $user_name, $first_name, $middle_name, $last_name)
    {
        $gacl = self::collectGaclApiObject();

        //see if this user is gacl protected (ie. do not allow
        //removal from the Administrators group)
        require_once(dirname(__FILE__) . '/../../../library/user.inc');
        require_once(dirname(__FILE__) . '/../../../library/calendar.inc');
        $userNametoID = getIDfromUser($user_name);
        if (checkUserSetting("gacl_protect", "1", $userNametoID) || $user_name == "admin") {
            $gacl_protect = true;
        } else {
            $gacl_protect = false;
        }

        //get array of all available group ID numbers
        $parent_id = $gacl->get_root_group_id();
        $arr_all_group_ids = $gacl->get_group_children($parent_id, 'ARO', 'RECURSE');

        //Cycle through ID array to find and process each selected group
        //Create a counter since processing of first hit is unique
        $counter = 0;
        foreach ($arr_all_group_ids as $value) {
            $arr_group_data = $gacl->get_group_data($value, 'ARO');
            if (
                (empty($arr_group_titles)) ||
                (in_array($arr_group_data[3], $arr_group_titles))
            ) {
                //We have a hit, so need to add group and increment counter
                // because processing of first hit is unique
                //This will also deal with an empty $arr_group_titles array
                // removing user from all groups unless 'admin'
                $counter = $counter + 1;
                //create user full name field
                if ($middle_name) {
                    $full_name = $first_name . " " . $middle_name . " " . $last_name;
                } else {
                    if ($last_name) {
                        $full_name = $first_name . " " . $last_name;
                    } else {
                        $full_name = $first_name;
                    }
                }

                //If this is not the first group to be added, then will skip below
                // and will be added. If this is the first group, then need to
                // go thru several steps before adding the group.
                if ($counter == 1) {
                    //get ID of user ARO object, if it exist
                    $user_aro_id = $gacl->get_object_id('users', $user_name, 'ARO');
                    if ($user_aro_id) {
                        //user ARO object already exist, so will edit it
                        $gacl->edit_object($user_aro_id, 'users', $full_name, $user_name, 10, 0, 'ARO');

                        //remove all current user ARO object group associations
                        $arr_remove_group_ids = $gacl->get_object_groups($user_aro_id, 'ARO', 'NO_RECURSE');
                        foreach ($arr_remove_group_ids as $value2) {
                            $gacl->del_group_object($value2, 'users', $user_name, 'ARO');
                        }
                    } else {
                        //user ARO object does not exist, so will create it
                        $gacl->add_object('users', $full_name, $user_name, 10, 0, 'ARO');
                    }
                }

                //place the user ARO object in the selected group (if group(s) is selected)
                if (!empty($arr_group_titles)) {
                    $gacl->add_group_object($value, 'users', $user_name, 'ARO');
                }

                //
                //Below will not allow 'admin' or gacl_protected user to be removed from 'admin' group
                //
                if ($gacl_protect) {
                    $boolean_admin = 0;
                    $admin_id = $gacl->get_object_id('users', $user_name, 'ARO');
                    $arr_admin = $gacl->get_object_groups($admin_id, 'ARO', 'NO_RECURSE');
                    foreach ($arr_admin as $value3) {
                        $arr_admin_data = $gacl->get_group_data($value3, 'ARO');
                        if (strcmp($arr_admin_data[2], 'admin') == 0) {
                            $boolean_admin = 1;
                        }
                    }
                    if (!$boolean_admin) {
                        foreach ($arr_all_group_ids as $value4) {
                            $arr_temp = $gacl->get_group_data($value4, 'ARO');
                            if ($arr_temp[2] == 'admin') {
                                $gacl->add_group_object($value4, 'users', $user_name, 'ARO');
                            }
                        }
                    }
                }
            }
            //if array of groups was empty, then we are done, and can break from loop
            if (empty($arr_group_titles)) {
                break;
            }
        }
        return true;
    }

    //
    // Returns true if acl exist
    // Returns false if acl doesn't exist
    //  EITHER $title or $name is required(send FALSE in variable
    //  not being used). If both are sent, then only $title will be
    //  used.
    //  $return_value is required
    //    $title = title of acl (string)
    //    $name = name of acl (string)
    //    $return_value = return value of acl (string)
    //
    public static function aclExist($title, $name, $return_value)
    {
        $gacl = self::collectGaclApiObject();
        if (!$name) {
            $acl = $gacl->search_acl(false, false, false, false, $title, false, false, false, $return_value);
        } else {
            if (!$title) {
                $group_id = $gacl->get_group_id($name, null, 'ARO');
                if ($group_id) {
                    $group_data = $gacl->get_group_data($group_id, 'ARO');
                    $acl = $gacl->search_acl(false, false, false, false, $group_data[3], false, false, false, $return_value);
                } else {
                    return false;
                }
            } else {
                $acl = $gacl->search_acl(false, false, false, false, $title, false, false, false, $return_value);
            }
        }
        if (!empty($acl)) {
            return true;
        } else {
            return false;
        }
    }

    //
    // This will add a new acl and group(if group doesn't yet exist)
    // with one aco in it.
    //   $acl_title = title of acl (string)
    //   $acl_name = name of acl (string)
    //   $return_value = return value of acl (string)
    //   $note = description of acl (array)
    //
    public static function aclAdd($acl_title, $acl_name, $return_value, $note)
    {
        $gacl = self::collectGaclApiObject();
        $group_id = $gacl->get_group_id($acl_name, $acl_title, 'ARO');
        if ($group_id) {
            //group already exist, so just create acl
            $gacl->add_acl(
                array("placeholder" => array("filler")),
                null,
                array($group_id),
                null,
                null,
                1,
                1,
                $return_value,
                $note
            );
        } else {
            //create group, then create acl
            $parent_id = $gacl->get_root_group_id();
            $aro_id = $gacl->add_group($acl_name, $acl_title, $parent_id, 'ARO');
            $gacl->add_acl(
                array("placeholder" => array("filler")),
                null,
                array($aro_id),
                null,
                null,
                1,
                1,
                $return_value,
                $note
            );
        }
        return;
    }

    //
    // This will remove acl. It will also remove group(if the group
    // is no longer associated with any acl's).
    //   $acl_title = title of acl (string)
    //   $acl_name = name of acl (string)
    //   $return_value = return value of acl (string)
    //   $note = description of acl (array)
    //
    public static function aclRemove($acl_title, $return_value)
    {
        $gacl = self::collectGaclApiObject();
        //First, delete the acl
        $acl_id = $gacl->search_acl(false, false, false, false, $acl_title, false, false, false, $return_value);
        $gacl->del_acl($acl_id[0]);
        //Then, remove the group(if no more acl's are remaining)
        $acl_search = $gacl->search_acl(false, false, false, false, $acl_title, false, false, false, false);
        if (empty($acl_search)) {
            $group_id = $gacl->get_group_id(null, $acl_title, 'ARO');
            $gacl->del_group($group_id, true, 'ARO');
        }
        return;
    }

    //
    // This will place the aco(s) into the selected acl
    //   $acl_title = title of acl (string)
    //   $return_value = return value of acl (string)
    //   $aco_id = id of aco (array)
    //
    public static function aclAddAcos($acl_title, $return_value, $aco_id)
    {
        $gacl = self::collectGaclApiObject();
        $acl_id = $gacl->search_acl(false, false, false, false, $acl_title, false, false, false, $return_value);
        foreach ($aco_id as $value) {
            $aco_data = $gacl->get_object_data($value, 'ACO');
            $aco_section = $aco_data[0][0];
            $aco_name = $aco_data[0][1];
            $gacl->append_acl($acl_id[0], null, null, null, null, array($aco_section => array($aco_name)));
        }
        return;
    }

    //
    // This will remove the aco(s) from the selected acl
    //  Note if all aco's are removed, then will place the filler-placeholder
    //  into the acl to avoid complete removal of the acl.
    //   $acl_title = title of acl (string)
    //   $return_value = return value of acl (string)
    //   $aco_id = id of aco (array)
    //
    public static function aclRemoveAcos($acl_title, $return_value, $aco_id)
    {
        $gacl = self::collectGaclApiObject();
        $acl_id = $gacl->search_acl(false, false, false, false, $acl_title, false, false, false, $return_value);

        // Check to see if removing all acos. If removing all acos then will
        //  ensure the filler-placeholder aco in acl to avoid complete
        //  removal of the acl.
        if (count($aco_id) == self::aclCountAcos($acl_title, $return_value)) {
            //1-get the filler-placeholder aco id
            $filler_aco_id = $gacl->get_object_id('placeholder', 'filler', 'ACO');
            //2-add filler-placeholder aco
            self::aclAddAcos($acl_title, $return_value, array($filler_aco_id));
            //3-ensure filler-placeholder aco is not to be deleted
            $safeListaco = self::removeElement($_POST["selection"], $filler_aco_id);
            //4-prepare to safely delete the acos
            $aco_id = $safeListaco;
        }

        foreach ($aco_id as $value) {
            $aco_data = $gacl->get_object_data($value, 'ACO');
            $aco_section = $aco_data[0][0];
            $aco_name = $aco_data[0][1];
            $gacl->shift_acl($acl_id[0], null, null, null, null, array($aco_section => array($aco_name)));
        }
        return;
    }

    //
    // This will return the number of aco objects
    //  in a specified acl.
    //   $acl_title = title of acl (string)
    //   $return_value = return value of acl (string)
    //
    private static function aclCountAcos($acl_title, $return_value)
    {
        $gacl = self::collectGaclApiObject();
        $acl_id = $gacl->search_acl(false, false, false, false, $acl_title, false, false, false, $return_value);
        $acl_data = $gacl->get_acl($acl_id[0]);
        $aco_count = 0;
        foreach ($acl_data['aco'] as $key => $value) {
            $aco_count = $aco_count + count($acl_data['aco'][$key]);
        }
        return $aco_count;
    }

    //
    // Function to remove an element from an array
    //
    private static function removeElement($arr, $val)
    {
        $arr2 = array();
        foreach ($arr as $value) {
            if ($value != $val) {
                array_push($arr2, $value);
            }
        }
        return $arr2;
    }

    // This generates an HTML options list for all ACOs.
    // The caller inserts this between <select> and </select> tags.
    //
    public static function genAcoHtmlOptions($default = '')
    {
        $acoArray = self::genAcoArray();
        $s = '';
        foreach ($acoArray as $section => $acos_array) {
            $s .= "<optgroup label='" . xla($section) . "'>\n";
            foreach ($acos_array as $aco_array) {
                $s .= "<option value='" . attr($aco_array['value']) . "'";
                if ($aco_array['value'] == $default) {
                    $s .= ' selected';
                }
                $s .= ">" . xlt($aco_array['name']) . "</option>";
            }
            $s .= "</optgroup>";
        }
        return $s;
    }


    // Returns array of all ACOs
    public static function genAcoArray()
    {
        $acoArray = array();
        $gacl = self::collectGaclApiObject();
        // collect and sort all aco objects
        $list_aco_objects = $gacl->get_objects(null, 0, 'ACO');
        ksort($list_aco_objects);
        foreach ($list_aco_objects as $seckey => $dummy) {
            if (empty($dummy)) {
                continue;
            }
            asort($list_aco_objects[$seckey]);
            $aco_section_data = $gacl->get_section_data($seckey, 'ACO');
            $aco_section_title = $aco_section_data[3];
            foreach ($list_aco_objects[$seckey] as $acokey) {
                $aco_id = $gacl->get_object_id($seckey, $acokey, 'ACO');
                $aco_data = $gacl->get_object_data($aco_id, 'ACO');
                $aco_title = $aco_data[0][3];
                $optkey = "$seckey|$acokey";
                $acoArray[$aco_section_title][$aco_id]['name'] = $aco_title;
                $acoArray[$aco_section_title][$aco_id]['value'] = $optkey;
            }
        }
        return $acoArray;
    }

    // check if aro group have superuser rule
    public static function isGroupIncludeSuperuser($aro_group_name)
    {
        $gacl = self::collectGaclApiObject();
        return empty($gacl->search_acl('admin', 'super', false, false, $aro_group_name)) ? false : true;
    }

    //
    // Returns acl listings(including return value) via xml message.
    //   $err = error strings (array)
    //
    public static function aclListingsXml($err)
    {
        $gacl = self::collectGaclApiObject();

        $message = "<?xml version=\"1.0\"?>\n" .
            "<response>\n";
        foreach (self::aclGetGroupTitleList() as $value) {
            $acl_id = $gacl->search_acl(false, false, false, false, $value, false, false, false, false);
            foreach ($acl_id as $value2) {
                $acl = $gacl->get_acl($value2);
                $ret = $acl["return_value"];
                $note = $acl["note"];

                // Modified 6-2009 by BM - Translate gacl group name if applicable
                //                         Translate return value
                //                         Translate description
                $message .= "\t<acl>\n" .
                    "\t\t<value>" . $value . "</value>\n" .
                    "\t\t<title>" . xl_gacl_group($value) . "</title>\n" .
                    "\t\t<returnid>" . $ret  . "</returnid>\n" .
                    "\t\t<returntitle>" . xl($ret)  . "</returntitle>\n" .
                    "\t\t<note>" . xl($note)  . "</note>\n" .
                    "\t</acl>\n";
            }
        }

        if (isset($err)) {
            foreach ($err as $value) {
                $message .= "\t<error>" . $value . "</error>\n";
            }
        }

        $message .= "</response>\n";
        return $message;
    }

    //
    // Return aco listings by sections(active and inactive lists)
    // via xml message.
    //   $group = group title (string)
    //   $return_value = return value (string)
    //   $err = error strings (array)
    //
    public static function acoListingsXml($group, $return_value, $err)
    {
        $gacl = self::collectGaclApiObject();

        //collect and sort all aco objects
        $list_aco_objects = $gacl->get_objects(null, 0, 'ACO');
        foreach ($list_aco_objects as $key => $value) {
            asort($list_aco_objects[$key]);
        }

        //collect aco objects within the specified acl(already sorted)
        $acl_id = $gacl->search_acl(false, false, false, false, $group, false, false, false, $return_value);
        $acl = $gacl->get_acl($acl_id[0]);
        $active_aco_objects = $acl["aco"];

        $message = "<?xml version=\"1.0\"?>\n" .
            "<response>\n" .
            "\t<inactive>\n";
        foreach ($list_aco_objects as $key => $value) {
            $counter = 0;
            foreach ($list_aco_objects[$key] as $value2) {
                if (!array_key_exists($key, $active_aco_objects) || !in_array($value2, $active_aco_objects[$key])) {
                    if ($counter == 0) {
                        $counter = $counter + 1;
                        $aco_section_data = $gacl->get_section_data($key, 'ACO');
                        $aco_section_title = $aco_section_data[3];

                        // Modified 6-2009 by BM - Translate gacl aco section name
                        $message .= "\t\t<section>\n" .
                            "\t\t\t<name>" . xl($aco_section_title) . "</name>\n";
                    }

                    $aco_id = $gacl->get_object_id($key, $value2, 'ACO');
                    $aco_data = $gacl->get_object_data($aco_id, 'ACO');
                    $aco_title = $aco_data[0][3];
                    $message .= "\t\t\t<aco>\n";

                    // Modified 6-2009 by BM - Translate gacl aco name
                    $message .= "\t\t\t\t<title>" . xl($aco_title) . "</title>\n";

                    $message .= "\t\t\t\t<id>" . $aco_id . "</id>\n";
                    $message .= "\t\t\t</aco>\n";
                }
            }

            if ($counter != 0) {
                $message .= "\t\t</section>\n";
            }
        }

        $message .= "\t</inactive>\n" .
            "\t<active>\n";
        foreach ($active_aco_objects as $key => $value) {
            $aco_section_data = $gacl->get_section_data($key, 'ACO');
            $aco_section_title = $aco_section_data[3];

            // Modified 6-2009 by BM - Translate gacl aco section name
            $message .= "\t\t<section>\n" .
                "\t\t\t<name>" . xl($aco_section_title) . "</name>\n";

            foreach ($active_aco_objects[$key] as $value2) {
                $aco_id = $gacl->get_object_id($key, $value2, 'ACO');
                $aco_data = $gacl->get_object_data($aco_id, 'ACO');
                $aco_title = $aco_data[0][3];
                $message .= "\t\t\t<aco>\n";

                // Modified 6-2009 by BM - Translate gacl aco name
                $message .= "\t\t\t\t<title>" . xl($aco_title) . "</title>\n";

                $message .= "\t\t\t\t<id>" . $aco_id . "</id>\n";
                $message .= "\t\t\t</aco>\n";
            }

            $message .= "\t\t</section>\n";
        }

        $message .= "\t</active>\n";
        if (isset($err)) {
            foreach ($err as $value) {
                $message .= "\t<error>" . $value . "</error>\n";
            }
        }

        $message .= "</response>\n";
        return $message;
    }

    //
    // Returns listing of all possible return values via xml message.
    //   $err = error strings (array)
    //
    public static function returnValuesXml($err)
    {
        $gacl = self::collectGaclApiObject();
        $returns = array();

        $message = "<?xml version=\"1.0\"?>\n" .
            "<response>\n";
        foreach (self::aclGetGroupTitleList() as $value) {
            $acl_id = $gacl->search_acl(false, false, false, false, $value, false, false, false, false);
            foreach ($acl_id as $value2) {
                $acl = $gacl->get_acl($value2);
                $ret = $acl["return_value"];
                if (!in_array($ret, $returns)) {
                    // Modified 6-2009 by BM - Translate return value
                    $message .= "\t<return>\n";
                    $message .= "\t\t<returnid>" . $ret  . "</returnid>\n";
                    $message .= "\t\t<returntitle>" . xl($ret)  . "</returntitle>\n";
                    $message .= "\t</return>\n";

                    array_push($returns, $ret);
                }
            }
        }

        if (isset($err)) {
            foreach ($err as $value) {
                $message .= "\t<error>" . $value . "</error>\n";
            }
        }

        $message .= "</response>\n";
        return $message;
    }

    /**
     * Returns the current access control version.
     *
     * @return  integer  The current access control version.
     */
    public static function getAclVersion()
    {
        $versionService = new VersionService();
        $version = $versionService->fetch();
        return $version['v_acl'];
    }

    /**
     * Records the access control version.
     *
     * @param  integer  $acl_version  access control version
     */
    public static function setAclVersion($acl_version)
    {
        $versionService = new VersionService();
        $version = $versionService->fetch();
        $version['v_acl'] = $acl_version;
        $versionService->update($version);
        return;
    }

    /**
     * Function will return an array that contains the ACL ID number. It will also check to ensure
     * the ACL exist and is not duplicated.
     *
     * @param  string  $title         Title of group.
     * @param  string  $return_value  What the acl returns), usually 'write' or 'addonly'
     * @return array                  An array that contains the ACL ID number.
     */
    public static function getAclIdNumber($title, $return_value)
    {
        $gacl = self::collectGaclApiObject();
        $temp_acl_id_array  = $gacl->search_acl(false, false, false, false, $title, false, false, false, $return_value);
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
    public static function addNewACL($title, $name, $return_value, $note)
    {
        $gacl = self::collectGaclApiObject();
        $temp_acl_id_array  = $gacl->search_acl(false, false, false, false, $title, false, false, false, $return_value);
        switch (count($temp_acl_id_array)) {
            case 0:
                $group_id = $gacl->get_group_id($name, $title, 'ARO');
                if ($group_id) {
                    //group already exist, so just create acl
                    $temp_acl_id = $gacl->add_acl(array("placeholder" => array("filler")), null, array($group_id), null, null, 1, 1, $return_value, $note);
                    if ($temp_acl_id) {
                        echo "The '$title' group already exist.</BR>";
                        echo "The '$title' group '$return_value' ACL has been successfully added.</BR>";
                        $temp_acl_id_array = array($temp_acl_id);
                    } else {
                        echo "The '$title' group already exist.</BR>";
                        echo "<B>ERROR</B>, Unable to create the '$title' group '$return_value' ACL.</BR>";
                    }
                } else {
                    //create group, then create acl
                    $parent_id = $gacl->get_root_group_id();
                    $aro_id = $gacl->add_group($name, $title, $parent_id, 'ARO');
                    $temp_acl_id = $gacl->add_acl(array("placeholder" => array("filler")), null, array($aro_id), null, null, 1, 1, $return_value, $note);
                    if ($aro_id) {
                        echo "The '$title' group has been successfully added.</BR>";
                    } else {
                        echo "<B>ERROR</B>, Unable to create the '$title' group.</BR>";
                    }

                    if ($temp_acl_id) {
                        echo "The '$title' group '$return_value' ACL has been successfully added.</BR>";
                        $temp_acl_id_array = array($temp_acl_id);
                    } else {
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
    public static function addObjectSectionAcl($name, $title)
    {
        $gacl = self::collectGaclApiObject();
        if ($gacl->get_object_section_section_id($title, $name, 'ACO')) {
            echo "The '$title' object section already exist.</BR>";
        } else {
            $tmp_boolean = $gacl->add_object_section($title, $name, 10, 0, 'ACO');
            if ($tmp_boolean) {
                echo "The '$title' object section has been successfully added.</BR>";
            } else {
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
    public static function addObjectAcl($section_name, $section_title, $object_name, $object_title)
    {
        $gacl = self::collectGaclApiObject();
        if ($gacl->get_object_id($section_name, $object_name, 'ACO')) {
            echo "The '$object_title' object in the '$section_title' section already exist.</BR>";
        } else {
            $tmp_boolean = $gacl->add_object($section_name, $object_title, $object_name, 10, 0, 'ACO');
            if ($tmp_boolean) {
                echo "The '$object_title' object in the '$section_title' section has been successfully added.</BR>";
            } else {
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
    public static function addObjectAclWithOrder($section_name, $section_title, $object_name, $object_title, $order_number)
    {
        $gacl = self::collectGaclApiObject();
        if ($gacl->get_object_id($section_name, $object_name, 'ACO')) {
            echo "The '$object_title' object in the '$section_title' section already exist.</BR>";
        } else {
            $tmp_boolean = $gacl->add_object($section_name, $object_title, $object_name, $order_number, 0, 'ACO');
            if ($tmp_boolean) {
                echo "The '$object_title' object in the '$section_title' section has been successfully added.</BR>";
            } else {
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
    public static function editObjectAcl($section_name, $section_title, $object_name, $object_title, $order_number)
    {
        $gacl = self::collectGaclApiObject();
        $tmp_objectID = $gacl->get_object_id($section_name, $object_name, 'ACO');
        if ($tmp_objectID) {
            $tmp_object = $gacl->get_object_data($tmp_objectID, 'ACO');
            if (
                $tmp_object[0][2] ==  $order_number &&
                $tmp_object[0][0] ==  $section_name &&
                $tmp_object[0][1] ==  $object_name &&
                $tmp_object[0][3] ==  $object_title
            ) {
                echo "The '$object_title' object in the '$section_title' section has already been updated.</BR>";
            } else {
                $tmp_boolean = $gacl->edit_object($tmp_objectID, $section_name, $object_title, $object_name, $order_number, 0, 'ACO');
                if ($tmp_boolean) {
                    echo "The '$object_title' object in the '$section_title' section has been successfully updated.</BR>";
                } else {
                    echo "<B>ERROR</B>,unable to update the '$object_title' object in the '$section_title' section.</BR>";
                }
            }
        } else {
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
    public static function updateAcl($array_acl_id_number, $group_title, $section_name, $section_title, $object_name, $object_title, $return_value)
    {
        $gacl = self::collectGaclApiObject();
        $tmp_array = $gacl->search_acl($section_name, $object_name, false, false, $group_title, false, false, false, $return_value);
        switch (count($tmp_array)) {
            case 0:
                $tmp_boolean = @$gacl->append_acl($array_acl_id_number[0], null, null, null, null, array($section_name => array($object_name)));
                if ($tmp_boolean) {
                    echo "Successfully placed the '$object_title' object of the '$section_title' section into the '$group_title' group '$return_value' ACL.</BR>";
                } else {
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
}
