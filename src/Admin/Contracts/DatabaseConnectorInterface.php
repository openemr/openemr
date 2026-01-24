<?php

/**
 * Database Connector Interface
 *
 * Contract for database connection management services.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Contracts;

use OpenEMR\Admin\ValueObjects\DatabaseCredentials;

interface DatabaseConnectorInterface
{
    /**
     * Get or create a database connection
     *
     * @throws \OpenEMR\Admin\Exceptions\DatabaseConnectionException
     */
    public function getConnection(DatabaseCredentials $credentials): \mysqli;

    /**
     * Close all pooled connections
     */
    public function closeAllConnections(): void;
}
