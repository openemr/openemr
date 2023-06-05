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

        $gl = sqlStatementNoLog(
            "SELECT gl_name, gl_value FROM `globals` WHERE `gl_name` IN(?, ?, ?, ?)",
            array("oefax_enable_sms", "oefax_enable_fax", "oesms_send", "oerestrict_users")
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
                       ('oesms_send', '0')"
        );
    }

    /**
     * @param $vendors
     * @return void
     */
    public function saveVendorGlobals($vendors): void
    {
        // Comes from a POST pass in. Only want what we want!
        $items['oefax_enable_sms'] = $vendors['sms_vendor'] ?? '';
        $items['oefax_enable_fax'] = $vendors['fax_vendor'] ?? '';
        $items['oesms_send'] = $vendors['allow_dialog'] ?? '';
        $items['oerestrict_users'] = $vendors['restrict'] ?? '';
        foreach ($items as $key => $vendor) {
            sqlQuery(
                "INSERT INTO `globals` (`gl_name`,`gl_value`) VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE `gl_name` = ?, `gl_value` = ?",
                array($key, $vendor, $key, $vendor)
            );
        }
    }
}
