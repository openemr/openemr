<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Encryption\Keys;

use OpenEMR\Encryption\Cipher\CipherInterface;
use OpenEMR\Encryption\KeyId;
use OutOfBoundsException;

use function array_key_exists;

/**
 * An eager-loaded keychain, which holds all of the keys/ciphers for active use.
 *
 * This will use the most-recently-registered KeyId as the current one.
 */
class Keychain implements KeychainInterface
{
    // This is intentionally not initialized in a constructor; trying to get
    // the current key id before anything is registered is an error.
    private KeyId $currentKeyId;

    /**
     * @var array<string, CipherInterface>
     */
    private array $mappings = [];

    public function getCipher(KeyId $keyId): CipherInterface
    {
        if ($this->hasKey($keyId)) {
            return $this->mappings[$keyId->id];
        }
        throw new OutOfBoundsException('Key id not registered');
    }

    public function getCurrentKeyId(): KeyId
    {
        return $this->currentKeyId;
    }

    public function hasKey(KeyId $keyId): bool
    {
        return array_key_exists($keyId->id, $this->mappings);
    }

    public function registerCipher(
        KeyId $id,
        CipherInterface $cipher,
    ): void {
        $this->mappings[$id->id] = $cipher;
        $this->currentKeyId = $id;
    }
}
