<?php

/**
 * One finding from an audit chain check.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging\Audit;

final readonly class AuditChainFailureItem
{
    public function __construct(
        public AuditChainFailure $kind,
        public string $id = '',
    ) {
    }
}
