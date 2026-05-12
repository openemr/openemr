<?php

declare(strict_types=1);

namespace OpenEMR\Services\KeyRotation;

use Doctrine\DBAL\Connection;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use OpenEMR\BC\Crypto\EncryptionConfig;
use OpenEMR\Common\Crypto\CryptoInterface;
use OpenEMR\Services\Storage\{ManagerInterface, Location};

/**
 * @phpstan-type DocumentRow array{
 *   id: int,
 *   type: 'file_url'|'blob'|'web_url',
 *   url: ?string,
 *   thumb_url: ?string,
 *   path_depth: ?int,
 * }
 */
class DocumentKeyRotation
{
    readonly private FilesystemOperator $documents;
    private bool $dryRun = true;

    public function __construct(
        private readonly EncryptionConfig $config,
        readonly private Connection $conn,
        private LoggerInterface $logger,
        readonly private CryptoInterface $crypto,
        ManagerInterface $storageManager,
    ) {
        $this->documents = $storageManager->getStorage(Location::Documents);
    }

    public function setDryRun(bool $dryRun): void
    {
        $this->dryRun = $dryRun;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function rotateAllDocuments(): void
    {
        // This takes a naive approach for paging and resource management: go
        // as far as possible and if it crashes from resource use, well, run
        // the script again.
        //
        // Note: this uses values instead of Document constant directly because
        // the file defining Document (at present) has side-effects
        $data = $this->conn->createQueryBuilder()
            ->select('id', 'type', 'url', 'thumb_url', 'path_depth') // what else?
            ->from('documents')
            ->where('encrypted = :encrypted')
            ->andWhere('storagemethod = :storage_method')
            ->setParameter('encrypted', $this->config->filesystemEncryption ? 0 : 1) // Inverse of current state
            ->setParameter('storage_method', 0) // STORAGE_METHOD_FILESYSTEM
            ->executeQuery()
            ->fetchAllAssociative();

        // TODO: batching, somehow.
        // max = max(id)
        // batchSize = n
        // loop(i; start < n)
        //   setMaxResults(n)
        //   setFirstResult(i)
        //
        // roughly?

        /**
         * @var DocumentRow $row
         */
        foreach ($data as $row) {
            $this->updateDocument($row);
        }

    }

    /**
     * @param DocumentRow $docRow
     */
    private function updateDocument(array $docRow): void
    {
        $id = $docRow['id'];
        $logContext = ['id' => $id];
        $this->logger->debug('Documents: processing {id}', $logContext);
        if ($docRow['url'] === null) {
            $this->logger->debug('Documents: Skip {id}, url is null', $logContext);
            return;
        }
        if ($docRow['path_depth'] === null) {
            $this->logger->error('Documents: Skip {id}, path_depth is null', $logContext);
            return;
        }

        $path = self::determineRelativePath($docRow['url'], $docRow['path_depth']);
        $doc = $this->documents->read($path);

        if ($this->crypto->isFilesystemValueLatest($doc)) {
            $this->logger->debug('Documents: {id} is current', $logContext);
            return;
        }

        $updated = $this->crypto->encryptForFilesystem(
            $this->crypto->decryptFromFilesystem($doc),
        );

        if ($this->dryRun) {
            $this->logger->info('Documents: not changing {id} (dry-run)', $logContext);
            return;
        }

        // In THEORY, doing the following should be safe:
        // - generate a new drive_uuid
        // - generate a new path based off of it
        // - write the $updated contents to the new path (leave existing file in place)
        // - update the db with (url, encrypted, drive_uuid)
        // - should be safe to unlink the old file??
        //
        // But definitely DO NOT just write the update to the current location,
        // otherwise a crash will leave the data in an inconsistent and
        // possibly-unrecoverable state.

        // hash is unchanged, it's always over plaintext

        // TODO: couchdb?? see filter in where clause
    }

    public static function determineRelativePath(string $absolute, int $depth): string
    {
        $parts = explode('/', $absolute);
        // Negative takes the last N instead of first
        // Add one for the filename itself
        $offset = -($depth + 1);
        $relative = array_slice($parts, $offset);
        return implode('/', $relative);
    }
}
