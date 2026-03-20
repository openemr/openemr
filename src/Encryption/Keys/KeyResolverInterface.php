<?php

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys;

use OpenEMR\Encryption\Cipher\CipherInterface;

interface KeyResolverInterface
{
    public function resolve(string $keyReference): CipherInterface;
}
