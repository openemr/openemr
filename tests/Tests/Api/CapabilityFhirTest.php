<?php

namespace OpenEMR\Tests\Api;

use PHPUnit\Framework\TestCase;
use OpenEMR\Tests\Api\ApiTestClient;

/**
 * Capability FHIR Endpoint Test Cases.
 * @coversDefaultClass OpenEMR\Tests\Api\ApiTestClient
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */
class CapabilityFhirTest extends TestCase
{
    const CAPABILITY_FHIR_ENDPOINT = "/apis/default/fhir/metadata";
    private $testClient;

    protected function setUp(): void
    {
        $baseUrl = getenv("OPENEMR_BASE_URL_API", true) ?: "https://localhost";
        $this->testClient = new ApiTestClient($baseUrl, false);
    }

    /**
     * @covers ::get with an invalid path
     */
    public function testInvalidGet()
    {
        $actualResponse = $this->testClient->get(self::CAPABILITY_FHIR_ENDPOINT . "ss");
        $this->assertEquals(401, $actualResponse->getStatusCode());
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        $actualResponse = $this->testClient->get(self::CAPABILITY_FHIR_ENDPOINT);
        $this->assertEquals(200, $actualResponse->getStatusCode());
    }
}
