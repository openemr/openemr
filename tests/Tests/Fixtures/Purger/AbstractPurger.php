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

use OpenEMR\Common\Database\Database;

/**
 * @template T of array
 */
abstract class AbstractPurger implements PurgerInterface
{
    /**
     * Backup of records to be able to
     * restore initial state of table
     */
    protected array $records = [];

    public function __construct(
        protected readonly Database $db,
        protected readonly string $table,
    ) {
    }

    protected function backup(): void
    {
        $this->records = $this->db->findAll($this->table);
    }

    abstract public function purge(): void;

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
