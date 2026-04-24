<?php

/**
 * ReadAndCloseNativeSessionStorageTest - Tests for read-and-close session storage
 *
 * Tests that ReadAndCloseNativeSessionStorage correctly:
 *  - Extracts read_and_close from options before passing to parent
 *  - Starts sessions in read-and-close mode (lock released immediately)
 *  - Makes save() a no-op when in read-and-close state
 *  - Reopens for writing and transitions to writable mode
 *  - Reports correct state via isClosedByReadAndClose()
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Common\Session;

use OpenEMR\Common\Session\Storage\ReadAndCloseNativeSessionStorage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;

class ReadAndCloseNativeSessionStorageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Close any active session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        // Reset session name to default
        session_name('PHPSESSID');
    }

    protected function tearDown(): void
    {
        // Close any active session and clear session data
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        $_SESSION = [];

        parent::tearDown();
    }

    // =========================================================================
    // Constructor Tests
    // =========================================================================

    /**
     * Test that read_and_close option is extracted from options and not passed to parent
     */
    public function testConstructorExtractsReadAndClose(): void
    {
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestSession',
            'read_and_close' => true,
        ]);

        // The storage should have extracted read_and_close.
        // We verify by checking that isClosedByReadAndClose is false before start
        $this->assertFalse(
            $storage->isClosedByReadAndClose(),
            'isClosedByReadAndClose should be false before session is started'
        );
    }

    /**
     * Test that construction without read_and_close works normally
     */
    public function testConstructorWithoutReadAndClose(): void
    {
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestSession',
        ]);

        $this->assertFalse(
            $storage->isClosedByReadAndClose(),
            'isClosedByReadAndClose should be false when read_and_close is not set'
        );
    }

    // =========================================================================
    // start() Tests
    // =========================================================================

    /**
     * Test that start() with readAndClose=true starts and immediately closes the session
     */
    public function testStartWithReadAndCloseOpensAndClosesSession(): void
    {
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestReadClose',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);

        $result = $storage->start();

        $this->assertTrue($result, 'start() should return true');
        $this->assertTrue(
            $storage->isClosedByReadAndClose(),
            'isClosedByReadAndClose should be true after read-and-close start'
        );

        // PHP session should be closed (read_and_close releases the lock)
        $this->assertEquals(
            PHP_SESSION_NONE,
            session_status(),
            'PHP session should be in NONE state after read_and_close start'
        );
    }

    /**
     * Test that start() without readAndClose delegates to parent normally
     */
    public function testStartWithoutReadAndCloseDelegatesToParent(): void
    {
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestNormal',
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);

        $result = $storage->start();

        $this->assertTrue($result, 'start() should return true');
        $this->assertFalse(
            $storage->isClosedByReadAndClose(),
            'isClosedByReadAndClose should be false for normal start'
        );

        // PHP session should be active (normal mode holds the lock)
        $this->assertEquals(
            PHP_SESSION_ACTIVE,
            session_status(),
            'PHP session should be ACTIVE after normal start'
        );
    }

    /**
     * Test that start() returns true if already started
     */
    public function testStartReturnsTrueIfAlreadyStarted(): void
    {
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestDouble',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);

        $storage->start();
        $result = $storage->start(); // second call

        $this->assertTrue($result, 'start() called twice should return true');
    }

    /**
     * Test that session data is accessible after read_and_close start
     */
    public function testSessionDataAccessibleAfterReadAndCloseStart(): void
    {
        // First, create a session with some data
        $setupStorage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestDataAccess',
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $setupSession = new Session($setupStorage, new AttributeBag('TestDataAccess'));
        $setupSession->start();
        $setupSession->set('test_key', 'hello_world');
        $sessionId = $setupSession->getId();
        $setupSession->save();

        // Now open the same session with read_and_close
        session_id($sessionId);
        $readStorage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestDataAccess',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $readSession = new Session($readStorage, new AttributeBag('TestDataAccess'));
        $readSession->start();

        $this->assertEquals(
            'hello_world',
            $readSession->get('test_key'),
            'Session data should be accessible after read_and_close start'
        );
    }

    // =========================================================================
    // save() Tests
    // =========================================================================

    /**
     * Test that save() is a no-op when session was opened with read_and_close
     */
    public function testSaveIsNoOpForReadAndCloseSession(): void
    {
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestSaveNoop',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);

        $storage->start();
        $this->assertTrue($storage->isClosedByReadAndClose());

        // save() should not throw or cause issues
        $storage->save();

        // Session should still report as closed by read_and_close state-wise,
        // but save transitions internal flags
        $this->assertFalse(
            $storage->isStarted(),
            'isStarted should be false after save'
        );
    }

    /**
     * Test that save() delegates to parent for normal (writable) sessions
     */
    public function testSaveDelegatesToParentForNormalSession(): void
    {
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestSaveNormal',
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);

        $storage->start();
        $this->assertFalse($storage->isClosedByReadAndClose());

        $storage->save();

        $this->assertFalse(
            $storage->isStarted(),
            'isStarted should be false after save on normal session'
        );
    }

    // =========================================================================
    // reopenForWriting() Tests
    // =========================================================================

    /**
     * Test that reopenForWriting transitions from read-only to writable mode
     */
    public function testReopenForWritingTransitionsToWritableMode(): void
    {
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestReopen',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);

        $storage->start();
        $this->assertTrue($storage->isClosedByReadAndClose());

        $storage->reopenForWriting();

        $this->assertFalse(
            $storage->isClosedByReadAndClose(),
            'isClosedByReadAndClose should be false after reopenForWriting'
        );
        $this->assertTrue(
            $storage->isStarted(),
            'isStarted should be true after reopenForWriting'
        );
        $this->assertEquals(
            PHP_SESSION_ACTIVE,
            session_status(),
            'PHP session should be ACTIVE after reopenForWriting'
        );
    }

    /**
     * Test that reopenForWriting is a no-op when not in read-and-close state
     */
    public function testReopenForWritingIsNoOpWhenNotReadAndClose(): void
    {
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestReopenNoop',
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);

        $storage->start();
        $this->assertFalse($storage->isClosedByReadAndClose());

        // Should not throw or change anything
        $storage->reopenForWriting();

        $this->assertTrue(
            $storage->isStarted(),
            'isStarted should remain true after no-op reopenForWriting'
        );
    }

    /**
     * Test full write cycle: read_and_close -> reopen -> write -> save
     */
    public function testFullWriteCycleAfterReadAndClose(): void
    {
        // Create a session with initial data
        $setupStorage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestWriteCycle',
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $setupSession = new Session($setupStorage, new AttributeBag('TestWriteCycle'));
        $setupSession->start();
        $setupSession->set('initial_key', 'initial_value');
        $sessionId = $setupSession->getId();
        $setupSession->save();

        // Open with read_and_close
        session_id($sessionId);
        $readStorage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestWriteCycle',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $readSession = new Session($readStorage, new AttributeBag('TestWriteCycle'));
        $readSession->start();

        // Verify initial data is readable
        $this->assertEquals('initial_value', $readSession->get('initial_key'));
        $this->assertTrue($readStorage->isClosedByReadAndClose());

        // Reopen for writing
        $readStorage->reopenForWriting();
        $this->assertFalse($readStorage->isClosedByReadAndClose());

        // Write new data
        $readSession->set('new_key', 'new_value');
        $readSession->save();

        // Verify the data was persisted by opening again
        session_id($sessionId);
        $verifyStorage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestWriteCycle',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $verifySession = new Session($verifyStorage, new AttributeBag('TestWriteCycle'));
        $verifySession->start();

        $this->assertEquals(
            'initial_value',
            $verifySession->get('initial_key'),
            'Initial data should still be present after write cycle'
        );
        $this->assertEquals(
            'new_value',
            $verifySession->get('new_key'),
            'New data should be persisted after reopen-write-save cycle'
        );
    }

    // =========================================================================
    // isClosedByReadAndClose() State Transition Tests
    // =========================================================================

    /**
     * Test state transitions through the full lifecycle
     */
    public function testStateTransitionsThroughLifecycle(): void
    {
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestStates',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);

        // Before start: false
        $this->assertFalse($storage->isClosedByReadAndClose(), 'Before start');

        // After read_and_close start: true
        $storage->start();
        $this->assertTrue($storage->isClosedByReadAndClose(), 'After read_and_close start');

        // After reopen: false
        $storage->reopenForWriting();
        $this->assertFalse($storage->isClosedByReadAndClose(), 'After reopenForWriting');

        // After save: true again (originally read_and_close + not started)
        $storage->save();
        $this->assertTrue($storage->isClosedByReadAndClose(), 'After save — still needs reopen for next write');
    }

    /**
     * Test that isClosedByReadAndClose returns true after reopen+save cycle.
     *
     * After reopenForWriting() + save(), the session is closed but was originally
     * opened with read_and_close. isClosedByReadAndClose() must return true so that
     * a subsequent withWritableSession() call properly reopens instead of triggering
     * Symfony's NativeSessionStorage::getBag() auto-start (which would hold the lock).
     */
    public function testIsClosedByReadAndCloseReturnsTrueAfterReopenSaveCycle(): void
    {
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestReopenSave',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);

        $storage->start();
        $this->assertTrue($storage->isClosedByReadAndClose(), 'After start');

        // First reopen+save cycle
        $storage->reopenForWriting();
        $this->assertFalse($storage->isClosedByReadAndClose(), 'After reopen (started=true)');

        $storage->save();
        $this->assertTrue($storage->isClosedByReadAndClose(), 'After save — needs reopen for next write');

        // Second reopen+save cycle should also work
        $storage->reopenForWriting();
        $this->assertFalse($storage->isClosedByReadAndClose(), 'After second reopen');
        $this->assertEquals(PHP_SESSION_ACTIVE, session_status(), 'PHP session active after second reopen');

        $storage->save();
        $this->assertTrue($storage->isClosedByReadAndClose(), 'After second save');
    }

    /**
     * Test that multiple reopen+write+save cycles persist all data
     */
    public function testMultipleReopenWriteSaveCyclesPersistData(): void
    {
        $setupStorage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestMultiWrite',
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $setupSession = new Session($setupStorage, new AttributeBag('TestMultiWrite'));
        $setupSession->start();
        $sessionId = $setupSession->getId();
        $setupSession->save();

        // Open with read_and_close
        session_id($sessionId);
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestMultiWrite',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $session = new Session($storage, new AttributeBag('TestMultiWrite'));
        $session->start();

        // First write cycle
        $storage->reopenForWriting();
        $session->set('key1', 'value1');
        $session->save();

        // Second write cycle
        $storage->reopenForWriting();
        $session->set('key2', 'value2');
        $session->save();

        // Third write cycle
        $storage->reopenForWriting();
        $session->set('key3', 'value3');
        $session->save();

        // Verify all three values persisted
        session_id($sessionId);
        $verifyStorage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestMultiWrite',
            'read_and_close' => true,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);
        $verifySession = new Session($verifyStorage, new AttributeBag('TestMultiWrite'));
        $verifySession->start();

        $this->assertEquals('value1', $verifySession->get('key1'), 'First write persisted');
        $this->assertEquals('value2', $verifySession->get('key2'), 'Second write persisted');
        $this->assertEquals('value3', $verifySession->get('key3'), 'Third write persisted');
    }

    /**
     * Test that construction with read_and_close=false behaves like stock NativeSessionStorage
     */
    public function testReadAndCloseFalseBehavesLikeStock(): void
    {
        $storage = new ReadAndCloseNativeSessionStorage([
            'name' => 'TestFalseFlag',
            'read_and_close' => false,
            'use_cookies' => false,
            'use_only_cookies' => false,
        ]);

        $storage->start();

        $this->assertFalse(
            $storage->isClosedByReadAndClose(),
            'read_and_close=false should behave like normal session'
        );
        $this->assertEquals(
            PHP_SESSION_ACTIVE,
            session_status(),
            'PHP session should be ACTIVE with read_and_close=false'
        );
    }
}
