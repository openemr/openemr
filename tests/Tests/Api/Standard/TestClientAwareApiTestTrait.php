<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Api\Standard;

use OpenEMR\Tests\Api\ApiTestClient;
use PHPUnit\Framework\TestCase;

/**
 * @mixin TestCase
 */
trait TestClientAwareApiTestTrait
{
    protected ApiTestClient $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    protected function tearDown(): void
    {
        // Remove test client created by ApiTestClient
        // $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }
}
