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

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM direct_message_log WHERE msg_id = ?', [$this->messageId]);
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

    private function insertSent(?int $patientId, ?int $userId): void
    {
        $sql = <<<'SQL'
            INSERT INTO direct_message_log (msg_type, msg_id, sender, recipient, status, patient_id, user_id)
            VALUES ('S', ?, 'from@direct.example', 'to@direct.example', 'Q', ?, ?)
            SQL;
        QueryUtils::sqlStatementThrowException($sql, [$this->messageId, $patientId, $userId]);
    }
}
