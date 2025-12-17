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

namespace OpenEMR\Tests\Fixtures;

/**
 * @template TRecord of array
 * @template-extends AbstractFixture<TRecord>
 */
abstract class AbstractRemovableFixture extends AbstractFixture implements RemovableFixtureInterface
{
    public function removeFixtureRecords(): void
    {
        foreach ($this->records as $record) {
            $this->removeRecord($record);

            unset($this->records[$record['id']]);
        }
    }

    /**
     * @phpstan-param TRecord $record
     */
    abstract protected function removeRecord(array $record): void;
}
