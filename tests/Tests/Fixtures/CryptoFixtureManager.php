<?php

/**
 * Manages crypto test fixtures for integration testing.
 *
 * Provides known keys and ciphertext for testing decryption across all
 * supported key versions (v1-v7). This enables regression testing when
 * refactoring encryption code.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;

class CryptoFixtureManager
{
    /**
     * Known plaintext used for all test vectors.
     */
    public const PLAINTEXT = 'Hello, OpenEMR! This is test data for encryption verification.';

    /**
     * Pre-computed ciphertext for each version using drive keys.
     * Generated with fixed IV and known plaintext for deterministic test vectors.
     */
    private const CIPHERTEXT_DRIVE = [
        1 => '001ABEiM0RVZneImaq7zN3u/68NUWRyPGHqfhzGwFpoD6d/ubwX6xpvzYihx0tOSqB9EB/CESXpLv22qDbevwHJt9I8xol/zCGMR6dpF9ceGUg=',
        2 => '00259ZKdJk1xhOLKHMw+08NebJDugYW5NeLZ7Rsdeo6gqkAESIzRFVmd4iZqrvM3e7/FpckoPHpo1o1JFR8sY9Se8LdHK9zz9IHv3zVM7o+N7jrmIqZLoglijwA34ZsSNl1STqGUAoyOCB86qold5XYiw==',
        3 => '003QmcPFkP+C1toVzMhuQf0Iyc4zy02xtSvEUjPp3c8NdIAESIzRFVmd4iZqrvM3e7/BWY8zGKfMZ1lCfL36zK+f8I8BXxbYVRZNHq98txwk/sswAJe9EUrV3pArVVwk8r9ljehbgdTWEa1ozt1ZA3KVQ==',
        4 => '004C2GVexrZuFyfM34zXrZVFmBHTLLxFAEBZsqFqw3SZZBVvBp2zxjHIMK672PwuQZIABEiM0RVZneImaq7zN3u/5t6WGR2fOaCWae8PXVGh6zJSzJ1pRCF9j51VKfjsCQD/oxx2diRSt9dOOWlBFup9+5rj6N5rNiwmy+Ur1Fty5s=',
        5 => '005XkuvujmthLob0C8lJl94XtZ+xpjbxiZTyAZ/d3rR1jvg1Z2I7HAboCfZRxI6QBEBABEiM0RVZneImaq7zN3u/y3BjIup4P3pU3A0u7Tz4RHnvfjfHCfwdQ0onSarFcGjvFtByHO2a7i7Qn6fe1WLnxiWbpUPKW1Sl+ne01Boh/E=',
        6 => '006uJvPQwpYUQI8wslRyBEpsDUcDaWuYCVWXh/YJUOe6/FNtk1TerDpMm7Z7K5MiWkMABEiM0RVZneImaq7zN3u/4JsZXeM6N7ecplEvzW0Emin0b5acdJrVl8G1UvMPSoix4+zTK0Xs5N1OE45/tt6lv02/aJhGnFFN8N612QX010=',
        7 => '007GQC+AGxRwHGnU+an+FB0tXnlYmnTIvzfPTn63c6HFxhq3eXTlFMmkLFHpeSWf8/jABEiM0RVZneImaq7zN3u/4BGRgoaqWYzMcvRQTU6h6rMksI3tRL7+fD4PrLNQLYsR45Bj5t0G5SyILUcyTPdiyHe2BOWnXFe2uMFq6Rp3tg=',
    ];

    /**
     * Pre-computed ciphertext for versions 4-7 using database keys.
     * Used to test KeySource::Database path.
     */
    private const CIPHERTEXT_DATABASE = [
        4 => '004guaUD+mZ3OySarB5w0K+iT2X+FxvfN0bXFfiLUxAvD4QFWkubbm0mM0vapeRr7e1ABEiM0RVZneImaq7zN3u/+K9PwBRXIvyvv7Wz2Yrixr/nGAoc14/OOMQG8MGtsZT2YqvYrV7drdN/eeJBguo0SuGMYwrvKROuheqXKqA+q0=',
        5 => '005Wxc+dyNFoOtwqvHi85qZLcQ9+HlbjSCnW86pYxiMfdYiF+uD2kts6DN/cy6cxAKRABEiM0RVZneImaq7zN3u/3itNWsy9zLOF7kPw+KXrNe6wx5gu6fqCk0cRamSzM6TLPcwHQpIQZXlXqmS18cfl/6FM8b5ClHjIG3zBNgWZ84=',
        6 => '006zD0IRAmlRi7s1JdgTN8fGcR0HRTBUKNxTMO0W0/0p2LS6oQRmpDBzcYaKgv+jMGbABEiM0RVZneImaq7zN3u/0NgrVjIFqvhzlmMmUISKE0g1IjrqJP2hJtiOy5riWLiVEtfnccchaACwt2toce7UWfweTzzpXXJPQxsVdWvv7U=',
        7 => '007xczi9kcAEsZYlYWOkE/8seuAdrLT5RIIds0zN+/ihOwJbFD3ALRmurTugQySL65hABEiM0RVZneImaq7zN3u/2U/6pkxtRrSpxsEY1OCjXz58+Lm0zGUKDQ8tKR6jsX7wnA3xNvFfG8jyW37FHQLv18SAMOQg56/Ry+JKHj+yAQ=',
    ];

    /**
     * Pre-computed encrypted key file contents for v5-7.
     * Drive keys encrypted with the corresponding version's database keys.
     * Fixed IV for key files: ffeeddccbbaa99887766554433221100
     */
    private const ENCRYPTED_KEY_FILES = [
        'fivea' => '005if27Rpj3/2wBaYNqCes/EiANOHPYTdztQFKhiMHU5XoLYFMdDdPmio8og6xmSY53/+7dzLuqmYh3ZlVEMyIRAHn8wuHrmnUHWYbqqjIP5EOHZ6UXiiqFw3u+WOeaXb6982XogaJPX+zu6egw5rt8zQ==',
        'fiveb' => '005+s8bt9+owSYDtF9e1IedzyFEQSnBUD81o+hfaKmbTo78lIJnLgllizI0KpvcX4bZ/+7dzLuqmYh3ZlVEMyIRAC12QdqRti6M7FX3eY+Gbq0GAe56nIYgif4fxYQlcFBeVXc2c4/EENNFv+ifq224XQ==',
        'sixa' => '0060UF4AP18OIMoiqBxFPv6319HumHmpuJdtKNNuxIQdWUu7id2cg1Vitvp5VsNES87/+7dzLuqmYh3ZlVEMyIRABbr1ENsV/AlrBntkpZTYHK3up3DvzEI5bxom+1P0WID7a7LJr3rZV3yeXaRBt8OFQ==',
        'sixb' => '006JZ/jfS7dnN5OIPebiESCxxVlwXqBJc4mMfzfATCpB6/AutncLMhwEM8zdcr/SGFN/+7dzLuqmYh3ZlVEMyIRABk3EXQ02eaW6qeDEgytrkc79tlVEnrcRgFiOuWw9c6xxgpontPjh7HSNhiWLkqMqg==',
        'sevena' => '007u4WHAuTlKsQosASvqxkJ0gpfACspNfleVB5gJ7SR/lVSM80OIuH2og1BZlZoDh8s/+7dzLuqmYh3ZlVEMyIRAPpwYzOQzwCR7CXpyBdiai2qsGGpYnatg/oLlj+FF+qYk6fO5cFnOKkLGi1X6Vvp9w==',
        'sevenb' => '007tTFfZl68U1oV7gv+BQNWG+ibYcOQ/0WScDYseSoXRrJ0oEYFY0wE/o8igTDyThfG/+7dzLuqmYh3ZlVEMyIRAAS+8r09tJHDngRlTcKLDsQZse5YbkTjVkjVph10+Q52YZrdJrf06DiuZmA36ZXxOw==',
    ];

    /**
     * Fixed 32-byte keys for each version/suffix combination.
     * Keys are derived from version name for reproducibility.
     * Format: 'versionsuffix' => hex-encoded 32-byte key
     */
    private const TEST_KEYS = [
        // Version 1: single key, no HMAC
        'one' => 'v1_key__________________________', // 32 bytes

        // Version 2: encryption + HMAC
        'twoa' => 'v2_encryption_key_______________',
        'twob' => 'v2_hmac_key_____________________',

        // Version 3: same format as v2
        'threea' => 'v3_encryption_key_______________',
        'threeb' => 'v3_hmac_key_____________________',

        // Version 4: same format as v5-7 but plaintext storage
        'foura' => 'v4_encryption_key_______________',
        'fourb' => 'v4_hmac_key_____________________',

        // Version 5
        'fivea' => 'v5_encryption_key_______________',
        'fiveb' => 'v5_hmac_key_____________________',

        // Version 6
        'sixa' => 'v6_encryption_key_______________',
        'sixb' => 'v6_hmac_key_____________________',

        // Version 7 (current)
        'sevena' => 'v7_encryption_key_______________',
        'sevenb' => 'v7_hmac_key_____________________',
    ];

    /**
     * Database keys stored in the `keys` table.
     *
     * For v4: used when KeySource::Database is specified.
     * For v5+: used to encrypt drive keys, AND when KeySource::Database is specified.
     *
     * Each version needs its own set because drive keys created when that
     * version was current would have been encrypted with that version's db keys.
     */
    private const DB_KEYS = [
        // v4: only used for KeySource::Database (drive keys are plaintext)
        'foura' => 'db_v4_encryption_key____________',
        'fourb' => 'db_v4_hmac_key__________________',
        // v5+: used for both drive key encryption and KeySource::Database
        'fivea' => 'db_v5_encryption_key____________',
        'fiveb' => 'db_v5_hmac_key__________________',
        'sixa' => 'db_v6_encryption_key____________',
        'sixb' => 'db_v6_hmac_key__________________',
        'sevena' => 'db_v7_encryption_key____________',
        'sevenb' => 'db_v7_hmac_key__________________',
    ];

    private string $siteDir;

    public function __construct(?string $siteDir = null)
    {
        if ($siteDir === null) {
            $siteDir = $GLOBALS['OE_SITE_DIR'];
            assert(is_string($siteDir));
        }
        $this->siteDir = $siteDir;
    }

    /**
     * Install all crypto fixtures (database keys and key files).
     */
    public function install(): void
    {
        $this->installDatabaseKeys();
        $this->installKeyFiles();
    }

    /**
     * Remove all crypto fixtures.
     */
    public function remove(): void
    {
        $this->removeDatabaseKeys();
        $this->removeKeyFiles();
    }

    /**
     * Get the known plaintext used in test vectors.
     */
    public function getPlaintext(): string
    {
        return self::PLAINTEXT;
    }

    /**
     * Get a test key by identifier.
     */
    public function getTestKey(string $identifier): string
    {
        if (!isset(self::TEST_KEYS[$identifier])) {
            throw new \InvalidArgumentException("Unknown test key: $identifier");
        }
        return self::TEST_KEYS[$identifier];
    }

    /**
     * Get known ciphertext for a specific version using drive keys.
     *
     * @param int $version Key version (1-7)
     * @return string The version-prefixed, base64-encoded ciphertext
     */
    public function getCiphertext(int $version): string
    {
        if (!isset(self::CIPHERTEXT_DRIVE[$version])) {
            throw new \InvalidArgumentException("Unsupported version: $version");
        }
        return self::CIPHERTEXT_DRIVE[$version];
    }

    /**
     * Get known ciphertext for a specific version using database keys.
     * Only versions 4-7 support database key source.
     *
     * @param int $version Key version (4-7)
     * @return string The version-prefixed, base64-encoded ciphertext
     */
    public function getCiphertextForDatabaseKeys(int $version): string
    {
        if (!isset(self::CIPHERTEXT_DATABASE[$version])) {
            throw new \InvalidArgumentException("Database keys not supported for version: $version");
        }
        return self::CIPHERTEXT_DATABASE[$version];
    }

    /**
     * Install database keys into the `keys` table.
     *
     * @throws \RuntimeException if any keys already exist (to prevent data loss)
     */
    private function installDatabaseKeys(): void
    {
        // First, check if ANY keys exist that we would overwrite
        $existingKeys = [];
        foreach (array_keys(self::DB_KEYS) as $name) {
            $existing = QueryUtils::querySingleRow("SELECT 1 FROM `keys` WHERE `name` = ?", [$name], log: false);
            if ($existing !== false) {
                $existingKeys[] = $name;
            }
        }

        if ($existingKeys !== []) {
            throw new \RuntimeException(sprintf(
                "Refusing to overwrite existing encryption keys: %s. " .
                "Running these tests would destroy real keys and make encrypted data unreadable. " .
                "Run tests in a clean database (CI does this automatically).",
                implode(', ', $existingKeys)
            ));
        }

        // Safe to install - no existing keys
        foreach (self::DB_KEYS as $name => $key) {
            $encodedKey = base64_encode($key);
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO `keys` (`name`, `value`) VALUES (?, ?)",
                [$name, $encodedKey],
                noLog: true
            );
        }
    }

    /**
     * Remove database keys from the `keys` table.
     */
    private function removeDatabaseKeys(): void
    {
        foreach (array_keys(self::DB_KEYS) as $name) {
            QueryUtils::sqlStatementThrowException("DELETE FROM `keys` WHERE `name` = ?", [$name], noLog: true);
        }
    }

    /**
     * Install key files on disk.
     *
     * @throws \RuntimeException if any key files already exist (to prevent data loss)
     */
    private function installKeyFiles(): void
    {
        $keyDir = $this->siteDir . '/documents/logs_and_misc/methods';
        if (!is_dir($keyDir)) {
            mkdir($keyDir, 0755, true);
        }

        // First, check if ANY key files exist that we would overwrite
        $existingFiles = [];
        foreach (array_keys(self::TEST_KEYS) as $keyName) {
            $path = $keyDir . '/' . $keyName;
            if (file_exists($path)) {
                $existingFiles[] = $keyName;
            }
        }

        if ($existingFiles !== []) {
            throw new \RuntimeException(sprintf(
                "Refusing to overwrite existing key files: %s. " .
                "Running these tests would destroy real keys and make encrypted data unreadable. " .
                "Run tests in a clean database (CI does this automatically).",
                implode(', ', $existingFiles)
            ));
        }

        // Safe to install - no existing key files

        // v1-v4: plaintext keys (base64 encoded)
        $plaintextVersions = ['one', 'twoa', 'twob', 'threea', 'threeb', 'foura', 'fourb'];
        foreach ($plaintextVersions as $keyName) {
            $path = $keyDir . '/' . $keyName;
            file_put_contents($path, base64_encode(self::TEST_KEYS[$keyName]));
        }

        // v5-v7: encrypted keys (pre-computed with fixed IV)
        foreach (self::ENCRYPTED_KEY_FILES as $keyName => $encryptedKey) {
            $path = $keyDir . '/' . $keyName;
            file_put_contents($path, $encryptedKey);
        }
    }

    /**
     * Remove key files from disk.
     */
    private function removeKeyFiles(): void
    {
        $keyDir = $this->siteDir . '/documents/logs_and_misc/methods';
        $allKeys = array_keys(self::TEST_KEYS);

        foreach ($allKeys as $keyName) {
            $path = $keyDir . '/' . $keyName;
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
}
