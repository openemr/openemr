<?php

/**
 * Null query context for CLI and test usage.
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
 * Query context that returns no user/session information.
 *
 * Use this for CLI commands, background jobs, or tests where there
 * is no authenticated user session.
 */
final class NullQueryContext implements QueryContextInterface
{
    public function getUser(): ?string
    {
        return null;
    }

    public function getGroup(): ?string
    {
        return null;
    }

    public function getPatientId(): ?int
    {
        return null;
    }

    public function getClientCertName(): ?string
    {
        return null;
    }
}
