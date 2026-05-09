<?php

declare(strict_types=1);

namespace OpenEMR\BC\Crypto;

use OpenEMR\Common\Crypto\{
    CryptoGenException,
    KeySource,
    KeyVersion,
};
use ValueError;

/**
 * This adds common methods related to CryptoInterface for contextual
 * encryption and checking about status
 */
trait ContextualEncryptionTrait
{
    public function encryptForDatabase(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        if (!$this->shouldEncryptForDatabase) {
            return $value;
        }
        return $this->encryptStandard($value, keySource: KeySource::Drive);
    }

    public function encryptForFilesystem(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        if (!$this->shouldEncryptForFilesystem) {
            return $value;
        }
        return $this->encryptStandard($value, keySource: KeySource::Database);
    }

    public function decryptFromDatabase(?string $value, ?int $minimumVersion = null): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        if (!$this->cryptCheckStandard($value)) {
            return $value;
        }
        $result = $this->decryptStandard($value, keySource: KeySource::Drive, minimumVersion: $minimumVersion);
        if ($result === false) {
            throw new CryptoGenException('Decryption failed');
        }
        return $result;
    }

    public function decryptFromFilesystem(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        if (!$this->cryptCheckStandard($value)) {
            return $value;
        }
        $result = $this->decryptStandard($value, keySource: KeySource::Database);
        if ($result === false) {
            throw new CryptoGenException('Decryption failed');
        }
        return $result;
    }

    /**
     * @inheritdoc
     *
     * Note: This uses the CryptoGen tooling even in BC\Crypto, intentionally.
     * As its internals note, it _also_ takes this path to remain compatible
     * with existing key management until we can fully cut over to new tooling.
     */
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
        } catch (ValueError) { // @phpstan-ignore openemr.forbiddenCatchType
            // Value is NOT encrypted.
            return !$this->shouldEncryptForDatabase;
        }
    }

    /**
     * See isDatabaseValueLatest - same.
     */
    public function isFilesystemValueLatest(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        try {
            $version = KeyVersion::fromPrefix($value);
            // Value IS encrypted
            return $this->shouldEncryptForFilesystem && $version === KeyVersion::CURRENT;
        } catch (ValueError) { // @phpstan-ignore openemr.forbiddenCatchType
            // Value is NOT encrypted.
            return !$this->shouldEncryptForFilesystem;
        }
    }
}
