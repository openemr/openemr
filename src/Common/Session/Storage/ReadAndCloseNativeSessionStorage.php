<?php

/**
 * ReadAndCloseNativeSessionStorage extends Symfony's NativeSessionStorage to support
 * PHP's read_and_close session option.
 *
 * Symfony's NativeSessionStorage::setOptions() silently drops `read_and_close` because
 * it is not in the hardcoded whitelist of allowed ini options. This class extracts the
 * option before passing to the parent and implements the read-and-close lifecycle:
 *
 *  1. start() with readAndClose=true calls session_start(['read_and_close' => true])
 *     which loads session data and immediately releases the file lock.
 *  2. save() is a no-op when the session was already closed by read_and_close.
 *  3. reopenForWriting() re-acquires the session lock so callers can write, then
 *     close again via save().
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @author    Claude Code AI
 * @copyright Copyright (c) 2026 Milan Zivkovic
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Session\Storage;

use OpenEMR\BC\ServiceContainer;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\AbstractProxy;

class ReadAndCloseNativeSessionStorage extends NativeSessionStorage
{
    private bool $readAndClose = false;

    private bool $sessionClosedByReadAndClose = false;

    private bool $wasOriginallyReadAndClose = false;

    /**
     * @param array<string, mixed> $options Session configuration options (may include 'read_and_close')
     * @param AbstractProxy|\SessionHandlerInterface|null $handler
     */
    public function __construct(array $options = [], AbstractProxy|\SessionHandlerInterface|null $handler = null, ?MetadataBag $metaBag = null)
    {
        // Extract read_and_close before parent sees it (parent would silently drop it)
        if (isset($options['read_and_close'])) {
            $this->readAndClose = (bool) $options['read_and_close'];
            unset($options['read_and_close']);
        }

        parent::__construct($options, $handler, $metaBag);
    }

    /**
     * {@inheritdoc}
     */
    public function start(): bool
    {
        if (!$this->readAndClose) {
            return parent::start();
        }

        if ($this->started) {
            return true;
        }

        if (\PHP_SESSION_ACTIVE === session_status()) {
            throw new \RuntimeException('Failed to start the session: already started by PHP.');
        }

        if (headers_sent($file, $line)) {
            throw new \RuntimeException(sprintf('Failed to start the session because headers have already been sent by "%s" at line %d.', $file, $line));
        }

        // Validate the session ID if one is present in the cookie
        $sessionName = session_name();
        $cookieSessionId = is_string($sessionName) ? ($_COOKIE[$sessionName] ?? null) : null;
        if (is_string($cookieSessionId) && !preg_match('/^[a-zA-Z0-9,-]{22,250}$/', $cookieSessionId)) {
            // Regenerate the session ID to avoid using an invalid one
            session_id(session_create_id() ?: '');
        }

        // Configure the save handler before starting
        if ($this->saveHandler instanceof \SessionHandlerInterface) {
            session_set_save_handler($this->saveHandler, false);
        }

        // Start with read_and_close -- PHP opens the session, loads data, and
        // immediately releases the lock.
        $started = session_start(['read_and_close' => true]);

        if (!$started) {
            throw new \RuntimeException('Failed to start the session.');
        }

        // Load the session data into Symfony's bag infrastructure
        $this->loadSession();

        $this->started = true;
        $this->closed = false;
        $this->sessionClosedByReadAndClose = true;
        $this->wasOriginallyReadAndClose = true;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function save(): void
    {
        // If the session was opened with read_and_close, PHP already closed it.
        // Just update our internal state flags.
        if ($this->sessionClosedByReadAndClose) {
            $this->closed = true;
            $this->started = false;
            return;
        }

        parent::save();
    }

    /**
     * Reopen the session for writing after it was opened with read_and_close.
     *
     * This acquires the session file lock, reconnects Symfony's bags to the
     * new $_SESSION superglobal, and transitions the storage to writable mode.
     * The caller is expected to call save() when done writing to release the lock.
     */
    public function reopenForWriting(): void
    {
        if (!$this->isClosedByReadAndClose()) {
            return;
        }

        ServiceContainer::getLogger()->warning(
            'Session reopened for writing after read_and_close',
            ['session_name' => session_name(), 'script' => $_SERVER['SCRIPT_NAME'] ?? 'unknown']
        );

        // Re-acquire the session (with lock this time)
        session_start();

        // Reconnect Symfony bags to the new $_SESSION entries
        $this->loadSession();

        $this->started = true;
        $this->closed = false;
        $this->sessionClosedByReadAndClose = false;
        $this->readAndClose = false;
    }

    /**
     * Whether the session needs to be reopened before writing.
     *
     * Returns true in two cases:
     *  1. Session was opened with read_and_close and has not been reopened yet.
     *  2. Session was originally read_and_close, was reopened+saved, and is now
     *     closed again. Without this check, a second withWritableSession() call
     *     would trigger Symfony's NativeSessionStorage::getBag() auto-start,
     *     opening a full session with write lock held until PHP shutdown.
     */
    public function isClosedByReadAndClose(): bool
    {
        return $this->sessionClosedByReadAndClose
            || ($this->wasOriginallyReadAndClose && !$this->started);
    }
}
