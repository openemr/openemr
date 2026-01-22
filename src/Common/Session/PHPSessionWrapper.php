<?php

/**
 * PHPSessionWrapper is a simple wrapper around PHP session handling, intended to provide
 * a consistent interface for session management between different parts of the OpenEMR application, core, and portal.
 * The challenge was to ensure that we can handle session in shared files using the same API while we are porting
 * portal to use Symfony Session. Once when the core is ported, we can remove this wrapper and use Symfony Session directly.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) Milan Zivkovic
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Session;

use OpenEMR\Core\OEGlobalsBag;
use Symfony\Component\HttpFoundation\Session\Session;

class PHPSessionWrapper implements SessionWrapperInterface
{
    public function __construct()
    {
        $globalsBag = OEGlobalsBag::getInstance();
        $webroot = $globalsBag->get('webroot');
        if ($webroot !== null && session_status() !== PHP_SESSION_ACTIVE) {
            SessionUtil::coreSessionStart($webroot, false);
        }
    }

    public function getId(): string
    {
        return session_id();
    }

    public function get(string $key, $default = null)
    {
        if (is_array($_SESSION)) {
            return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
        }
        return $default;
    }

    public function set(string $key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function has(string $key)
    {
        if (is_array($_SESSION)) {
            return array_key_exists($key, $_SESSION);
        }
        return false;
    }

    public function remove(string $key)
    {
        if (! array_key_exists($key, $_SESSION)) {
            return null;
        }

        $value = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $value;
    }

    public function migrate(bool $destroy = false): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return session_regenerate_id($destroy);
        }
        return true;
    }

    public function getSymfonySession(): ?Session
    {
        return null;
    }

    public function isSymfonySession(): bool
    {
        return false;
    }

    public function save(): void
    {
        session_write_close();
    }

    public function clear(): void
    {
        session_unset();
    }

    public function all(): array
    {
        return is_array($_SESSION) ? $_SESSION : [];
    }
}
