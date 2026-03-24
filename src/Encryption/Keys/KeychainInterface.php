<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys;

use OpenEMR\Encryption\Cipher\CipherInterface;

interface KeychainInterface
{
    public function getCipher(string $keyId): CipherInterface;
}
