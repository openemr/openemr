<?php

/**
 * Database tests for the direct_message_log repository.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\DirectMessaging;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\DirectMessaging\DirectMessageLogRepository;
use OpenEMR\Common\DirectMessaging\DirectMessageLogStatus;
use OpenEMR\Common\DirectMessaging\DirectMessageType;
use OpenEMR\Common\DirectMessaging\PhiMailDeliveryStatus;
use PHPUnit\Framework\TestCase;

final class DirectMessageLogRepositoryTest extends TestCase
{
    private string $messageId;

    private DirectMessageLogRepository $repository;

    protected function setUp(): void
    {
        $this->messageId = uniqid('phimail-repository-test-', true);
        $this->repository = new DirectMessageLogRepository();
    }

    /**
     * Removes the rows each test logged.
     */
    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM direct_message_log WHERE msg_id IN (?, ?, ?)', [$this->messageId, '<' . $this->messageId . '>', $this->messageId . '-other']);
    }

    /**
     * Records the referral a sent message carries.
     */
    public function testRecordsTheReferralASentMessageCarries(): void
    {
        $this->repository->recordSent($this->messageId, 'from@direct.example', 'to@direct.example', 42, 3, 7);

        self::assertEquals(7, QueryUtils::fetchSingleValue('SELECT referral_id FROM direct_message_log WHERE msg_id = ?', 'referral_id', [$this->messageId]));
    }

    /**
     * Records where a received reply was filed.
     */
    public function testRecordsWhereAReceivedReplyWasFiled(): void
    {
        $this->repository->recordReceived($this->messageId, 'from@direct.example', 'to@direct.example', 7, 42, 9, 501);

        $row = QueryUtils::querySingleRow('SELECT patient_id, referral_id, note_id FROM direct_message_log WHERE msg_id = ?', [$this->messageId]);
        self::assertEquals(['patient_id' => 42, 'referral_id' => 9, 'note_id' => 501], $row);
    }

    /**
     * A reply is matched to the sent message it answers.
     */
    public function testAReplyIsMatchedToTheSentMessageItAnswers(): void
    {
        $this->repository->recordSent($this->messageId, 'from@direct.example', 'to@direct.example', 42, 3, 7);

        $target = $this->repository->findReplyTarget(['unknown@direct.example', $this->messageId], 'To@Direct.example');

        self::assertNotNull($target);
        self::assertSame([$this->messageId, 42, 7], [$target->messageId, $target->patientId, $target->referralId]);
    }

    /**
     * An id logged in angle brackets still matches.
     */
    public function testAnIdLoggedInAngleBracketsStillMatches(): void
    {
        $this->repository->recordSent('<' . $this->messageId . '>', 'from@direct.example', 'to@direct.example', 42, 3, null);

        $target = $this->repository->findReplyTarget([$this->messageId], 'to@direct.example');

        self::assertNotNull($target);
        self::assertSame(['<' . $this->messageId . '>', 42, null], [$target->messageId, $target->patientId, $target->referralId]);
    }

    /**
     * The nearest id wins.
     */
    public function testTheNearestIdWins(): void
    {
        $this->repository->recordSent($this->messageId, 'from@direct.example', 'to@direct.example', 42, 3, 7);
        $this->repository->recordSent($this->messageId . '-other', 'from@direct.example', 'to@direct.example', 43, 3, 8);

        $target = $this->repository->findReplyTarget([$this->messageId . '-other', $this->messageId], 'to@direct.example');

        self::assertNotNull($target);
        self::assertSame(43, $target->patientId);
    }

    /**
     * Only a message sent about a patient is a reply's target.
     */
    public function testOnlyAMessageSentAboutAPatientIsAReplysTarget(): void
    {
        $this->repository->recordSent($this->messageId, 'from@direct.example', 'to@direct.example', null, 3);
        $this->repository->recordReceived($this->messageId . '-other', 'from@direct.example', 'to@direct.example', 7, 42, 9, 501);

        self::assertNull($this->repository->findReplyTarget([$this->messageId, $this->messageId . '-other'], 'to@direct.example'));
        self::assertNull($this->repository->findReplyTarget([], 'to@direct.example'));
    }

    /**
     * Message ids aren't secret: a message from anyone but the one it was sent to isn't a reply to it.
     */
    public function testAReplyFromAnotherAddressIsNotMatched(): void
    {
        $this->repository->recordSent($this->messageId, 'from@direct.example', 'to@direct.example', 42, 3, 7);

        self::assertNull($this->repository->findReplyTarget([$this->messageId], 'someone-else@direct.example'));
        self::assertNull($this->repository->findReplyTarget([$this->messageId], ' '));
    }

    public function testFindsASentMessage(): void
    {
        $this->insertSent(patientId: 42, userId: 3);

        $entry = $this->repository->findByMessageId($this->messageId);

        self::assertNotNull($entry);
        self::assertSame([42, 3, 'to@direct.example'], [$entry->patientId, $entry->userId, $entry->recipient]);
        self::assertNotSame('', $entry->createdAt);
    }

    public function testFindsASentMessageWithoutPatientOrUser(): void
    {
        $this->insertSent(patientId: null, userId: null);

        $entry = $this->repository->findByMessageId($this->messageId);

        self::assertNotNull($entry);
        self::assertNull($entry->patientId);
        self::assertNull($entry->userId);
    }

    public function testFindsNothingForAnUnknownMessage(): void
    {
        self::assertNull($this->repository->findByMessageId($this->messageId));
    }

    public function testUpdatesTheStatusOfASentMessage(): void
    {
        $this->insertSent(patientId: 42, userId: 3);

        $this->repository->updateStatus($this->messageId, PhiMailDeliveryStatus::Failed, 'recipient rejected');

        $row = QueryUtils::querySingleRow('SELECT status, status_info, status_ts FROM direct_message_log WHERE msg_id = ?', [$this->messageId]);
        self::assertIsArray($row);
        self::assertSame(['F', 'recipient rejected'], [$row['status'], $row['status_info']]);
        self::assertNotNull($row['status_ts']);
    }

    public function testRecordsAReceivedMessage(): void
    {
        $this->repository->recordReceived($this->messageId, 'from@direct.example', 'to@direct.example', 7);

        $row = QueryUtils::querySingleRow(
            'SELECT msg_type, sender, recipient, status, user_id FROM direct_message_log WHERE msg_id = ?',
            [$this->messageId],
        );
        self::assertIsArray($row);
        // The database returns numbers as strings, so compare loosely.
        self::assertEquals(
            ['msg_type' => 'R', 'sender' => 'from@direct.example', 'recipient' => 'to@direct.example', 'status' => 'R', 'user_id' => 7],
            $row,
        );
    }

    public function testRecordsASentMessage(): void
    {
        $this->repository->recordSent($this->messageId, 'from@direct.example', 'to@direct.example', 42, 3);

        $row = QueryUtils::querySingleRow(
            'SELECT msg_type, sender, recipient, status, patient_id, user_id FROM direct_message_log WHERE msg_id = ?',
            [$this->messageId],
        );
        self::assertIsArray($row);
        // The database returns numbers as strings, so compare loosely.
        self::assertEquals(
            ['msg_type' => 'S', 'sender' => 'from@direct.example', 'recipient' => 'to@direct.example', 'status' => 'S', 'patient_id' => 42, 'user_id' => 3],
            $row,
        );
    }

    public function testRecordsASentMessageWithoutPatientOrUser(): void
    {
        $this->repository->recordSent($this->messageId, 'from@direct.example', 'to@direct.example', null, null);

        $entry = $this->repository->findByMessageId($this->messageId);

        self::assertNotNull($entry);
        self::assertNull($entry->patientId);
        self::assertNull($entry->userId);
    }

    public function testFindsTheLatestMessagesNewestFirst(): void
    {
        $this->insertSent(patientId: null, userId: null);
        $this->repository->recordReceived($this->messageId, 'from@direct.example', 'to@direct.example', 7);

        $records = $this->repository->findLatest(2);

        self::assertCount(2, $records);
        [$received, $sent] = $records;
        self::assertGreaterThan($sent->id, $received->id);

        self::assertSame([DirectMessageType::Received, DirectMessageLogStatus::Received], [$received->type, $received->status]);
        self::assertNotNull($received->statusChangedAt);

        self::assertSame(
            [DirectMessageType::Sent, DirectMessageLogStatus::Queued, 'from@direct.example', 'to@direct.example', null],
            [$sent->type, $sent->status, $sent->sender, $sent->recipient, $sent->statusChangedAt],
        );
        self::assertNotSame('', $sent->createdAt);
    }

    public function testFindsMessagesInAnIdRangeNewestFirst(): void
    {
        $this->insertSent(patientId: null, userId: null);
        $this->insertSent(patientId: null, userId: null);
        $this->insertSent(patientId: null, userId: null);
        $ids = $this->insertedIds();

        $records = $this->repository->findByIdRange($ids[0], $ids[1]);

        self::assertSame([$ids[1], $ids[0]], array_map(static fn ($record): int => $record->id, $records));
    }

    /**
     * @return list<int> the ids of this test's rows, oldest first
     */
    private function insertedIds(): array
    {
        $rows = QueryUtils::fetchRecords('SELECT id FROM direct_message_log WHERE msg_id = ? ORDER BY id', [$this->messageId]);
        $ids = [];
        foreach ($rows as $row) {
            $id = $row['id'] ?? null;
            self::assertIsNumeric($id);
            $ids[] = (int) $id;
        }
        return $ids;
    }

    private function insertSent(?int $patientId, ?int $userId): void
    {
        $sql = <<<'SQL'
            INSERT INTO direct_message_log (msg_type, msg_id, sender, recipient, status, patient_id, user_id)
            VALUES ('S', ?, 'from@direct.example', 'to@direct.example', 'Q', ?, ?)
            SQL;
        QueryUtils::sqlStatementThrowException($sql, [$this->messageId, $patientId, $userId]);
    }
}
