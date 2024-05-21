<?php

/**
 * ClaimRevConnector Module Services
 *
 * This file contains the services for the ClaimRevConnector module.
 *
 * PHP version 7
 *
 * @package   OpenEMR Modules
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\ClaimRevConnector\Services;

class ModuleServices
{
    public function __construct()
    {
    }

    /**
     * @param      $flag
     * @param bool $reset
     * @return array|bool|null
     */
    public static function setTaskState($flag, bool $reset = false): array|bool|null
    {
        $sql_next = "UPDATE `background_services` SET `active` = ? WHERE `name` = ? OR `name` = ?";
        if ($reset) {
            $sql_next = "UPDATE `background_services` SET `active` = ?, next_run = NOW() WHERE `name` = ? OR `name` = ?";
        }
        return sqlQuery($sql_next, array($flag, 'ClaimRev_Send', 'ClaimRev_Receive'));
    }
    /**
     * @param $modId   string|int module id or directory name
     * @param $flag    string|int 1 or 0 to activate or deactivate module.
     * @param $flag_ui string|int custom flag to activate or deactivate Manager UI button states.
     * @return array|bool|null
     */
    public static function setModuleState($modId, $flag, $flag_ui): array|bool|null
    {
        if (($flag_ui == '1') || ($flag == '0')) {
            self::setTaskState('0', false);
        } else {
            // set BG tasks to active if module is active.
            self::setTaskState('1', false);
        }
        // set module state.
        $sql = "UPDATE `modules` SET `mod_active` = ?, `mod_ui_active` = ? WHERE `mod_id` = ? OR `mod_directory` = ?";
        return sqlQuery($sql, array($flag, $flag_ui, $modId, $modId));
    }

    public static function getModuleState($modId): bool
    {
        $sql = "SELECT `mod_active` FROM `modules` WHERE `mod_id` = ? OR `mod_directory` = ?";
        $flag = sqlQuery($sql, array($modId, $modId));

        return !empty($flag['mod_active']);
    }

    /**
     * @return bool true if all the Admin claim rev settings have been configured.  Otherwise, false.
     */
    public function isClaimRevConfigured(): bool
    {

        $config = $this->getVendorGlobals();
        $keys = array_keys($config);
        foreach ($keys as $key) {
            // these are always required to run module.
            if (
                $key === 'oe_claimrev_config_clientid'
                || $key === 'oe_claimrev_config_clientsecret'
            ) {
                $value = $config[$key] ?? null;
                if (empty($value)) {
                    self::setTaskState('0');
                    return false;
                }
            }
        }
        self::setTaskState('1');
        return true;
    }

    private function getVendorGlobals(): array|bool
    {
        $gl = sqlStatementNoLog(
            "SELECT gl_name, gl_value FROM `globals` WHERE `gl_name` IN(?, ?, ?)",
            array("oe_claimrev_config_clientid", "oe_claimrev_config_clientsecret", "oe_claimrev_config_add_menu_button")
        );
        return $gl;
    }
}
