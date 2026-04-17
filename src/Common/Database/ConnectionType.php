<?php

/**
 * Database connection types
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Database;

enum ConnectionType
{
    /**
     * The main read/write connection. Nearly all DB traffic will use this.
     */
    case Main;
    /**
     * Used during audit operations and some application bootstrapping.
     * Separate from the main connection because it:
     * - Enables offsite audit
     * - Will not disrupt autoincrement ids when writing to the audit tables
     * - Breaks a circular dependency when setting up auditing middleware
     */
    case NonAudited;
}
