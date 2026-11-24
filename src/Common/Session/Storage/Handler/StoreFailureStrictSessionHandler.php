<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Session\Storage\Handler;

use OpenEMR\Common\Session\SessionStoreUnavailableException;
use SessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\StrictSessionHandler;

/**
 * Strict handler over PHP's native save handler that tells a missing session
 * apart from an unreachable store.
 *
 * \SessionHandler::read() returns '' when no session exists and false when
 * the native module (files, redis, ...) failed. Symfony's validateId() keeps
 * the ID when the read fails and rethrows the failure from read(), so PHP
 * aborts the start without issuing a new ID (symfony/symfony#66513). This
 * handler turns the bare false into a retryable 503.
 */
final class StoreFailureStrictSessionHandler extends StrictSessionHandler
{
    public function __construct(private readonly SessionHandler $native)
    {
        parent::__construct($native);
    }

    protected function doRead(#[\SensitiveParameter] string $sessionId): string
    {
        $data = $this->native->read($sessionId);
        if ($data === false) {
            throw new SessionStoreUnavailableException();
        }

        return $data;
    }
}
