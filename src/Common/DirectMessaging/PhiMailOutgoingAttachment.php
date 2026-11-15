<?php

/**
 * A document to attach to an outgoing Direct message.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\DirectMessaging;

final readonly class PhiMailOutgoingAttachment
{
    /**
     * @param string $fileName the attachment's name, which the protocol ends at the first space
     */
    public function __construct(
        public PhiMailAttachmentType $type,
        public string $fileName,
        public string $content,
    ) {
    }
}
