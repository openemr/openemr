<?php

/**
 * Handles the TeleHealthVideoRegistrationController Unit Tests
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Comlink Inc <https://comlinkinc.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Comlink\OpenEMR\Modules\TeleHealthModule;

use Comlink\OpenEMR\Modules\TeleHealthModule\Controller\TeleHealthVideoRegistrationController;
use Comlink\OpenEMR\Modules\TeleHealthModule\Models\TeleHealthUser;
use Comlink\OpenEMR\Modules\TeleHealthModule\Models\UserVideoRegistrationRequest;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthProviderRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\Repository\TeleHealthUserRepository;
use Comlink\OpenEMR\Modules\TeleHealthModule\Services\TelehealthRegistrationCodeService;
use Comlink\OpenEMR\Modules\TeleHealthModule\Services\TeleHealthRemoteRegistrationService;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use PHPUnit\Framework\TestCase;

class TeleHealthVideoRegistrationControllerTest extends TestCase
{
    /**
     * @var TeleHealthVideoRegistrationController
     */
    private $controller;

    /**
     * @var TelehealthGlobalConfig
     */
    private $telehealthConfig;

    /**
     * @var TelehealthRegistrationCodeService
     */
    private $registrationCodeService;

    protected function setUp(): void
    {
        global $GLOBALS;
        parent::setUp();
        $globalsConfig = new TelehealthGlobalConfig();
        $this->telehealthConfig = $globalsConfig;
        $providerRepo = new TeleHealthProviderRepository(new SystemLogger(), $globalsConfig);
        $userRepo = new TeleHealthUserRepository();
        $this->registrationCodeService = new TelehealthRegistrationCodeService($globalsConfig, $userRepo);
        $remoteRepo = new TeleHealthRemoteRegistrationService($globalsConfig, $this->registrationCodeService);

        $this->controller = new TeleHealthVideoRegistrationController($remoteRepo, $providerRepo);
    }

    public function testAddNewUser()
    {

        $userRequest = $this->getCreateUserRequest();

        $mock = $this->createMock(TeleHealthUserRepository::class);

        $mock->expects($this->once())
            ->method('saveUser')
            ->willReturn(1);

        $mock->expects($this->once())
            ->method('decryptPassword')
            ->willReturn($userRequest->getPassword());

        $this->controller->setTelehealthUserRepository($mock);
        $savedTelehealthUserId = $this->controller->addNewUser($userRequest);
        $this->assertEquals(1, $savedTelehealthUserId, "Request was made and saved user id was returned");
    }

    public function testSuspendUser()
    {
        $controller = $this->controller;
        $userRequest = $this->getCreateUserRequest();

        $mock = $this->createMock(TeleHealthUserRepository::class);
        $mock->method('saveUser')
            ->willReturn(1);
        $mock->method('getUser')
            ->willReturn($this->getMockUser(1, $userRequest->getUsername()));
        $mock->expects($this->once())
            ->method('decryptPassword')
            ->willReturn($userRequest->getPassword());

        $controller->setTelehealthUserRepository($mock);
        $savedTelehealthUserId = $controller->addNewUser($userRequest);
        $this->assertNotFalse($savedTelehealthUserId, "failed to provision new user before update");

        $result = $controller->suspendUser($userRequest->getUsername(), $userRequest->getPassword());
        $this->assertEquals(true, $result, "Request was made and user was suspended");
    }

    public function testDeactivateUser()
    {
        $this->markTestIncomplete("skipping test as we don't have a use for deactivation at this point");
    }

    public function testResumeUser()
    {
        $controller = $this->controller;
        $userRequest = $this->getCreateUserRequest();

        $mock = $this->createMock(TeleHealthUserRepository::class);
        $mock->method('saveUser')
            ->willReturn(1);
        $mock->method('getUser')
            ->willReturn($this->getMockUser(1, $userRequest->getUsername()));
        $mock->expects($this->once())
            ->method('decryptPassword')
            ->willReturn($userRequest->getPassword());

        $controller->setTelehealthUserRepository($mock);
        $savedTelehealthUserId = $controller->addNewUser($userRequest);
        $this->assertNotFalse($savedTelehealthUserId, "failed to provision new user before update");

        $result = $controller->suspendUser($userRequest->getUsername(), $userRequest->getPassword());
        $this->assertEquals(true, $result, "Request was made and user was suspended");

        // now resume the user and make sure that works
        $result = $controller->resumeUser($userRequest->getUsername(), $userRequest->getPassword());
        $this->assertEquals(true, $result, "Request was made and user status was resumed");
    }

    public function testUpdateUser()
    {
        $controller = $this->controller;
        $userRequest = $this->getCreateUserRequest();

        $mock = $this->createMock(TeleHealthUserRepository::class);
        $mock->method('saveUser')
            ->willReturn(1);
        $mock->method('getUser')
            ->willReturn($this->getMockUser(1, $userRequest->getUsername()));
        $mock->expects($this->once())
            ->method('decryptPassword')
            ->willReturn($userRequest->getPassword());

        $controller->setTelehealthUserRepository($mock);
        $savedTelehealthUserId = $controller->addNewUser($userRequest);
        $this->assertNotFalse($savedTelehealthUserId, "failed to provision new user before update");

        // now attempt to update the user
        $userRequest->setLastName("Test 2 first name " . $userRequest->getUsername())
            ->setPassword(sha1($userRequest->getUsername() . " random password"));
        $result = $controller->updateUser($userRequest);
        $this->assertEquals(1, $result, "Request was made and saved user id was returned");
    }

    private function getCreateUserRequest(): UserVideoRegistrationRequest
    {
        $uuid = UuidRegistry::getRegistryForTable("users")->createUuid();

        $userRepository = new TeleHealthUserRepository();
        $password = $userRepository->createUniquePassword();

        $userRequest = new UserVideoRegistrationRequest();
        $userRequest->setUsername(UuidRegistry::uuidToString($uuid))
            ->setPassword($password)
            ->setFirstName("Test First Name " . $userRequest->getUsername())
            ->setLastName("Test Last Name " . $userRequest->getUsername())
            ->setInstituationId($this->telehealthConfig->getInstitutionId())
            ->setInstitutionName($this->telehealthConfig->getInstitutionName())
            ->setDbRecordId(1)
            ->setRegistrationCode($this->registrationCodeService->generateRegistrationCode());
        return $userRequest;
    }

    private function getMockUser($id, $username, $dbRecordId = null): TeleHealthUser
    {
        $user = new TeleHealthUser();
        return $user->setId($id)->setUsername($username)->setDbRecordId($dbRecordId);
    }
}
