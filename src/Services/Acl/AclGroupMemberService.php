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

namespace OpenEMR\Services\Acl;

use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Database\Repository\User\UserRepository;
use OpenEMR\Common\Utils\ArrayUtils;
use OpenEMR\Core\Traits\SingletonTrait;
use OpenEMR\Gacl\GaclApi;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @phpstan-type TUserInput = array{
 *     fname: string,
 *     mname: ?string,
 *     lname: string,
 *     username: string,
 * }
 *
 * @phpstan-type TUserOutput = array{
 *     id: int,
 *     uuid: string,
 *     fname: string,
 *     mname: ?string,
 *     lname: string,
 *     email: string,
 *     username: string,
 * }
 */
class AclGroupMemberService
{
    use SingletonTrait;

    private const ALLOWED_USER_FIELDS = [
        'id',
        'uuid',
        'fname',
        'mname',
        'lname',
        'email',
        'username',
    ];

    protected static function createInstance(): static
    {
        return new self(
            new GaclApi(),
            AclGroupService::getInstance(),
            UserRepository::getInstance(),
        );
    }

    public function __construct(
        private readonly GaclApi $acl,
        private readonly AclGroupService $aclGroupService,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * Add User member into ACL group by Group ID
     *
     * @see AclExtended::setUserAro
     *
     * @phpstan-param TUserInput $user
     * @phpstan-param array{order?: int, hidden?: bool} $option
     *
     * @throws InvalidArgumentException
     */
    public function addUserToGroup(array $user, int $groupId, array $options = []): void
    {
        Assert::keyExists($user, 'fname');
        Assert::keyExists($user, 'mname');
        Assert::keyExists($user, 'lname');
        Assert::keyExists($user, 'username');

        if ('' !== $user['mname']) {
            $fullName = sprintf('%s %s %s', $user['fname'], $user['mname'], $user['lname']);
        } else {
            $fullName = sprintf('%s %s', $user['fname'], $user['lname']);
        }
        $username = $user['username'];
        $memberOrder = $options['order'] ?? 0;
        $memberHidden = ($options['hidden'] ?? false) ? 1 : 0;

        $userAroId = $this->acl->get_object_id('users', $username, 'ARO');
        if (false !== $userAroId) {
            $this->acl->edit_object($userAroId, 'users', $fullName, $username, $memberOrder, $memberHidden, 'ARO');
        } else {
            $this->acl->add_object('users', $fullName, $username, $memberOrder, $memberHidden, 'ARO');
        }

        // Expecting non-False when member added to group or already a member of group
        Assert::notFalse(
            $this->acl->add_group_object(
                $groupId,
                'users',
                $username,
            ),
            'Unknown error during ACL Group Member creation'
        );
    }

    /**
     * Return array of Members (Users) of given Group
     *
     * If user is no longer exists - it filtered out from result
     *
     * @phpstan-return array<TUserOutput>
     * @throws InvalidArgumentException
     */
    public function getAll(int $groupId): array
    {
        $groupObjects = $this->acl->get_group_objects($groupId, 'ARO', 'RECURSE');

        return array_map(
            fn (array $user): array => ArrayUtils::filter($user, self::ALLOWED_USER_FIELDS),
            array_filter(array_map(
                fn (string $username): array|null => $this->userRepository->findOneByUsername($username) ?: null,
                $groupObjects['users'] ?? []
            ))
        );
    }

    /**
     * @todo Do we need to Recursively remove from child groups?
     *
     * @phpstan-param TUserInput $user
     * @throws InvalidArgumentException
     */
    public function deleteUserFromGroup(array $user, int $groupId): void
    {
        Assert::keyExists($user, 'username');

        Assert::true(
            $this->aclGroupService->isIdValid($groupId),
            sprintf('Group with ID %s was not found', $groupId)
        );

        Assert::true(
            $this->isUserMemberOfGroup($user, $groupId),
            sprintf('User %d is not a member of Group %d', $user['username'], $groupId)
        );

        Assert::notFalse(
            $this->acl->del_group_object($groupId, 'users', $user['username'], 'ARO'),
            'Unknown error during User member removal from ACL Group'
        );
    }

    /**
     * @phpstan-param TUserInput $user
     * @throws InvalidArgumentException
     */
    public function deleteUserFromAllGroups(array $user): void
    {
        Assert::keyExists($user, 'username');

        $userAroId = $this->acl->get_object_id('users', $user['username'], 'ARO');
        if (!$userAroId) {
            // User not registered in ACL, nothing to clean
            return;
        }

        /** @var int[] $groups */
        $groupIds = $this->acl->get_object_groups($userAroId, 'ARO');
        if ([] === $groupIds) {
            // No groups this ARO is a member of
            return;
        }

        // Remove from each group
        foreach ($groupIds as $groupId) {
            $this->acl->del_group_object($groupId, 'users', $user['username'], 'ARO');
        }

        // Delete the ARO entry itself
        $this->acl->del_object($userAroId, 'ARO');
    }

    /**
     * @phpstan-param TUserInput $user
     * @throws InvalidArgumentException
     */
    public function isUserMemberOfGroup(array $user, int $groupId): bool
    {
        Assert::keyExists($user, 'username');

        $userAroId = $this->acl->get_object_id('users', $user['username'], 'ARO');
        if (!$userAroId) {
            return false;
        }

        return in_array(
            $groupId,
            array_map(
                'intval',
                $this->acl->get_object_groups($userAroId, 'ARO'),
            ),
            true
        );
    }
}
