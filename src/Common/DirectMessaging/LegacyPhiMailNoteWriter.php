<?php

/**
 * Writes phiMail notes through library/pnotes.inc.php addPnote(), since no note service exists.
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

use OpenEMR\Services\GpRelationService;

final readonly class LegacyPhiMailNoteWriter implements PhiMailNoteWriter
{
    private const AUTHOR = 'phimail-service';
    private const TITLE = 'Unassigned';
    private const STATUS = 'New';
    // gprelations type codes
    private const RELATION_DOCUMENT = 1;
    private const RELATION_NOTE = 6;

    public function addNote(int $patientId, string $text, string $assignedTo): int
    {
        return addPnote($patientId, $text, 0, 1, self::TITLE, $assignedTo, '', self::STATUS, self::AUTHOR);
    }

    public function linkDocument(int $documentId, int $noteId): void
    {
        GpRelationService::setGpRelation(self::RELATION_DOCUMENT, $documentId, self::RELATION_NOTE, $noteId);
    }
}
