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

namespace OpenEMR\Tests\Fixtures\Purger;

/**
 * Remove records by truncating table,
 * restore one-by-one
 *
 * @template T of array
 * @extends AbstractPurger<T>
 */
abstract class AbstractTruncatePurger extends AbstractPurger
{
    public function purge(): void
    {
        $this->backup();

        $this->db->truncate($this->table);
    }

    public function restore(): void
    {
        $this->db->truncate($this->table);

        foreach ($this->records as $record) {
            $this->restoreRecord($record);
        }
    }

    /**
     * @phpstan-param T $record
     */
    protected function restoreRecord(array $record): void
    {
        $this->db->insert($this->table, $record);
    }
}
