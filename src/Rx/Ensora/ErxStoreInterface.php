<?php

/**
 * The Ensora eRx queries and updates the launch page runs.
 *
 * interface/eRxStore.php implements this, so code in src/ never names a
 * class that only interface/ pages load. Single-row lookups return no
 * columns when nothing matches.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Rx\Ensora;

interface ErxStoreInterface
{
    /**
     * @return array<mixed> the primary business entity `facility` row
     */
    public function getFacilityPrimary(): array;

    /**
     * @return array<mixed> `users` row
     */
    public function getUserById(int $id): array;

    /**
     * @return array<mixed> the user's `facility` row
     */
    public function getUserFacility(int $id): array;

    /**
     * @return array<mixed> `patient_data` row
     */
    public function getPatientByPatientId(int $patientId): array;

    /**
     * @return array<mixed> height, height_units, weight and weight_units
     */
    public function getPatientVitalsByPatientId(int $patientId): array;

    /**
     * @return list<array<mixed>>
     */
    public function getPatientHealthplansByPatientId(int $patientId): array;

    /**
     * @return list<array<mixed>>
     */
    public function getPatientAllergiesByPatientId(int $patientId): array;

    /**
     * @return list<array<mixed>>
     */
    public function getPatientDiagnosisByPatientId(int $patientId): array;

    /**
     * @return array<mixed> the prescription, when it belongs to the patient
     */
    public function getPrescriptionById(int $prescriptionId, int $patientId): array;

    /**
     * @return list<array<mixed>>
     */
    public function selectMedicationsNotUploadedByPatientId(int $patientId, int $uploadActive, int $limit): array;

    /**
     * @return list<array<mixed>>
     */
    public function selectPrescriptionIdsNotUploadedByPatientId(int $patientId, int $uploadActive, int $limit): array;

    public function updatePatientImportStatusByPatientId(int $patientId, int $status): void;

    public function updateAllergyUploadedByPatientIdAllergyId(int $uploaded, int $patientId, int $allergyId): void;

    public function updatePrescriptionsUploadActiveByPatientIdPrescriptionId(int $upload, int $active, int $patientId, int $prescriptionId): void;

    public function updateErxUploadedByListId(int $listId, int $erx = 0): void;
}
