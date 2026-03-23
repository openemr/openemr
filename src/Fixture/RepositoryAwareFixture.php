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

use OpenEMR\Common\Database\Repository\RepositoryInterface;

abstract class RepositoryAwareFixture extends AbstractFixture implements AccessibleFixtureInterface, RemovableFixtureInterface
{
    use AccessibleFixtureTrait;
    use RemovableFixtureTrait;

    public function __construct(
        protected readonly RepositoryInterface $repository,
        array $filenames,
    ) {
        parent::__construct($filenames);
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
