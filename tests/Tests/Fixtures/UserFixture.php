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

use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Common\Auth\Password\RandomPasswordGenerator;
use OpenEMR\Common\Database\Repository\RepositoryFactory;
use OpenEMR\Common\Database\Repository\User\UserRepository;
use OpenEMR\Common\Database\Repository\User\UserSecureRepository;
use OpenEMR\Services\Acl\AclGroupMemberService;
use OpenEMR\Services\UserService;

/**
 * @phpstan-import-type TUser from UserRepository
 * @template-extends UuidAwareFixture<TUser>
 */
class UserFixture extends UuidAwareFixture
{
    private readonly UserSecureRepository $userSecureRepository;

    private readonly AclGroupMemberService $aclGroupMemberService;

    private readonly RandomPasswordGenerator $randomPasswordGenerator;

    private readonly AuthHash $authHash;

    public function __construct()
    {
        parent::__construct(
            RepositoryFactory::createRepository(UserRepository::class)
        );

        $this->userSecureRepository = RepositoryFactory::createRepository(UserSecureRepository::class);
        $this->aclGroupMemberService = new AclGroupMemberService(new UserService());
        $this->randomPasswordGenerator = RandomPasswordGenerator::getInstance();
        $this->authHash = new AuthHash();
    }

    public function load(): void
    {
        $this->loadFromFile(sprintf('%s/data/users.json', __DIR__));
    }

    public function getRecordByUsername(string $username): array
    {
        return $this->getRecordBy('username', $username);
    }

    protected function loadRecord(array $record): array
    {
        $password = $record['password'] ?: $this->randomPasswordGenerator->generatePassword();
        $record['password'] = $this->authHash->passwordHash($password);
        $record['authorized'] = 1;

        $record = parent::loadRecord($record);

        unset($record['password']);

        return $record;
    }

    protected function removeRecord(array $record): void
    {
        $this->aclGroupMemberService->deleteUserFromAllGroups($record);

        parent::removeRecord($record);

        $this->userSecureRepository->remove($record['id']);
    }
}
