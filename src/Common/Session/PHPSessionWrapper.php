<?php

namespace OpenEMR\Common\Session;

use OpenEMR\Common\Session\SessionWrapperInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class PHPSessionWrapper implements SessionWrapperInterface
{

    public function get(string $key, $default = null)
    {
        return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : $default;
    }

    public function set(string $key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function has(string $key)
    {
        return array_key_exists($key, $_SESSION);
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
        return session_regenerate_id($destroy);
    }

    public function getSymfonySession(): ?Session
    {
        return null;
    }
}
