<?php

/**
 * SessionUtilPortalSessionAccessor — production PortalSessionAccessor that delegates
 * reads to the supplied SessionInterface and routes writes through SessionUtil, so the
 * portal's read-and-close session lock is reopened correctly on each write.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Controllers\Portal;

use OpenEMR\Common\Session\SessionUtil;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionUtilPortalSessionAccessor implements PortalSessionAccessor
{
    public function __construct(
        private readonly SessionInterface $session,
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->session->get($key, $default);
    }

    public function has(string $key): bool
    {
        return $this->session->has($key);
    }

    public function set(string $key, mixed $value): void
    {
        SessionUtil::setSession($key, $value);
    }

    public function setMany(array $kvs): void
    {
        SessionUtil::setSession($kvs);
    }

    public function remove(string $key): void
    {
        SessionUtil::unsetSession($key);
    }

    public function removeMany(array $keys): void
    {
        SessionUtil::unsetSession($keys);
    }
}
