<?php

/**
 * Whole-form copy-forward operations of the eye exam form.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag;

/**
 * Copy-forward requests that are not scoped to a single {@see Zone}. The browser
 * sends these through the same `zone` request parameter the zones use.
 */
enum CopyMode: string
{
    /** Impression and plan items only. */
    case IMPPLAN = 'IMPPLAN';

    /** Every zone, plus the impression. */
    case ALL = 'ALL';

    /** The entire prior record, for the read-only live view of another user's charting. */
    case READONLY = 'READONLY';
}
