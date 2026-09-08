<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Logging;

interface AuditLoggerInterface
{
    /**
     * @param ?mixed[] $binds
     */
    public function auditSQLEvent(string $statement, bool $outcome, ?array $binds = null): void;
}
