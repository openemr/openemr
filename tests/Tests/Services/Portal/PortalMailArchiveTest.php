<?php

/**
 * Tests portal message archive handling.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Portal;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../portal/lib/portal_mail.inc.php';

class PortalMailArchiveTest extends TestCase
{
    private const OWNER = 'phpunit-portal-archive-owner';
    private const OTHER_OWNER = 'phpunit-portal-archive-other-owner';
    private const MAIL_CHAIN = 9913963;

    /** @var array<string, mixed> */
    private array $savedSessionValues = [];

    /** @var list<string> */
    private array $previouslyMissingSessionKeys = [];

    private bool $savedEnableAuditLog = false;

    protected function setUp(): void
    {
        $this->removeFixtures();

        $this->savedEnableAuditLog = (bool) ($GLOBALS['enable_auditlog'] ?? false);
        $GLOBALS['enable_auditlog'] = false;

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        foreach (['authUser', 'ptName', 'portal_username', 'patient_portal_onsite_two', 'pid'] as $key) {
            if ($session->has($key)) {
                $this->savedSessionValues[$key] = $session->get($key);
            } else {
                $this->previouslyMissingSessionKeys[] = $key;
            }
        }
        SessionUtil::setSession([
            'authUser' => 'phpunit-portal-auditor',
            'patient_portal_onsite_two' => false,
        ]);
    }

    protected function tearDown(): void
    {
        $this->removeFixtures();
        $GLOBALS['enable_auditlog'] = $this->savedEnableAuditLog;
        SessionUtil::setUnsetSession($this->savedSessionValues, $this->previouslyMissingSessionKeys);
    }

    #[Test]
    public function archiveByMailChainUpdatesOnlyMessagesOwnedByTheCaller(): void
    {
        $firstId = $this->insertMessage(self::OWNER, 'Portal Sender', 'Staff Recipient');
        $secondId = $this->insertMessage(self::OWNER, 'Staff Sender', 'Portal Recipient');
        $otherOwnerId = $this->insertMessage(self::OTHER_OWNER, 'Other Sender', 'Other Recipient');

        updatePortalMailMessageStatus(self::MAIL_CHAIN, 'Delete', self::OWNER);

        $archivedRows = QueryUtils::fetchRecords(
            'SELECT id, message_status, activity, deleted, delete_date FROM onsite_mail ' .
            'WHERE id IN (?, ?) ORDER BY id',
            [$firstId, $secondId],
        );

        $this->assertCount(2, $archivedRows);
        foreach ($archivedRows as $row) {
            $this->assertSame('Delete', $row['message_status']);
            $this->assertEquals(1, $row['activity']);
            $this->assertEquals(1, $row['deleted']);
            $this->assertNotEmpty($row['delete_date']);
        }

        $otherOwnerRow = QueryUtils::querySingleRow(
            'SELECT message_status, activity, deleted, delete_date FROM onsite_mail WHERE id = ?',
            [$otherOwnerId],
        );
        if (!is_array($otherOwnerRow)) {
            throw new \RuntimeException('Portal mail fixture for the other owner was not found');
        }

        $this->assertSame('New', $otherOwnerRow['message_status']);
        $this->assertEquals(1, $otherOwnerRow['activity']);
        $this->assertEquals(0, $otherOwnerRow['deleted']);
        $this->assertEmpty($otherOwnerRow['delete_date']);
    }

    private function insertMessage(string $owner, string $senderName, string $recipientName): int
    {
        return (int) QueryUtils::sqlInsert(
            'INSERT INTO onsite_mail ' .
            '(date, body, owner, user, groupname, authorized, activity, title, assigned_to, ' .
            'message_status, mail_chain, sender_id, sender_name, recipient_id, recipient_name, ' .
            'reply_mail_chain, deleted) ' .
            'VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                'PHPUnit portal archive message',
                $owner,
                'phpunit-portal-user',
                'Default',
                0,
                1,
                'PHPUnit portal archive',
                '',
                'New',
                self::MAIL_CHAIN,
                'phpunit-sender-id',
                $senderName,
                'phpunit-recipient-id',
                $recipientName,
                self::MAIL_CHAIN,
                0,
            ],
        );
    }

    private function removeFixtures(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM onsite_mail WHERE `owner` IN (?, ?)',
            [self::OWNER, self::OTHER_OWNER],
        );
    }
}
