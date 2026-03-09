<?php

/**
 * DBAL driver wrapper for query auditing.
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
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use OpenEMR\Database\QueryAuditing\QueryAuditorInterface;
use SensitiveParameter;

/**
 * Driver wrapper that creates auditing connections.
 *
 * @internal This driver should only be instantiated by AuditingMiddleware.
 */
final class AuditingDriver extends AbstractDriverMiddleware
{
    public function __construct(
        DriverInterface $driver,
        private readonly QueryAuditorInterface $auditor,
    ) {
        parent::__construct($driver);
    }

    public function connect(
        #[SensitiveParameter]
        array $params,
    ): AuditingConnection {
        return new AuditingConnection(
            parent::connect($params),
            $this->auditor,
        );
    }
}
