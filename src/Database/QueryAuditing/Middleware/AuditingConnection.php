<?php

/**
 * DBAL connection wrapper for query auditing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Database\QueryAuditing\Middleware;

use Doctrine\DBAL\Driver\Connection as ConnectionInterface;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement as DriverStatement;
use OpenEMR\Database\QueryAuditing\QueryAuditorInterface;

/**
 * Connection wrapper that audits query executions.
 *
 * Intercepts query(), exec(), and prepare() to audit SQL executions.
 *
 * @internal This connection should only be instantiated by AuditingDriver.
 */
final class AuditingConnection extends AbstractConnectionMiddleware
{
    public function __construct(
        private readonly ConnectionInterface $wrappedConnection,
        private readonly QueryAuditorInterface $auditor,
    ) {
        parent::__construct($wrappedConnection);
    }

    public function prepare(string $sql): DriverStatement
    {
        return new AuditingStatement(
            parent::prepare($sql),
            $this->auditor,
            $this->wrappedConnection,
            $sql,
        );
    }

    public function query(string $sql): Result
    {
        $result = parent::query($sql);
        $this->auditor->audit($this->wrappedConnection, $sql, null, true);
        return $result;
    }

    public function exec(string $sql): int|string
    {
        $result = parent::exec($sql);
        $this->auditor->audit($this->wrappedConnection, $sql, null, true);
        return $result;
    }
}
