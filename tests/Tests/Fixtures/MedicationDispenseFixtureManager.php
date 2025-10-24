<?php

/*
 * MedicationDispenseFixtureManager.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\PatientService;

class MedicationDispenseFixtureManager
{
    private $createdRecords = [];

    /**
     * Create a drug sale dispense record for testing
     */
    public function createDrugSaleDispense(array $overrides = []): array
    {
        $defaults = [
            'uuid' => UuidRegistry::uuidToString(UuidRegistry::getRegistryForTable('drug_sales')->createUuid()),
            'drug_id' => 1,
            'inventory_id' => 1,
            'prescription_id' => 0,
            'patient_id' => $this->createPatient(),
            'encounter' => 0,
            'user' => 'testuser',
            'sale_date' => '2024-01-15',
            'quantity' => 30,
            'fee' => 25.00,
            'billed' => 0,
            'trans_type' => 1, // sale
            'bill_date' => '2024-01-15 10:00:00',
            'drug_name' => 'Test Medication',
            'ndc_number' => '12345-678-90',
            'rxnorm_code' => '123456',
            'lot_number' => 'LOT123',
            'expiration_date' => '2025-12-31',
            'dispense_type' => 'FF', // Final Fill
            'unit' => 'tablet',
            'patient_uuid' => null,
            'prescription_uuid' => null,
            'encounter_uuid' => null
        ];

        $data = array_merge($defaults, $overrides);

        // Handle special cases
        if (isset($overrides['different_patient']) && $overrides['different_patient']) {
            $data['patient_id'] = $this->createPatient();
        }

        // Create drug if needed
        $drugId = $this->createDrug($data);
        $data['drug_id'] = $drugId;

        // Create inventory record
        $inventoryId = $this->createDrugInventory($drugId, $data);
        $data['inventory_id'] = $inventoryId;

        // Create prescription if needed
        if ($data['prescription_id'] === 0) {
            $data['prescription_id'] = $this->createPrescription($data['patient_id']);
        }

        // Create encounter if needed
        if ($data['encounter'] === 0) {
            $data['encounter'] = $this->createEncounter($data['patient_id']);
        }

        // Get UUIDs for references
        $data['patient_uuid'] = $this->getPatientUuid($data['patient_id']);
        $data['prescription_uuid'] = $this->getPrescriptionUuid($data['prescription_id']);
        $data['encounter_uuid'] = $this->getEncounterUuid($data['encounter']);

        // Insert drug_sales record
        $sql = "INSERT INTO drug_sales (
            uuid, drug_id, inventory_id, prescription_id, pid, encounter,
            user, sale_date, quantity, fee, billed, trans_type, bill_date,
            notes, selector
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $bind = [
            UuidRegistry::uuidToBytes($data['uuid']),
            $data['drug_id'],
            $data['inventory_id'],
            $data['prescription_id'],
            $data['patient_id'],
            $data['encounter'],
            $data['user'],
            $data['sale_date'],
            $data['quantity'],
            $data['fee'],
            $data['billed'],
            $data['trans_type'],
            $data['bill_date'],
            'Test dispense notes',
            'test-selector'
        ];

        $result = QueryUtils::sqlInsert($sql, $bind);
        $data['sale_id'] = $result;

        $this->createdRecords['drug_sales'][] = $data['sale_id'];

        return $data;
    }

    /**
     * Create an immunization dispense record for testing
     */
    public function createImmunizationDispense(array $overrides = []): array
    {
        $defaults = [
            'uuid' => UuidRegistry::uuidToString(UuidRegistry::getRegistryForTable('immunization')->createUuid()),
            'patient_id' => $this->createPatient(),
            'administered_date' => '2024-01-15 14:30:00',
            'immunization_id' => 1,
            'cvx_code' => '140', // Influenza vaccine
            'manufacturer' => 'Test Manufacturer',
            'lot_number' => 'VAX123',
            'administered_by_id' => $this->createUser(),
            'administered_by' => 'Dr. Test',
            'education_date' => '2024-01-15',
            'vis_date' => '2024-01-01',
            'note' => 'Test immunization note',
            'amount_administered' => 0.5,
            'amount_administered_unit' => 'mL',
            'expiration_date' => '2025-12-31',
            'route' => 'intramuscular',
            'administration_site' => 'left arm',
            'added_erroneously' => 0,
            'completion_status' => 'completed',
            'information_source' => 'provider',
            'ordering_provider' => null,
            'patient_uuid' => null,
            'administered_by_uuid' => null,
            'ordering_provider_uuid' => null,
            'immunization_name' => 'Influenza Vaccine'
        ];

        $data = array_merge($defaults, $overrides);

        // Handle special cases
        if (isset($overrides['different_patient']) && $overrides['different_patient']) {
            $data['patient_id'] = $this->createPatient();
        }

        // Get UUIDs for references
        $data['patient_uuid'] = $this->getPatientUuid($data['patient_id']);
        $data['administered_by_uuid'] = $this->getUserUuid($data['administered_by_id']);

        if ($data['ordering_provider']) {
            $data['ordering_provider_uuid'] = $this->getUserUuid($data['ordering_provider']);
        }

        // Insert immunizations record
        $sql = "INSERT INTO immunizations (
            uuid, patient_id, administered_date, immunization_id, cvx_code,
            manufacturer, lot_number, administered_by_id, administered_by,
            education_date, vis_date, note, amount_administered,
            amount_administered_unit, expiration_date, route,
            administration_site, added_erroneously, completion_status,
            information_source, ordering_provider, create_date, update_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $bind = [
            UuidRegistry::uuidToBytes($data['uuid']),
            $data['patient_id'],
            $data['administered_date'],
            $data['immunization_id'],
            $data['cvx_code'],
            $data['manufacturer'],
            $data['lot_number'],
            $data['administered_by_id'],
            $data['administered_by'],
            $data['education_date'],
            $data['vis_date'],
            $data['note'],
            $data['amount_administered'],
            $data['amount_administered_unit'],
            $data['expiration_date'],
            $data['route'],
            $data['administration_site'],
            $data['added_erroneously'],
            $data['completion_status'],
            $data['information_source'],
            $data['ordering_provider']
        ];

        $result = QueryUtils::sqlInsert($sql, $bind);
        $data['id'] = $result;

        $this->createdRecords['immunizations'][] = $data['id'];

        return $data;
    }

    /**
     * Create a test drug record
     */
    private function createDrug(array $data): int
    {
        $existingDrug = QueryUtils::querySingleRow('select drug_id FROM drugs WHERE name = ?', [$data['drug_name']]);

        if ($existingDrug) {
            return $existingDrug['drug_id'];
        }

        $sql = "INSERT INTO drugs (name, ndc_number, drug_code, form, size, unit, route, substitute) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $bind = [
            $data['drug_name'],
            $data['ndc_number'],
            $data['rxnorm_code'],
            'tablet',
            '500mg',
            $data['unit'] ?? 'tablet',
            'oral',
            0
        ];

        $drugId = QueryUtils::sqlInsert($sql, $bind);
        $this->createdRecords['drugs'][] = $drugId;

        return $drugId;
    }

    /**
     * Create a test drug inventory record
     */
    private function createDrugInventory(int $drugId, array $data): int
    {
        $sql = "INSERT INTO drug_inventory (drug_id, lot_number, expiration, on_hand, warehouse_id, vendor_id) VALUES (?, ?, ?, ?, ?, ?)";
        $bind = [
            $drugId,
            $data['lot_number'],
            $data['expiration_date'],
            100, // on_hand quantity
            1,   // warehouse_id
            1    // vendor_id
        ];

        $inventoryId = QueryUtils::sqlInsert($sql, $bind);
        $this->createdRecords['drug_inventory'][] = $inventoryId;

        return $inventoryId;
    }

    /**
     * Create a test prescription record
     */
    private function createPrescription(int $patientId): int
    {
        $uuid = UuidRegistry::getRegistryForTable('prescriptions')->createUuid();

        $sql = "INSERT INTO prescriptions (uuid, patient_id, drug, active, date_added, provider_id, dosage, route, `interval`, substitute, refills) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $bind = [
            $uuid,
            $patientId,
            'Test Prescription Drug',
            1,
            date('Y-m-d H:i:s'),
            $this->createUser(),
            '1 tablet',
            1, // oral route
            1, // daily
            0, // no substitution
            3  // 3 refills
        ];

        $prescriptionId = QueryUtils::sqlInsert($sql, $bind);
        $this->createdRecords['prescriptions'][] = $prescriptionId;

        return $prescriptionId;
    }

    /**
     * Create a test patient record
     */
    private function createPatient(): int
    {
        $patientService = new PatientService();
        $result = $patientService->insert([
            'fname' => 'Test',
            'lname' => 'Patient_' . time() . '_' . random_int(1000, 9999),
            'DOB' => '1990-01-01',
            'sex' => 'Male'
        ]);
        if (!$result->isValid()) {
            throw new \Exception("Failed to create test patient: " . implode(", ", $result->getMessages()));
        }
        $id = $result->getFirstDataResult()['pid'];
        $this->createdRecords['patient_data'][] = $id;

        return $id;
    }

    /**
     * Create a test encounter record
     */
    private function createEncounter(int $patientId): int
    {
        $encounterService = new \OpenEMR\Services\EncounterService();
        $puuid = $this->getPatientUuid($patientId);
        $result = $encounterService->insertEncounter($puuid, [
            'pc_catid' => 1, // default category
            'class_code' => 'AMB', // ambulatory
            'reason' => 'Test encounter for medication dispense',
            'date' => date('Y-m-d H:i:s'),
            'provider_id' => $this->createUser(),
            'facility_id' => 1, // default facility
            'user' => $this->createUser(),
            'group' => 'Default'
        ]);
        if (!$result->isValid()) {
            throw new \Exception("Failed to create test encounter");
        }
        $encounterId = $result->getFirstDataResult()['encounter'];
        $this->createdRecords['form_encounter'][] = $encounterId;

        return $encounterId;
    }

    /**
     * Create a test user record
     */
    private function createUser(): int
    {
        $uuid = UuidRegistry::getRegistryForTable('users')->createUuid();

        $sql = "INSERT INTO users (uuid, username, fname, lname, npi, active) VALUES (?, ?, ?, ?, ?, ?)";
        $bind = [
            $uuid,
            'testuser_' . time() . '_' . random_int(1000, 9999),
            'Test',
            'User',
            '1234567890',
            1
        ];

        $userId = QueryUtils::sqlInsert($sql, $bind);
        $this->createdRecords['users'][] = $userId;

        return $userId;
    }

    /**
     * Get patient UUID by ID
     */
    private function getPatientUuid(int $patientId): string
    {
        $row = QueryUtils::querySingleRow('select uuid FROM patient_data WHERE pid = ?', [$patientId]);
        return UuidRegistry::uuidToString($row['uuid']);
    }

    /**
     * Get prescription UUID by ID
     */
    private function getPrescriptionUuid(int $prescriptionId): string
    {
        $row = QueryUtils::querySingleRow('select uuid FROM prescriptions WHERE id = ?', [$prescriptionId]);
        return UuidRegistry::uuidToString($row['uuid']);
    }

    /**
     * Get encounter UUID by ID
     */
    private function getEncounterUuid(int $encounterId): string
    {
        $row = QueryUtils::querySingleRow('select uuid FROM form_encounter WHERE encounter = ?', [$encounterId]);
        return UuidRegistry::uuidToString($row['uuid']);
    }

    /**
     * Get user UUID by ID
     */
    private function getUserUuid(int $userId): string
    {
        $row = QueryUtils::querySingleRow('select uuid FROM users WHERE id = ?', [$userId]);
        return UuidRegistry::uuidToString($row['uuid']);
    }

    /**
     * Remove all created test fixtures
     */
    public function removeFixtures(): void
    {

        foreach ($this->createdRecords as $table => $ids) {
            foreach ($ids as $id) {
                // delete from uuid registry
                QueryUtils::sqlStatementThrowException("DELETE FROM `uuid_registry` WHERE table_name = ? AND table_id = ?", [$table, $id]);
                QueryUtils::sqlStatementThrowException("DELETE FROM $table WHERE " . $this->getIdColumn($table) . " = ?", [$id]);
            }
        }

        $this->createdRecords = [];
    }

    /**
     * Get the primary key column name for a table
     */
    private function getIdColumn(string $table): string
    {
        $idColumns = [
            'drug_sales' => 'sale_id',
            'immunizations' => 'id',
            'drugs' => 'drug_id',
            'drug_inventory' => 'inventory_id',
            'prescriptions' => 'id',
            'patient_data' => 'pid',
            'form_encounter' => 'encounter',
            'users' => 'id'
        ];

        return $idColumns[$table] ?? 'id';
    }

    /**
     * Create multiple drug sale dispenses for a patient
     */
    public function createMultipleDrugSaleDispenses(int $count, array $baseData = []): array
    {
        $records = [];
        $patientId = $baseData['patient_id'] ?? $this->createPatient();

        for ($i = 0; $i < $count; $i++) {
            $data = array_merge($baseData, [
                'patient_id' => $patientId,
                'quantity' => 30 + $i,
                'sale_date' => date('Y-m-d', strtotime("+$i days")),
                'drug_name' => 'Test Drug ' . ($i + 1)
            ]);

            $records[] = $this->createDrugSaleDispense($data);
        }

        return $records;
    }

    /**
     * Create multiple immunization dispenses for a patient
     */
    public function createMultipleImmunizationDispenses(int $count, array $baseData = []): array
    {
        $records = [];
        $patientId = $baseData['patient_id'] ?? $this->createPatient();

        $cvxCodes = ['140', '141', '150', '158']; // Different vaccine types

        for ($i = 0; $i < $count; $i++) {
            $data = array_merge($baseData, [
                'patient_id' => $patientId,
                'cvx_code' => $cvxCodes[$i % count($cvxCodes)],
                'administered_date' => date('Y-m-d H:i:s', strtotime("+$i days")),
                'immunization_name' => 'Test Vaccine ' . ($i + 1)
            ]);

            $records[] = $this->createImmunizationDispense($data);
        }

        return $records;
    }
}
