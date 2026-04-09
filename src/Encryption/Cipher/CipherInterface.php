<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Cipher;

use OpenEMR\Encryption\{
    Ciphertext,
    Plaintext,
};

interface CipherInterface
{
    public function decrypt(Ciphertext $ciphertext): Plaintext;

    public function encrypt(Plaintext $plaintext): Ciphertext;
}
