<?php

/**
 * Isolated tests for the legacy phiMail protocol helpers that ccr/transmitCCD.php calls.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\library;

use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\TestCase;

#[BackupGlobals(true)]
final class DirectMessageHelpersTest extends TestCase
{
    /** @var resource */
    private $client;

    /** @var resource */
    private $server;

    public static function setUpBeforeClass(): void
    {
        $projectDir = dirname(__DIR__, 4);
        OEGlobalsBag::getInstance()->set('fileroot', $projectDir);
        require_once $projectDir . '/library/translation.inc.php';
        require_once $projectDir . '/library/direct_message_check.inc.php';
    }

    protected function setUp(): void
    {
        $pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if ($pair === false) {
            // @codeCoverageIgnoreStart
            // Defensive — socket pairs are always available on the platforms CI runs.
            self::fail('could not create a socket pair');
            // @codeCoverageIgnoreEnd
        }
        [$this->client, $this->server] = $pair;
    }

    public function testWriteExpectOkReturnsTrueOnOk(): void
    {
        fwrite($this->server, "OK\n");

        self::assertTrue(phimail_write_expect_OK($this->client, "TO someone@direct.example\n"));
        self::assertSame("TO someone@direct.example\n", fgets($this->server));
    }

    public function testWriteExpectOkReturnsAnyOtherReply(): void
    {
        fwrite($this->server, "FAIL not allowed\n");

        self::assertSame("FAIL not allowed\n", phimail_write_expect_OK($this->client, "TO someone@direct.example\n"));
    }

    public function testWriteExpectOkReturnsFalseWhenTheServerHasHungUp(): void
    {
        fclose($this->server);

        self::assertFalse(phimail_write_expect_OK($this->client, "SEND\n"));
    }

    public function testWriteIgnoresAHungUpServer(): void
    {
        fclose($this->server);

        phimail_write($this->client, "SEND\n");

        self::assertFalse(fgets($this->client));
    }

    public function testCloseClosesTheStream(): void
    {
        phimail_close($this->client);

        self::assertFalse(fgets($this->server));
    }
}
