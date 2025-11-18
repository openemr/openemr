<?php

namespace OpenEMR\Common\Session;

use OpenEMR\Common\Session\SessionWrapperInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class PHPSessionWrapper implements SessionWrapperInterface
{

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
}
