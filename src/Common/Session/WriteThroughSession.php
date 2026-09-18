<?php

/**
 * WriteThroughSession extends Symfony's Session to automatically persist writes
 * on read_and_close sessions.
 *
 * When a session is opened with read_and_close=true, PHP immediately releases
 * the session file lock. Direct $session->set() / $session->remove() calls
 * modify the in-memory bag but are never flushed to disk. This class intercepts
 * those mutations and uses the reopen-write-save cycle from
 * ReadAndCloseNativeSessionStorage so that every write is durable — matching
 * the behavior of SessionUtil::setSession() / unsetSession().
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Session;

use OpenEMR\Common\Session\Storage\ReadAndCloseNativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

class WriteThroughSession extends Session
{
    private ?ReadAndCloseNativeSessionStorage $readAndCloseStorage = null;

    public function __construct(SessionStorageInterface $storage, ?AttributeBagInterface $attributes = null)
    {
        parent::__construct($storage, $attributes);

        if ($storage instanceof ReadAndCloseNativeSessionStorage) {
            $this->readAndCloseStorage = $storage;
        }
    }

    public function set(string $name, mixed $value): void
    {
        $storage = $this->getStorageNeedingReopen();
        if ($storage !== null) {
            $storage->reopenForWriting();
            parent::set($name, $value);
            $this->save();
            return;
        }

        parent::set($name, $value);
    }

    public function remove(string $name): mixed
    {
        $storage = $this->getStorageNeedingReopen();
        if ($storage !== null) {
            $storage->reopenForWriting();
            $result = parent::remove($name);
            $this->save();
            return $result;
        }

        return parent::remove($name);
    }

    public function clear(): void
    {
        $storage = $this->getStorageNeedingReopen();
        if ($storage !== null) {
            $storage->reopenForWriting();
            parent::clear();
            $this->save();
            return;
        }

        parent::clear();
    }

    private function getStorageNeedingReopen(): ?ReadAndCloseNativeSessionStorage
    {
        if ($this->readAndCloseStorage !== null && $this->readAndCloseStorage->isClosedByReadAndClose()) {
            return $this->readAndCloseStorage;
        }

        return null;
    }
}
