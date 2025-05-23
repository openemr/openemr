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
}
