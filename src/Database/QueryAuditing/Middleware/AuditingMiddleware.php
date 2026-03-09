<?php

/**
 * DBAL middleware for query auditing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <eric.stern@gmail.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Database\QueryAuditing\Middleware;

use Doctrine\DBAL\Driver as DriverInterface;
use Doctrine\DBAL\Driver\Middleware;
use OpenEMR\Database\QueryAuditing\QueryAuditorInterface;

/**
 * DBAL middleware that audits all query executions.
 *
 * This middleware wraps the DBAL driver to intercept and audit all
 * SQL queries for compliance logging.
 */
final class AuditingMiddleware implements Middleware
{
    public function __construct(
        private readonly QueryAuditorInterface $auditor,
    ) {
    }

    public function wrap(DriverInterface $driver): DriverInterface
    {
        return new AuditingDriver($driver, $this->auditor);
    }
}
