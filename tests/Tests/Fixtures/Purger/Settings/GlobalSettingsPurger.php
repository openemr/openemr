<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Fixtures\Purger\Settings;

use OpenEMR\Common\Database\DatabaseManager;
use OpenEMR\Common\Database\DatabaseTables;
use OpenEMR\Core\Traits\SingletonTrait;
use OpenEMR\Tests\Fixtures\Purger\AbstractTruncatePurger;

class GlobalSettingsPurger extends AbstractTruncatePurger
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        return new self(
            DatabaseManager::getInstance(),
            DatabaseTables::TABLE_GLOBAL_SETTINGS,
        );
    }
}
