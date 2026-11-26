<?php

/**
 * Locks the $skipAuditLog opt-out on EventAuditLogger::auditSQLEvent (#15422).
 *
 * Both the ADOdb path and the DBAL QueryAuditor middleware funnel through
 * auditSQLEvent(), so the skip gate lives there and covers both. The flag is
 * read through OEGlobalsBag (no `global` keyword).
 *
 * EventAuditLogger::createInstance() opens a dedicated database connection,
 * so the test bypasses the constructor (the documented isolated-test pattern
 * from FhirObservationVitalsUcumTest). The skip gate runs before any instance
 * state is read, so a constructor-bypassed instance is safe for the skip path.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve-Wiig
 * @copyright Copyright (c) 2026 Steve-Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Logging;

use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\TestCase;

class EventAuditLoggerSkipAuditTest extends TestCase
{
    private EventAuditLogger $logger;

    protected function setUp(): void
    {
        $this->logger = (new \ReflectionClass(EventAuditLogger::class))->newInstanceWithoutConstructor();
    }

    protected function tearDown(): void
    {
        OEGlobalsBag::getInstance()->set('skipAuditLog', false);
    }

    public function testSkipFlagSuppressesSqlAudit(): void
    {
        OEGlobalsBag::getInstance()->set('skipAuditLog', true);
        $this->logger->auditSQLEvent('UPDATE patient_data SET fname = ? WHERE pid = ?', true, ['x', 1]);
        $this->addToAssertionCount(1); // returned without entering the logging path
    }

    public function testWithoutSkipFlagTheLoggingPathIsEntered(): void
    {
        // Canary for the test above: without the flag the same call proceeds
        // into the logging path, which reads session state that does not
        // exist on a constructor-bypassed instance, so it must fail. If this
        // ever stops throwing, the skip test above has become vacuous.
        OEGlobalsBag::getInstance()->set('skipAuditLog', false);
        $this->expectException(\Throwable::class);
        $this->logger->auditSQLEvent('UPDATE patient_data SET fname = ? WHERE pid = ?', true, ['x', 1]);
    }
}