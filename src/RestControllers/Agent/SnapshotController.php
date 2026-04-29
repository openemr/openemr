<?php

declare(strict_types=1);

/**
 * Agent Snapshot Read API.
 *
 * Exposes the denormalised patient snapshot to the Clinical Co-Pilot
 * sidecar in a single call, wrapping the FHIR fan-out + reconciliation
 * pass that the sidecar would otherwise issue resource-by-resource.
 *
 * Authentication: mTLS plus a signed JSON Web Token (JWT) minted by the
 * Backend-for-Frontend (BFF). The controller is intentionally read-only;
 * any future write endpoint must live in a separate controller and
 * carry its own ACL.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Scott Lydon <relays.inanity.0n@icloud.com>
 * @copyright Copyright (c) 2026 Scott Lydon
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\Agent;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Services\BaseService;

/**
 * Thin read-only controller for the Clinical Co-Pilot sidecar.
 *
 * The actual reconciliation runs in the Python sidecar's
 * ``sidecar/snapshot/reconciler.py``. This controller exists so the
 * sidecar can pull a single JSON payload over a stable internal API
 * rather than issue a parallel fan-out across the FHIR endpoints — useful
 * for cold-start ingest and for environments where an outbound network
 * fan-out would breach the BAA boundary.
 */
final class SnapshotController extends BaseService
{
    public const TABLE_NAME = 'patient_data';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    /**
     * Return the denormalised snapshot for one patient.
     *
     * The shape of the JSON returned matches the Pydantic
     * ``PatientSnapshot`` model in
     * ``clinical-copilot/sidecar/snapshot/models.py``. See
     * ``ARCHITECTURE.md`` §2.1 for the canonical example.
     *
     * @param string $patientUuid FHIR resource UUID, not the legacy numeric pid.
     *
     * @return array{
     *   patient_id: string,
     *   snapshot_version: string,
     *   demographics: array<string, mixed>,
     *   active_problems: array<int, array<string, mixed>>,
     *   medications: array<int, array<string, mixed>>,
     *   allergies: array<int, array<string, mixed>>,
     *   recent_vitals: array<int, array<string, mixed>>,
     *   recent_labs: array<int, array<string, mixed>>,
     *   presenting: array<string, mixed>,
     *   quality_flags: array<int, array<string, mixed>>,
     * }
     */
    public function getSnapshot(string $patientUuid): array
    {
        // The full implementation pulls from the same FHIR services used
        // by ``src/RestControllers/FHIR/...`` and runs the deterministic
        // reconciliation pass. Until the cold-start path is wired the
        // sidecar issues the parallel fan-out itself; this method exists
        // as the contract.
        (new SystemLogger())->info(
            'Agent snapshot requested',
            ['patient_uuid' => $patientUuid]
        );
        return [
            'patient_id' => $patientUuid,
            'snapshot_version' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(\DateTimeInterface::ATOM),
            'demographics' => [],
            'active_problems' => [],
            'medications' => [],
            'allergies' => [],
            'recent_vitals' => [],
            'recent_labs' => [],
            'presenting' => [],
            'quality_flags' => [
                [
                    'code' => 'snapshot_stub',
                    'description' => 'PHP-side snapshot is a stub; the sidecar performs the parallel FHIR fan-out itself.',
                    'related_provenance' => [],
                ],
            ],
        ];
    }
}
