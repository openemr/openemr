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

namespace OpenEMR\Tests\Fixtures\Purger\Settings;

use OpenEMR\Common\Database\DatabaseManagerFactory;
use OpenEMR\Core\Traits\SingletonFactoryTrait;
use OpenEMR\Tests\Fixtures\Purger\CompositePurger;

/**
 * Purge settings in tables:
 * - globals
 * - user_settings
 *
 * Usage:
 *   $purger = CompositeSettingsPurgerFactory::getInstance();
 */
class CompositeSettingsPurgerFactoryTrait
{
    use SingletonFactoryTrait;

    protected static function createInstance(): object
    {
        $db = DatabaseManagerFactory::getInstance();

        return new CompositePurger([
            new GlobalSettingsPurger($db),
            new UserSettingsPurger($db),
        ]);
    }
}
