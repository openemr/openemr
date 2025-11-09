<?php

namespace OpenEMR\Common\Session;

use Symfony\Component\HttpFoundation\Session\Session;

interface SessionWrapperInterface
{
    public function get(string $key, $default = null);
    public function set(string $key, $value);
    public function has(string $key);
    public function remove(string $key);
    public function migrate(bool $destroy = false): bool;
    public function getSymfonySession(): ?Session;
}
