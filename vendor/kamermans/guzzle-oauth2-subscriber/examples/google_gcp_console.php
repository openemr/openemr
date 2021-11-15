<?php
/**
 * Google Cloud Platform authentication example script
 * This console script uses the AuthorizationCode and RefreshToken grant types use the Google Cloud Platform
 * REST API.  When you run this script, you will be prompted for a Client ID and Client Secret.  If you have
 * gcloud configured on your machined, these can be found in Linux at:
 *   /home/USER/.config/gcloud/legacy_credentials/EMAIL/adc.json
 * Alternatively you can create and download credentials that have the scopes listed in the code below, but you
 * may need to adjust the $redirect_uri in that case.
 */

use kamermans\OAuth2\GrantType\AuthorizationCode;
use kamermans\OAuth2\GrantType\RefreshToken;
use kamermans\OAuth2\Persistence\FileTokenPersistence;
use kamermans\OAuth2\OAuth2Middleware;
use GuzzleHttp\HandlerStack;

require_once __DIR__."/../vendor/autoload.php";

$token_storage = new FileTokenPersistence('/tmp/token.txt');

echo "Enter your Google Cloud Platform Application Client ID: ";
$client_id = trim(fgets(STDIN, 1024));

echo "Enter your Google Cloud Platform Application Client Secret: ";
$client_secret = trim(fgets(STDIN, 1024));

// This is the authentication code that the user pasted into the console, or null if we don't need one
$auth_code = null;

// This is the redirect URI for the Google Cloud Platform SDK (aka 'gcloud')
// To use your own OAuth application, adjust this or prompt for it
$redirect_uri = 'urn:ietf:wg:oauth:2.0:oob';

// If we have no access token or refresh token, we need to get user consent to obtain one
if ($token_storage->hasToken() === false) {
    $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'prompt' => 'select_account',
        'scope' => implode(' ', [
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/cloud-platform',
            'https://www.googleapis.com/auth/appengine.admin',
            'https://www.googleapis.com/auth/compute',
            'https://www.googleapis.com/auth/accounts.reauth',
        ]),
        'access_type' => 'offline',
    ]);

    echo "Go to the following link in your browser:\n\n";
    echo "    $auth_url\n\n";

    echo "Enter verification code: ";
    $auth_code = trim(fgets(STDIN, 1024));
}

// Authorization client - this is used to request OAuth access tokens
$reauth_client = new GuzzleHttp\Client([
    // URL for access_token request
    'base_uri' => 'https://www.googleapis.com/oauth2/v4/token',
    // 'debug' => true,
]);

$reauth_config = [
    'code' => $auth_code,
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
];

$grant_type = new AuthorizationCode($reauth_client, $reauth_config);
$refresh_grant_type = new RefreshToken($reauth_client, $reauth_config);
$oauth = new OAuth2Middleware($grant_type, $refresh_grant_type);
$oauth->setTokenPersistence($token_storage);
$stack = HandlerStack::create();
$stack->push($oauth);

// This is the normal Guzzle client that you use in your application
$client = new GuzzleHttp\Client([
    'handler' => $stack,
    'auth'    => 'oauth',
]);

$endpoint_url = 'https://cloudresourcemanager.googleapis.com/v1/projects';
$response = $client->get($endpoint_url);
echo $response->getBody(), "\n";
