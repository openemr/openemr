<?php

/**
 * Class to be call from Module Manager reporting management events.
 * Example is if module is disabled or unregistered ect.
 *
 * @package   OpenEMR Modules
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

class ModuleManagerAfterActions
{
    // Prevent instantiation
    private function __construct()
    {
    }

    // currently register isn't supported and support should be a part of install.
    public static function moduleManagerAction($methodName, $modId, $currentActionStatus = 'Success')
    {
        // Check if the action method exists
        if (method_exists(self::class, $methodName)) {
            return self::$methodName($modId, $currentActionStatus);
        } else {
            // TODO Perhaps this should be an exception!
            return "Module cleanup method $methodName does not exist.";
        }
    }

    private static function install($modId, $currentActionStatus)
    {
        return $currentActionStatus;
    }

    private static function enable($modId, $currentActionStatus)
    {
        return $currentActionStatus;
    }

    private static function disable($modId, $currentActionStatus)
    {
        return $currentActionStatus;
    }

    private static function unregister($modId, $currentActionStatus)
    {
        return $currentActionStatus;
    }

    private static function install_sql($modId, $currentActionStatus)
    {
        return $currentActionStatus;
    }

    private static function upgrade_sql($modId, $currentActionStatus)
    {
        return $currentActionStatus;
    }
}
