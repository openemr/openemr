<?php

/**
 * PortalSessionAccessor — narrow read/write surface over the patient portal session.
 *
 * Exists so PatientPortalLoginController doesn't have to call $session->set() / ->remove()
 * directly, which the upstream phpstan rule `openemr.forbidDirectSessionWrite` rejects:
 * those methods silently fail on read-and-close sessions, and the production code path
 * has to route writes through SessionUtil::setSession/unsetSession to auto-reopen the
 * session lock.
 *
 * The interface is testable: the in-memory implementation in
 * tests/Tests/Unit/Portal/PatientPortalLoginControllerTest.php stores values in a
 * plain array; the production SessionUtilPortalSessionAccessor delegates writes to
 * SessionUtil and reads to a SessionInterface.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Controllers\Portal;

interface PortalSessionAccessor
{
    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    public function set(string $key, mixed $value): void;

    /**
     * @param array<string, mixed> $kvs
     */
    public function setMany(array $kvs): void;

    public function remove(string $key): void;

    /**
     * @param list<string> $keys
     */
    public function removeMany(array $keys): void;
}
