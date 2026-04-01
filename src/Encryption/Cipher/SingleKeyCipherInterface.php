<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Cipher;

use OpenEMR\Encryption\Keys\KeyMaterial;

interface SingleKeyCipherInterface extends CipherInterface
{
    public function __construct(KeyMaterial $key);
}
