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

namespace OpenEMR\Setting\Fixture;

use OpenEMR\Common\Database\DatabaseManager;
use OpenEMR\Common\Database\DatabaseTables;
use OpenEMR\Core\Traits\SingletonTrait;

class KeysFixture extends AbstractSettingsFixture
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        return new self(
            DatabaseManager::getInstance(),
        );
    }

    public function __construct(
        DatabaseManager $db,
    ) {
        parent::__construct(
            $db,
            DatabaseTables::TABLE_KEYS,
            [
                'keys.json',
            ],
        );
    }
}
