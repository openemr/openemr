<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\BC\Crypto;

use Doctrine\DBAL\{
    ArrayParameterType,
    Connection,
};

/**
 * Determines the state of what OpenEMR wants various encryption to be in.
 *
 * While this data is available through OEGlobalsBag, the tooling that wants to
 * leverage this is avoiding coupling to the legacy bootstrapping system. This
 * lazy-loads just the config that's needed using DBAL and works totally
 * independently.
 */
readonly class EncryptionConfig
{
    public bool $databaseEncryption;
    public bool $filesystemEncryption;

    public function __construct(
        Connection $conn,
    ) {
        $configs = $conn->createQueryBuilder()
            ->select('gl_name', 'gl_value')
            ->from('globals')
            ->where('gl_name IN (:keys)')
            ->setParameter('keys', ['drive_encryption', 'database_encryption'], ArrayParameterType::STRING)
            ->executeQuery()
            ->fetchAllKeyValue();

        // Note: this replicates the logic from library/globals.php :/
        $this->databaseEncryption = ($configs['database_encryption'] ?? '1') === '1';
        $this->filesystemEncryption = ($configs['drive_encryption'] ?? '1') === '1';
    }
}
