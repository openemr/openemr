<?php

declare(strict_types=1);

namespace OpenEMR\Modules\RainforestPayment\Rainforest;

use GuzzleHttp\{Client, ClientInterface};
use Money\Money;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Core\OEGlobalsBag;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use SensitiveParameter;

class Rainforest
{
    private const SANDBOX_HOST = 'https://api.sandbox.rainforestpay.com';
    private const PRODUCTION_HOST = 'https://api.rainforestpay.com';

    public function __construct(
        private ClientInterface $client,
        #[SensitiveParameter] private string $apiKey,
        private string $merchantId,
        private string $platformId,
    ) {
    }

    /**
     * Use the RainforestPay APIs to create information needed to use the
     * payment component.
     *
     * @link https://docs.rainforestpay.com/docs/process-payins-via-component
     *
     * TODO: tie this to a specific payment rather than yolo generating a uuid
     *
     * @param EncounterData[] $encounters
     *
     * @return array{
     *   payin_config_id: string,
     *   session_key: string,
     * }
     */
    public function getPaymentComponentParameters(Money $amount, string $patientId, array $encounters): array
    {
        // TODO: This should leverage Guzzle's abilities to make parallel
        // requests.
        $sessionPayload = [
            'ttl' => 86400,
            'statements' => [
                [
                    'permissions' => ['group#payment_component'],
                    'constraints' => [
                        'merchant' => [
                            'merchant_id' => $this->merchantId,
                        ],
                    ],
                ],
            ],
        ];
        $sessionResponse = $this->post('/v1/sessions', $sessionPayload);
        $sessionKey = $sessionResponse['data']['session_key'];


        $payinPayload = [
            'merchant_id' => $this->merchantId,
            'idempotency_key' => Uuid::uuid4()->toString(),
            'amount' => (int) $amount->getAmount(),
            'currency_code' => $amount->getCurrency()->getCode(),
            'metadata' => new Metadata(
                patientId: $patientId,
                encounters: $encounters,
            ),
        ];
        $payinResponse = $this->post('/v1/payin_configs', $payinPayload);
        $payinConfigId = $payinResponse['data']['payin_config_id'];

        return [
            'payin_config_id' => $payinConfigId,
            'session_key' => $sessionKey,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function post(string $path, array $payload): array
    {
        $response = $this->client->request('POST', $path, [
            'json' => $payload,
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $this->apiKey),
            ],
        ]);
        return self::parseResponse($response);
    }

    /**
     * Creates a client configured for internal use to interact with the
     * RainforestPay APIs. This is not part of the constructor so that a mock
     * client can be passed in during testing.
     */
    public static function makeClient(bool $liveMode): ClientInterface
    {
        return new Client([
            'base_uri' => $liveMode ? self::PRODUCTION_HOST : self::SANDBOX_HOST,
            'headers' => [
                'Rainforest-Api-Version' => '2024-10-16',
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ],
        ]);
    }

    /**
     * Create Rainforest instance from globals using OEGlobalsBag
     */
    public static function makeFromGlobals(OEGlobalsBag $bag): Rainforest
    {
        $cryptoGen = new CryptoGen();
        
        // Get encrypted values from globals and decrypt them
        $apiKeyEncrypted = $bag->get('rainforest_api_key') ?? '';
        $merchantIdEncrypted = $bag->get('rainforest_merchant_id') ?? '';
        $platformIdEncrypted = $bag->get('rainforest_platform_id') ?? '';
        
        $apiKey = !empty($apiKeyEncrypted) ? $cryptoGen->decryptStandard($apiKeyEncrypted) : '';
        $merchantId = !empty($merchantIdEncrypted) ? $cryptoGen->decryptStandard($merchantIdEncrypted) : '';
        $platformId = !empty($platformIdEncrypted) ? $cryptoGen->decryptStandard($platformIdEncrypted) : '';

        // Use gateway_mode_production from main OpenEMR globals
        $prod = $bag->get('gateway_mode_production') === '1' || $bag->get('gateway_mode_production') === 1;
        $client = self::makeClient($prod);
        return new Rainforest(
            client: $client,
            apiKey: $apiKey !== false ? $apiKey : '',
            merchantId: $merchantId !== false ? $merchantId : '',
            platformId: $platformId !== false ? $platformId : '',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function parseResponse(ResponseInterface $response): array
    {
        $json = (string) $response->getBody();
        $parsed = json_decode($json, flags: JSON_THROW_ON_ERROR | JSON_OBJECT_AS_ARRAY);
        assert(is_array($parsed));
        return $parsed;
    }
}
