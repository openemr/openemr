<?php

/**
 * Tests portal dashboard alert counts.
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

require_once __DIR__ . '/../../../../library/dated_reminder_functions.php';

class PortalAlertCountsTest extends TestCase
{
    private const CHECKSUM_PREFIX = 'phpunit-portal-alert-counts-13961-';

    private mixed $savedAuthUser = null;

    private bool $authUserWasPresent = false;

    protected function setUp(): void
    {
        $this->removeFixtures();

        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $this->authUserWasPresent = $session->has('authUser');
        $this->savedAuthUser = $session->get('authUser');
        SessionUtil::setSession('authUser', 'phpunit-portal-alert-counts-13961');
    }

    protected function tearDown(): void
    {
        $this->removeFixtures();
        if ($this->authUserWasPresent) {
            SessionUtil::setSession('authUser', $this->savedAuthUser);
        } else {
            SessionUtil::unsetSession('authUser');
        }
    }

    #[Test]
    public function countsOnlyAuditableWaitingActivityWithoutDoubleCountingPayments(): void
    {
        $before = GetPortalAlertCounts();

        $this->insertActivity('payment', 'waiting', 1, 'payment');
        $this->insertActivity('profile', 'waiting', 1, 'profile');
        $this->insertActivity('payment', 'waiting', 0, 'not-auditable');
        $this->insertActivity('payment', 'waiting-review', 1, 'not-exact-waiting');

        $after = GetPortalAlertCounts();

        $this->assertSame(2, $this->countValue($after, 'auditCnt') - $this->countValue($before, 'auditCnt'));
        $this->assertSame(1, $this->countValue($after, 'paymentCnt') - $this->countValue($before, 'paymentCnt'));
        $this->assertSame(2, $this->countValue($after, 'total') - $this->countValue($before, 'total'));
    }

    private function insertActivity(string $activity, string $status, int $requireAudit, string $suffix): void
    {
        QueryUtils::sqlInsert(
            'INSERT INTO onsite_portal_activity ' .
            '(date, patient_id, activity, require_audit, pending_action, action_taken, status, narrative, ' .
            'table_action, table_args, action_user, action_taken_time, checksum) ' .
            'VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                9913961,
                $activity,
                $requireAudit,
                '',
                '',
                $status,
                'PHPUnit portal alert count fixture',
                '',
                '',
                '',
                null,
                self::CHECKSUM_PREFIX . $suffix,
            ],
        );
    }

    private function removeFixtures(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM onsite_portal_activity WHERE checksum LIKE ?',
            [self::CHECKSUM_PREFIX . '%'],
        );
    }

    /**
     * @param array<string, mixed> $counts
     */
    private function countValue(array $counts, string $key): int
    {
        $value = $counts[$key] ?? null;
        if (!is_numeric($value)) {
            throw new \RuntimeException("Portal alert count '$key' is not numeric");
        }

        return (int) $value;
    }
}
