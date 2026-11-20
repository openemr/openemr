<?php

/**
 * Test double: a note writer that records the notes and document links it was asked for.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\DirectMessaging;

use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\DirectMessaging\PhiMailNoteWriter;

final class RecordingNoteWriter implements PhiMailNoteWriter
{
    /** @var list<array{patientId: int, text: string, assignedTo: string}> */
    public array $notes = [];

    /** @var list<array{int, int}> document and note ID of each link */
    public array $links = [];

    /** Thrown in place of adding a note, when set */
    public ?SqlQueryException $failure = null;

    private int $nextId = 501;

    public function addNote(int $patientId, string $text, string $assignedTo): int
    {
        if ($this->failure instanceof SqlQueryException) {
            throw $this->failure;
        }
        $this->notes[] = ['patientId' => $patientId, 'text' => $text, 'assignedTo' => $assignedTo];
        return $this->nextId++;
    }

    public function linkDocument(int $documentId, int $noteId): void
    {
        $this->links[] = [$documentId, $noteId];
    }
}
