<?php
/**
 * This class is responsible for checking if the server's PHP version is compatible with OpenEMR.
 *
 * Note that this will only be used within setup.php, sql_upgrade.php,
 * sql_patch.php, acl_upgrade.php, admin.php, and globals.php.
 *
 * Copyright (C) 2017 Matthew Vita <matthewvita48@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
 */

// No namespace here because this class is mostly required outside of the
// OpenEMR context.

class Checker {
    private static $minimumPhpVersion = "5.4.0";

    private static function xlDelegate($value) {
        if (function_exists("xl")) {
            return xl($value);
        }

        return $value;
    }

    /**
     * Checks to see if minimum PHP version is met.
     *
     * @return bool | warning string
     */
    public static function checkPhpVersion() {
        $phpCheck = self::isPhpSupported();
        $response = "";

        if (!$phpCheck) {
            $response .= self::xlDelegate("PHP version needs to be at least") . " " . self::$minimumPhpVersion . ".";
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
    private static function isPhpSupported() {
        return version_compare(phpversion(), self::$minimumPhpVersion, ">=");
    }
}
