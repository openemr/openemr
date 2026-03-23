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
 * @template-extends AbstractRepository<TEntity>
 */
abstract class IdAwareAbstractRepository extends AbstractRepository
{
    /** @use IdAwareRepositoryTrait<TEntity> */
    use IdAwareRepositoryTrait;
}
