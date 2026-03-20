<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Cipher;

use OpenEMR\Encryption\Keys\KeyManagerInterface;

interface CipherInterface
{
    public function decrypt(string $ciphertext, string $keyId, KeyManagerInterface $manager): string;
}
