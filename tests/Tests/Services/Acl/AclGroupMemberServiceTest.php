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

namespace OpenEMR\Tests\Services\Acl;

use OpenEMR\Services\Acl\AclGroupMemberService;
use OpenEMR\Tests\Fixtures\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[Group('group')]
#[Group('group-member')]
#[CoversClass(AclGroupMemberService::class)]
#[CoversMethod(AclGroupMemberService::class, 'addUserToGroup')]
#[CoversMethod(AclGroupMemberService::class, 'getAll')]
#[CoversMethod(AclGroupMemberService::class, 'deleteUserFromGroup')]
#[CoversMethod(AclGroupMemberService::class, 'deleteUserFromAllGroups')]
class AclGroupMemberServiceTest extends TestCase
{
    private UserFixture $userFixture;

    private AclGroupMemberService $aclGroupMemberService;

    protected function setUp(): void
    {
        $this->userFixture = UserFixture::getInstance();
        $this->aclGroupMemberService = AclGroupMemberService::getInstance();

        $this->userFixture->load();
    }

    protected function tearDown(): void
    {
        $this->userFixture->removeFixtureRecords();
    }

    #[Test]
    #[DataProvider('addUserToGroupDataProvider')]
    public function addUserToGroupTest(
        string $fixtureUsername,
        int $groupId
    ): void {
        $user = $this->userFixture->getRecordByUsername($fixtureUsername);

        $this->assertFalse(
            $this->aclGroupMemberService->isUserMemberOfGroup($user, $groupId),
            sprintf(
                'User %s (%d) expected to be NOT a member of Group %d at beginning of test',
                $user['username'],
                $user['id'],
                $groupId
            )
        );
        $this->aclGroupMemberService->addUserToGroup($user, $groupId);
        $this->assertTrue(
            $this->aclGroupMemberService->isUserMemberOfGroup($user, $groupId),
            sprintf(
                'User %s (%d) expected to be a member of Group %d at the end of test',
                $user['username'],
                $user['id'],
                $groupId
            )
        );
    }

    public static function addUserToGroupDataProvider(): iterable
    {
        yield ['badams', 11]; // Admins
        yield ['badams', 12]; // Clinicians
        yield ['ohernandez', 13]; // Physicians
    }

    #[Test]
    #[DataProvider('getAllDataProvider')]
    public function getAllTest(
        array $fixtureUsernames,
        int $groupId,
        int $expectedMembersCount,
    ): void {
        foreach ($fixtureUsernames as $fixtureUsername) {
            $user = $this->userFixture->getRecordByUsername($fixtureUsername);
            $this->aclGroupMemberService->addUserToGroup($user, $groupId);
        }

        $this->assertCount(
            $expectedMembersCount,
            $this->aclGroupMemberService->getAll($groupId)
        );
    }

    public static function getAllDataProvider(): iterable
    {
        yield [[], 12, 0]; // Clinicians
        yield [['smitchell'], 12, 1]; // Clinicians
        yield [['badams', 'ohernandez'], 13, 2]; // Physicians
    }

    #[Test]
    #[DataProvider('deleteUserFromGroupDataProvider')]
    public function deleteUserFromGroupTest(
        string $fixtureUsername,
        int $groupId,
    ): void {
        $user = $this->userFixture->getRecordByUsername($fixtureUsername);
        $this->aclGroupMemberService->addUserToGroup($user, $groupId);

        $this->assertTrue(
            $this->aclGroupMemberService->isUserMemberOfGroup($user, $groupId),
            sprintf(
                'User %s (%d) expected to be a member of Group %d at the beginning of test',
                $user['username'],
                $user['id'],
                $groupId
            )
        );
        $this->aclGroupMemberService->deleteUserFromGroup($user, $groupId);
        $this->assertFalse(
            $this->aclGroupMemberService->isUserMemberOfGroup($user, $groupId),
            sprintf(
                'User %s (%d) expected to be NOT a member of Group %d at end of test',
                $user['username'],
                $user['id'],
                $groupId
            )
        );
    }

    public static function deleteUserFromGroupDataProvider(): iterable
    {
        yield ['badams', 11]; // Admins
        yield ['badams', 12]; // Clinicians
        yield ['ohernandez', 13]; // Physicians
    }

    #[Test]
    #[DataProvider('deleteUserFromAllGroupsDataProvider')]
    public function deleteUserFromAllGroupsTest(
        string $fixtureUsername,
        array $groupIds,
    ): void {
        $user = $this->userFixture->getRecordByUsername($fixtureUsername);
        foreach ($groupIds as $groupId) {
            $this->aclGroupMemberService->addUserToGroup($user, $groupId);
            $this->assertTrue(
                $this->aclGroupMemberService->isUserMemberOfGroup($user, $groupId),
                sprintf(
                    'User %s (%d) expected to be a member of Group %d at the beginning of test',
                    $user['username'],
                    $user['id'],
                    $groupId
                )
            );
        }

        $this->aclGroupMemberService->deleteUserFromAllGroups($user);
        foreach ($groupIds as $groupId) {
            $this->assertFalse(
                $this->aclGroupMemberService->isUserMemberOfGroup($user, $groupId),
                sprintf(
                    'User %s (%d) expected to be NOT a member of Group %d at end of test',
                    $user['username'],
                    $user['id'],
                    $groupId
                )
            );
        }
    }

    public static function deleteUserFromAllGroupsDataProvider(): iterable
    {
        yield ['badams', [11, 12, 13]];
        yield ['ohernandez', [11, 12, 13]];
    }
}
