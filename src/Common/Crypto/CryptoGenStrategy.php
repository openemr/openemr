<?php

namespace OpenEMR\Common\Crypto;

use Exception;
use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Common\Crypto\EncryptionStrategyInterface;
use OpenEMR\Common\Utils\RandomGenUtils;

class CryptoGenStrategy implements EncryptionStrategyInterface
{
    private string $encryptionVersion = "006";
    private string $keyVersion = "six";
    private array $keyCache = [];

    public function encryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive')
    {
        return $this->encryptionVersion . $this->coreEncrypt($value, $customPassword, $keySource, $this->keyVersion);
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

        return match ($encryptionVersion) {
            6 => $this->coreDecrypt($trimmedValue, $customPassword, $keySource, "six"),
            5 => $this->coreDecrypt($trimmedValue, $customPassword, $keySource, "five"),
            4 => $this->coreDecrypt($trimmedValue, $customPassword, $keySource, "four"),
            2, 3 => $this->aes256DecryptTwo($trimmedValue, $customPassword),
            1 => $this->aes256DecryptOne($trimmedValue, $customPassword),
            default => (function () {
                error_log("OpenEMR Error : Decryption is not working because of unknown encrypt/decrypt version.");
                return false;
            })(),
        };
    }

    public function cryptCheckStandard(?string $value): bool
    {
        return !empty($value) && preg_match('/^00[1-6]/', $value);
    }

    private function coreEncrypt(?string $sValue, ?string $customPassword = null, string $keySource = 'drive', ?string $keyNumber = null): string
    {
        $keyNumber = isset($keyNumber) ? $keyNumber : $this->keyVersion;

        $this->validateOpenSSLExtension();

        [$sSecretKey, $sSecretKeyHmac, $sSalt] = $this->deriveKeys($customPassword, $keyNumber, $keySource);
        $this->validateKeys($sSecretKey, $sSecretKeyHmac);

        $iv = RandomGenUtils::produceRandomBytes(openssl_cipher_iv_length('aes-256-cbc'));
        if (empty($iv)) {
            throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
        }

        $processedValue = openssl_encrypt(
            $sValue,
            'aes-256-cbc',
            $sSecretKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        $hmacHash = hash_hmac('sha384', $iv . $processedValue, $sSecretKeyHmac, true);

        if ($sValue != "" && ($processedValue == "" || $hmacHash == "")) {
            throw new CryptoGenException("OpenEMR Error : Encryption is not working (encrypted value is blank or hmac hash is blank).");
        }

        $completedValue = $hmacHash . $iv . $processedValue;
        if (!empty($customPassword)) {
            $completedValue = $sSalt . $completedValue;
        }

        return base64_encode($completedValue);
    }

    private function coreDecrypt(string $sValue, ?string $customPassword = null, string $keySource = 'drive', ?string $keyNumber = null): false|string
    {
        $keyNumber = isset($keyNumber) ? $keyNumber : $this->keyVersion;

        if (!extension_loaded('openssl')) {
            error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
            return false;
        }

        $raw = base64_decode($sValue, true);
        if ($raw === false) {
            error_log("OpenEMR Error : Decryption did not work because illegal characters were noted in base64_encoded data.");
            return false;
        }

        if (empty($customPassword)) {
            $sSecretKey = $this->collectCryptoKey($keyNumber, "a", $keySource);
            $sSecretKeyHmac = $this->collectCryptoKey($keyNumber, "b", $keySource);
        } else {
            $sSalt = mb_substr($raw, 0, 32, '8bit');
            $raw = mb_substr($raw, 32, null, '8bit');
            $sPreKey = hash_pbkdf2('sha384', $customPassword, $sSalt, 100000, 32, true);
            $sSecretKey = hash_hkdf('sha384', $sPreKey, 32, 'aes-256-encryption', $sSalt);
            $sSecretKeyHmac = hash_hkdf('sha384', $sPreKey, 32, 'sha-384-authentication', $sSalt);
        }

        if (empty($sSecretKey) || empty($sSecretKeyHmac)) {
            error_log("OpenEMR Error : Decryption is not working because key(s) is blank.");
            return false;
        }

        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $hmacHash = mb_substr($raw, 0, 48, '8bit');
        $iv = mb_substr($raw, 48, $ivLength, '8bit');
        $encrypted_data = mb_substr($raw, ($ivLength + 48), null, '8bit');

        $calculatedHmacHash = hash_hmac('sha384', $iv . $encrypted_data, $sSecretKeyHmac, true);

        if (hash_equals($hmacHash, $calculatedHmacHash)) {
            return openssl_decrypt(
                $encrypted_data,
                'aes-256-cbc',
                $sSecretKey,
                OPENSSL_RAW_DATA,
                $iv
            );
        } else {
            try {
                throw new Exception("OpenEMR Error: Decryption failed HMAC Authentication!");
            } catch (Exception $e) {
                $stackTrace = debug_backtrace();
                $formattedStackTrace = $this->formatExceptionMessage($stackTrace);
                error_log(errorLogEscape($e->getMessage()) . "\n" . errorLogEscape($formattedStackTrace));
                return false;
            }
        }
    }

    private function formatExceptionMessage($stackTrace): string
    {
        $formattedStackTrace = "Possibly Config Password or Token. Error Call Stack:\n";
        foreach ($stackTrace as $index => $call) {
            $formattedStackTrace .= "#" . $index . " ";
            if (isset($call['file'])) {
                $formattedStackTrace .= $call['file'] . " ";
                if (isset($call['line'])) {
                    $formattedStackTrace .= "(" . $call['line'] . "): ";
                }
            }
            if (isset($call['class'])) {
                $formattedStackTrace .= $call['class'] . $call['type'];
            }
            if (isset($call['function'])) {
                $formattedStackTrace .= $call['function'] . "()\n";
            }
        }
        return $formattedStackTrace;
    }

    public function aes256DecryptTwo(?string $sValue, ?string $customPassword = null): false|string
    {
        if (!extension_loaded('openssl')) {
            error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
            return false;
        }

        if (empty($customPassword)) {
            $sSecretKey = $this->collectCryptoKey("two", "a");
            $sSecretKeyHmac = $this->collectCryptoKey("two", "b");
        } else {
            $sSecretKey = hash("sha256", $customPassword, true);
            $sSecretKeyHmac = $sSecretKey;
        }

        if (empty($sSecretKey) || empty($sSecretKeyHmac)) {
            error_log("OpenEMR Error : Decryption is not working because key(s) is blank.");
            return false;
        }

        $raw = base64_decode($sValue, true);
        if ($raw === false) {
            error_log("OpenEMR Error : Decryption did not work because illegal characters were noted in base64_encoded data.");
            return false;
        }

        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $hmacHash = mb_substr($raw, 0, 32, '8bit');
        $iv = mb_substr($raw, 32, $ivLength, '8bit');
        $encrypted_data = mb_substr($raw, ($ivLength + 32), null, '8bit');

        $calculatedHmacHash = hash_hmac('sha256', $iv . $encrypted_data, $sSecretKeyHmac, true);

        if (hash_equals($hmacHash, $calculatedHmacHash)) {
            return openssl_decrypt(
                $encrypted_data,
                'aes-256-cbc',
                $sSecretKey,
                OPENSSL_RAW_DATA,
                $iv
            );
        } else {
            try {
                throw new Exception("OpenEMR Error: Decryption failed hmac authentication!");
            } catch (Exception $e) {
                $stackTrace = debug_backtrace();
                $formattedStackTrace = $this->formatExceptionMessage($stackTrace);
                error_log(errorLogEscape($e->getMessage()) . "\n" . errorLogEscape($formattedStackTrace));
                return false;
            }
        }
    }

    public function aes256DecryptOne(?string $sValue, ?string $customPassword = null): false|string
    {
        if (!extension_loaded('openssl')) {
            error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
            return false;
        }

        if (empty($customPassword)) {
            $sSecretKey = $this->collectCryptoKey();
        } else {
            $sSecretKey = hash("sha256", $customPassword);
        }

        if (empty($sSecretKey)) {
            error_log("OpenEMR Error : Decryption is not working because key is blank.");
            return false;
        }

        $raw = base64_decode($sValue);

        $ivLength = openssl_cipher_iv_length('aes-256-cbc');

        $iv = substr($raw, 0, $ivLength);
        $encrypted_data = substr($raw, $ivLength);

        return openssl_decrypt(
            $encrypted_data,
            'aes-256-cbc',
            $sSecretKey,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    private function validateOpenSSLExtension(): void
    {
        if (!extension_loaded('openssl')) {
            throw new CryptoGenException("OpenEMR Error : Encryption is not working because missing openssl extension.");
        }
    }

    private function deriveKeys(?string $customPassword, string $keyNumber, string $keySource): array
    {
        if (empty($customPassword)) {
            return [
                $this->collectCryptoKey($keyNumber, "a", $keySource),
                $this->collectCryptoKey($keyNumber, "b", $keySource),
                null
            ];
        }

        $sSalt = RandomGenUtils::produceRandomBytes(32);
        if (empty($sSalt)) {
            throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
        }

        $sPreKey = hash_pbkdf2('sha384', $customPassword, $sSalt, 100000, 32, true);
        return [
            hash_hkdf('sha384', $sPreKey, 32, 'aes-256-encryption', $sSalt),
            hash_hkdf('sha384', $sPreKey, 32, 'sha-384-authentication', $sSalt),
            $sSalt
        ];
    }

    private function validateKeys(string $secretKey, string $secretKeyHmac): void
    {
        if (empty($secretKey) || empty($secretKeyHmac)) {
            throw new CryptoGenException("OpenEMR Error : Encryption is not working because key(s) is blank.");
        }
    }

    private function collectCryptoKey(string $version = "one", string $sub = "", string $keySource = 'drive'): string
    {
        $cacheLabel = $version . $sub . $keySource;
        if (!empty($this->keyCache[$cacheLabel])) {
            return $this->keyCache[$cacheLabel];
        }

        $label = $version . $sub;

        if ($keySource == 'database') {
            $sqlValue = sqlQueryNoLog("SELECT `value` FROM `keys` WHERE `name` = ?", [$label]);
            if (empty($sqlValue['value'])) {
                $newKey = RandomGenUtils::produceRandomBytes(32);
                if (empty($newKey)) {
                    throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
                }
                sqlStatementNoLog("INSERT INTO `keys` (`name`, `value`) VALUES (?, ?)", [$label, base64_encode($newKey)]);
            }
        } else {
            if (!file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label)) {
                $newKey = RandomGenUtils::produceRandomBytes(32);
                if (empty($newKey)) {
                    throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
                }
                if (($version == "one") || ($version == "two") || ($version == "three") || ($version == "four")) {
                    file_put_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label, base64_encode($newKey));
                } else {
                    // For newer key versions, we need to encrypt using database keys
                    // Create a temporary instance to handle this without circular dependency
                    $tempStrategy = new self();
                    $encryptedKey = $tempStrategy->encryptUsingDatabaseKeys($newKey, $version);
                    file_put_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label, $encryptedKey);
                }
            }
        }

        if ($keySource == 'database') {
            $sqlKey = sqlQueryNoLog("SELECT `value` FROM `keys` WHERE `name` = ?", [$label]);
            $key = base64_decode($sqlKey['value']);
        } else {
            if (($version == "one") || ($version == "two") || ($version == "three") || ($version == "four")) {
                $key = base64_decode(rtrim(file_get_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label)));
            } else {
                $key = $this->decryptUsingDatabaseKeys(file_get_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label), $version);
            }
        }

        if (empty($key)) {
            if ($keySource == 'database') {
                throw new CryptoGenException("OpenEMR Error : Key creation in database is not working - Exiting.");
            } else {
                if (!file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label)) {
                    throw new CryptoGenException("OpenEMR Error : Key creation in drive is not working - Exiting.");
                } else {
                    throw new CryptoGenException("OpenEMR Error : Key in drive is not compatible (ie. can not be decrypted) with key in database - Exiting.");
                }
            }
        }

        $this->keyCache[$cacheLabel] = $key;
        return $key;
    }

    private function encryptUsingDatabaseKeys(string $value, string $keyNumber): string
    {
        $sSecretKey = $this->collectCryptoKey($keyNumber, "a", 'database');
        $sSecretKeyHmac = $this->collectCryptoKey($keyNumber, "b", 'database');

        $iv = RandomGenUtils::produceRandomBytes(openssl_cipher_iv_length('aes-256-cbc'));
        if (empty($iv)) {
            throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
        }

        $processedValue = openssl_encrypt(
            $value,
            'aes-256-cbc',
            $sSecretKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        $hmacHash = hash_hmac('sha384', $iv . $processedValue, $sSecretKeyHmac, true);
        $completedValue = $hmacHash . $iv . $processedValue;

        return $this->encryptionVersion . base64_encode($completedValue);
    }

    private function decryptUsingDatabaseKeys(string $value, string $keyNumber): string
    {
        $encryptionVersion = intval(mb_substr($value, 0, 3, '8bit'));
        $trimmedValue = mb_substr($value, 3, null, '8bit');

        if ($encryptionVersion != 6) {
            throw new CryptoGenException("OpenEMR Error : Unexpected encryption version for database key decryption: " . $encryptionVersion);
        }

        $sSecretKey = $this->collectCryptoKey($keyNumber, "a", 'database');
        $sSecretKeyHmac = $this->collectCryptoKey($keyNumber, "b", 'database');

        $raw = base64_decode($trimmedValue, true);
        if ($raw === false) {
            throw new CryptoGenException("OpenEMR Error : Decryption did not work because illegal characters were noted in base64_encoded data.");
        }

        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $hmacHash = mb_substr($raw, 0, 48, '8bit');
        $iv = mb_substr($raw, 48, $ivLength, '8bit');
        $encrypted_data = mb_substr($raw, ($ivLength + 48), null, '8bit');

        $calculatedHmacHash = hash_hmac('sha384', $iv . $encrypted_data, $sSecretKeyHmac, true);

        if (!hash_equals($hmacHash, $calculatedHmacHash)) {
            throw new CryptoGenException("OpenEMR Error: Database key decryption failed HMAC Authentication!");
        }

        return openssl_decrypt(
            $encrypted_data,
            'aes-256-cbc',
            $sSecretKey,
            OPENSSL_RAW_DATA,
            $iv
        );
    }
}
