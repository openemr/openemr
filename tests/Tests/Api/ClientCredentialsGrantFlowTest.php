<?php

/*
 * ClientCredentialsGrantFlowTest.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Api;

use JsonException;
use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\Kernel;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\RestControllers\AuthorizationController;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

class ClientCredentialsGrantFlowTest extends TestCase
{
    /**
     * @return void
     * @throws JsonException
     * @throws OAuthServerException
     * @throws Exception
     */
    public function testClientCredentialsGrantFlow(): void
    {
        $jwks = json_decode(file_get_contents(__DIR__ . '/../data/Unit/Common/Auth/Grant/jwk-public-valid.json'), true, 512, JSON_THROW_ON_ERROR);
        // Create a client that supports client credentials grant
        $request = HttpRestRequest::create("/oauth2/register", 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
                "application_type" => "private",
               "redirect_uris" =>
                 ["http=>//localhost:4567/inferno/oauth2/static/redirect"],
               "post_logout_redirect_uris" =>
                 ["http=>//localhost:4567/inferno/oauth2/static/logout"],
               "client_name" => "OpenEMR System Credentials Test Client - disable in production",
                "initiate_login_uri" => "https=>/localhost=>4567/inferno/oauth2/static/launch",
               "token_endpoint_auth_method" => "client_secret_post",
               "contacts" => ["test@open-emr.org"],
               "scope" => 'system/*.$export system/Patient.$export system/*.$bulkdata-status system/Group.$export system/Medication.read system/AllergyIntolerance.read system/CarePlan.read system/CareTeam.read system/Condition.read system/Device.read system/DiagnosticReport.read system/DocumentReference.read system/Binary.read system/Encounter.read system/Goal.read system/Immunization.read system/Location.read system/MedicationRequest.read system/Observation.read system/Organization.read system/Practitioner.read system/Procedure.read system/Provenance.read system/Group.read',
               "jwks" => ["keys" => [["kty" => "RSA","e" => "AQAB","kid" => "5c17409c-87f0-4713-814f-c864bfe876bc","n" => "4dKFtTbLuj_ohXaxa5yOkQK6uarDBww-7QtQaA8zDt2IjfpcEW5hRbKMswU5cXmMSLc33c_jemJQoXxWHriW4xO0FREqvA0u4PpInJtte7uwqDzml0sDUS6LLqWdOANapEnvovH7aAUb-v_GTU6eK3pcJsquQTnTLOeXLSkk9ukGJDQ5rcbkguOQXZAngKhalWGzHx_rYoQv2kH1F9rshgfjpiPFmjs9EyRtuo1yc8RQvioAKugc72MbPPlGN6saesDh3tnvyL5sbMs1cLjSldehZ4y0KHVSubuvipcM4RctbUIiZQQSwVIV3hCLKhVSKX_owz_46vpvk-7VyKwDjH5D6kdM86u_g6SkP7cF272LAsNUJak98qLWaogWGm-UWzaHvZpuS2w5sMMOE-8tEBZc-ZIjOgDWWy5AYYl8KCpVeOwuQlZ59X3rd67Pinc98LmDd4jJTGYWNsygdti76MEWlvA9tP8E8dOcr_SSn9TN832NopbjbG9H6dXid3e7XNobLAGRXM9n0JD0MPOH3ltMBQDi6JDzkFoYmONtNI2-e6_R_uogoCDZWUqZF72eGknoawPGwGLEqRW1sIoI4ziVT9hsZxaoiMVjIAYNOxBmooTBgp4NgbbQUXtqZxcEnzgZO3WVBg0P_ldkeu-kS4xV0mg8x25TQhws1EytVa0"]]]
        ]));
        $storage = new MockFileSessionStorage();
        $session = new Session($storage);
        $session->set('site_id', 'default');
        $globalsBag = new OEGlobalsBag([
            'webroot' => ''
        ]);
        $kernel = $this->createMock(OEHttpKernel::class);
        $kernel->method('getGlobalsBag')
            ->willReturn($globalsBag);
        $authorizationController = new AuthorizationController($session, $kernel);
        $response = $authorizationController->clientRegistration($request);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $body = $response->getBody()->getContents();
        $this->assertNotEmpty($body, "Response body should not be empty");
        $client = json_decode($body, true);
        $this->assertNotNull($client, "Response body should be valid JSON");
        $this->assertArrayHasKey('client_id', $client);
        $this->assertArrayHasKey('client_secret', $client);

        $clientService = new ClientRepository();
        $client = $clientService->getClientEntity($client['client_id']);
        $this->assertNotFalse($client, "Client should be retrievable from repository");
    }

    /**
     * SSRF regression at the DCR boundary: a client registration that submits
     * a `jwks_uri` resolving to the AWS/GCP cloud-metadata IP must be rejected
     * before storage. The strict policy is forced on for this test by setting
     * OPENEMR__ENVIRONMENT to a non-dev value, mimicking production wiring.
     *
     * Counterpart to {@see testClientCredentialsGrantFlow}: that test posts
     * inline `jwks` (no fetch needed); this one targets the URL path that
     * an attacker would try to exploit.
     *
     * @throws JsonException
     * @throws Exception
     */
    public function testClientRegistrationRejectsUnsafeJwksUri(): void
    {
        $previousEnv = $_ENV['OPENEMR__ENVIRONMENT'] ?? null;
        $_ENV['OPENEMR__ENVIRONMENT'] = 'production';

        try {
            $body = json_encode([
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example.com/cb'],
                'client_name' => 'SSRF probe — should be rejected',
                'token_endpoint_auth_method' => 'private_key_jwt',
                'contacts' => ['security@open-emr.org'],
                'scope' => 'system/Patient.read',
                // AWS/GCP/Azure cloud-metadata IP. The canonical SSRF target.
                'jwks_uri' => 'https://169.254.169.254/latest/meta-data/jwks',
            ], JSON_THROW_ON_ERROR);
            $request = HttpRestRequest::create("/oauth2/register", 'POST', [], [], [], [
                'CONTENT_TYPE' => 'application/json',
            ], $body);

            $storage = new MockFileSessionStorage();
            $session = new Session($storage);
            $session->set('site_id', 'default');
            $globalsBag = new OEGlobalsBag(['webroot' => '']);
            // Wire a real Kernel so AuthorizationController->buildJwksUrlValidator()
            // can read isDev() — production mode (strict policy) is what we want here.
            // Once a kernel is in the bag, OEGlobalsBag delegates path getters to it,
            // so we must give the kernel a webRoot to avoid "Kernel was constructed
            // without a webRoot" downstream.
            $globalsBag->set('kernel', new Kernel(projectDir: '', webRoot: ''));
            $kernel = $this->createMock(OEHttpKernel::class);
            $kernel->method('getGlobalsBag')->willReturn($globalsBag);

            $controller = new AuthorizationController($session, $kernel);
            $response = $controller->clientRegistration($request);

            // The controller serializes OAuthServerExceptions to a JSON response
            // with a 4xx status + invalid_client_metadata body, rather than
            // throwing. Both indicators are checked so a future refactor that
            // changes one but not the other still trips this assertion.
            $this->assertSame(400, $response->getStatusCode(), 'Expected 400 from invalid_client_metadata');
            // The body stream has just been written to by generateHttpResponse(),
            // so the cursor is at the end. Casting to string materializes the
            // full payload regardless of position.
            $responseBody = (string) $response->getBody();
            $decoded = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
            $this->assertIsArray($decoded, 'Error response body must be a JSON object');
            $this->assertSame('invalid_client_metadata', $decoded['error'] ?? null);
            $description = $decoded['error_description'] ?? $decoded['message'] ?? '';
            $this->assertIsString($description, 'Error response must carry a string description');
            $this->assertStringContainsString('jwks_uri', $description);
        } finally {
            if ($previousEnv === null) {
                unset($_ENV['OPENEMR__ENVIRONMENT']);
            } else {
                $_ENV['OPENEMR__ENVIRONMENT'] = $previousEnv;
            }
        }
    }
}
