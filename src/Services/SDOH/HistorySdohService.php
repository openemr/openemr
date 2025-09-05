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
                'measure' => $cc('http://loinc.org', '93033-9', 'Are you worried about losing your housing [PRAPARE]'),
                'target' => $cc('http://loinc.org', 'LA32-8', 'No'),
                'description' => ['text' => 'Stable housing'],
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

        $needsGoal = fn($status): bool => in_array($status, ['present', 'at_risk', 'yes'], true);

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

    /**
     * @param int $pid
     * @return array|null
     */
    public static function getCurrentAssessment(int $pid): ?array
    {
        return sqlQuery(
            "SELECT * FROM form_history_sdoh WHERE pid = ? ORDER BY updated_at DESC, id DESC LIMIT 1",
            [$pid]
        ) ?: null;
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
    private function ccSystemToCodeType(?string $system): string
    {
        if (!$system) {
            return '';
        }
        $s = strtolower($system);
        if (strpos($s, 'loinc') !== false) {
            return 'LOINC'; }
        if (strpos($s, 'snomed') !== false) {
            return 'SNOMED-CT'; }
        if (strpos($s, 'rxnorm') !== false) {
            return 'RXNORM'; }
        return '';
    }
    /**
     * Return a display string for a FHIR CodeableConcept or a plain string.
     */
    private function ccText($cc): string
    {
        return HistorySdohService::ccDisplay($cc);
    }
}
