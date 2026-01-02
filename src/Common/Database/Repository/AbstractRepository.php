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

namespace OpenEMR\Common\Database\Repository;

use OpenEMR\Common\Database\DatabaseManager;
use OpenEMR\Common\Database\Exception\DatabaseQueryException;
use OpenEMR\Common\Database\Exception\NonUniqueDatabaseResultException;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Core\Traits\SingletonTrait;
use Webmozart\Assert\InvalidArgumentException;

/**
 * Using this repository approach allow us to prepare for
 * Doctrine ORM migration by using repositories and similar naming convention
 *
 * @template TEntity of array
 */
abstract class AbstractRepository implements RepositoryInterface
{
    use SingletonTrait;

    abstract protected static function createInstance(): static;

    public function __construct(
        protected readonly DatabaseManager $db,
        protected readonly string $table,
        protected readonly array $orderBy = [],
    ) {
    }

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @phpstan-return TEntity
     */
    public function normalize(array $data): array
    {
        return $data;
    }

    /**
     * Usage:
     *   $user_id = $this->insert(['uuid' => $uuid, 'fname' => 'Igor', 'lname' => 'Mukhin', ...]);
     *
     * @phpstan-param TEntity $data
     *
     * @throws InvalidArgumentException
     * @throws SqlQueryException
     */
    public function insert(array $data): int
    {
        return $this->db->insert($this->table, $data);
    }

    /**
     * Usage:
     *   $affectedRows = $this->update(['address' => 'New address'], ['uuid' => $uuid]);
     *
     * @phpstan-param TEntity $data
     *
     * @throws InvalidArgumentException
     * @throws SqlQueryException
     */
    public function update(array $data, array $condition): int
    {
        return $this->db->update($this->table, $data, $condition);
    }

    /**
     * Usage:
     *   $usersCount = $this->count();
     *
     * @throws InvalidArgumentException
     * @throws SqlQueryException
     */
    public function count(): int
    {
        return $this->db->countBy($this->table, []);
    }

    /**
     * Usage:
     *   $nursesCount = $this->countBy(['specialty' => 'Nursing']);
     *
     * @throws InvalidArgumentException
     * @throws SqlQueryException
     */
    public function countBy(array $condition): int
    {
        return $this->db->countBy($this->table, $condition);
    }

    /**
     * Usage:
     *   $adminsPasswordHash = $this->getSingleScalarResultBy('password', ['username' => 'admin']);
     *
     * @throws InvalidArgumentException
     * @throws DatabaseQueryException
     */
    public function getSingleScalarResultBy(string $column, array $condition): null|string|int
    {
        return $this->db->getSingleScalarResultBy($this->table, $column, $condition);
    }

    /**
     * Usage:
     *   $adminsUsernames = $this->getSingleColumnResultBy('username', ['group' => 'Admins']);
     *
     * @throws InvalidArgumentException
     * @throws DatabaseQueryException
     */
    public function getSingleColumnResultBy(string $column, array $condition): array
    {
        return $this->db->getSingleColumnResultBy($this->table, $column, $condition);
    }

    /**
     * Usage:
     *   $admin = $this->findOneBy(['username' => 'admin']);
     *
     * @phpstan-return TEntity|null
     * @throws DatabaseQueryException
     * @throws NonUniqueDatabaseResultException
     */
    public function findOneBy(array $condition): null|array
    {
        $data = $this->db->findOneBy($this->table, $condition);
        if (null === $data) {
            return null;
        }

        return $this->normalize($data);
    }

    /**
     * Usage:
     *   $users = $this->findAll();
     *
     * @phpstan-return array<TEntity>
     * @throws DatabaseQueryException
     */
    public function findAll(): array
    {
        return array_map(
            fn ($data): array => $this->normalize($data),
            $this->db->findAll($this->table, $this->orderBy)
        );
    }

    /**
     * Usage:
     *   $authorizedUsers = $this->findBy(['authorized' => 1]);
     *
     * @phpstan-return array<TEntity>
     * @throws DatabaseQueryException
     */
    public function findBy(array $condition): array
    {
        return array_map(
            fn ($data): array => $this->normalize($data),
            $this->db->findBy($this->table, $condition, $this->orderBy)
        );
    }

    /**
     * Usage:
     *   $removed = $this->removeAll();
     */
    public function removeAll(): int
    {
        return $this->db->removeBy($this->table, []);
    }

    /**
     * Usage:
     *   $affected = $this->removeBy(['uuid' => $uuid]);
     *   $affected = $this->removeBy(['fname' => 'Igor', 'lname' => 'Mukhin']);
     *
     * @throws InvalidArgumentException
     * @throws DatabaseQueryException
     */
    public function removeBy(array $condition): int
    {
        return $this->db->removeBy($this->table, $condition);
    }
}
