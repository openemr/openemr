<?php

/**
 * bariatric_psych_eval.inc - labels and option lists for the Bariatric Psych Eval form
 * Modeled on interface/forms/phq9/phq9.inc.php
 *
 * Field/section structure mirrors the practice's own clearance-letter template exactly
 * (see letter.php), so the chart-note form and the generated letter stay in lockstep -
 * every letter section has one corresponding narrative field here. A few additional
 * structured fields (contraindication screen, substance-use flag, recommendation status)
 * are chart-only and don't appear in the letter itself - they're for the provider's own
 * tracking/reporting.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Barbara Rix <admin@starbirdrisingwellness.com>
 * @copyright Copyright (c) 2026 Barbara Rix <admin@starbirdrisingwellness.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

if (!defined('OPENEMR_GLOBALS_LOADED')) {
    http_response_code(404);
    exit();
}

$str_form_name = xl('Bariatric Surgery Psychological Evaluation');
$str_form_title = xl('Bariatric Psych Eval');

$str_yes = xl('Yes');
$str_no = xl('No');

$str_substance_use_options = [
    '' => xl('Please select'),
    'none' => xl('None reported'),
    'past_resolved' => xl('Past use, resolved'),
    'current_concern' => xl('Current concern'),
];

$str_recommendation_options = [
    '' => xl('Please select'),
    'cleared' => xl('Cleared for surgery'),
    'cleared_conditions' => xl('Cleared with conditions'),
    'deferred' => xl('Deferred - recommend further treatment before clearance'),
    'not_recommended' => xl('Not recommended at this time'),
];

// [section heading, [fieldName => [label, type]]]
// type: text, date, textarea, yesno, select_substance, select_recommendation
$str_sections = [
    xl('Evaluation Details') => [
        'eval_date' => [xl('Evaluation Date'), 'date'],
        'evaluator_name' => [xl('Evaluator Name (as it should appear on the letter)'), 'text'],
        'procedure_type' => [xl('Planned Procedure'), 'text'],
        'referring_provider' => [xl('Referring Provider'), 'text'],
    ],
    xl('Presenting Problems') => [
        'presenting_problems' => [xl('History of obesity, current weight/height, chronic pain, mobility issues, cardiac/other conditions, dietary efforts'), 'textarea'],
    ],
    xl('History of Presenting Problems') => [
        'history_of_problem' => [xl('History with weight concerns: age of onset, highest/lowest adult weights, previous weight-loss attempts, yo-yo dieting'), 'textarea'],
    ],
    xl('Current Functioning') => [
        'cf_sleep' => [xl('Sleep (patterns, BiPAP/CPAP use, pain, restfulness)'), 'textarea'],
        'cf_employment_education' => [xl('Employment / Education'), 'textarea'],
        'cf_family' => [xl('Family (living arrangements, relationships)'), 'textarea'],
        'cf_social' => [xl('Social (activities, caregiver support, community involvement)'), 'textarea'],
        'cf_exercise' => [xl('Exercise / Physical Activity (including limitations)'), 'textarea'],
        'cf_eating_regime' => [xl('Eating Regime / Appetite (diet, food preferences, appetite medications)'), 'textarea'],
        'cf_energy_levels' => [xl('Energy Levels'), 'textarea'],
    ],
    xl('Current Medications') => [
        'current_medications' => [xl('All current medications'), 'textarea'],
    ],
    xl('Psychiatric History') => [
        'psychiatric_history' => [xl('Diagnoses, hospitalizations/outpatient treatment history, current psychiatric medications'), 'textarea'],
    ],
    xl('Medical History') => [
        'medical_history' => [xl('Chronic medical conditions and surgical history'), 'textarea'],
    ],
    xl('Developmental, Social, and Family History') => [
        'developmental_social_family_history' => [xl("Childhood, family history of mental illness, current support network"), 'textarea'],
    ],
    xl('Diagnosis') => [
        'diagnosis' => [xl('Relevant diagnoses with codes'), 'textarea'],
    ],
    xl('Clinical Formulation') => [
        'clinical_formulation' => [xl('Summary of chronic conditions, management, physical functioning, social support, current limitations'), 'textarea'],
    ],
    xl('Recommendations') => [
        'recommendations' => [xl('Recommendations for proceeding with surgery, mental health treatment, and post-surgery care'), 'textarea'],
    ],
    xl('Chart Tracking (not included in the letter)') => [
        'binge_eating_flag' => [xl('Binge eating currently present'), 'yesno'],
        'suicidality_history' => [xl('History of suicidal ideation or attempts; current risk'), 'textarea'],
        'current_therapy' => [xl('Currently in psychotherapy'), 'yesno'],
        'current_therapy_details' => [xl('Therapy details (provider, frequency, focus)'), 'textarea'],
        'substance_use_flag' => [xl('Overall substance use concern'), 'select_substance'],
        'active_untreated_psychiatric_illness' => [xl('Active, untreated psychiatric illness'), 'yesno'],
        'active_substance_abuse' => [xl('Active substance abuse'), 'yesno'],
        'untreated_eating_disorder' => [xl('Untreated eating disorder'), 'yesno'],
        'cognitive_impairment_limiting_consent' => [xl('Cognitive impairment limiting informed consent'), 'yesno'],
        'unrealistic_expectations_flag' => [xl('Significantly unrealistic expectations'), 'yesno'],
        'recommendation' => [xl('Recommendation status'), 'select_recommendation'],
    ],
];

/**
 * The patient's most recent prior Bariatric Psych Eval, for the "Copy Forward from Last
 * Evaluation" button in common.php. Returns null if the patient has no prior evaluation.
 *
 * @return array<string, mixed>|null
 */
function bpe_get_latest_prior_eval(int $pid): ?array
{
    return sqlQuery("SELECT * FROM form_bariatric_psych_eval WHERE pid = ? ORDER BY date DESC LIMIT 1", [$pid]) ?: null;
}
