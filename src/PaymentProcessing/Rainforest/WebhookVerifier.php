<?php

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing\Rainforest;

use SensitiveParameter;
use UnexpectedValueException;
use Psr\Http\Message\ServerRequestInterface;

use function hash_equals;
use function json_decode;
use function sprintf;
use function str_contains;
use function str_starts_with;
use function substr;

use const JSON_THROW_ON_ERROR;

// https://docs.svix.com/receiving/verifying-payloads/how-manual
class WebhookVerifier
{
    private string $secretBytes;

    public function __construct(
        #[SensitiveParameter] string $webhookSecret,
    ) {
        if (!str_contains($webhookSecret, '_')) {
            throw new UnexpectedValueException('Invalid secret');
        }
        [$prefix, $data] = explode('_', $webhookSecret, 2);
        $this->secretBytes = base64_decode($webhookSecret);
    }

    public function verify(ServerRequestInterface $request): Webhook
    {
        $id = $request->getHeaderLine('svix-id');
        $timestamp = $request->getHeaderLine('svix-timestamp');

        $body = (string) $request->getBody();

        $signedContent = sprintf(
            '%s.%s.%s',
            $id,
            $timestamp,
            $body,
        );
        $signature = hash_hmac(
            algo: 'sha256',
            data: $signedContent,
            key: $this->secretBytes,
            binary: true,
        );


        // Perform the verification in constant time.
        // DO NOT CHANGE THIS TO RETURN EARLY AS AN OPTIMIZATION.
        // Doing so will introduce a side-channel attack that could lead to
        // webhook exploitation.
        $verified = false;
        $reqSigs = $request->getHeaderLine('svix-signature');
        $signaturesToTry = explode(' ', $reqSigs);

        foreach ($signaturesToTry as $signatureWithVersion) {
            // The signature format is documented, so short-circuiting
            // a malformed entry doesn't yield additional information.
            if (!str_starts_with($signatureWithVersion, 'v1,')) {
                // TODO: log
                continue;
            }
            $testSig = substr($signatureWithVersion, 3);

            if (hash_equals($signature, $testSig)) {
                $verified = true;
            }
        }

        if (!$verified) {
            throw new \RuntimeException('Invalid signature');
        }

        $parsedBody = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        return new Webhook($parsedBody);
    }
}
