<?php

/**
 * Password-grant mint helpers for api-tier tests.
 *
 * Composable, scope-parameterized helpers that mint bearer tokens via
 * the OAuth2 password grant. Supports both user_role=users (admin)
 * and user_role=patient (portal) flows.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Support\OAuth2;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PHPUnit\Framework\Assert;

class PasswordGrant
{
    /**
     * Mint a users-role (admin) bearer via password grant. Uses the
     * seeded admin/pass credentials.
     *
     * @return array{access_token: string, scopes_granted: list<string>, response_body: array<mixed>}
     */
    public static function mintUserRoleToken(
        string $baseUrl,
        string $clientId,
        string $clientSecret,
        string $scope,
    ): array {
        return self::post($baseUrl, [
            'grant_type' => 'password',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => $scope,
            'user_role' => 'users',
            'username' => 'admin',
            'password' => 'pass',
        ]);
    }

    /**
     * Mint a patient-role bearer via password grant against a portal
     * patient fixture. The fixture array must carry
     * `portal_login_username`, `plain_password`, and `email` — matches
     * PortalPatientFixtureManager::installPortalPatient's return shape.
     *
     * @param array<string, mixed> $fixture
     *
     * @return array{access_token: string, scopes_granted: list<string>, response_body: array<mixed>}
     */
    public static function mintPatientRoleToken(
        string $baseUrl,
        string $clientId,
        string $clientSecret,
        string $scope,
        array $fixture,
    ): array {
        Assert::assertIsString($fixture['portal_login_username'] ?? null);
        Assert::assertIsString($fixture['plain_password'] ?? null);
        Assert::assertIsString($fixture['email'] ?? null);
        return self::post($baseUrl, [
            'grant_type' => 'password',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => $scope,
            'user_role' => 'patient',
            'username' => $fixture['portal_login_username'],
            'password' => $fixture['plain_password'],
            'email' => $fixture['email'],
        ]);
    }

    /**
     * @param array<string, string> $form
     *
     * @return array{access_token: string, scopes_granted: list<string>, response_body: array<mixed>}
     */
    private static function post(string $baseUrl, array $form): array
    {
        $resp = self::buildClient()->post($baseUrl . '/oauth2/default/token', [
            'form_params' => $form,
        ]);
        $bodyRaw = (string) $resp->getBody();
        Assert::assertSame(
            200,
            $resp->getStatusCode(),
            'Password-grant mint must succeed. Body: ' . $bodyRaw
        );
        $body = json_decode($bodyRaw, true);
        Assert::assertIsArray($body);
        $token = $body['access_token'] ?? null;
        Assert::assertIsString($token);
        Assert::assertNotSame('', $token);
        $scopeValue = $body['scope'] ?? '';
        /** @var list<string> $granted */
        $granted = is_string($scopeValue) && $scopeValue !== '' ? explode(' ', $scopeValue) : [];
        return [
            'access_token' => $token,
            'scopes_granted' => $granted,
            'response_body' => $body,
        ];
    }

    private static function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => new CookieJar(),
            'timeout' => 15,
        ]);
    }
}
