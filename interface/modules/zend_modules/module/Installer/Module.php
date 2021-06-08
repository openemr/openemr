<?php

/**
 * interface/modules/zend_modules/module/Installer/Module.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jacob T.Paul <jacob@zhservices.com>
 * @author    Shalini Balakrishnan  <shalini@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Installer;

// Add these import statements:
use Installer\Model\InstModule;
use Installer\Model\InstModuleTable;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Console\Adapter\AdapterInterface as Console;

/**
 * Handles the initial module load.  Any configuration should in the module.config.php file
 * instead of overloading methods here if at all possible
 */
class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            // TODO: The zf3 autoloader should handle autoloading these classes by default but it's not right now
            // we need to figure out why that is so we can remove this unnecessary piece.
            'Laminas\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getConsoleUsage(Console $console)
    {
        return [
            ['zfc-module', 'Part of route for console call'],
            ['--site=<site_name>', 'Name of site, by default: "default" '],
            ['--modaction=<action_name>', 'Available actions: install_sql, install_acl, upgrade_acl, upgrade_sql, install, enable, disable, unregister'],
            ['--modname=<module_name>', 'Name of module'],
            ['acl-modify', 'Part of route for console call'],
            ['--site=<site_name>', 'Name of site, by default: "default" '],
            ['--modname=<module_name>', 'Name of module'],
            ['--aclgroup=<acl_group_name>', 'Name of ACL group'],
            ['--aclaction=<action_name>', 'Available actions: enable, disable'],
            ['register', 'Part of route for console call'],
            ['--mtype=<module_type>', 'module'],
            ['--modname=<module_name>', 'Name of module'],
            ['ccda-import', 'Part of route for console call'],
            ['--site=<site_name>', 'Name of site, by default: "default" '],
            ['--document_id=<document_id>', 'The ccda document id that will be imported into the audit tables'],
            ['ccda-newpatient', 'Part of route for console call'],
            ['--site=<site_name>', 'Name of site, by default: "default" '],
            ['--am_id=<am_id>', 'The master audit table id of patient that will be imported as a new patient'],
            ['--document_id=<document_id>', 'The ccda document id that was previously imported into the audit table'],
            ['ccda-newpatient-import', 'Part of route for console call'],
            ['--site=<site_name>', 'Name of site, by default: "default" '],
            ['--document=<document>', 'File (path) that will be imported to create the new patient'],
        ];
    }
}
