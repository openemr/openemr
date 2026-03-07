<?php

/**
 * SessionUtilReadAndCloseTest - Tests for SessionUtil open-write-close pattern
 *
 * Tests that SessionUtil::setSession(), unsetSession(), and setUnsetSession()
 * correctly reopen a read-and-close session for writing, perform the operation,
 * and close the session again to release the lock.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @author    Claude Code AI
 * @copyright Copyright (c) 2026 Milan Zivkovic
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Common\Session;

use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Session\Storage\ReadAndCloseNativeSessionStorage;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;

class SessionUtilReadAndCloseTest extends TestCase
{
    /** @var array<mixed, mixed> */
    private array $originalCookie;

    /** @var array<mixed, mixed> */
    private array $originalServer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalCookie = $_COOKIE;
        $this->originalServer = $_SERVER;

        $GLOBALS['web_root'] = '';

        // Close any active session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        // Reset session name to default
        session_name('PHPSESSID');

        // Reset the singleton instance before each test
        $this->resetSingleton();
    }

    protected function tearDown(): void
    {
        $_COOKIE = $this->originalCookie;
        $_SERVER = $this->originalServer;

        $this->resetSingleton();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        parent::tearDown();
    }

    private function resetSingleton(): void
    {
        $reflection = new ReflectionClass(SessionWrapperFactory::class);
        $instancesProperty = $reflection->getProperty('instances');
        $instancesProperty->setValue(null, []);
    }

    /**
     * Helper: creates a ReadAndCloseNativeSessionStorage + Session, starts it,
     * and wires it into the SessionWrapperFactory singleton.
     *
     * @return array{storage: ReadAndCloseNativeSessionStorage, session: Session, sessionId: string}
     */
    private function createReadAndCloseSession(): array
    {
        // First create a writable session to establish session data
        $setupStorage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestSessionUtil',
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $setupSession = new Session($setupStorage, new AttributeBag('TestSessionUtil'));
        $setupSession->start();
        $sessionId = $setupSession->getId();
        $setupSession->save();

        // Now open the same session with read_and_close
        session_id($sessionId);
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestSessionUtil',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $session = new Session($storage, new AttributeBag('TestSessionUtil'));
        $session->start();

        // Wire into the singleton
        $factory = SessionWrapperFactory::getInstance();
        $factory->setActiveSession($session, $storage);

        return ['storage' => $storage, 'session' => $session, 'sessionId' => $sessionId];
    }

    // =========================================================================
    // setSession() Tests
    // =========================================================================

    /**
     * Test that setSession() reopens a read-and-close session, writes, and closes
     */
    public function testSetSessionReopensAndClosesReadAndCloseSession(): void
    {
        $ctx = $this->createReadAndCloseSession();
        $storage = $ctx['storage'];

        // Before: session should be in read-and-close state
        $this->assertTrue(
            $storage->isClosedByReadAndClose(),
            'Session should be in read-and-close state before setSession'
        );

        // setSession should reopen, write, and close
        SessionUtil::setSession('test_key', 'test_value');

        // After: session should no longer be in read-and-close state
        // (it was reopened, written, and saved -- now in normal closed state)
        $this->assertFalse(
            $storage->isClosedByReadAndClose(),
            'Session should not be in read-and-close state after setSession'
        );

        // Verify the data was persisted
        session_id($ctx['sessionId']);
        $verifyStorage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestSessionUtil',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $verifySession = new Session($verifyStorage, new AttributeBag('TestSessionUtil'));
        $verifySession->start();

        $this->assertEquals(
            'test_value',
            $verifySession->get('test_key'),
            'Data written by setSession should be persisted'
        );
    }

    /**
     * Test that setSession() with array reopens and writes all keys
     */
    public function testSetSessionArrayReopensAndClosesReadAndCloseSession(): void
    {
        $ctx = $this->createReadAndCloseSession();
        $storage = $ctx['storage'];

        $this->assertTrue($storage->isClosedByReadAndClose());

        SessionUtil::setSession(['key1' => 'val1', 'key2' => 'val2']);

        $this->assertFalse($storage->isClosedByReadAndClose());

        // Verify persistence
        session_id($ctx['sessionId']);
        $verifyStorage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestSessionUtil',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $verifySession = new Session($verifyStorage, new AttributeBag('TestSessionUtil'));
        $verifySession->start();

        $this->assertEquals('val1', $verifySession->get('key1'));
        $this->assertEquals('val2', $verifySession->get('key2'));
    }

    /**
     * Test that setSession() works normally with a writable (non read-and-close) session
     */
    public function testSetSessionWorksWithWritableSession(): void
    {
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestWritable',
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $session = new Session($storage, new AttributeBag('TestWritable'));
        $session->start();

        $factory = SessionWrapperFactory::getInstance();
        $factory->setActiveSession($session, $storage);

        $this->assertFalse($storage->isClosedByReadAndClose());

        // Should work without any reopen/close dance
        SessionUtil::setSession('writable_key', 'writable_value');

        // Session should still be active (not closed)
        $this->assertTrue(
            $storage->isStarted(),
            'Writable session should still be started after setSession'
        );

        $this->assertEquals('writable_value', $session->get('writable_key'));
    }

    // =========================================================================
    // unsetSession() Tests
    // =========================================================================

    /**
     * Test that unsetSession() reopens a read-and-close session, removes key, and closes
     */
    public function testUnsetSessionReopensAndClosesReadAndCloseSession(): void
    {
        // Create session with initial data
        $setupStorage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestUnset',
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $setupSession = new Session($setupStorage, new AttributeBag('TestUnset'));
        $setupSession->start();
        $setupSession->set('to_remove', 'exists');
        $setupSession->set('to_keep', 'stays');
        $sessionId = $setupSession->getId();
        $setupSession->save();

        // Reopen with read_and_close
        session_id($sessionId);
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestUnset',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $session = new Session($storage, new AttributeBag('TestUnset'));
        $session->start();

        $factory = SessionWrapperFactory::getInstance();
        $factory->setActiveSession($session, $storage);

        $this->assertTrue($storage->isClosedByReadAndClose());

        SessionUtil::unsetSession('to_remove');

        $this->assertFalse($storage->isClosedByReadAndClose());

        // Verify the key was removed and the other key remains
        session_id($sessionId);
        $verifyStorage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestUnset',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $verifySession = new Session($verifyStorage, new AttributeBag('TestUnset'));
        $verifySession->start();

        $this->assertNull(
            $verifySession->get('to_remove'),
            'Removed key should be null after unsetSession'
        );
        $this->assertEquals(
            'stays',
            $verifySession->get('to_keep'),
            'Other keys should remain after unsetSession'
        );
    }

    /**
     * Test that unsetSession() with array removes multiple keys
     */
    public function testUnsetSessionArrayReopensAndClosesReadAndCloseSession(): void
    {
        $setupStorage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestUnsetArr',
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $setupSession = new Session($setupStorage, new AttributeBag('TestUnsetArr'));
        $setupSession->start();
        $setupSession->set('rem1', 'a');
        $setupSession->set('rem2', 'b');
        $setupSession->set('keep', 'c');
        $sessionId = $setupSession->getId();
        $setupSession->save();

        session_id($sessionId);
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestUnsetArr',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $session = new Session($storage, new AttributeBag('TestUnsetArr'));
        $session->start();

        $factory = SessionWrapperFactory::getInstance();
        $factory->setActiveSession($session, $storage);

        SessionUtil::unsetSession(['rem1', 'rem2']);

        // Verify
        session_id($sessionId);
        $verifyStorage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestUnsetArr',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $verifySession = new Session($verifyStorage, new AttributeBag('TestUnsetArr'));
        $verifySession->start();

        $this->assertNull($verifySession->get('rem1'));
        $this->assertNull($verifySession->get('rem2'));
        $this->assertEquals('c', $verifySession->get('keep'));
    }

    // =========================================================================
    // setUnsetSession() Tests
    // =========================================================================

    /**
     * Test that setUnsetSession() reopens, sets and unsets, then closes
     */
    public function testSetUnsetSessionReopensAndClosesReadAndCloseSession(): void
    {
        $setupStorage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestSetUnset',
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $setupSession = new Session($setupStorage, new AttributeBag('TestSetUnset'));
        $setupSession->start();
        $setupSession->set('old_key', 'old_value');
        $sessionId = $setupSession->getId();
        $setupSession->save();

        session_id($sessionId);
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestSetUnset',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $session = new Session($storage, new AttributeBag('TestSetUnset'));
        $session->start();

        $factory = SessionWrapperFactory::getInstance();
        $factory->setActiveSession($session, $storage);

        $this->assertTrue($storage->isClosedByReadAndClose());

        SessionUtil::setUnsetSession(
            ['new_key' => 'new_value'],
            ['old_key']
        );

        $this->assertFalse($storage->isClosedByReadAndClose());

        // Verify
        session_id($sessionId);
        $verifyStorage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestSetUnset',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $verifySession = new Session($verifyStorage, new AttributeBag('TestSetUnset'));
        $verifySession->start();

        $this->assertEquals(
            'new_value',
            $verifySession->get('new_key'),
            'New key should be set after setUnsetSession'
        );
        $this->assertNull(
            $verifySession->get('old_key'),
            'Old key should be removed after setUnsetSession'
        );
    }

    // =========================================================================
    // Edge Case Tests
    // =========================================================================

    /**
     * Test that withWritableSession is a no-op when storage is null
     * (e.g. when session was set via setActiveSession without storage)
     */
    public function testSetSessionWorksWhenStorageIsNull(): void
    {
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestNullStorage',
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $session = new Session($storage, new AttributeBag('TestNullStorage'));
        $session->start();

        // Set session without providing storage
        $factory = SessionWrapperFactory::getInstance();
        $factory->setActiveSession($session);

        // Should work fine -- no reopen/close dance needed
        SessionUtil::setSession('null_storage_key', 'value');

        $this->assertEquals('value', $session->get('null_storage_key'));
    }
}
