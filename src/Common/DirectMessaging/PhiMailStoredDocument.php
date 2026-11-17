<?php

/**
 * A message part stored as an OpenEMR document.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

final readonly class PhiMailStoredDocument
{
    /**
     * @param ?string $url where the document is stored, or null when the store did not report one
     * @param int $size the stored file's size in bytes
     */
    public function __construct(
        public int $id,
        public ?string $url,
        public int $size,
    ) {
    }

    /**
     * The last path segment of the URL, as shown in the received-message note.
     */
    public function fileName(): string
    {
        return $this->url === null ? '' : basename($this->url);
    }
}
