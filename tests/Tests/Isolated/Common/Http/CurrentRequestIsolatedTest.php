<?php

/**
 * Isolated CurrentRequest Test
 *
 * Exercises the process-wide request holder without a database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Http;

use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Http\HttpRestRequest;
use PHPUnit\Framework\TestCase;

class CurrentRequestIsolatedTest extends TestCase
{
    /**
     * Saved verbatim and restored verbatim; never indexed, so the superglobals'
     * own `array<mixed>` shape is the honest type here.
     *
     * @var array<mixed>
     */
    private array $originalGet;

    /** @var array<mixed> */
    private array $originalServer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalGet = $_GET;
        $this->originalServer = $_SERVER;
        CurrentRequest::reset();
    }

    protected function tearDown(): void
    {
        CurrentRequest::reset();
        $_GET = $this->originalGet;
        $_SERVER = $this->originalServer;

        parent::tearDown();
    }

    public function testHasIsFalseUntilARequestIsEstablished(): void
    {
        $this->assertFalse(CurrentRequest::has());
    }

    public function testGetBuildsARequestFromTheSuperglobalsOnFirstUse(): void
    {
        $_GET = ['patient' => '42'];

        $request = CurrentRequest::get();

        $this->assertSame('42', $request->query->getString('patient'));
        $this->assertTrue(CurrentRequest::has(), 'get() should establish the request it built');
    }

    /**
     * The whole point of the holder: one instance per process. A second call
     * must not rebuild, because createFromGlobals() rewrites $_GET/$_SERVER on
     * the way through and the rebuilt request would not match the first.
     */
    public function testGetReturnsTheSameInstanceOnRepeatCalls(): void
    {
        $first = CurrentRequest::get();
        $second = CurrentRequest::get();

        $this->assertSame($first, $second);
    }

    public function testGetReturnsTheInstanceAnEntryPointPublished(): void
    {
        $published = new HttpRestRequest(['site' => 'default']);

        CurrentRequest::set($published);

        $this->assertSame($published, CurrentRequest::get());
        $this->assertTrue(CurrentRequest::has());
    }

    /**
     * Mirrors the REST path: the dispatcher publishes a request, the stack
     * mutates it, and a later reader — globals.php — must observe those
     * mutations rather than a freshly built stand-in.
     */
    public function testMutationsMadeAfterPublishingAreVisibleToLaterReaders(): void
    {
        $published = new HttpRestRequest();
        CurrentRequest::set($published);

        $published->setApiType('fhir');
        $published->setIsLocalApi(true);

        $observed = CurrentRequest::get();
        $this->assertSame('fhir', $observed->getApiType());
        $this->assertTrue($observed->isLocalApi());
    }

    public function testSetReplacesAPreviouslyEstablishedRequest(): void
    {
        $lazilyBuilt = CurrentRequest::get();
        $published = new HttpRestRequest();

        CurrentRequest::set($published);

        $this->assertNotSame($lazilyBuilt, CurrentRequest::get());
        $this->assertSame($published, CurrentRequest::get());
    }

    public function testResetDropsTheRequestSoTheNextGetRebuilds(): void
    {
        $first = CurrentRequest::get();

        CurrentRequest::reset();

        $this->assertFalse(CurrentRequest::has());
        $this->assertNotSame($first, CurrentRequest::get());
    }
}
