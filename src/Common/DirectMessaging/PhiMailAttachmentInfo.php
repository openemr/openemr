<?php

/**
 * What the server says about an attachment of a received message before sending it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

final readonly class PhiMailAttachmentInfo
{
    /**
     * @param string $name the sender's file name, or '' when it gave none
     * @param string $mimeType lowercase, without parameters
     */
    public function __construct(
        public string $name,
        public string $mimeType,
        public string $description,
    ) {
    }
}
