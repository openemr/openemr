<?php

/**
 * What the audit chain check found.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

enum AuditChainFailure: string
{
    case Break = 'break';
    case Missing = 'missing';
    case Anchor = 'anchor';
    case Upgrade = 'upgrade';
    case Key = 'key';
    case Omitted = 'omitted';
}
