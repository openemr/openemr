<?php

/**
 * Discovers the provider and exchanges an authorization code for tokens.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Tamir Suliman
 * @copyright Copyright (c) 2026 Tamir Suliman
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Auth\OidcRp;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;

final class OidcRpClient
{
    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function discover(OidcRpSettings $settings): OidcProviderMetadata
    {
        OidcIssuerUrl::assertSafe($settings->issuer, $settings->allowHttp);
        $discoveryUrl = $settings->discoveryUrl();
        OidcIssuerUrl::assertSafe($discoveryUrl, $settings->allowHttp, OidcIssuerUrl::hostOf($settings->issuer));

        $document = $this->getJson($discoveryUrl);
        $metadata = OidcProviderMetadata::fromDiscoveryDocument($document, $settings->issuer);
        $issuerHost = OidcIssuerUrl::hostOf($settings->issuer);
        OidcIssuerUrl::assertSafe($metadata->authorizationEndpoint, $settings->allowHttp, $issuerHost);
        OidcIssuerUrl::assertSafe($metadata->tokenEndpoint, $settings->allowHttp, $issuerHost);
        OidcIssuerUrl::assertSafe($metadata->jwksUri, $settings->allowHttp, $issuerHost);
        if ($metadata->endSessionEndpoint !== '') {
            OidcIssuerUrl::assertSafe($metadata->endSessionEndpoint, $settings->allowHttp, $issuerHost);
        }

        return $metadata;
    }

    /**
     * @return array{authorization_url: string, state: string, nonce: string, code_verifier: string}
     */
    public function buildAuthorizationRequest(OidcRpSettings $settings, OidcProviderMetadata $metadata, string $redirectUri): array
    {
        $pkce = OidcPkce::create();
        $state = bin2hex(random_bytes(16));
        $nonce = bin2hex(random_bytes(16));
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $settings->clientId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $settings->scopes),
            'state' => $state,
            'nonce' => $nonce,
            'code_challenge' => $pkce->challenge,
            'code_challenge_method' => 'S256',
        ], '', '&', PHP_QUERY_RFC3986);

        return [
            'authorization_url' => $metadata->authorizationEndpoint . '?' . $query,
            'state' => $state,
            'nonce' => $nonce,
            'code_verifier' => $pkce->verifier,
        ];
    }

    /**
     * @return array{id_token: string, access_token: string}
     */
    public function exchangeAuthorizationCode(
        OidcRpSettings $settings,
        OidcProviderMetadata $metadata,
        string $code,
        string $redirectUri,
        string $codeVerifier,
    ): array {
        $body = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => $settings->clientId,
            'code_verifier' => $codeVerifier,
        ];
        if ($settings->clientSecret !== '') {
            $body['client_secret'] = $settings->clientSecret;
        }

        $document = $this->postForm($metadata->tokenEndpoint, $body);
        $idToken = $document['id_token'] ?? null;
        if (!is_string($idToken) || $idToken === '') {
            throw new OidcRpException('Token response did not include an ID token');
        }

        $accessToken = $document['access_token'] ?? '';
        return [
            'id_token' => $idToken,
            'access_token' => is_string($accessToken) ? $accessToken : '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getJson(string $url): array
    {
        $request = $this->requestFactory->createRequest('GET', $url)
            ->withHeader('Accept', 'application/json');
        return $this->decodeJsonResponse($url, $request);
    }

    /**
     * @param array<string, string> $fields
     * @return array<string, mixed>
     */
    private function postForm(string $url, array $fields): array
    {
        $request = $this->requestFactory->createRequest('POST', $url)
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($this->streamFactory->createStream(http_build_query($fields, '', '&', PHP_QUERY_RFC3986)));
        return $this->decodeJsonResponse($url, $request);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonResponse(string $url, \Psr\Http\Message\RequestInterface $request): array
    {
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $exception) {
            $this->logger->error('OIDC HTTP request failed', ['url' => $url, 'exception' => $exception]);
            throw new OidcRpException('Unable to contact the identity provider', 0, $exception);
        }

        $status = $response->getStatusCode();
        $payload = $response->getBody()->getContents();
        if ($status < 200 || $status >= 300) {
            $this->logger->error('OIDC HTTP request returned an error status', [
                'url' => $url,
                'status' => $status,
            ]);
            throw new OidcRpException('Identity provider returned an error');
        }

        try {
            $decoded = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new OidcRpException('Identity provider returned invalid JSON', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new OidcRpException('Identity provider returned invalid JSON');
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}
