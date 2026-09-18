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

    /**
     * Returns an array of session configuration directives, without the
     * `session.` prefix.
     *
     * @see https://www.php.net/manual/en/session.configuration.php
     * @see https://php.net/session_start
     *
     * @return array<string, mixed>
     */
    public function toSessionStartOptions(): array
    {
        return [
            'name' => $this->name,
            'cookie_path' => $this->cookiePath,
            'gc_maxlifetime' => $this->gcMaxLifetime,
            'use_strict_mode' => $this->useStrictMode,
            'use_cookies' => $this->useCookies,
            'use_only_cookies' => $this->useOnlyCookies,
            'cookie_samesite' => $this->cookieSameSite,
            'cookie_secure' => $this->cookieSecure,
            'cookie_httponly' => $this->cookieHttpOnly,
            'read_and_close' => $this->readAndClose,
        ];
    }
}
