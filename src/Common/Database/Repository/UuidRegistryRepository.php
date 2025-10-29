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

namespace OpenEMR\Common\Database\Repository;

use OpenEMR\Common\Database\Database;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Uuid\UuidRegistry;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Webmozart\Assert\InvalidArgumentException;

/**
 * Usage:
 *   $uuidRegistryRepository = RepositoryFactory::createRepository(UuidRegistryRepository::class);
 *   $affected = $uuidRegistryRepository->removeByUuidAndTable($uuid, 'users');
 *
 * @phpstan-type TUuidRegistry = array{
 *     uuid: string,
 *     table_name: string,
 *     table_id: string,
 *     table_vertical: string,
 *     couchdb: string,
 *     document_drive: int,
 *     mapped: int,
 *     created: string,
 * }
 *
 * @extends AbstractRepository<TUuidRegistry>
 */
class UuidRegistryRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct('uuid_registry');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function removeByUuidAndTable(string $uuid, string $table): int
    {
        try {
            $uuidBytes = UuidRegistry::isValidStringUUID($uuid) ? UuidRegistry::uuidToBytes($uuid) : $uuid;
        } catch (InvalidUuidStringException $exception) {
            throw new InvalidArgumentException(sprintf('UUID %s is invalid', $uuid), 0, $exception);
        }

        return $this->removeBy([
            'table_name' => $table,
            'uuid' => $uuidBytes,
        ]);
    }
}
