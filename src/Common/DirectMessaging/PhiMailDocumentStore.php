<?php

/**
 * Stores a downloaded message part as an OpenEMR document.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

interface PhiMailDocumentStore
{
    /**
     * @param string $path the downloaded file; the caller removes it afterward
     * @param int $ownerId the OpenEMR user recorded as the document's owner
     * @param ?int $patientId the patient whose chart it's filed in, or null for the Direct folder no
     *     patient has, where a message nobody can match goes
     * @return ?PhiMailStoredDocument null when the document was not stored
     * @throws PhiMailDocumentException
     */
    public function store(string $name, string $mimeType, string $path, int $ownerId, ?int $patientId = null): ?PhiMailStoredDocument;
}
