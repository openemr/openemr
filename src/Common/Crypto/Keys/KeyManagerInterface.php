<?php

declare(strict_types=1);

namespace OpenEMR\Common\Crypto\Keys;

interface KeyManagerInterface
{
    public function getKey(string $identifer): KeyMaterial;
}
