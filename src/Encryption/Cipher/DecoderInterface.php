<?php

declare(strict_types=1);

namespace OpenEMR\Common\Crypto\Decrypt;

use OpenEMR\Common\Crypto\{
    CryptoGenException,
    Keys\KeyManagerInterface,
};

interface DecoderInterface
{
    public function decrypt(string $ciphertext, string $keyId, KeyManagerInterface $manager): string;
}
