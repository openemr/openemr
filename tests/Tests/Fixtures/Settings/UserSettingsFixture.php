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

namespace OpenEMR\Tests\Fixtures\Settings;

use OpenEMR\Common\Database\DatabaseManager;
use OpenEMR\Common\Database\DatabaseTables;
use OpenEMR\Core\Traits\SingletonTrait;
use OpenEMR\Tests\Fixtures\AbstractFixture;

class UserSettingsFixture extends AbstractFixture
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        return new self(
            DatabaseManager::getInstance(),
        );
    }

    public function __construct(
        private readonly DatabaseManager $db,
    ) {
    }

    public function load(): void
    {
        $this->loadFromFile(sprintf('%s/../data/settings/user_settings.json', __DIR__));
    }

    /**
     * Settings has no IDs, so we have custom implementation
     */
    protected function loadRecord(array $record): array
    {
        $this->db->insert(DatabaseTables::TABLE_USER_SETTINGS, $record);

        return $record;
    }
}
