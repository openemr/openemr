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
use OpenEMR\Services\PatientReview\ReviewActor;
use OpenEMR\Services\PatientReview\ReviewQueueFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../library/dated_reminder_functions.php';

class PortalAlertCountsTest extends TestCase
{
    /** A patient id no fixture uses. */
    private const PID = 9913961;

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
    public function countsPendingReviewWithoutDoubleCountingPayments(): void
    {
        $before = GetPortalAlertCounts();

        $workflows = ReviewQueueFactory::portalWorkflows();
        $patient = ReviewActor::patient(self::PID, 'phpunit-portal-alert-counts');
        $staff = ReviewActor::user(1, 'admin');

        $workflows->submitPayment(self::PID, '{}', '', true, $patient);
        $workflows->submitProfileChange(self::PID, ['fname' => 'Ann'], $patient);
        // An invoice waits on the patient, not on staff: not counted.
        $workflows->requestInvoicePayment(self::PID, [], $staff);
        // A charted document is no longer pending: not counted.
        $documentId = QueryUtils::sqlInsert(
            'INSERT INTO `onsite_documents` (`pid`, `doc_type`, `patient_signed_status`, `accept_signed_status`,'
            . " `authorizing_signator`, `denial_reason`, `file_name`, `file_path`) VALUES (?, 'Consent', 0, 0, '', '', 'alert-counts-test', '')",
            [self::PID]
        );
        $workflows->submitDocument(self::PID, $documentId, 'Consent', $patient);
        $workflows->completeDocument(self::PID, $documentId, $staff);

        $after = GetPortalAlertCounts();

        $this->assertSame(2, $this->countValue($after, 'auditCnt') - $this->countValue($before, 'auditCnt'));
        $this->assertSame(1, $this->countValue($after, 'paymentCnt') - $this->countValue($before, 'paymentCnt'));
        $this->assertSame(2, $this->countValue($after, 'total') - $this->countValue($before, 'total'));
    }

    private function removeFixtures(): void
    {
        foreach (['patient_review_secret', 'patient_review_event'] as $table) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM ' . QueryUtils::escapeTableName($table)
                . ' WHERE `request_id` IN (SELECT `id` FROM `patient_review_request` WHERE `pid` = ?)',
                [self::PID]
            );
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM `patient_review_request` WHERE `pid` = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `onsite_documents` WHERE `pid` = ?', [self::PID]);
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
