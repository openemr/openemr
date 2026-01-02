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

use OpenEMR\Common\Database\Exception\DatabaseQueryException;
use OpenEMR\Common\Database\Exception\NonUniqueDatabaseResultException;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @template TEntity of array
 * @mixin AbstractRepository<TEntity>
 */
trait IdAwareRepositoryTrait
{
    /**
     * Usage:
     *   $user = $this->find($userId);
     *
     * @phpstan-return TEntity|null
     *
     * @throws DatabaseQueryException
     * @throws NonUniqueDatabaseResultException
     */
    public function find(string|int $id): null|array
    {
        $data = $this->db->find($this->table, $id);
        if (null === $data) {
            return null;
        }

        return $this->normalize($data);
    }

    /**
     * Usage:
     *   $affected = $this->remove($id);
     *
     * @throws InvalidArgumentException
     * @throws DatabaseQueryException
     */
    public function remove(int $id): int
    {
        return $this->db->removeBy($this->table, ['id' => $id]);
    }
}
