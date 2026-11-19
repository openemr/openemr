<?php

/**
 * TrustedUserServiceTest.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
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
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `api_refresh_token` WHERE `client_id` LIKE ?",
            ['%' . self::TRUSTED_CLIENT_TAG . '%'],
            noLog: true
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM `api_token` WHERE `client_id` LIKE ?",
            ['%' . self::TRUSTED_CLIENT_TAG . '%'],
            noLog: true
        );
    }

    /**
     * Verify api authorization with a valid user uuid
     */
    public function testIsTrustedUserWithUserId(): void
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

    public function testSaveTrustedUserThrowsExceptionIfInvalidUserId(): void
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

    public function testDeleteTrustedUserByIdRevokesApiTokens(): void
    {
        // The trusted-user row is the admin-facing handle on a specific
        // OAuth app's hold on a user. Deleting it must also close the
        // api_refresh_token / api_token rows the trusted-user record
        // backs; otherwise an outstanding refresh token keeps minting
        // access tokens after the admin has revoked the app.
        $systemUser = (new UserService())->getSystemUser();
        $this->assertIsArray($systemUser);
        $userUuid = $systemUser['uuid'];
        $this->assertIsString($userUuid);
        $clientId = Uuid::uuid4()->toString() . self::TRUSTED_CLIENT_TAG;
        $code = $clientId;
        $service = new TrustedUserService();
        $id = $service->saveTrustedUser(
            $clientId,
            $userUuid,
            'openid',
            0,
            $code,
            '{"sessionId"=>"value"}',
            'authorization_code'
        );

        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `api_refresh_token` (`user_id`, `client_id`, `token`, `expiry`, `revoked`) "
                . "VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), 0)",
            [$userUuid, $clientId, $clientId . '-refresh']
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `api_token` (`user_id`, `client_id`, `token`, `expiry`, `revoked`) "
                . "VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), 0)",
            [$userUuid, $clientId, $clientId . '-access']
        );

        $service->deleteTrustedUserById($id);

        $trustedUser = $service->getTrustedUserByCode($code);
        $this->assertEmpty($trustedUser, 'Trusted user row must be deleted');

        $refresh = QueryUtils::querySingleRow(
            "SELECT `revoked` FROM `api_refresh_token` WHERE `token` = ?",
            [$clientId . '-refresh']
        );
        $access = QueryUtils::querySingleRow(
            "SELECT `revoked` FROM `api_token` WHERE `token` = ?",
            [$clientId . '-access']
        );
        $this->assertIsArray($refresh);
        $this->assertIsArray($access);
        $this->assertEquals(
            1,
            $refresh['revoked'] ?? 0,
            'api_refresh_token row must be revoked when the trusted-user row is deleted'
        );
        $this->assertEquals(
            1,
            $access['revoked'] ?? 0,
            'api_token row must be revoked when the trusted-user row is deleted'
        );
    }
}
