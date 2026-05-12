<?php

declare(strict_types=1);

namespace OpenEMR\Services\KeyRotation;

use Doctrine\DBAL\{
    ArrayParameterType,
    Connection,
};

/**
 * Determines the state of what OpenEMR wants various encryption to be in
 */
readonly class AppConfig
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
