<?php

/**
 * Database connection types
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <eric.stern@aoeu.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Database;

enum ConnectionType: string
{
    case Main = 'main';
    case Audit = 'audit';
}
