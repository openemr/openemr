<?php

/**
 * CryptoGen class - Facade for encryption/decryption operations.
 *
 * This class serves as a facade that loads the encryption strategy selected during
 * installation from the database and delegates all encryption/decryption operations
 * to that strategy.
 *
 * The encryption strategy is selected once during installation and stored in the
 * database to ensure data accessibility even if modules are removed.
 *
 * For details on the encryption implementation, see the individual strategy classes:
 * - CryptoGenStrategy: Default AES-256-CBC with HMAC-SHA384
 * - NullEncryptionStrategy: No encryption (identity function)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ensoftek, Inc
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2015 Ensoftek, Inc
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Crypto;

// Help phpstan find sqlQueryNoLog
require_once __DIR__ . '/../../../library/sql.inc.php';

use Exception;
use function sqlQueryNoLog;
use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Common\Crypto\EncryptionStrategyInterface;
use OpenEMR\Common\Crypto\EncryptionStrategySelector;
use OpenEMR\Common\Crypto\CryptoGenStrategy;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLogger;

class CryptoGen
{
    private EncryptionStrategyInterface $encryptionStrategy;
    private SystemLogger $logger;

    /**
     * Constructor - Initialize CryptoGen with database-stored encryption strategy.
     *
     * Loads the encryption strategy from the database global setting.
     * Falls back to default CryptoGenStrategy if no strategy is stored.
     *
     * @throws CryptoGenException If strategy loading fails
     */
    public function __construct()
    {
        $this->logger = new SystemLogger();

        $this->logger->debug("CryptoGen: Constructor called");

        // Load strategy from database
        $this->encryptionStrategy = $this->loadStrategyFromDatabase();

        $strategyClass = get_class($this->encryptionStrategy);
        $this->logger->debug("CryptoGen: Loaded encryption strategy", [
            'strategy_class' => $strategyClass
        ]);

        EventAuditLogger::instance()->newEvent(
            'crypto.strategy.loaded',
            $_SESSION['authUser'] ?? 'system',
            $_SESSION['authProvider'] ?? 'system',
            1,
            "Encryption strategy loaded from database: {$strategyClass}"
        );
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


    /**
     * Load encryption strategy from database global setting.
     *
     * Note: This method is only called once during construction and the result
     * is stored in $this->encryptionStrategy.
     *
     * @return EncryptionStrategyInterface Loaded strategy or default CryptoGenStrategy
     * @throws CryptoGenException If strategy loading fails
     */
    private function loadStrategyFromDatabase(): EncryptionStrategyInterface
    {
        try {
            // Check if we have a database connection and the globals table exists
            if (!isset($GLOBALS['dbase']) || empty($GLOBALS['dbase'])) {
                $this->logger->debug("CryptoGen: No database connection, using default strategy");
                return new CryptoGenStrategy();
            }

            // Query for the encryption strategy global setting
            $result = sqlQueryNoLog("SELECT gl_value FROM globals WHERE gl_name = 'encryption_strategy_serialized' AND gl_index = 0");

            if (empty($result['gl_value'])) {
                $this->logger->debug("CryptoGen: No encryption strategy stored in database, using default");
                return new CryptoGenStrategy();
            }

            $serializedStrategy = $result['gl_value'];
            $this->logger->debug("CryptoGen: Found serialized strategy in database");

            // Use the strategy selector to deserialize
            $selector = new EncryptionStrategySelector();
            $strategy = $selector->deserializeStrategy($serializedStrategy);

            if ($strategy === null) {
                $this->logger->error("CryptoGen: Failed to deserialize strategy, using default");
                return new CryptoGenStrategy();
            }

            return $strategy;
        } catch (Exception $e) {
            $this->logger->error("CryptoGen: Error loading strategy from database", [
                'error' => $e->getMessage()
            ]);

            // Fall back to default strategy
            return new CryptoGenStrategy();
        }
    }
}
