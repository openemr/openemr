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
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\TrustedUserService;
use OpenEMR\Services\UserService;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class TrustedUserServiceTest extends TestCase
{
    private $clientId;
    const TRUSTED_CLIENT_TAG = '-trusted-user-test';

    /** @var list<string> uuid strings of users this test created */
    private array $createdUserUuids = [];

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
        foreach ($this->createdUserUuids as $uuid) {
            $uuidBytes = UuidRegistry::uuidToBytes($uuid);
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM `users` WHERE `uuid` = ?",
                [$uuidBytes],
                noLog: true
            );
        }
        $this->createdUserUuids = [];
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

    /**
     * Deleting a trusted-user row is a per-OAuth-app offboarding. It must
     * close that one app's hold on the user: the matching
     * api_refresh_token / api_token rows get revoked alongside the row
     * delete so the offboarded app cannot keep minting access tokens
     * against a token it already held. A user who authorized several
     * apps keeps their tokens for the apps that were not offboarded.
     */
    public function testDeleteTrustedUserByIdRevokesApiTokensForThatClientOnly(): void
    {
        // Use a dedicated test user, not the shared system user: the
        // revoke helpers would otherwise touch rows this test did not
        // create, and tearDown would not know to restore them.
        $userUuid = $this->createDedicatedTestUser();

        $service = new TrustedUserService();

        $firstClient = Uuid::uuid4()->toString() . self::TRUSTED_CLIENT_TAG;
        $secondClient = Uuid::uuid4()->toString() . self::TRUSTED_CLIENT_TAG;
        $idA = $service->saveTrustedUser(
            $firstClient,
            $userUuid,
            'openid',
            0,
            $firstClient,
            '{"sessionId"=>"value"}',
            'authorization_code'
        );
        $service->saveTrustedUser(
            $secondClient,
            $userUuid,
            'openid',
            0,
            $secondClient,
            '{"sessionId"=>"value"}',
            'authorization_code'
        );

        $this->insertActiveTokensFor($userUuid, $firstClient);
        $this->insertActiveTokensFor($userUuid, $secondClient);

        $service->deleteTrustedUserById($idA);

        $this->assertEmpty(
            $service->getTrustedUserByCode($firstClient),
            'Deleted trusted-user row must be gone'
        );
        $this->assertNotEmpty(
            $service->getTrustedUserByCode($secondClient),
            'The other client\'s trusted-user row must be untouched'
        );

        $this->assertSame(
            1,
            $this->readTokenRevoked('api_refresh_token', $firstClient . '-refresh'),
            'api_refresh_token row for the deleted client must be revoked'
        );
        $this->assertSame(
            1,
            $this->readTokenRevoked('api_token', $firstClient . '-access'),
            'api_token row for the deleted client must be revoked'
        );
        $this->assertSame(
            0,
            $this->readTokenRevoked('api_refresh_token', $secondClient . '-refresh'),
            'The other client\'s api_refresh_token must stay active'
        );
        $this->assertSame(
            0,
            $this->readTokenRevoked('api_token', $secondClient . '-access'),
            'The other client\'s api_token must stay active'
        );
    }

    /**
     * Create a dedicated users row for this test so revocation helpers
     * cannot touch tokens that belong to the real admin / system user.
     * Registered for cleanup in tearDown.
     */
    private function createDedicatedTestUser(): string
    {
        $uuidStr = Uuid::uuid4()->toString();
        $uuidBytes = UuidRegistry::uuidToBytes($uuidStr);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `users` (`uuid`, `username`, `fname`, `lname`, `active`, `authorized`) "
                . "VALUES (?, ?, 'TrustedUserServiceTest', 'Fixture', 1, 0)",
            [$uuidBytes, 'trusted-user-test-' . $uuidStr]
        );
        $this->createdUserUuids[] = $uuidStr;
        return $uuidStr;
    }

    private function insertActiveTokensFor(string $userUuidStr, string $clientId): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `api_refresh_token` (`user_id`, `client_id`, `token`, `expiry`, `revoked`) "
                . "VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), 0)",
            [$userUuidStr, $clientId, $clientId . '-refresh']
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `api_token` (`user_id`, `client_id`, `token`, `expiry`, `revoked`) "
                . "VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), 0)",
            [$userUuidStr, $clientId, $clientId . '-access']
        );
    }

    private function readTokenRevoked(string $table, string $token): int
    {
        $row = QueryUtils::querySingleRow(
            "SELECT `revoked` FROM `" . $table . "` WHERE `token` = ?",
            [$token]
        );
        if (!is_array($row) || !isset($row['revoked']) || !is_numeric($row['revoked'])) {
            return -1;
        }
        return (int) $row['revoked'];
    }
}
