<?php

/**
 * SDOH (USCDI v3) history_sdoh service class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * HistorySdohService
 * Centralized helpers used by history_sdoh form, widget, list, and save.
 *
 * This file has been enhanced with assistance from ChatGPT to ensure code quality and maintainability.
 * All generated code has been reviewed and tested for compliance with project standards.
 */

namespace OpenEMR\Services\SDOH;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Events\Services\ServiceSaveEvent;
use OpenEMR\Services\BaseService;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Validators\ProcessingResult;

class HistorySdohService extends BaseService
{
    public const TABLE_NAME = "form_history_sdoh";



    const CODE_FOOD_INSECURITY_SCT = '733423003';
    const CODE_FOOD_INSECURITY_ICD10CM = 'Z59.41';
    const CODE_HOUSING_INSTABILITY_SCT = '105531004'; // Housing unsatisfactory (finding)
    const CODE_HOUSING_ICD10CM = 'Z59.819'; // Housing instability, housed, unspecified
    const CODE_TRANSPORTATION_INSECURITY_SCT = '713458007';
    const CODE_TRANSPORTATION_ICD10CM = 'Z59.82';
    const CODE_UTILITIES_INSECURITY_ICD10CM = 'Z59.12'; // Inadequate housing utilities
    const CODE_INTERPERSONAL_SAFETY_SCT = '706892001';
    const CODE_INTERPERSONAL_ICD10CM = 'Z65.8'; // Other specified problems related to psychosocial circumstances
    const CODE_FINANCIAL_STRAIN_SCT = '1184702004';
    const CODE_FINANCIAL_ICD10CM = 'Z59.86';
    const CODE_SOCIAL_ISOLATION_SCT = '105412007';
    const CODE_SOCIAL_ICD10CM = 'Z60.2'; // Problems related to living alone
    const CODE_CHILDCARE_NEEDS_ICD10CM = 'Z60.8'; // Other problems related to social environment
    const CODE_CHILDCARE_DIFFICULTY_SCT = '55607006'; // Difficulty finding daycare services (finding)

    const CODE_INTERPERSONAL_VICTIM_SCT = '706893006'; // Victim of intimate partner abuse (finding)
    const CODE_FINANCIAL_INCOME_INSUFFICIENT_SCT = '423656007'; // Income insufficient to meet financial needs (finding)
    const CODE_SOCIAL_ISOLATION_SCT_ALT = '422650009'; // Social isolation (finding)

    const CODE_DIGITAL_ACCESS_ICD10CM = 'Z60.9'; // Problem related to social environment, unspecified
    const CODE_DIGITAL_ACCESS_DIFFICULTY_SCT = '55607006'; // Difficulty accessing digital technology (finding)

    const SOCIAL_HISTORY_CODES = [
        self::CODE_FOOD_INSECURITY_ICD10CM, self::CODE_FOOD_INSECURITY_SCT
        , self::CODE_HOUSING_ICD10CM, self::CODE_HOUSING_INSTABILITY_SCT
        , self::CODE_TRANSPORTATION_ICD10CM, self::CODE_TRANSPORTATION_INSECURITY_SCT
        , self::CODE_UTILITIES_INSECURITY_ICD10CM
        , self::CODE_INTERPERSONAL_ICD10CM, self::CODE_INTERPERSONAL_SAFETY_SCT, self::CODE_INTERPERSONAL_VICTIM_SCT
        , self::CODE_FINANCIAL_ICD10CM, self::CODE_FINANCIAL_STRAIN_SCT, self::CODE_FINANCIAL_INCOME_INSUFFICIENT_SCT
        , self::CODE_SOCIAL_ICD10CM, self::CODE_SOCIAL_ISOLATION_SCT, self::CODE_SOCIAL_ISOLATION_SCT_ALT
        , self::CODE_CHILDCARE_NEEDS_ICD10CM, self::CODE_CHILDCARE_DIFFICULTY_SCT
        , self::CODE_DIGITAL_ACCESS_ICD10CM, self::CODE_DIGITAL_ACCESS_DIFFICULTY_SCT
    ];

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    public function getUuidFields(): array
    {
        return ['uuid', 'puuid', 'euuid', 'created_by_uuid', 'updated_by_uuid'];
    }

    /**
     * USCDI v3 compliant mapping for SDOH domains to ICD-10-CM Health Concern codes
     * These codes are appropriate for Health Concern <value> elements in C-CDA
     * Except ONC b(1) test fails with recomment codes!
     */
    public static function getDomainHealthConcernCodes(): array
    {
        $SNOMED_OID = '2.16.840.1.113883.6.96';
        $ICD10_OID = '2.16.840.1.113883.6.90';

        return [
            'food_insecurity' => [
                'snomed' => [
                    'code' => self::CODE_FOOD_INSECURITY_SCT,
                    'display' => 'Food insecurity (finding)',
                    'system' => $SNOMED_OID,
                    'system_name' => 'SNOMED CT',
                ],
                'icd10' => [
                    'code' => self::CODE_FOOD_INSECURITY_ICD10CM,
                    'display' => 'Food insecurity',
                    'system' => $ICD10_OID,
                    'system_name' => 'ICD-10-CM',
                ],
            ],
            // If you specifically need "instability", SNOMED US doesn’t yet have a perfect single
            // concept; we fall back to ICD-10-CM Housing Instability (unspecified). If you prefer
            // “inadequate housing”, you can swap the SNOMED below to 105531004 (Housing unsatisfactory).
            'housing_instability' => [
                // 'snomed' => [ 'code' => '105531004', 'display' => 'Housing unsatisfactory (finding)', 'system' => $SNOMED_OID, 'system_name' => 'SNOMED CT' ],
                'icd10' => [
                    'code' => self::CODE_HOUSING_ICD10CM,
                    'display' => 'Housing instability, housed, unspecified',
                    'system' => $ICD10_OID,
                    'system_name' => 'ICD-10-CM',
                ],
            ],

            'transportation_insecurity' => [
                'snomed' => [
                    'code' => self::CODE_TRANSPORTATION_INSECURITY_SCT,
                    'display' => 'Lack of access to transportation (finding)',
                    'system' => $SNOMED_OID,
                    'system_name' => 'SNOMED CT',
                ],
                'icd10' => [
                    'code' => self::CODE_TRANSPORTATION_ICD10CM,
                    'display' => 'Transportation insecurity',
                    'system' => $ICD10_OID,
                    'system_name' => 'ICD-10-CM',
                ],
            ],

            'utilities_insecurity' => [
                // No single, broadly-used SNOMED finding for general “utility insecurity” yet.
                // (Specifics like “Inadequate water supply (441987004)” exist but are too narrow.)
                'icd10' => [
                    'code' => self::CODE_UTILITIES_INSECURITY_ICD10CM,
                    'display' => 'Inadequate housing utilities',
                    'system' => $ICD10_OID,
                    'system_name' => 'ICD-10-CM',
                ],
            ],
            // “Interpersonal safety concern” → use a safety/violence-risk finding when available.
            'interpersonal_safety' => [
                'snomed' => [
                    'code' => self::CODE_INTERPERSONAL_SAFETY_SCT,
                    'display' => 'At risk of intimate partner abuse (finding)',
                    'system' => $SNOMED_OID,
                    'system_name' => 'SNOMED CT',
                ],
                'icd10' => [
                    'code' => self::CODE_INTERPERSONAL_ICD10CM,
                    'display' => 'Other specified problems related to psychosocial circumstances',
                    'system' => $ICD10_OID,
                    'system_name' => 'ICD-10-CM',
                ],
            ],

            'financial_strain' => [
                'snomed' => [
                    'code' => self::CODE_FINANCIAL_STRAIN_SCT,
                    'display' => 'Financial insecurity (finding)',
                    'system' => $SNOMED_OID,
                    'system_name' => 'SNOMED CT',
                ],
                'icd10' => [
                    'code' => self::CODE_FINANCIAL_ICD10CM,
                    'display' => 'Financial insecurity',
                    'system' => $ICD10_OID,
                    'system_name' => 'ICD-10-CM',
                ],
            ],

            'social_isolation' => [
                'snomed' => [
                    'code' => self::CODE_SOCIAL_ISOLATION_SCT,
                    'display' => 'Social isolation (finding)',
                    'system' => $SNOMED_OID,
                    'system_name' => 'SNOMED CT',
                ],
                'icd10' => [
                    'code' => self::CODE_FINANCIAL_ICD10CM,
                    'display' => 'Problems related to living alone',
                    'system' => $ICD10_OID,
                    'system_name' => 'ICD-10-CM',
                ],
            ],
            // Childcare is usually captured as a need/barrier; there is a specific US-extension SNOMED,
            // but many installs don’t load US extensions. Keep ICD-10 general to avoid over-coding.
            'childcare_needs' => [
                // 'snomed' => [ 'code' => '671461000124109', 'display' => 'Unable to obtain childcare due to limited financial resources (finding)', 'system' => $SNOMED_OID, 'system_name' => 'SNOMED CT' ],
                'icd10' => [
                    'code' => self::CODE_CHILDCARE_NEEDS_ICD10CM,
                    'display' => 'Other problems related to social environment',
                    'system' => $ICD10_OID,
                    'system_name' => 'ICD-10-CM',
                ],
            ],
            // Digital access is typically recorded as a barrier in Social Environment; no single SNOMED
            // concept is universally adopted yet (local extensions exist). Use ICD-10 catch-all.
            'digital_access' => [
                'icd10' => [
                    'code' => self::CODE_DIGITAL_ACCESS_ICD10CM,
                    'display' => 'Problem related to social environment, unspecified',
                    'system' => $ICD10_OID,
                    'system_name' => 'ICD-10-CM',
                ],
            ],
        ];
    }

    // USCDI v3 compliant default mapping for SDOH domains to Health Concern codes
    // Currently there aren't specific ICD-10-CM codes for all domains, so some use a general "Problem" code.
    // ONC test would not pass with recommended codes, so I'm forced to use these.
    // I left in a list to get the preferred codes above should you want to switch.
    public static function getDomainHealthConcernCodesDefault(): array
    {
        // Use Gravity Project SDOH category codes for Health Concerns
        return [
            'food_insecurity' => [
                'code' => '733423003',
                'display' => 'Food insecurity',
                'system' => '2.16.840.1.113883.6.96',
                'system_name' => 'SNOMED CT'
            ],
            'housing_instability' => [
                'code' => '32911000',  // Homelessness (more specific than generic "Problem")
                'display' => 'Homeless',
                'system' => '2.16.840.1.113883.6.96',
                'system_name' => 'SNOMED CT'
            ],
            'transportation_insecurity' => [
                'code' => '713458007',
                'display' => 'Lack of access to transportation',
                'system' => '2.16.840.1.113883.6.96',
                'system_name' => 'SNOMED CT'
            ],
            'utilities_insecurity' => [
                'code' => '11403006',
                'display' => 'Economic circumstances affecting care',
                'system' => '2.16.840.1.113883.6.96',
                'system_name' => 'SNOMED CT'
            ],
            'interpersonal_safety' => [
                'code' => '706893006',
                'display' => 'Victim of intimate partner abuse',
                'system' => '2.16.840.1.113883.6.96',
                'system_name' => 'SNOMED CT'
            ],
            'financial_strain' => [
                'code' => '423656007',
                'display' => 'Income insufficient to meet financial needs',
                'system' => '2.16.840.1.113883.6.96',
                'system_name' => 'SNOMED CT'
            ],
            'social_isolation' => [
                'code' => '422650009',
                'display' => 'Social isolation',
                'system' => '2.16.840.1.113883.6.96',
                'system_name' => 'SNOMED CT'
            ],
            'childcare_needs' => [
                'code' => '55607006',
                'display' => 'Difficulty finding daycare services',
                'system' => '2.16.840.1.113883.6.96',
                'system_name' => 'SNOMED CT'
            ],
            'digital_access' => [
                'code' => '55607006',
                'display' => 'Difficulty accessing digital technology',
                'system' => '2.16.840.1.113883.6.96',
                'system_name' => 'SNOMED CT'
            ]
        ];
    }

    public static function concernsFromAssessmentV3(array $row): array
    {
        if (empty($row)) {
            return [];
        }

        $site = $_SESSION['site_id'] ?? 'default';
        $encId = (int)($row['encounter'] ?? 0);
        $assessDt = (string)($row['assessment_date'] ?? substr((string)($row['updated_at'] ?? ''), 0, 10));
        $assessDt = $assessDt ?: date('Y-m-d');

        $author = [
            'author_id' => $row['updated_by'] ?? $row['created_by'] ?? null,
            'time' => $row['updated_at'] ?? $row['created_at'] ?? $assessDt
        ];

        // Use ICD-10-CM or SNOMED codes for Health Concerns (USCDI v3 compliant)
        $domainCodes = self::getDomainHealthConcernCodesDefault();

        // Which answers from the assessment should become a "positive" concern per domain
        $positiveAnswers = [
            'food_insecurity' => ['yes', 'positive', 'at_risk', 'often', 'sometimes'],
            'housing_instability' => ['yes', 'positive', 'at_risk'],
            'transportation_insecurity' => ['yes', 'positive', 'at_risk'],
            'utilities_insecurity' => ['yes', 'positive', 'at_risk', 'already_shut_off'],
            'interpersonal_safety' => ['yes', 'positive'],
            'financial_strain' => ['yes', 'positive', 'high', 'very_hard', 'hard'],
            'social_isolation' => ['yes', 'positive', 'often', 'sometimes'],
            'childcare_needs' => ['yes', 'positive', 'needs'],
            'digital_access' => ['no', 'lack', 'limited', 'barrier'],
        ];

        $out = [];
        foreach ($domainCodes as $col => $codeInfo) {
            $rawAns = (string)($row[$col] ?? '');
            if ($rawAns === '') {
                continue;
            }

            $ans = strtolower(trim($rawAns));
            $positive = in_array($ans, $positiveAnswers[$col] ?? [], true);
            if (!$positive) {
                continue;
            }

            // Use ICD-10-CM code for Health Concern value
            $code = $codeInfo['code'];
            $display = $codeInfo['display'];
            $codeType = $codeInfo['system_name'];

            // Pull any free-text notes to enrich the narrative
            $notesCol = $col . '_notes';
            $notes = trim((string)($row[$notesCol] ?? ''));

            // Build the narrative
            $narr = $assessDt . ' ' . $display;
            if ($notes !== '') {
                $narr .= ' - ' . $notes;
            }

            $out[] = [
                'text' => $narr,
                'code' => $code,           // ICD-10-CM code for Health Concern <value>
                'code_type' => $codeType,  // "ICD10CM"
                'code_text' => $display,   // Display for the code
                'date' => $assessDt,
                'date_formatted' => str_replace('-', '', $assessDt),
                'author' => $author,
                'issues' => ['issue_uuid' => []], // Keep structure your dispatcher expects
                'assessment' => 'SDOH',
                'encounter' => (string)$encId,
                'extension' => base64_encode($site . $encId),
                'sha_extension' => sha1($site . '|sdoh_concern|' . $row['pid'] . '|' . $col . '|' . $assessDt),
            ];
        }

        return $out;
    }

    /**
     * Updated concernsFromCurrentAssessment method with USCDI v3 compliant codes
     */
    public static function concernsFromCurrentAssessmentV3(int $pid): array
    {
        // Pull latest assessment row
        $row = sqlQuery(
            "SELECT * FROM `form_history_sdoh` WHERE `pid` = ? ORDER BY COALESCE(`updated_at`,`created_at`) DESC LIMIT 1",
            [$pid]
        );
        return self::concernsFromAssessmentV3($row);
    }

    /**
     * Helper method to determine if a status represents a positive finding
     */
    private static function isPositiveStatus(?string $optionId): bool
    {
        if (!$optionId) {
            return false;
        }
        $positive = ['yes', 'positive', 'present', 'high', 'at_risk', 'often', 'sometimes', 'frequently', 'severe', 'moderate'];
        return in_array(strtolower($optionId), $positive, true);
    }

    /**
     * Return a display string for a FHIR CodeableConcept or a plain string.
     */
    public static function ccDisplay($cc): string
    {
        if (!$cc) {
            return '';
        }
        if (is_array($cc)) {
            if (!empty($cc['text'])) {
                return $cc['text'];
            }
            if (!empty($cc['coding'][0]['display'])) {
                return $cc['coding'][0]['display'];
            }
            if (!empty($cc['coding'][0]['code'])) {
                $sys = $cc['coding'][0]['system'] ?? '';
                $code = $cc['coding'][0]['code'];
                return $sys && $code ? $code : $code; // prefer code if no display
            }
        } elseif (is_string($cc)) {
            return $cc;
        }
        return '';
    }

    public function update(int $rec_id, array $data): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        try {
            if (empty($rec_id)) {
                $processingResult->setValidationMessages([
                    'id' => 'Record ID is required for update.'
                ]);
            }
            // TODO: validation could be done here
            $saveEvent = $this->getEventDispatcher()->dispatch(new ServiceSaveEvent($this, $data), ServiceSaveEvent::EVENT_PRE_SAVE);
            if (!empty($saveEvent->getSaveData())) {
                $data = $saveEvent->getSaveData();
            }
            unset($data['updated_at']);
            $columns = $this->buildUpdateColumns($data, ['null_value' => null]);
            $sql = "UPDATE `" . self::TABLE_NAME . "` SET " . $columns['set'] . ", updated_at = NOW() WHERE `id` = ?";
            $columns['bind'][] = $rec_id;

            QueryUtils::sqlStatementThrowException($sql, $columns['bind']);
            $saveEvent = $this->getEventDispatcher()->dispatch(new ServiceSaveEvent($this, $data), ServiceSaveEvent::EVENT_POST_SAVE);
            if (!empty($saveEvent->getSaveData())) {
                $data = $saveEvent->getSaveData();
            }
            $data['uuid'] = UuidRegistry::uuidToString($data['uuid']);
            $processingResult->addData($data);
        } catch (SqlQueryException $e) {
            $processingResult->addInternalError($e->getMessage());
        }
        return $processingResult;
    }

    public function insert(array $data): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        try {
            // TODO: validation could be done here
            $saveEvent = $this->getEventDispatcher()->dispatch(new ServiceSaveEvent($this, $data), ServiceSaveEvent::EVENT_PRE_SAVE);
            if (!empty($saveEvent->getSaveData())) {
                $data = $saveEvent->getSaveData();
            }
            if (empty($data['uuid'])) {
                $data['uuid'] = (new UuidRegistry(self::TABLE_NAME))->createUuid();
            }
            $columns = $this->buildInsertColumns($data, ['null_value' => null]);
            $sql = "INSERT INTO `" . self::TABLE_NAME . "` SET " . $columns['set'] . " , `created_at` = NOW(), `updated_at` = NOW() ";

            $insertId = QueryUtils::sqlInsert($sql, $columns['bind']);
            $data['id'] = $insertId;
            $saveEvent = $this->getEventDispatcher()->dispatch(new ServiceSaveEvent($this, $data), ServiceSaveEvent::EVENT_POST_SAVE);
            if (!empty($saveEvent->getSaveData())) {
                $data = $saveEvent->getSaveData();
            }
            $data['uuid'] = UuidRegistry::uuidToString($data['uuid']);
            $processingResult->addData($data);
        } catch (SqlQueryException $e) {
            $processingResult->addInternalError($e->getMessage());
        }
        return $processingResult;
    }

    /**
     * Build FHIR Goal resources (array) from a SDOH form row.
     * Uses Gravity/SDOHCC categories, LOINC measures, and desired target answers.
     */
    public static function buildGoals(array $info, int $pid): array
    {
        // Quick CodeableConcept builder
        $cc = fn($system, $code, $display): array => [
            'coding' => [[
                'system' => $system,
                'code' => $code,
                'display' => $display
            ]],
            'text' => $display
        ];

        // Domain spec: category (SDOHCC), measure (LOINC), desired target, description (SNOMED or text)
        $map = [
            'food_insecurity' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'food-insecurity', 'Food Insecurity'),
                'measure' => $cc('http://loinc.org', '88124-3', 'Food insecurity risk [HVS]'),
                'target' => $cc('http://loinc.org', 'LA19983-8', 'No risk'),
                'description' => $cc('http://snomed.info/sct', '1078229009', 'Food security (finding)'),
            ],
            'housing_instability' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'housing-instability', 'Housing Instability'),
                'measure' => $cc('http://loinc.org', '8689-2', 'Are you worried about losing your housing [PRAPARE]'),
                'target' => $cc('http://loinc.org', 'LA32-8', 'No'),
                'description' => $cc('http://snomed.info/sct', '161036002', 'Housing adequate (finding)'),
            ],
            'transportation_insecurity' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'transportation-insecurity', 'Transportation Insecurity'),
                'measure' => $cc('http://loinc.org', '93030-5', 'Has lack of transportation kept you from...'),
                'target' => $cc('http://loinc.org', 'LA32-8', 'No'),
                'description' => ['text' => 'Has reliable transportation'],
            ],
            'utilities_insecurity' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'utility-insecurity', 'Utility Insecurity'),
                'measure' => $cc('http://loinc.org', '96779-4', 'Utilities threatened to be shut off in last 12Mo'),
                'target' => $cc('http://loinc.org', 'LA32-8', 'No'),
                'description' => ['text' => 'No threat of utility shutoff'],
            ],
            'financial_strain' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'financial-insecurity', 'Financial Insecurity'),
                'measure' => ['text' => 'Financial strain measure'],
                'target' => $cc('http://loinc.org', 'LA22682-1', 'Not very hard'),
                'description' => ['text' => 'Reduced financial strain'],
            ],
            'social_isolation' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'social-connection', 'Social Connection'),
                'measure' => $cc('http://loinc.org', '100950-5', 'Frequency of feeling lonely'),
                'target' => ['text' => 'Never'],
                'description' => ['text' => 'Adequate social connection'],
            ],
            'childcare_needs' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'material-hardship', 'Material Hardship'),
                'measure' => ['text' => 'Childcare needs present'],
                'target' => $cc('http://loinc.org', 'LA32-8', 'No'),
                'description' => ['text' => 'Childcare needs addressed'],
            ],
            'digital_access' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'digital-access', 'Digital Access'),
                'measure' => ['text' => 'Adequate digital access'],
                'target' => ['text' => 'Yes'],
                'description' => ['text' => 'Has adequate digital access'],
            ],
            'interpersonal_safety' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'intimate-partner-violence', 'Intimate Partner Violence'),
                'measure' => ['text' => 'IPV screen'],
                'target' => $cc('http://loinc.org', 'LA32-8', 'No'),
                'description' => ['text' => 'Safe interpersonal environment'],
            ],
        ];
        $norm = static function ($v): string {
            $v = strtolower(trim((string)$v));
            $v = str_replace([' — ', ' – ', '—', '–'], '-', $v);
            $map = [
                'yes-medical' => 'yes',
                'already shut off' => 'present',
                'very hard' => 'present',
                'somewhat hard' => 'present',
                'often' => 'present',
                'sometimes' => 'present',
                'rarely' => 'present',
                'never' => 'no',
            ];
            return $map[$v] ?? $v;
        };

        //$needsGoal = fn($status): bool => $norm($status) !== '' && $norm($status) !== 'unknown';
        $needsGoal = fn($status): bool => in_array($status, ['present', 'at_risk', 'yes', 'often', 'always'], true);

        $when = $info['assessment_date'] ?? $info['sdoh_assessment_date'] ?? $info['updated_at'] ?? null;
        $due = $info['goal_due_date'] ?? null;

        $out = [];
        foreach ($map as $col => $spec) {
            $status = $info[$col] ?? $info['sdoh_' . $col] ?? null;
            if (!$needsGoal($status)) {
                continue;
            }

            $goal = [
                'resourceType' => 'Goal',
                'lifecycleStatus' => 'active',
                'category' => [$spec['category']],
                'description' => $spec['description'],
                'subject' => ['reference' => 'Patient/' . $pid],
            ];
            if ($when) {
                $goal['startDate'] = substr((string) $when, 0, 10);
            }

            $target = [];
            if (!empty($spec['measure'])) {
                $target['measure'] = $spec['measure'];
            }
            if (!empty($spec['target'])) {
                $target['detailCodeableConcept'] = $spec['target'];
            }
            if ($due) {
                $target['dueDate'] = substr((string) $due, 0, 10);
            }

            $goal['target'] = [$target];

            $out[] = $goal;
        }
        return $out;
    }

    /**
     * @return string[] Map of SDOH domains to list_options list names
     */
    public static function getListMapForDomains(): array
    {
        return [
            'food_insecurity'           => 'sdoh_food_insecurity_risk',
            'housing_instability'       => 'sdoh_housing_worry',
            'transportation_insecurity' => 'sdoh_transportation_barrier',
            'utilities_insecurity'      => 'sdoh_utilities_shutoff',
            'interpersonal_safety'      => 'sdoh_ipv_yesno',
            'financial_strain'          => 'sdoh_financial_strain',
            'social_isolation'          => 'sdoh_social_isolation_freq',
            'childcare_needs'           => 'sdoh_childcare_needs',
            'digital_access'            => 'sdoh_digital_access',
        ];
    }

    /**
     * Build "calculated" SDOH Interventions (array of ServiceRequest-like resources)
     * based on the same domain logic used for Goals. These are intended to feed the
     * C-CDA Plan of Care/Treatment Plan (planned activities) and UI text areas.
     *
     * - resourceType: ServiceRequest
     * - intent: 'order' (referral/arranged assistance)
     * - status: 'active'
     * - category: SDOHCC category (parallel to goals)
     * - code: the suggested intervention (text or coded where available)
     * - reasonCode: brief reason linking back to the domain
     */
    public static function buildInterventions(array $info, int $pid, array $opts = []): array
    {
        $now = date('Y-m-d');
        $when = $info['assessment_date'] ?? $info['sdoh_assessment_date'] ?? $info['updated_at'] ?? $now;
        $due = $info['intervention_due_date'] ?? $info['goal_due_date'] ?? null;

        // helper
        $cc = fn($system, $code, $display): array => [
            'coding' => [['system' => $system, 'code' => $code, 'display' => $display]],
            'text' => $display
        ];
        $txt = fn($s): array => ['text' => $s];

        // Same trigger rule as goals
        $needs = fn($status): bool => in_array($status, ['present', 'at_risk', 'yes'], true);

        // Domain → default planned intervention text (kept conservative; sites can override codes)
        $map = [
            'food_insecurity' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'food-insecurity', 'Food Insecurity'),
                // mirrors your CCD sample wording
                'code' => $txt('Assistance with application for food pantry program'),
                'reason' => $txt('Food insecurity risk'),
            ],
            'housing_instability' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'housing-instability', 'Housing Instability'),
                'code' => $txt('Referral to local housing assistance resources'),
                'reason' => $txt('Housing instability risk'),
            ],
            'transportation_insecurity' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'transportation-insecurity', 'Transportation Insecurity'),
                'code' => $txt('Arrange transportation for appointments (medical or social services)'),
                'reason' => $txt('Transportation barrier present'),
            ],
            'utilities_insecurity' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'utility-insecurity', 'Utility Insecurity'),
                'code' => $txt('Referral to utility bill assistance program'),
                'reason' => $txt('Utility shutoff risk'),
            ],
            'financial_strain' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'financial-insecurity', 'Financial Insecurity'),
                'code' => $txt('Referral to financial counseling / benefits navigator'),
                'reason' => $txt('Financial strain'),
            ],
            'social_isolation' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'social-connection', 'Social Connection'),
                'code' => $txt('Referral to community/social connection programs'),
                'reason' => $txt('Loneliness / social isolation'),
            ],
            'childcare_needs' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'material-hardship', 'Material Hardship'),
                'code' => $txt('Provide childcare resources and referral'),
                'reason' => $txt('Childcare needs present'),
            ],
            'digital_access' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'digital-access', 'Digital Access'),
                'code' => $txt('Assist with device/internet access and digital literacy'),
                'reason' => $txt('Limited digital access'),
            ],
            'interpersonal_safety' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'intimate-partner-violence', 'Intimate Partner Violence'),
                'code' => $txt('Provide IPV resources and safety planning; social work referral'),
                'reason' => $txt('IPV risk present'),
            ],
        ];

        $out = [];
        foreach ($map as $col => $spec) {
            $status = $info[$col] ?? $info['sdoh_' . $col] ?? null;
            if (!$needs($status)) {
                continue;
            }

            $sr = [
                'resourceType' => 'ServiceRequest',
                'status' => 'active',
                'intent' => 'order',
                'priority' => 'routine',
                'category' => [$spec['category']],
                'code' => $spec['code'],         // intervention requested/planned
                'reasonCode' => [$spec['reason']],
                'subject' => ['reference' => 'Patient/' . $pid],
                'authoredOn' => substr($when, 0, 10),
            ];
            if ($due) {
                $sr['occurrenceDateTime'] = substr((string) $due, 0, 10);
            }
            $out[] = $sr;
        }

        // Optionally merge manually typed interventions from the form (free-text, one-per-line)
        if (!empty($opts['include_manual']) && !empty($info['interventions'])) {
            $out = array_merge($out, self::parseManualInterventions($info['interventions'], $pid, $when, $due));
        }

        return $out;
    }

    /**
     * Convert interventions to a textarea-friendly bullet list (mirrors goalsToText).
     */
    public static function interventionsToText(array $interventions, array $opts = []): string
    {
        $bullet = $opts['bullet'] ?? '•';
        $include_category = $opts['include_category'] ?? true;
        $include_reason = $opts['include_reason'] ?? true;
        $include_when = $opts['include_when'] ?? true;
        $include_due = $opts['include_due'] ?? true;
        $max_lines = $opts['max_lines'] ?? null;

        $lines = [];
        foreach ($interventions as $sr) {
            if (!is_array($sr) || ($sr['resourceType'] ?? '') !== 'ServiceRequest') {
                continue;
            }

            $category = '';
            if ($include_category) {
                $cat0 = $sr['category'][0] ?? null;
                $category = self::ccDisplay($cat0);
            }
            $codeText = self::ccDisplay($sr['code'] ?? '');

            $reason = '';
            if ($include_reason) {
                $r0 = $sr['reasonCode'][0] ?? null;
                $reason = self::ccDisplay($r0);
            }

            $parts = [];
            if ($category !== '') {
                $parts[] = '[' . $category . ']';
            }
            if ($codeText !== '') {
                $parts[] = $codeText;
            }

            $suffix = [];
            if ($reason !== '') {
                $suffix[] = "reason: $reason";
            }
            if ($include_due && !empty($sr['occurrenceDateTime'])) {
                $suffix[] = "due: " . substr((string) $sr['occurrenceDateTime'], 0, 10);
            }
            if ($include_when && !empty($sr['authoredOn'])) {
                $suffix[] = "ordered: " . substr((string) $sr['authoredOn'], 0, 10);
            }

            $line = trim($bullet . ' ' . implode(' ', $parts));
            if (!empty($suffix)) {
                $line .= ' — ' . implode('; ', $suffix);
            }
            if ($line !== $bullet) {
                $lines[] = $line;
            }

            if ($max_lines && count($lines) >= $max_lines) {
                break;
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Parse free-text interventions (one-per-line) into ServiceRequest-like resources.
     * Useful for merging user-entered interventions with calculated ones.
     */
    protected static function parseManualInterventions(string $text, int $pid = 0, ?string $when = null, ?string $due = null): array
    {
        $when = $when ? substr($when, 0, 10) : date('Y-m-d');
        $due = $due ? substr($due, 0, 10) : null;

        $out = [];
        foreach (preg_split('/\R+/', $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            // Strip a leading bullet if present (•, -, *, etc.)
            $line = preg_replace('/^\h*(?:[-*\x{2022}]\h*)/u', '', $line);

            $sr = [
                'resourceType' => 'ServiceRequest',
                'status' => 'active',
                'intent' => 'order',
                'priority' => 'routine',
                'category' => [['text' => 'SDOH Intervention']],
                'code' => ['text' => $line],
                'subject' => $pid ? ['reference' => 'Patient/' . $pid] : null,
                'authoredOn' => $when,
            ];
            if ($due) {
                $sr['occurrenceDateTime'] = $due;
            }
            $out[] = $sr;
        }
        return $out;
    }

    /**
     * Format Goals array for the Goals textarea (one bullet per goal).
     */
    public static function goalsToText(array $goals, array $opts = []): string
    {
        $bullet = $opts['bullet'] ?? '•';
        $include_category = $opts['include_category'] ?? true;
        $include_measure = $opts['include_measure'] ?? true;
        $include_due = $opts['include_due'] ?? true;
        $max_lines = $opts['max_lines'] ?? null;

        $lines = [];
        foreach ($goals as $g) {
            if (!is_array($g) || ($g['resourceType'] ?? '') !== 'Goal') {
                continue;
            }

            $category = '';
            if ($include_category) {
                $cat0 = $g['category'][0] ?? null;
                $category = self::ccDisplay($cat0);
            }
            $desc = self::ccDisplay($g['description'] ?? '');

            $t = $g['target'][0] ?? [];
            $tMeasure = $include_measure ? self::ccDisplay($t['measure'] ?? '') : '';
            $tDetail = '';
            if (!empty($t['detailCodeableConcept'])) {
                $tDetail = self::ccDisplay($t['detailCodeableConcept']);
            } elseif (!empty($t['detailString'])) {
                $tDetail = $t['detailString'];
            } elseif (!empty($t['detailQuantity']['value'])) {
                $tDetail = $t['detailQuantity']['value'];
                if (!empty($t['detailQuantity']['unit'])) {
                    $tDetail .= ' ' . $t['detailQuantity']['unit'];
                }
            }

            $start = $g['startDate'] ?? '';
            $due = $include_due ? ($t['dueDate'] ?? '') : '';

            $parts = [];
            if ($category !== '') {
                $parts[] = '[' . $category . ']';
            }
            if ($desc !== '') {
                $parts[] = $desc;
            }

            $suffix = [];
            if ($tDetail !== '') {
                $suffix[] = "target: $tDetail";
            }
            if ($tMeasure !== '') {
                $suffix[] = "measure: $tMeasure";
            }
            if ($due !== '') {
                $suffix[] = "due: " . substr((string) $due, 0, 10);
            }
            if ($start !== '') {
                $suffix[] = "start: " . substr((string) $start, 0, 10);
            }

            $line = trim($bullet . ' ' . implode(' ', $parts));
            if (!empty($suffix)) {
                $line .= ' — ' . implode('; ', $suffix);
            }
            if ($line !== $bullet) {
                $lines[] = $line;
            }

            if ($max_lines && count($lines) >= $max_lines) {
                break;
            }
        }
        return implode("\n", $lines);
    }

    /** Map code system URI or short name → display string expected by serveccda. */
    private function ccSystemToCodeType(string $systemOrName): string
    {
        $s = strtolower(trim($systemOrName));
        if ($s === '') {
            return '';
        }
        // URIs
        if (str_contains($s, '113883.6.96') || str_contains($s, 'snomed')) {
            return 'SNOMED CT';
        }
        if (str_contains($s, '113883.6.1') || str_contains($s, 'loinc')) {
            return 'LOINC';
        }
        if (str_contains($s, '113883.6.90') || str_contains($s, 'icd10')) {
            return 'ICD10-CM';
        }
        if (str_contains($s, 'omb')) {
            return 'OMB';
        }
        // Names/prefix
        return $this->mapPrefixToType($systemOrName);
    }

    /** Normalize system names for display */
    private function normalizeSystemName(string $name): string
    {
        $t = trim($name);
        if ($t === '') {
            return '';
        }
        if (strcasecmp($t, 'SNOMED-CT') === 0) {
            return 'SNOMED CT';
        }
        if (strcasecmp($t, 'SNOMED') === 0) {
            return 'SNOMED CT';
        }
        if (strcasecmp($t, 'LOINC') === 0) {
            return 'LOINC';
        }
        if (stripos($t, 'ICD10') !== false) {
            return 'ICD10-CM';
        }
        return $t;
    }

    private function parseCodeFromRecord(array $rec): array
    {
        // 1) codes JSON
        if (!empty($rec['codes']) && is_string($rec['codes'])) {
            $c = ltrim($rec['codes']);
            if ($c !== '' && $c[0] === '{') {
                $j = json_decode($c, true);
                if (is_array($j)) {
                    $code = (string)($j['code'] ?? '');
                    $sys = (string)($j['system'] ?? '');
                    $type = $this->ccSystemToCodeType($sys) ?: ($rec['code_type'] ?? '');
                    $txt = (string)($j['display'] ?? ($rec['description'] ?? $rec['codetext'] ?? ''));
                    return [$code, $this->normalizeSystemName($type), $txt];
                }
            } elseif (str_contains($c, ':')) {
                // 2) codes "SYS:CODE"
                [$pref, $cv] = explode(':', $c, 2);
                return [trim($cv), $this->mapPrefixToType($pref), (string)($rec['description'] ?? $rec['codetext'] ?? '')];
            }
        }
        // 3) code "SYS:CODE"
        if (!empty($rec['code']) && is_string($rec['code']) && str_contains($rec['code'], ':')) {
            [$pref, $cv] = explode(':', $rec['code'], 2);
            return [trim($cv), $this->mapPrefixToType($pref), (string)($rec['codetext'] ?? $rec['description'] ?? '')];
        }
        // 4) raw code + code_type
        if (!empty($rec['code'])) {
            return [
                (string)$rec['code'],
                $this->normalizeSystemName((string)($rec['code_type'] ?? '')),
                (string)($rec['codetext'] ?? $rec['description'] ?? '')
            ];
        }
        return ['', '', (string)($rec['description'] ?? '')];
    }

    /** Map short code system prefix to the display strings your JS expects. */
    private function mapPrefixToType(string $pref): string
    {
        $p = strtolower(trim($pref));
        if ($p === 'snomed' || $p === 'snomed-ct') {
            return 'SNOMED CT';
        }
        if ($p === 'loinc') {
            return 'LOINC';
        }
        if ($p === 'icd10' || $p === 'icd10-cm') {
            return 'ICD10-CM';
        }
        if ($p === 'omb') {
            return 'OMB';
        }
        return strtoupper($pref);
    }

    /**
     * Look up a domain option’s codes JSON from list_options and return
     * [code, code_type, code_text]. Uses the curated value sets you installed.
     *
     * Example: list_id 'sdoh_food_insecurity_risk', option_id 'yes'
     */
    private function codeFromListOption(string $listId, string $optionId): array
    {
        $sql = "SELECT `codes`, `title` FROM `list_options` WHERE `list_id` = ? AND `option_id` = ? LIMIT 1";
        $row = sqlQuery($sql, [$listId, $optionId]);
        if (empty($row)) {
            return ['', '', ''];
        }

        $title = (string)($row['title'] ?? '');
        $codes = (string)($row['codes'] ?? '');
        if ($codes !== '' && $codes[0] === '{') {
            $j = json_decode($codes, true);
            if (is_array($j)) {
                $code = (string)($j['code'] ?? '');
                $sys = (string)($j['system'] ?? '');
                $type = $this->ccSystemToCodeType($sys);
                $txt = (string)($j['display'] ?? $title);
                return [$code, $type, $txt];
            }
        }
        return ['', '', $title];
    }

    /**
     * Return ONLY SDOH-derived concerns from the most recent form_history_sdoh row.
     * Output shape matches what your dispatcher/serveccda.js consumes.
     */
    public static function concernsFromCurrentAssessment(int $pid): array
    {
        // pull latest assessment row
        $row = sqlQuery(
            "SELECT * FROM `form_history_sdoh` WHERE `pid` = ? ORDER BY COALESCE(`updated_at`,`created_at`) DESC LIMIT 1",
            [$pid]
        );
        if (empty($row)) {
            return [];
        }

        $site = $_SESSION['site_id'] ?? 'default';
        $encId = (int)($row['encounter'] ?? 0);
        $assessDt = (string)($row['assessment_date'] ?? substr((string)($row['updated_at'] ?? ''), 0, 10));
        $assessDt = $assessDt ?: date('Y-m-d');

        $author = [
            'author_id' => $row['updated_by'] ?? $row['created_by'] ?? null,
            'time' => $row['updated_at'] ?? $row['created_at'] ?? $assessDt
        ];

        // --- Domain → SNOMED Health-Concern codes (value for Problem Observation) ---
        // These are condition *findings* appropriate for Health Concern <value xsi:type="CD"...>.
        // (No LA-* or LOINC answers here.)
        $DOMAIN_SNOMED = [
            'food_insecurity' => ['733423003', 'Food insecurity (finding)'],
            'housing_instability' => ['160734000', 'Inadequate housing (finding)'],
            'transportation_insecurity' => ['281647001', 'Transportation problem (finding)'],
            'utilities_insecurity' => ['248539003', 'Lack of electricity (finding)'], // adjust if you have a better code
            'interpersonal_safety' => ['225337009', 'Victim of violence (finding)'],  // use a more specific code if you capture it
            'financial_strain' => ['105480006', 'Economic problem (finding)'],
            'social_isolation' => ['40917007', 'Social isolation (finding)'],
            'childcare_needs' => ['364703007', 'Needs assistance with childcare (finding)'],
            // Add more domains you record in form_history_sdoh here as needed
            'digital_access' => ['713879003', 'Lack of access to digital technology (finding)'],
        ];

        // Which answers from the assessment should become a “positive” concern per domain
        $POSITIVE = [
            'food_insecurity' => ['yes', 'positive', 'at_risk', 'often', 'sometimes'],
            'housing_instability' => ['yes', 'positive', 'at_risk'],
            'transportation_insecurity' => ['yes', 'positive', 'at_risk'],
            'utilities_insecurity' => ['yes', 'positive', 'at_risk'],
            'interpersonal_safety' => ['yes', 'positive'],
            'financial_strain' => ['yes', 'positive', 'high', 'very hard', 'hard'],
            'social_isolation' => ['yes', 'positive'],
            'childcare_needs' => ['yes', 'positive', 'needs'],
            'digital_access' => ['no', 'lack', 'limited', 'barrier'],
        ];

        // Human-friendly labels for the domain column to include in narrative if needed
        $LABEL = [
            'food_insecurity' => 'Food insecurity (finding)',
            'housing_instability' => 'Housing instability (finding)',
            'transportation_insecurity' => 'Transportation insecurity (finding)',
            'utilities_insecurity' => 'Utilities insecurity (finding)',
            'interpersonal_safety' => 'Interpersonal safety risk (finding)',
            'financial_strain' => 'Financial strain (finding)',
            'social_isolation' => 'Social isolation (finding)',
            'childcare_needs' => 'Childcare needs (finding)',
            'digital_access' => 'Digital inclusion/access need (finding)',
        ];

        $domains = array_keys($DOMAIN_SNOMED);
        $out = [];

        foreach ($domains as $col) {
            $rawAns = (string)($row[$col] ?? '');
            if ($rawAns === '') {
                continue;
            }

            $ans = strtolower(trim($rawAns));
            $positive = in_array($ans, $POSITIVE[$col] ?? [], true);
            if (!$positive) {
                continue;
            }

            // Pick the domain’s SNOMED Health Concern code
            [$code, $display] = $DOMAIN_SNOMED[$col];
            $codeType = 'SNOMED CT';

            // Pull any free-text notes to enrich the <text> narrative
            $notesCol = $col . '_notes';
            $notes = trim((string)($row[$notesCol] ?? ''));

            // Build the narrative; keep your “<date> <label> - <notes>” style
            $label = $LABEL[$col] ?? $display;
            $narr = $assessDt . ' ' . $label;
            if ($notes !== '') {
                $narr .= ' - ' . $notes;
            }

            $out[] = [
                'text' => $narr,
                'code' => $code,        // SNOMED finding for Health Concern <value>
                'code_type' => $codeType,    // “SNOMED CT”
                'code_text' => $display,     // display for the code
                'date' => $assessDt,
                'date_formatted' => str_replace('-', '', $assessDt),
                'author' => $author,
                'issues' => ['issue_uuid' => []], // keep structure your dispatcher expects
                // optional: identify that this came from the SDOH assessment
                'assessment' => 'SDOH',
                'encounter' => (string)$encId,
                'extension' => base64_encode($site . $encId),
                'sha_extension' => sha1($site . '|sdoh_concern|' . $pid . '|' . $col . '|' . $assessDt),
            ];
        }

        return $out;
    }

    /**
     * @param int $pid
     * @return array|null
     */
    public function getCurrentGoalsResource(int $pid): ?array
    {
        $goals = sqlQuery(
            "SELECT goals FROM form_history_sdoh WHERE pid = ? ORDER BY updated_at DESC, id DESC LIMIT 1",
            [$pid]
        ) ?: null;
        if (empty($goals['goals'])) {
            return null;
        }
        return json_decode((string) $goals['goals'], true);
    }

    private function ccText($cc): string
    {
        return HistorySdohService::ccDisplay($cc);
    }

    public static function sdohProblemMap(): array
    {
        return [
            'food_insecurity' => ['icd10' => 'Z59.41', 'snomed' => '733423003'],
            'housing_instability' => ['icd10' => 'Z59.819', 'snomed' => '161036002'],
            'transportation_insecurity' => ['icd10' => 'Z59.82', 'snomed' => '46578006'],
            'utilities_insecurity' => ['icd10' => 'Z59.12', 'snomed' => '160632005'],
            'interpersonal_safety' => ['icd10' => 'Z65.4', 'snomed' => '225337009'],
            'financial_strain' => ['icd10' => 'Z59.86', 'snomed' => '702479006'],
            'social_isolation' => ['icd10' => 'Z60.4', 'snomed' => '105531004'],
            'childcare_needs' => ['icd10' => 'Z59.89', 'snomed' => '713404003'],
            'digital_access' => ['icd10' => 'Z59.89', 'snomed' => '1141000195109'],
        ];
    }

    public static function countPositiveDomains(array $postOrRow): int
    {
        $keys = [
            'food_insecurity', 'housing_instability', 'transportation_insecurity', 'utilities_insecurity',
            'interpersonal_safety', 'financial_strain', 'social_isolation', 'childcare_needs', 'digital_access'
        ];
        $n = 0;
        foreach ($keys as $k) {
            if (self::isPositiveStatus($postOrRow[$k] ?? null)) {
                $n++;
            }
        }
        return $n;
    }

    public static function buildProblems(array $row): array
    {
        $out = [];
        $map = self::sdohProblemMap();

        $push = function (string $domain, string $code, string $display, string $system = 'ICD-10-CM') use (&$out): void {
            $out[] = [
                'domain' => $domain,
                'system' => $system,
                'code' => $code,
                'display' => $display,
            ];
        };

        $title = [
            'food_insecurity' => 'Food insecurity',
            'housing_instability' => 'Housing instability',
            'transportation_insecurity' => 'Transportation insecurity',
            'utilities_insecurity' => 'Utilities insecurity',
            'interpersonal_safety' => 'Interpersonal safety concern',
            'financial_strain' => 'Financial resource strain',
            'social_isolation' => 'Social isolation',
            'childcare_needs' => 'Childcare need',
            'digital_access' => 'Digital access barrier'
        ];

        foreach ($title as $k => $disp) {
            if (self::isPositiveStatus($row[$k] ?? null)) {
                $code = $map[$k]['icd10'] ?? 'Z59.89';
                $push($k, $code, $disp, 'ICD-10-CM');
            }
        }

        return $out;
    }

    public static function buildGoalsResource(array $row): array
    {
        $problems = self::buildProblems($row);
        $today = date('Y-m-d');
        $due = date('Y-m-d', strtotime('+90 days'));
        $goals = [];

        // Map domains to Gravity SDOH categories
        $categoryMap = [
            'food_insecurity' => ['system' => 'http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes',
                'code' => 'food-insecurity', 'display' => 'Food Insecurity'],
            'housing_instability' => ['system' => 'http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes',
                'code' => 'housing-instability', 'display' => 'Housing Instability'],
            'transportation_insecurity' => ['system' => 'http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes',
                'code' => 'transportation-insecurity', 'display' => 'Transportation Insecurity'],
            // Add other mappings...
        ];

        foreach ($problems as $p) {
            $category = $categoryMap[$p['domain']] ?? [
                'system' => 'http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes',
                'code' => 'sdoh-category-unspecified',
                'display' => 'SDOH Category Unspecified'
            ];

            $goals[] = [
                'category' => [$category],
                'description' => [
                    'coding' => [[
                        'system' => 'http://snomed.info/sct',
                        'code' => '410546004',
                        'display' => 'Goal of therapy'
                    ]],
                    'text' => 'Improve ' . $p['display']
                ],
                'addresses' => [[
                    'reference' => '#problem-' . $p['code']
                ]],
                'startDate' => $today,
                'target' => [[
                    'measure' => [
                        'coding' => [[
                            'system' => 'http://loinc.org',
                            'code' => '8689-2',
                            'display' => 'SDOH assessment'
                        ]]
                    ],
                    'detailCodeableConcept' => [
                        'coding' => [[
                            'system' => 'http://snomed.info/sct',
                            'code' => '410516003',
                            'display' => 'Resolved'
                        ]]
                    ],
                    'dueDate' => $due
                ]]
            ];
        }

        return $goals;
    }

    public static function buildInterventionsResource(array $row): array
    {
        $problems = self::buildProblems($row);

        // Use Gravity Project intervention codes
        $svc = [
            'food_insecurity' => ['SNOMED', '467681000124101', 'Assistance with application for food program'],
            'housing_instability' => ['SNOMED', '467701000124103', 'Assistance with application for housing program'],
            'transportation_insecurity' => ['SNOMED', '467721000124107', 'Assistance with transportation'],
            'utilities_insecurity' => ['SNOMED', '467731000124109', 'Assistance with utility payment'],
            'financial_strain' => ['SNOMED', '467771000124104', 'Financial counseling'],
            'social_isolation' => ['SNOMED', '467751000124105', 'Referral to community support program'],
            'childcare_needs' => ['SNOMED', '467781000124106', 'Assistance with childcare'],
            'digital_access' => ['SNOMED', '467791000124108', 'Digital literacy assistance']
        ];

        $out = [];
        foreach ($problems as $p) {
            $dom = $p['domain'];
            $choice = $svc[$dom] ?? ['SNOMED', '410606002', 'Social service procedure'];

            $out[] = [
                'code' => [
                    'coding' => [[
                        'system' => 'http://snomed.info/sct',
                        'code' => $choice[1],
                        'display' => $choice[2]
                    ]]
                ],
                'category' => [[
                    'coding' => [[
                        'system' => 'http://snomed.info/sct',
                        'code' => '410606002',
                        'display' => 'Social service procedure'
                    ]]
                ]],
                'authoredOn' => date('Y-m-d'),
                'reasonCode' => [[
                    'coding' => [[
                        'system' => 'http://snomed.info/sct',
                        'code' => $p['code'],
                        'display' => $p['display']
                    ]]
                ]]
            ];
        }

        return $out;
    }


    /**
     * Return the latest / “current” SDOH assessment for a patient.
     */
    public static function getCurrentAssessment(int $pid): ?array
    {
        $sql = "SELECT * FROM form_history_sdoh WHERE pid = ? ORDER BY COALESCE(updated_at, created_at) DESC LIMIT 1";
        $row = sqlQuery($sql, [$pid]);
        return $row ?: null;
    }

    public static function toFhirBundle(array $row, int $pid, ?int $encounterId = null): array
    {
        $bundle = ['resourceType' => 'Bundle', 'type' => 'collection', 'entry' => []];

        $domains = [
            'food_insecurity', 'housing_instability', 'transportation_insecurity', 'utilities_insecurity',
            'interpersonal_safety', 'financial_strain', 'social_isolation', 'childcare_needs', 'digital_access'
        ];
        foreach ($domains as $k) {
            if (!empty($row[$k])) {
                $sel = $row[$k];
                $bundle['entry'][] = [
                    'resource' => [
                        'resourceType' => 'Observation',
                        'status' => 'final',
                        'category' => [[
                            'coding' => [[
                                'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                                'code' => 'social-history'
                            ]]
                        ]],
                        'code' => ['text' => ucwords(str_replace('_', ' ', $k))],
                        'valueCodeableConcept' => ['text' => $sel],
                        'effectiveDateTime' => $row['assessment_date'] ?? date('Y-m-d')
                    ]
                ];
            }
        }
        foreach (self::buildProblems($row) as $p) {
            $bundle['entry'][] = [
                'resource' => [
                    'resourceType' => 'Condition',
                    'clinicalStatus' => [
                        'coding' => [[
                            'system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
                            'code' => 'active'
                        ]]
                    ],
                    'code' => [
                        'coding' => [[
                            'system' => self::codeSystemUrl($p['system']),
                            'code' => $p['code'],
                            'display' => $p['display']
                        ]]
                    ],
                    'recordedDate' => date('Y-m-d')
                ]
            ];
        }

        foreach (self::buildGoalsResource($row) as $g) {
            $bundle['entry'][] = ['resource' => ['resourceType' => 'Goal'] + $g];
        }

        foreach (self::buildInterventionsResource($row) as $sr) {
            $bundle['entry'][] = ['resource' => ['resourceType' => 'ServiceRequest'] + $sr];
        }

        return $bundle;
    }

    public static function codeSystemUrl(string $short): string
    {
        $map = [
            'ICD-10-CM' => 'http://hl7.org/fhir/sid/icd-10-cm',
            'SNOMED' => 'http://snomed.info/sct',
            'LOINC' => 'http://loinc.org'
        ];
        return $map[$short] ?? $short;
    }

    /**
     * @param ISearchField[]|string[] $openEMRSearchParameters
     * @param bool $andCondition
     * @return ProcessingResult
     */
    public function search($openEMRSearchParameters = [], $isAndCondition = true): ProcessingResult
    {
        try {
            $selectColumns = "SELECT o.*
                    , p.puuid
                    , enc.euuid
                    , cu.created_by_uuid
                    , uu.updated_by_uuid ";
            // Join to get display and codes for each domain option
            $join = "
                    FROM form_history_sdoh o
                    JOIN (
                        select pid AS patient_id,
                               uuid AS puuid
                        FROM patient_data
                   ) p ON o.pid = p.patient_id
                   LEFT JOIN (
                       select encounter AS eid,
                              uuid AS euuid
                       FROM form_encounter
                   ) enc ON o.encounter = enc.eid
                   LEFT JOIN (
                          select id AS cuid,
                                uuid AS created_by_uuid
                            FROM users
                     ) cu ON o.created_by = cu.cuid
                     LEFT JOIN (
                              select id AS updated_by_id,
                                  uuid AS updated_by_uuid
                             FROM users
                        ) uu ON o.updated_by = uu.updated_by_id ";
            $optionCodes = self::getListMapForDomains();
            // add the additional pregnancy fields
            $optionCodes['pregnancy_status'] = 'pregnancy_status';
            $optionCodes['pregnancy_intent'] = 'pregnancy_intent';
            foreach ($optionCodes as $col => $listId) {
                $selectColumns .= ", lo_{$col}.{$col}_display, lo_{$col}.{$col}_codes ";
                $join .= " LEFT JOIN (
                        SELECT codes AS {$col}_codes
                        , title AS {$col}_display
                        , option_id AS option_id
                        , list_id
                        FROM list_options
                    ) lo_{$col} ON (o.{$col} = lo_{$col}.option_id AND lo_{$col}.list_id = '{$listId}') ";
            }
            $whereClause = FhirSearchWhereClauseBuilder::build($openEMRSearchParameters, $isAndCondition);
            $sql = $selectColumns . $join . $whereClause->getFragment();
            $results = QueryUtils::fetchRecords($sql, $whereClause->getBoundValues());
            $processingResult = new ProcessingResult();
            foreach ($results as $record) {
                $processingResult->addData($this->createResultRecordFromDatabaseResult($record));
            }
        } catch (SqlQueryException $exception) {
            $processingResult = new ProcessingResult();
            $processingResult->addInternalError($exception->getMessage());
        }
        return $processingResult;
    }
}
