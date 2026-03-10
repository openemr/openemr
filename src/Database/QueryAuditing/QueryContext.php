<?php

/**
 * Session-backed query context implementation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Database\QueryAuditing;

use OpenEMR\Common\Session\SessionWrapperInterface;

/**
 * Provides query context from session and server variables.
 */
final class QueryContext implements QueryContextInterface
{
    public function __construct(
        private readonly SessionWrapperInterface $session,
    ) {
    }

    public function getUser(): ?string
    {
        $user = $this->session->get('authUser');
        return is_string($user) && $user !== '' ? $user : null;
    }

    public function getGroup(): ?string
    {
        $group = $this->session->get('authProvider');
        return is_string($group) && $group !== '' ? $group : null;
    }

    public function getPatientId(): ?int
    {
        $pid = $this->session->get('pid');
        if ($pid === null || $pid === '') {
            return null;
        }
        if (!is_numeric($pid)) {
            return null;
        }
        return (int) $pid;
    }

    public function getClientCertName(): ?string
    {
        $cn = $_SERVER['SSL_CLIENT_S_DN_CN'] ?? null;
        return is_string($cn) && $cn !== '' ? $cn : null;
    }
}
