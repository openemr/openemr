<?php

/**
 * Tests for ApiLogRedactor
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Logging\Audit;

use OpenEMR\Common\Logging\Audit\ApiLogRedactor;
use PHPUnit\Framework\TestCase;

final class ApiLogRedactorTest extends TestCase
{
    private ApiLogRedactor $redactor;

    protected function setUp(): void
    {
        $this->redactor = new ApiLogRedactor();
    }

    public function testTokenRequestFormEncodedPasswordRedacted(): void
    {
        $body = 'grant_type=password&username=admin&password=hunter2&scope=user%2FPatient.read';
        $redacted = $this->redactor->redactRequest('/oauth2/default/token', $body);
        parse_str($redacted, $parsed);
        self::assertSame(ApiLogRedactor::SENTINEL, $parsed['password'] ?? null);
        self::assertSame('admin', $parsed['username']);
        self::assertSame('password', $parsed['grant_type']);
        self::assertSame('user/Patient.read', $parsed['scope']);
    }

    public function testTokenRequestFormEncodedClientSecretAndCodeVerifierRedacted(): void
    {
        $body = 'grant_type=authorization_code&code=abc&client_id=cid&client_secret=supersecret&code_verifier=xyz';
        $redacted = $this->redactor->redactRequest('/oauth2/default/token', $body);
        parse_str($redacted, $parsed);
        self::assertSame(ApiLogRedactor::SENTINEL, $parsed['client_secret']);
        self::assertSame(ApiLogRedactor::SENTINEL, $parsed['code_verifier']);
        self::assertSame('cid', $parsed['client_id']);
        self::assertSame('abc', $parsed['code']);
    }

    public function testTokenRequestJsonBodyAlsoRedacted(): void
    {
        $body = json_encode(['grant_type' => 'password', 'username' => 'admin', 'password' => 'hunter2']);
        self::assertIsString($body);
        $redacted = $this->redactor->redactRequest('/oauth2/default/token', $body);
        $decoded = json_decode($redacted, true);
        self::assertIsArray($decoded);
        self::assertSame(ApiLogRedactor::SENTINEL, $decoded['password']);
        self::assertSame('admin', $decoded['username']);
    }

    public function testTokenResponseRedactsBearerFields(): void
    {
        $body = json_encode([
            'access_token' => 'eyJhbGciOi...',
            'refresh_token' => 'rt-abc',
            'id_token' => 'id-abc',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scope' => 'user/Patient.read',
        ]);
        self::assertIsString($body);
        $redacted = $this->redactor->redactResponse('/oauth2/default/token', $body);
        $decoded = json_decode($redacted, true);
        self::assertIsArray($decoded);
        self::assertSame(ApiLogRedactor::SENTINEL, $decoded['access_token']);
        self::assertSame(ApiLogRedactor::SENTINEL, $decoded['refresh_token']);
        self::assertSame(ApiLogRedactor::SENTINEL, $decoded['id_token']);
        self::assertSame('Bearer', $decoded['token_type']);
        self::assertSame(3600, $decoded['expires_in']);
        self::assertSame('user/Patient.read', $decoded['scope']);
    }

    public function testRegistrationResponseRedactsClientSecretAndRegistrationAccessToken(): void
    {
        $body = json_encode([
            'client_id' => 'cid',
            'client_secret' => 'supersecret',
            'registration_access_token' => 'ratoken',
            'client_name' => 'test app',
            'redirect_uris' => ['https://localhost/cb'],
        ]);
        self::assertIsString($body);
        $redacted = $this->redactor->redactResponse('/oauth2/default/registration', $body);
        $decoded = json_decode($redacted, true);
        self::assertIsArray($decoded);
        self::assertSame(ApiLogRedactor::SENTINEL, $decoded['client_secret']);
        self::assertSame(ApiLogRedactor::SENTINEL, $decoded['registration_access_token']);
        self::assertSame('cid', $decoded['client_id']);
        self::assertSame('test app', $decoded['client_name']);
        self::assertSame(['https://localhost/cb'], $decoded['redirect_uris']);
    }

    public function testRegistrationRequestIsNotRedacted(): void
    {
        $body = json_encode([
            'client_name' => 'test app',
            'redirect_uris' => ['https://localhost/cb'],
            'scope' => 'user/Patient.read',
        ]);
        self::assertIsString($body);
        $redacted = $this->redactor->redactRequest('/oauth2/default/registration', $body);
        self::assertSame($body, $redacted);
    }

    public function testFhirUrlsPassThroughUnchanged(): void
    {
        $body = json_encode(['resourceType' => 'Patient', 'name' => [['family' => 'Smith']]]);
        self::assertIsString($body);
        self::assertSame($body, $this->redactor->redactRequest('/apis/default/fhir/Patient', $body));
        self::assertSame($body, $this->redactor->redactResponse('/apis/default/fhir/Patient', $body));
    }

    public function testQueryStringDoesNotDefeatMatch(): void
    {
        $body = json_encode(['access_token' => 't']);
        self::assertIsString($body);
        $redacted = $this->redactor->redactResponse('/oauth2/default/token?x=1', $body);
        $decoded = json_decode($redacted, true);
        self::assertIsArray($decoded);
        self::assertSame(ApiLogRedactor::SENTINEL, $decoded['access_token']);
    }

    public function testEmptyBodyReturnsEmpty(): void
    {
        self::assertSame('', $this->redactor->redactRequest('/oauth2/default/token', ''));
        self::assertSame('', $this->redactor->redactResponse('/oauth2/default/registration', ''));
    }

    public function testUnparseableBodyPassesThrough(): void
    {
        $body = 'this is neither JSON nor x-www-form-urlencoded shaped data';
        self::assertSame($body, $this->redactor->redactRequest('/oauth2/default/token', $body));
    }
}
