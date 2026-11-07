<?php

/**
 * Delivers received Direct messages and send failures as OpenEMR messages (pnotes).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

use OpenEMR\Common\Database\SqlQueryException;

interface PhiMailNoteWriter
{
    /**
     * @param int $patientId the patient the note is about, or 0 for none
     * @param string $assignedTo username of the OpenEMR user the note is assigned to
     * @return int the new note's ID
     * @throws SqlQueryException
     */
    public function addNote(int $patientId, string $text, string $assignedTo): int;

    /**
     * @throws SqlQueryException
     */
    public function linkDocument(int $documentId, int $noteId): void;
}
