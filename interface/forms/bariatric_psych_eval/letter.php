<?php

/**
 * Bariatric Psych Eval - clearance letter generator.
 * Renders the saved evaluation as a formatted, printable letter matching the practice's
 * own template exactly (section order/wording, and omitting any section with no content
 * rather than showing it blank). Opened via target="_blank" from common.php, so it's a
 * real new browser tab, not something trapped in OpenEMR's frame system.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Barbara Rix <admin@starbirdrisingwellness.com>
 * @copyright Copyright (c) 2026 Barbara Rix <admin@starbirdrisingwellness.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once(__DIR__ . "/bariatric_psych_eval.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Session\PatientSessionUtil;

if (!AclMain::aclCheckCore('patients', 'docs')) {
    die(xlt("Not authorized"));
}

$formIdInput = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$formId = is_int($formIdInput) && $formIdInput > 0 ? $formIdInput : 0;
if ($formId <= 0) {
    die(xlt("Missing form id."));
}

$pid = PatientSessionUtil::getPid();

$data = sqlQuery("SELECT * FROM form_bariatric_psych_eval WHERE id = ? AND pid = ?", [$formId, $pid]);
if (!$data) {
    die(xlt("Evaluation not found for this patient."));
}

$patient = sqlQuery("SELECT fname, lname, DOB FROM patient_data WHERE pid = ?", [$pid]);
$patientName = trim(($patient['fname'] ?? '') . ' ' . ($patient['lname'] ?? ''));
$patientDob = !empty($patient['DOB']) ? oeFormatShortDate($patient['DOB']) : '';

// Default to the evaluating provider's name from their user record rather than leaving it
// blank, if the form's own (editable) Evaluator Name field was never filled in.
$evaluatingUser = sqlQuery("SELECT fname, lname, title FROM users WHERE username = ?", [$data['user'] ?? '']);
$evaluatingProviderName = trim(
    ($evaluatingUser['title'] ?? '') . ' ' . ($evaluatingUser['fname'] ?? '') . ' ' . ($evaluatingUser['lname'] ?? '')
);
$evaluatorName = !empty($data['evaluator_name']) ? $data['evaluator_name'] : $evaluatingProviderName;

/**
 * Prints a paragraph section (header, then body as one or more <p> paragraphs split on blank
 * lines) - only if there's actually content, matching the template's "omit if not mentioned"
 * instruction.
 */
function bpe_print_paragraph_section(string $heading, ?string $body): void
{
    if (empty(trim((string) $body))) {
        return;
    }
    echo '<h2>' . xlt($heading) . '</h2>';
    foreach (preg_split('/\R{2,}/', trim($body)) as $para) {
        echo '<p>' . nl2br(text(trim($para))) . '</p>';
    }
}

/**
 * Prints a bullet-list section (header, then one <li> per non-empty line) - only if there's
 * content.
 */
function bpe_print_bullet_section(string $heading, ?string $body): void
{
    if (empty(trim((string) $body))) {
        return;
    }
    echo '<h2>' . xlt($heading) . '</h2><ul>';
    foreach (preg_split('/\R/', trim($body)) as $line) {
        $line = trim($line);
        if ($line !== '') {
            echo '<li>' . text($line) . '</li>';
        }
    }
    echo '</ul>';
}

/**
 * "Current Functioning" is one letter section made of several labeled bullets - one per
 * sub-field, each only included if that specific sub-field has content.
 *
 * @param array<string, string> $subfields label => value
 */
function bpe_print_current_functioning(array $subfields): void
{
    $nonEmpty = array_filter($subfields, static fn($v) => trim((string) $v) !== '');
    if (empty($nonEmpty)) {
        return;
    }
    echo '<h2>' . xlt('Current Functioning') . '</h2><ul>';
    foreach ($nonEmpty as $label => $value) {
        echo '<li><b>' . text($label) . ':</b> ' . nl2br(text(trim($value))) . '</li>';
    }
    echo '</ul>';
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo xlt('Bariatric Surgery Psychological Evaluation Letter'); ?></title>
<style>
    body { font-family: Georgia, 'Times New Roman', serif; max-width: 720px; margin: 2rem auto; padding: 0 1rem; color: #222; line-height: 1.5; }
    h2 { font-size: 1rem; margin-top: 1.5rem; margin-bottom: 0.4rem; }
    ul { margin-top: 0.2rem; }
    p { margin: 0.5rem 0; }
    .letter-date { margin-bottom: 1.5rem; }
    .signature { margin-top: 2.5rem; }
    @media print {
        .no-print { display: none; }
    }
</style>
</head>
<body>
    <div class="no-print" style="text-align:right; margin-bottom:1rem;">
        <button onclick="window.print();"><?php echo xlt('Print'); ?></button>
    </div>

    <div class="letter-date"><?php echo xlt('Date'); ?>: <?php echo text(oeFormatShortDate(date('Y-m-d'))); ?></div>

    <p><?php echo xlt('Re'); ?>: <?php echo text($patientName); ?><br />
    <?php echo xlt('DOB'); ?>: <?php echo text($patientDob); ?></p>

    <p><?php echo xlt('Dear Bariatric Surgery Team'); ?>,</p>

    <p><?php echo xlt('I am writing to provide a detailed report on') . ' ' . text($patientName) . ', ' . xlt('who presents for a psychiatric evaluation in preparation for bariatric surgery. Below is a summary of the clinical interview and relevant medical history.'); ?></p>

    <?php
    bpe_print_paragraph_section('Presenting Problems', $data['presenting_problems'] ?? null);
    bpe_print_paragraph_section('History of Presenting Problems', $data['history_of_problem'] ?? null);
    bpe_print_current_functioning([
        xl('Sleep') => $data['cf_sleep'] ?? '',
        xl('Employment/Education') => $data['cf_employment_education'] ?? '',
        xl('Family') => $data['cf_family'] ?? '',
        xl('Social') => $data['cf_social'] ?? '',
        xl('Exercise/Physical Activity') => $data['cf_exercise'] ?? '',
        xl('Eating Regime/Appetite') => $data['cf_eating_regime'] ?? '',
        xl('Energy Levels') => $data['cf_energy_levels'] ?? '',
    ]);
    bpe_print_bullet_section('Current Medications', $data['current_medications'] ?? null);
    bpe_print_paragraph_section('Psychiatric History', $data['psychiatric_history'] ?? null);
    bpe_print_paragraph_section('Medical History', $data['medical_history'] ?? null);
    bpe_print_paragraph_section('Developmental, Social, and Family History', $data['developmental_social_family_history'] ?? null);
    bpe_print_bullet_section('Diagnosis', $data['diagnosis'] ?? null);
    bpe_print_paragraph_section('Clinical Formulation', $data['clinical_formulation'] ?? null);
    bpe_print_paragraph_section('Recommendations', $data['recommendations'] ?? null);
    ?>

    <p><?php echo xlt('Please do not hesitate to contact me if you require any additional information or clarification.'); ?></p>

    <div class="signature">
        <p><?php echo xlt('Sincerely'); ?>,</p>
        <p><?php echo text($evaluatorName); ?></p>
    </div>
</body>
</html>
