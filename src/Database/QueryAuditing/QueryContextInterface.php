<?php

/**
 * Interface for query execution context.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <eric.stern@gmail.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Database\QueryAuditing;

/**
 * Provides context about the current user and session for audit logging.
 *
 * This interface abstracts session access to enable testing without
 * depending on the session wrapper.
 */
interface QueryContextInterface
{
    /**
     * Get the current authenticated username.
     */
    public function getUser(): ?string;

    /**
     * Get the current user's group/provider.
     */
    public function getGroup(): ?string;

    /**
     * Get the current patient ID from context.
     */
    public function getPatientId(): ?int;

    /**
     * Get the client certificate common name if present.
     */
    public function getClientCertName(): ?string;
}
