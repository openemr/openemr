<?php

/**
 * Tests that MedEx Practice::sync() only retires queued messages once MedEx confirms their removal.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\MedEx;

use MedExApi\Practice;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../library/MedEx/API.php';

class MedExSyncWithdrawTest extends TestCase
{
    /** Four digits: medex_outgoing.msg_pc_eid is varchar(11), so "recall_" leaves room for four. */
    private const TEST_PID = 9913;

    private const TEST_USERNAME = 'phpunit-medex-sync';

    /** pc_eid of the cancelled appointment fixture. */
    private int $cancelledEid = 0;

    private bool $insertedPrefs = false;

    protected function setUp(): void
    {
        $this->removeFixtures();

        if (QueryUtils::fetchSingleValue('SELECT COUNT(*) AS c FROM medex_prefs', 'c') == 0) {
            QueryUtils::sqlInsert('INSERT INTO medex_prefs (MedEx_id, ME_username, ME_providers, ME_facilities) VALUES (42, ?, ?, ?)', [self::TEST_USERNAME, '', '']);
            $this->insertedPrefs = true;
        }

        // a cancelled appointment with a reminder still queued for it
        $this->cancelledEid = (int) QueryUtils::sqlInsert(
            "INSERT INTO openemr_postcalendar_events (pc_pid, pc_eventDate, pc_startTime, pc_apptstatus, pc_multiple) VALUES (?, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', 'x', 0)",
            [self::TEST_PID]
        );
        $this->queueMessage((string) $this->cancelledEid);

        // a recall whose patient has since booked an appointment
        QueryUtils::sqlInsert(
            "INSERT INTO openemr_postcalendar_events (pc_pid, pc_eventDate, pc_startTime, pc_apptstatus, pc_multiple) VALUES (?, DATE_ADD(CURDATE(), INTERVAL 10 DAY), '10:00:00', '-', 0)",
            [self::TEST_PID]
        );
        $this->queueMessage('recall_' . self::TEST_PID);
    }

    protected function tearDown(): void
    {
        $this->removeFixtures();
        if ($this->insertedPrefs) {
            QueryUtils::sqlStatementThrowException('DELETE FROM medex_prefs WHERE ME_username = ?', [self::TEST_USERNAME]);
        }
    }

    #[Test]
    public function retiresTheMessagesOnceMedExConfirmsTheirRemoval(): void
    {
        $curl = $this->sync(['found_replies' => '0']);

        $this->assertSame(['DONE', 'SCHEDULED'], $this->replies());
        $this->assertSame([(string) $this->cancelledEid, 'recall_' . self::TEST_PID], $curl->removalRequest);
    }

    /**
     * Replies to remMessaging that don't confirm the removal.
     *
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unconfirmedRemovalProvider(): array
    {
        return [
            'MedEx returns an error' => [['error' => 'remove failed']],
            'no usable reply' => [null],
        ];
    }

    #[Test]
    #[DataProvider('unconfirmedRemovalProvider')]
    public function keepsTheMessagesQueuedSoTheNextSyncRetries(mixed $removalReply): void
    {
        $this->sync($removalReply);

        $this->assertSame(['To Send', 'To Send'], $this->replies());
    }

    /**
     * Runs sync() against a scripted MedEx, failing on any PHP warning or notice
     * it raises; phpunit.xml only reports those.
     *
     * @return object{removalRequest: mixed}
     */
    private function sync(mixed $removalReply): object
    {
        $curl = new class ($removalReply) {
            public mixed $removalRequest = null;

            private string $url = '';

            private mixed $data = null;

            public function __construct(private readonly mixed $removalReply)
            {
            }

            public function setUrl(string $url): void
            {
                $this->url = $url;
            }

            public function setData(mixed $data): void
            {
                $this->data = $data;
            }

            public function makeRequest(): void
            {
                if (str_contains($this->url, 'remMessaging')) {
                    $this->removalRequest = $this->data;
                }
            }

            public function getResponse(): mixed
            {
                return match (true) {
                    str_contains($this->url, 'remMessaging') => $this->removalReply,
                    str_contains($this->url, 'sync_responses') => ['messages' => []],
                    default => [],
                };
            }
        };
        $medex = new class ($curl) {
            public function __construct(public object $curl)
            {
            }

            public function getUrl(string $path): string
            {
                return 'https://medex.invalid/' . $path;
            }
        };

        $raised = [];
        set_error_handler(function (int $errno, string $message, string $file, int $line) use (&$raised): bool {
            $raised[] = $message . ' at ' . basename($file) . ':' . $line;
            return true;
        });
        try {
            (new Practice($medex))->sync('phpunit-token');
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $raised);
        return $curl;
    }

    /**
     * msg_reply of the cancelled appointment's message, then the recall's.
     *
     * @return list<mixed>
     */
    private function replies(): array
    {
        return [
            QueryUtils::fetchSingleValue('SELECT msg_reply FROM medex_outgoing WHERE msg_pc_eid = ?', 'msg_reply', [(string) $this->cancelledEid]),
            QueryUtils::fetchSingleValue('SELECT msg_reply FROM medex_outgoing WHERE msg_pc_eid = ?', 'msg_reply', ['recall_' . self::TEST_PID]),
        ];
    }

    private function queueMessage(string $pcEid): void
    {
        QueryUtils::sqlInsert(
            "INSERT INTO medex_outgoing (msg_pid, msg_pc_eid, campaign_uid, msg_type, msg_reply) VALUES (?, ?, 1, 'SMS', 'To Send')",
            [self::TEST_PID, $pcEid]
        );
    }

    private function removeFixtures(): void
    {
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM medex_outgoing WHERE msg_pid = ?",
            [self::TEST_PID]
        );
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM openemr_postcalendar_events WHERE pc_pid = ?',
            [self::TEST_PID]
        );
    }
}
