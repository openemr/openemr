<?php

/**
 * AclMain class.
 *
 *   Provides Acl functions for standard acl checks.
 *
 *   Note that it stores a static Gacl object to improve performance (this avoids doing
 *    separate database connection for every call to Gacl)
 *
 * The following Access Control Objects (ACO) are currently supported.
 * These are the "things to be protected":
 *
 * Section "admin" (Administration):
 *   super       Superuser - can delete patients, encounters, issues
 *   calendar    Calendar Settings
 *   database    Database Reporting
 *   forms       Forms Administration
 *   practice    Practice Settings
 *   superbill   Superbill Codes Administration
 *   users       Users/Groups/Logs Administration
 *   batchcom    Batch Communication Tool
 *   language    Language Interface Tool
 *   drugs       Pharmacy Dispensary
 *   acl         ACL Administration
 *   multipledb  Multipledb
 *   menu        Menu
 *   manage_modules Manage modules
 *
 * Section "acct" (Accounting):
 *   bill        Billing (write optional)
 *   disc        Allowed to discount prices (in Fee Sheet or Checkout form)
 *   eob         EOB Data Entry
 *   rep         Financial Reporting - my encounters
 *   rep_a       Financial Reporting - anything
 *
 * Section "patients" (Patient Information):
 *   appt        Appointments (write,wsome optional)
 *   demo        Demographics (write,addonly optional)
 *   med         Medical Records and History (write,addonly optional)
 *   trans       Transactions, e.g. referrals (write optional)
 *   docs        Documents (write,addonly optional)
 *   docs_rm     Documents Delete
 *   pat_rep     Patient Report
 *   notes       Patient Notes (write,addonly optional)
 *   sign        Sign Lab Results (write,addonly optional)
 *   reminder    Patient Reminders (write,addonly optional)
 *   alert       Clinical Reminders/Alerts (write,addonly optional)
 *   disclosure  Disclosures (write,addonly optional)
 *   rx          Prescriptions (write,addonly optional)
 *   amendment   Amendments (write,addonly optional)
 *   lab         Lab Results (write,addonly optional)
 *
 * Section "encounters" (Encounter Information):
 *   auth        Authorize - my encounters
 *   auth_a      Authorize - any encounters
 *   coding      Coding - my encounters (write,wsome optional)
 *   coding_a    Coding - any encounters (write,wsome optional)
 *   notes       Notes - my encounters (write,addonly optional)
 *   notes_a     Notes - any encounters (write,addonly optional)
 *   date_a      Fix encounter dates - any encounters
 *   relaxed     Less-private information (write,addonly optional)
 *               (e.g. the Sports Fitness encounter form)
 *
 * Section "squads" applies to sports team use only:
 *   acos in this section define the user-specified list of squads
 *
 * Section "sensitivities" (Sensitivities):
 *   normal     Normal
 *   high       High
 *
 * Section "lists" (Lists):
 *   default    Default List (write,addonly optional)
 *   state      State List (write,addonly optional)
 *   country    Country List (write,addonly optional)
 *   language   Language List (write,addonly optional)
 *   ethrace    Ethnicity-Race List (write,addonly optional)
 *
 * Section "placeholder" (Placeholder):
 *   filler     Placeholder (Maintains empty ACLs)
 *
 * Section "nationnotes" (Nation Notes):
 *   nn_configure     Nation Notes
 *
 * Section "patientportal" (Patient Portal):
 *   portal     Patient Portal
 *
 * Section "menus" (Menus):
 *   modle      Module
 *
 * Section "groups" (Groups):
 *   gadd       View/Add/Update groups
 *   gcalendar  View/Create/Update groups appointment in calendar
 *   glog       Group encounter log
 *   gdlog      Group detailed log of appointment in patient record
 *   gm         Send message from the permanent group therapist to the personal therapist
 *
 * Section "inventory" (Inventory):
 *   lots         Lots
 *   sales        Sales
 *   purchases    Purchases
 *   transfers    Transfers
 *   adjustments  Adjustments
 *   consumption  Consumption
 *   destruction  Destruction
 *   reporting    Reporting
 * Note admin/drugs permission is required to create products;
 * we also allow that to substitute for all inventory permissions.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Acl;

use OpenEMR\Gacl\Gacl;

class AclMain
{
    // Holds the static Gacl object
    private static $gaclObject;

    // Collect the stored Gacl object (create it if it doesn't yet exist)
    //  Sharing one object will prevent opening a database connection for every call to Gacl.
    private static function collectGaclObject()
    {
        if (!is_object(self::$gaclObject)) {
            // Gacl object does not yet exist, so create it
            self::$gaclObject = new Gacl();
        }
        return self::$gaclObject;
    }

    /**
     * Clear the GACL Cache.  We use this in Unit Tests, but this function should be avoided to prevent smashing
     * the database.
     */
    public static function clearGaclCache()
    {
        $object = self::collectGaclObject();
        $object->clear_cache();
    }

    /**
     * Check if a user has a given type or types of access to an access control object.
     *
     * This function will check for access to the given ACO.
     * view    - The user may view but not add or modify entries
     * write   - The user may add or modify the ACO
     * wsome   - The user has limited add/modify access to the ACO
     * addonly - The user may view and add but not modify entries
     *
     * @param string       $section      Category of ACO
     * @param string       $value        Subcategory of ACO
     * @param string       $user         Optional user being checked for access.
     * @param string|array $return_value Type or types of access being requested.
     * @return bool  FALSE if access is denied, TRUE if allowed.
     */
    public static function aclCheckCore($section, $value, $user = '', $return_value = ''): bool
    {
        if (! $user) {
            $user = $_SESSION['authUser'];
        }

        // Superuser always gets access to everything.
        if (($section != 'admin' || $value != 'super') && self::aclCheckCore('admin', 'super', $user)) {
            return true;
        }

        // This will return all pertinent ACL's (including return_values and whether allow/deny)
        // Walk through them to assess for access
        $gacl_object = self::collectGaclObject();
        $acl_results = $gacl_object->acl_query($section, $value, 'users', $user, null, null, null, null, null, true);
        if (empty($acl_results)) {
            return false; //deny access
        }
        $access = false; //flag
        $deny = false; //flag
        foreach ($acl_results as $acl_result) {
            if (empty($acl_result['acl_id'])) {
                return false; //deny access, since this happens if no pertinent ACL's are returned
            }
            if (is_array($return_value)) {
                foreach ($return_value as $single_return_value) {
                    if (empty($single_return_value)) {
                        // deal with case if not looking for specific return value
                        if ($acl_result['allow']) {
                            $access = true;
                        } else {
                            $deny = true;
                        }
                    } else { //!empty($single_return_value)
                        // deal with case if looking for specific return value
                        if ($acl_result['return_value'] == $single_return_value) {
                            if ($acl_result['allow']) {
                                $access = true;
                            } else {
                                $deny = true;
                            }
                        }
                    }
                }
            } else { // $return_value is not an array (either empty or with one value)
                if (empty($return_value)) {
                    // deal with case if not looking for specific return value
                    if ($acl_result['allow']) {
                        $access = true;
                    } else {
                        $deny = true;
                    }
                } else { //!empty($return_value)
                    // deal with case if looking for specific return value
                    if ($acl_result['return_value'] == $return_value) {
                        if ($acl_result['allow']) {
                            $access = true;
                        } else {
                            $deny = true;
                        }
                    }
                }
            }
        }

        // Now decide whether user has access
        // (Note a denial takes precedence)
        if (!$deny && $access) {
            return true;
        }
        return false;
    }

    /**
     * Checks ACL
     *
     * Same Functionality in the Zend Module
     * for ACL Check in Zend
     * Path openemr/interface/modules/zend_modules/module/Application/src/Application/Model/ApplicationTable
     * Function Name zAclCheck
     *
     * @param String $user_id Auth user Id
     * $param String $section_identifier ACL Section id
     * @return boolean
     */
    public static function zhAclCheck($user_id, $section_identifier)
    {
        $sql_user_acl = " SELECT
                        COUNT(allowed) AS count
                      FROM
                        module_acl_user_settings AS usr_settings
                        LEFT JOIN module_acl_sections AS acl_sections
                            ON usr_settings.section_id = acl_sections.`section_id`
                      WHERE
                          acl_sections.section_identifier = ? AND usr_settings.user_id = ? AND usr_settings.allowed = ?";
        $sql_user_group = " SELECT
                          gagp.id AS group_id
                        FROM
                          gacl_aro AS garo
                          LEFT JOIN `gacl_groups_aro_map` AS gamp
                            ON garo.id = gamp.aro_id
                          LEFT JOIN `gacl_aro_groups` AS gagp
                            ON gagp.id = gamp.group_id
                          RIGHT JOIN `users_secure` usr
                            ON usr. username =  garo.value
                        WHERE
                          garo.section_value = ? AND usr. id = ?";
        $res_groups     = sqlStatement($sql_user_group, array('users',$user_id));

        // Prepare the group queries with the placemakers and binding array for the IN part
        $groups_sql_param = array();
        $groupPlacemakers = "";
        $firstFlag = true;
        while ($row = sqlFetchArray($res_groups)) {
            array_push($groups_sql_param, $row['group_id']);
            if ($firstFlag) {
                $groupPlacemakers = "?";
                $firstFlag = false;
            } else {
                $groupPlacemakers .= ",?";
            }
        }
        $sql_group_acl_base  = " SELECT
                        COUNT(allowed) AS count
                      FROM
                        module_acl_group_settings AS group_settings
                        LEFT JOIN module_acl_sections AS  acl_sections
                          ON group_settings.section_id = acl_sections.section_id
                      WHERE
                        group_settings.group_id IN (" . $groupPlacemakers . ") AND acl_sections.`section_identifier` = ? ";

        $sql_group_acl_allowed = $sql_group_acl_base . " AND group_settings.allowed = '1'";

        // Complete the group queries sql binding array
        array_push($groups_sql_param, $section_identifier);

        $count_group_allowed    = 0;
        $count_user_allowed     = 0;

        $res_user_allowed       = sqlQuery($sql_user_acl, array($section_identifier,$user_id,1));
        $count_user_allowed     = $res_user_allowed['count'];

        $res_group_allowed      = sqlQuery($sql_group_acl_allowed, $groups_sql_param);
        $count_group_allowed    = $res_group_allowed['count'];

        if ($count_user_allowed > 0) {
            return true;
        } elseif ($count_group_allowed > 0) {
            return true;
        } else {
            return false;
        }
    }

    // Permissions check for an ACO in "section|aco" format.
    // Note $return_value may be an array of return values.
    //
    public static function aclCheckAcoSpec($aco_spec, $user = '', $return_value = '')
    {
        if (empty($aco_spec)) {
            return true;
        }
        $tmp = explode('|', $aco_spec);
        if (!is_array($return_value)) {
            $return_value = array($return_value);
        }
        foreach ($return_value as $rv) {
            if (self::aclCheckCore($tmp[0], $tmp[1], $user, $rv)) {
                return true;
            }
        }
        return false;
    }

    // Permissions check for a specified encounter form type.
    // Note $return_value may be an array of return values.
    //
    public static function aclCheckForm($formdir, $user = '', $return_value = '')
    {
        require_once(dirname(__FILE__) . '/../../../library/registry.inc.php');
        $tmp = getRegistryEntryByDirectory($formdir, 'aco_spec');
        return self::aclCheckAcoSpec($tmp['aco_spec'], $user, $return_value);
    }

    // Permissions check for a specified issue type.
    // Note $return_value may be an array of return values.
    //
    public static function aclCheckIssue($type, $user = '', $return_value = '')
    {
        require_once(dirname(__FILE__) . '/../../../library/lists.inc.php');
        global $ISSUE_TYPES;
        if (empty($ISSUE_TYPES[$type][5])) {
            return true;
        }
        return self::aclCheckAcoSpec($ISSUE_TYPES[$type][5], $user, $return_value);
    }

    //Fetches aco for given postcalendar category
    public static function fetchPostCalendarCategoryACO($pc_catid)
    {
        $aco = sqlQuery(
            "SELECT aco_spec FROM openemr_postcalendar_categories WHERE pc_catid = ? LIMIT 1",
            array($pc_catid)
        );
        return $aco['aco_spec'];
    }
}
