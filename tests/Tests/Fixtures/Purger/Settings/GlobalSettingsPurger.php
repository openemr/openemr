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

use OpenEMR\Common\Database\Database;
use OpenEMR\Common\Database\DatabaseTables;
use OpenEMR\Tests\Fixtures\Purger\AbstractTruncatePurger;

class GlobalSettingsPurger extends AbstractTruncatePurger
{
    public function __construct(Database $db)
    {
        parent::__construct(
            $db,
            DatabaseTables::TABLE_GLOBAL_SETTINGS,
        );
    }
}
