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
     * @return array
     */
    public function getVendorGlobals(): array
    {
        $vendors['weno_rx_enable'] = '0';
        $vendors['weno_rx_enable_test'] = '0';
        $vendors['weno_encryption_key'] = '';
        $vendors['weno_admin_username'] = '';
        $vendors['weno_admin_password'] = '';

        $gl = sqlStatementNoLog(
            "SELECT gl_name, gl_value FROM `globals` WHERE `gl_name` IN(?, ?, ?, ?, ?)",
            array( "weno_rx_enable", "weno_rx_enable_test", "weno_encryption_key", "weno_admin_username", "weno_admin_password")
        );
        if (empty($gl)) {
            $this->saveVendorGlobals($vendors);
            return $vendors;
        }
        while ($row = sqlFetchArray($gl)) {
            $vendors[$row['gl_name']] = $row['gl_value'];
        }
        $crypt = new CryptoGen();
        $vendors['weno_encryption_key'] = $crypt->decryptStandard($vendors['weno_encryption_key']);
        $vendors['weno_admin_password'] = $crypt->decryptStandard($vendors['weno_admin_password']);

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
        $vendors['weno_rx_enable']      = $items['weno_rx_enable'] ?? '0';
        $vendors['weno_rx_enable_test'] = $items['weno_rx_enable_test'] ?? '0';
        $vendors['weno_encryption_key'] = $items['weno_encryption_key'];
        $vendors['weno_admin_username'] = $items['weno_admin_username'];
        $vendors['weno_admin_password'] = $items['weno_admin_password'];

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
    function getModuleRegistry($modId, $col = '*'): array
    {
        $registry = [];
        $sql = "SELECT $col FROM modules WHERE mod_id = ?";
        $results = sqlQuery($sql, array($modId));
        foreach ($results as $k => $v) {
            $registry[$k] = trim((preg_replace('/\R/', '', $v)));
        }

        return $registry;
    }

    /**
     * Returns true if all the weno settings have been configured.  Otherwise, it returns false.
     *
     * @return bool
     */
    public function isWenoConfigured(): bool
    {
        $config = $this->getVendorGlobals();
        $keys = array_keys($config);
        foreach ($keys as $key) {
            if ($key === 'weno_rx_enable_test') {
                continue;
            }
            $value = $GLOBALS[$key] ?? null;

            if (empty($value)) {
                return false;
            }
        }
        return true;
    }

    public function setModuleState($modId, $flag, $flag_ui): array|bool|null
    {
        $sql = "UPDATE `modules` SET `mod_active` = ?, `mod_ui_active` = ? WHERE `mod_id` = ?";
        return sqlQuery($sql, array($flag, $flag_ui, $modId));
    }
}
