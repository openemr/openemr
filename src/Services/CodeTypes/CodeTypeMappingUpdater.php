<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\CodeTypes;

use Doctrine\ORM\EntityManagerInterface;
use OpenEMR\Entities\Code;
use OpenEMR\Entities\CodeType;
use OpenEMR\Entities\ListOption;
use Psr\Log\LoggerInterface;

/**
 * Updates list_options.codes mappings for activated code types (SNOMED, CPT4).
 *
 * This maps known option_id values to their corresponding medical codes,
 * allowing the UI to display human-readable labels while storing
 * standard code references.
 */
class CodeTypeMappingUpdater
{
    private const SNOMED_ENCOUNTER_TYPE_MAPPINGS = [
        'visit-after-hours' => '185463005',
        'visit-after-hours-not-night' => '185464004',
        'weekend-visit' => '185465003',
        'office-visit' => '30346009',
        'established-patient' => '3391000175108',
        'new-patient' => '37894004',
        'postoperative-follow-up' => '439740005',
    ];

    private const SNOMED_IMMUNIZATION_REFUSAL_REASON_MAPPINGS = [
        'religious_exemption' => '183945002',
        'patient_decision' => '105480006',
        'parental_decision' => '105480006',
        'financial_problem' => '160932005',
        'financial_circumstances_change' => '160934006',
        'alternative_treatment_requested' => '182890002',
        'patient_declined_procedure' => '105480006',
        'patient_declined_drug' => '182895007',
        'patient_declined_drug_effects' => '182897004',
        'patient_declined_drug_beliefs' => '182900006',
        'patient_declined_drug_cannot_pay' => '182902003',
        'patient_moved' => '184081006',
        'patient_dissatisfied_result' => '185479006',
        'patient_dissatisfied_doctor' => '185481008',
        'patient_variable_income' => '224187001',
        'patient_self_discharge' => '225928004',
        'drugs_not_completed' => '266710000',
        'family_illness' => '266966009',
        'follow_defaulted' => '275694009',
        'patient_noncompliance' => '275936005',
        'patient_noshow' => '281399006',
        'patient_further_opinion' => '310343007',
        'patient_treatment_delay' => '373787003',
        'patient_medication_declined' => '406149000',
        'patient_medication_forgot' => '408367005',
        'patient_non_compliant' => '413311005',
        'procedure_not_wanted' => '416432009',
        'income_insufficient' => '423656007',
        'income_necessities_only' => '424739004',
        'refused' => '443390004',
        'patient_procedure_discontinued' => '713247000',
    ];

    private const CPT4_ENCOUNTER_TYPE_MAPPINGS = [
        'new-patient-10' => 'New Patient (Brief)',
        'new-patient-15-29' => 'New Patient (Limited)',
        'new-patient-30-44' => 'Level 3, New Patient, Office Visit',
        'new-patient-45-59' => 'Extended Physical Exam',
        'new-patient-60-74' => 'New Exam (Comprehensive)',
        'established-patient-10-19' => 'Established Patient (Limited)',
        'established-patient-20-29' => 'Established Patient (Detailed)',
        'established-patient-30-39' => 'Established Patient (Extended)',
        'established-patient-40-54' => 'Established Patient (Comprehensive)',
    ];

    private const CODE_TYPE_SNOMED = 'SNOMED';
    private const CODE_TYPE_SNOMED_CT = 'SNOMED-CT';
    private const CODE_TYPE_SNOMED_PR = 'SNOMED-PR';
    private const CODE_TYPE_CPT4 = 'CPT4';

    private const LIST_ID_ENCOUNTER_TYPES = 'encounter-types';
    private const LIST_ID_IMMUNIZATION_REFUSAL = 'immunization_refusal_reason';

    public function __construct(
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Updates mappings for all currently activated code types.
     */
    public function updateActivatedMappings(): void
    {
        $repo = $this->em->getRepository(CodeType::class);
        $activatedCodeTypes = $repo->findBy(
            ['active' => true],
            ['seq' => 'ASC', 'key' => 'ASC'],
        );

        foreach ($activatedCodeTypes as $codeType) {
            if ($codeType->key === self::CODE_TYPE_CPT4) {
                $this->updateCPT4Mappings();
            } elseif ($this->isSnomedCodeType($codeType->key)) {
                $this->updateSNOMEDMappings($codeType->key);
            }
        }
    }

    /**
     * Updates SNOMED-CT mappings for encounter types and immunization refusal reasons.
     */
    public function updateSNOMEDMappings(string $codeType): void
    {
        if (!$this->shouldUpdateSNOMEDMappings()) {
            $this->logger->info('Skipping SNOMED mappings update', ['codeType' => $codeType]);
            return;
        }

        $this->logger->info("Updating {codeType} Mappings", ['codeType' => $codeType]);
        $this->updateListWithSnomedCodes(
            self::SNOMED_ENCOUNTER_TYPE_MAPPINGS,
            self::LIST_ID_ENCOUNTER_TYPES,
        );
        $this->updateListWithSnomedCodes(
            self::SNOMED_IMMUNIZATION_REFUSAL_REASON_MAPPINGS,
            self::LIST_ID_IMMUNIZATION_REFUSAL,
        );
    }

    /**
     * Updates CPT4 mappings for encounter types.
     */
    public function updateCPT4Mappings(): void
    {
        if (!$this->shouldUpdateCPT4Mappings()) {
            $this->logger->info('Skipping CPT4 mappings update');
            return;
        }

        $this->logger->info('Updating CPT4 Mappings');
        foreach (self::CPT4_ENCOUNTER_TYPE_MAPPINGS as $optionId => $codeText) {
            $code = $this->findCPT4Code($codeText);
            if ($code === null) {
                continue;
            }

            $codes = self::CODE_TYPE_CPT4 . ':' . $code->code;
            $this->updateListOptionCodes(self::LIST_ID_ENCOUNTER_TYPES, $optionId, $codes);
        }
        $this->em->flush();
    }

    private function shouldUpdateSNOMEDMappings(): bool
    {
        if (!$this->isCodeTypeActive(self::CODE_TYPE_SNOMED_CT)) {
            return false;
        }

        if ($this->listNeedsSnomedUpdate(self::SNOMED_ENCOUNTER_TYPE_MAPPINGS, self::LIST_ID_ENCOUNTER_TYPES)) {
            return true;
        }

        if ($this->listNeedsSnomedUpdate(self::SNOMED_IMMUNIZATION_REFUSAL_REASON_MAPPINGS, self::LIST_ID_IMMUNIZATION_REFUSAL)) {
            return true;
        }

        return false;
    }

    private function shouldUpdateCPT4Mappings(): bool
    {
        if (!$this->isCodeTypeActive(self::CODE_TYPE_CPT4)) {
            return false;
        }

        foreach (self::CPT4_ENCOUNTER_TYPE_MAPPINGS as $optionId => $codeText) {
            $code = $this->findCPT4Code($codeText);
            if ($code === null) {
                continue;
            }

            $listOption = $this->getListOption(self::LIST_ID_ENCOUNTER_TYPES, $optionId);
            $expectedCodes = self::CODE_TYPE_CPT4 . ':' . $code->code;

            if ($listOption?->codes !== $expectedCodes) {
                return true;
            }
        }

        return false;
    }

    private function findCPT4Code(string $codeText): ?Code
    {
        $codeTypeEntity = $this->em->getRepository(CodeType::class)->find(self::CODE_TYPE_CPT4);
        if ($codeTypeEntity === null) {
            return null;
        }

        return $this->em->getRepository(Code::class)->findOneBy([
            'codeText' => $codeText,
            'codeType' => $codeTypeEntity->id,
        ]);
    }

    private function isSnomedCodeType(string $codeType): bool
    {
        return in_array($codeType, [self::CODE_TYPE_SNOMED, self::CODE_TYPE_SNOMED_CT, self::CODE_TYPE_SNOMED_PR], true);
    }

    private function isCodeTypeActive(string $codeType): bool
    {
        $entity = $this->em->getRepository(CodeType::class)->findOneBy([
            'key' => $codeType,
            'active' => true,
        ]);

        return $entity !== null;
    }

    /**
     * @param array<string, string> $mappings
     */
    private function listNeedsSnomedUpdate(array $mappings, string $listId): bool
    {
        foreach ($mappings as $optionId => $codeId) {
            $listOption = $this->getListOption($listId, $optionId);
            $expectedCodes = self::CODE_TYPE_SNOMED_CT . ':' . $codeId;

            if ($listOption?->codes !== $expectedCodes) {
                return true;
            }
        }

        return false;
    }

    private function getListOption(string $listId, string $optionId): ?ListOption
    {
        return $this->em->getRepository(ListOption::class)->find([
            'listId' => $listId,
            'optionId' => $optionId,
        ]);
    }

    /**
     * @param array<string, string> $mappings
     */
    private function updateListWithSnomedCodes(array $mappings, string $listId): void
    {
        foreach ($mappings as $optionId => $codeId) {
            $codes = self::CODE_TYPE_SNOMED_CT . ':' . $codeId;
            $this->updateListOptionCodes($listId, $optionId, $codes);
        }
        $this->em->flush();
    }

    private function updateListOptionCodes(string $listId, string $optionId, string $codes): void
    {
        $listOption = $this->getListOption($listId, $optionId);
        if ($listOption === null) {
            $this->logger->warning('ListOption not found', [
                'listId' => $listId,
                'optionId' => $optionId,
            ]);
            return;
        }

        $listOption->codes = $codes;

        $this->logger->debug('Updated list_options', [
            'listId' => $listId,
            'optionId' => $optionId,
            'codes' => $codes,
        ]);
    }
}
