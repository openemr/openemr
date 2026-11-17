<?php

/**
 * Isolated tests for parsing direct_message_log rows into DirectMessageLogRecord.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\DirectMessaging;

use OpenEMR\Common\DirectMessaging\DirectMessageLogRecord;
use OpenEMR\Common\DirectMessaging\DirectMessageLogStatus;
use OpenEMR\Common\DirectMessaging\DirectMessageType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DirectMessageLogRecordTest extends TestCase
{
    public function testParsesARowAsTheDatabaseReturnsIt(): void
    {
        $record = DirectMessageLogRecord::fromRow(self::row());

        self::assertEquals(
            new DirectMessageLogRecord(
                id: 12,
                type: DirectMessageType::Sent,
                createdAt: '2026-01-05 09:00:00',
                sender: 'sender@direct.example',
                recipient: 'recipient@direct.example',
                status: DirectMessageLogStatus::Dispatched,
                statusChangedAt: '2026-01-05 09:30:00',
            ),
            $record,
        );
    }

    public function testParsesAnIntIdAndANullStatusTimestamp(): void
    {
        $record = DirectMessageLogRecord::fromRow(self::row(['id' => 12, 'status_ts' => null]));

        self::assertSame(12, $record->id);
        self::assertNull($record->statusChangedAt);
    }

    public function testParsesUnknownCodesAsNull(): void
    {
        $record = DirectMessageLogRecord::fromRow(self::row(['msg_type' => 'X', 'status' => 'Z']));

        self::assertNull($record->type);
        self::assertNull($record->status);
        self::assertNull($record->typeLabel());
        self::assertNull($record->statusLabel());
    }

    /**
     * @param array<string, mixed> $overrides
     */
    #[DataProvider('malformedRowProvider')]
    public function testRejectsAMalformedRow(array $overrides): void
    {
        $this->expectException(\UnexpectedValueException::class);

        DirectMessageLogRecord::fromRow(self::row($overrides));
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function malformedRowProvider(): array
    {
        return [
            'missing id' => [['id' => null]],
            'non-numeric id' => [['id' => '12a']],
            'missing msg_type' => [['msg_type' => null]],
            'missing create_ts' => [['create_ts' => null]],
            'missing sender' => [['sender' => null]],
            'missing recipient' => [['recipient' => null]],
            'missing status' => [['status' => null]],
            'non-string status_ts' => [['status_ts' => 5]],
        ];
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private static function row(array $overrides = []): array
    {
        return array_merge([
            'id' => '12',
            'msg_type' => 'S',
            'create_ts' => '2026-01-05 09:00:00',
            'sender' => 'sender@direct.example',
            'recipient' => 'recipient@direct.example',
            'status' => 'D',
            'status_ts' => '2026-01-05 09:30:00',
        ], $overrides);
    }
}
