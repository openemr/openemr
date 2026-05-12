<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Encryption\Storage;

use Doctrine\DBAL\Connection;
use OpenEMR\Encryption\Keys\KeyMaterial;
use OutOfBoundsException;
use UnexpectedValueException;

/**
 * Read key material from the `keys` table via DBAL.
 *
 * Important: this bypasses query audit logging, which is necessary to prevent
 * recursion in the audit logger itself which can encrypt messages.
 */
readonly class PlaintextKeyInDbKeysTable implements KeyStorageInterface
{
    // Note: depending where we land on SQL logging, this may need
    // a ConnectionManager instead of the raw Connection. Or ORM's
    // EntityManager directly.
    public function __construct(
        private Connection $conn,
    ) {
    }

    public function getKey(KeyMaterialId $identifier): KeyMaterial
    {
        $result = $this->conn->createQueryBuilder()
            ->select('value')
            ->from('`keys`')
            ->where('name = :name')
            ->setParameter('name', $identifier->id)
            ->fetchOne();

        if ($result === false) {
            throw new OutOfBoundsException('No key found');
        }

        if (!is_string($result)) {
            throw new UnexpectedValueException('Key found, invalid data format');
        }

        $key = base64_decode($result, strict: true);
        if ($key === false) {
            throw new UnexpectedValueException('Key found, malformed');
        }

        return new KeyMaterial($key);
    }

    public function storeKey(KeyMaterialId $identifier, KeyMaterial $key): void
    {
        $this->conn->insert('`keys`', [
            'name' => $identifier->id,
            'value' => base64_encode($key->key),
        ]);
    }
}
