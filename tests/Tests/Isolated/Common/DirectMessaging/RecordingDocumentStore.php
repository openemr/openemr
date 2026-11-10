<?php

/**
 * Test double: a document store that records what it was asked to store.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\DirectMessaging;

use OpenEMR\Common\DirectMessaging\PhiMailDocumentException;
use OpenEMR\Common\DirectMessaging\PhiMailDocumentStore;
use OpenEMR\Common\DirectMessaging\PhiMailStoredDocument;
use Symfony\Component\Filesystem\Filesystem;

final class RecordingDocumentStore implements PhiMailDocumentStore
{
    /** @var list<array{name: string, mimeType: string, contents: string, ownerId: int, patientId: ?int}> */
    public array $stored = [];

    /** Thrown in place of storing, when set */
    public ?PhiMailDocumentException $failure = null;

    /** Return null in place of a document, as addNewDocument() does when the upload fails */
    public bool $refuse = false;

    /** Store without reporting a URL */
    public bool $withoutUrl = false;

    private int $nextId = 101;

    /**
     * Stores a received document, in the patient's chart when one is given.
     */
    public function store(string $name, string $mimeType, string $path, int $ownerId, ?int $patientId = null): ?PhiMailStoredDocument
    {
        if ($this->failure instanceof PhiMailDocumentException) {
            throw $this->failure;
        }
        if ($this->refuse) {
            return null;
        }
        $contents = (new Filesystem())->readFile($path);
        $this->stored[] = ['name' => $name, 'mimeType' => $mimeType, 'contents' => $contents, 'ownerId' => $ownerId, 'patientId' => $patientId];
        $id = $this->nextId++;
        return new PhiMailStoredDocument($id, $this->withoutUrl ? null : 'file:///documents/direct/file-' . $id, strlen($contents));
    }
}
