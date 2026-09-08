<?php

/**
 * Dispatches PatientCreatedEvent for legacy create paths that bypass
 * PatientService::databaseInsert().
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Events\Patient;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class PatientCreatedEventNotifier
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Dispatch PatientCreatedEvent for the freshly-created patient identified by $pid.
     *
     * $patientRow is the result of re-reading the patient_data row after the
     * legacy insert (newPatientData()) returned. Pass `false` to indicate the
     * row was not found — the canonical signal QueryUtils::querySingleRow()
     * uses for "no row." A missing row means the just-completed insert
     * vanished between write and re-read; we log it so the silently-skipped
     * lifecycle event surfaces in operator dashboards.
     *
     * @param array<mixed>|false $patientRow
     */
    public function notify(int $pid, array|false $patientRow): void
    {
        if ($patientRow === false) {
            $this->logger->error(
                'PatientCreatedEvent skipped: patient_data row missing immediately after newPatientData()',
                ['pid' => $pid]
            );
            return;
        }

        $this->dispatcher->dispatch(
            new PatientCreatedEvent($patientRow),
            PatientCreatedEvent::EVENT_HANDLE
        );
    }
}
