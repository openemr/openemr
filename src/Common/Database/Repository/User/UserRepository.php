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

namespace OpenEMR\Common\Database\Repository\User;

use OpenEMR\Common\Database\Repository\IdAwareAbstractRepository;
use OpenEMR\Common\Uuid\UuidRegistry;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Webmozart\Assert\InvalidArgumentException;

/**
 * Related to users_secure DB table
 *
 * Usage:
 *   $userRepository = RepositoryFactory::createRepository(UserRepository::class);
 *   $isUuidTaken = $userRepository->countByUuid($uuid) > 0;
 *   $user = $userRepository->findOneByUuid($uuid);
 *   $user = $userRepository->findOneByUsername('igormukhin');
 *   $nursesCount = $userRepository->countBy(['specialty' => 'Nursing']);
 *
 * @phpstan-type TUser = array{
 *     id: int,
 *     uuid: ?string,
 *     username: ?string,
 *     email: ?string,
 *     authorized: ?int,
 *     active: int,
 *     suffix: ?string,
 *     fname: ?string,
 *     mname: ?string,
 *     lname: ?string,
 *     specialty: ?string,
 *     organization: ?string,
 *     portal_user: int,
 * }
 *
 * @extends IdAwareAbstractRepository<TUser>
 */
class UserRepository extends IdAwareAbstractRepository
{
    public function __construct()
    {
        parent::__construct('users');
    }

    public function countByUuid(string $uuid): int
    {
        try {
            $uuidBytes = UuidRegistry::uuidToBytes($uuid);
        } catch (InvalidUuidStringException $exception) {
            throw new InvalidArgumentException(sprintf('UUID %s is invalid', $uuid), 0, $exception);
        }

        return $this->countBy([
            'uuid' => $uuidBytes
        ]);
    }

    /**
     * @phpstan-return TUser|null
     */
    public function findOneByUuid(string $uuid): array|null
    {
        try {
            $uuidBytes = UuidRegistry::uuidToBytes($uuid);
        } catch (InvalidUuidStringException $exception) {
            throw new InvalidArgumentException(sprintf('UUID %s is invalid', $uuid), 0, $exception);
        }

        return $this->findOneBy([
            'uuid' => $uuidBytes
        ]);
    }

    /**
     * @phpstan-return TUser|null
     */
    public function findOneByUsername(string $username): array|null
    {
        return $this->findOneBy([
            'username' => $username,
        ]);
    }
}
