<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\FaxSMS;

/**
 * Companion to event bootstrapping
 */
class BootstrapService
{
    public function __construct()
    {
    }

    /**
     * @return array
     */
    public function getVendorGlobals(): array
    {
        $vendors['oefax_enable_sms'] = '';
        $vendors['oefax_enable_fax'] = '';
        $vendors['oesms_send'] = '';
        $vendors['oerestrict_users'] = '';
        $vendors['oe_enable_email'] = '';

        $gl = sqlStatementNoLog(
            "SELECT gl_name, gl_value FROM `globals` WHERE `gl_name` IN(?, ?, ?, ?, ?)",
            array("oefax_enable_sms", "oefax_enable_fax", "oesms_send", "oerestrict_users", 'oe_enable_email')
        );
        while ($row = sqlFetchArray($gl)) {
            $vendors[$row['gl_name']] = $row['gl_value'];
        }

        return $vendors;
    }

    /**
     * @return void
     */
    public function createVendorGlobals(): void
    {
        sqlInsert(
            "INSERT INTO `globals` (`gl_name`,`gl_value`) 
                VALUES ('oefax_enable_fax', '0'), 
                       ('oefax_enable_sms', '0'),
                       ('oerestrict_users', '0'),
                       ('oesms_send', '0'),
                       ('oe_enable_email', '0')"
        );
    }

    /**
     * @param $vendors
     * @return void
     */
    public function saveVendorGlobals($vendors): void
    {
        // Comes from a Setup form POST pass in. Only want what we want!
        // Move form names to global name. Easier to read.
        $items['oefax_enable_sms'] = $vendors['sms_vendor'] ?? '';
        $items['oefax_enable_fax'] = $vendors['fax_vendor'] ?? '';
        $items['oesms_send'] = $vendors['allow_dialog'] ?? '';
        $items['oerestrict_users'] = $vendors['restrict'] ?? '';
        $items['oe_enable_email'] = $vendors['email_vendor'] ?? '';
        foreach ($items as $key => $vendor) {
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
     * @param $items
     * @return void
     */
    public function saveModuleListenerGlobals($items): void
    {
        foreach ($items as $key => $vendor) {
            $id = sqlQuery(
                "INSERT INTO `globals` (`gl_name`,`gl_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `gl_name` = ?, `gl_value` = ?",
                array($key, $vendor, $key, $vendor)
            );
        }
    }

    /**
     * @param $settings
     * @return array|false|null
     */
    public function persistSetupSettings($settings): array|null|false
    {
        // vendor for backup of setup globals.
        $vendor = '_persisted';
        $authId = 0;
        $content = json_encode($settings);
        $sql = "INSERT INTO `module_faxsms_credentials` (`id`, `auth_user`, `vendor`, `credentials`) 
            VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `auth_user`= ?, `vendor` = ?, `credentials`= ?, `updated` = NOW()";

        return sqlQuery($sql, array('', $authId, $vendor, $content, $authId, $vendor, $content));
    }

    /**
     * @return array
     */
    public function fetchPersistedSetupSettings(): array
    {
        $vendor = '_persisted';
        $authUserId = 0;
        $globals = sqlQuery("SELECT `credentials` FROM `module_faxsms_credentials` WHERE `auth_user` = ? AND `vendor` = ?", array($authUserId, $vendor)) ?? [];
        if (is_string($globals['credentials'])) {
            return json_decode($globals['credentials'], true) ?? [];
        }
        return [];
    }
}
