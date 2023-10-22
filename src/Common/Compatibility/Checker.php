<?php

namespace OpenEMR\Common\Compatibility;

/**
 * Check if the server's PHP version is compatible with OpenEMR.
 *
 * Note that this will only be used within setup.php, sql_upgrade.php,
 * sql_patch.php, acl_upgrade.php, admin.php, and globals.php.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @copyright Copyright (c) 2017 Matthew Vita
 */
class Checker
{
    private static $minimumPhpVersion = "8.1.0";

    /**
     * Checks to see if minimum PHP version is met.
     *
     * @return bool | warning string
     */
    public static function checkPhpVersion()
    {
        $phpCheck = self::isPhpSupported();
        $response = "";

        if (!$phpCheck) {
            $response .= "PHP version needs to be at least" . " " . self::$minimumPhpVersion . ".";
        } else {
            $response = true;
        }

        return $response;
    }

    /**
     * Checks to see if minimum PHP version is met.
     *
     * @return bool
     */
    private static function isPhpSupported()
    {
        return version_compare(phpversion(), self::$minimumPhpVersion, ">=");
    }
}
