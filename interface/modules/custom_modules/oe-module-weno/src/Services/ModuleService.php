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
     * @param $flag
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
        $vendors['weno_secondary_encryption_key'] = '';
        $vendors['weno_secondary_admin_username'] = '';
        $vendors['weno_secondary_admin_password'] = '';

        $gl = sqlStatementNoLog(
            "SELECT gl_name, gl_value FROM `globals` WHERE `gl_name` IN(?, ?, ?, ?, ?, ?, ?, ?)",
            array("weno_rx_enable", "weno_rx_enable_test", "weno_encryption_key", "weno_admin_username", "weno_admin_password", "weno_secondary_encryption_key", "weno_secondary_admin_username", "weno_secondary_admin_password")
        );
        if (empty($gl)) {
            $this->saveVendorGlobals($vendors);
            return $vendors;
        }
        while ($row = sqlFetchArray($gl)) {
            $vendors[$row['gl_name']] = $row['gl_value'];
        }
        if ($decrypt) {
            $crypt = new CryptoGen();
            $vendors['weno_encryption_key'] = $crypt->decryptStandard($vendors['weno_encryption_key']);
            $vendors['weno_admin_password'] = $crypt->decryptStandard($vendors['weno_admin_password']);
            $vendors['weno_secondary_encryption_key'] = $crypt->decryptStandard($vendors['weno_secondary_encryption_key']);
            $vendors['weno_secondary_admin_password'] = $crypt->decryptStandard($vendors['weno_secondary_admin_password']);
        }

        return $vendors;
    }

    /**
     * @param $vendors
     * @return void
     */
    public function saveVendorGlobals($items): void
    {
        $crypt = new CryptoGen();
        $items['weno_encryption_key'] = $crypt->encryptStandard($items['weno_encryption_key']);
        $items['weno_admin_password'] = $crypt->encryptStandard($items['weno_admin_password']);
        $items['weno_secondary_encryption_key'] = $crypt->encryptStandard($items['weno_secondary_encryption_key']);
        $items['weno_secondary_admin_password'] = $crypt->encryptStandard($items['weno_secondary_admin_password']);
        $vendors['weno_rx_enable'] = $items['weno_rx_enable'] ?? '0';
        $vendors['weno_rx_enable_test'] = $items['weno_rx_enable_test'] ?? '0';
        $vendors['weno_encryption_key'] = $items['weno_encryption_key'];
        $vendors['weno_admin_username'] = $items['weno_admin_username'];
        $vendors['weno_admin_password'] = $items['weno_admin_password'];
        $vendors['weno_secondary_encryption_key'] = $items['weno_secondary_encryption_key'];
        $vendors['weno_secondary_admin_username'] = $items['weno_secondary_admin_username'];
        $vendors['weno_secondary_admin_password'] = $items['weno_secondary_admin_password'];

        foreach ($vendors as $key => $vendor) {
            sqlQuery(
                "INSERT INTO `globals` (`gl_name`,`gl_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `gl_name` = ?, `gl_value` = ?",
                array($key, $vendor, $key, $vendor)
            );
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
            if (
                $key === 'weno_rx_enable_test'
                || $key === 'weno_secondary_admin_username'
                || $key === 'weno_secondary_admin_password'
                || $key === 'weno_secondary_encryption_key'
            ) {
                continue;
            }
            $value = $GLOBALS[$key] ?? null;

            if (empty($value)) {
                self::setTaskState('0', false);
                return false;
            }
        }
        self::setTaskState('1', false);
        return true;
    }

    public static function statusPharmacyDownloadReset(): bool
    {
        $logService = new WenoLogService();
        $log = $logService->getLastPharmacyDownloadStatus();
        if ($log['status'] ?? '' != 'Success') {
            if (($log['count'] ?? 0) > 0) {
                return true;
            }
            $sql = "UPDATE `background_services` SET `next_run` = current_timestamp(), `active` = '1' WHERE `name` = ? && `next_run` > current_timestamp()";
            sqlQuery($sql, array('WenoExchangePharmacies'));
            return true;
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
}
