<?php

use OpenEMR\Common\Session\SessionUtil;

if ($oauthjwk !== true) {
    $message = xlt("Error. Not authorized");
    SessionUtil::oauthSessionCookieDestroy();
    echo $message;
    exit();
}

$public = file_get_contents($gbl::$publicKey);
$keyPublic = openssl_pkey_get_details(openssl_pkey_get_public($public));
$key_info = [
    'kty' => 'RSA',
    'n' => base64url_encode($keyPublic['rsa']['n']),
    'e' => base64url_encode($keyPublic['rsa']['e']),
];
$key_info['use'] = 'sig';

$jsonData = ['keys' => [$key_info]];

SessionUtil::oauthSessionCookieDestroy();

try {
    header('Content-type: application/json');
    echo json_encode($jsonData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT) . PHP_EOL;
    exit;
} catch (Exception $e) {
    http_response_code(400);
    exit;
}

function base64url_encode($input)
{
    return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
}
