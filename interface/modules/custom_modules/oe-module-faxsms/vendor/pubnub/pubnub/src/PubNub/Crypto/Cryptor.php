<?php

namespace PubNub\Crypto;

use PubNub\Crypto\Payload as CryptoPayload;

abstract class Cryptor
{
    public const CRYPTOR_ID = null;

    abstract public function encrypt(string $text, ?string $cipherKey = null): CryptoPayload;
    abstract public function decrypt(CryptoPayload $payload, ?string $cipherKey = null);
}
