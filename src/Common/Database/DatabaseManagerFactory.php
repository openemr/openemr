<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Database;

use OpenEMR\Core\Traits\SingletonFactoryTrait;

/**
 * Usage:
 *   $db = DatabaseManagerFactory::getInstance();
 *   $db->truncate('logs');
 */
class DatabaseManagerFactory
{
    use SingletonFactoryTrait;

    protected static function createInstance(): Database
    {
        return new Database(
            $GLOBALS['adodb']['db'],
        );
    }
}
