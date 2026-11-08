<?php

/**
 * Contract for fax vendor clients that expose the shared document-disposal
 * endpoint. Implemented via FaxDocumentDisposalTrait so every fax client
 * disposes documents through the same authorised, path-confined routine.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\FaxSMS\Contracts;

interface FaxDocumentDisposalInterface
{
    /**
     * Stage (action=setup) or stream (action=download) a fax document, after
     * authorising the caller and confining the request-supplied path to the
     * module's temporary base directory.
     *
     * @return string JSON status, or streams the file and exits (download)
     */
    public function disposeDocument(): string;
}
