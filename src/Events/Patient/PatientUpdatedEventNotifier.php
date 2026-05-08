<?php

/**
 * Dispatches PatientUpdatedEvent for legacy update paths that bypass
 * PatientService::databaseUpdate().
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

final readonly class PatientUpdatedEventNotifier
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Dispatch PatientUpdatedEvent for the patient identified by $pid.
     *
     * $dataBeforeUpdate is the result of getPatientData($pid) captured by
     * the caller *before* the legacy update (updatePatientData()) ran. Pass
     * `false` to indicate the row was not found — the canonical signal
     * sqlQuery() uses for "no row." A missing pre-update row means the
     * caller asked us to announce an update for a patient that did not
     * exist; we log it so the silently-skipped lifecycle event surfaces in
     * operator dashboards and skip dispatch (PatientUpdatedEvent's contract
     * is that subscribers receive a real pre-update snapshot).
     *
     * @param array<mixed>|false $dataBeforeUpdate
     * @param array<mixed> $newPatientData
     */
    public function notify(int $pid, array|false $dataBeforeUpdate, array $newPatientData): void
    {
        if ($dataBeforeUpdate === false) {
            $this->logger->error(
                'PatientUpdatedEvent skipped: pre-update patient_data row missing',
                ['pid' => $pid]
            );
            return;
        }

        $this->dispatcher->dispatch(
            new PatientUpdatedEvent($dataBeforeUpdate, $newPatientData),
            PatientUpdatedEvent::EVENT_HANDLE
        );
    }
}
