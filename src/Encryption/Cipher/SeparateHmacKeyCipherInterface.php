<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Cipher;

use OpenEMR\Encryption\Keys\KeyMaterial;

interface SeparateHmacKeyCipherInterface extends CipherInterface
{
    public function __construct(KeyMaterial $key, KeyMaterial $hmacKey);
}
