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
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Acl\AclGroupMemberService;
use OpenEMR\Services\UserService;

class UserFixture extends UuidAwareFixture
{
    private AclGroupMemberService $aclGroupMemberService;

    private RandomPasswordGenerator $randomPasswordGenerator;

    private AuthHash $authHash;

    public function __construct()
    {
        parent::__construct('users');

        $this->aclGroupMemberService = new AclGroupMemberService(new UserService());
        $this->randomPasswordGenerator = new RandomPasswordGenerator();
        $this->authHash = new AuthHash();
    }

    public function load(): void
    {
        $this->loadFromFile(sprintf('%s/users.json', __DIR__));
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

        QueryUtils::removeById('users_secure', $record['id']);
    }
}
