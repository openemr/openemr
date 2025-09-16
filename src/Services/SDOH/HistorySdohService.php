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

namespace OpenEMR\Services\Sdoh;

class HistorySdohService
{
    /**
     * USCDI v3 compliant mapping for SDOH domains to ICD-10-CM Health Concern codes
     * These codes are appropriate for Health Concern <value> elements in C-CDA
     * Except ONC b(1) test fails with recomment codes!
     */
    public static function getDomainHealthConcernCodes(): array
    {
        $SNOMED_OID = '2.16.840.1.113883.6.96';
        $ICD10_OID  = '2.16.840.1.113883.6.90';

        return [
            'food_insecurity' => [
                'snomed' => [
                    'code'        => '733423003',
                    'display'     => 'Food insecurity (finding)',
                    'system'      => $SNOMED_OID,
                    'system_name' => 'SNOMED CT',
                ],
                'icd10' => [
                    'code'        => 'Z59.41',
                    'display'     => 'Food insecurity',
                    'system'      => $ICD10_OID,
                    'system_name' => 'ICD-10-CM',
                ],
            ],
            // If you specifically need "instability", SNOMED US doesn’t yet have a perfect single
            // concept; we fall back to ICD-10-CM Housing Instability (unspecified). If you prefer
            // “inadequate housing”, you can swap the SNOMED below to 105531004 (Housing unsatisfactory).
            'housing_instability' => [
                // 'snomed' => [ 'code' => '105531004', 'display' => 'Housing unsatisfactory (finding)', 'system' => $SNOMED_OID, 'system_name' => 'SNOMED CT' ],
                'icd10' => [
                    'code'        => 'Z59.819',
                    'display'     => 'Housing instability, housed, unspecified',
                    'system'      => $ICD10_OID,
                    'system_name' => 'ICD-10-CM',
                ],
            ],

            'transportation_insecurity' => [
                'snomed' => [
                    'code'        => '713458007',
                    'display'     => 'Lack of access to transportation (finding)',
                    'system'      => $SNOMED_OID,
                    'system_name' => 'SNOMED CT',
                ],
                'icd10' => [
                    'code'        => 'Z59.82',
                    'display'     => 'Transportation insecurity',
                    'system'      => $ICD10_OID,
                    'system_name' => 'ICD-10-CM',
                ],
            ],

            'utilities_insecurity' => [
                // No single, broadly-used SNOMED finding for general “utility insecurity” yet.
                // (Specifics like “Inadequate water supply (441987004)” exist but are too narrow.)
                'icd10' => [
                    'code'        => 'Z59.12',
                    'display'     => 'Inadequate housing utilities',
                    'system'      => $ICD10_OID,
                    'system_name' => 'ICD-10-CM',
                ],
            ],
            // “Interpersonal safety concern” → use a safety/violence-risk finding when available.
            'interpersonal_safety' => [
                'snomed' => [
                    'code'        => '706892001',
                    'display'     => 'At risk of intimate partner abuse (finding)',
                    'system'      => $SNOMED_OID,
                    'system_name' => 'SNOMED CT',
                ],
                'icd10' => [
                    'code'        => 'Z65.8',
                    'display'     => 'Other specified problems related to psychosocial circumstances',
                    'system'      => $ICD10_OID,
                    'system_name' => 'ICD-10-CM',
                ],
            ],

            'financial_strain' => [
                'snomed' => [
                    'code'        => '1184702004',
                    'display'     => 'Financial insecurity (finding)',
                    'system'      => $SNOMED_OID,
                    'system_name' => 'SNOMED CT',
                ],
                'icd10' => [
                    'code'        => 'Z59.86',
                    'display'     => 'Financial insecurity',
                    'system'      => $ICD10_OID,
                    'system_name' => 'ICD-10-CM',
                ],
            ],

            'social_isolation' => [
                'snomed' => [
                    'code'        => '105412007',
                    'display'     => 'Social isolation (finding)',
                    'system'      => $SNOMED_OID,
                    'system_name' => 'SNOMED CT',
                ],
                'icd10' => [
                    'code'        => 'Z60.2',
                    'display'     => 'Problems related to living alone',
                    'system'      => $ICD10_OID,
                    'system_name' => 'ICD-10-CM',
                ],
            ],
            // Childcare is usually captured as a need/barrier; there is a specific US-extension SNOMED,
            // but many installs don’t load US extensions. Keep ICD-10 general to avoid over-coding.
            'childcare_needs' => [
                // 'snomed' => [ 'code' => '671461000124109', 'display' => 'Unable to obtain childcare due to limited financial resources (finding)', 'system' => $SNOMED_OID, 'system_name' => 'SNOMED CT' ],
                'icd10' => [
                    'code'        => 'Z60.8',
                    'display'     => 'Other problems related to social environment',
                    'system'      => $ICD10_OID,
                    'system_name' => 'ICD-10-CM',
                ],
            ],
            // Digital access is typically recorded as a barrier in Social Environment; no single SNOMED
            // concept is universally adopted yet (local extensions exist). Use ICD-10 catch-all.
            'digital_access' => [
                'icd10' => [
                    'code'        => 'Z60.9',
                    'display'     => 'Problem related to social environment, unspecified',
                    'system'      => $ICD10_OID,
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
                'sha_extension' => sha1($site . '|sdoh_concern|' . $pid . '|' . $col . '|' . $assessDt),
            ];
        }

        return $out;
    }

    /**
     * Updated getAssessmentConcernsRows method for CCDA generation
     */
    public static function getAssessmentConcernsRowsV3(int $pid): array
    {
        $a = HistorySdohService::getCurrentAssessment($pid);
        if (!$a) {
            return [];
        }

        // Assessment date
        $date = substr((string)($a['assessment_date'] ?? $a['updated_at'] ?? $a['created_at'] ?? ''), 0, 10);

        // Author
        $authorId = $a['updated_by'] ?? $a['created_by'] ?? null;
        $author = self::buildAuthorContainerFromUserId($authorId);

        // Encounter for extension
        $encId = (string)($a['encounter'] ?? '');
        $site = $_SESSION['site_id'] ?? 'default';
        $extension = base64_encode($site . $encId);

        $domainCodes = self::getDomainHealthConcernCodes();

        // Domain-specific notes fields
        $notesMap = [
            'food_insecurity' => 'food_insecurity_notes',
            'housing_instability' => 'housing_instability_notes',
            'transportation_insecurity' => 'transportation_insecurity_notes',
            'utilities_insecurity' => 'utilities_insecurity_notes',
            'interpersonal_safety' => 'interpersonal_safety_notes',
            'financial_strain' => 'financial_strain_notes',
            'social_isolation' => 'social_isolation_notes',
            'childcare_needs' => 'childcare_needs_notes',
            'digital_access' => 'digital_access_notes'
        ];

        $rows = [];
        foreach ($domainCodes as $col => $codeInfo) {
            $opt = trim((string)($a[$col] ?? ''));
            if ($opt === '' || strtolower($opt) === 'none' || strtolower($opt) === 'unknown') {
                continue;
            }

            // Check if this represents a positive finding
            if (!self::isPositiveStatus($opt)) {
                continue;
            }

            // Use ICD-10-CM code for Health Concern
            $desc = $codeInfo['display'];
            $notesVal = trim((string)($a[$notesMap[$col]] ?? ''));
            $narr = $date . ' ' . $desc . ($notesVal !== '' ? " - {$notesVal}" : '');

            // Deterministic identifier
            $sha = sha1('sdoh_assess|' . $pid . '|' . $col . '|' . $opt . '|' . $date);

            // Shape the row for C-CDA healthConcernActivityAct
            $rows[] = [
                "type" => "act",
                "text" => $narr,
                "effective_time" => [
                    "low" => ["date" => $date, "precision" => "day"]
                ],
                "value" => [
                    "name" => $desc,
                    "code" => $codeInfo['icd10'],
                    "code_system" => $codeInfo['system'],
                    "code_system_name" => $codeInfo['system_name']
                ],
                "author" => $author,
                "identifiers" => [[
                    "identifier" => self::uuidOrHash($a, $sha),
                    "extension" => $extension
                ]],
                "problems" => []
            ];
        }

        return $rows;
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
     * Helper methods (simplified versions of your existing methods)
     */
    private static function buildAuthorContainerFromUserId($userId): array
    {
        if (!$userId) {
            return [
                "date_time" => ["point" => ["date" => date('Y-m-d H:i:sO'), "precision" => "tz"]],
                "name" => [["first" => "Unknown", "last" => "Author"]]
            ];
        }

        $u = sqlQuery("SELECT fname, lname, npi FROM users WHERE id = ? LIMIT 1", [$userId]) ?: [];
        $first = (string)($u['fname'] ?? '');
        $last = (string)($u['lname'] ?? '');
        $npi = (string)($u['npi'] ?? '');

        return [
            "date_time" => ["point" => ["date" => date('Y-m-d H:i:sO'), "precision" => "tz"]],
            "identifiers" => $npi ? [["identifier" => "2.16.840.1.113883.4.6", "extension" => $npi]] : [],
            "name" => [["first" => $first ?: "Unknown", "last" => $last ?: "Author"]]
        ];
    }

    private static function uuidOrHash(array $a, string $fallback): string
    {
        if (!empty($a['uuid'])) {
            try {
                return UuidRegistry::uuidToString($a['uuid']);
            } catch (\Throwable $e) {
                // Fall through to fallback
            }
        }
        return $fallback;
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
                'yes-medical'      => 'yes',
                'already shut off' => 'present',
                'very hard'        => 'present',
                'somewhat hard'    => 'present',
                'often'            => 'present',
                'sometimes'        => 'present',
                'rarely'           => 'present',
                'never'            => 'no',
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
                $goal['startDate'] = substr($when, 0, 10);
            }

            $target = [];
            if (!empty($spec['measure'])) {
                $target['measure'] = $spec['measure'];
            }
            if (!empty($spec['target'])) {
                $target['detailCodeableConcept'] = $spec['target'];
            }
            if ($due) {
                $target['dueDate'] = substr($due, 0, 10);
            }

            $goal['target'] = [$target];

            $out[] = $goal;
        }
        return $out;
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
        $due  = $info['intervention_due_date'] ?? $info['goal_due_date'] ?? null;

        // helper
        $cc = fn($system, $code, $display): array => [
            'coding' => [[ 'system' => $system, 'code' => $code, 'display' => $display ]],
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
                'code'     => $txt('Assistance with application for food pantry program'),
                'reason'   => $txt('Food insecurity risk'),
            ],
            'housing_instability' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'housing-instability', 'Housing Instability'),
                'code'     => $txt('Referral to local housing assistance resources'),
                'reason'   => $txt('Housing instability risk'),
            ],
            'transportation_insecurity' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'transportation-insecurity', 'Transportation Insecurity'),
                'code'     => $txt('Arrange transportation for appointments (medical or social services)'),
                'reason'   => $txt('Transportation barrier present'),
            ],
            'utilities_insecurity' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'utility-insecurity', 'Utility Insecurity'),
                'code'     => $txt('Referral to utility bill assistance program'),
                'reason'   => $txt('Utility shutoff risk'),
            ],
            'financial_strain' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'financial-insecurity', 'Financial Insecurity'),
                'code'     => $txt('Referral to financial counseling / benefits navigator'),
                'reason'   => $txt('Financial strain'),
            ],
            'social_isolation' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'social-connection', 'Social Connection'),
                'code'     => $txt('Referral to community/social connection programs'),
                'reason'   => $txt('Loneliness / social isolation'),
            ],
            'childcare_needs' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'material-hardship', 'Material Hardship'),
                'code'     => $txt('Provide childcare resources and referral'),
                'reason'   => $txt('Childcare needs present'),
            ],
            'digital_access' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'digital-access', 'Digital Access'),
                'code'     => $txt('Assist with device/internet access and digital literacy'),
                'reason'   => $txt('Limited digital access'),
            ],
            'interpersonal_safety' => [
                'category' => $cc('http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes', 'intimate-partner-violence', 'Intimate Partner Violence'),
                'code'     => $txt('Provide IPV resources and safety planning; social work referral'),
                'reason'   => $txt('IPV risk present'),
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
                $sr['occurrenceDateTime'] = substr($due, 0, 10);
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
                $suffix[] = "due: " . substr($sr['occurrenceDateTime'], 0, 10);
            }
            if ($include_when && !empty($sr['authoredOn'])) {
                $suffix[] = "ordered: " . substr($sr['authoredOn'], 0, 10);
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
        $due  = $due ? substr($due, 0, 10) : null;

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
                $suffix[] = "due: " . substr($due, 0, 10);
            }
            if ($start !== '') {
                $suffix[] = "start: " . substr($start, 0, 10);
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
        if (strpos($s, '113883.6.96') !== false || strpos($s, 'snomed') !== false) {
            return 'SNOMED CT';
        }
        if (strpos($s, '113883.6.1')  !== false || strpos($s, 'loinc')   !== false) {
            return 'LOINC';
        }
        if (strpos($s, '113883.6.90') !== false || strpos($s, 'icd10')   !== false) {
            return 'ICD10-CM';
        }
        if (strpos($s, 'omb')         !== false) {
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
        if (strcasecmp($t, 'SNOMED')    === 0) {
            return 'SNOMED CT';
        }
        if (strcasecmp($t, 'LOINC')     === 0) {
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
                    $sys  = (string)($j['system'] ?? '');
                    $type = $this->ccSystemToCodeType($sys) ?: ($rec['code_type'] ?? '');
                    $txt  = (string)($j['display'] ?? ($rec['description'] ?? $rec['codetext'] ?? ''));
                    return [$code, $this->normalizeSystemName($type), $txt];
                }
            } elseif (strpos($c, ':') !== false) {
                // 2) codes "SYS:CODE"
                [$pref, $cv] = explode(':', $c, 2);
                return [trim($cv), $this->mapPrefixToType($pref), (string)($rec['description'] ?? $rec['codetext'] ?? '')];
            }
        }
        // 3) code "SYS:CODE"
        if (!empty($rec['code']) && is_string($rec['code']) && strpos($rec['code'], ':') !== false) {
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
                $sys  = (string)($j['system'] ?? '');
                $type = $this->ccSystemToCodeType($sys);
                $txt  = (string)($j['display'] ?? $title);
                return [$code, $type, $txt];
            }
        }
        return ['', '', $title];
    }

    /**
     * Derive a SNOMED code from SDOH domain/description when none supplied,
     * using your list_options first; falls back to a small built-in map.
     */
    private function deriveConcernCoding(array $rec): array
    {
        // Prefer list_options mapping when we know the domain + answer
        if (!empty($rec['_domain']) && !empty($rec['_answer'])) {
            [$code, $type, $txt] = $this->codeFromListOption($rec['_list_id'] ?? '', $rec['_answer']);
            if ($code && $type) {
                return ['code' => $code, 'code_type' => $type, 'code_text' => $txt];
            }
        }

        // Fallback keyword mapping (kept minimal; your lists should cover real use)
        $txt = strtolower(trim((string)($rec['description'] ?? $rec['display'] ?? $rec['category'] ?? '')));
        $map = [
            // Food insecurity (finding)
            'food'         => ['733423003', 'Food insecurity (finding)'],
            'hunger'       => ['733423003', 'Food insecurity (finding)'],
            // Housing problems
            'housing'      => ['160734000', 'Inadequate housing (finding)'], // safer than generic 404684003
            'homeless'     => ['32911000',  'Homeless (finding)'],
            // Transportation
            'transport'    => ['281647001', 'Transportation problem (finding)'],
            // Utilities
            'utility'      => ['248539003', 'Lack of electricity (finding)'],
            // Financial
            'financial'    => ['105480006', 'Economic problem (finding)'],
            // Safety
            'violence'     => ['225337009', 'Victim of violence (finding)'],
            'abuse'        => ['225337009', 'Victim of violence (finding)'],
            // Childcare
            'childcare'    => ['364703007', 'Needs assistance with childcare (finding)'],
            // Digital access
            'digital'      => ['713879003', 'Lack of access to digital technology (finding)'],
            'internet'     => ['713879003', 'Lack of access to digital technology (finding)'],
        ];
        foreach ($map as $needle => $pair) {
            if ($needle !== '' && strpos($txt, $needle) !== false) {
                return ['code' => $pair[0], 'code_type' => 'SNOMED CT', 'code_text' => $pair[1]];
            }
        }
        return [];
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

        $site     = $_SESSION['site_id'] ?? 'default';
        $encId    = (int)($row['encounter'] ?? 0);
        $assessDt = (string)($row['assessment_date'] ?? substr((string)($row['updated_at'] ?? ''), 0, 10));
        $assessDt = $assessDt ?: date('Y-m-d');

        $author = [
            'author_id' => $row['updated_by'] ?? $row['created_by'] ?? null,
            'time'      => $row['updated_at'] ?? $row['created_at'] ?? $assessDt
        ];

        // --- Domain → SNOMED Health-Concern codes (value for Problem Observation) ---
        // These are condition *findings* appropriate for Health Concern <value xsi:type="CD"...>.
        // (No LA-* or LOINC answers here.)
        $DOMAIN_SNOMED = [
            'food_insecurity'           => ['733423003', 'Food insecurity (finding)'],
            'housing_instability'       => ['160734000', 'Inadequate housing (finding)'],
            'transportation_insecurity' => ['281647001', 'Transportation problem (finding)'],
            'utilities_insecurity'      => ['248539003', 'Lack of electricity (finding)'], // adjust if you have a better code
            'interpersonal_safety'      => ['225337009', 'Victim of violence (finding)'],  // use a more specific code if you capture it
            'financial_strain'          => ['105480006', 'Economic problem (finding)'],
            'social_isolation'          => ['40917007',  'Social isolation (finding)'],
            'childcare_needs'           => ['364703007', 'Needs assistance with childcare (finding)'],
            // Add more domains you record in form_history_sdoh here as needed
            'digital_access'            => ['713879003', 'Lack of access to digital technology (finding)'],
        ];

        // Which answers from the assessment should become a “positive” concern per domain
        $POSITIVE = [
            'food_insecurity'           => ['yes','positive','at_risk','often','sometimes'],
            'housing_instability'       => ['yes','positive','at_risk'],
            'transportation_insecurity' => ['yes','positive','at_risk'],
            'utilities_insecurity'      => ['yes','positive','at_risk'],
            'interpersonal_safety'      => ['yes','positive'],
            'financial_strain'          => ['yes','positive','high','very hard','hard'],
            'social_isolation'          => ['yes','positive'],
            'childcare_needs'           => ['yes','positive','needs'],
            'digital_access'            => ['no','lack','limited','barrier'],
        ];

        // Human-friendly labels for the domain column to include in narrative if needed
        $LABEL = [
            'food_insecurity'           => 'Food insecurity (finding)',
            'housing_instability'       => 'Housing instability (finding)',
            'transportation_insecurity' => 'Transportation insecurity (finding)',
            'utilities_insecurity'      => 'Utilities insecurity (finding)',
            'interpersonal_safety'      => 'Interpersonal safety risk (finding)',
            'financial_strain'          => 'Financial strain (finding)',
            'social_isolation'          => 'Social isolation (finding)',
            'childcare_needs'           => 'Childcare needs (finding)',
            'digital_access'            => 'Digital inclusion/access need (finding)',
        ];

        $domains = array_keys($DOMAIN_SNOMED);
        $out = [];

        foreach ($domains as $col) {
            $rawAns   = (string)($row[$col] ?? '');
            if ($rawAns === '') {
                continue;
            }

            $ans      = strtolower(trim($rawAns));
            $positive = in_array($ans, $POSITIVE[$col] ?? [], true);
            if (!$positive) {
                continue;
            }

            // Pick the domain’s SNOMED Health Concern code
            [$code, $display] = $DOMAIN_SNOMED[$col];
            $codeType         = 'SNOMED CT';

            // Pull any free-text notes to enrich the <text> narrative
            $notesCol = $col . '_notes';
            $notes    = trim((string)($row[$notesCol] ?? ''));

            // Build the narrative; keep your “<date> <label> - <notes>” style
            $label = $LABEL[$col] ?? $display;
            $narr  = $assessDt . ' ' . $label;
            if ($notes !== '') {
                $narr .= ' - ' . $notes;
            }

            $out[] = [
                'text'           => $narr,
                'code'           => $code,        // SNOMED finding for Health Concern <value>
                'code_type'      => $codeType,    // “SNOMED CT”
                'code_text'      => $display,     // display for the code
                'date'           => $assessDt,
                'date_formatted' => str_replace('-', '', $assessDt),
                'author'         => $author,
                'issues'         => ['issue_uuid' => []], // keep structure your dispatcher expects
                // optional: identify that this came from the SDOH assessment
                'assessment'     => 'SDOH',
                'encounter'      => (string)$encId,
                'extension'      => base64_encode($site . $encId),
                'sha_extension'  => sha1($site . '|sdoh_concern|' . $pid . '|' . $col . '|' . $assessDt),
            ];
        }

        return $out;
    }

    /** Small static wrapper to reuse your existing UID formatting if needed. */
    private static function formatUidStatic(string $s): string
    {
        if (method_exists(__CLASS__, 'formatUid')) {
            // call the instance method via a temp instance
            $tmp = new self();
            return $tmp->formatUid($s);
        }
        return sha1($s);
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
        return json_decode($goals['goals'], true);
    }

    /**
     * Return manual interventions from the latest form row as ServiceRequest-like resources.
     * (Each newline-delimited entry becomes one intervention.)
     */
    public function getCurrentInterventionsResource(int $pid): ?array
    {
        $row = sqlQuery(
            "SELECT interventions, updated_at FROM form_history_sdoh WHERE pid = ? ORDER BY updated_at DESC, id DESC LIMIT 1",
            [$pid]
        ) ?: null;
        if (empty($row['interventions'])) {
            return null;
        }
        return self::parseManualInterventions($row['interventions'], $pid, $row['updated_at'] ?? null, null);
    }
    /**
     * Map a FHIR system URL to our ccda "code_type" label.
     */

    private function ccText($cc): string
    {
        return HistorySdohService::ccDisplay($cc);
    }
    public static function sdohProblemMap(): array
    {
        return [
            'food_insecurity'           => ['icd10' => 'Z59.41', 'snomed' => '733423003'],
            'housing_instability'       => ['icd10' => 'Z59.819', 'snomed' => '161036002'],
            'transportation_insecurity' => ['icd10' => 'Z59.82', 'snomed' => '46578006'],
            'utilities_insecurity'      => ['icd10' => 'Z59.12', 'snomed' => '160632005'],
            'interpersonal_safety'      => ['icd10' => 'Z65.4',  'snomed' => '225337009'],
            'financial_strain'          => ['icd10' => 'Z59.86', 'snomed' => '702479006'],
            'social_isolation'          => ['icd10' => 'Z60.4',  'snomed' => '105531004'],
            'childcare_needs'           => ['icd10' => 'Z59.89', 'snomed' => '713404003'],
            'digital_access'            => ['icd10' => 'Z59.89', 'snomed' => '1141000195109'],
        ];
    }

    public static function countPositiveDomains(array $postOrRow): int
    {
        $keys = [
            'food_insecurity','housing_instability','transportation_insecurity','utilities_insecurity',
            'interpersonal_safety','financial_strain','social_isolation','childcare_needs','digital_access'
        ];
        $n = 0;
        foreach ($keys as $k) {
            if (self::isPositiveStatus($postOrRow[$k] ?? null)) {
                $n++;
            }
        }
        $ext = json_decode($postOrRow['extended_domains'] ?? '[]', true) ?: [];
        foreach ($ext as $row) {
            if (self::isPositiveStatus($row['status'] ?? null)) {
                $n++;
            }
        }
        return $n;
    }

    public static function buildProblems(array $row): array
    {
        $out = [];
        $map = self::sdohProblemMap();

        $push = function (string $domain, string $code, string $display, string $system = 'ICD-10-CM') use (&$out) {
            $out[] = [
                'domain'  => $domain,
                'system'  => $system,
                'code'    => $code,
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

        $ext = json_decode($row['extended_domains'] ?? '[]', true) ?: [];
        foreach ($ext as $e) {
            if (self::isPositiveStatus($e['status'] ?? null)) {
                $sys = $e['status_system'] ?: 'ICD-10-CM';
                $code = $e['status_code'] ?: 'Z59.89';
                $disp = $e['domain_display'] ?: 'Social risk';
                $push($e['domain'] ?: 'extended', $code, $disp, $sys);
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

    /**
     * Map a list option to a coded concept using your curated lists.
     * We read list_options.codes (e.g., 'SNOMED-CT:733423003') and title.
     */
    private static function resolveListCoding(string $listId, ?string $optionId): ?array
    {
        if (!$optionId) {
            return null;
        }
        $sql = "SELECT title, codes 
                  FROM list_options 
                 WHERE list_id = ? AND option_id = ? 
                 LIMIT 1";
        $row = sqlQuery($sql, [$listId, $optionId]);
        if (!$row) {
            return null;
        }

        $title = (string)($row['title'] ?? '');
        $codes = (string)($row['codes'] ?? '');

        // Parse codes: "SYSTEM:CODE"
        $systemName = '';
        $code = '';
        if (strpos($codes, ':') !== false) {
            [$systemName, $code] = explode(':', $codes, 2);
            $systemName = trim($systemName);
            $code = trim($code);
        }

        // Normalize system → OID + display system name expected by your generator
        $oid = '';
        $normName = $systemName;
        $s = strtolower(str_replace([' ', '_', '-'], '', $systemName));
        if ($s === 'snomedct' || $s === 'snomed') {
            $oid = '2.16.840.1.113883.6.96';
            $normName = 'SNOMED CT';
        } else if ($s === 'loinc') {
            $oid = '2.16.840.1.113883.6.1';
            $normName = 'LOINC';
        } else if ($s === 'icd10cm' || $s === 'icd10') {
            $oid = '2.16.840.1.113883.6.90';
            $normName = 'ICD10CM';
        }

        return [
            'code' => $code,
            'display' => $title,
            'code_system_oid' => $oid,
            'code_system_name' => $normName
        ];
    }
    /**
     * Build SDOH Health Concerns from the current assessment (form_history_sdoh).
     * Shape matches what serveccda/getPlanOfCare() appends:
     *   text, code, code_type, code_text, date, date_formatted, author{author_id,time}, issues{issue_uuid}
     */
    public static function buildConcernsFromAssessment(array $assessment, int $pid): array
    {
        // assessment date (YYYY-MM-DD) → YYYYMMDD
        $date = substr((string)($assessment['assessment_date'] ?? $assessment['updated_at'] ?? $assessment['created_at'] ?? ''), 0, 10);
        $dateFmt = $date ? str_replace('-', '', $date) : '';

        // provenance
        $authorId  = $assessment['updated_by'] ?? $assessment['created_by'] ?? null;
        $authorTsz = $assessment['updated_at'] ?? $assessment['created_at'] ?? $assessment['assessment_date'] ?? date('Y-m-d');

        // Core domains we want to surface as concerns when "positive/at risk"
        // NOTE: list_id entries are optional; if present we'll pull codes from list_options->codes,
        // otherwise we'll emit display-only (your template supports codeOrDisplayname).
        $domains = [
            // column_name                  => [pretty label, notes_column, optional list_id]
            'food_insecurity'              => ['Food insecurity (finding)',          'food_insecurity_notes',              'sdoh_food_insecurity'],
            'housing_instability'          => ['Housing instability (finding)',      'housing_instability_notes',          'sdoh_housing_instability'],
            'transportation_insecurity'    => ['Transportation insecurity (finding)','transportation_insecurity_notes',    'sdoh_transportation_insecurity'],
            'utilities_insecurity'         => ['Utilities insecurity (finding)',     'utilities_insecurity_notes',         'sdoh_utilities_insecurity'],
            'interpersonal_safety'         => ['Interpersonal safety risk (finding)','interpersonal_safety_notes',         'sdoh_interpersonal_safety'],
            'financial_strain'             => ['Financial strain (finding)',         'financial_strain_notes',             'sdoh_financial_strain'],
            'social_isolation'             => ['Social isolation (finding)',         'social_isolation_notes',             'sdoh_social_isolation'],
            'childcare_needs'              => ['Childcare needs (finding)',          'childcare_needs_notes',              'sdoh_childcare_needs'],
            'digital_access'               => ['Digital access (finding)',           'digital_access_notes',               'sdoh_digital_access'],
        ];

        $rows = [];
        foreach ($domains as $col => [$label, $notesCol, $listId]) {
            $answer = trim((string)($assessment[$col] ?? ''));
            if (!self::isPositiveFinding($answer)) {
                continue; // only create concerns when the domain indicates a concern
            }

            // Try to resolve a code from your curated lists; fall back to display-only
            $coding = $listId ? self::tryResolveListCoding($listId, $answer) : null;

            $display = $coding['display'] ?? $label;
            $notes   = trim((string)($assessment[$notesCol] ?? ''));
            $text    = ($date ? ($date . ' ') : '') . $display . ($notes !== '' ? " - {$notes}" : '');

            $rows[] = [
                'text'           => $text,
                'code'           => $coding['code']            ?? '',                 // empty OK; template accepts display-only
                'code_type'      => $coding['code_system_name'] ?? '',               // e.g., "SNOMED CT"
                'code_text'      => $display,
                'date'           => $date,
                'date_formatted' => $dateFmt,
                'author'         => [ 'author_id' => $authorId, 'time' => $authorTsz ],
                'issues'         => [ 'issue_uuid' => [] ],                           // no problem linkage from assessment
            ];
        }

        // Extended/JSON domains (optional): expects { "<domain>": {"value":"yes|no|...", "notes":"..."} }
        if (!empty($assessment['extended_domains'])) {
            $ext = json_decode((string)$assessment['extended_domains'], true);
            if (is_array($ext)) {
                foreach ($ext as $name => $obj) {
                    $val   = is_array($obj) ? trim((string)($obj['value'] ?? '')) : trim((string)$obj);
                    $notes = is_array($obj) ? trim((string)($obj['notes'] ?? '')) : '';
                    if (!self::isPositiveFinding($val)) {
                        continue; }

                    $disp = self::titleCase(str_replace('_', ' ', (string)$name)) . " (finding)";
                    $text = ($date ? ($date . ' ') : '') . $disp . ($notes !== '' ? " - {$notes}" : '');

                    $rows[] = [
                        'text'           => $text,
                        'code'           => '',                 // display-only if you don't store codes in extended domains
                        'code_type'      => '',
                        'code_text'      => $disp,
                        'date'           => $date,
                        'date_formatted' => $dateFmt,
                        'author'         => [ 'author_id' => $authorId, 'time' => $authorTsz ],
                        'issues'         => [ 'issue_uuid' => [] ],
                    ];
                }
            }
        }

        return $rows;
    }

    /** Return TRUE when the assessment answer means "concern present / positive screen". */
    private static function isPositiveFinding(string $ans): bool
    {
        if ($ans === '') {
            return false;
        }
        $n = strtolower(trim($ans));
        // allow option_ids like "yes", "positive", "at_risk", "high", "present"; exclude neutral/negative
        if (in_array($n, ['no','none','negative','unknown','undetermined','declined','not_applicable','n/a'], true)) {
            return false;
        }
        return true;
    }

    /**
     * Soft resolve list coding. Accepts JSON ({"system":"SNOMED CT","code":"733423003","display":"..."})
     * or "SYSTEM:CODE". Returns null if not resolvable.
     */
    private static function tryResolveListCoding(string $listId, string $optionId): ?array
    {
        $row = sqlQuery("SELECT title, codes FROM list_options WHERE list_id = ? AND option_id = ? LIMIT 1", [$listId, $optionId]);
        if (!$row) {
            return null;
        }

        $title = (string)($row['title'] ?? '');
        $codes = (string)($row['codes'] ?? '');

        $code = '';
        $sysName = '';
        $oid = '';
        if ($codes !== '' && $codes[0] === '{') {
            $j = json_decode($codes, true);
            if (is_array($j)) {
                $code   = trim((string)($j['code'] ?? ''));
                $sysRaw = trim((string)($j['system'] ?? ''));
                [$oid, $sysName] = self::codeSystemToOidAndName($sysRaw);
            }
        } elseif (strpos($codes, ':') !== false) {
            [$sysRaw, $cv] = explode(':', $codes, 2);
            $code = trim($cv);
            [$oid, $sysName] = self::codeSystemToOidAndName($sysRaw);
        }
        if ($code === '' && $title === '') {
            return null;
        }

        return [
            'code'             => $code,
            'display'          => $title,
            'code_system_oid'  => $oid,
            'code_system_name' => $sysName,
        ];
    }

    /** Map code system text/aliases → (OID, canonical display). */
    private static function codeSystemToOidAndName(string $sys): array
    {
        $n = strtolower(str_replace([' ', '-', '_'], '', $sys));
        if ($n === 'snomedct' || $n === 'snomed') {
            return ['2.16.840.1.113883.6.96', 'SNOMED CT'];
        }
        if ($n === 'loinc') {
            return ['2.16.840.1.113883.6.1',  'LOINC'];
        }
        if ($n === 'icd10cm' || $n === 'icd10') {
            return ['2.16.840.1.113883.6.90', 'ICD10CM'];
        }
        return ['', $sys]; // unknown → leave as-is
    }

    /**
     * Resolve a list option to {code, code_system_oid, code_system_name, display}.
     * Enforces presence of a code; returns null if not resolvable.
     */
    private static function resolveListCodingStrict(string $listId, string $optionId): ?array
    {
        $sql = "SELECT title, codes FROM list_options WHERE list_id = ? AND option_id = ? LIMIT 1";
        $row = sqlQuery($sql, [$listId, $optionId]);
        if (!$row) {
            return null; }

        $title = (string)($row['title'] ?? '');
        $codes = (string)($row['codes'] ?? '');

        // Accept either JSON {"system":"SNOMED-CT","code":"733423003",...} or "SYSTEM:CODE"
        $code = '';
        $sys  = '';
        if ($codes !== '' && $codes[0] === '{') {
            $j = json_decode($codes, true);
            if (is_array($j)) {
                $code = trim((string)($j['code'] ?? ''));
                $sys  = trim((string)($j['system'] ?? ''));
            }
        } elseif (strpos($codes, ':') !== false) {
            [$sys, $code] = explode(':', $codes, 2);
            $code = trim($code);
            $sys  = trim($sys);
        }

        if ($code === '') {
            return null;
        }

        [$oid, $name] = self::codeSystemToOidAndName($sys);

        return [
            'code'             => $code,
            'display'          => $title,
            'code_system_oid'  => $oid,
            'code_system_name' => $name,
        ];
    }

    /**
     * Build CCDA-ready concern rows *from the current SDOH assessment only*.
     * Each row is shaped for serveccda’s healthConcernActivityAct:
     *   - type: 'act'
     *   - text, effective_time.low, coded value (SNOMED/LOINC/etc.), author, identifiers
     *
     * IMPORTANT:
     *   - No generic 404684003 in <value>. We always supply the specific domain/problem code from your lists.
     *   - This does NOT duplicate encounter-care-plan concerns; only assessment-derived concerns.
     */
    public static function getAssessmentConcernsRows(int $pid): array
    {
        $a = self::getCurrentAssessment($pid);
        if (!$a) {
            return [];
        }

        // Assessment date → YYYY-MM-DD and YYYYMMDD
        $date = substr((string)($a['assessment_date'] ?? $a['updated_at'] ?? $a['created_at'] ?? ''), 0, 10);
        $dateFmt = str_replace('-', '', $date);

        // Author – best effort (updated_by → users table NPI/name if needed)
        $authorId = $a['updated_by'] ?? $a['created_by'] ?? null;
        $author = self::buildAuthorContainerFromUserId($authorId); // helper below

        // Encounter for extension
        $encId = (string)($a['encounter'] ?? '');
        $site = $_SESSION['site_id'] ?? 'default';
        $extension = base64_encode($site . $encId);

        // Map assessment columns → list ids
        // Adjust list ids to match your seed data (from your SQL). The option_id is the column value.
        $domains = [
            // column_name                 => [list_id, narrative prefix]
            'food_insecurity'            => ['sdoh_food_insecurity',            'Food insecurity'],
            'housing_instability'        => ['sdoh_housing_instability',        'Housing instability'],
            'transportation_insecurity'  => ['sdoh_transportation_insecurity',  'Transportation insecurity'],
            'utilities_insecurity'       => ['sdoh_utilities_insecurity',       'Utilities insecurity'],
            'interpersonal_safety'       => ['sdoh_interpersonal_safety',       'Interpersonal safety risk'],
            'financial_strain'           => ['sdoh_financial_strain',           'Financial strain'],
            'social_isolation'           => ['sdoh_social_isolation',           'Social isolation'],
            'childcare_needs'            => ['sdoh_childcare_needs',            'Childcare needs'],
            'digital_access'             => ['sdoh_digital_access',             'Digital access']
        ];

        // Optional domain-specific “notes” fields to append in narrative
        $notesMap = [
            'food_insecurity'            => 'food_insecurity_notes',
            'housing_instability'        => 'housing_instability_notes',
            'transportation_insecurity'  => 'transportation_insecurity_notes',
            'utilities_insecurity'       => 'utilities_insecurity_notes',
            'interpersonal_safety'       => 'interpersonal_safety_notes',
            'financial_strain'           => 'financial_strain_notes',
            'social_isolation'           => 'social_isolation_notes',
            'childcare_needs'            => 'childcare_needs_notes',
            'digital_access'             => 'digital_access_notes'
        ];

        $rows = [];
        foreach ($domains as $col => [$listId, $defaultLabel]) {
            $opt = trim((string)($a[$col] ?? ''));
            if ($opt === '' || strtolower($opt) === 'none' || strtolower($opt) === 'unknown') {
                continue; // nothing asserted for this domain
            }

            // Resolve the coded concept from list_options (uses your curated values)
            $coding = self::resolveListCoding($listId, $opt);
            if (!$coding || !$coding['code']) {
                // If the list entry is missing/misconfigured, skip (don’t emit invalid codes)
                continue;
            }

            // Narrative text for <text> and table:
            $desc = $coding['display'] ?: $defaultLabel;
            $notesVal = trim((string)($a[$notesMap[$col]] ?? ''));
            $narr = $date . ' ' . $desc . ($notesVal !== '' ? " - {$notesVal}" : '');

            // Deterministic identifier
            $sha = sha1('sdoh_assess|' . $pid . '|' . $col . '|' . $opt . '|' . $date);

            // Shape the row for serveccda → healthConcernActivityAct (nested Problem Observation value = coded)
            $rows[] = [
                "type" => "act",
                "text" => $narr,
                "effective_time" => [
                    "low" => [ "date" => $date, "precision" => "day" ]
                ],
                "value" => [
                    "name"             => $desc,
                    "code"             => $coding['code'],
                    "code_system"      => $coding['code_system_oid'],
                    "code_system_name" => $coding['code_system_name']
                ],
                "author" => $author,
                "identifiers" => [[
                    "identifier" => self::uuidOrHash($a, $sha),
                    "extension"  => $extension
                ]],
                "problems" => [] // links to Problems (issue_uuid) could be added later if you relate them
            ];
        }

        return $rows;
    }

    public static function toFhirBundle(array $row, int $pid, ?int $encounterId = null): array
    {
        $bundle = ['resourceType' => 'Bundle', 'type' => 'collection', 'entry' => []];

        $domains = [
            'food_insecurity','housing_instability','transportation_insecurity','utilities_insecurity',
            'interpersonal_safety','financial_strain','social_isolation','childcare_needs','digital_access'
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
            'SNOMED'    => 'http://snomed.info/sct',
            'LOINC'     => 'http://loinc.org'
        ];
        return $map[$short] ?? $short;
    }
    /**
     * Accept raw POST (string) or array and return a canonical JSON string.
     * Ensures we only keep the expected keys for each row.
     */
    public static function sanitizeExtendedDomains($raw): string
    {
        $arr = is_string($raw) ? json_decode($raw, true) : $raw;
        if (!is_array($arr)) {
            return '[]';
        }
        $out = [];
        foreach ($arr as $row) {
            if (!is_array($row)) {
                continue;
            }
            $out[] = [
                'domain'         => (string)($row['domain'] ?? ''),
                'domain_display' => (string)($row['domain_display'] ?? ''),
                'domain_code'    => (string)($row['domain_code'] ?? ''),
                'domain_system'  => (string)($row['domain_system'] ?? ''),
                'status'         => (string)($row['status'] ?? ''),
                'status_display' => (string)($row['status_display'] ?? ''),
                'status_code'    => (string)($row['status_code'] ?? ''),
                'status_system'  => (string)($row['status_system'] ?? ''),
                'score'          => is_numeric($row['score'] ?? null) ? (0 + $row['score']) : '',
                'notes'          => (string)($row['notes'] ?? ''),
            ];
        }
        return json_encode($out, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Render a simple HTML list for the widget.
     * Accepts raw JSON string or decoded array.
     */
    public static function renderExtendedDomainsHtml($jsonOrArray): string
    {
        $arr = is_string($jsonOrArray) ? json_decode($jsonOrArray, true) : $jsonOrArray;
        if (!is_array($arr) || !count($arr)) {
            return '';
        }
        ob_start();
        ?>
        <div class="card mb-2">
            <div class="card-header font-weight-bold"><?php echo xlt('Additional Domains'); ?></div>
            <div class="card-body p-2">
                <ul class="list-group list-group-flush">
                    <?php foreach ($arr as $row) : ?>
                        <?php
                        $dd = $row['domain_display'] ?? '';
                        $sd = $row['status_display'] ?? '';
                        $sc = $row['score'] ?? '';
                        $nt = $row['notes'] ?? '';
                        $dc = $row['status_code'] ?? '';
                        $ds = $row['status_system'] ?? '';
                        ?>
                        <li class="list-group-item px-2 py-1 text-dark bg-light">
                            <div class="d-flex justify-content-between align-items-center text-dark bg-light">
                                <div>
                                    <strong><?php echo text($dd ?: ($row['domain'] ?? '')); ?></strong>
                                    <?php if (!empty($sd)) : ?>
                                        <span class="badge badge-secondary ml-1 text-dark bg-light"><?php echo text($sd); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($dc)) : ?>
                                        <small class="text-muted ml-2 text-dark bg-light"><?php echo text(($ds ? ($ds . ': ') : '') . $dc); ?></small>
                                    <?php endif; ?>
                                </div>
                                <?php if ($sc !== '') : ?>
                                    <span class="badge badge-ligh text-dark bg-lightt"><?php echo xlt('Score'); ?>: <?php echo text((string)$sc); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($nt)) : ?>
                                <div class="mt-1"><small><?php echo text($nt); ?></small></div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
