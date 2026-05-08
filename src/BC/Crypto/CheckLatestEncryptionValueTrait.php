<?php

declare(strict_types=1);

namespace OpenEMR\BC\Crypto;

use OpenEMR\Common\Crypto\KeyVersion;

trait CheckLatestEncryptionValueTrait
{
    public function isDatabaseValueLatest(string $value): bool
    {
        if ($value === '') {
            // Empty never encrypted
            return true;
        }

        try {
            $version = KeyVersion::fromPrefix($value);
            // Value IS encrypted
            return $this->shouldEncryptForDatabase && $version === KeyVersion::CURRENT;
        } catch (\ValueError) {
            // Value is NOT encrypted.
            return !$this->shouldEncryptForDatabase;
        }
        // db not togglable yet
        try {
            return KeyVersion::fromPrefix($value ?? '') === KeyVersion::CURRENT;
        } catch (\ValueError) {
            return false;
        }
    }

    public function isFilesystemValueLatest(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        try {
            $version = KeyVersion::fromPrefix($value);
            // Value IS encrypted
            return $this->shouldEncryptForFilesystem && $version === KeyVersion::CURRENT;
        } catch (\ValueError) {
            // Value is NOT encrypted.
            return !$this->shouldEncryptForFilesystem;
        }
    }
}
