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

namespace OpenEMR\Tests\Api\Standard\Admin\Acl;

use OpenEMR\RestControllers\Standard\Admin\Acl\AdminAclGroupRestController;
use OpenEMR\Services\Acl\AclGroupService;
use OpenEMR\Tests\Api\ApiTestClient;
use OpenEMR\Tests\Fixtures\Acl\AclGroupFixture;
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
#[Group('api-standard-admin-acl-group')]
#[CoversClass(AdminAclGroupRestController::class)]
#[CoversMethod(AdminAclGroupRestController::class, 'post')]
#[CoversMethod(AdminAclGroupRestController::class, 'getOne')]
#[CoversMethod(AdminAclGroupRestController::class, 'getAll')]
class AdminAclGroupApiTest extends TestCase
{
    private AclGroupFixture $fixture;

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixture = AclGroupFixture::getInstance();
        $this->fixture->load();
    }

    protected function tearDown(): void
    {
        // @todo Test DB isolation and wiping, as otherwise new records inserting into non-test db and only wiped manually like this:

        // Remove all groups added by post*Test
        $groupService = AclGroupService::getInstance();
        foreach (['inspectors', 'inspectorswithparentid'] as $groupValue) {
            $groupService->deleteByValue($groupValue);
        }

        // Remove all groups added by fixtures
        $this->fixture->removeFixtureRecords();

        // Remove test client created by ApiTestClient
        // $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    /**
     * @see AdminAclGroupRestController::post()
     * @see AclGroupService::insert()
     */
    #[Test]
    #[DataProvider('postFailedValidationDataProvider')]
    public function postFailedValidationTest(
        array $data,
        int $expectedValidationErrorsCount,
        ?array $expectedValidationErrors = null,
    ): void {
        $response = $this->testClient->request('POST', '/apis/default/api/admin/acl/group', [], $data);
        $this->assertEquals(400, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount($expectedValidationErrorsCount, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);
        $this->assertArrayNotHasKey('id', $json['data']);
        $this->assertArrayNotHasKey('id', $json['data']);

        if (null !== $expectedValidationErrors) {
            $this->assertEquals($expectedValidationErrors, $json['validationErrors']);
        }
    }

    public static function postFailedValidationDataProvider(): iterable
    {
        // Mandatory field missing
        yield 'Empty payload' => [[], 2, [
            'value' => [
                'Required::NON_EXISTENT_KEY' => 'value must be provided, but does not exist'
            ],
            'name' => [
                'Required::NON_EXISTENT_KEY' => 'name must be provided, but does not exist'
            ],
        ]];

        yield 'One mandatory field provided' => [[
            'name' => 'Inspectors',
        ], 1];

        // Name validation
        yield 'Too long name' => [[
            'value' => 'inspectors',
            'name' => str_pad('', 256, '_'),
        ], 1, [
            'name' => [
                'LengthBetween::TOO_LONG' => 'Group Name must be 255 characters or shorter',
            ],
        ]];

        yield 'Too short name' => [[
            'value' => 'inspectors',
            'name' => str_pad('', 2, '_'),
        ], 1, [
            'name' => [
                'LengthBetween::TOO_SHORT' => 'Group Name must be 3 characters or longer',
            ],
        ]];

        // Value validation
        yield 'Too long value' => [[
            'value' => str_pad('', 256, 'a'),
            'name' => 'Inspectors',
        ], 1, [
            'value' => [
                'LengthBetween::TOO_LONG' => 'Group Value must be 150 characters or shorter',
            ],
        ]];

        yield 'Too short value' => [[
            'value' => str_pad('', 2, 'a'),
            'name' => 'Inspectors',
        ], 1, [
            'value' => [
                'LengthBetween::TOO_SHORT' => 'Group Value must be 3 characters or longer',
            ],
        ]];

        yield 'Invalid value' => [[
            'value' => 'abcd1', // 1 is not allowed
            'name' => 'Inspectors',
        ], 1, [
            'value' => [
                'value' => 'Value abcd1 is not valid. Only lowercase letters (a-z) allowed.',
            ],
        ]];

        yield 'Taken value' => [[
            'value' => 'readers', // readers already loaded by fixtures
            'name' => 'Readers',
        ], 1, [
            'value' => [
                'value' => 'Value readers is taken',
            ],
        ]];

        // Parent ID validation
        yield 'Parent ID does not exists' => [[
            'parent_id' => 1, // 1 is not correct ID
            'value' => 'inspectors',
            'name' => 'Inspectors',
        ], 1, [
            'parent_id' => [
                'parent_id' => 'Parent Group with ID 1 does not exists.'
            ],
        ]];
    }

    /**
     * Parent ID fallback to 0
     *
     * @see AdminAclGroupRestController::post()
     * @see AclGroupService::insert()
     */
    #[Test]
    #[DataProvider('postSucceededDataProvider')]
    public function postSucceededTest(array $data): void
    {
        $response = $this->testClient->request('POST', '/apis/default/api/admin/acl/group', [], $data);
        $this->assertEquals(201, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        $this->assertNotEmpty($json['data']['id']);
        foreach ($data as $fieldName => $value) {
            $this->assertEquals($value, $json['data'][$fieldName]);
        }
    }

    public static function postSucceededDataProvider(): iterable
    {
        yield 'Minimal' => [[
            // No parent_id passed - existing root id will be used by default
            'value' => 'inspectors',
            'name' => 'Inspectors',
        ]];

        yield 'With Parent ID' => [[
            'parent_id' => 11,
            'value' => 'inspectorswithparentid',
            'name' => 'Inspectors with Parent ID',
        ]];
    }

    /**
     * @see AdminAclGroupRestController::getOne()
     * @see AclGroupService::getOneById()
     */
    #[Test]
    #[DataProvider('getOneFailedDataProvider')]
    public function getOneFailedTest(
        int|string $id,
        int $expectedStatusCode,
        int $expectedValidationErrorsCount,
        ?array $expectedValidationErrors = null,
    ): void {
        $response = $this->testClient->request('GET', sprintf('/apis/default/api/admin/acl/group/%s', $id));
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount($expectedValidationErrorsCount, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        if (null !== $expectedValidationErrors) {
            $this->assertEquals($expectedValidationErrors, $json['validationErrors']);
        }
    }

    public static function getOneFailedDataProvider(): iterable
    {
        yield 'Invalid identifier type' => ['not-integerish', 400, 1, ['ID not-integerish is invalid. Integer expected']];
        yield 'User does not exists' => [1, 404, 0];
    }

    /**
     * @see AdminAclGroupRestController::getOne()
     * @see AclGroupService::getOneById()
     */
    #[Test]
    public function getOneSucceededTest(): void
    {
        $record = $this->fixture->getRandomRecord();

        $response = $this->testClient->request('GET', sprintf('/apis/default/api/admin/acl/group/%s', $record['id']));
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);
        $this->assertEquals($record, $json['data']);
    }

    /**
     * It returns non-empty array of groups and each of them has expected fields
     *
     * @see AdminAclGroupRestController::getAll()
     * @see AclGroupService::getAll()
     */
    #[Test]
    public function getAllTest(): void
    {
        $response = $this->testClient->request('GET', '/apis/default/api/admin/acl/group');
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);
        $this->assertGreaterThan(0, count($json['data']));

        foreach ($json['data'] as $searchResult) {
            $this->assertArrayHasKey('id', $searchResult);
            $this->assertArrayHasKey('parent_id', $searchResult);
            $this->assertArrayHasKey('name', $searchResult);
            $this->assertArrayHasKey('value', $searchResult);
        }
    }

    /**
     * @see AdminAclGroupRestController::delete()
     * @see AclGroupService::deleteById()
     */
    #[Test]
    #[DataProvider('deleteFailedDataProvider')]
    public function deleteFailedTest(
        string $id,
        int $expectedValidationErrorsCount,
        ?array $expectedValidationErrors = null,
        int $expectedStatusCode = 400
    ): void {
        $response = $this->testClient->request('DELETE', sprintf('/apis/default/api/admin/acl/group/%s', $id));
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount($expectedValidationErrorsCount, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        if (null !== $expectedValidationErrors) {
            $this->assertEquals($expectedValidationErrors, $json['validationErrors']);
        }
    }

    public static function deleteFailedDataProvider(): iterable
    {
        yield 'Invalid ID' => ['invalid-id', 1, ['ID invalid-id is invalid. Integer expected']];
        yield 'No such group' => ['0', 1, ['Group 0 was not found']];
    }

    /**
     * @see AdminAclGroupRestController::delete()
     * @see AclGroupService::deleteById()
     */
    #[Test]
    public function deleteSucceededTest(): void
    {
        $record = $this->fixture->getRandomRecord();

        $response = $this->testClient->request('DELETE', sprintf('/apis/default/api/admin/acl/group/%s', $record['id']));
        $this->assertEquals(200, $response->getStatusCode());
    }
}
