<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Session;

final readonly class SessionConfiguration
{
    public function __construct(
        public string $name,
        public string $cookiePath,
        public int $gcMaxLifetime = SessionUtil::DEFAULT_GC_MAXLIFETIME,
        public bool $useStrictMode = true,
        public bool $useCookies = true,
        public bool $useOnlyCookies = true,
        public string $cookieSameSite = 'Strict',
        public bool $cookieSecure = false,
        public bool $cookieHttpOnly = true,
        public bool $readAndClose = false,
    ) {
    }
}
