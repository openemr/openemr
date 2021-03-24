<?php

namespace OpenEMR\Tests\Api;

use OpenEMR\RestControllers\AuthorizationController;
use OpenEMR\RestControllers\FHIR\FhirMetaDataRestController;
use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Api\ApiTestClient;

/**
 * Capability FHIR Endpoint Test Cases.
 * @coversDefaultClass OpenEMR\Tests\Api\ApiTestClient
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class SmartConfigurationTest extends TestCase
{
    const SMART_CONFIG_ENDPOINT = "/apis/default/fhir/.well-known/smart-configuration";

    /**
     * @var ApiTestClient
     */
    private $testClient;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * Base url endpoint for oauth2 capability uris
     * @var string
     */
    private $oauthBaseUrl;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
    }

    public function tearDown(): void
    {
        $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    /**
     * @covers ::get with an invalid path
     */
    public function testInvalidPathGet()
    {
        $actualResponse = $this->testClient->get(self::SMART_CONFIG_ENDPOINT . "ss");
        $this->assertEquals(401, $actualResponse->getStatusCode());
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        $actualResponse = $this->testClient->get(self::SMART_CONFIG_ENDPOINT);
        $this->assertEquals(200, $actualResponse->getStatusCode());
    }
}
