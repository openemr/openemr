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

namespace OpenEMR\Tests\Api\Standard\Admin\User;

use OpenEMR\Common\Database\Repository\User\UserSecureRepository;
use OpenEMR\Fixture\CompositeFixture;
use OpenEMR\Fixture\CompositeFixtureFactory;
use OpenEMR\Fixture\Purger\CompositePurger;
use OpenEMR\Fixture\Purger\CompositePurgerFactory;
use OpenEMR\RestControllers\Standard\User\UserRestController;
use OpenEMR\Services\User\UserService;
use OpenEMR\Tests\Api\ApiTestClient;
use OpenEMR\Tests\Api\Standard\Common\UserApiPatchDataProviderAwareTrait;
use OpenEMR\Tests\Api\Standard\Common\UserApiPostDataProviderAwareTrait;
use OpenEMR\Tests\Api\Standard\User\AssertValidUserAwareTrait;
use OpenEMR\Tests\Api\Standard\User\MeAwareTrait;
use OpenEMR\Tests\Common\Auth\AssertCorrectUserPasswordAwareTrait;
use OpenEMR\Fixture\AdditionalUserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('api')]
#[Group('api-standard')]
#[Group('api-standard-admin-user')]
#[CoversClass(UserRestController::class)]
#[CoversMethod(UserRestController::class, 'getOne')]
#[CoversMethod(UserRestController::class, 'getAll')]
#[CoversMethod(UserRestController::class, 'post')]
#[CoversMethod(UserRestController::class, 'patch')]
class AdminUserApiTest extends TestCase
{
    use UserApiPostDataProviderAwareTrait;
    use UserApiPatchDataProviderAwareTrait;
    use AssertValidUserAwareTrait;
    use AssertCorrectUserPasswordAwareTrait;
    use MeAwareTrait;

    private readonly CompositePurger $purger;

    private readonly CompositeFixture $fixture;

    protected function setUp(): void
    {
        $this->userSecureRepository = UserSecureRepository::getInstance();

        $this->purger = CompositePurgerFactory::createPurgeable();
        $this->purger->purge();

        $this->fixture = new CompositeFixture([
            ...CompositeFixtureFactory::createLikeCleanInstallation()->getFixtures(),
            AdditionalUserFixture::getInstance(),
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
     * @see UserRestController::getOne()
     * @see UserService::getOneByUuid()
     */
    #[Test]
    public function getOneTest(): void
    {
        $record = $this->getMyUser();
        $response = $this->testClient->request('GET', sprintf(
            '/apis/default/api/admin/user/%s',
            $record['uuid'],
        ));
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        foreach ($record as $fieldName => $value) {
            $this->assertEquals($record[$fieldName], $json['data'][$fieldName], sprintf(
                "Expected '%s', got '%s'",
                $record[$fieldName],
                $json['data'][$fieldName],
            ));
        }
    }

    /**
     * @see UserRestController::getAll()
     * @see UserService::getAll()
     */
    #[Test]
    #[DataProvider('getAllDataProvider')]
    public function getAllTest(array $searchQuery, int $expectedCount, array $expectedData = []): void
    {
        $response = $this->testClient->request('GET', '/apis/default/api/admin/user', $searchQuery);
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        $searchResults = $json['data'];
        $this->assertCount($expectedCount, $searchResults);

        if ([] === $expectedData) {
            return;
        }

        foreach ($searchResults as $resultIndex => $searchResult) {
            foreach (array_keys($expectedData) as $dataAttribute) {
                $this->assertEquals(
                    $expectedData[$dataAttribute],
                    $searchResult[$dataAttribute],
                    sprintf(
                        'Result #%d - Expected %s to be %s, got %s',
                        $resultIndex,
                        $dataAttribute,
                        $expectedData[$dataAttribute],
                        $searchResult[$dataAttribute],
                    ),
                );
            }
        }
    }

    public static function getAllDataProvider(): iterable
    {
        yield 'No search' => [
            [], // No search parameters
            7, // 4 existing default users + 3 from fixtures
        ];

        yield 'Search users by First name' => [
            ['fname' => 'Benjamin'],
            1,
            [
                'fname' => 'Benjamin',
                'email' => 'badams@example.com',
            ],
        ];

        yield 'Search users by Last name' => [
            ['lname' => 'Hernandez'],
            1,
            [
                'lname' => 'Hernandez',
                'email' => 'ohernandez@example.com',
            ],
        ];

        yield 'Search users by Email' => [
            ['email' => 'ohernandez@example.com'],
            1,
            ['email' => 'ohernandez@example.com'],
        ];

        yield 'Search users by Tax ID' => [
            ['federaltaxid' => '888-88-8888'],
            1,
            [
                'federaltaxid' => '888-88-8888',
                'email' => 'ohernandez@example.com',
            ],
        ];

        yield 'Search users by Specialty' => [
            ['specialty' => 'Nursing'],
            2,
            ['specialty' => 'Nursing'],
        ];

        yield 'Search users by State' => [
            ['state' => 'NY'],
            2,
            ['state' => 'NY'],
        ];

        yield 'Search users by City' => [
            ['city' => 'New York'],
            2,
            ['city' => 'New York'],
        ];

        yield 'Search users by ZIP' => [
            ['zip' => '10002'],
            1,
            [
                'zip' => '10002',
                'email' => 'smitchell@example.com',
            ],
        ];

        // @todo Partial search support and phone normalization (123-456789 > 123456789 should be found by 456789 or 123456)
        yield 'Search users by Phone' => [
            ['phone' => '2125551200'],
            1,
            [
                'phone' => '2125551200',
                'email' => 'smitchell@example.com',
            ],
        ];

        yield 'Search users by Organization' => [
            ['organization' => 'Evergreen Health'],
            1,
            [
                'organization' => 'Evergreen Health',
                'email' => 'smitchell@example.com',
            ],
        ];

        yield 'Search users by License' => [
            ['state_license_number' => 'WA665544'],
            1,
            [
                'state_license_number' => 'WA665544',
                'email' => 'smitchell@example.com',
            ],
        ];

        // @todo Add other fields?
    }

    /**
     * @see UserRestController::post()
     * @see UserService::insert()
     */
    #[Test]
    #[DataProvider('postFailedValidationDataProvider')]
    public function postFailedValidationTest(
        array $data,
        int|array $expectedValidationErrors,
    ): void {
        $response = $this->testClient->request('POST', '/apis/default/api/admin/user', [], $data);
        $this->assertEquals(400, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);

        if (is_integer($expectedValidationErrors)) {
            $this->assertCount($expectedValidationErrors, $json['validationErrors']);
        }
        $this->assertCount(0, $json['internalErrors']);
        $this->assertArrayNotHasKey('id', $json['data']);
        $this->assertArrayNotHasKey('uuid', $json['data']);

        if (is_array($expectedValidationErrors)) {
            $this->assertEquals($expectedValidationErrors, $json['validationErrors']);
        }
    }

    public static function postFailedValidationDataProvider(): iterable
    {
        yield 'Empty payload' => [[], 3];

        yield 'One mandatory field provided' => [[
            'fname' => 'Correct',
        ], 2];

        yield 'Two mandatory fields provided' => [[
            'fname' => 'Correct',
            'lname' => 'Correct',
        ], 1];

        yield 'Too long fname' => [[
            'fname' => str_pad('', 256, '_'),
            'lname' => 'Correct',
            'username' => 'testuser0',
        ], 1, [
            'fname' => [
                'LengthBetween::TOO_LONG' => 'First Name must be 255 characters or shorter',
            ],
        ]];

        yield 'Too short lname' => [[
            'fname' => 'Correct',
            'lname' => str_pad('', 1, '_'),
            'username' => 'testuser0',
        ], 1, [
            'lname' => [
                'LengthBetween::TOO_SHORT' => 'Last Name must be 2 characters or longer',
            ],
        ]];

        yield 'Too long lname' => [[
            'fname' => 'Correct',
            'lname' => str_pad('', 256, '_'),
            'username' => 'testuser0',
        ], 1, [
            'lname' => [
                'LengthBetween::TOO_LONG' => 'Last Name must be 255 characters or shorter',
            ],
        ]];

        yield 'Invalid email' => [[
            'fname' => 'Correct',
            'lname' => 'Correct',
            'email' => 'invalid@examplecom',
            'username' => 'testuser0',
        ], 1, [
            'email' => [
                'Email::INVALID' => 'Email invalid@examplecom is not a valid email',
            ],
        ]];

        yield 'Too short username' => [[
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => str_pad('', 2, '_'),
        ], 1, [
            'username' => [
                'LengthBetween::TOO_SHORT' => 'Username must be 3 characters or longer',
            ],
        ]];

        yield 'Too long username' => [[
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => str_pad('', 33, '_'),
        ], 1, [
            'username' => [
                'LengthBetween::TOO_LONG' => 'Username must be 32 characters or shorter',
            ],
        ]];

        yield 'Too short password' => [[
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => 'testuser0',
            'password' => str_pad('aB1@', 8, '_'),
        ], 1, [
            'password' => [
                'Password::TOO_WEAK' => 'Provided password is too weak'
            ],
        ]];

        yield 'Too long password' => [[
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => 'testuser0',
            'password' => str_pad('aB1@', 73, '_'),
        ], 1, [
            'password' => [
                'LengthBetween::TOO_LONG' => 'Password must be 72 characters or shorter',
            ],
        ]];

        if ((bool) $GLOBALS['secure_password']) {
            yield 'Not strong password passed' => [[
                'fname' => 'Correct',
                'lname' => 'Correct',
                'username' => 'testuser0',
                'password' => str_pad('aB', 9, '_'), // Valid length, but missing numerics
            ], 1, [
                'password' => [
                    'Callback::INVALID_VALUE' => 'Password is invalid',
                ],
            ]];
        }
    }

    /**
     * Password autogenerated in this case
     *
     * @see UserRestController::post()
     * @see UserService::insert()
     */
    #[Test]
    #[DataProvider('postSucceededDataProvider')]
    public function postSucceededTest(array $data): void
    {
        $response = $this->testClient->request('POST', '/apis/default/api/admin/user', [], $data);
        $this->assertEquals(201, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);
        $this->assertNotEmpty($json['data']['id']);
        $this->assertNotEmpty($json['data']['uuid']);
        $this->assertNotEmpty($json['data']['username']);
        $this->assertNotEmpty($json['data']['password']);

        $this->assertEquals($data['username'], $json['data']['username']); // sent === returned

        if (isset($data['password'])) {
            $this->assertEquals($data['password'], $json['data']['password']); // sent === returned
            $this->assertCorrectUserPassword($data['password'], $json['data']); // returned === actual
        }

        $response = $this->testClient->request('GET', sprintf('/apis/default/api/admin/user/%s', $json['data']['uuid']));
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        foreach ($data as $fieldName => $value) {
            if ('password' === $fieldName) {
                continue; // We omit password checking here, as it was checked proper way before
            }

            $this->assertEquals($value, $json['data'][$fieldName]);
        }
    }

    /**
     * @see UserRestController::patch()
     * @see UserService::patch()
     */
    #[Test]
    #[DataProvider('patchFailedValidationDataProvider')]
    public function patchFailedValidationTest(
        array $data,
        int $expectedValidationErrorsCount,
        ?array $expectedValidationErrors = null,
    ): void {
        $uuid = $this->getMyUuid();
        $response = $this->testClient->request('PATCH', sprintf('/apis/default/api/admin/user/%s', $uuid), [], $data);
        $this->assertEquals(400, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount($expectedValidationErrorsCount, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);
        $this->assertArrayNotHasKey('id', $json['data']);
        $this->assertArrayNotHasKey('uuid', $json['data']);

        if (null !== $expectedValidationErrors) {
            $this->assertEquals($expectedValidationErrors, $json['validationErrors']);
        }
    }

    /**
     * @see UserRestController::patch()
     * @see UserService::patch()
     */
    #[Test]
    #[DataProvider('patchSucceededDataProvider')]
    public function patchSucceededTest(array $data): void
    {
        $uuid = $this->getMyUuid();
        $response = $this->testClient->request('PATCH', sprintf('/apis/default/api/admin/user/%s', $uuid), [], $data);
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        $this->assertValidUser($json['data']);

        if (isset($data['password'])) {
            $this->assertCorrectUserPassword($data['password'], $json['data']);
        }

        $response = $this->testClient->request('GET', sprintf('/apis/default/api/admin/user/%s', $uuid));
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        foreach ($data as $fieldName => $value) {
            if ('password' === $fieldName) {
                continue;
            }

            $this->assertEquals($value, $json['data'][$fieldName]);
        }
    }

    /**
     * @see UserRestController::delete()
     * @see UserService::deleteOneByUuid()
     */
    #[Test]
    #[DataProvider('deleteFailedDataProvider')]
    public function deleteFailedTest(
        string $uuid,
        int $expectedValidationErrorsCount,
        ?array $expectedValidationErrors = null,
        int $expectedStatusCode = 400,
    ): void {
        $response = $this->testClient->request('DELETE', sprintf('/apis/default/api/admin/user/%s', $uuid));
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
        yield 'Invalid UUID' => ['invalid-uuid', 1, ['UUID invalid-uuid is invalid']];
        yield 'No such user' => ['A01236541EB743BB8F7C96E777EDD3A7', 1, ['User with UUID A01236541EB743BB8F7C96E777EDD3A7 not found']];
        // @todo yield 'No access to manipulate given user' => [];
    }

    /**
     * @see UserRestController::delete()
     * @see UserService::deleteByUuid()
     */
    #[Test]
    public function deleteSucceededTest(): void
    {
        $response = $this->testClient->request('DELETE', sprintf(
            '/apis/default/api/admin/user/%s',
            $this->getMyUuid(),
        ));

        $this->assertEquals(200, $response->getStatusCode());
    }
}
