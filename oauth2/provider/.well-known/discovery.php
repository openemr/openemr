<?php

use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ScopeRepository;
use OpenEMR\Common\Session\SessionUtil;

if ($oauthdisc !== true) {
    $message = xlt("Error. Not authorized");
    SessionUtil::oauthSessionCookieDestroy();
    echo $message;
    exit();
}

$passwordGrantString = '';
if (!empty($GLOBALS['oauth_password_grant'])) {
    $passwordGrantString = '"password",';
}
// PHP is a fickle beast!
$scopeRepository = new ScopeRepository();
$claims_array = $scopeRepository->getSupportedClaims();
$claims = json_encode($claims_array, JSON_PRETTY_PRINT);

$scopes_array_smart = $scopeRepository->getCurrentSmartScopes();
$scopes_array = $scopeRepository->getCurrentStandardScopes();
$scopes_array = array_merge($scopes_array_smart, $scopes_array);

$scopes = json_encode($scopes_array, JSON_PRETTY_PRINT);

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
    "RS256"
],
"claims_locales_supported": [
    "en-US"
],
"ui_locales_supported": [
    "en-US"
]
}
TEMPLATE;

SessionUtil::oauthSessionCookieDestroy();

header('Content-Type: application/json');
echo($discovery);
