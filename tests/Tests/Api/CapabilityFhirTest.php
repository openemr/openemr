<?php

namespace OpenEMR\Tests\Api;

use OpenEMR\FHIR\SMART\Capability;
use OpenEMR\RestControllers\AuthorizationController;
use OpenEMR\RestControllers\FHIR\FhirMetaDataRestController;
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
    const CAPABILITY_OAUTH_PREFIX = "/oauth2/default";
    const CAPABILITY_FHIR_ENDPOINT_INVALID_SITE = "/apis/baddefault/fhir/metadata";

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
        $this->baseUrl = $baseUrl;
        $this->oauthBaseUrl = $baseUrl . self::CAPABILITY_OAUTH_PREFIX;
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
        $actualResponse = $this->testClient->get(self::CAPABILITY_FHIR_ENDPOINT . "ss");
        $this->assertEquals(401, $actualResponse->getStatusCode());
    }

    /**
     * @covers ::get with an invalid site
     */
    public function testInvalidSiteGet()
    {
        $actualResponse = $this->testClient->get(self::CAPABILITY_FHIR_ENDPOINT_INVALID_SITE);
        $this->assertEquals(400, $actualResponse->getStatusCode());
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        $actualResponse = $this->testClient->get(self::CAPABILITY_FHIR_ENDPOINT);
        $this->assertEquals(200, $actualResponse->getStatusCode());
        $body = $actualResponse->getBody();
        $this->assertNotNull($body); // make sure we have a body here


        $statement = json_decode($body, true);
        $this->assertCapabilityHasSMARTRequirements($statement);
    }

    private function assertCapabilityHasSMARTRequirements($statement)
    {

        $this->assertArrayHasKey('rest', $statement, "Rest capability must be defined");
        $restDef = $statement['rest'][0];
        $this->assertArrayHasKey('security', $restDef, "Rest security object defined");
        $this->assertArrayHasKey('service', $restDef['security'], "Rest security.service object defined and is not empty");
        $securityService = $restDef['security']['service'][0];

        $this->assertArrayHasKey('coding', $securityService, "Rest security.service[].coding object defined");
        $this->assertArrayHasKey('text', $securityService, "Rest security.service[].text object defined");
        $coding = $securityService['coding'][0];
        $this->assertEquals("http://hl7.org/fhir/restful-security-service", $coding['system'], "Rest security.service[].coding[].system set");
        $this->assertEquals("SMART-on-FHIR", $coding['code'], "Rest security.service[].coding[].code set");

        $this->assertArrayHasKey('extension', $restDef['security'], "Rest security.extension object defined");

        $oauthExtension = $this->getExtension($restDef['security'], "http://fhir-registry.smarthealthit.org/StructureDefinition/oauth-uris");
        $this->assertNotNull($oauthExtension, "Oauth extension should be defined in capability statement");
        $this->assertArrayHasKey('extension', $oauthExtension, "Oauth extension should have embedded extensions");
        $this->assertEquals(4, count($oauthExtension['extension']), "Extension should have token, authorize, manage extensions");

        $tokenUri = $this->oauthBaseUrl . AuthorizationController::getTokenPath();
        $authorizeUri = $this->oauthBaseUrl . AuthorizationController::getAuthorizePath();
        $manageUri = $this->oauthBaseUrl . AuthorizationController::getManagePath();
        $this->assertEquals("token", $oauthExtension['extension'][0]['url'], "OAUTH Extension[0].url should be token");
//        $this->assertEquals($tokenUri, $oauthExtension['extension'][0]['valueUri'], "OAUTH Extension[0].valueUri does not match server token uri");

        $this->assertEquals("authorize", $oauthExtension['extension'][1]['url'], "OAUTH Extension[1].url should be authorize");
//        $this->assertEquals($authorizeUri, $oauthExtension['extension'][1]['valueUri'], "OAUTH Extension[1].valueUri does not match server url");

        // found out manage is optional, if we add it back in we can uncomment this.
//        $this->assertEquals("manage", $oauthExtension['extension'][2]['url'], "OAUTH Extension[2].url should be authorize");
//        $this->assertEquals($manageUri, $oauthExtension['extension'][2]['valueUri'], "OAUTH Extension[2].valueUri does not match server url");


        $smartExtensions = $this->getExtensionList($restDef['security'], "http://fhir-registry.smarthealthit.org/StructureDefinition/capabilities");
        $enabledCapabilities = [];
        foreach ($smartExtensions as $index => $extension) {
            $enabledCapabilities[] = $extension['valueCode'];
        }

        // the capabilities the server currently has
        $expectedCapabilities = Capability::FHIR_SUPPORTED_CAPABILITIES;
        $missing_capabilities = array_diff($expectedCapabilities, $enabledCapabilities);
        $this->assertEquals([], $missing_capabilities, "Capabilities statement is missing expected SMART extensions of " . implode(",", $missing_capabilities));
    }

    public function getExtension($capabilityStatementRestDefinition, $extensionUri)
    {
        $result = null;
        $list = $this->getExtensionList($capabilityStatementRestDefinition, $extensionUri);
        if (!empty($list)) {
            $result = array_pop($list);
        }
        return $result;
    }

    public function getExtensionList($capabilityStatementRestDefinition, $extensionUri)
    {
        $list = [];
        if (!empty($capabilityStatementRestDefinition['extension'])) {
            foreach ($capabilityStatementRestDefinition['extension'] as $index => $extension) {
                if ($extension['url'] == $extensionUri) {
                    $list[] = $extension;
                }
            }
        }
        return $list;
    }
}
