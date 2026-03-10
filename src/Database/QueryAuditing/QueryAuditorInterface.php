<?php

/**
 * Interface for SQL query auditing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Database\QueryAuditing;

/**
 * Audits SQL query executions for compliance logging.
 *
 * This is the main entry point for query auditing, designed to be called
 * from DBAL middleware or legacy ADODB wrappers.
 */
interface QueryAuditorInterface
{
    /**
     * Audit a SQL query execution.
     *
     * @param string $sql The SQL statement that was executed
     * @param array<int|string, mixed>|null $params Bound parameters (if any)
     * @param bool $success Whether the query executed successfully
     */
    public function audit(string $sql, ?array $params, bool $success): void;
}
