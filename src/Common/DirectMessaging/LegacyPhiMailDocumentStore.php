<?php

/**
 * Stores received Direct message parts through library/documents.php addNewDocument().
 *
 * Copyright (C) 2013, 2021 EMR Direct <https://www.emrdirect.com/>
 * Copyright (C) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  EMR Direct <https://www.emrdirect.com/>
 * @author  Michael A. Smith <michael@opencoreemr.com>
 * @link    https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

use OpenEMR\Events\Core\Sanitize\IsAcceptedFileFilterEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class LegacyPhiMailDocumentStore implements PhiMailDocumentStore
{
    // Received documents are filed under this directory instead of a patient's.
    private const DIRECTORY = 'direct';
    private const CATEGORY_ID = 1;
    // UPLOAD_ERR_OK
    private const UPLOAD_OK = '0';

    /** @var \Closure(string, string, string, int, int, ?int): mixed */
    private \Closure $addNewDocument;

    // The same closure must be passed to removeListener() as to addListener().
    private \Closure $allowAnyFileTypeListener;

    /**
     * @param ?EventDispatcherInterface $dispatcher where the file-type check runs, or null when there is no kernel
     * @param ?\Closure(string, string, string, int, int, ?int): mixed $addNewDocument stands in for addNewDocument() in tests;
     *     takes the name, MIME type, path, size, and owner
     */
    public function __construct(
        private ?EventDispatcherInterface $dispatcher,
        ?\Closure $addNewDocument = null,
    ) {
        $this->addNewDocument = $addNewDocument ?? self::addNewDocument(...);
        $this->allowAnyFileTypeListener = $this->allowAnyFileType(...);
    }

    /**
     * Stores a received document, in the patient's chart when one is given.
     */
    public function store(string $name, string $mimeType, string $path, int $ownerId, ?int $patientId = null): ?PhiMailStoredDocument
    {
        // Allow every file type while this document is stored, then stop.
        $this->dispatcher?->addListener(IsAcceptedFileFilterEvent::EVENT_FILTER_IS_ACCEPTED_FILE, $this->allowAnyFileTypeListener);
        try {
            $size = (new \SplFileInfo($path))->getSize();
            $result = ($this->addNewDocument)($name, $mimeType, $path, $size, $ownerId, $patientId);
        } catch (\RuntimeException $e) {
            throw new PhiMailDocumentException('Failed storing a Direct message document', 0, $e);
        } finally {
            $this->dispatcher?->removeListener(IsAcceptedFileFilterEvent::EVENT_FILTER_IS_ACCEPTED_FILE, $this->allowAnyFileTypeListener);
        }

        if (!is_array($result)) {
            return null;
        }
        $id = $result['doc_id'] ?? null;
        if (is_string($id) && ctype_digit($id)) {
            $id = (int) $id;
        }
        if (!is_int($id)) {
            return null;
        }
        $url = $result['url'] ?? null;
        return new PhiMailStoredDocument($id, is_string($url) ? $url : null, $size);
    }

    /**
     * Accept the file whatever type the upload check detects. A Direct message
     * declares its own MIME type, and the local detection can disagree with it
     * (application/xml against text/xml), so the check is skipped for these
     * documents only.
     */
    public function allowAnyFileType(IsAcceptedFileFilterEvent $event): IsAcceptedFileFilterEvent
    {
        return $event->setAllowedFile(true);
    }

    /**
     * Stores the file through library/documents.php addNewDocument().
     */
    private static function addNewDocument(string $name, string $mimeType, string $path, int $size, int $ownerId, ?int $patientId): mixed
    {
        require_once dirname(__DIR__, 3) . '/library/documents.php';
        // addNewDocument() fills in an upload's $_FILES entry, so the error and size are strings.
        // A patient's id files it in their chart; the directory name files it where no patient has it.
        return addNewDocument($name, $mimeType, $path, self::UPLOAD_OK, (string) $size, $ownerId, $patientId === null ? self::DIRECTORY : (string) $patientId, self::CATEGORY_ID, '', 1, true);
    }
}
