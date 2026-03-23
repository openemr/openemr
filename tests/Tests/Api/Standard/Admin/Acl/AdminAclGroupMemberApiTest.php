<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Api\Standard\Admin\Acl;

use OpenEMR\Fixture\AclGroupFixture;
use OpenEMR\Fixture\CompositeFixture;
use OpenEMR\Fixture\CompositeFixtureFactory;
use OpenEMR\Fixture\Purger\CompositePurger;
use OpenEMR\Fixture\Purger\CompositePurgerFactory;
use OpenEMR\RestControllers\Standard\Admin\Acl\AdminAclGroupMemberRestController;
use OpenEMR\Services\Acl\AclGroupMemberService;
use OpenEMR\Tests\Api\ApiTestClient;
use OpenEMR\Tests\Api\Standard\User\MeAwareTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('api')]
#[Group('api-standard')]
#[Group('api-standard-admin')]
#[Group('api-standard-admin-acl')]
#[Group('api-standard-admin-acl-group-member')]
#[CoversClass(AdminAclGroupMemberRestController::class)]
#[CoversMethod(AdminAclGroupMemberRestController::class, 'getAll')]
#[CoversMethod(AdminAclGroupMemberRestController::class, 'post')]
#[CoversMethod(AdminAclGroupMemberRestController::class, 'delete')]
class AdminAclGroupMemberApiTest extends TestCase
{
    use AssertValidAclGroupMemberAwareTrait;
    use MeAwareTrait;

    private readonly CompositePurger $purger;

    private readonly CompositeFixture $fixture;

    private AclGroupFixture $aclGroupFixture;

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $this->purger = CompositePurgerFactory::createPurgeable();
        $this->purger->purge();

        $this->aclGroupFixture = AclGroupFixture::getInstance();
        $this->fixture = new CompositeFixture([
            ...CompositeFixtureFactory::createLikeCleanInstallation()->getFixtures(),
            $this->aclGroupFixture,
        ]);
        $this->fixture->load();

        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    protected function tearDown(): void
    {
        $this->purger->restore();
    }

    /**
     * @see AdminAclGroupMemberRestController::getAll()
     * @see AclGroupMemberService::getAll()
     */
    #[Test]
    #[DataProvider('getAllDataProvider')]
    public function getAllTest(
        int $groupId,
    ): void {
        $response = $this->testClient->request('GET', sprintf('/apis/default/api/admin/acl/group/%d/member', $groupId));
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);
        $this->assertGreaterThan(0, count($json['data']));

        foreach ($json['data'] as $member) {
            $this->assertValidAclGroupMember($member);
        }
    }

    public static function getAllDataProvider(): iterable
    {
        yield [10]; // Users
        yield [11]; // Administrators
    }

    /**
     * @see AdminAclGroupMemberRestController::post()
     * @see AclGroupMemberService::insert()
     */
    #[Test]
    #[DataProvider('postSucceededDataProvider')]
    public function postSucceededTest(
        string|int $groupId,
        array $data,
    ): void {
        if (is_string($groupId)) {
            // Converting group value (string) to Group ID (integer)
            $groupId = $this->aclGroupFixture->getRecordBy('value', $groupId)['id'];
        }

        $uuid = $this->getMyUuid();
        $response = $this->testClient->request('POST', sprintf('/apis/default/api/admin/acl/group/%d/member/%s', $groupId, $uuid), [], $data);
        $this->assertEquals(201, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);
    }

    public static function postSucceededDataProvider(): iterable
    {
        yield 'No optional payload - Readers (not there)' => ['readers', []]; // Readers AclGroupFixture (additional_acl_groups.json)

        yield 'No optional payload - Users (already there)' => [10, []]; // Users
        yield 'No optional payload - Administrators (already there)' => [11, []]; // Administrators

        yield 'With optional payload - order' => [11, ['order' => 55]]; // Administrators
        yield 'With optional payload - hidden' => [11, ['hidden' => true]]; // Administrators
        yield 'With optional payload - order & hidden' => [11, ['order' => 55, 'hidden' => false]]; // Administrators
    }

    /**
     * @see AdminAclGroupMemberRestController::delete()
     * @see AclGroupMemberService::deleteById()
     */
    #[Test]
    public function deleteSucceededTest(): void
    {
        $uuid = $this->getMyUuid();

        $response = $this->testClient->request('DELETE', sprintf('/apis/default/api/admin/acl/group/%d/member/%s', 11, $uuid));
        $this->assertEquals(200, $response->getStatusCode());
    }
}
