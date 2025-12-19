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

namespace OpenEMR\Tests\Fixtures\Purger;

use OpenEMR\Common\Database\DatabaseManager;

/**
 * Remove and restore records one-by-one
 *
 * @template T of array
 * @template-extends AbstractPurger<T>
 */
abstract class AbstractOneByOnePurger extends AbstractPurger
{
    public function __construct(
        DatabaseManager $db,
        string $table,
        protected readonly string $identifierFieldName = 'id'
    ) {
        parent::__construct($db, $table);
    }

    public function purge(): void
    {
        $this->backup();

        foreach ($this->records as $record) {
            $this->purgeRecord($record);
        }
    }

    /**
     * @phpstan-param T $record
     */
    protected function purgeRecord(array $record): void
    {
        $this->db->remove(
            $this->table,
            $record[$this->identifierFieldName],
            $this->identifierFieldName,
        );
    }
}
