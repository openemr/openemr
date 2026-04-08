<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Encryption\Storage;

use Doctrine\DBAL\Connection;
use OpenEMR\Common\Database\{ConnectionManager, ConnectionType};
use OpenEMR\Encryption\Keys\KeyMaterial;

readonly class PlaintextKeyInDbKeysTable implements KeyStorageInterface
{
    private Connection $conn;
    public function __construct(
        ConnectionManager $manager,
    ) {
        // This needs the non-audited connection since the audit tooling can
        // encrypt data which would create a dependency loop.
        $this->conn = $manager->get(ConnectionType::NonAudited);
    }

    public function getKey(KeyMaterialId $identifier): KeyMaterial
    {
        $result = $this->conn->fetchOne(
            'SELECT value FROM `keys` WHERE name = ?',
            [$identifier->id],
        );

        if (!is_string($result)) {
            throw new \Exception('No key found');
        }

        $key = base64_decode($result, strict: true);
        if ($key === false) {
            throw new \Exception('Malformed key');
        }

        return new KeyMaterial($key);
    }

    public function storeKey(KeyMaterialId $identifier, KeyMaterial $key): void
    {
        throw new \BadMethodCallException('Not implemented');
    }
}
