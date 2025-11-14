<?php

namespace OpenEMR\Common\Session;

use OpenEMR\Common\Session\SessionWrapperInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class SymfonySessionWrapper implements SessionWrapperInterface
{
    public function __construct(private readonly Session $session)
    {
    }

    public function get(string $key, $default = null)
    {
        return $this->session->get($key, $default);
    }

    public function set(string $key, $value)
    {
        return $this->session->set($key, $value);
    }

    public function has(string $key)
    {
        return $this->session->has($key);
    }

    public function remove(string $key)
    {
        return $this->session->remove($key);
    }

    public function migrate(bool $destroy = false): bool
    {
        return $this->session->migrate($destroy);
    }

    public function getSymfonySession(): ?Session
    {
        return $this->session;
    }

    public function isSymfonySession(): bool
    {
        return true;
    }

    public function save(): void
    {
        $this->session->save();
    }

    public function clear(): void
    {
        $this->session->clear();
    }
}
