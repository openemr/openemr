<?php

/**
 * Writes a Plan through OpenEMR's own services and data objects, so the
 * rows look like the ones the screens make.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Load;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\ORDataObject\ContactAddress;
use OpenEMR\Common\ORDataObject\ContactTelecom;
use OpenEMR\Common\ORDataObject\Person;
use OpenEMR\DemoSeed\Plan\ContactOwner;
use OpenEMR\DemoSeed\Plan\PatientUpdate;
use OpenEMR\DemoSeed\Plan\Plan;
use OpenEMR\DemoSeed\Plan\PlannedAddress;
use OpenEMR\DemoSeed\Plan\PlannedPerson;
use OpenEMR\DemoSeed\Plan\PlannedRelation;
use OpenEMR\DemoSeed\Plan\PlannedTelecom;
use OpenEMR\DemoSeed\SeedFile\FacilitySpec;
use OpenEMR\DemoSeed\SeedFile\ProviderSpec;
use OpenEMR\Services\ContactRelationService;
use OpenEMR\Services\ContactService;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\PersonPatientLinkService;
use OpenEMR\Services\PractitionerService;

final class PlanWriter
{
    private const LINK_METHOD = 'import';

    /** @var array<string, int> facility id by seed-file key */
    private array $facilityIds = [];
    /** @var array<string, string> facility name by seed-file key */
    private array $facilityNames = [];
    /** @var array<string, int> users.id by seed-file key */
    private array $providerIds = [];
    /** @var array<string, int> person.id by plan reference */
    private array $personIds = [];
    /** @var array<string, int> contact.id by ContactOwner::key() */
    private array $contactIds = [];

    public function __construct(
        private readonly FacilityService $facilityService,
        private readonly PractitionerService $practitionerService,
        private readonly PatientService $patientService,
        private readonly ContactService $contactService,
        private readonly ContactRelationService $relationService,
        private readonly PersonPatientLinkService $linkService,
    ) {
    }

    /**
     * @param callable(string): void $progress called with a line per part written
     */
    public function write(Plan $plan, callable $progress): void
    {
        foreach ($plan->facilities as $facility) {
            $this->facilityIds[$facility->key] = $this->writeFacility($facility);
            $this->facilityNames[$facility->key] = $facility->name;
        }
        $progress(sprintf('%d facilities', count($plan->facilities)));

        foreach ($plan->providers as $provider) {
            $this->providerIds[$provider->key] = $this->writeProvider($provider);
        }
        $progress(sprintf('%d providers', count($plan->providers)));

        foreach ($plan->patientUpdates as $update) {
            $this->writePatientUpdate($update, $plan);
        }
        $progress(sprintf('%d patients updated (marital status, state, provider, phones)', count($plan->patientUpdates)));

        foreach ($plan->people as $person) {
            $this->personIds[$person->ref] = $this->writePerson($person);
        }
        foreach ($plan->relations as $relation) {
            $this->writeRelation($relation);
        }
        $progress(sprintf('%d related people, %d relations', count($plan->people), count($plan->relations)));

        foreach ($plan->telecoms as $telecom) {
            $this->writeTelecom($telecom);
        }
        foreach ($plan->addresses as $address) {
            $this->writeAddress($address);
        }
        $progress(sprintf('%d phones and emails, %d addresses', count($plan->telecoms), count($plan->addresses)));
    }

    private function writeFacility(FacilitySpec $facility): int
    {
        return $this->facilityService->insertFacility([
            'name' => $facility->name,
            'phone' => $facility->phone ?? '',
            'fax' => $facility->fax ?? '',
            'street' => $facility->address->street,
            'city' => $facility->address->city,
            'state' => $facility->address->state,
            'postal_code' => $facility->address->postalCode,
            'country_code' => 'USA',
            'federal_ein' => $facility->taxId ?? '',
            'facility_npi' => $facility->npi ?? '',
            'pos_code' => $facility->posCode === null ? '' : (string) $facility->posCode,
            'service_location' => $facility->serviceLocation ? 1 : 0,
            'billing_location' => $facility->billingLocation ? 1 : 0,
            'accepts_assignment' => $facility->billingLocation ? 1 : 0,
            'color' => $facility->color ?? '',
            'primary_business_entity' => 0,
            'inactive' => 0,
        ]);
    }

    private function writeProvider(ProviderSpec $provider): int
    {
        $facility = $this->facilityIdFor($provider->facilityKey);
        $result = $this->practitionerService->insert([
            'username' => $provider->username,
            'title' => $provider->title ?? '',
            'fname' => $provider->firstName,
            'lname' => $provider->lastName,
            'valedictory' => $provider->credentials ?? '',
            'specialty' => $provider->specialty ?? '',
            'npi' => $provider->npi,
            'facility_id' => $facility,
            'facility' => $this->facilityName($provider->facilityKey),
            'authorized' => 1,
            'calendar' => 1,
            'active' => 1,
            'info' => 'Demo seed provider',
        ]);
        $data = $result->isValid() ? $result->getData() : null;
        $row = is_array($data) ? ($data[0] ?? null) : null;
        $id = is_array($row) ? ($row['id'] ?? null) : null;
        if (!is_numeric($id)) {
            throw new LoadException('Could not add provider ' . $provider->username . ': '
                . json_encode([$result->getValidationMessages(), $result->getInternalErrors()]));
        }
        return (int) $id;
    }

    private function writePatientUpdate(PatientUpdate $update, Plan $plan): void
    {
        $fields = $update->fields;
        if ($update->providerKey !== null) {
            $providerId = $this->providerIds[$update->providerKey]
                ?? throw new LoadException('Unknown provider key ' . $update->providerKey);
            $fields['providerID'] = (string) $providerId;
            $fields['care_team_provider'] = (string) $providerId;
            foreach ($plan->providers as $provider) {
                if ($provider->key === $update->providerKey) {
                    $fields['care_team_facility'] = (string) $this->facilityIdFor($provider->facilityKey);
                }
            }
        }
        if ($fields === []) {
            return;
        }
        // PatientService drops unknown columns without a word
        $unknown = array_diff(array_keys($fields), QueryUtils::listTableFields('patient_data'));
        if ($unknown !== []) {
            throw new LoadException('patient_data has no column(s): ' . implode(', ', $unknown));
        }
        if ($this->patientService->databaseUpdate(['pid' => $update->pid, ...$fields]) === false) {
            throw new LoadException('Could not update patient ' . $update->pid);
        }
    }

    private function writePerson(PlannedPerson $planned): int
    {
        $person = new Person();
        $person->set_first_name($planned->firstName);
        $person->set_last_name($planned->lastName);
        if ($planned->sex !== null) {
            $person->set_gender($planned->sex);
        }
        if ($planned->birthDate !== null) {
            $person->set_birth_date(\DateTime::createFromImmutable($planned->birthDate));
        }
        if ($planned->note !== null) {
            $person->set_notes($planned->note);
        }
        $person->persist();
        $personId = $person->get_id();
        if ($personId <= 0) {
            throw new LoadException('Could not add person ' . $planned->ref);
        }
        if ($planned->linkedPid !== null) {
            $result = $this->linkService->linkPersonToPatient(
                $personId,
                $this->patientDataId($planned->linkedPid),
                null,
                self::LINK_METHOD,
                'Demo seed: this person is the patient',
            );
            if (!$result->isValid()) {
                throw new LoadException('Could not link person ' . $planned->ref . ' to patient ' . $planned->linkedPid);
            }
        }
        return $personId;
    }

    private function writeRelation(PlannedRelation $relation): void
    {
        $result = $this->relationService->createRelationship(
            $this->contactId(ContactOwner::patient($relation->ownerPid)),
            'person',
            $this->personId($relation->personRef),
            [
                'relationship' => $relation->relationship,
                'role' => $relation->role,
                'contact_priority' => $relation->priority,
                'is_primary_contact' => $relation->flags->primaryContact ? 1 : 0,
                'is_emergency_contact' => $relation->flags->emergencyContact ? 1 : 0,
                'can_make_medical_decisions' => $relation->flags->medicalDecisions ? 1 : 0,
                'can_receive_medical_info' => $relation->flags->receiveMedicalInfo ? 1 : 0,
            ],
        );
        if (!$result->isValid()) {
            throw new LoadException('Could not relate patient ' . $relation->ownerPid . ' to ' . $relation->personRef);
        }
    }

    private function writeTelecom(PlannedTelecom $planned): void
    {
        $telecom = new ContactTelecom();
        $telecom->set_contact_id($this->contactId($planned->owner));
        $telecom->set_rank($planned->rank);
        $telecom->set_system($planned->system);
        $telecom->set_use($planned->use);
        $telecom->set_value($planned->value);
        $telecom->set_status($planned->active ? 'A' : 'I');
        $telecom->set_is_primary($planned->primary ? 'Y' : 'N');
        $telecom->set_period_start(\DateTime::createFromImmutable($planned->periodStart));
        $telecom->set_period_end($planned->periodEnd === null ? null : \DateTime::createFromImmutable($planned->periodEnd));
        if ($planned->reason !== null) {
            $telecom->set_inactivated_reason($planned->reason);
        }
        if ($planned->note !== null) {
            $telecom->set_notes($planned->note);
        }
        $telecom->persist();
    }

    private function writeAddress(PlannedAddress $planned): void
    {
        $contactAddress = new ContactAddress();
        $contactAddress->set_contact_id($this->contactId($planned->owner));
        $contactAddress->set_type($planned->type);
        $contactAddress->set_use($planned->use);
        $contactAddress->set_status($planned->active ? 'A' : 'I');
        $contactAddress->set_is_primary($planned->primary ? 'Y' : 'N');
        $contactAddress->set_period_start(\DateTime::createFromImmutable($planned->periodStart));
        $contactAddress->set_period_end($planned->periodEnd === null ? null : \DateTime::createFromImmutable($planned->periodEnd));
        if ($planned->reason !== null) {
            $contactAddress->set_inactivated_reason($planned->reason);
        }
        if ($planned->note !== null) {
            $contactAddress->set_notes($planned->note);
        }
        $address = $contactAddress->getAddress();
        $address->set_line1($planned->address->street);
        $address->set_city($planned->address->city);
        $address->set_state($planned->address->state);
        $address->set_postalcode($planned->address->postalCode);
        $address->set_country('USA');
        $address->set_foreign_id(null);
        $contactAddress->persist();
    }

    private function contactId(ContactOwner $owner): int
    {
        $key = $owner->key();
        if (!isset($this->contactIds[$key])) {
            $contact = $owner->pid !== null
                ? $this->contactService->getOrCreateForEntity('patient_data', $owner->pid)
                : $this->contactService->getOrCreateForEntity('person', $this->personId($owner->personRef ?? ''));
            $this->contactIds[$key] = $contact->get_id() ?? throw new LoadException('Could not add a contact for ' . $key);
        }
        return $this->contactIds[$key];
    }

    private function personId(string $ref): int
    {
        return $this->personIds[$ref] ?? throw new LoadException('Unknown person ' . $ref);
    }

    private function facilityIdFor(string $key): int
    {
        return $this->facilityIds[$key] ?? throw new LoadException('Unknown facility key ' . $key);
    }

    private function facilityName(string $key): string
    {
        return $this->facilityNames[$key] ?? throw new LoadException('Unknown facility key ' . $key);
    }

    private function patientDataId(int $pid): int
    {
        $id = QueryUtils::fetchSingleValue('SELECT `id` FROM `patient_data` WHERE `pid` = ?', 'id', [$pid]);
        if (!is_numeric($id)) {
            throw new LoadException('No patient with pid ' . $pid);
        }
        return (int) $id;
    }
}
