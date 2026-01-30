<?php

namespace OpenEMR\RestControllers\Authorization;

use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClaimRepository;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEGlobalsBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use Symfony\Component\HttpFoundation\Response;

class OAuth2DiscoveryController
{
    private ScopeRepository $scopeRepository;
    private ClaimRepository $claimRepository;

    public function __construct(ClaimRepository $claimRepository, ScopeRepository $scopeRepository, private readonly OEGlobalsBag $globalsBag, private readonly string $baseUrl)
    {
        $this->setClaimRepository($claimRepository);
        $this->setScopeRepository($scopeRepository);
    }

    public function setClaimRepository(ClaimRepository $claimRepository): void
    {
        $this->claimRepository = $claimRepository;
    }

    public function getClaimsRepository(): ClaimRepository
    {
        return $this->claimRepository;
    }

    public function setScopeRepository(ScopeRepository $scopeRepository): void
    {
        $this->scopeRepository = $scopeRepository;
    }

    public function getScopeRepository(): ScopeRepository
    {
        return $this->scopeRepository;
    }

    public function getDiscoveryResponse(HttpRestRequest $request): Response
    {
        $passwordGrantString = '';
        if (!empty($this->globalsBag->getInt('oauth_password_grant') > 0)) {
            $passwordGrantString = '"password",';
        }
// PHP is a fickle beast!
        $scopeRepository = $this->getScopeRepository();
        $claims_array = $this->getClaimsRepository()->getSupportedClaims();
        $claims = json_encode($claims_array, JSON_PRETTY_PRINT);

        $scopes_array = $scopeRepository->getCurrentSmartScopes();
//        $scopes_array = $scopeRepository->getCurrentStandardScopes();
//        $scopes_array = array_merge($scopes_array_smart, $scopes_array);

        $scopes = json_encode($scopes_array, JSON_PRETTY_PRINT);

// Note: for token_endpoint_auth_signing_alg_values_supported we only support RS384 to be spec compliant
// @see http://hl7.org/fhir/uv/bulkdata/authorization/index.html#registering-a-smart-backend-service-communicating-public-keys
//  We can't support ES384 right now because lobucci/jwt does not support that.
        // TODO: @adunsulag if we replace lboccui/jwt with a library that supports ES384 then we can add it here
        $base_url = $this->baseUrl;
        $discovery = <<<TEMPLATE
{
"issuer": "$base_url",
"authorization_endpoint": "$base_url/authorize",
"token_endpoint": "$base_url/token",
"jwks_uri": "$base_url/jwk",
"userinfo_endpoint": "$base_url/userinfo",
"registration_endpoint": "$base_url/registration",
"end_session_endpoint": "$base_url/logout",
"introspection_endpoint": "$base_url/introspect",
"scopes_supported": $scopes,
"response_types_supported": [
    "code",
    "token",
    "id_token",
    "code token",
    "code id_token",
    "token id_token",
    "code token id_token"
],
"code_challenge_methods_supported": [
    "S256",
    "plain"
],
"grant_types_supported": [
    "authorization_code",
    $passwordGrantString
    "refresh_token"
],
"response_modes_supported": [
    "query",
    "fragment",
    "form_post"
],
"subject_types_supported": [
    "public"
],
"claims_supported": $claims,
"require_request_uri_registration": ["false"],
"id_token_signing_alg_values_supported": [
    "RS256"
],
"token_endpoint_auth_methods_supported": [
    "client_secret_post"
],
"token_endpoint_auth_signing_alg_values_supported": [
    "RS256",
    "RS384"
],
"claims_locales_supported": [
    "en-US"
],
"ui_locales_supported": [
    "en-US"
]
}
TEMPLATE;

        $request->getSession()->invalidate();
        return new JsonResponse($discovery, 200, [], true);
    }
}
