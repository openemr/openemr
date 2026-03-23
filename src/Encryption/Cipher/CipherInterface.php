<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Cipher;

use OpenEMR\Encryption\Plaintext;

interface CipherInterface
{
    public function decrypt(string $ciphertext): Plaintext;
}
