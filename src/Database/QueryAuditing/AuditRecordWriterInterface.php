<?php

/**
 * Interface for audit record writers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Database\QueryAuditing;

use Doctrine\DBAL\Driver\Connection;

interface AuditRecordWriterInterface
{
    public function write(
        Connection $connection,
        int $success,
        string $event,
        string $user,
        string $group,
        string $comments,
        ?int $patientId,
        ?string $category,
        string $logFrom = 'open-emr',
    ): void;
}
