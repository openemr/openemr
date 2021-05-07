<?php

/**
 * TrustedUserServiceTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use OpenEMR\Services\TrustedUserService;
use OpenEMR\Services\UserService;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class TrustedUserServiceTest extends TestCase
{
    private $clientId;
    const TRUSTED_CLIENT_TAG = '-trusted-user-test';

    public function setUp(): void
    {
    }

    public function tearDown(): void
    {
        // clean up any database records we made
        sqlQueryNoLog("DELETE FROM `oauth_trusted_user` WHERE `code` LIKE '%"
            . self::TRUSTED_CLIENT_TAG . "%'");
    }

    /**
     * Verify api authorization with a valid user uuid
     */
    public function testIsTrustedUserWithUserId()
    {
        $userUuid = (new UserService())->getSystemUser()['uuid'];
        $clientId = Uuid::uuid4()->toString();
        $service = new TrustedUserService();
        $code = $clientId . self::TRUSTED_CLIENT_TAG;
        $id = $service->saveTrustedUser(
            $clientId,
            $userUuid,
            'openid',
            0,
            $code,
            '{"sessionId"=>"value"}',
            'client_credentials'
        );


        $trustedUser = $service->getTrustedUserByCode($code);
        $this->assertNotEmpty($trustedUser, "Trusted user should have saved with valid user uuid");

        // now check to make sure our user is trusted
        $isTrusted = $service->isTrustedUser($clientId, $userUuid);

        $this->assertEquals(true, $isTrusted, "Client with valid user uuid should be trusted");
    }

    public function testSaveTrustedUserThrowsExceptionIfInvalidUserId()
    {
        $clientId = Uuid::uuid4()->toString();
        $service = new TrustedUserService();
        $code = $clientId . self::TRUSTED_CLIENT_TAG;
        $this->expectException(\InvalidArgumentException::class);
        $id = $service->saveTrustedUser(
            $clientId,
            null,
            'openid',
            0,
            $code,
            '{"sessionId"=>"value"}',
            'authorization_grant'
        );
    }
}
