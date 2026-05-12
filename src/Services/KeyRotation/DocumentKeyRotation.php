<?php

declare(strict_types=1);

namespace OpenEMR\Services\KeyRotation;

use Doctrine\DBAL\Connection;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
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
        private readonly AppConfig $config,
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
        // storagemethod=Document::STORAGE_METHOD_FILESYSTEM
            ->setParameter('encrypted', $this->config->filesystemEncryption ? 0 : 1) // Inverse of current state
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
        if ($docRow['url'] === null) {
            $this->logger->debug('Documents: Skip {id}, url is null', ['id' => $id]);
            return;
        }
        if ($docRow['path_depth'] === null) {
            $this->logger->error('Documents: Skip {id}, path_depth is null', ['id' => $id]);
            return;
        }
        // FIXME: this needs path munging
        $path = self::determineRelativePath($docRow['url'], $docRow['path_depth']);
        $doc = $this->documents->read($path);

        if ($this->crypto->isFilesystemValueLatest($doc)) {
            $this->logger->debug('Documents: {id} is current', ['id' => $id]);
            return;
        }

        $updated = $this->crypto->encryptForFilesystem(
            $this->crypto->decryptFromFilesystem($doc),
        );

        if ($this->dryRun) {
            $this->logger->info('Documents: not changing {id} (dry-run)', ['id' => $id]);
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

        // TODO: couchdb??
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
