<?php

declare(strict_types=1);

namespace OpenEMR\Common\Crypto\Decrypt;

use OpenEMR\Common\Crypto\{
    CryptoGenException,
    KeyMaterial,
};

interface StrategyInterface
{
    public function decrypt(string $ciphertext, KeyMaterial $keyMaterial): string;
}
