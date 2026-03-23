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

use OpenEMR\Common\Database\Repository\AbstractRepository;
use OpenEMR\Common\Database\Repository\UuidRegistryRepository;
use OpenEMR\Common\Uuid\UuidRegistry;

abstract class UuidAwareFixture extends RepositoryAwareFixture
{
    public function __construct(
        AbstractRepository $repository,
        private readonly UuidRegistryRepository $uuidRegistryRepository,
        private readonly UuidRegistry $uuidRegistry,
        array $filenames,
    ) {
        parent::__construct($repository, $filenames);
    }

    protected function loadRecord(array $record): array
    {
        $record['uuid'] = $this->uuidRegistry->createUuid();
        $record = parent::loadRecord($record);
        $record['uuid'] = UuidRegistry::uuidToString($record['uuid']);

        return $record;
    }

    protected function removeRecord(array $record): void
    {
        $this->uuidRegistryRepository->removeByUuidAndTable($record['uuid'], $this->repository->getTable());

        parent::removeRecord($record);
    }
}
