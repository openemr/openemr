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
     * We had api authorization issues where null user id's were not returning as trusted user's
     * so this test makes sure that a client with a null
     */
    public function testIsTrustedUserWithNullUserId()
    {
        $clientId = Uuid::uuid4()->toString();
        $service = new TrustedUserService();
        $code = $clientId . self::TRUSTED_CLIENT_TAG;
        $id = $service->saveTrustedUser(
            $clientId,
            null,
            'openid',
            0,
            $code,
            '{"sessionId"=>"value"}',
            'client_credentials'
        );


        $trustedUser = $service->getTrustedUserByCode($code);
        $this->assertNotEmpty($trustedUser, "Trusted user should have saved with null user id");

        // now check to make sure our user is trusted
        $isTrusted = $service->isTrustedUser($clientId, null);

        $this->assertEquals(true, $isTrusted, "Client with null user id should be trusted");
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
