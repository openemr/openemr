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

declare(strict_types=1);

namespace OpenEMR\Common\Crypto;

// Help phpstan find sqlQueryNoLog
require_once __DIR__ . '/../../../library/sql.inc.php';

use Exception;
use function sqlQueryNoLog;
use function sqlStatementNoLog;
use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Common\Crypto\EncryptionStrategyInterface;
use OpenEMR\Common\Crypto\EncryptionStrategySelector;
use OpenEMR\Common\Crypto\CryptoGenStrategy;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Logging\SystemLogger;

class CryptoGen
{
    private EncryptionStrategyInterface $encryptionStrategy;

    private SystemLogger $systemLogger;

    /**
     * Check if CryptoGen can be safely instantiated.
     *
     * @return bool True if database connection is available and CryptoGen can be used
     */
    public static function ready(): bool
    {
        return isset($GLOBALS['adodb']['db']) && !empty($GLOBALS['adodb']['db']);
    }

    /**
     * Get the encryption strategy name from the keys table.
     *
     * @return string|null The strategy name, or null if not found
     */
    public static function getEncryptionStrategyName(): ?string
    {
        if (!self::ready()) {
            return null;
        }

        $result = sqlQueryNoLog("SELECT value FROM keys WHERE name = 'encryption_strategy_name'");
        return $result['value'] ?? null;
    }

    /**
     * Set the encryption strategy name in the keys table.
     *
     * @param string $strategyName The strategy name to store
     * @return bool True on success, false on failure
     */
    public static function setEncryptionStrategyName(string $strategyName): bool
    {
        if (!self::ready()) {
            return false;
        }

        try {
            // Use INSERT ... ON DUPLICATE KEY UPDATE for MySQL compatibility
            sqlStatementNoLog(
                "INSERT INTO keys (name, value) VALUES ('encryption_strategy_name', ?) ON DUPLICATE KEY UPDATE value = VALUES(value)",
                [$strategyName]
            );
            return true;
        } catch (Exception $e) {
            error_log("CryptoGen: Failed to set encryption strategy name: " . $e->getMessage());
            return false;
        }
    }

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
        $this->systemLogger = new SystemLogger();

        $this->systemLogger->debug("CryptoGen: Constructor called");

        // Load strategy from database
        $this->encryptionStrategy = $this->loadStrategyFromDatabase();

        $strategyClass = get_class($this->encryptionStrategy);
        $this->systemLogger->debug(
            "CryptoGen: Loaded encryption strategy",
            ['strategy_class' => $strategyClass]
        );
    }


    /**
     * Standard function to encrypt
     *
     * @param  string|null $value          This is the data to encrypt.
     * @param  string|null $customPassword If provide a password, then will derive keys from this.(and will not use the standard keys)
     * @param  string      $keySource      This is the source of the standard keys. Options are 'drive' and 'database'
     * @return string|null Encrypted data or null if input is null
     */
    public function encryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive'): ?string
    {
        $this->systemLogger->debug(
            "CryptoGen: encryptStandard called",
            [
                'has_custom_password' => !is_null($customPassword),
                'key_source' => $keySource,
                'strategy' => get_class($this->encryptionStrategy)
            ]
        );

        $result = $this->encryptionStrategy->encryptStandard($value, $customPassword, $keySource);

        $this->systemLogger->debug(
            "CryptoGen: encryptStandard completed",
            ['success' => $result !== null]
        );

        return $result;
    }

    /**
     * Standard function to decrypt
     *
     * @param  string|null $value          This is the data to decrypt.
     * @param  string|null $customPassword If provide a password, then will derive keys from this.(and will not use the standard keys)
     * @param  string      $keySource      This is the source of the standard keys. Options are 'drive' and 'database'
     * @param  int|null    $minimumVersion This is the minimum encryption version supported (useful if accepting encrypted data
     *                                     from outside OpenEMR to ensure bad actor is not trying to use an older version).
     * @return false|string|null
     */
    public function decryptStandard(?string $value, ?string $customPassword = null, string $keySource = 'drive', ?int $minimumVersion = null): false|string|null
    {
        $this->systemLogger->debug(
            "CryptoGen: decryptStandard called",
            [
                'has_custom_password' => !is_null($customPassword),
                'key_source' => $keySource,
                'minimum_version' => $minimumVersion,
                'strategy' => get_class($this->encryptionStrategy)
            ]
        );

        $result = $this->encryptionStrategy->decryptStandard($value, $customPassword, $keySource, $minimumVersion);

        $this->systemLogger->debug(
            "CryptoGen: decryptStandard completed",
            ['success' => $result !== false]
        );

        return $result;
    }

    /**
     * Check if a crypt block is valid to use for the standard method
     * (basically checks if correct values are used)
     */
    public function cryptCheckStandard(?string $value): bool
    {
        $this->systemLogger->debug(
            "CryptoGen: cryptCheckStandard called",
            ['strategy' => get_class($this->encryptionStrategy)]
        );

        $result = $this->encryptionStrategy->cryptCheckStandard($value);

        $this->systemLogger->debug(
            "CryptoGen: cryptCheckStandard completed",
            ['result' => $result]
        );

        return $result;
    }


    /**
     * Load encryption strategy from database keys table.
     *
     * Note: This method is only called once during construction and the result
     * is stored in $this->encryptionStrategy.
     *
     * @return EncryptionStrategyInterface Loaded strategy or default CryptoGenStrategy
     * @throws CryptoGenException If strategy loading fails
     */
    private function loadStrategyFromDatabase(): EncryptionStrategyInterface
    {
        // Check if we have a database connection
        if (!self::ready()) {
            throw new CryptoGenException("Fatal error: No database connection available for loading encryption strategy");
        }

        $encryptionStrategySelector = new EncryptionStrategySelector();

        // Get the encryption strategy name from keys table
        $strategyName = self::getEncryptionStrategyName();

        if (empty($strategyName)) {
            $this->systemLogger->debug("CryptoGen: No encryption strategy name stored in database, setting default");
            // Set the default strategy in the database
            $defaultStrategy = $encryptionStrategySelector->getDefaultStrategy();
            $defaultStrategyId = $defaultStrategy->getId();
            self::setEncryptionStrategyName($defaultStrategyId);
            $this->systemLogger->debug(
                sprintf("CryptoGen: Set default encryption strategy in database to '%s'", $defaultStrategyId),
                ['strategy_name' => $defaultStrategyId]
            );
            return $defaultStrategy;
        }

        if (!is_string($strategyName)) {
            throw new CryptoGenException("Fatal error: Encryption strategy name from database is not a string");
        }

        $this->systemLogger->debug(
            "CryptoGen: Found strategy name in database",
            ['strategy_name' => $strategyName]
        );

        // Use the strategy selector to get strategy by name
        $strategy = $encryptionStrategySelector->getStrategyByName($strategyName);

        if ($strategy instanceof \OpenEMR\Common\Crypto\EncryptionStrategyInterface) {
            return $strategy;
        }

        throw new CryptoGenException(sprintf("Fatal error: Encryption strategy '%s' configured in database but not found", $strategyName));
    }
}
