<?php

/**
 * OpenEMR Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023-2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\DashboardContext\Services;

use OpenEMR\Common\Database\QueryUtils;

/**
 * Companion to event bootstrapping
 */
class ModuleService
{
    public function __construct()
    {
    }

    /**
     * Grab all Laminas Module setup or columns values.
     *
     * @param        $modId
     * @param string $col
     * @return array
     */
    function getModuleRegistry($modId, string $col = '*'): array
    {
        $registry = [];
        $sql = "SELECT $col FROM modules WHERE mod_id = ? OR `mod_directory` = ?";
        $results = QueryUtils::querySingleRow($sql, [$modId, $modId]);
        foreach ($results as $k => $v) {
            $registry[$k] = trim(((string)preg_replace('/\R/', '', (string)$v)));
        }

        return $registry;
    }

    /**
     * @param $modId   string|int module id or directory name
     * @param $flag    string|int 1 or 0 to activate or deactivate module.
     * @param $flag_ui string|int custom flag to activate or deactivate Manager UI button states.
     * @return array|bool|null
     */
    public static function setModuleState($modId, $flag, $flag_ui): array|bool|null
    {
        // set module state.
        $sql = "UPDATE `modules` SET `mod_active` = ?, `mod_ui_active` = ? WHERE `mod_id` = ? OR `mod_directory` = ?";
        return QueryUtils::querySingleRow($sql, [$flag, $flag_ui, $modId, $modId]);
    }

    public static function getModuleState($modId): bool
    {
        $sql = "SELECT `mod_active` FROM `modules` WHERE `mod_id` = ? OR `mod_directory` = ?";
        $flag = QueryUtils::querySingleRow($sql, [$modId, $modId]);

        return !empty($flag['mod_active']);
    }

    /**
     * @return string
     */
    public function getProviderName(): string
    {
        $provider_info = QueryUtils::querySingleRow("select fname, mname, lname from users where username=? ", [$_SESSION["authUser"]]);
        $provider_info ??= ['fname' => '', 'mname' => '', 'lname' => ''];
        return $provider_info['fname'] . " " . $provider_info['mname'] . " " . $provider_info['lname'];
    }
}
