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

namespace OpenEMR\Services\User;

use Exception;
use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Common\Auth\Password\RandomPasswordGenerator;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\Repository\User\GroupRepository;
use OpenEMR\Common\Database\Repository\User\UserRepository;
use OpenEMR\Common\Database\Repository\User\UserSecureRepository;
use OpenEMR\Common\Database\Repository\UuidRegistryRepository;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\Traits\SingletonTrait;
use OpenEMR\Services\Acl\AclGroupMemberService;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class UserService
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        return new self(
            AclGroupMemberService::getInstance(),
            UserRepository::getInstance(),
            UserSecureRepository::getInstance(),
            UuidRegistryRepository::getInstance(),
            GroupRepository::getInstance(),
            RandomPasswordGenerator::getInstance(),
            AuthHash::getInstance(),
        );
    }

    public function __construct(
        private readonly AclGroupMemberService $aclGroupMemberService,
        private readonly UserRepository $userRepository,
        private readonly UserSecureRepository $userSecureRepository,
        private readonly UuidRegistryRepository $uuidRegistryRepository,
        private readonly GroupRepository $groupRepository,
        private readonly RandomPasswordGenerator $randomPasswordGenerator,
        private readonly AuthHash $authHash,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getOneByUuid(string $uuid): ?array
    {
        return $this->userRepository->findOneByUuid($uuid);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function insert(array $data): array
    {
        Assert::keyExists($data, 'fname');
        Assert::keyExists($data, 'lname');
        Assert::keyExists($data, 'username');

        try {
            $password = $data['password'] ?: $this->randomPasswordGenerator->generatePassword();

            $aclGroupIds = $data['acl_group_ids'] ?? [];
            unset($data['acl_group_ids']);

            QueryUtils::startTransaction();
            $uuid = (new UuidRegistry(['table_name' => 'users']))->createUuid();

            $data['id'] = $this->userRepository->insert(array_merge($data, [
                'uuid' => $uuid,
                'password' => 'NoLongerUsed',
                'authorized' => 1,
            ]));

            $this->userSecureRepository->insert([
                'id' => $data['id'],
                'username' => $data['username'],
                'password' => $this->authHash->passwordHash($password),
            ]);

            $this->groupRepository->insert([
                'user' => $data['username'],
                'name' => 'Default',
            ]);

            if ($aclGroupIds) {
                foreach ($aclGroupIds as $aclGroupId) {
                    $this->aclGroupMemberService->addUserToGroup(
                        $data,
                        $aclGroupId
                    );
                }
            }

            QueryUtils::commitTransaction();

            return [
                'id' => $data['id'],
                'uuid' => UuidRegistry::uuidToString($uuid),
                'password' => $password,
            ];
        } catch (Exception $e) {
            QueryUtils::rollbackTransaction();
            throw $e;
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function deleteOneByUuid(string $uuid): void
    {
        $user = $this->userRepository->findOneByUuid($uuid);
        Assert::notNull($user, sprintf('User with UUID %s not found', $uuid));

        QueryUtils::startTransaction();
        try {
            // @todo Remove uuid from uuid_mapping?
            $this->aclGroupMemberService->deleteUserFromAllGroups($user);
            $this->userSecureRepository->remove($user['id']);
            $this->uuidRegistryRepository->removeByUuidAndTable($uuid, 'users');
            $this->userRepository->remove($user['id']);
            QueryUtils::commitTransaction();
        } catch (Exception $e) {
            QueryUtils::rollbackTransaction();
            throw $e;
        }
    }
}
