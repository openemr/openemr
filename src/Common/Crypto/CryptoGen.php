<?php

/**
 * CryptoGen class.
 *
 *   OpenEMR encryption/decryption strategy:
 *    1. Two separate private key sets are used, one key set in the database and one key set on the drive.
 *    2. The private database key set is stored in the keys mysql table
 *    3. The private drive key set is stored in sites/<site-name>/documents/logs_and_misc/methods/
 *    4. The private database key set is used when encrypting/decrypting data that is stored on the drive.
 *    5. The private drive key set is used when encrypting/decrypting data that is stored in the database.
 *    6. The private drive key set is encrypted by the private database key set
 *    7. Encryption/key versioning is used to support algorithm improvements while also ensuring
 *       backwards compatibility of decryption.
 *    8. To ensure performance, the CryptoGen class will cache the key sets that are used inside the object,
 *       which avoids numerous repeat calls to collect the key sets (and repeat decryption of the key set
 *       from the drive).
 *    9. There is also support for passphrase encryption/decryption (ie. no private keys are used).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ensoftek, Inc
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2015 Ensoftek, Inc
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Crypto;

use Exception;
use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Common\Crypto\EncryptionStrategyInterface;
use OpenEMR\Common\Crypto\EncryptionStrategyEvent;
use OpenEMR\Common\Crypto\CryptoGenStrategy;
use OpenEMR\Common\Utils\RandomGenUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLogger;

class CryptoGen
{
    # This is the current encrypt/decrypt version
    # (this will always be a three digit number that we will
    #  increment when update the encrypt/decrypt methodology
    #  which allows being able to maintain backward compatibility
    #  to decrypt values from prior versions)
    # Remember to update cryptCheckStandard() and decryptStandard()
    #  when increment this.
    private string $encryptionVersion = "006";
    # This is the current key version. As above, will increment this
    #  when update the encrypt/decrypt methodology to allow backward
    #  compatibility.
    # Remember to update decryptStandard() when increment this.
    private string $keyVersion = "six";

    # Key cache to optimize key collection, which avoids numerous repeat
    #  calls to collect the key sets (and repeat decryption of the key set
    #  from the drive).
    private array $keyCache = [];

    private EncryptionStrategyInterface $encryptionStrategy;
    private SystemLogger $logger;

    /**
     * Constructor - Initialize CryptoGen with encryption strategy.
     *
     * Event-based selection takes precedence over constructor parameter.
     * This allows modules to override the encryption strategy even when
     * one is explicitly provided in the constructor.
     *
     * Priority order:
     * 1. Event-dispatched strategy (highest priority)
     * 2. Constructor parameter
     * 3. Default CryptoGenStrategy (fallback)
     *
     * @param EncryptionStrategyInterface|null $encryptionStrategy Strategy to use if no event handler provides one
     * @throws CryptoGenException If invalid encryption strategy is provided
     */
    public function __construct(?EncryptionStrategyInterface $encryptionStrategy = null)
    {
        $this->logger = new SystemLogger();

        $this->logger->debug("CryptoGen: Constructor called", [
            'provided_strategy' => $encryptionStrategy ? get_class($encryptionStrategy) : 'null'
        ]);

        // Dispatch event to allow modules to provide custom encryption strategy
        $event = new EncryptionStrategyEvent();
        if (isset($GLOBALS['kernel']) && method_exists($GLOBALS['kernel'], 'getEventDispatcher')) {
            $this->logger->debug("CryptoGen: Dispatching strategy selection event");
            $GLOBALS['kernel']->getEventDispatcher()->dispatch($event, EncryptionStrategyEvent::STRATEGY_SELECT);
        } else {
            $this->logger->debug("CryptoGen: No event dispatcher available, skipping event dispatch");
        }
        if ($event->hasStrategy() && $event->getStrategy() instanceof EncryptionStrategyInterface) {
            // Event-based strategy takes precedence
            $this->encryptionStrategy = $event->getStrategy();
            $strategyClass = get_class($this->encryptionStrategy);
            EventAuditLogger::instance()->newEvent(
                EncryptionStrategyEvent::STRATEGY_SELECT,
                $_SESSION['authUser'] ?? 'system',
                $_SESSION['authProvider'] ?? 'system',
                1,
                "Event-dispatched encryption strategy selected: {$strategyClass}"
            );
        } elseif ($encryptionStrategy instanceof EncryptionStrategyInterface) {
            // Constructor parameter strategy
            $this->encryptionStrategy = $encryptionStrategy;
            $strategyClass = get_class($this->encryptionStrategy);
            EventAuditLogger::instance()->newEvent(
                EncryptionStrategyEvent::STRATEGY_SELECT,
                $_SESSION['authUser'] ?? 'system',
                $_SESSION['authProvider'] ?? 'system',
                1,
                "Constructor-provided encryption strategy selected: {$strategyClass}"
            );
        } elseif (is_null($encryptionStrategy)) {
            // Default fallback strategy
            $this->encryptionStrategy = new CryptoGenStrategy();
            EventAuditLogger::instance()->newEvent(
                EncryptionStrategyEvent::STRATEGY_SELECT,
                $_SESSION['authUser'] ?? 'system',
                $_SESSION['authProvider'] ?? 'system',
                1,
                "Default encryption strategy selected: CryptoGenStrategy"
            );
        } else {
            throw new CryptoGenException("OpenEMR Error: Invalid encryption strategy provided.");
        }
    }


    /**
     * Standard function to encrypt
     *
     * @param string|null $value          This is the data to encrypt.
     * @param string|null $customPassword If provide a password, then will derive keys from this.(and will not use the standard keys)
     * @param string      $keySource      This is the source of the standard keys. Options are 'drive' and 'database'
     *
     */
    public function encryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive')
    {
        $this->logger->debug("CryptoGen: encryptStandard called", [
            'has_custom_password' => !is_null($customPassword),
            'key_source' => $keySource,
            'strategy' => get_class($this->encryptionStrategy)
        ]);

        $result = $this->encryptionStrategy->encryptStandard($value, $customPassword, $keySource);

        $this->logger->debug("CryptoGen: encryptStandard completed", [
            'success' => $result !== null
        ]);

        return $result;
    }

    /**
     * Standard function to decrypt
     *
     * @param string|null $value          This is the data to decrypt.
     * @param string|null $customPassword If provide a password, then will derive keys from this.(and will not use the standard keys)
     * @param string      $keySource      This is the source of the standard keys. Options are 'drive' and 'database'
     * @param int|null    $minimumVersion This is the minimum encryption version supported (useful if accepting encrypted data
     *                                    from outside OpenEMR to ensure bad actor is not trying to use an older version).
     * @return false|string
     */
    public function decryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive', ?int $minimumVersion = null): false|string
    {
        $this->logger->debug("CryptoGen: decryptStandard called", [
            'has_custom_password' => !is_null($customPassword),
            'key_source' => $keySource,
            'minimum_version' => $minimumVersion,
            'strategy' => get_class($this->encryptionStrategy)
        ]);

        $result = $this->encryptionStrategy->decryptStandard($value, $customPassword, $keySource, $minimumVersion);

        $this->logger->debug("CryptoGen: decryptStandard completed", [
            'success' => $result !== false
        ]);

        return $result;
    }

    /**
     * Check if a crypt block is valid to use for the standard method
     * (basically checks if correct values are used)
     */
    public function cryptCheckStandard(?string $value): bool
    {
        $this->logger->debug("CryptoGen: cryptCheckStandard called", [
            'strategy' => get_class($this->encryptionStrategy)
        ]);

        $result = $this->encryptionStrategy->cryptCheckStandard($value);

        $this->logger->debug("CryptoGen: cryptCheckStandard completed", [
            'result' => $result
        ]);

        return $result;
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

    /**
     * Function to AES256 decrypt a given string, version 2
     *
     * @param string|null $sValue              Encrypted data that will be decrypted.
     * @param string|null $customPassword If null, then use standard key. If provide a password, then will derive key from this.
     * @return false|string alse          returns the decrypted data or false if failed.
     */
    public function aes256DecryptTwo(?string $sValue, ?string $customPassword = null): false|string
    {
        if (!extension_loaded('openssl')) {
            error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
            return false;
        }

        if (empty($customPassword)) {
            // Collect the encryption keys.
            // The first key is for encryption. Then second key is for the HMAC hash
            $sSecretKey = $this->collectCryptoKey("two", "a");
            $sSecretKeyHmac = $this->collectCryptoKey("two", "b");
        } else {
            // Turn the password into a hash(note use binary) to use as the keys
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
                // throw an exception
                throw new Exception("OpenEMR Error: Decryption failed hmac authentication!");
            } catch (Exception $e) {
                // log the exception message and call stack then return legacy null as false for
                // those evaluating the return value as $return == false which with legacy will eval as false.
                // I've seen this in the codebase, and it's a bit of a hack, but it's a way to return false instead of null.
                // Dev's should use empty() instead of == false to check return from this function.
                // The goal here is so the call stack is exposed to track back to where the call originated.
                $stackTrace = debug_backtrace();
                $formattedStackTrace = $this->formatExceptionMessage($stackTrace);
                error_log(errorLogEscape($e->getMessage()) . "\n" . errorLogEscape($formattedStackTrace));
                return false;
            }
        }
    }

    /**
     * Function to AES256 decrypt a given string, version 1
     *
     * @param string|null $sValue              Encrypted data that will be decrypted.
     * @param string|null $customPassword If null, then use standard key. If provide a password, then will derive key from this.
     * @return false|string               returns the decrypted data.
     */
    public function aes256DecryptOne(?string $sValue, ?string $customPassword = null): false|string
    {
        if (!extension_loaded('openssl')) {
            error_log("OpenEMR Error : Decryption is not working because missing openssl extension.");
            return false;
        }

        if (empty($customPassword)) {
            // Collect the key. If it does not exist, then create it
            $sSecretKey = $this->collectCryptoKey();
        } else {
            // Turn the password into a hash to use as the key
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

    // Function to decrypt a given string
    // This specific function is only used for backward compatibility
    // TODO: Should be removed in the future.
    public function aes256Decrypt_mycrypt($sValue)
    {
        $sSecretKey = pack('H*', "bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3");
        return rtrim(
            mcrypt_decrypt(
                MCRYPT_RIJNDAEL_256,
                $sSecretKey,
                base64_decode($sValue),
                MCRYPT_MODE_ECB,
                mcrypt_create_iv(
                    mcrypt_get_iv_size(
                        MCRYPT_RIJNDAEL_256,
                        MCRYPT_MODE_ECB
                    ),
                    MCRYPT_RAND
                )
            ),
            "\0"
        );
    }

    /**
     * Function to collect (and create, if needed) the standard keys
     *  This mechanism will allow easy migration to new keys/ciphers in the future while
     *  also maintaining backward compatibility of encrypted data.
     *
     * Note that to increase performance, it will store the key as a variable in this object in case
     *  the key is used again (especially important when reading encrypted log entries where there
     *  can be hundreds of decryption calls where it otherwise requires 5 steps to get the key; collect
     *  key set from database, collect key set from drive, decrypt key set from drive using the database
     *  key; caching the key will bypass all these steps).
     *
     * @param string $version     This is the number/version of they key.
     * @param string $sub         This is the sublabel of the key
     * @param string $keySource   This is the source of the standard keys. Options are 'drive' and 'database'
     *                            The 'drive' keys are stored at sites/<site-dir>/documents/logs_and_misc/methods
     *                            The 'database' keys are stored in the 'keys' sql table
     * @return string             Returns the key in raw form.
     * @throws CryptoGenException if fails, which are critical errors requiring die of script
     */
    private function collectCryptoKey(string $version = "one", string $sub = "", string $keySource = 'drive'): string
    {
        $this->logger->debug("CryptoGen: collectCryptoKey called", [
            'version' => $version,
            'sub' => $sub,
            'key_source' => $keySource
        ]);

        // Check if key is in the cache first (and return it if it is)
        $cacheLabel = $version . $sub . $keySource;
        if (!empty($this->keyCache[$cacheLabel])) {
            $this->logger->debug("CryptoGen: Key found in cache", ['cache_label' => $cacheLabel]);
            return $this->keyCache[$cacheLabel];
        }

        // Build the main label
        $label = $version . $sub;

        // If the key does not exist, then create it
        if ($keySource == 'database') {
            $sqlValue = sqlQueryNoLog("SELECT `value` FROM `keys` WHERE `name` = ?", [$label]);
            if (empty($sqlValue['value'])) {
                // Create a new key and place in database
                // Produce a 256bit key (32 bytes equals 256 bits)
                $newKey = RandomGenUtils::produceRandomBytes(32);
                if (empty($newKey)) {
                    throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
                }
                sqlStatementNoLog("INSERT INTO `keys` (`name`, `value`) VALUES (?, ?)", [$label, base64_encode($newKey)]);
            }
        } else { //$keySource == 'drive'
            if (!file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label)) {
                // Create a key and place in drive
                // Produce a 256bit key (32 bytes equals 256 bits)
                $newKey = RandomGenUtils::produceRandomBytes(32);
                if (empty($newKey)) {
                    throw new CryptoGenException("OpenEMR Error : Random Bytes error - exiting");
                }
                if (($version == "one") || ($version == "two") || ($version == "three") || ($version == "four")) {
                    // older key versions that did not encrypt the key on the drive
                    file_put_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label, base64_encode($newKey));
                } else {
                    file_put_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label, $this->encryptStandard($newKey, null, 'database'));
                }
            }
        }

        // Collect key
        if ($keySource == 'database') {
            $sqlKey = sqlQueryNoLog("SELECT `value` FROM `keys` WHERE `name` = ?", [$label]);
            $key = base64_decode($sqlKey['value']);
        } else { //$keySource == 'drive'
            if (($version == "one") || ($version == "two") || ($version == "three") || ($version == "four")) {
                // older key versions that did not encrypt the key on the drive
                $key = base64_decode(rtrim(file_get_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label)));
            } else {
                $key = $this->decryptStandard(file_get_contents($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label), null, 'database');
            }
        }

        // Ensure have a key (if do not have key, then is critical error, and will exit)
        if (empty($key)) {
            if ($keySource == 'database') {
                throw new CryptoGenException("OpenEMR Error : Key creation in database is not working - Exiting.");
            } else { //$keySource == 'drive'
                if (!file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/methods/" . $label)) {
                    throw new CryptoGenException("OpenEMR Error : Key creation in drive is not working - Exiting.");
                } else {
                    throw new CryptoGenException("OpenEMR Error : Key in drive is not compatible (ie. can not be decrypted) with key in database - Exiting.");
                }
            }
        }

        // Store key in cache and then return the key
        $this->keyCache[$cacheLabel] = $key;
        $this->logger->debug("CryptoGen: Key collected and cached", [
            'cache_label' => $cacheLabel
        ]);
        return $key;
    }
}
