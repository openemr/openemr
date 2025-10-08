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
use OpenEMR\Common\Database\Repository\RepositoryFactory;
use OpenEMR\Common\Database\Repository\UuidRegistryRepository;
use OpenEMR\Common\Uuid\UuidRegistry;

abstract class UuidAwareFixture extends AbstractRepositoryAwareFixture
{
    private readonly UuidRegistryRepository $uuidRegistryRepository;

    private readonly UuidRegistry $uuidRegistry;

    public function __construct(
        AbstractRepository $repository,
    ) {
        parent::__construct($repository);

        $this->uuidRegistryRepository = RepositoryFactory::createRepository(UuidRegistryRepository::class);
        $this->uuidRegistry = new UuidRegistry(['table_name' => $this->repository->getTable()]);
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
