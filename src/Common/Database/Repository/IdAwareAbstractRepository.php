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
use OpenEMR\Common\Database\DatabaseManagerFactory;
use OpenEMR\Common\Database\Exception\DatabaseQueryException;
use OpenEMR\Common\Database\Exception\NonUniqueDatabaseResultException;
use OpenEMR\Common\Database\SqlQueryException;
use Webmozart\Assert\InvalidArgumentException;

/**
 * Usage:
 *   class UserRepository extends IdAwareAbstractRepository {
 *       public function __construct() { parent::__construct('users'); }
 *       public function countBySpecialty(string $specialty): int { return $this->countBy(['specialty' => $specialty]); }
 *   }
 *
 *   $userRepository = new UserRepository();
 *   $user = $userRepository->find(1);
 *   $affected = $userRepository->remove(1);
 *   $nursesCount = $userRepository->countBy(['specialty' => 'Nursing']);
 *   $nursesCount = $userRepository->countBySpecialty('Nursing');
 *
 * @template TEntity of array
 * @extends AbstractRepository<TEntity>
 */
abstract class IdAwareAbstractRepository extends AbstractRepository
{
    /** @uses IdAwareRepositoryTrait<TEntity> */
    use IdAwareRepositoryTrait;
}
