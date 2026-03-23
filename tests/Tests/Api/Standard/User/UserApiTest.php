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

namespace OpenEMR\Tests\Api\Standard\User;

use OpenEMR\Common\Database\Repository\User\UserSecureRepository;
use OpenEMR\Fixture\CompositeFixture;
use OpenEMR\Fixture\CompositeFixtureFactory;
use OpenEMR\RestControllers\Standard\User\UserRestController;
use OpenEMR\Services\User\UserService;
use OpenEMR\Tests\Api\ApiTestClient;
use OpenEMR\Tests\Api\Standard\Common\UserApiPatchDataProviderAwareTrait;
use OpenEMR\Tests\Common\Auth\AssertCorrectUserPasswordAwareTrait;
use OpenEMR\Fixture\Purger\CompositePurger;
use OpenEMR\Fixture\Purger\CompositePurgerFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('api')]
#[Group('api-standard')]
#[Group('api-standard-user')]
#[CoversClass(UserRestController::class)]
#[CoversMethod(UserRestController::class, 'getOne')]
#[CoversMethod(UserRestController::class, 'patch')]
class UserApiTest extends TestCase
{
    use UserApiPatchDataProviderAwareTrait;
    use AssertValidUserAwareTrait;
    use AssertCorrectUserPasswordAwareTrait;

    private readonly CompositePurger $purger;

    private readonly CompositeFixture $fixture;

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $this->userSecureRepository = UserSecureRepository::getInstance();

        $this->purger = CompositePurgerFactory::createPurgeable();
        $this->purger->purge();

        $this->fixture = CompositeFixtureFactory::createLikeCleanInstallation();
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
     * Logged in as an admin, so expecting to see admin username
     *
     * @see UserRestController::getOne()
     * @see UserService::getOneByUuid()
     */
    #[Test]
    public function getOneTest(): void
    {
        $response = $this->testClient->request('GET', '/apis/default/api/user/me');
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);

        $this->assertValidUser($json['data']);

        $this->assertEquals('admin', $json['data']['username']);
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
        $response = $this->testClient->request('PATCH', '/apis/default/api/user/me', [], $data);
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
        $response = $this->testClient->request('PATCH', '/apis/default/api/user/me', [], $data);
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        $this->assertValidUser($json['data']);

        if (isset($data['password'])) {
            $this->assertCorrectUserPassword($data['password'], $json['data']);
        }

        $response = $this->testClient->request('GET','/apis/default/api/user/me');
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
}
