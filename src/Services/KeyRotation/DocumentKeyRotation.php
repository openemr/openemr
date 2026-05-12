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
            ->select('id', 'type', 'url', 'thumb_url') // what else?
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
        print_r($docRow);
        $id = $docRow['id'];
        if ($docRow['url'] === null) {
            $this->logger->debug('Documents: Skip {id}, url is null', ['id' => $id]);
            return;
        }
        // FIXME: this needs path munging
        $doc = $this->documents->read($docRow['url']);
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

        // Write updated document to FS
        // Update DB to reflect curent encryption state
        // TODO: needs atomic or at least safe update

        // hash is unchanged, it's always over plaintext


        // dry-run check
        // write file, update table (this needs a way to de-risk a crash)
        // - encrypted = $this->filesystemEncryption ? 1 : 0
        // - (new identifiers/path)?

        // todo: how to handle couchdb?
        print_r($doc);
    }
}
