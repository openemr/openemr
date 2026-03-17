<?php

declare(strict_types=1);

namespace OpenEMR\Common\Crypto\Decrypt;

use OpenEMR\Common\Crypto\{
    CryptoGenException,
    Keys\KeyManagerInterface,
};

interface StrategyInterface
{
    public function decrypt(string $ciphertext, string $keyId, KeyManagerInterface $keyManager): string;
}
