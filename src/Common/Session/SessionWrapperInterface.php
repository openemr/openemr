<?php

/**
 * SessionWrapperInterface is a simple interface for a session wrapper. It is based on the session functionality
 * in use through shared files between core and portal. Additionally, it has some functions to easily check if it is Symfony Session
 * and to retrieve underlying Symfony Session.
 * Once when the core is ported to the Symfony Session, we can remove this wrapper and use Symfony Session directly.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) Milan Zivkovic
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Session;

use Symfony\Component\HttpFoundation\Session\Session;

interface SessionWrapperInterface
{
    public function getId(): string;
    public function get(string $key, $default = null);
    public function set(string $key, $value);
    public function has(string $key);
    public function remove(string $key);
    public function migrate(bool $destroy = false): bool;
    public function save(): void;
    public function clear(): void;
    public function getSymfonySession(): ?Session;
    public function isSymfonySession(): bool;
    public function all(): array;
}
