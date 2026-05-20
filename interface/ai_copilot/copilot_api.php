<?php

/**
 * OpenEMR Medical Co-Pilot Demo JSON endpoint.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use PHPMailer\PHPMailer\PHPMailer;

const AI_COPILOT_SAFETY_NOTE = 'Draft only. Human review required. This does not replace clinical, billing, or compliance review.';
const AI_COPILOT_NO_PATIENT_NOTE = 'No demo patient is selected, so this answer is general guidance only. Select a demo patient for chart-specific support.';

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$requestStartedAt = microtime(true);

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    aiCopilotJsonResponse(405, [
        'error' => 'Use POST for this endpoint.',
        'meta' => aiCopilotErrorMeta(aiCopilotResolveRequestId(null), 'method_not_allowed'),
    ]);
    exit;
}

$rawInput = file_get_contents('php://input') ?: '';
$requestId = aiCopilotResolveRequestId(null);
$payload = json_decode($rawInput, true);
if (!is_array($payload)) {
    aiCopilotJsonResponse(400, [
        'error' => 'Invalid JSON request body.',
        'meta' => aiCopilotErrorMeta($requestId, 'invalid_json'),
    ]);
    exit;
}

$requestId = aiCopilotResolveRequestId($payload['request_id'] ?? $payload['trace_id'] ?? null);

$csrfToken = $payload['csrf_token_form'] ?? '';
if (!is_string($csrfToken) || !CsrfUtils::verifyCsrfToken($csrfToken, session: $session)) {
    aiCopilotJsonResponse(403, [
        'error' => xla('Authentication Error'),
        'meta' => aiCopilotErrorMeta($requestId, 'authentication_error'),
    ]);
    exit;
}

$action = aiCopilotResolveAction($payload['action'] ?? 'chat');
$roleCatalog = aiCopilotRoleCatalog();
$role = aiCopilotResolveRole($payload['role'] ?? 'doctor', $roleCatalog);
$validModes = aiCopilotModeCatalog();

$patientId = filter_var($payload['patient_id'] ?? $payload['pid'] ?? null, FILTER_VALIDATE_INT);
$patient = [];
if ($patientId !== false && $patientId !== null && $patientId > 0) {
    $patient = sqlQuery(
        "SELECT pid, pubpid, fname, lname, DOB, sex, genericval1, genericval2, email, phone_home, phone_cell
         FROM patient_data
         WHERE pid = ?
           AND genericname1 = 'demo_scenario'
         LIMIT 1",
        [$patientId]
    ) ?: [];

    if (empty($patient)) {
        aiCopilotJsonResponse(404, [
            'error' => 'Selected demo patient was not found.',
            'meta' => aiCopilotErrorMeta($requestId, 'patient_not_found'),
        ]);
        exit;
    }
}

$requestedMode = is_string($payload['mode'] ?? '') ? trim($payload['mode']) : '';

if ($action === 'send_reminder_email') {
    $fullContext = !empty($patient)
        ? aiCopilotBuildContext($patient, 'send_reminder')
        : aiCopilotBuildGeneralContext('send_reminder', '');
    $context = aiCopilotFilterContextForRole($fullContext, $role, 'send_reminder');
    $result = aiCopilotSendReminderEmailAction($role, $context);
    $result['meta'] = aiCopilotBuildResponseMeta(
        $requestId,
        $role,
        'send_reminder',
        $context,
        [
            'answer' => $result['message'] ?? '',
            'sections' => [],
            'tags' => $result['tags'] ?? [],
        ],
        [
            'restricted_by_role' => $role !== 'front_desk',
            'restriction_type' => $role !== 'front_desk' ? 'front_desk_reminder_permission_block' : null,
            'fallback_used' => false,
        ],
        $requestStartedAt
    );
    aiCopilotJsonResponse(200, $result);
    exit;
}

$message = aiCopilotNormalizePrompt($payload['message'] ?? '');
if ($message === '') {
    aiCopilotJsonResponse(400, [
        'error' => 'Enter a prompt before sending.',
        'meta' => aiCopilotErrorMeta($requestId, 'empty_prompt'),
    ]);
    exit;
}

$mode = aiCopilotResolveMode($requestedMode, $message, $validModes);
$chatHistory = aiCopilotNormalizeChatHistory($payload['chat_history'] ?? []);
$fullContext = !empty($patient)
    ? aiCopilotBuildContext($patient, $mode)
    : aiCopilotBuildGeneralContext($mode, $message);
$context = aiCopilotFilterContextForRole($fullContext, $role, $mode);
$sources = aiCopilotBuildSources($context);
$permissionResponse = aiCopilotMaybeBuildRolePermissionResponse($role, $mode, $message, $context);
if ($permissionResponse !== []) {
    $meta = aiCopilotBuildResponseMeta(
        $requestId,
        $role,
        $mode,
        $context,
        $permissionResponse,
        [
            'restricted_by_role' => true,
            'restriction_type' => $permissionResponse['restriction_type'] ?? 'role_guardrail',
            'fallback_used' => false,
        ],
        $requestStartedAt
    );
    aiCopilotJsonResponse(200, [
        'ok' => true,
        'mode' => $mode,
        'role' => $role,
        'patient' => !empty($context['patient']['name']) ? $context['patient']['name'] : null,
        'answer' => $permissionResponse['answer'],
        'sections' => $permissionResponse['sections'],
        'tags' => $permissionResponse['tags'],
        'sources' => $sources,
        'safety_note' => aiCopilotRoleSafetyNote($role),
        'engine' => 'guardrail',
        'meta' => $meta,
    ]);
    exit;
}

$draft = aiCopilotGenerateDraft($role, $mode, $message, $chatHistory, $context, $validModes[$mode]);
$meta = aiCopilotBuildResponseMeta(
    $requestId,
    $role,
    $mode,
    $context,
    $draft,
    [
        'restricted_by_role' => false,
        'fallback_used' => ($draft['engine'] ?? '') === 'fallback',
        'fallback_reason' => $draft['fallback_reason'] ?? null,
    ],
    $requestStartedAt
);

aiCopilotJsonResponse(200, [
    'ok' => true,
    'mode' => $mode,
    'role' => $role,
    'patient' => !empty($context['patient']['name']) ? $context['patient']['name'] : null,
    'answer' => $draft['answer'],
    'sections' => $draft['sections'],
    'tags' => $draft['tags'],
    'sources' => $sources,
    'safety_note' => aiCopilotRoleSafetyNote($role),
    'engine' => $draft['engine'],
    'meta' => $meta,
]);

function aiCopilotJsonResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function aiCopilotCreateId(string $prefix): string
{
    try {
        return $prefix . '_' . bin2hex(random_bytes(8));
    } catch (\Throwable) {
        return $prefix . '_' . str_replace('.', '', uniqid('', true));
    }
}

function aiCopilotResolveRequestId(mixed $value): string
{
    $candidate = is_string($value) ? trim($value) : '';
    if ($candidate === '') {
        return aiCopilotCreateId('request');
    }

    return preg_replace('/[^a-zA-Z0-9_\-]/', '', $candidate) ?: aiCopilotCreateId('request');
}

function aiCopilotErrorMeta(string $requestId, string $errorCategory): array
{
    return [
        'request_id' => $requestId,
        'error_category' => $errorCategory,
        'fallback_used' => false,
    ];
}

function aiCopilotResponseTextLength(array $response): int
{
    $parts = [];
    $answer = aiCopilotCleanText($response['answer'] ?? '');
    if ($answer !== '') {
        $parts[] = $answer;
    }

    foreach (($response['sections'] ?? []) as $section) {
        if (!is_array($section)) {
            continue;
        }

        $title = aiCopilotCleanText($section['title'] ?? '');
        if ($title !== '') {
            $parts[] = $title;
        }

        foreach (($section['items'] ?? []) as $item) {
            $itemText = aiCopilotCleanText((string) $item);
            if ($itemText !== '') {
                $parts[] = $itemText;
            }
        }
    }

    $value = implode("\n", $parts);
    return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
}

function aiCopilotContextScope(string $role, array $context): string
{
    if (!aiCopilotContextHasPatient($context)) {
        return 'general_prompt';
    }

    return match ($role) {
        'nurse' => 'nursing_limited',
        'billing' => 'billing_limited',
        'front_desk' => 'front_desk_minimum_phi',
        default => 'full_clinical',
    };
}

function aiCopilotBuildResponseMeta(
    string $requestId,
    string $role,
    string $mode,
    array $context,
    array $response,
    array $overrides,
    float $requestStartedAt
): array {
    return [
        'request_id' => $requestId,
        'mode' => $mode,
        'role' => $role,
        'fallback_used' => (bool) ($overrides['fallback_used'] ?? false),
        'fallback_reason' => $overrides['fallback_reason'] ?? null,
        'restricted_by_role' => (bool) ($overrides['restricted_by_role'] ?? false),
        'restriction_type' => $overrides['restriction_type'] ?? null,
        'context_scope' => aiCopilotContextScope($role, $context),
        'latency_ms' => (int) round((microtime(true) - $requestStartedAt) * 1000),
        'response_length' => aiCopilotResponseTextLength($response),
    ];
}

function aiCopilotResolveAction(mixed $value): string
{
    $action = is_string($value) ? trim($value) : 'chat';
    return in_array($action, ['chat', 'send_reminder_email'], true) ? $action : 'chat';
}

function aiCopilotRoleCatalog(): array
{
    return [
        'doctor' => [
            'title' => 'Doctor',
            'note' => 'Clinical support only. No autonomous diagnosis, prescribing, orders, or chart writes.',
            'allowed_modes' => [
                'general_assistant',
                'differential_diagnosis',
                'medication_info',
                'clinical_notes',
                'treatment_plan',
                'follow_up',
                'visit_summary',
                'patient_education',
                'billing',
                'billing_review',
            ],
        ],
        'nurse' => [
            'title' => 'Nurse',
            'note' => 'Education and follow-up support only. Medication changes require clinician review.',
            'allowed_modes' => [
                'general_assistant',
                'medication_info',
                'clinical_notes',
                'follow_up',
                'visit_summary',
                'patient_education',
            ],
        ],
        'billing' => [
            'title' => 'Billing Staff',
            'note' => 'Billing review only. No automatic claim submission or definitive coding.',
            'allowed_modes' => [
                'general_assistant',
                'billing',
                'billing_review',
                'visit_summary',
            ],
        ],
        'front_desk' => [
            'title' => 'Front Desk',
            'note' => 'Minimum necessary PHI. Scheduling, contact, and reminder workflows only.',
            'allowed_modes' => [
                'general_assistant',
                'appointment_info',
                'patient_contact',
                'send_reminder',
                'front_desk_summary',
            ],
        ],
    ];
}

function aiCopilotResolveRole(mixed $value, array $roleCatalog): string
{
    $role = is_string($value) ? trim($value) : 'doctor';
    return isset($roleCatalog[$role]) ? $role : 'doctor';
}

function aiCopilotModeCatalog(): array
{
    return [
        'general_assistant' => [
            'title' => 'General clinical support',
            'style' => 'Answer like a concise beta clinical support assistant. Use the user message as the main instruction and stay grounded in the provided chart context when available.',
        ],
        'differential_diagnosis' => [
            'title' => 'Differential Diagnosis',
            'style' => 'Offer likely possibilities, red flags, key gaps, and what to clarify next. Never claim final diagnosis certainty or recommend unsupervised treatment.',
        ],
        'medication_info' => [
            'title' => 'Medication Info',
            'style' => 'Review medication safety, adherence, interactions, monitoring, and counseling points. Do not change medications or prescribe.',
        ],
        'clinical_notes' => [
            'title' => 'Clinical Notes',
            'style' => 'Draft concise note support using the chart context. Include Subjective, Objective, Assessment, and Plan sections.',
        ],
        'treatment_plan' => [
            'title' => 'Treatment Plan',
            'style' => 'Draft follow-up, monitoring, education, and safety checks. Do not issue orders or a final treatment decision.',
        ],
        'billing' => [
            'title' => 'Billing',
            'style' => 'Provide billing-support suggestions only. Focus on documentation points, possible coding considerations, and missing documentation to review before billing. Never submit claims, never suggest upcoding, and never present definitive CPT or ICD coding without documentation support.',
        ],
        'follow_up' => [
            'title' => 'Follow-Up',
            'style' => 'Draft follow-up timing, monitoring, patient instructions, escalation precautions, and care coordination. Do not place orders or finalize treatment.',
        ],
        'visit_summary' => [
            'title' => 'Visit Summary',
            'style' => 'Draft a concise visit summary using the chart context and any prior treatment-plan discussion in chat history. Keep it patient-safe, beta-labeled, and read-only.',
        ],
        'patient_education' => [
            'title' => 'Patient Education',
            'style' => 'Explain the existing plan in patient-friendly language using the documented chart context only. Do not create a new diagnosis or change treatment.',
        ],
        'physician_summary' => [
            'title' => 'Physician Summary',
            'style' => 'Provide a concise chart summary for rapid review.',
        ],
        'ma_rooming' => [
            'title' => 'MA Rooming Checklist',
            'style' => 'Provide preparation and confirmation items only.',
        ],
        'billing_review' => [
            'title' => 'Billing Claim Review',
            'style' => 'Explain the billing issue in plain language and what should be checked first. Never submit or modify claims automatically.',
        ],
        'appointment_info' => [
            'title' => 'Appointment Info',
            'style' => 'Use minimum necessary scheduling information only: appointment type, date/time, provider, location, and check-in instructions.',
        ],
        'patient_contact' => [
            'title' => 'Patient Contact',
            'style' => 'Use minimum necessary contact information only. Confirm email, phone, and basic appointment workflow details without exposing clinical content.',
        ],
        'send_reminder' => [
            'title' => 'Send Reminder',
            'style' => 'Draft a scheduling reminder using only administrative appointment details and no clinical information.',
        ],
        'front_desk_summary' => [
            'title' => 'Front Desk Summary',
            'style' => 'Provide a short administrative summary with appointment details, contact confirmation points, and check-in instructions only.',
        ],
    ];
}

function aiCopilotResolveMode(string $requestedMode, string $message, array $validModes): string
{
    if ($requestedMode !== '' && isset($validModes[$requestedMode])) {
        return $requestedMode;
    }

    $inferredMode = aiCopilotInferModeFromMessage($message);
    if (isset($validModes[$inferredMode])) {
        return $inferredMode;
    }

    return 'general_assistant';
}

function aiCopilotInferModeFromMessage(string $message): string
{
    $value = strtolower($message);

    if (preg_match('/appointment|scheduled|check[- ]?in|reminder email|reminder message|reminder/', $value)) {
        if (preg_match('/contact|email|phone|confirm/', $value)) {
            return 'patient_contact';
        }
        if (preg_match('/send|draft/', $value)) {
            return 'send_reminder';
        }
        if (preg_match('/summary/', $value)) {
            return 'front_desk_summary';
        }
        return 'appointment_info';
    }
    if (preg_match('/contact information|contact info|phone number|email address/', $value)) {
        return 'patient_contact';
    }
    if (preg_match('/claim|cpt|icd|payer|rejection|denial|resubmi|missing diagnosis link/', $value)) {
        return 'billing_review';
    }
    if (preg_match('/billing|coding|coder|documentation needed before billing|payment due|health insurance|insurance on file|insurance/', $value)) {
        return 'billing';
    }
    if (preg_match('/visit summary|summary of visit/', $value)) {
        return 'visit_summary';
    }
    if (preg_match('/patient-friendly|patient friendly|education|counseling points/', $value)) {
        return 'patient_education';
    }
    if (preg_match('/medication|drug|interaction|counsel|double-check/', $value)) {
        return 'medication_info';
    }
    if (preg_match('/treatment plan|let me see .* treatment plan|give me .* treatment plan/', $value)) {
        return 'treatment_plan';
    }
    if (preg_match('/follow-up|follow up|monitoring items|escalation precautions|care coordination/', $value)) {
        return 'follow_up';
    }
    if (preg_match('/soap|note|documentation|chart for a doctor|30 seconds|30-second|summary/', $value)) {
        return 'clinical_notes';
    }
    if (preg_match('/differential|diagnosis|causing|red flag|miss|questions should the clinician ask/', $value)) {
        return 'differential_diagnosis';
    }
    if (preg_match('/rooming|checklist|confirm before the provider/', $value)) {
        return 'ma_rooming';
    }

    return 'general_assistant';
}

function aiCopilotNormalizePrompt(mixed $value): string
{
    return is_string($value) ? aiCopilotCleanText($value) : '';
}

function aiCopilotNormalizeChatHistory(mixed $value): array
{
    if (!is_array($value)) {
        return [];
    }

    $normalized = [];
    foreach ($value as $item) {
        if (!is_array($item)) {
            continue;
        }

        $role = $item['role'] ?? '';
        $content = aiCopilotNormalizePrompt($item['content'] ?? '');
        if ($content === '' || !in_array($role, ['user', 'assistant'], true)) {
            continue;
        }

        $normalized[] = [
            'role' => $role,
            'content' => $content,
        ];
    }

    return array_slice($normalized, -8);
}

function aiCopilotBuildGeneralContext(string $mode, string $message): array
{
    return [
        'role' => 'doctor',
        'mode' => $mode,
        'scenario' => 'general_prompt',
        'patient_selected' => false,
        'patient' => [],
        'prompt' => $message,
        'next_appointment' => [],
        'appointments' => [],
        'encounters' => [],
        'notes' => [],
        'problems' => [],
        'allergies' => [],
        'medications' => [],
        'latest_vitals' => [],
        'primary_insurance' => [],
        'billing' => [
            'encounter' => [],
            'rows' => [],
            'claim' => [],
        ],
    ];
}

function aiCopilotBuildContext(array $patient, string $mode): array
{
    $pid = (int) $patient['pid'];
    $patientLabel = trim(($patient['fname'] ?? '') . ' ' . ($patient['lname'] ?? ''));

    $nextAppointment = sqlQuery(
        "SELECT pc_title, pc_hometext, pc_eventDate, pc_startTime, pc_endTime, pc_apptstatus, pc_room, pc_location
         FROM openemr_postcalendar_events
         WHERE pc_pid = ?
           AND pc_eventDate >= CURDATE()
         ORDER BY pc_eventDate ASC, pc_startTime ASC
         LIMIT 1",
        [(string) $pid]
    ) ?: [];

    $appointments = aiCopilotFetchAll(
        "SELECT pc_title, pc_hometext, pc_eventDate, pc_startTime, pc_endTime, pc_apptstatus, pc_room, pc_location
         FROM openemr_postcalendar_events
         WHERE pc_pid = ?
         ORDER BY pc_eventDate DESC, pc_startTime DESC
         LIMIT 6",
        [(string) $pid]
    );

    $encounters = aiCopilotFetchAll(
        "SELECT date, reason, encounter, billing_note
         FROM form_encounter
         WHERE pid = ?
         ORDER BY date DESC
         LIMIT 6",
        [$pid]
    );

    $notes = aiCopilotFetchAll(
        "SELECT date, title, body
         FROM pnotes
         WHERE pid = ?
           AND deleted = 0
         ORDER BY date DESC
         LIMIT 8",
        [$pid]
    );

    $problems = aiCopilotFetchAll(
        "SELECT title, diagnosis, comments, begdate
         FROM lists
         WHERE pid = ?
           AND type = 'medical_problem'
           AND (enddate IS NULL OR enddate = '0000-00-00 00:00:00' OR enddate > NOW())
         ORDER BY begdate DESC, date DESC
         LIMIT 12",
        [$pid]
    );

    $allergies = aiCopilotFetchAll(
        "SELECT title, diagnosis, comments, begdate
         FROM lists
         WHERE pid = ?
           AND type = 'allergy'
           AND (enddate IS NULL OR enddate = '0000-00-00 00:00:00' OR enddate > NOW())
         ORDER BY begdate DESC, date DESC
         LIMIT 12",
        [$pid]
    );

    $medications = aiCopilotFetchAll(
        "SELECT drug, dosage, quantity, note, start_date
         FROM prescriptions
         WHERE patient_id = ?
           AND active = 1
         ORDER BY COALESCE(date_modified, date_added) DESC, id DESC
         LIMIT 12",
        [$pid]
    );

    $latestVitals = sqlQuery(
        "SELECT date, bps, bpd, weight, height, temperature, pulse, respiration, note, BMI, oxygen_saturation
         FROM form_vitals
         WHERE pid = ?
           AND activity = 1
         ORDER BY date DESC, id DESC
         LIMIT 1",
        [$pid]
    ) ?: [];

    $primaryInsurance = sqlQuery(
        "SELECT i.type, i.plan_name, i.policy_number, i.copay, ic.name AS carrier
         FROM insurance_data AS i
         LEFT JOIN insurance_companies AS ic ON ic.id = i.provider
         WHERE i.pid = ?
           AND i.type = 'primary'
         ORDER BY (i.date IS NULL) ASC, i.date DESC
         LIMIT 1",
        [$pid]
    ) ?: [];

    $billingEncounter = $encounters[0] ?? [];
    $billingRows = [];
    $claimRow = [];
    if (!empty($billingEncounter['encounter'])) {
        $billingRows = aiCopilotFetchAll(
            "SELECT code_type, code, code_text, fee, justify, billed, activity
             FROM billing
             WHERE pid = ?
               AND encounter = ?
             ORDER BY id ASC",
            [$pid, (int) $billingEncounter['encounter']]
        );

        $claimRow = sqlQuery(
            "SELECT version, payer_id, status, bill_time, process_time, process_file, submitted_claim
             FROM claims
             WHERE patient_id = ?
               AND encounter_id = ?
             ORDER BY version DESC
             LIMIT 1",
            [$pid, (int) $billingEncounter['encounter']]
        ) ?: [];
    }

    if (empty($billingRows)) {
        $billingRows = aiCopilotFetchAll(
            "SELECT code_type, code, code_text, fee, justify, billed, activity
             FROM billing
             WHERE pid = ?
             ORDER BY date DESC, id DESC
             LIMIT 10",
            [$pid]
        );
    }

    if (empty($claimRow)) {
        $claimRow = sqlQuery(
            "SELECT version, payer_id, status, bill_time, process_time, process_file, submitted_claim
             FROM claims
             WHERE patient_id = ?
             ORDER BY process_time DESC, bill_time DESC, version DESC
             LIMIT 1",
            [$pid]
        ) ?: [];
    }

    return [
        'role' => 'doctor',
        'mode' => $mode,
        'scenario' => $patient['genericval1'] ?? '',
        'patient_selected' => true,
        'patient' => [
            'pid' => $pid,
            'pubpid' => $patient['pubpid'] ?? '',
            'name' => $patientLabel,
            'fname' => $patient['fname'] ?? '',
            'lname' => $patient['lname'] ?? '',
            'dob' => $patient['DOB'] ?? '',
            'sex' => $patient['sex'] ?? '',
            'email' => aiCopilotCleanText($patient['email'] ?? ''),
            'phone_home' => aiCopilotCleanText($patient['phone_home'] ?? ''),
            'phone_cell' => aiCopilotCleanText($patient['phone_cell'] ?? ''),
        ],
        'prompt' => $patient['genericval2'] ?? '',
        'next_appointment' => aiCopilotNormalizeAppointment($nextAppointment),
        'appointments' => array_map('aiCopilotNormalizeAppointment', $appointments),
        'encounters' => array_map('aiCopilotNormalizeEncounter', $encounters),
        'notes' => array_map('aiCopilotNormalizeNote', $notes),
        'problems' => array_map('aiCopilotNormalizeProblem', $problems),
        'allergies' => array_map('aiCopilotNormalizeProblem', $allergies),
        'medications' => array_map('aiCopilotNormalizeMedication', $medications),
        'latest_vitals' => aiCopilotNormalizeVitals($latestVitals),
        'primary_insurance' => aiCopilotNormalizeInsurance($primaryInsurance),
        'billing' => [
            'encounter' => aiCopilotNormalizeEncounter($billingEncounter),
            'rows' => array_map('aiCopilotNormalizeBillingRow', $billingRows),
            'claim' => aiCopilotNormalizeClaim($claimRow),
        ],
    ];
}

function aiCopilotGenerateDraft(string $role, string $mode, string $message, array $chatHistory, array $context, array $modeConfig): array
{
    $apiKey = aiCopilotReadEnv('OPENAI_API_KEY');
    $fallbackReason = 'missing_openai_key';
    if ($apiKey !== '') {
        $openAiResponse = aiCopilotGenerateOpenAiDraft($apiKey, $role, $mode, $message, $chatHistory, $context, $modeConfig);
        if ($openAiResponse !== []) {
            $openAiResponse['engine'] = 'openai';
            $openAiResponse['fallback_reason'] = null;
            return $openAiResponse;
        }
        $fallbackReason = 'openai_error';
    }

    $fallbackResponse = aiCopilotGenerateFallbackDraft($role, $mode, $message, $context);
    $fallbackResponse['engine'] = 'fallback';
    $fallbackResponse['fallback_reason'] = $fallbackReason;
    return $fallbackResponse;
}

function aiCopilotGenerateOpenAiDraft(string $apiKey, string $role, string $mode, string $message, array $chatHistory, array $context, array $modeConfig): array
{
    if (!function_exists('curl_init')) {
        return [];
    }

    $model = aiCopilotReadEnv('OPENAI_MODEL');
    if ($model === '') {
        $model = 'gpt-4o-mini';
    }

    $baseUrl = aiCopilotReadEnv('OPENAI_BASE_URL');
    if ($baseUrl === '') {
        $baseUrl = 'https://api.openai.com/v1';
    }

    $messages = [
        [
            'role' => 'system',
            'content' => 'You are Medical Co-Pilot, a beta OpenEMR demo assistant for read-only clinical decision support. Use the user message as the main instruction. Stay grounded in the supplied chart context. Never claim final diagnosis certainty. Never write to the chart, submit orders, prescribe, finalize diagnosis, submit claims, or suggest upcoding. If no patient is selected, say the answer is general only.',
        ],
        [
            'role' => 'system',
            'content' => aiCopilotRoleSystemInstruction($role),
        ],
        [
            'role' => 'system',
            'content' => 'Mode guidance: ' . ($modeConfig['title'] ?? $mode) . '. ' . ($modeConfig['style'] ?? ''),
        ],
        [
            'role' => 'system',
            'content' => aiCopilotBuildStructuredFormatInstruction($mode),
        ],
        [
            'role' => 'system',
            'content' => 'Chart/demo context JSON: ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ],
    ];

    foreach ($chatHistory as $historyMessage) {
        $messages[] = $historyMessage;
    }

    $messages[] = [
        'role' => 'user',
        'content' => $message,
    ];

    $requestBody = [
        'model' => $model,
        'messages' => $messages,
        'temperature' => 0.2,
        'max_tokens' => 720,
    ];

    $curl = curl_init(rtrim($baseUrl, '/') . '/chat/completions');
    if ($curl === false) {
        return [];
    }

    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($requestBody, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ]);

    $responseBody = curl_exec($curl);
    $httpCode = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);

    if (!is_string($responseBody) || $responseBody === '' || $httpCode >= 400) {
        return [];
    }

    $decoded = json_decode($responseBody, true);
    $assistantText = $decoded['choices'][0]['message']['content'] ?? '';
    if (!is_string($assistantText) || trim($assistantText) === '') {
        return [];
    }

    return aiCopilotDecodeStructuredAssistantResponse($assistantText, $mode);
}

function aiCopilotBuildStructuredFormatInstruction(string $mode): string
{
    $instruction = match ($mode) {
        'differential_diagnosis' => 'Return JSON only with keys: answer, sections, tags. Use sections in this order: Likely considerations (tone neutral), Red flags (tone red), Key gaps (tone yellow), Next steps / clarification (tone neutral). Each section must contain an items array of short bullet strings.',
        'medication_info' => 'Return JSON only with keys: answer, sections, tags. Use sections in this order: Current medication picture, Safety checks, Monitoring considerations, Patient counseling points. Use tone neutral unless a clear caution deserves tone yellow.',
        'clinical_notes' => 'Return JSON only with keys: answer, sections, tags. Use sections in this order: Draft note, Subjective, Objective, Assessment, Plan. Keep content concise and chart-style.',
        'treatment_plan' => 'Return JSON only with keys: answer, sections, tags. Use sections in this order: Immediate priorities, Suggested workup or monitoring, Patient education, Follow-up, Safety precautions.',
        'billing' => 'Return JSON only with keys: answer, sections, tags. Use sections in this order: Billing documentation summary, Possible coding considerations, Missing documentation, Risk / compliance reminders.',
        'follow_up' => 'Return JSON only with keys: answer, sections, tags. Use sections in this order: Follow-up timeframe, What to monitor, Patient instructions, Escalation precautions, Care coordination.',
        'visit_summary' => 'Return JSON only with keys: answer, sections, tags. Use sections in this order: Visit summary, Key concerns addressed, Plan discussed, Follow-up instructions, Patient-friendly explanation.',
        'patient_education' => 'Return JSON only with keys: answer, sections, tags. Use sections in this order: Patient-friendly explanation, Safety reminders.',
        'billing_review' => 'Return JSON only with keys: answer, sections, tags. Use sections in this order: Plain-language issue, What to check first, Guardrails.',
        'appointment_info' => 'Return JSON only with keys: answer, sections, tags. Use sections in this order: Appointment details, Check-in instructions.',
        'patient_contact' => 'Return JSON only with keys: answer, sections, tags. Use sections in this order: Contact details, Contact workflow reminders.',
        'send_reminder' => 'Return JSON only with keys: answer, sections, tags. Use sections in this order: Reminder draft, Delivery details.',
        'front_desk_summary' => 'Return JSON only with keys: answer, sections, tags. Use sections in this order: Administrative summary, Next appointment, Contact details.',
        default => 'Return JSON only with keys: answer, sections, tags. Use a few short sections that best fit the user request.',
    };

    return $instruction . ' Tags should be a short array of compact clinical labels. Include a brief statement in answer that the draft must be verified by a licensed clinician.';
}

function aiCopilotDecodeStructuredAssistantResponse(string $assistantText, string $mode): array
{
    $candidate = trim($assistantText);
    if (str_starts_with($candidate, '```')) {
        $candidate = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $candidate) ?? $candidate;
        $candidate = trim($candidate);
    }

    $decoded = json_decode($candidate, true);
    if (!is_array($decoded)) {
        return [];
    }

    return aiCopilotNormalizeStructuredResponse($decoded, $mode);
}

function aiCopilotNormalizeStructuredResponse(array $response, string $mode): array
{
    $answer = aiCopilotNormalizePrompt($response['answer'] ?? '');
    $sections = [];
    $rawSections = $response['sections'] ?? [];
    if (is_array($rawSections)) {
        foreach ($rawSections as $section) {
            if (!is_array($section)) {
                continue;
            }

            $title = aiCopilotNormalizePrompt($section['title'] ?? '');
            $tone = strtolower(aiCopilotNormalizePrompt($section['tone'] ?? 'neutral'));
            $items = [];
            foreach (($section['items'] ?? []) as $item) {
                $itemText = aiCopilotNormalizePrompt($item);
                if ($itemText !== '') {
                    $items[] = $itemText;
                }
            }

            if ($title === '' || $items === []) {
                continue;
            }

            if (!in_array($tone, ['neutral', 'red', 'yellow'], true)) {
                $tone = 'neutral';
            }

            $sections[] = [
                'title' => $title,
                'tone' => $tone,
                'items' => array_slice($items, 0, 6),
            ];
        }
    }

    $tags = [];
    foreach (($response['tags'] ?? []) as $tag) {
        $tagText = aiCopilotNormalizePrompt($tag);
        if ($tagText !== '') {
            $tags[] = $tagText;
        }
    }
    $tags = aiCopilotFinalizeTags($tags);

    if ($answer === '' && $sections === []) {
        return [];
    }

    if ($answer === '') {
        $answer = 'Beta clinical support draft for review by a licensed clinician.';
    }

    return [
        'answer' => $answer,
        'sections' => $sections,
        'tags' => $tags,
    ];
}

function aiCopilotGenerateFallbackDraft(string $role, string $mode, string $message, array $context): array
{
    if ($role === 'front_desk') {
        return aiCopilotBuildFrontDeskFallbackResponse($mode, $message, $context);
    }

    if ($role === 'billing') {
        return aiCopilotBuildBillingStaffFallbackResponse($mode, $message, $context);
    }

    $normalized = strtolower($message);

    if (preg_match('/patient-friendly/', $normalized)) {
        return aiCopilotBuildPatientFriendlyResponse($context);
    }

    if (preg_match('/visit summary|summary of visit/', $normalized)) {
        return aiCopilotBuildVisitSummaryResponse($context);
    }

    if (preg_match('/30 seconds|30-second|doctor who has 30 seconds|summarize this chart/', $normalized)) {
        return aiCopilotBuildChartSummaryResponse($context);
    }

    if ($mode === 'general_assistant') {
        return aiCopilotBuildGeneralFallbackResponse($role, $message, $context);
    }

    return match ($mode) {
        'differential_diagnosis' => aiCopilotBuildDifferentialResponse($context),
        'medication_info' => aiCopilotBuildMedicationResponse($context),
        'clinical_notes' => aiCopilotBuildClinicalNotesResponse($context),
        'treatment_plan' => aiCopilotBuildTreatmentPlanResponse($context),
        'billing' => aiCopilotBuildBillingSupportResponse($context),
        'follow_up' => aiCopilotBuildFollowUpResponse($context),
        'visit_summary' => aiCopilotBuildVisitSummaryResponse($context),
        'patient_education' => aiCopilotBuildPatientFriendlyResponse($context),
        'physician_summary' => aiCopilotBuildChartSummaryResponse($context),
        'ma_rooming' => aiCopilotBuildRoomingResponse($context),
        'billing_review' => aiCopilotBuildBillingReviewResponse($context),
        default => aiCopilotBuildGeneralFallbackResponse($role, $message, $context),
    };
}

function aiCopilotBuildGeneralFallbackResponse(string $role, string $message, array $context): array
{
    $normalized = strtolower($message);
    $inferredMode = aiCopilotInferModeFromMessage($message);
    if ($inferredMode !== 'general_assistant') {
        return aiCopilotGenerateFallbackDraft($role, $inferredMode, $message, $context);
    }

    if (preg_match('/patient-friendly/', $normalized)) {
        return aiCopilotBuildPatientFriendlyResponse($context);
    }

    return aiCopilotBuildChartSummaryResponse($context);
}

function aiCopilotBuildChartSummaryResponse(array $context): array
{
    if (!aiCopilotContextHasPatient($context)) {
        return aiCopilotBuildNoPatientResponse(
            'Here is a general chart-summary framework. Select a demo patient for patient-specific support.',
            [
                aiCopilotBuildSection('Situation', ['Clarify the chief complaint, symptom timing, and active safety concerns.']),
                aiCopilotBuildSection('Key chart data', ['Review medications, allergies, recent vitals, recent labs, and the latest encounter note.']),
                aiCopilotBuildSection('What to clarify next', ['Ask what changed recently and whether any urgent red flags are active right now.']),
            ],
            ['general summary']
        );
    }

    $facts = aiCopilotExtractClinicalFacts($context);

    return aiCopilotBuildResponse(
        'Here is a concise beta chart summary for ' . $context['patient']['name'] . '. This draft still needs licensed-clinician review.',
        [
            aiCopilotBuildSection('Situation', [
                aiCopilotFallbackValue($facts['chief_complaint'], 'Recent clinical concern needs review.') . ' ' . aiCopilotFallbackValue($facts['history_of_present_illness'], ''),
            ]),
            aiCopilotBuildSection('Key chart data', [
                'Problems: ' . aiCopilotFallbackValue(aiCopilotJoinList($facts['conditions']), 'No structured problems were found.'),
                'Medications: ' . aiCopilotFallbackValue($facts['medication_line'], 'No active medications were found.'),
                'Allergies: ' . aiCopilotFallbackValue($facts['allergy_line'], 'No allergy list was found in structured data.'),
                'Vitals/labs: ' . aiCopilotFallbackValue(aiCopilotJoinParts([$facts['vitals_line'], $facts['recent_labs']]), 'Recent vitals or labs were not found.'),
            ]),
            aiCopilotBuildSection('What to clarify next', [
                aiCopilotFallbackValue($facts['follow_up_considerations'], 'Confirm what is active right now, what has changed, and what needs urgent escalation.'),
            ]),
        ],
        aiCopilotScenarioTags($context, ['chart summary'])
    );
}

function aiCopilotBuildDifferentialResponse(array $context): array
{
    if (!aiCopilotContextHasPatient($context)) {
        return aiCopilotBuildNoPatientResponse(
            'No demo patient is selected, so this is a general differential framework only.',
            [
                aiCopilotBuildSection('Likely considerations', ['Start with the symptom pattern, timing, comorbidities, medications, and recent vitals/labs.']),
                aiCopilotBuildSection('Red flags', ['Escalate for unstable vitals, severe pain, respiratory distress, syncope, focal neurologic changes, or rapidly worsening infection signs.'], 'red'),
                aiCopilotBuildSection('Key gaps', ['Clarify what symptoms are active now, what changed recently, and what objective data are still missing.'], 'yellow'),
                aiCopilotBuildSection('Next steps / clarification', ['Select a demo patient to generate a chart-specific beta differential draft.']),
            ],
            ['general differential']
        );
    }

    $facts = aiCopilotExtractClinicalFacts($context);
    $key = $facts['patient_key'];

    return match ($key) {
        'DEMO-BILL-1003' => aiCopilotBuildResponse(
            $context['patient']['name'] . ' has exertional chest pressure, dyspnea, and several cardiovascular risk factors, so urgent cardiopulmonary causes need to stay high on the list. This beta draft must be verified by a licensed clinician.',
            [
                aiCopilotBuildSection('Likely considerations', [
                    'Acute coronary syndrome or unstable angina/NSTEMI because the pain is pressure-like, worse with exertion, and occurs in the setting of diabetes, hypertension, and hyperlipidemia.',
                    'Pulmonary embolism if shortness of breath becomes disproportionate, pleuritic symptoms emerge, or additional clotting risk factors are uncovered.',
                    'Aortic dissection is less supported by the current note but still important if pain becomes tearing, radiates to the back, or neurologic deficits appear.',
                    'GERD or musculoskeletal chest pain remain lower-acuity alternatives if the urgent cardiac workup is reassuring.',
                ]),
                aiCopilotBuildSection('Red flags', [
                    'Ongoing or worsening chest pressure, increasing shortness of breath, diaphoresis, syncope, or new neurologic symptoms.',
                    'Current charted vitals show BP 162/96 and pulse 104, which add concern in this symptom context.',
                    'No troponin result is documented yet, so an ACS rule-out is incomplete.',
                ], 'red'),
                aiCopilotBuildSection('Key gaps', [
                    'Whether the chest pressure is active right now, whether it radiates, and whether it is pleuritic or reproducible.',
                    'ECG findings, troponin status, and whether there are leg symptoms, immobilization, or other PE risk factors.',
                    'Medication adherence just before symptom onset, especially lisinopril, atorvastatin, aspirin, and diabetes therapy.',
                ], 'yellow'),
                aiCopilotBuildSection('Next steps / clarification', [
                    'Clarify whether the patient needs immediate escalation or ED evaluation now.',
                    'Consider ECG, troponin, and close vitals monitoring per clinician judgment.',
                    'After urgent issues are addressed, revisit chronic BP, diabetes, and lipid control.',
                ]),
            ],
            aiCopilotScenarioTags($context, ['chest pressure', 'exertional dyspnea', 'ACS risk', 'cardiometabolic risk'])
        ),
        'DEMO-PCP-1001' => aiCopilotBuildResponse(
            $context['patient']['name'] . ' has a diabetic foot wound with drainage, redness, pain, and neuropathy, so infection-related and structural foot complications need to stay high on the list. This beta draft must be verified by a licensed clinician.',
            [
                aiCopilotBuildSection('Likely considerations', [
                    'Diabetic foot ulcer with surrounding cellulitis because the note describes a plantar wound with erythema and drainage.',
                    'Localized abscess if there is fluctuance, increasing pain, or a deeper pocket not yet documented.',
                    'Osteomyelitis risk because the wound has drainage in a patient with poor glycemic control and neuropathy.',
                    'Neuropathic injury with delayed recognition and possible vascular insufficiency contributing to poor healing.',
                ]),
                aiCopilotBuildSection('Red flags', [
                    'Rapidly spreading redness, severe swelling, crepitus, foul odor, systemic symptoms, or rising temperature.',
                    'Hyperglycemia plus infection concern, especially with WBC 11.8 and A1C 9.6 in the chart context.',
                    'Reduced pulses or concern for deep tissue involvement could change urgency quickly.',
                ], 'red'),
                aiCopilotBuildSection('Key gaps', [
                    'Exact wound depth, size, probe-to-bone status, and whether there is fluctuance or necrosis.',
                    'Current glucose trend, foot pulses, offloading status, and whether the patient has fever, chills, or streaking redness.',
                    'Recent footwear trauma, home wound care, and any prior diabetic foot infections or vascular workup.',
                ], 'yellow'),
                aiCopilotBuildSection('Next steps / clarification', [
                    'Clarify infection severity and document a focused foot exam.',
                    'Consider wound culture or imaging if deeper infection or osteomyelitis is a concern.',
                    'Review need for offloading, wound care follow-up, and urgent escalation if infection is spreading.',
                ]),
            ],
            aiCopilotScenarioTags($context, ['diabetic foot wound', 'cellulitis risk', 'osteomyelitis risk', 'neuropathy'])
        ),
        default => aiCopilotBuildResponse(
            $context['patient']['name'] . ' has respiratory symptoms in the setting of asthma and allergy overlap, so airway and trigger-related causes lead the differential from the current chart context. This beta draft must be verified by a licensed clinician.',
            [
                aiCopilotBuildSection('Likely considerations', [
                    'Asthma symptom flare because the patient reports cough, wheezing, nocturnal symptoms, and increased rescue inhaler use.',
                    'Viral upper respiratory infection if there is a concurrent infectious trigger not yet fully documented.',
                    'Allergic rhinitis/postnasal drip and GERD overlap because both are already part of the chart context and can worsen cough or chest tightness.',
                    'Pneumonia is less supported by the current note but would move up if fever, focal findings, or hypoxia appear.',
                ]),
                aiCopilotBuildSection('Red flags', [
                    'Increasing work of breathing, inability to speak full sentences, hypoxia, cyanosis, or rapidly worsening chest tightness.',
                    'Rising rescue inhaler use with poor relief or new chest pain out of proportion to the current chart.',
                    'Severe distress is not documented now, but respiratory symptoms can worsen quickly if control is poor.',
                ], 'red'),
                aiCopilotBuildSection('Key gaps', [
                    'Exact rescue inhaler frequency, controller adherence, inhaler technique, and recent trigger exposure.',
                    'Presence of fever, sputum, sick contacts, chest imaging, or objective peak-flow data.',
                    'Whether anxiety is secondary to dyspnea or an overlapping driver of symptoms.',
                ], 'yellow'),
                aiCopilotBuildSection('Next steps / clarification', [
                    'Clarify symptom severity, nighttime frequency, and trigger pattern.',
                    'Review inhaler technique, controller adherence, and whether a peak flow or additional respiratory assessment is needed.',
                    'Recheck for urgent breathing red flags if symptoms worsen or oxygenation declines.',
                ]),
            ],
            aiCopilotScenarioTags($context, ['asthma flare', 'wheezing', 'allergic triggers', 'respiratory symptoms'])
        ),
    };
}

function aiCopilotBuildMedicationResponse(array $context): array
{
    if (!aiCopilotContextHasPatient($context)) {
        return aiCopilotBuildNoPatientResponse(
            'No demo patient is selected, so this is a general medication-review framework only.',
            [
                aiCopilotBuildSection('Current medication picture', ['Review the active list, allergies, what the patient is actually taking, and any recent medication changes.']),
                aiCopilotBuildSection('Safety checks', ['Look for missed doses, interaction risks, renal dosing concerns, and duplicate therapy.']),
                aiCopilotBuildSection('Monitoring considerations', ['Tie medication review to vitals, recent labs, and symptom severity.']),
                aiCopilotBuildSection('Patient counseling points', ['Confirm understanding, adherence barriers, and what warning symptoms should prompt faster follow-up.']),
            ],
            ['general medication review']
        );
    }

    $facts = aiCopilotExtractClinicalFacts($context);
    $allergies = aiCopilotFallbackValue($facts['allergy_line'], 'No structured allergy list found.');

    return match ($facts['patient_key']) {
        'DEMO-BILL-1003' => aiCopilotBuildResponse(
            'The current medication picture should be reviewed in the context of chest-pressure red flags and cardiometabolic risk. This beta draft must still be verified by a licensed clinician.',
            [
                aiCopilotBuildSection('Current medication picture', [
                    'Active medications in the chart: ' . aiCopilotFallbackValue($facts['medication_line'], 'No active medications found.'),
                    'The list fits the documented hypertension, diabetes, and hyperlipidemia history.',
                ]),
                aiCopilotBuildSection('Safety checks', [
                    'Confirm adherence to metformin, lisinopril, atorvastatin, and aspirin, especially around the time symptoms began.',
                    'Aspirin use should remain clinician-directed in the chest-pain context rather than self-adjusted from this beta draft.',
                    'Allergy status is documented as: ' . $allergies,
                ]),
                aiCopilotBuildSection('Monitoring considerations', [
                    'Recheck blood pressure, pulse, glucose, renal function, and potassium when reviewing the ACE inhibitor and diabetes therapy.',
                    'LDL and A1C remain above goal in the seeded chart context and support chronic risk review after urgent issues are addressed.',
                ]),
                aiCopilotBuildSection('Patient counseling points', [
                    'Tell the clinician if any doses were missed, if chest symptoms are active now, or if there was any self-treatment before the visit.',
                    'Bring the actual medication list or bottles if available because adherence details matter in this scenario.',
                ]),
            ],
            aiCopilotScenarioTags($context, ['metformin', 'lisinopril', 'atorvastatin', 'aspirin', 'adherence'])
        ),
        'DEMO-PCP-1001' => aiCopilotBuildResponse(
            'The medication review should focus on glycemic control, wound-healing risk, neuropathy treatment, and antibiotic-safety context. This beta draft must still be verified by a licensed clinician.',
            [
                aiCopilotBuildSection('Current medication picture', [
                    'Active medications in the chart: ' . aiCopilotFallbackValue($facts['medication_line'], 'No active medications found.'),
                    'This list supports diabetes management plus neuropathy symptom control.',
                ]),
                aiCopilotBuildSection('Safety checks', [
                    'Glipizide raises hypoglycemia risk if meal timing is inconsistent or intake drops because of illness.',
                    'Metformin and gabapentin both deserve a renal-function check in the infection and wound-healing context.',
                    'Allergy status is documented as: ' . $allergies . ' That matters if antibiotics are being considered.',
                ]),
                aiCopilotBuildSection('Monitoring considerations', [
                    'The seeded chart shows A1C 9.6, glucose 248, and WBC 11.8, so poor control and infection burden both need monitoring.',
                    'Review home glucose trends, wound progression, and whether pain or numbness is changing.',
                ]),
                aiCopilotBuildSection('Patient counseling points', [
                    'Ask what the patient is actually taking, whether any doses were missed, and whether there were recent lows or dizziness.',
                    'Reinforce foot protection, wound-care follow-up, and the need to report spreading redness, fever, or worsening drainage quickly.',
                ]),
            ],
            aiCopilotScenarioTags($context, ['metformin', 'glipizide', 'gabapentin', 'wound care', 'hypoglycemia risk'])
        ),
        default => aiCopilotBuildResponse(
            'The medication review should focus on asthma control, rescue inhaler use, and steroid/controller safety. This beta draft must still be verified by a licensed clinician.',
            [
                aiCopilotBuildSection('Current medication picture', [
                    'Active medications in the chart: ' . aiCopilotFallbackValue($facts['medication_line'], 'No active medications found.'),
                    'The list suggests both rescue and controller therapy plus allergy overlap management.',
                ]),
                aiCopilotBuildSection('Safety checks', [
                    'Frequent albuterol use can signal poor control and can contribute to tachycardia or shakiness.',
                    'Review controller adherence and whether the patient is using any duplicate inhalers or old steroid prescriptions.',
                    'Prednisone side effects such as insomnia, mood changes, and glucose effects should be reviewed if the recent course is active.',
                    'Allergy status is documented as: ' . $allergies,
                ]),
                aiCopilotBuildSection('Monitoring considerations', [
                    'Track rescue inhaler frequency, nocturnal symptoms, pulse, oxygen saturation, and response to controller therapy.',
                    'If symptoms are not improving, consider whether additional respiratory evaluation is needed rather than relying only on repeated rescue medication.',
                ]),
                aiCopilotBuildSection('Patient counseling points', [
                    'Review inhaler technique and remind the patient to rinse after inhaled steroid use.',
                    'Clarify trigger exposures, when to seek urgent breathing evaluation, and whether the patient is taking the controller every day as directed.',
                ]),
            ],
            aiCopilotScenarioTags($context, ['albuterol', 'controller inhaler', 'prednisone', 'inhaler technique'])
        ),
    };
}

function aiCopilotBuildClinicalNotesResponse(array $context): array
{
    if (!aiCopilotContextHasPatient($context)) {
        return aiCopilotBuildNoPatientResponse(
            'No demo patient is selected, so this is a general clinical-note structure only.',
            [
                aiCopilotBuildSection('Draft note', ['Select a demo patient to generate a patient-specific beta note draft.']),
                aiCopilotBuildSection('Subjective', ['Capture the chief complaint, symptom timing, key symptoms, and relevant history.']),
                aiCopilotBuildSection('Objective', ['Include recent vitals, exam findings, medications, allergies, and helpful labs.']),
                aiCopilotBuildSection('Assessment', ['Summarize the working clinical concerns without claiming final certainty.']),
                aiCopilotBuildSection('Plan', ['Outline monitoring, follow-up, patient education, and safety checks.']),
            ],
            ['general note draft']
        );
    }

    $facts = aiCopilotExtractClinicalFacts($context);

    return match ($facts['patient_key']) {
        'DEMO-BILL-1003' => aiCopilotBuildResponse(
            'Here is a beta draft note summary for ' . $context['patient']['name'] . '. This draft must be verified by a licensed clinician.',
            [
                aiCopilotBuildSection('Draft note', [
                    'Adult male with hypertension, diabetes, and hyperlipidemia presenting with 2 days of intermittent exertional chest pressure, mild shortness of breath, and nausea; urgent cardiac causes remain important to exclude.',
                ]),
                aiCopilotBuildSection('Subjective', [
                    aiCopilotFallbackValue($facts['history_of_present_illness'], 'Chest-pressure history needs further clarification.'),
                    'Symptoms documented: ' . aiCopilotFallbackValue($facts['symptoms'], 'Chest pressure symptoms noted.'),
                ]),
                aiCopilotBuildSection('Objective', [
                    'Vitals: ' . aiCopilotFallbackValue($facts['vitals_line'], 'No recent vitals found.'),
                    'Recent labs: ' . aiCopilotFallbackValue($facts['recent_labs'], 'No recent labs found.'),
                    'Medication/allergy context: ' . aiCopilotFallbackValue(aiCopilotJoinParts([$facts['medication_line'], $facts['allergy_line']]), 'Medication or allergy details were limited.'),
                ]),
                aiCopilotBuildSection('Assessment', [
                    'Exertional chest pressure with cardiometabolic risk factors raises concern for ACS while lower-acuity GI or musculoskeletal causes remain possible.',
                    'No troponin is documented yet, so the chart does not show a complete rule-out.',
                ]),
                aiCopilotBuildSection('Plan', [
                    'Clarify whether symptoms are active now and assess need for urgent escalation.',
                    'Consider ECG, troponin review, vitals monitoring, and chronic disease follow-up after the acute risk question is addressed.',
                ]),
            ],
            aiCopilotScenarioTags($context, ['beta note', 'chest pain', 'ACS risk', 'cardiac workup'])
        ),
        'DEMO-PCP-1001' => aiCopilotBuildResponse(
            'Here is a beta draft note summary for ' . $context['patient']['name'] . '. This draft must be verified by a licensed clinician.',
            [
                aiCopilotBuildSection('Draft note', [
                    'Adult male with diabetes, obesity, and neuropathy presenting with a right plantar foot wound, new redness, drainage, and increasing pain concerning for diabetic foot infection risk.',
                ]),
                aiCopilotBuildSection('Subjective', [
                    aiCopilotFallbackValue($facts['history_of_present_illness'], 'Foot wound history needs further clarification.'),
                    'Symptoms documented: ' . aiCopilotFallbackValue($facts['symptoms'], 'Foot wound symptoms noted.'),
                ]),
                aiCopilotBuildSection('Objective', [
                    'Vitals: ' . aiCopilotFallbackValue($facts['vitals_line'], 'No recent vitals found.'),
                    'Recent labs: ' . aiCopilotFallbackValue($facts['recent_labs'], 'No recent labs found.'),
                    'Exam context: ' . aiCopilotFallbackValue($facts['recent_exam'], 'No recent exam summary found.'),
                ]),
                aiCopilotBuildSection('Assessment', [
                    'Diabetic foot ulcer with concern for cellulitis and deeper infection risk in the setting of poor glycemic control and neuropathy.',
                    'Osteomyelitis and vascular insufficiency should be kept in mind if the exam suggests depth, poor perfusion, or poor healing.',
                ]),
                aiCopilotBuildSection('Plan', [
                    'Clarify wound severity, depth, pulses, and offloading status.',
                    'Review infection workup, glycemic management, wound care follow-up, and urgent precautions for worsening infection.',
                ]),
            ],
            aiCopilotScenarioTags($context, ['beta note', 'diabetic foot wound', 'infection risk', 'wound care'])
        ),
        default => aiCopilotBuildResponse(
            'Here is a beta draft note summary for ' . $context['patient']['name'] . '. This draft must be verified by a licensed clinician.',
            [
                aiCopilotBuildSection('Draft note', [
                    'Adult female with asthma and seasonal allergy overlap presenting with cough, wheezing, nocturnal chest tightness, and increased rescue inhaler use without severe distress documented.',
                ]),
                aiCopilotBuildSection('Subjective', [
                    aiCopilotFallbackValue($facts['history_of_present_illness'], 'Respiratory history needs further clarification.'),
                    'Symptoms documented: ' . aiCopilotFallbackValue($facts['symptoms'], 'Respiratory symptoms noted.'),
                ]),
                aiCopilotBuildSection('Objective', [
                    'Vitals: ' . aiCopilotFallbackValue($facts['vitals_line'], 'No recent vitals found.'),
                    'Recent respiratory context: ' . aiCopilotFallbackValue($facts['recent_exam'], 'No recent respiratory exam summary found.'),
                    'Medication/allergy context: ' . aiCopilotFallbackValue(aiCopilotJoinParts([$facts['medication_line'], $facts['allergy_line']]), 'Medication or allergy details were limited.'),
                ]),
                aiCopilotBuildSection('Assessment', [
                    'Current chart context is most consistent with asthma symptom flare with allergic-trigger overlap, while infection or reflux overlap still needs clarification.',
                    'No severe hypoxia or severe distress is documented in the seeded chart context.',
                ]),
                aiCopilotBuildSection('Plan', [
                    'Review inhaler technique, controller adherence, trigger exposure, and whether peak-flow or additional respiratory evaluation is needed.',
                    'Reinforce urgent precautions for worsening breathing symptoms or poor response to rescue medication.',
                ]),
            ],
            aiCopilotScenarioTags($context, ['beta note', 'asthma symptoms', 'wheezing', 'inhaler review'])
        ),
    };
}

function aiCopilotBuildTreatmentPlanResponse(array $context): array
{
    if (!aiCopilotContextHasPatient($context)) {
        return aiCopilotBuildNoPatientResponse(
            'No demo patient is selected, so this is a general treatment-planning framework only.',
            [
                aiCopilotBuildSection('Immediate priorities', ['Identify any active red flags and whether urgent escalation is needed.']),
                aiCopilotBuildSection('Suggested workup or monitoring', ['Tie the plan to recent vitals, labs, medications, and the latest encounter note.']),
                aiCopilotBuildSection('Patient education', ['Explain the plan in plain language and review warning symptoms.']),
                aiCopilotBuildSection('Follow-up', ['Define what needs short-interval review vs routine follow-up.']),
                aiCopilotBuildSection('Safety precautions', ['Keep the plan read-only and clinician-verified.']),
            ],
            ['general treatment plan']
        );
    }

    $facts = aiCopilotExtractClinicalFacts($context);

    return match ($facts['patient_key']) {
        'DEMO-BILL-1003' => aiCopilotBuildResponse(
            'The treatment-planning draft should prioritize urgent symptom triage before chronic disease follow-up. This beta draft must be verified by a licensed clinician.',
            [
                aiCopilotBuildSection('Immediate priorities', [
                    'Clarify whether chest pressure or dyspnea is active now and whether the patient needs urgent escalation or ED evaluation.',
                    'Recheck vitals and symptom severity in real time because the seeded chart already shows elevated BP and pulse.',
                ]),
                aiCopilotBuildSection('Suggested workup or monitoring', [
                    'Consider ECG and troponin review, ongoing vitals monitoring, and focused cardiopulmonary reassessment per clinician judgment.',
                    'After the acute risk question is addressed, revisit diabetes, BP, and lipid control because the chart shows elevated A1C, LDL, and glucose.',
                ]),
                aiCopilotBuildSection('Patient education', [
                    'Advise the patient to report worsening chest pressure, shortness of breath, diaphoresis, or syncope immediately.',
                    'Review the importance of bringing an accurate medication list and sharing whether any doses were missed.',
                ]),
                aiCopilotBuildSection('Follow-up', [
                    'Short-interval follow-up is reasonable after urgent evaluation to revisit cardiometabolic risk management.',
                    'Medication adherence, blood pressure, and diabetes monitoring should be reassessed after the acute issue is clarified.',
                ]),
                aiCopilotBuildSection('Safety precautions', [
                    'Do not treat this beta draft as a final diagnosis or an order set.',
                    'Escalate faster if symptoms are ongoing or if the workup raises concern for ACS or another cardiopulmonary emergency.',
                ]),
            ],
            aiCopilotScenarioTags($context, ['urgent triage', 'ECG/troponin consideration', 'blood pressure', 'diabetes follow-up'])
        ),
        'DEMO-PCP-1001' => aiCopilotBuildResponse(
            'The treatment-planning draft should prioritize wound severity assessment, infection risk stratification, and glycemic control. This beta draft must be verified by a licensed clinician.',
            [
                aiCopilotBuildSection('Immediate priorities', [
                    'Assess wound depth, drainage, surrounding erythema, pulses, and whether the infection appears superficial or deeper.',
                    'Escalate quickly if redness is spreading, systemic symptoms appear, or there is concern for deep tissue involvement.',
                ]),
                aiCopilotBuildSection('Suggested workup or monitoring', [
                    'Review need for wound culture, imaging, or additional labs if osteomyelitis or abscess is a concern.',
                    'Monitor glucose control and renal function because the wound is occurring with A1C 9.6 and glucose 248 in the seeded chart context.',
                ]),
                aiCopilotBuildSection('Patient education', [
                    'Review foot protection, offloading, daily wound observation, and the importance of reporting worsening drainage or redness.',
                    'Reinforce adherence to diabetes medications and when to report low or high glucose concerns.',
                ]),
                aiCopilotBuildSection('Follow-up', [
                    'Consider close wound follow-up and referral needs such as podiatry or wound care depending on severity.',
                    'Reassess infection trend, pain, numbness, and home wound care within a short interval if the patient is managed outpatient.',
                ]),
                aiCopilotBuildSection('Safety precautions', [
                    'Escalate sooner for fever, rapidly spreading redness, worsening pain, foul odor, or concern for deep infection.',
                    'This beta draft should not be treated as an antibiotic order or a final wound-management decision.',
                ]),
            ],
            aiCopilotScenarioTags($context, ['wound assessment', 'infection severity', 'offloading', 'podiatry follow-up'])
        ),
        default => aiCopilotBuildResponse(
            'The treatment-planning draft should focus on asthma control assessment, trigger review, and urgent breathing precautions. This beta draft must be verified by a licensed clinician.',
            [
                aiCopilotBuildSection('Immediate priorities', [
                    'Assess current respiratory effort, rescue inhaler response, and whether symptoms are worsening or interfering with speech or sleep.',
                    'Check whether nocturnal symptoms and frequent rescue use suggest poor control that needs more urgent review.',
                ]),
                aiCopilotBuildSection('Suggested workup or monitoring', [
                    'Review inhaler technique, controller adherence, trigger exposure, and whether peak flow or additional respiratory evaluation is needed.',
                    'Recheck pulse, respirations, oxygen saturation, and symptom trend if symptoms do not improve.',
                ]),
                aiCopilotBuildSection('Patient education', [
                    'Explain the difference between rescue and controller inhalers, and reinforce rinsing after inhaled steroid use.',
                    'Review common triggers and what symptoms should prompt same-day or urgent follow-up.',
                ]),
                aiCopilotBuildSection('Follow-up', [
                    'Arrange close follow-up if rescue use remains high or nighttime symptoms continue.',
                    'Revisit controller adherence, trigger reduction, and whether the recent steroid burst changed symptoms.',
                ]),
                aiCopilotBuildSection('Safety precautions', [
                    'Escalate urgently for worsening shortness of breath, poor relief from rescue medication, hypoxia, or inability to speak full sentences.',
                    'This beta draft should not be treated as a final asthma action plan without clinician review.',
                ]),
            ],
            aiCopilotScenarioTags($context, ['asthma control', 'inhaler technique', 'trigger review', 'urgent breathing precautions'])
        ),
    };
}

function aiCopilotBuildBillingSupportResponse(array $context): array
{
    if (!aiCopilotContextHasPatient($context)) {
        return aiCopilotBuildNoPatientResponse(
            'No demo patient is selected, so this is a general billing-support framework only.',
            [
                aiCopilotBuildSection('Billing documentation summary', ['Summarize the visit reason, symptom severity, comorbidities, decision-making, and what the clinician actually reviewed or reassessed.']),
                aiCopilotBuildSection('Possible coding considerations', ['Keep any coding discussion provisional and tied to documented history, exam, medical decision-making, and diagnosis linkage.']),
                aiCopilotBuildSection('Missing documentation', ['Check for missing symptom detail, exam findings, diagnosis linkage, and incomplete plan or follow-up documentation.']),
                aiCopilotBuildSection('Risk / compliance reminders', ['Use this only as billing-support guidance. Do not submit claims, do not suggest upcoding, and keep coder/clinician review in the loop.']),
            ],
            ['Billing review', 'Review needed']
        );
    }

    $facts = aiCopilotExtractClinicalFacts($context);
    $visitType = aiCopilotFallbackValue($facts['visit_type'], 'established outpatient visit');
    $billingSupport = aiCopilotFallbackValue($facts['billing_support'], 'Document the symptom story, clinician assessment, and why the documented plan was appropriate for the visit context.');

    return match ($facts['patient_key']) {
        'DEMO-BILL-1003' => aiCopilotBuildResponse(
            'This billing-support draft is chart-based and not a final coding decision. Coder and clinician review are still required before billing action.',
            [
                aiCopilotBuildSection('Billing documentation summary', [
                    'Visit context reads like a ' . $visitType . ' for exertional chest pressure with mild dyspnea and nausea in a patient with hypertension, diabetes, and hyperlipidemia.',
                    'The chart should clearly show symptom timing, exertional trigger, associated symptoms, current severity, vitals reassessment, and the clinician\'s urgency assessment.',
                ]),
                aiCopilotBuildSection('Possible coding considerations', [
                    'Established outpatient E/M support depends on documented history, exam, and medical decision-making rather than this beta draft.',
                    'If no definitive diagnosis is established, symptom-based diagnosis linkage and risk-based decision-making should be documented carefully for human billing review.',
                ]),
                aiCopilotBuildSection('Missing documentation', [
                    'Clarify whether chest pressure was active during the visit, whether it radiated, and whether there were diaphoresis, syncope, or pleuritic features.',
                    'Document ECG or troponin review status, ED referral discussion if relevant, and medication-adherence context because those details affect billing-support review.',
                    aiCopilotFallbackValue($billingSupport, 'Document the clinician rationale for urgent evaluation or close follow-up.'),
                ]),
                aiCopilotBuildSection('Risk / compliance reminders', [
                    'Do not treat any CPT or ICD suggestion as definitive without full documentation support.',
                    'Do not submit or resubmit claims from this beta draft, and do not suggest upcoding.',
                ]),
            ],
            ['Billing review', 'Chart context', 'Review needed']
        ),
        'DEMO-PCP-1001' => aiCopilotBuildResponse(
            'This billing-support draft is chart-based and not a final coding decision. Coder and clinician review are still required before billing action.',
            [
                aiCopilotBuildSection('Billing documentation summary', [
                    'Visit context reads like a ' . $visitType . ' for a diabetic right foot wound with redness, drainage, pain, neuropathy, and poor glycemic control.',
                    'Documentation should show wound location, size or depth if assessed, surrounding erythema or drainage, neuropathy findings, and the clinician\'s infection-severity assessment.',
                ]),
                aiCopilotBuildSection('Possible coding considerations', [
                    'E/M support should follow the documented history, focused foot exam, infection-risk assessment, and complexity of decision-making.',
                    'Problem-list and diagnosis linkage should reflect the wound, diabetes context, neuropathy, and any infection concerns only when they are actually documented.',
                ]),
                aiCopilotBuildSection('Missing documentation', [
                    'Add foot-exam details such as pulses, depth, drainage amount, probe-to-bone concern, and offloading or wound-care counseling if reviewed.',
                    'If imaging, labs, referral, or escalation were discussed, that reasoning should be documented because it strengthens billing-support clarity.',
                    aiCopilotFallbackValue($billingSupport, 'Document wound severity, diabetic risk, and why close follow-up or referral was recommended.'),
                ]),
                aiCopilotBuildSection('Risk / compliance reminders', [
                    'Use this only as a documentation checklist for coder and clinician review.',
                    'Do not submit claims or assume a specific billed service is supported until the signed note is complete.',
                ]),
            ],
            ['Billing review', 'Chart context', 'Review needed']
        ),
        default => aiCopilotBuildResponse(
            'This billing-support draft is chart-based and not a final coding decision. Coder and clinician review are still required before billing action.',
            [
                aiCopilotBuildSection('Billing documentation summary', [
                    'Visit context reads like a ' . $visitType . ' for cough, wheezing, nocturnal chest tightness, and increased rescue inhaler use in a patient with asthma and allergy overlap.',
                    'Documentation should show symptom frequency, rescue inhaler use, controller adherence, respiratory exam context, and how symptom control was assessed.',
                ]),
                aiCopilotBuildSection('Possible coding considerations', [
                    'E/M support should reflect the documented respiratory history, assessment of control or flare severity, and any change in risk-based decision-making.',
                    'Diagnosis linkage should stay consistent with what is documented, such as asthma symptoms, allergic overlap, or other clearly supported concerns.',
                ]),
                aiCopilotBuildSection('Missing documentation', [
                    'Clarify nighttime symptom frequency, recent trigger exposure, controller adherence, and whether the patient improved or worsened after recent therapy.',
                    'Respiratory findings, oxygenation review, and inhaler-technique counseling help support the visit story before billing review.',
                    aiCopilotFallbackValue($billingSupport, 'Document symptom control assessment, medication review, and follow-up planning in the signed note.'),
                ]),
                aiCopilotBuildSection('Risk / compliance reminders', [
                    'Keep billing discussion provisional and documentation-based.',
                    'Do not submit claims, do not suggest upcoding, and keep coder/clinician review required.',
                ]),
            ],
            ['Billing review', 'Chart context', 'Review needed']
        ),
    };
}

function aiCopilotBuildFollowUpResponse(array $context): array
{
    if (!aiCopilotContextHasPatient($context)) {
        return aiCopilotBuildNoPatientResponse(
            'No demo patient is selected, so this is a general follow-up framework only.',
            [
                aiCopilotBuildSection('Follow-up timeframe', ['Tie timing to the severity of symptoms, active red flags, and whether urgent reassessment is needed.']),
                aiCopilotBuildSection('What to monitor', ['Track symptoms, recent vitals or labs, medication adherence, and whether the patient is improving or worsening.']),
                aiCopilotBuildSection('Patient instructions', ['Explain what to watch at home, how to use medications as directed, and when to contact the clinic sooner.']),
                aiCopilotBuildSection('Escalation precautions', ['Escalate for worsening symptoms, unstable vitals, or any urgent red flags.']),
                aiCopilotBuildSection('Care coordination', ['Identify whether PCP, specialty, wound care, or urgent care coordination is needed.']),
            ],
            ['Follow-up', 'Review needed']
        );
    }

    $facts = aiCopilotExtractClinicalFacts($context);

    return match ($facts['patient_key']) {
        'DEMO-BILL-1003' => aiCopilotBuildResponse(
            'This follow-up draft should stay clinician-directed because the symptom profile includes cardiopulmonary red flags. Human review is required.',
            [
                aiCopilotBuildSection('Follow-up timeframe', [
                    'If chest pressure or dyspnea is active now, same-day urgent evaluation or ED escalation should stay on the table.',
                    'If urgent evaluation is completed and the patient is stable, short-interval follow-up over the next few days can support BP, diabetes, and symptom reassessment.',
                ]),
                aiCopilotBuildSection('What to monitor', [
                    'Monitor recurrence of chest pressure, worsening shortness of breath, syncope, diaphoresis, blood pressure, and glucose trends.',
                    'Confirm whether ECG or troponin review occurred and whether medication adherence changed around symptom onset.',
                ]),
                aiCopilotBuildSection('Patient instructions', [
                    'Ask the patient to report worsening chest pain, trouble breathing, fainting, or new neurologic symptoms immediately.',
                    'Bring an updated medication list and home BP or glucose readings to the next review if available.',
                ]),
                aiCopilotBuildSection('Escalation precautions', [
                    'Escalate faster for active chest pressure, increasing dyspnea, abnormal vitals, or concern for ACS or another cardiopulmonary emergency.',
                ], 'red'),
                aiCopilotBuildSection('Care coordination', [
                    'Coordinate with the evaluating clinician regarding urgent testing review and whether cardiology or higher-acuity follow-up is needed.',
                ]),
            ],
            ['Follow-up', 'Red flags', 'Review needed']
        ),
        'DEMO-PCP-1001' => aiCopilotBuildResponse(
            'This follow-up draft should stay clinician-directed because the wound may worsen quickly if infection deepens. Human review is required.',
            [
                aiCopilotBuildSection('Follow-up timeframe', [
                    'Short-interval wound follow-up within 24 to 72 hours is reasonable if the patient is managed outpatient.',
                    'Earlier reassessment is needed if the foot exam suggests deeper infection, poor perfusion, or rapidly changing symptoms.',
                ]),
                aiCopilotBuildSection('What to monitor', [
                    'Monitor wound drainage, erythema spread, pain, numbness, glucose control, and any fever or systemic symptoms.',
                    'Track whether offloading and home wound care are actually happening between visits.',
                ]),
                aiCopilotBuildSection('Patient instructions', [
                    'Review daily wound observation, clean dressing care as directed by the clinician, and foot protection or offloading instructions.',
                    'Advise the patient to bring home glucose information and report missed diabetes medication doses or hypoglycemia concerns.',
                ]),
                aiCopilotBuildSection('Escalation precautions', [
                    'Escalate for spreading redness, foul odor, more drainage, fever, worsening pain, or concern for deep infection.',
                ], 'red'),
                aiCopilotBuildSection('Care coordination', [
                    'Coordinate wound care or podiatry follow-up if the clinician thinks the wound needs closer specialty review.',
                ]),
            ],
            ['Follow-up', 'Red flags', 'Review needed']
        ),
        default => aiCopilotBuildResponse(
            'This follow-up draft should stay clinician-directed because respiratory symptoms can change quickly if control worsens. Human review is required.',
            [
                aiCopilotBuildSection('Follow-up timeframe', [
                    'Close follow-up within days to a couple of weeks is reasonable depending on symptom burden and rescue-inhaler use.',
                    'Escalate sooner if symptoms are worsening or the patient is not improving after recent therapy.',
                ]),
                aiCopilotBuildSection('What to monitor', [
                    'Monitor rescue inhaler frequency, nighttime symptoms, wheezing, oxygenation if available, trigger exposure, and controller adherence.',
                    'Check whether the recent prednisone burst changed symptoms and whether side effects are becoming a problem.',
                ]),
                aiCopilotBuildSection('Patient instructions', [
                    'Review inhaler technique, controller use every day as prescribed, and rinsing after steroid inhaler use.',
                    'Ask the patient to note triggers, nighttime symptoms, and when rescue medication is needed.',
                ]),
                aiCopilotBuildSection('Escalation precautions', [
                    'Escalate urgently for worsening breathing, poor rescue-inhaler response, cyanosis, or inability to speak comfortably.',
                ], 'red'),
                aiCopilotBuildSection('Care coordination', [
                    'Coordinate follow-up with the primary clinician and consider whether additional asthma education or respiratory follow-up is needed.',
                ]),
            ],
            ['Follow-up', 'Red flags', 'Review needed']
        ),
    };
}

function aiCopilotBuildVisitSummaryResponse(array $context): array
{
    if (!aiCopilotContextHasPatient($context)) {
        return aiCopilotBuildNoPatientResponse(
            'No demo patient is selected, so this is a general visit-summary framework only.',
            [
                aiCopilotBuildSection('Visit summary', ['Summarize why the patient was seen, the biggest concerns addressed, and the draft plan reviewed.']),
                aiCopilotBuildSection('Key concerns addressed', ['List the main symptoms, comorbidities, and safety issues that shaped the visit.']),
                aiCopilotBuildSection('Plan discussed', ['Describe the draft workup, monitoring, treatment-plan themes, and follow-up needs.']),
                aiCopilotBuildSection('Follow-up instructions', ['State when the patient should follow up and what warning symptoms should prompt faster contact.']),
                aiCopilotBuildSection('Patient-friendly explanation', ['Translate the plan into short plain-language takeaways.']),
            ],
            ['Visit summary', 'Review needed']
        );
    }

    $facts = aiCopilotExtractClinicalFacts($context);

    return match ($facts['patient_key']) {
        'DEMO-BILL-1003' => aiCopilotBuildResponse(
            'Here is a draft summary of the visit for review by the clinical team. This beta summary should not be written into the chart automatically.',
            [
                aiCopilotBuildSection('Visit summary', [
                    'Adult male with hypertension, diabetes, and hyperlipidemia seen for intermittent exertional chest pressure with mild shortness of breath and nausea over 2 days.',
                ]),
                aiCopilotBuildSection('Key concerns addressed', [
                    'Urgent cardiopulmonary causes remained important to exclude because of exertional symptoms and cardiometabolic risk factors.',
                    'Recent vitals and labs also supported follow-up for BP, diabetes, and lipid control once urgent risk was reviewed.',
                ]),
                aiCopilotBuildSection('Plan discussed', [
                    'Draft planning centered on real-time symptom reassessment, possible ECG or troponin review, vitals monitoring, and clinician-directed escalation if symptoms were active.',
                ]),
                aiCopilotBuildSection('Follow-up instructions', [
                    'Follow up promptly after urgent evaluation and return sooner for worsening chest pain, dyspnea, diaphoresis, syncope, or new neurologic symptoms.',
                ]),
                aiCopilotBuildSection('Patient-friendly explanation', [
                    'The visit focused on making sure the chest symptoms were not missing a serious heart or lung problem and on planning close follow-up for chronic risk factors.',
                ]),
            ],
            ['Visit summary', 'Red flags', 'Review needed']
        ),
        'DEMO-PCP-1001' => aiCopilotBuildResponse(
            'Here is a draft summary of the visit for review by the clinical team. This beta summary should not be written into the chart automatically.',
            [
                aiCopilotBuildSection('Visit summary', [
                    'Adult male with diabetes, obesity, and neuropathy seen for a right foot wound with redness, drainage, numbness, and increasing pain.',
                ]),
                aiCopilotBuildSection('Key concerns addressed', [
                    'The visit addressed diabetic foot infection risk, wound severity, neuropathy, and the effect of poor glycemic control on healing.',
                ]),
                aiCopilotBuildSection('Plan discussed', [
                    'Draft planning centered on foot exam clarification, infection-severity review, possible labs or imaging if deeper infection was suspected, and wound-care follow-up.',
                ]),
                aiCopilotBuildSection('Follow-up instructions', [
                    'Follow up within a short interval and seek faster care for spreading redness, fever, more drainage, foul odor, or worsening pain.',
                ]),
                aiCopilotBuildSection('Patient-friendly explanation', [
                    'The visit focused on checking whether the foot sore was getting infected, protecting the foot, and tightening follow-up so the wound does not worsen.',
                ]),
            ],
            ['Visit summary', 'Follow-up', 'Review needed']
        ),
        default => aiCopilotBuildResponse(
            'Here is a draft summary of the visit for review by the clinical team. This beta summary should not be written into the chart automatically.',
            [
                aiCopilotBuildSection('Visit summary', [
                    'Adult female with asthma and allergy overlap seen for cough, wheezing, nocturnal chest tightness, and increased rescue inhaler use.',
                ]),
                aiCopilotBuildSection('Key concerns addressed', [
                    'The visit addressed asthma symptom control, rescue-inhaler overuse, controller adherence, and potential trigger overlap from allergies or GERD.',
                ]),
                aiCopilotBuildSection('Plan discussed', [
                    'Draft planning centered on inhaler technique review, controller adherence, trigger assessment, symptom monitoring, and urgent precautions for worsening breathing.',
                ]),
                aiCopilotBuildSection('Follow-up instructions', [
                    'Follow up if nighttime symptoms or frequent rescue use continue, and seek urgent care sooner for worsening shortness of breath or poor rescue response.',
                ]),
                aiCopilotBuildSection('Patient-friendly explanation', [
                    'The visit focused on understanding why breathing symptoms were flaring and on making sure the inhaler plan and follow-up steps were clear.',
                ]),
            ],
            ['Visit summary', 'Follow-up', 'Review needed']
        ),
    };
}

function aiCopilotBuildRoomingResponse(array $context): array
{
    $summary = aiCopilotBuildChartSummaryResponse($context);
    $summary['answer'] = 'The current demo is optimized for clinical support rather than a rooming-only workflow, but here is a concise prep summary. This draft still needs human review.';
    return $summary;
}

function aiCopilotBuildBillingReviewResponse(array $context): array
{
    if (!aiCopilotContextHasPatient($context)) {
        return aiCopilotBuildNoPatientResponse(
            'No demo patient is selected, so this is a general billing-review framework only.',
            [
                aiCopilotBuildSection('Plain-language issue', ['Look for missing diagnosis-to-procedure linkage, payer mismatches, or documentation gaps.']),
                aiCopilotBuildSection('What to check first', ['Compare the billed CPT line to the diagnosis list, encounter note, and payer/member details.']),
                aiCopilotBuildSection('Guardrails', ['Keep the review read-only and do not submit or upcode from this beta draft.']),
            ],
            ['general billing review']
        );
    }

    $claimIssue = aiCopilotClaimIssueFromContext($context);
    $claimChecks = aiCopilotClaimChecksFromContext($context);

    return aiCopilotBuildResponse(
        'The claim review still looks read-only and beta-safe: the main issue is a missing or unsupported diagnosis linkage. This draft must still be verified by a human billing reviewer.',
        [
            aiCopilotBuildSection('Plain-language issue', [
                aiCopilotFallbackValue($claimIssue, 'The claim appears to need diagnosis-to-procedure review before any resubmission.'),
            ]),
            aiCopilotBuildSection('What to check first', $claimChecks !== [] ? $claimChecks : [
                'Confirm that the diagnosis is linked to the CPT line item.',
                'Verify that the encounter documentation supports the billed service.',
                'Recheck payer and member details before any human resubmission decision.',
            ]),
            aiCopilotBuildSection('Guardrails', [
                'Do not submit, resubmit, or re-code from this beta draft.',
                'Do not suggest upcoding or unsupported diagnosis changes.',
            ]),
        ],
        aiCopilotScenarioTags($context, ['billing review', 'diagnosis link', 'claim support'])
    );
}

function aiCopilotBuildPatientFriendlyResponse(array $context): array
{
    if (!aiCopilotContextHasPatient($context)) {
        return aiCopilotBuildNoPatientResponse(
            'No demo patient is selected, so this is a general patient-friendly explanation framework only.',
            [
                aiCopilotBuildSection('Patient-friendly explanation', ['Restate the plan in plain language, what needs follow-up, and what warning symptoms should prompt earlier contact.']),
                aiCopilotBuildSection('Safety reminders', ['Keep the explanation supportive but avoid presenting a final diagnosis or treatment order.']),
            ],
            ['general patient education']
        );
    }

    $facts = aiCopilotExtractClinicalFacts($context);

    return match ($facts['patient_key']) {
        'DEMO-BILL-1003' => aiCopilotBuildResponse(
            'Here is a patient-friendly beta explanation draft that still needs clinician review.',
            [
                aiCopilotBuildSection('Patient-friendly explanation', [
                    'Your chart suggests that your chest pressure and shortness of breath need careful review because some urgent heart-related causes still need to be ruled out.',
                    'We would want to check how you are feeling right now, review your heart-risk history, and make sure important testing and monitoring are considered quickly if symptoms continue.',
                ]),
                aiCopilotBuildSection('Safety reminders', [
                    'Tell the care team right away if the chest pressure gets worse, breathing becomes harder, or you feel faint.',
                ]),
            ],
            aiCopilotScenarioTags($context, ['patient education', 'chest pain precautions'])
        ),
        'DEMO-PCP-1001' => aiCopilotBuildResponse(
            'Here is a patient-friendly beta explanation draft that still needs clinician review.',
            [
                aiCopilotBuildSection('Patient-friendly explanation', [
                    'Your foot sore is concerning because diabetes and numbness can make wounds harder to notice and slower to heal.',
                    'We would want to look closely at the wound, make sure the redness and drainage are not getting worse, and talk about wound care plus blood sugar control.',
                ]),
                aiCopilotBuildSection('Safety reminders', [
                    'Please report fever, worsening redness, more drainage, or quickly increasing pain right away.',
                ]),
            ],
            aiCopilotScenarioTags($context, ['patient education', 'foot wound precautions'])
        ),
        default => aiCopilotBuildResponse(
            'Here is a patient-friendly beta explanation draft that still needs clinician review.',
            [
                aiCopilotBuildSection('Patient-friendly explanation', [
                    'Your symptoms suggest that your asthma may not be fully controlled right now, especially because you are coughing, wheezing, and needing the rescue inhaler more often.',
                    'We would want to review how you are using your inhalers, what might be triggering symptoms, and what warning signs mean you need faster care.',
                ]),
                aiCopilotBuildSection('Safety reminders', [
                    'Please get urgent help if breathing becomes much harder, the rescue inhaler is not helping, or you cannot speak comfortably.',
                ]),
            ],
            aiCopilotScenarioTags($context, ['patient education', 'asthma precautions'])
        ),
    };
}

function aiCopilotBuildNoPatientResponse(string $answer, array $sections, array $tags): array
{
    return aiCopilotBuildResponse(AI_COPILOT_NO_PATIENT_NOTE . ' ' . $answer, $sections, $tags);
}

function aiCopilotBuildResponse(string $answer, array $sections = [], array $tags = []): array
{
    return [
        'answer' => aiCopilotCleanText($answer),
        'sections' => array_values(array_filter($sections, static fn($section) => is_array($section))),
        'tags' => aiCopilotFinalizeTags($tags),
    ];
}

function aiCopilotBuildSection(string $title, array $items, string $tone = 'neutral'): array
{
    $cleanItems = [];
    foreach ($items as $item) {
        $itemText = aiCopilotCleanText((string) $item);
        if ($itemText !== '') {
            $cleanItems[] = $itemText;
        }
    }

    return [
        'title' => $title,
        'tone' => in_array($tone, ['neutral', 'red', 'yellow'], true) ? $tone : 'neutral',
        'items' => $cleanItems,
    ];
}

function aiCopilotRoleAllowsMode(string $role, string $mode): bool
{
    $roleCatalog = aiCopilotRoleCatalog();
    return in_array($mode, $roleCatalog[$role]['allowed_modes'] ?? [], true);
}

function aiCopilotRoleSafetyNote(string $role): string
{
    return match ($role) {
        'front_desk' => 'Administrative workflow only. Minimum necessary PHI. Human review required before any outreach is sent.',
        'billing' => 'Billing-support only. Human billing and compliance review required. No claims are submitted automatically.',
        'nurse' => 'Draft support only. Human nursing and clinician review required. Medication changes and orders are restricted.',
        default => AI_COPILOT_SAFETY_NOTE,
    };
}

function aiCopilotRoleSystemInstruction(string $role): string
{
    return match ($role) {
        'nurse' => 'Nurse role: you may help with education, symptom-triage support, follow-up reminders, care instructions, medication education based on the current plan, note support, and escalation red flags. Never change medication plans, place orders, or finalize treatment.',
        'billing' => 'Billing Staff role: you may help with billing-documentation review, missing documentation checklists, coding-support suggestions, and billing-facing visit summaries. Never submit claims automatically, never assign final codes with certainty, and avoid unnecessary deep clinical reasoning.',
        'front_desk' => 'Front Desk role: use minimum necessary PHI only. You may help with appointment information, contact confirmation, reminder drafting, and general administrative workflow. Do not disclose diagnoses, medication details, labs, treatment plans, or deep clinical notes.',
        default => 'Doctor role: provide clinical reasoning support only. Use language such as consider, possible, review, or clinician should verify. Never claim final diagnosis certainty, place orders, prescribe, or write back to the chart automatically.',
    };
}

function aiCopilotMaybeBuildRolePermissionResponse(string $role, string $mode, string $message, array $context): array
{
    $value = strtolower($message);

    if (!aiCopilotRoleAllowsMode($role, $mode)) {
        return match ($role) {
            'front_desk' => array_merge(aiCopilotBuildResponse(
                'I can help with scheduling and basic administrative information, but clinical details are restricted for this role.',
                [],
                ['Front desk', 'Minimum PHI', 'Review needed']
            ), ['restriction_type' => 'front_desk_phi_limit']),
            'billing' => array_merge(aiCopilotBuildResponse(
                'I can help with billing review, but I cannot submit claims automatically or expose unnecessary clinical detail in this role.',
                [],
                ['Billing review', 'Review needed']
            ), ['restriction_type' => 'billing_role_scope_limit']),
            'nurse' => array_merge(aiCopilotBuildResponse(
                'I can help with education, follow-up, and escalation support, but that request is outside the nurse role in this demo.',
                [],
                ['Review needed', 'Follow-up']
            ), ['restriction_type' => 'nurse_role_scope_limit']),
            default => array_merge(aiCopilotBuildResponse(
                'I can help with clinical support and review-oriented draft guidance, but that request is outside the configured doctor role workflows in this demo.',
                [],
                ['Review needed', 'Chart context']
            ), ['restriction_type' => 'doctor_role_scope_limit']),
        };
    }

    if ($role === 'doctor' && preg_match('/final diagnosis|definitive diagnosis|diagnose this patient|certain diagnosis/', $value)) {
        return array_merge(aiCopilotBuildResponse(
            'I can support differential reasoning, but I cannot autonomously diagnose the patient.',
            [],
            ['Review needed', 'Red flags']
        ), ['restriction_type' => 'doctor_autonomous_diagnosis_block']);
    }

    if ($role === 'nurse' && preg_match('/(change|start|stop|increase|decrease|switch).*(medication|drug|dose|insulin|inhaler|pill)|medication change/', $value)) {
        return array_merge(aiCopilotBuildResponse(
            'I can help explain the current medication plan and flag items to review, but medication changes should be handled by the prescribing clinician.',
            [],
            ['Medication review', 'Review needed']
        ), ['restriction_type' => 'nurse_medication_change_block']);
    }

    if ($role === 'billing' && preg_match('/submit.*claim|resubmit.*claim|send.*claim/', $value)) {
        return array_merge(aiCopilotBuildResponse(
            'I can help prepare a billing review checklist, but I cannot submit claims automatically.',
            [],
            ['Billing review', 'Review needed']
        ), ['restriction_type' => 'billing_claim_submission_block']);
    }

    if ($role === 'front_desk' && preg_match('/medication|lab|diagnosis|differential|a1c|treatment|soap|clinical note|plan|prescri/', $value)) {
        return array_merge(aiCopilotBuildResponse(
            'This role only has access to scheduling and basic contact workflows. Medication details are restricted.',
            [],
            ['Front desk', 'Minimum PHI', 'Review needed']
        ), ['restriction_type' => 'front_desk_phi_limit']);
    }

    return [];
}

function aiCopilotFilterContextForRole(array $context, string $role, string $mode): array
{
    $context['role'] = $role;

    if (!aiCopilotContextHasPatient($context)) {
        return $context;
    }

    return match ($role) {
        'nurse' => aiCopilotBuildNurseRoleContext($context),
        'billing' => aiCopilotBuildBillingRoleContext($context),
        'front_desk' => aiCopilotBuildFrontDeskRoleContext($context),
        default => $context,
    };
}

function aiCopilotBuildNurseRoleContext(array $context): array
{
    $facts = aiCopilotExtractClinicalFacts($context);
    $notes = [
        [
            'date' => $context['notes'][0]['date'] ?? '',
            'title' => 'DEMO AI - Clinical Context',
            'body' => implode("\n", array_filter([
                'Chief complaint: ' . aiCopilotFallbackValue($facts['chief_complaint'], 'Current visit concern needs review.'),
                $facts['history_of_present_illness'] !== '' ? 'History of present illness: ' . $facts['history_of_present_illness'] : '',
                $facts['symptoms'] !== '' ? 'Symptoms: ' . $facts['symptoms'] : '',
                $facts['allergy_line'] !== '' ? 'Allergies: ' . $facts['allergy_line'] : '',
                $facts['recent_exam'] !== '' ? 'Recent exam: ' . $facts['recent_exam'] : '',
                $facts['medication_concerns'] !== '' ? 'Medication concerns: ' . $facts['medication_concerns'] : '',
                $facts['follow_up_considerations'] !== '' ? 'Follow-up considerations: ' . $facts['follow_up_considerations'] : '',
            ])),
        ],
    ];

    foreach ($context['notes'] as $note) {
        if (($note['title'] ?? '') === 'DEMO AI - Recent Encounter Note') {
            $notes[] = $note;
            break;
        }
    }

    $medications = [];
    foreach ($context['medications'] as $medication) {
        $medications[] = [
            'drug' => $medication['drug'] ?? '',
            'dosage' => $medication['dosage'] ?? '',
            'start_date' => $medication['start_date'] ?? '',
        ];
    }

    $context['notes'] = $notes;
    $context['medications'] = $medications;
    $context['billing'] = ['encounter' => [], 'rows' => [], 'claim' => []];
    $context['primary_insurance'] = [];

    return $context;
}

function aiCopilotBuildBillingRoleContext(array $context): array
{
    $facts = aiCopilotExtractClinicalFacts($context);
    $problemTitles = [];
    foreach (array_slice($context['problems'], 0, 4) as $problem) {
        if (!empty($problem['title'])) {
            $problemTitles[] = $problem['title'];
        }
    }

    return [
        'role' => 'billing',
        'mode' => $context['mode'] ?? '',
        'scenario' => $context['scenario'] ?? '',
        'patient_selected' => $context['patient_selected'] ?? false,
        'patient' => [
            'pid' => $context['patient']['pid'] ?? null,
            'pubpid' => $context['patient']['pubpid'] ?? '',
            'name' => $context['patient']['name'] ?? '',
        ],
        'next_appointment' => [],
        'appointments' => [],
        'encounters' => array_slice($context['encounters'], 0, 1),
        'notes' => [],
        'problems' => [],
        'allergies' => [],
        'medications' => [],
        'latest_vitals' => [],
        'primary_insurance' => $context['primary_insurance'],
        'billing' => $context['billing'],
        'billing_summary' => [
            'visit_type' => $facts['visit_type'],
            'chief_complaint' => $facts['chief_complaint'],
            'documentation_summary' => $facts['billing_support'],
            'problems_addressed' => $problemTitles,
            'follow_up_considerations' => $facts['follow_up_considerations'],
            'encounter_reason' => $context['encounters'][0]['reason'] ?? '',
        ],
    ];
}

function aiCopilotBuildFrontDeskRoleContext(array $context): array
{
    $appointment = $context['next_appointment'];
    $location = implode(', ', array_values(array_filter([
        aiCopilotCleanText($appointment['location'] ?? ''),
        aiCopilotCleanText($appointment['room'] ?? ''),
    ], static fn($value) => $value !== '')));
    $phone = aiCopilotFirstNonEmpty([
        $context['patient']['phone_cell'] ?? '',
        $context['patient']['phone_home'] ?? '',
    ]);

    return [
        'role' => 'front_desk',
        'mode' => $context['mode'] ?? '',
        'scenario' => $context['scenario'] ?? '',
        'patient_selected' => $context['patient_selected'] ?? false,
        'patient' => [
            'pid' => $context['patient']['pid'] ?? null,
            'pubpid' => $context['patient']['pubpid'] ?? '',
            'name' => $context['patient']['name'] ?? '',
            'fname' => $context['patient']['fname'] ?? '',
            'lname' => $context['patient']['lname'] ?? '',
            'email' => $context['patient']['email'] ?? '',
            'phone' => $phone,
        ],
        'next_appointment' => [
            'date' => $appointment['date'] ?? '',
            'start_time' => $appointment['start_time'] ?? '',
            'end_time' => $appointment['end_time'] ?? '',
            'appointment_type' => $appointment['appointment_type'] ?? ($appointment['title'] ?? ''),
            'provider_name' => $appointment['provider_name'] ?? '',
            'location' => $location,
            'check_in_instructions' => $appointment['check_in_instructions'] ?? '',
            'check_in_status' => $appointment['check_in_status'] ?? '',
        ],
        'appointments' => [],
        'encounters' => [],
        'notes' => [],
        'problems' => [],
        'allergies' => [],
        'medications' => [],
        'latest_vitals' => [],
        'primary_insurance' => [],
        'billing' => ['encounter' => [], 'rows' => [], 'claim' => []],
    ];
}

function aiCopilotBuildFrontDeskFallbackResponse(string $mode, string $message, array $context): array
{
    if (!aiCopilotContextHasPatient($context)) {
        return aiCopilotBuildNoPatientResponse(
            'Select a demo patient to use front-desk workflows like appointment lookup, contact confirmation, or reminder drafting.',
            [
                aiCopilotBuildSection('Administrative summary', ['Pick a demo patient to see appointment, contact, and check-in details.']),
            ],
            ['Front desk', 'Minimum PHI']
        );
    }

    $appointment = $context['next_appointment'];
    $formattedDateTime = aiCopilotFormatAppointmentDateTime($appointment['date'] ?? '', $appointment['start_time'] ?? '');
    $type = aiCopilotFallbackValue($appointment['appointment_type'] ?? '', 'scheduled visit');
    $provider = aiCopilotFallbackValue($appointment['provider_name'] ?? '', 'the assigned provider');
    $location = aiCopilotFallbackValue($appointment['location'] ?? '', 'the clinic');
    $email = aiCopilotFallbackValue($context['patient']['email'] ?? '', 'no demo email on file');
    $phone = aiCopilotFallbackValue($context['patient']['phone'] ?? '', 'no demo phone on file');
    $instructions = aiCopilotFallbackValue($appointment['check_in_instructions'] ?? '', 'Please arrive 15 minutes early and bring any required documents.');
    $status = aiCopilotFallbackValue($appointment['check_in_status'] ?? '', 'Not checked in');
    $firstName = aiCopilotFallbackValue($context['patient']['fname'] ?? '', $context['patient']['name'] ?? 'the patient');

    return match ($mode) {
        'patient_contact' => aiCopilotBuildResponse(
            $context['patient']['name'] . '\'s demo contact information is ' . $email . ' and ' . $phone . '.',
            [
                aiCopilotBuildSection('Contact details', [
                    'Email: ' . $email,
                    'Phone: ' . $phone,
                ]),
                aiCopilotBuildSection('Contact workflow reminders', [
                    'Use minimum necessary PHI when confirming contact details.',
                    'Confirm the patient is still using this email and phone number before outreach.',
                ]),
            ],
            ['Front desk', 'Contact', 'Minimum PHI']
        ),
        'send_reminder' => aiCopilotBuildResponse(
            'I drafted a reminder for ' . $context['patient']['name'] . ' using scheduling details only.',
            [
                aiCopilotBuildSection('Reminder draft', [
                    'Subject: Appointment Reminder from OpenEMR Demo Clinic',
                    'Hello ' . $firstName . ', this is a reminder that your next appointment is scheduled for ' . $formattedDateTime . ' with ' . $provider . ' for ' . $type . ' at ' . $location . '. Please arrive 15 minutes early and bring any required documents. Thank you, OpenEMR Demo Clinic.',
                ]),
                aiCopilotBuildSection('Delivery details', [
                    'Recipient: ' . $email,
                    'Check-in instructions: ' . $instructions,
                    'Status: ' . $status,
                ]),
            ],
            ['Front desk', 'Reminder', 'Minimum PHI']
        ),
        'front_desk_summary' => aiCopilotBuildResponse(
            $context['patient']['name'] . ' has a demo appointment workflow ready for front-desk review.',
            [
                aiCopilotBuildSection('Administrative summary', [
                    'Contact on file: ' . $email . ' / ' . $phone,
                    'Check-in status: ' . $status,
                ]),
                aiCopilotBuildSection('Next appointment', [
                    $formattedDateTime . ' with ' . $provider . ' for ' . $type . ' at ' . $location . '.',
                ]),
                aiCopilotBuildSection('Contact details', [
                    'Check-in instructions: ' . $instructions,
                ]),
            ],
            ['Front desk', 'Appointment', 'Minimum PHI']
        ),
        default => aiCopilotBuildResponse(
            $context['patient']['name'] . '\'s next appointment is scheduled for ' . $formattedDateTime . ' with ' . $provider . ' for ' . $type . ' at ' . $location . '.',
            [
                aiCopilotBuildSection('Appointment details', [
                    'Appointment type: ' . $type,
                    'Provider: ' . $provider,
                    'Location: ' . $location,
                ]),
                aiCopilotBuildSection('Check-in instructions', [
                    $instructions,
                    'Check-in status: ' . $status,
                ]),
            ],
            ['Front desk', 'Appointment', 'Minimum PHI']
        ),
    };
}

function aiCopilotBuildBillingStaffFallbackResponse(string $mode, string $message, array $context): array
{
    if (!aiCopilotContextHasPatient($context)) {
        return aiCopilotBuildNoPatientResponse(
            'Select a demo patient to use billing-review workflows.',
            [
                aiCopilotBuildSection('Billing documentation summary', ['Choose a demo patient to review visit context, missing documentation, or claim-support details.']),
            ],
            ['Billing review', 'Review needed']
        );
    }

    $summary = $context['billing_summary'] ?? [];
    $visitType = aiCopilotFallbackValue($summary['visit_type'] ?? '', 'established outpatient visit');
    $complaint = aiCopilotFallbackValue($summary['chief_complaint'] ?? '', 'the documented visit concern');
    $encounterReason = aiCopilotFallbackValue($summary['encounter_reason'] ?? '', 'documented encounter context');
    $documentationSummary = aiCopilotFallbackValue($summary['documentation_summary'] ?? '', 'Document the core symptom story, assessment, and plan reviewed during the visit.');
    $claimIssue = aiCopilotClaimIssueFromContext($context);
    $claimChecks = aiCopilotClaimChecksFromContext($context);

    if ($mode === 'visit_summary') {
        return aiCopilotBuildResponse(
            'Here is a billing-facing visit summary draft for coder and clinician review.',
            [
                aiCopilotBuildSection('Visit summary', [
                    'Visit type: ' . $visitType,
                    'Encounter reason: ' . $encounterReason,
                    'High-level concern: ' . $complaint,
                ]),
                aiCopilotBuildSection('Key concerns addressed', [
                    'Problems addressed: ' . aiCopilotFallbackValue(aiCopilotJoinList($summary['problems_addressed'] ?? []), 'See the signed encounter note for the full problem list.'),
                ]),
                aiCopilotBuildSection('Plan discussed', [
                    $documentationSummary,
                ]),
                aiCopilotBuildSection('Follow-up instructions', [
                    aiCopilotFallbackValue($summary['follow_up_considerations'] ?? '', 'Review what follow-up or escalation instructions were documented in the final note.'),
                ]),
                aiCopilotBuildSection('Patient-friendly explanation', [
                    'This summary is for billing-support review only and does not replace the signed chart note.',
                ]),
            ],
            ['Billing review', 'Visit summary', 'Review needed']
        );
    }

    if ($mode === 'billing_review') {
        return aiCopilotBuildResponse(
            'I can help with billing review, but I cannot submit claims automatically. Here is the claim-support issue to review first.',
            [
                aiCopilotBuildSection('Plain-language issue', [
                    aiCopilotFallbackValue($claimIssue, 'The claim appears to need diagnosis-to-procedure review before any resubmission.'),
                ]),
                aiCopilotBuildSection('What to check first', $claimChecks !== [] ? $claimChecks : [
                    'Compare the billed service to the encounter documentation and diagnosis linkage.',
                    'Confirm payer/member details before any coder action.',
                ]),
                aiCopilotBuildSection('Guardrails', [
                    'Do not submit or resubmit claims from this demo assistant.',
                    'Do not assign final codes with certainty unless the signed documentation supports them.',
                ]),
            ],
            ['Billing review', 'Review needed', 'Chart context']
        );
    }

    return aiCopilotBuildResponse(
        'This is a billing-support suggestion only. Coder and clinician review are still required.',
        [
            aiCopilotBuildSection('Billing documentation summary', [
                'Visit type: ' . $visitType,
                'Chief complaint: ' . $complaint,
                'Encounter reason: ' . $encounterReason,
            ]),
            aiCopilotBuildSection('Possible coding considerations', [
                'Keep coding discussion tied to the documented history, exam, and decision-making rather than this beta draft.',
                'Use diagnosis linkage only if the encounter documentation clearly supports it.',
            ]),
            aiCopilotBuildSection('Missing documentation', [
                $documentationSummary,
                aiCopilotFallbackValue($summary['follow_up_considerations'] ?? '', 'Clarify any follow-up or escalation instructions that were part of the visit.'),
            ]),
            aiCopilotBuildSection('Risk / compliance reminders', [
                'No automatic claim submission is allowed.',
                'No definitive coding assignment should be made without human review.',
            ]),
        ],
        ['Billing review', 'Review needed', 'Chart context']
    );
}

function aiCopilotSendReminderEmailAction(string $role, array $context): array
{
    if ($role !== 'front_desk') {
        return [
            'ok' => false,
            'sent' => false,
            'demo' => true,
            'message' => 'Only the Front Desk role can send appointment reminder emails in this demo.',
            'tags' => ['Front desk', 'Minimum PHI', 'Review needed'],
            'sources' => aiCopilotBuildSources($context),
            'safety_note' => aiCopilotRoleSafetyNote($role),
        ];
    }

    if (!aiCopilotContextHasPatient($context)) {
        return [
            'ok' => false,
            'sent' => false,
            'demo' => true,
            'message' => 'Select a demo patient before sending a reminder email.',
            'tags' => ['Front desk', 'Reminder', 'Minimum PHI'],
            'sources' => aiCopilotBuildSources($context),
            'safety_note' => aiCopilotRoleSafetyNote($role),
        ];
    }

    $email = aiCopilotCleanText($context['patient']['email'] ?? '');
    $appointment = $context['next_appointment'] ?? [];
    if ($email === '' || ($appointment['date'] ?? '') === '') {
        return [
            'ok' => false,
            'sent' => false,
            'demo' => true,
            'message' => 'A demo email address and upcoming appointment are required before sending a reminder.',
            'tags' => ['Front desk', 'Reminder', 'Minimum PHI'],
            'sources' => aiCopilotBuildSources($context),
            'safety_note' => aiCopilotRoleSafetyNote($role),
        ];
    }

    $subject = 'Appointment Reminder from OpenEMR Demo Clinic';
    $body = aiCopilotBuildReminderEmailBody($context);
    $sender = aiCopilotResolveReminderSender();

    $sent = false;
    if ($sender !== '' && filter_var($sender, FILTER_VALIDATE_EMAIL) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mail = new PHPMailer(true);
        try {
            $mail->setFrom($sender, 'OpenEMR Demo Clinic');
            $mail->addAddress($email, $context['patient']['name'] ?? '');
            $mail->isMail();
            $mail->Subject = $subject;
            $mail->Body = $body;
            $sent = $mail->send();
        } catch (\Throwable) {
            $sent = false;
        }
    }

    return [
        'ok' => true,
        'sent' => $sent,
        'demo' => !$sent,
        'message' => $sent
            ? 'Reminder email sent to ' . $email . '.'
            : 'Demo reminder prepared for ' . $email . '. Email sending is not configured in this environment.',
        'tags' => ['Front desk', 'Reminder', 'Minimum PHI'],
        'sources' => aiCopilotBuildSources($context),
        'safety_note' => aiCopilotRoleSafetyNote($role),
    ];
}

function aiCopilotResolveReminderSender(): string
{
    $sender = OEGlobalsBag::getInstance()->getString('patient_reminder_sender_email');
    if ($sender === '') {
        $sender = OEGlobalsBag::getInstance()->getString('practice_return_email_path');
    }

    return aiCopilotCleanText($sender);
}

function aiCopilotBuildReminderEmailBody(array $context): string
{
    $appointment = $context['next_appointment'] ?? [];
    $location = aiCopilotFallbackValue($appointment['location'] ?? '', 'OpenEMR Demo Clinic');

    return "Hello " . aiCopilotFallbackValue($context['patient']['fname'] ?? '', 'Patient') . ",\n\n"
        . 'This is a reminder that your next appointment is scheduled for '
        . aiCopilotFormatAppointmentDateTime($appointment['date'] ?? '', $appointment['start_time'] ?? '')
        . ' with ' . aiCopilotFallbackValue($appointment['provider_name'] ?? '', 'your provider')
        . ' for ' . aiCopilotFallbackValue($appointment['appointment_type'] ?? '', 'your scheduled visit')
        . ' at ' . $location . ".\n\n"
        . "Please arrive 15 minutes early and bring any required documents.\n\n"
        . "Thank you,\nOpenEMR Demo Clinic";
}

function aiCopilotFormatAppointmentDateTime(string $date, string $time): string
{
    if ($date === '') {
        return 'the scheduled appointment time';
    }

    $timestamp = strtotime(trim($date . ' ' . $time));
    if ($timestamp === false) {
        return trim($date . ' ' . $time);
    }

    return date('F j, Y \a\t g:i A', $timestamp);
}

function aiCopilotExtractClinicalFacts(array $context): array
{
    $clinicalNote = aiCopilotFindNoteBody($context['notes'], 'DEMO AI - Clinical Context');
    $recentEncounterNote = aiCopilotFindNoteBody($context['notes'], 'DEMO AI - Recent Encounter Note');
    $clinicalMap = aiCopilotParseLabeledNote($clinicalNote);
    $recentMap = aiCopilotParseLabeledNote($recentEncounterNote);

    $conditions = [];
    foreach ($context['problems'] as $problem) {
        if (!empty($problem['title'])) {
            $conditions[] = $problem['title'];
        }
    }

    return [
        'patient_key' => $context['patient']['pubpid'] ?? '',
        'chief_complaint' => aiCopilotMapValue($clinicalMap, 'chief complaint'),
        'visit_type' => aiCopilotMapValue($clinicalMap, 'visit type'),
        'history_of_present_illness' => aiCopilotMapValue($clinicalMap, 'history of present illness'),
        'symptoms' => aiCopilotMapValue($clinicalMap, 'symptoms'),
        'past_medical_history' => aiCopilotMapValue($clinicalMap, 'past medical history'),
        'risk_factors' => aiCopilotMapValue($clinicalMap, 'risk factors'),
        'recent_labs' => aiCopilotMapValue($clinicalMap, 'recent labs'),
        'recent_exam' => aiCopilotFirstNonEmpty([
            aiCopilotMapValue($clinicalMap, 'recent exam'),
            aiCopilotMapValue($recentMap, 'objective'),
        ]),
        'medication_concerns' => aiCopilotMapValue($clinicalMap, 'medication concerns'),
        'billing_support' => aiCopilotMapValue($clinicalMap, 'billing support'),
        'follow_up_considerations' => aiCopilotMapValue($clinicalMap, 'follow-up considerations'),
        'recent_note_subjective' => aiCopilotMapValue($recentMap, 'subjective'),
        'recent_note_assessment' => aiCopilotMapValue($recentMap, 'assessment'),
        'recent_note_plan' => aiCopilotMapValue($recentMap, 'plan'),
        'conditions' => $conditions,
        'medication_line' => aiCopilotFormatMedicationList($context['medications']),
        'allergy_line' => aiCopilotFormatConditionList($context['allergies']),
        'vitals_line' => aiCopilotFormatVitalsSummary($context['latest_vitals']),
    ];
}

function aiCopilotParseLabeledNote(string $body): array
{
    $map = [];
    if ($body === '') {
        return $map;
    }

    $lines = preg_split('/\R+/', $body) ?: [];
    foreach ($lines as $line) {
        if (!str_contains($line, ':')) {
            continue;
        }

        [$label, $value] = explode(':', $line, 2);
        $label = strtolower(trim($label));
        $value = aiCopilotCleanText($value);
        if ($label !== '' && $value !== '') {
            $map[$label] = $value;
        }
    }

    return $map;
}

function aiCopilotMapValue(array $map, string $key): string
{
    return aiCopilotCleanText($map[strtolower($key)] ?? '');
}

function aiCopilotScenarioTags(array $context, array $extraTags = []): array
{
    return aiCopilotFinalizeTags(array_merge($extraTags, ['Chart context', 'Review needed']));
}

function aiCopilotFinalizeTags(array $tags): array
{
    $preferredOrder = [
        'Front desk',
        'Appointment',
        'Contact',
        'Reminder',
        'Minimum PHI',
        'Billing review',
        'Visit summary',
        'Draft note',
        'Medication review',
        'Follow-up',
        'Red flags',
        'Patient education',
        'Chart context',
        'Review needed',
        'Beta',
    ];

    $normalized = [];
    foreach ($tags as $tag) {
        $tagText = aiCopilotNormalizePrompt($tag);
        if ($tagText === '') {
            continue;
        }

        $normalized[] = aiCopilotCanonicalTagLabel($tagText);
    }

    if ($normalized === []) {
        $normalized = ['Chart context', 'Review needed'];
    }

    $normalized = array_values(array_unique(array_filter($normalized, static fn($tag) => $tag !== '')));

    usort($normalized, static function (string $left, string $right) use ($preferredOrder): int {
        $leftIndex = array_search($left, $preferredOrder, true);
        $rightIndex = array_search($right, $preferredOrder, true);
        $leftIndex = $leftIndex === false ? 999 : $leftIndex;
        $rightIndex = $rightIndex === false ? 999 : $rightIndex;

        if ($leftIndex === $rightIndex) {
            return strcmp($left, $right);
        }

        return $leftIndex <=> $rightIndex;
    });

    return array_slice($normalized, 0, 3);
}

function aiCopilotCanonicalTagLabel(string $tag): string
{
    $value = strtolower($tag);

    return match (true) {
        preg_match('/front desk/', $value) === 1 => 'Front desk',
        preg_match('/appointment|schedule/', $value) === 1 => 'Appointment',
        preg_match('/contact/', $value) === 1 => 'Contact',
        preg_match('/reminder|outreach|email/', $value) === 1 => 'Reminder',
        preg_match('/minimum.*phi|minimum phi/', $value) === 1 => 'Minimum PHI',
        preg_match('/billing|claim|coding|cpt|icd|payer|diagnosis link/', $value) === 1 => 'Billing review',
        preg_match('/follow[- ]?up|monitoring|care coordination|offloading|podiatry|trigger review/', $value) === 1 => 'Follow-up',
        preg_match('/red flag|acs|urgent|triage|infection risk|osteomyelitis|dyspnea|precaution/', $value) === 1 => 'Red flags',
        preg_match('/medication|metformin|lisinopril|atorvastatin|aspirin|glipizide|gabapentin|albuterol|prednisone|inhaler|adherence/', $value) === 1 => 'Medication review',
        preg_match('/note|soap|documentation/', $value) === 1 => 'Draft note',
        preg_match('/visit summary|chart summary|summary/', $value) === 1 => 'Visit summary',
        preg_match('/patient education|patient-friendly/', $value) === 1 => 'Patient education',
        preg_match('/review/', $value) === 1 => 'Review needed',
        preg_match('/beta/', $value) === 1 => 'Beta',
        preg_match('/chart|context/', $value) === 1 => 'Chart context',
        default => ucfirst(substr(aiCopilotCleanText($tag), 0, 28)),
    };
}

function aiCopilotClaimIssueFromContext(array $context): string
{
    $billingNote = aiCopilotFindNoteBody($context['notes'], 'DEMO AI - Billing Claim Source');
    $issue = '';
    if ($billingNote !== '') {
        $issue = aiCopilotMapValue(aiCopilotParseLabeledNote(str_replace('. ', "\n", $billingNote)), 'plain-language issue');
    }

    $missingLinkRow = aiCopilotFindBillingRowMissingDiagnosisLink($context['billing']['rows']);
    if ($missingLinkRow !== []) {
        $issue = trim(($missingLinkRow['code_type'] ?? 'Procedure') . ' ' . ($missingLinkRow['code'] ?? '') . ' is missing a diagnosis link on the encounter.');
    }

    if ($issue === '') {
        $issue = 'The chart suggests a missing or unsupported diagnosis link on the claim.';
    }

    return $issue;
}

function aiCopilotClaimChecksFromContext(array $context): array
{
    $billingNote = aiCopilotFindNoteBody($context['notes'], 'DEMO AI - Billing Claim Source');
    if ($billingNote === '') {
        return [];
    }

    preg_match('/What to check first:\s*(.+?)\.\s*AI safety:/i', $billingNote, $matches);
    $raw = aiCopilotCleanText($matches[1] ?? '');
    return aiCopilotSplitChecklist($raw);
}

function aiCopilotBuildSources(array $context): array
{
    if (!aiCopilotContextHasPatient($context)) {
        return ['no patient chart selected'];
    }

    $sources = ['patient_data'];

    if (!empty($context['appointments']) || !empty($context['next_appointment'])) {
        $sources[] = 'openemr_postcalendar_events';
    }
    if (!empty($context['encounters'])) {
        $sources[] = 'form_encounter';
    }
    if (!empty($context['notes'])) {
        $sources[] = 'pnotes';
    }
    if (!empty($context['problems']) || !empty($context['allergies'])) {
        $sources[] = 'lists';
    }
    if (!empty($context['medications'])) {
        $sources[] = 'prescriptions';
    }
    if (!empty($context['latest_vitals'])) {
        $sources[] = 'form_vitals';
    }
    if (!empty($context['billing']['rows']) || !empty($context['billing']['claim']) || !empty($context['primary_insurance'])) {
        $sources[] = 'billing/demo claim data';
    }

    return array_values(array_unique($sources));
}

function aiCopilotContextHasPatient(array $context): bool
{
    return !empty($context['patient_selected']) && !empty($context['patient']['name']);
}

function aiCopilotFetchAll(string $sql, array $binds = []): array
{
    $result = sqlStatement($sql, $binds);
    $rows = [];
    while ($row = sqlFetchArray($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function aiCopilotNormalizeAppointment(array $appointment): array
{
    if (empty($appointment)) {
        return [];
    }

    $summary = aiCopilotCleanMultilineText($appointment['pc_hometext'] ?? '');
    $summaryMap = aiCopilotParseLabeledNote($summary);

    return [
        'title' => aiCopilotCleanText($appointment['pc_title'] ?? ''),
        'date' => $appointment['pc_eventDate'] ?? '',
        'start_time' => $appointment['pc_startTime'] ?? '',
        'end_time' => $appointment['pc_endTime'] ?? '',
        'status' => $appointment['pc_apptstatus'] ?? '',
        'room' => aiCopilotCleanText($appointment['pc_room'] ?? ''),
        'location' => aiCopilotCleanText($appointment['pc_location'] ?? ''),
        'summary' => $summary,
        'appointment_type' => aiCopilotFirstNonEmpty([
            rtrim(aiCopilotMapValue($summaryMap, 'appointment type'), '.'),
            aiCopilotCleanText($appointment['pc_title'] ?? ''),
        ]),
        'provider_name' => rtrim(aiCopilotMapValue($summaryMap, 'provider'), '.'),
        'check_in_instructions' => aiCopilotMapValue($summaryMap, 'check-in instructions'),
        'check_in_status' => rtrim(aiCopilotMapValue($summaryMap, 'check-in status'), '.'),
    ];
}

function aiCopilotNormalizeEncounter(array $encounter): array
{
    if (empty($encounter)) {
        return [];
    }

    return [
        'date' => $encounter['date'] ?? '',
        'reason' => aiCopilotCleanText($encounter['reason'] ?? ''),
        'encounter' => (string) ($encounter['encounter'] ?? ''),
        'billing_note' => aiCopilotCleanText($encounter['billing_note'] ?? ''),
    ];
}

function aiCopilotNormalizeNote(array $note): array
{
    return [
        'date' => $note['date'] ?? '',
        'title' => aiCopilotCleanText($note['title'] ?? ''),
        'body' => aiCopilotCleanMultilineText($note['body'] ?? ''),
    ];
}

function aiCopilotNormalizeProblem(array $problem): array
{
    return [
        'title' => aiCopilotCleanText($problem['title'] ?? ''),
        'diagnosis' => aiCopilotCleanText($problem['diagnosis'] ?? ''),
        'comments' => aiCopilotCleanText($problem['comments'] ?? ''),
        'begdate' => $problem['begdate'] ?? '',
    ];
}

function aiCopilotNormalizeMedication(array $medication): array
{
    return [
        'drug' => aiCopilotCleanText($medication['drug'] ?? ''),
        'dosage' => aiCopilotCleanText($medication['dosage'] ?? ''),
        'quantity' => aiCopilotCleanText($medication['quantity'] ?? ''),
        'note' => aiCopilotCleanText($medication['note'] ?? ''),
        'start_date' => $medication['start_date'] ?? '',
    ];
}

function aiCopilotNormalizeVitals(array $vitals): array
{
    if (empty($vitals)) {
        return [];
    }

    return [
        'date' => $vitals['date'] ?? '',
        'bps' => aiCopilotCleanText((string) ($vitals['bps'] ?? '')),
        'bpd' => aiCopilotCleanText((string) ($vitals['bpd'] ?? '')),
        'weight' => aiCopilotCleanText((string) ($vitals['weight'] ?? '')),
        'height' => aiCopilotCleanText((string) ($vitals['height'] ?? '')),
        'temperature' => aiCopilotCleanText((string) ($vitals['temperature'] ?? '')),
        'pulse' => aiCopilotCleanText((string) ($vitals['pulse'] ?? '')),
        'respiration' => aiCopilotCleanText((string) ($vitals['respiration'] ?? '')),
        'note' => aiCopilotCleanText($vitals['note'] ?? ''),
        'bmi' => aiCopilotCleanText((string) ($vitals['BMI'] ?? '')),
        'oxygen_saturation' => aiCopilotCleanText((string) ($vitals['oxygen_saturation'] ?? '')),
    ];
}

function aiCopilotNormalizeInsurance(array $insurance): array
{
    if (empty($insurance)) {
        return [];
    }

    $labelParts = array_filter([
        aiCopilotCleanText($insurance['carrier'] ?? ''),
        aiCopilotCleanText($insurance['plan_name'] ?? ''),
    ]);

    return [
        'type' => $insurance['type'] ?? '',
        'carrier' => aiCopilotCleanText($insurance['carrier'] ?? ''),
        'plan_name' => aiCopilotCleanText($insurance['plan_name'] ?? ''),
        'policy_number' => aiCopilotCleanText($insurance['policy_number'] ?? ''),
        'copay' => aiCopilotCleanText($insurance['copay'] ?? ''),
        'label' => implode(' / ', $labelParts),
    ];
}

function aiCopilotNormalizeBillingRow(array $row): array
{
    return [
        'code_type' => aiCopilotCleanText($row['code_type'] ?? ''),
        'code' => aiCopilotCleanText($row['code'] ?? ''),
        'code_text' => aiCopilotCleanText($row['code_text'] ?? ''),
        'fee' => (string) ($row['fee'] ?? ''),
        'justify' => aiCopilotCleanText($row['justify'] ?? ''),
        'billed' => (string) ($row['billed'] ?? ''),
        'activity' => (string) ($row['activity'] ?? ''),
    ];
}

function aiCopilotNormalizeClaim(array $claim): array
{
    if (empty($claim)) {
        return [];
    }

    return [
        'version' => (string) ($claim['version'] ?? ''),
        'payer_id' => (string) ($claim['payer_id'] ?? ''),
        'status' => isset($claim['status']) ? (int) $claim['status'] : null,
        'bill_time' => $claim['bill_time'] ?? '',
        'process_time' => $claim['process_time'] ?? '',
        'process_file' => aiCopilotCleanText($claim['process_file'] ?? ''),
        'submitted_claim' => aiCopilotCleanText($claim['submitted_claim'] ?? ''),
    ];
}

function aiCopilotReadEnv(string $key): string
{
    $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    return is_string($value) ? trim($value) : '';
}

function aiCopilotCleanText(string $text): string
{
    return trim(preg_replace('/\s+/', ' ', $text) ?? '');
}

function aiCopilotCleanMultilineText(string $text): string
{
    $normalized = str_replace(["\r\n", "\r"], "\n", trim($text));
    if ($normalized === '') {
        return '';
    }

    $lines = preg_split('/\n+/', $normalized) ?: [];
    $cleanLines = [];
    foreach ($lines as $line) {
        $line = trim(preg_replace('/[ \t]+/', ' ', $line) ?? '');
        if ($line !== '') {
            $cleanLines[] = $line;
        }
    }

    return implode("\n", $cleanLines);
}

function aiCopilotFindNoteBody(array $notes, string $title): string
{
    foreach ($notes as $note) {
        if (($note['title'] ?? '') === $title) {
            return $note['body'] ?? '';
        }
    }
    return '';
}

function aiCopilotFormatMedicationList(array $medications): string
{
    $parts = [];
    foreach ($medications as $medication) {
        $drug = $medication['drug'] ?? '';
        if ($drug === '') {
            continue;
        }
        $parts[] = trim($drug . (!empty($medication['dosage']) ? ' ' . $medication['dosage'] : ''));
    }

    return implode('; ', $parts);
}

function aiCopilotFormatConditionList(array $conditions): string
{
    $parts = [];
    foreach ($conditions as $condition) {
        $title = $condition['title'] ?? '';
        if ($title !== '') {
            $parts[] = $title;
        }
    }

    return implode('; ', $parts);
}

function aiCopilotFormatVitalsSummary(array $vitals): string
{
    if ($vitals === []) {
        return '';
    }

    $parts = [];
    if (($vitals['bps'] ?? '') !== '' && ($vitals['bpd'] ?? '') !== '') {
        $parts[] = 'BP ' . $vitals['bps'] . '/' . $vitals['bpd'];
    }
    if (($vitals['pulse'] ?? '') !== '') {
        $parts[] = 'pulse ' . $vitals['pulse'];
    }
    if (($vitals['respiration'] ?? '') !== '') {
        $parts[] = 'respirations ' . $vitals['respiration'];
    }
    if (($vitals['temperature'] ?? '') !== '') {
        $parts[] = 'temp ' . $vitals['temperature'] . ' F';
    }
    if (($vitals['oxygen_saturation'] ?? '') !== '') {
        $parts[] = 'SpO2 ' . $vitals['oxygen_saturation'] . '%';
    }

    return implode(', ', $parts);
}

function aiCopilotSplitChecklist(string $rawValue): array
{
    if ($rawValue === '') {
        return [];
    }

    $normalized = str_replace(';', ',', $rawValue);
    $parts = array_map('trim', explode(',', $normalized));
    return array_values(array_filter(array_map('aiCopilotUcfirst', $parts), static fn($item) => $item !== ''));
}

function aiCopilotFallbackValue(string $value, string $fallback): string
{
    return $value !== '' ? $value : $fallback;
}

function aiCopilotUcfirst(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    return strtoupper(substr($value, 0, 1)) . substr($value, 1);
}

function aiCopilotJoinList(array $values): string
{
    return implode(', ', array_values(array_filter(array_map('aiCopilotCleanText', $values), static fn($value) => $value !== '')));
}

function aiCopilotJoinParts(array $values): string
{
    return implode('; ', array_values(array_filter(array_map(static fn($value) => aiCopilotCleanText((string) $value), $values), static fn($value) => $value !== '')));
}

function aiCopilotFindBillingRowMissingDiagnosisLink(array $rows): array
{
    foreach ($rows as $row) {
        $codeType = strtoupper((string) ($row['code_type'] ?? ''));
        if (!in_array($codeType, ['CPT4', 'HCPCS', 'CPT'], true)) {
            continue;
        }
        if (trim((string) ($row['justify'] ?? '')) === '') {
            return $row;
        }
    }
    return [];
}

function aiCopilotFirstNonEmpty(array $values): string
{
    foreach ($values as $value) {
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }
    }

    return '';
}
