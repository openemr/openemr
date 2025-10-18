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

use OpenEMR\Common\Database\Repository\AbstractRepository;

abstract class AbstractRepositoryAwareFixture extends AbstractRemovableFixture
{
    public function __construct(
        protected readonly AbstractRepository $repository,
    ) {
    }

    protected function loadRecord(array $record): array
    {
        $record['id'] = $this->repository->insert($record);

        return $record;
    }

    protected function removeRecord(array $record): void
    {
        $this->repository->remove($record['id']);
    }
}
