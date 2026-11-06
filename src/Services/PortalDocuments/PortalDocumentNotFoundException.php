<?php

/**
 * A portal document id that names no document.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PortalDocuments;

final class PortalDocumentNotFoundException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Portal document not found');
    }
}
