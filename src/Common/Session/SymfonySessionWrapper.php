<?php

/**
 * SymfonySessionWrapper is a simple wrapper around Symfony Session, intended to provide
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

use Symfony\Component\HttpFoundation\Session\Session;

readonly class SymfonySessionWrapper implements SessionWrapperInterface
{
    public function __construct(private Session $session)
    {
    }

    public function getId(): string
    {
        return $this->session->getId();
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

    public function all(): array
    {
        return $this->session->all();
    }
}
