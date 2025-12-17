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

namespace OpenEMR\Tests\Fixtures\Settings;

use OpenEMR\Common\Database\Database;
use OpenEMR\Common\Database\DatabaseTables;
use OpenEMR\Tests\Fixtures\AbstractFixture;

class GlobalSettingsFixture extends AbstractFixture
{
    private string $table = DatabaseTables::TABLE_GLOBAL_SETTINGS;

    public function __construct(
        private readonly Database $db
    ) {
    }

    public function load(): void
    {
        $this->loadFromFile(sprintf('%s/../data/settings/globals.json', __DIR__));
    }

    protected function loadRecord(array $record): array
    {
        $this->db->insert($this->table, $record);

        return $record;
    }
}
