<?php

/**
 * DBAL statement wrapper for query auditing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Database\QueryAuditing\Middleware;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result as ResultInterface;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;
use OpenEMR\Database\QueryAuditing\QueryAuditorInterface;

/**
 * Statement wrapper that audits prepared statement executions.
 *
 * Collects bound parameters and audits when execute() is called.
 *
 * @internal This statement should only be instantiated by AuditingConnection.
 */
final class AuditingStatement extends AbstractStatementMiddleware
{
    /** @var array<int|string, mixed> */
    private array $params = [];

    public function __construct(
        StatementInterface $statement,
        private readonly QueryAuditorInterface $auditor,
        private readonly Connection $connection,
        private readonly string $sql,
    ) {
        parent::__construct($statement);
    }

    public function bindValue(int|string $param, mixed $value, ParameterType $type): void
    {
        $this->params[$param] = $value;
        parent::bindValue($param, $value, $type);
    }

    public function execute(): ResultInterface
    {
        $result = parent::execute();
        $this->auditor->audit($this->connection, $this->sql, $this->params, true);
        return $result;
    }
}
