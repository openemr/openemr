<?php

/**
 * OpenEMR Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023-2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

use OpenEMR\Common\Crypto\CryptoGen;

/**
 * Companion to event bootstrapping
 */
class ModuleService
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
        return sqlQuery($sql_next, array($flag, 'WenoExchange', 'WenoExchangePharmacies'));
    }

    /**
     * @return array
     */
    public function getVendorGlobals($decrypt = true): array
    {
        $vendors['weno_rx_enable'] = '0';
        $vendors['weno_rx_enable_test'] = '0';
        $vendors['weno_encryption_key'] = '';
        $vendors['weno_admin_username'] = '';
        $vendors['weno_admin_password'] = '';
        $vendors['weno_provider_email'] = '';
        $vendors['weno_provider_password'] = '';

        $gl = sqlStatementNoLog(
            "SELECT gl_name, gl_value FROM `globals` WHERE `gl_name` IN(?, ?, ?, ?, ?)",
            array("weno_rx_enable", "weno_rx_enable_test", "weno_encryption_key", "weno_admin_username", "weno_admin_password")
        );
        $us = sqlStatementNoLog(
            "SELECT `setting_label`, `setting_value`, `setting_user` FROM `user_settings` WHERE `setting_label` IN(?, ?) AND `setting_user` = ?",
            array("global:weno_provider_email", "global:weno_provider_password", $_SESSION['authUserID'])
        );
        if (empty($gl)) {
            $this->saveVendorGlobals($vendors, 'global');
            return $vendors;
        }
        if (empty($us)) {
            $this->saveVendorGlobals($vendors, 'user');
            return $vendors;
        }
        while ($row = sqlFetchArray($gl)) {
            $vendors[$row['gl_name']] = $row['gl_value'];
        }
        while ($row = sqlFetchArray($us)) {
            $key = substr($row['setting_label'], 7);
            $vendors[$key] = $row['setting_value'];
        }
        if ($decrypt) {
            $crypt = new CryptoGen();
            $vendors['weno_encryption_key'] = $crypt->decryptStandard($vendors['weno_encryption_key']);
            $vendors['weno_admin_password'] = $crypt->decryptStandard($vendors['weno_admin_password']);
            $vendors['weno_provider_password'] = $crypt->decryptStandard($vendors['weno_provider_password']);
        }

        return $vendors;
    }

    /**
     * @param $items
     * @return void
     */
    public function saveVendorGlobals($items, $which = null): void
    {
        $crypt = new CryptoGen();
        $items['weno_encryption_key'] = $crypt->encryptStandard($items['weno_encryption_key']);
        $items['weno_admin_password'] = $crypt->encryptStandard($items['weno_admin_password']);
        $items['weno_provider_password'] = $crypt->encryptStandard($items['weno_provider_password']);
        $vendors['weno_rx_enable'] = $items['weno_rx_enable'] ?? '0';
        $vendors['weno_rx_enable_test'] = $items['weno_rx_enable_test'] ?? '0';
        $vendors['weno_encryption_key'] = $items['weno_encryption_key'];
        $vendors['weno_admin_username'] = $items['weno_admin_username'];
        $vendors['weno_admin_password'] = $items['weno_admin_password'];
        $userSettings['weno_provider_email'] = $items['weno_provider_email'];
        $userSettings['weno_provider_password'] = $items['weno_provider_password'];

        $GLOBALS['weno_encryption_key'] = $items['weno_encryption_key'];
        $GLOBALS['weno_admin_password'] = $items['weno_admin_password'];

        if ($which != 'user') {
            foreach ($vendors as $key => $vendor) {
                $GLOBALS[$key] = $vendor;
                sqlQuery(
                    "INSERT INTO `globals` (`gl_name`,`gl_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `gl_name` = ?, `gl_value` = ?",
                    array($key, $vendor, $key, $vendor)
                );
            }
        }
        if ($which != 'global') {
            foreach ($userSettings as $key => $vendor) {
                $GLOBALS[$key] = $vendor;
                sqlQuery(
                    "INSERT INTO `user_settings` (`setting_label`,`setting_value`, `setting_user`) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE `setting_value` = ?, `setting_user` = ?",
                    array('global:' . $key, $vendor, $_SESSION['authUserID'], $vendor, $_SESSION['authUserID'])
                );
            }
        }
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
        $results = sqlQuery($sql, array($modId, $modId));
        foreach ($results as $k => $v) {
            $registry[$k] = trim((preg_replace('/\R/', '', $v)));
        }

        return $registry;
    }

    /**
     * @return bool true if all the Admin weno settings have been configured.  Otherwise, false.
     */
    public function isWenoConfigured(): bool
    {
        self::statusPharmacyDownloadReset(); // if last failed, reset to active
        $config = $this->getVendorGlobals();
        $keys = array_keys($config);
        foreach ($keys as $key) {
            // these are always required to run module.
            if (
                $key === 'weno_rx_enable'
                || $key === 'weno_admin_username'
                || $key === 'weno_admin_password'
                || $key === 'weno_encryption_key'
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

    public static function statusPharmacyDownloadReset(): bool
    {
        $logService = new WenoLogService();
        $log = $logService->getLastPharmacyDownloadStatus();
        if ($log['status'] ?? '' == 'Failed') {
            if (($log['count'] ?? 0) > 0) {
                return true;
            }
            // TODO need to add lookup for last 3 failed status and if it's been over three attempts then stop trying.
            //$sql = "UPDATE `background_services` SET `next_run` = current_timestamp(), `active` = '1' WHERE `name` = ? && `next_run` > current_timestamp()";
            //sqlQuery($sql, array('WenoExchangePharmacies'));
            //return true;
        }
        return false;
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

    /**
     * @return string
     */
    public function getProviderName(): string
    {
        $provider_info = sqlQuery("select fname, mname, lname from users where username=? ", [$_SESSION["authUser"]]);
        $provider_info = $provider_info ?? ['fname' => '', 'mname' => '', 'lname' => ''];
        return $provider_info['fname'] . " " . $provider_info['mname'] . " " . $provider_info['lname'];
    }
}
