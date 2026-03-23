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

namespace OpenEMR\Setting\Fixture\Purger;

use OpenEMR\Common\Database\DatabaseManager;
use OpenEMR\Common\Database\DatabaseTables;
use OpenEMR\Core\Traits\SingletonTrait;
use OpenEMR\Fixture\Purger\TruncatePurger;

class GlobalSettingsPurger extends TruncatePurger
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
