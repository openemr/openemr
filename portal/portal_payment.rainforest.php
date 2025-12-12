<?php

declare(strict_types=1);

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('405 Method Not Allowed');
    exit(1);
}

// Future scope: proper JSON API
$dollars = $_POST['dollars'] ?? '0.00';
assert(is_string($dollars));
// TODO: validate this properly
$cents = intval(100 * floatval($dollars));
error_log($dollars);
error_log((string)$cents);

// TODO: read from config!
$apiKey = '';
$pid = '';
$mid = '';

$client = new \GuzzleHttp\Client([
    'base_uri' => 'https://api.sandbox.rainforestpay.com',
    'headers' => [
        'Rainforest-Api-Version' => '2024-10-16',
        'accept' => 'application/json',
        'content-type' => 'application/json',
        'authorization' => 'Bearer ' . $apiKey,
    ],
]);

$output = [];

try {
    $payload = [
        'ttl' => 86400,
        'statements' => [
            [
                'permissions' => ['group#payment_component'],
                'constraints' => [
                    'merchant' => [
                        'merchant_id' => $mid,
                    ],
                ],
            ],
        ],
    ];
    $response = $client->request('POST', '/v1/sessions', [
        'json' => $payload,
    ]);

    $json = (string) $response->getBody();
    $parsed = json_decode($json, flags: JSON_THROW_ON_ERROR | JSON_OBJECT_AS_ARRAY);
    // print_r($parsed);

    // $out['session_id'] = $parsed['data']['session_id'];
    $out['session_key'] = $parsed['data']['session_key'];

} catch (Throwable $e) {
    echo $e;
    exit(1);
}

// echo "Payin config\n";


try {
    $uuid = Ramsey\Uuid\Uuid::uuid4();
    // \PHPStan\dumpType($uuid);
    $payload = [
        'merchant_id' => $mid,
        'idempotency_key' => $uuid->toString(),
        // 'amount' => 5000,
        'amount' => $cents,
        'currency_code' => 'USD',
    ];
    // \PHPStan\dumpType($payload);
    $response = $client->request('POST', '/v1/payin_configs', [
        'json' => $payload,
    ]);
    $json = (string) $response->getBody();
    // echo $json;
    $parsed = json_decode($json, flags: JSON_THROW_ON_ERROR | JSON_OBJECT_AS_ARRAY);
    // print_r($parsed);
    //
    $out['payin_config_id'] = $parsed['data']['payin_config_id'];
} catch (Throwable $e) {
    echo $e;
    exit(1);
}

header('Content-type: application/json');
echo json_encode($out);
