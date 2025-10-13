<?php

declare(strict_types=1);

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Api;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\RestControllers\UserRestController;
use OpenEMR\Services\UserService;
use OpenEMR\Tests\Common\Auth\AuthHashAwareTrait;
use OpenEMR\Tests\Fixtures\UserFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @see UserRestController
 */
#[Group('user')]
#[Group('api')]
#[CoversClass(UserRestController::class)]
#[CoversMethod(UserRestController::class, 'post')]
#[CoversMethod(UserRestController::class, 'getOne')]
#[CoversMethod(UserRestController::class, 'getAll')]
class UserApiTest extends TestCase
{
    use AuthHashAwareTrait;

    private readonly UserFixture $fixture;

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->fixture = new UserFixture();
        $this->fixture->load();
    }

    protected function tearDown(): void
    {
        // @todo Test DB isolation and wiping, as otherwise new records inserting into non-test db and only wiped manually like this:

        // Remove all users added by post*Test
        foreach (['testuser0', 'testuser1', 'testuser2', 'testuser3'] as $username) {
            $uuid = QueryUtils::getSingleScalarResultBy('users', 'uuid', ['username' => $username]);
            if (null !== $uuid) {
                QueryUtils::removeBy('uuid_registry', [
                    'table_name' => 'users',
                    'uuid' => $uuid,
                ]);
            }

            QueryUtils::removeBy('users', ['username' => $username]);
            QueryUtils::removeBy('users_secure', ['username' => $username]);
        }

        // Remove all users added by fixtures
        $this->fixture->removeFixtureRecords();
    }

    /**
     * @see UserRestController::post()
     * @see UserService::insert()
     */
    #[Test]
    #[DataProvider('postFailedValidationDataProvider')]
    public function postFailedValidationTest(
        array $data,
        int $expectedValidationErrorsCount,
        ?array $expectedValidationErrors = null,
    ): void {
        $response = $this->testClient->request('POST', '/apis/default/api/user', [], $data);
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
                'email' => 'Email invalid@examplecom is not a valid email',
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
                'LengthBetween::TOO_SHORT' => 'Password must be 9 characters or longer',
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
            yield 'Not strength password' => [[
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
    public function postSuccessMinimalTest(): void
    {
        $data = [
            'fname' => 'Test',
            'lname' => 'User',
            'username' => 'testuser1',
        ];
        $response = $this->testClient->request('POST', '/apis/default/api/user', [], $data);
        $this->assertEquals(201, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);
        $this->assertNotEmpty($json['data']['id']);
        $this->assertNotEmpty($json['data']['uuid']);
        $this->assertNotEmpty($json['data']['password']);

        $this->assertCorrectUserPassword($json['data']['password'], $json['data']['id'], $data['username']); // returned === actual
    }

    /**
     * Password passed
     *
     * @see UserRestController::post()
     * @see UserService::insert()
     */
    #[Test]
    public function postSuccessMinimalWithPasswordTest(): void
    {
        $data = [
            'fname' => 'Test',
            'lname' => 'User',
            'username' => 'testuser2',
            'password' => 'testUser2Pa$$w0rd',
        ];

        $response = $this->testClient->request('POST', '/apis/default/api/user', [], $data);
        $this->assertEquals(201, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);
        $this->assertNotEmpty($json['data']['id']);
        $this->assertNotEmpty($json['data']['uuid']);
        $this->assertNotEmpty($json['data']['password']);

        $this->assertEquals($data['password'], $json['data']['password']); // sent === returned
        $this->assertCorrectUserPassword($json['data']['password'], $json['data']['id'], $data['username']); // returned === actual
    }

    /**
     * @see UserRestController::post()
     * @see UserService::insert()
     */
    #[Test]
    public function postSuccessFullTest(): void
    {
        $data = [
            'title' => 'Dr.',
            'fname' => 'Test',
            'mname' => 'M.',
            'lname' => 'User',
            'federaltaxid' => '999-99-9999',
            'federaldrugid' => 'EF2345678',
            'upin' => '',
            'facility_id' => '6',
            'facility' => 'Harmony Medical Group',
            'npi' => '1234567890',
            'email' => 'apatel@example.com',
            'specialty' => 'Cardiology',
            'billname' => null,
            'url' => null,
            'assistant' => null,
            'organization' => 'Harmony Medical',
            'valedictory' => 'MD',
            'street' => '321 Ocean Blvd',
            'streetb' => 'Suite 500',
            'city' => 'Miami',
            'state' => 'FL',
            'zip' => '33101',
            'phone' => '{305} 555-3310',
            'fax' => '{305} 555-3311',
            'phonew1' => '{305} 555-3312',
            'phonecell' => '{305} 555-3313',
            'notes' => null,
            'state_license_number' => 'FL778899',
            'username' => 'testuser3',
        ];

        $response = $this->testClient->request('POST', '/apis/default/api/user', [], $data);
        $this->assertEquals(201, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);
        $this->assertNotEmpty($json['data']['id']);
        $this->assertNotEmpty($json['data']['uuid']);

        $this->assertCorrectUserPassword($json['data']['password'], $json['data']['id'], $data['username']); // returned === actual

        $response = $this->testClient->request('GET', sprintf('/apis/default/api/user/%s', $json['data']['uuid']));
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        foreach ($data as $fieldName => $value) {
            $this->assertEquals($value, $json['data'][$fieldName]);
        }
    }

    /**
     * @see UserRestController::getOne()
     * @see UserService::getUserByUUID()
     */
    #[Test]
    public function getOneTest(): void
    {
        $record = $this->fixture->getRandomRecord();

        $response = $this->testClient->request('GET', sprintf('/apis/default/api/user/%s', $record['uuid']));
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);
        $this->assertEquals($record['uuid'], $json['data']['uuid']);

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
        $response = $this->testClient->request('GET', '/apis/default/api/user', $searchQuery);
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
}
