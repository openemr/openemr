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

namespace OpenEMR\Common\Database\Repository\User;

use OpenEMR\Common\Database\DatabaseManager;
use OpenEMR\Common\Database\DatabaseTables;
use OpenEMR\Common\Database\Repository\IdAwareAbstractRepository;
use OpenEMR\Common\Uuid\UuidRegistry;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Webmozart\Assert\InvalidArgumentException;

/**
 * Related to users_secure DB table
 *
 * Usage:
 *   $userRepository = UserRepository::class::getInstance();
 *   $isUuidTaken = $userRepository->countByUuid($uuid) > 0;
 *   $user = $userRepository->findOneByUuid($uuid);
 *   $user = $userRepository->findOneByUsername('igormukhin');
 *   $nursesCount = $userRepository->countBy(['specialty' => 'Nursing']);
 *
 * @phpstan-type TUser = array{
 *     id: int,
 *     uuid: string,
 *     username: ?string,
 *
 *     authorized: ?int,
 *     active: int,
 *     see_auth: ?int,
 *     portal_user: int,
 *
 *     title: ?string,
 *     suffix: ?string,
 *     fname: ?string,
 *     mname: ?string,
 *     lname: ?string,
 *
 *     specialty: ?string,
 *     organization: ?string,
 *     taxonomy: ?string,
 *     physician_type: ?string,
 *     npi: ?string,
 *     upin: ?string,
 *     federaltaxid: ?string,
 *     federaldrugid: ?string,
 *     billname: ?string,
 *
 *     email: ?string,
 *     email_direct: ?string,
 *     google_signin_email: ?string,
 *
 *     phone: ?string,
 *     fax: ?string,
 *     phonew1: ?string,
 *     phonew2: ?string,
 *     phonecell: ?string,
 *
 *     street: ?string,
 *     streetb: ?string,
 *     city: ?string,
 *     state: ?string,
 *     zip: ?string,
 *     country_code: ?string,
 *
 *     street2: ?string,
 *     streetb2: ?string,
 *     city2: ?string,
 *     state2: ?string,
 *     zip2: ?string,
 *     country_code2: ?string,
 *
 *     facility: ?string,
 *     facility_id: ?int,
 *     billing_facility: ?string,
 *     billing_facility_id: ?int,
 *
 *     cal_ui: ?int,
 *     calendar: ?int,
 *     main_menu_role: ?string,
 *     patient_menu_role: ?string,
 *     abook_type: ?string,
 *     default_warehouse: ?string,
 *     irnpool: ?string,
 *
 *     weno_prov_id: ?string,
 *     newcrop_user_role: ?string,
 *     cpoe: ?int,
 *
 *     url: ?string,
 *     assistant: ?string,
 *     valedictory: ?string,
 *     notes: ?string,
 *     info: ?string,
 *     source: ?string,
 *     supervisor_id: ?int,
 *     state_license_number: ?string,
 *     state_license_number2: ?string,
 *
 *     date_created: ?string,
 *     last_updated: ?string,
 * }
 *
 * @template-extends IdAwareAbstractRepository<TUser>
 */
class UserRepository extends IdAwareAbstractRepository
{
    protected static function createInstance(): static
    {
        return new self(
            DatabaseManager::getInstance(),
            DatabaseTables::TABLE_USERS,
            [
                'lname' => 'ASC',
                'fname' => 'ASC',
                'mname' => 'ASC',
            ],
        );
    }

    public function normalize(array $data): array
    {
        $data['uuid'] = UuidRegistry::uuidToString($data['uuid']);

        // We don't want password field to be returned
        // Also, it contains usually boilerplate string / not used
        unset($data['password']);

        return $data;
    }

    public function countByUuid(string $uuid): int
    {
        try {
            $uuidBytes = UuidRegistry::uuidToBytes($uuid);
        } catch (InvalidUuidStringException $exception) {
            throw new InvalidArgumentException(sprintf('UUID %s is invalid', $uuid), 0, $exception);
        }

        return $this->countBy([
            'uuid' => $uuidBytes,
        ]);
    }

    /**
     * @phpstan-return TUser|null
     *
     * @throws InvalidArgumentException
     */
    public function findOneByUuid(string $uuid): array|null
    {
        try {
            $uuidBytes = UuidRegistry::uuidToBytes($uuid);
        } catch (InvalidUuidStringException $exception) {
            throw new InvalidArgumentException(sprintf('UUID %s is invalid', $uuid), 0, $exception);
        }

        return $this->findOneBy([
            'uuid' => $uuidBytes,
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

    /**
     * @phpstan-return array<TUser>
     */
    public function findActive(): array
    {
        return $this->findOneBy([
            'active' => 1,
        ]);
    }
}
