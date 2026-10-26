<?php

/**
 * Database test: Billing Manager claim processing audits each claim's
 * statements against that claim's patient, not the chart open in the session.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\BillingProcessor\BillingProcessor;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\TestCase;

class BillingProcessorAuditPatientTest extends TestCase
{
    /** Synthetic ids, clear of the demo data. */
    private const OPEN_CHART_PID = 99990041;
    private const CLAIM_PID = 99990042;
    private const CLAIM_ENCOUNTER = 99990142;

    /** @var array<string, mixed> */
    private array $savedGlobals = [];

    protected function setUp(): void
    {
        $globals = OEGlobalsBag::getInstance();
        foreach (['enable_auditlog', 'audit_events_patient-record'] as $name) {
            $this->savedGlobals[$name] = $globals->get($name);
            $globals->set($name, '1');
        }
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->set('authUser', 'admin');
        $session->set('authUserID', QueryUtils::fetchSingleValue('SELECT id FROM users WHERE username = ?', 'id', ['admin']));
        $session->set('pid', self::OPEN_CHART_PID);
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
        foreach ($this->savedGlobals as $name => $value) {
            OEGlobalsBag::getInstance()->set($name, $value);
        }
        SessionWrapperFactory::getInstance()->getActiveSession()->remove('pid');
    }

    public function testEachClaimIsAuditedAgainstItsOwnPatient(): void
    {
        $before = $this->lastLogId();

        // Billing Manager: "Mark as Cleared" on the other patient's claim.
        (new BillingProcessor([
            'bn_mark' => '1',
            'claims' => [self::CLAIM_PID . '-' . self::CLAIM_ENCOUNTER => ['bill' => '1', 'partner' => '-1', 'payer' => 'p0']],
        ]))->execute();

        $patients = QueryUtils::fetchTableColumn(
            "SELECT DISTINCT patient_id FROM log WHERE id > ? AND event LIKE 'patient-record-%'",
            'patient_id',
            [$before]
        );
        $this->assertNotEmpty($patients, 'Marking the claim cleared writes patient-record audit events');
        $this->assertSame([(string) self::CLAIM_PID], array_map(fn(mixed $id): string => is_scalar($id) ? (string) $id : '', $patients), "The claim's patient, not the open chart");

        // After the batch, statements fall back to the session's open chart again.
        $after = $this->lastLogId();
        QueryUtils::sqlStatementThrowException('UPDATE billing SET activity = activity WHERE pid = ?', [self::CLAIM_PID]);
        $patient = QueryUtils::fetchSingleValue(
            "SELECT patient_id FROM log WHERE id > ? AND event LIKE 'patient-record-%' ORDER BY id DESC LIMIT 1",
            'patient_id',
            [$after]
        );
        $this->assertSame((string) self::OPEN_CHART_PID, is_scalar($patient) ? (string) $patient : null);
    }

    private function lastLogId(): int
    {
        $id = QueryUtils::fetchSingleValue('SELECT COALESCE(MAX(id), 0) AS id FROM log', 'id');
        $this->assertIsNumeric($id);
        return (int) $id;
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM claims WHERE patient_id = ? AND encounter_id = ?',
            [self::CLAIM_PID, self::CLAIM_ENCOUNTER]
        );
    }
}
