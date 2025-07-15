<?php

namespace OpenEMR\Common\Crypto;

use OpenEMR\Common\Crypto\EncryptionStrategyInterface;

class NullEncryptionStrategy implements EncryptionStrategyInterface
{
    private string $nullVersion = "000";

    public function encryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive')
    {
        return ($value === null) ? null : $this->nullVersion . base64_encode($value);
    }

    public function decryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive', ?int $minimumVersion = null): false|string
    {
        if (empty($value)) {
            return "";
        }

        $encryptionVersion = intval(mb_substr($value, 0, 3, '8bit'));
        $trimmedValue = mb_substr($value, 3, null, '8bit');

        if (!empty($minimumVersion) && ($encryptionVersion < $minimumVersion)) {
            error_log("OpenEMR Error : Decryption is not working because the encrypt/decrypt version is lower than allowed.");
            return false;
        }

        if ($encryptionVersion !== 0) {
            error_log("OpenEMR Error : Null encryption strategy cannot decrypt non-null encrypted data (version: " . $encryptionVersion . ").");
            return false;
        }
        $decoded = base64_decode($trimmedValue, true);
        if ($decoded === false) {
            error_log("OpenEMR Error : Null decryption did not work because illegal characters were noted in base64_encoded data.");
            return false;
        }
        return $decoded;
    }

    public function cryptCheckStandard(?string $value): bool
    {
        return empty($value) ? false : (preg_match('/^000/', $value) === 1);
    }
}
