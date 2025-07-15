<?php

namespace OpenEMR\Common\Crypto;

interface EncryptionStrategyInterface
{
    public function encryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive');

    public function decryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive', ?int $minimumVersion = null): false|string;

    public function cryptCheckStandard(?string $value): bool;
}
