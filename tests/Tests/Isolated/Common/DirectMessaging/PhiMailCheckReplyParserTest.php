<?php

/**
 * Isolated tests for parsing the phiMail server's reply to CHECK.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\DirectMessaging;

use OpenEMR\Common\DirectMessaging\PhiMailCheckReplyParser;
use OpenEMR\Common\DirectMessaging\PhiMailDeliveryStatus;
use OpenEMR\Common\DirectMessaging\PhiMailMailReply;
use OpenEMR\Common\DirectMessaging\PhiMailNoneReply;
use OpenEMR\Common\DirectMessaging\PhiMailStatusReply;
use OpenEMR\Common\DirectMessaging\PhiMailUnexpectedResponseException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PhiMailCheckReplyParserTest extends TestCase
{
    public function testParsesNone(): void
    {
        self::assertInstanceOf(PhiMailNoneReply::class, PhiMailCheckReplyParser::parse("NONE\n"));
    }

    /**
     * @return array<string, array{string, PhiMailStatusReply}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function statusProvider(): array
    {
        return [
            'dispatched' => [
                "STATUS msg-1 dispatched\n",
                new PhiMailStatusReply('msg-1', PhiMailDeliveryStatus::Dispatched, '', "STATUS msg-1 dispatched\n"),
            ],
            'failed, with information' => [
                "STATUS msg-2 failed recipient address rejected\n",
                new PhiMailStatusReply('msg-2', PhiMailDeliveryStatus::Failed, 'recipient address rejected', "STATUS msg-2 failed recipient address rejected\n"),
            ],
            'unrecognized code' => [
                "STATUS msg-3 delayed\n",
                new PhiMailStatusReply('msg-3', PhiMailDeliveryStatus::Unknown, '', "STATUS msg-3 delayed\n"),
            ],
        ];
    }

    #[DataProvider('statusProvider')]
    public function testParsesStatus(string $line, PhiMailStatusReply $expected): void
    {
        self::assertEquals($expected, PhiMailCheckReplyParser::parse($line));
    }

    public function testParsesMail(): void
    {
        $line = "MAIL to@direct.example from@direct.example 2 msg-4\n";

        self::assertEquals(
            new PhiMailMailReply('to@direct.example', 'from@direct.example', 2, 'msg-4', $line),
            PhiMailCheckReplyParser::parse($line),
        );
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unexpectedProvider(): array
    {
        return [
            'FAIL' => ["FAIL not authenticated\n"],
            'empty' => [''],
            'NONE without a line feed' => ['NONE'],
            'unknown keyword' => ["BOGUS\n"],
            'STATUS without a code' => ["STATUS msg-1\n"],
            'STATUS without a message ID' => ["STATUS  dispatched\n"],
            'STATUS with an empty code' => ["STATUS msg-1  recipient rejected\n"],
            'MAIL with an empty recipient' => ["MAIL  from@direct.example 0 msg-4\n"],
            'MAIL with an empty sender' => ["MAIL to@direct.example  0 msg-4\n"],
            'MAIL with an extra field' => ["MAIL to@direct.example from@direct.example 0 msg-4 extra\n"],
            'MAIL without a message ID' => ["MAIL to@direct.example from@direct.example 0\n"],
            'MAIL with an empty message ID' => ["MAIL to@direct.example from@direct.example 0 \n"],
            'MAIL with a non-numeric count' => ["MAIL to@direct.example from@direct.example two msg-4\n"],
        ];
    }

    #[DataProvider('unexpectedProvider')]
    public function testRejectsAnythingElse(string $line): void
    {
        try {
            PhiMailCheckReplyParser::parse($line);
            self::fail('parse() accepted ' . var_export($line, true));
        } catch (PhiMailUnexpectedResponseException $e) {
            self::assertSame($line, $e->response);
        }
    }
}
