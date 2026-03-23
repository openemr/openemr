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

namespace OpenEMR\Fixture;

use OpenEMR\Common\Database\DatabaseManager;

/**
 * @template TRecord of array
 */
class DbAwareAbstractFixture extends AbstractFixture
{
    public function __construct(
        private readonly DatabaseManager $db,
        private readonly string $table,
        array $filenames,
    ) {
        parent::__construct(
            $filenames,
        );
    }

    protected function loadRecord(array $record): array
    {
        $this->db->insert($this->table, $record);

        return $record;
    }
}
