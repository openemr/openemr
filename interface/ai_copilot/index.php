<?php

/**
 * OpenEMR Medical Co-Pilot Demo.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;

$isEmbedded = !empty($_GET['embedded']) && $_GET['embedded'] === '1';
$session = SessionWrapperFactory::getInstance()->getActiveSession();
if (empty($session->get('csrf_private_key'))) {
    CsrfUtils::setupCsrfKey($session);
}

$csrfToken = CsrfUtils::collectCsrfToken(session: $session);
$demoPatients = [];

$patientResult = sqlStatement(
    "SELECT pid, pubpid, fname, lname, DOB
     FROM patient_data
     WHERE genericname1 = ?
     ORDER BY lname, fname",
    ['demo_scenario']
);

while ($row = sqlFetchArray($patientResult)) {
    $demoPatients[] = $row;
}

$quickActions = [
    [
        'mode' => 'differential_diagnosis',
        'title' => xl('Differential Diagnosis'),
        'summary' => xl('Red flags, missing information, and likely causes.'),
        'prompt' => xl('For the selected patient, suggest a differential diagnosis based on the chart context. Include likely possibilities, red flags, key gaps, and what should be clarified next.'),
    ],
    [
        'mode' => 'medication_info',
        'title' => xl('Medication Info'),
        'summary' => xl('Safety concerns, interactions, and counseling points.'),
        'prompt' => xl('Review this patient\'s medications and explain possible safety concerns, adherence issues, interactions, monitoring needs, and counseling points.'),
    ],
    [
        'mode' => 'clinical_notes',
        'title' => xl('Clinical Notes'),
        'summary' => xl('Draft summaries, SOAP notes, and documentation support.'),
        'prompt' => xl('Draft a concise clinical note summary for this patient using the available chart context.'),
    ],
    [
        'mode' => 'treatment_plan',
        'title' => xl('Treatment Plan'),
        'summary' => xl('Follow-up, education, and safety checks.'),
        'prompt' => xl('Create a draft treatment plan for this patient, including follow-up, patient education, monitoring, and safety checks.'),
    ],
    [
        'mode' => 'billing',
        'title' => xl('Billing'),
        'summary' => xl('Documentation support, coding considerations, and review gaps.'),
        'prompt' => xl('Review the selected patient\'s visit context and suggest billing-support documentation points, possible coding considerations, and missing documentation needed before billing review.'),
    ],
    [
        'mode' => 'billing_review',
        'title' => xl('Claim Review'),
        'summary' => xl('Plain-language claim issue review and first checks.'),
        'prompt' => xl('Why did this claim fail, and what should I check first?'),
    ],
    [
        'mode' => 'follow_up',
        'title' => xl('Follow-Up'),
        'summary' => xl('Timeframe, monitoring, instructions, and escalation precautions.'),
        'prompt' => xl('Create a follow-up plan for the selected patient, including timeframe, monitoring items, patient instructions, and escalation precautions.'),
    ],
    [
        'mode' => 'visit_summary',
        'title' => xl('Visit Summary'),
        'summary' => xl('Concise summary of concerns, plan, and follow-up.'),
        'prompt' => xl('Create a draft summary of this visit for the selected patient. Include key concerns addressed, plan discussed, follow-up instructions, and a patient-friendly explanation.'),
    ],
    [
        'mode' => 'patient_education',
        'title' => xl('Patient Education'),
        'summary' => xl('Patient-friendly explanation of the current plan.'),
        'prompt' => xl('Create a patient-friendly explanation of the current plan using only the documented chart context. Emphasize education, follow-up, and safety reminders.'),
    ],
    [
        'mode' => 'appointment_info',
        'title' => xl('Appointment Info'),
        'summary' => xl('Next appointment, provider, location, and check-in details.'),
        'prompt' => xl('Can you tell me this patient\'s next appointment?'),
    ],
    [
        'mode' => 'patient_contact',
        'title' => xl('Patient Contact'),
        'summary' => xl('Confirm email, phone, and contact workflow details.'),
        'prompt' => xl('Can you help me confirm this patient\'s contact information?'),
    ],
    [
        'mode' => 'send_reminder',
        'title' => xl('Send Reminder'),
        'summary' => xl('Draft a demo reminder using minimum necessary PHI.'),
        'prompt' => xl('Draft an appointment reminder for the selected patient using only scheduling and contact details.'),
    ],
    [
        'mode' => 'front_desk_summary',
        'title' => xl('Front Desk Summary'),
        'summary' => xl('Short administrative summary for check-in and outreach.'),
        'prompt' => xl('Create a short front desk summary for the selected patient with appointment details, contact confirmation points, and check-in instructions only.'),
    ],
];

$roleCatalog = [
    'doctor' => [
        'title' => xl('Doctor'),
        'note' => xl('Clinical support only. No autonomous diagnosis, orders, prescribing, or chart writes.'),
        'quick_actions' => ['differential_diagnosis', 'medication_info', 'clinical_notes', 'treatment_plan', 'billing', 'follow_up'],
        'allowed_modes' => ['general_assistant', 'differential_diagnosis', 'medication_info', 'clinical_notes', 'treatment_plan', 'billing', 'billing_review', 'follow_up', 'visit_summary', 'patient_education'],
    ],
    'nurse' => [
        'title' => xl('Nurse'),
        'note' => xl('Education and follow-up support only. Medication changes require clinician review.'),
        'quick_actions' => ['medication_info', 'clinical_notes', 'follow_up', 'visit_summary', 'patient_education'],
        'allowed_modes' => ['general_assistant', 'medication_info', 'clinical_notes', 'follow_up', 'visit_summary', 'patient_education'],
    ],
    'billing' => [
        'title' => xl('Billing Staff'),
        'note' => xl('Billing review only. No automatic claim submission or definitive coding.'),
        'quick_actions' => ['billing', 'billing_review', 'visit_summary'],
        'allowed_modes' => ['general_assistant', 'billing', 'billing_review', 'visit_summary'],
    ],
    'front_desk' => [
        'title' => xl('Front Desk'),
        'note' => xl('Minimum necessary PHI. Scheduling, contact, and reminder workflows only.'),
        'quick_actions' => ['appointment_info', 'patient_contact', 'send_reminder', 'front_desk_summary'],
        'allowed_modes' => ['general_assistant', 'appointment_info', 'patient_contact', 'send_reminder', 'front_desk_summary'],
    ],
];

$appConfig = [
    'csrfToken' => $csrfToken,
    'apiUrl' => 'copilot_api.php',
    'embedded' => $isEmbedded,
    'greeting' => xla('Hello! I\'m your medical co-pilot. I can help with clinical reasoning support, medication education, documentation, follow-up planning, billing review, and front-desk scheduling workflows. How can I help you today?'),
    'loadingText' => xla('Thinking through the chart context...'),
    'sendingReminderText' => xla('Preparing the reminder workflow...'),
    'emptyPromptMessage' => xla('Enter a prompt before sending.'),
    'apiFailureMessage' => xla('I ran into a temporary problem while drafting that response. Please retry. Draft only. Human review required.'),
    'generalModeTitle' => xla('General clinical support'),
    'inputPlaceholder' => xla('Ask a chart question or enter a general clinical support prompt...'),
    'visitSummaryPrompt' => xla('Create a draft summary of this visit for the selected patient. Include key concerns addressed, plan discussed, follow-up instructions, and a patient-friendly explanation.'),
    'visitSummaryButtonLabel' => xla('View Summary of Visit'),
    'sendReminderEmailButtonLabel' => xla('Send Reminder Email'),
    'footerText' => xla('AI-assisted clinical support • Always verify with clinical judgment'),
    'noPatientSelectedText' => xla('No demo patient selected. Patient-specific answers require selecting a demo patient.'),
    'noPatientSelectedShortText' => xla('No demo patient selected'),
    'selectedPatientPrefix' => xla('Using demo patient:'),
    'copyText' => xla('Copy'),
    'copiedText' => xla('Copied'),
    'likeText' => xla('Like'),
    'dislikeText' => xla('Dislike'),
    'promptHelperLabelSingular' => xla('prompt helper'),
    'promptHelperLabelPlural' => xla('prompt helpers'),
    'noQuickActionsText' => xla('No quick actions available'),
    'defaultRole' => 'doctor',
    'quickActions' => $quickActions,
    'roleCatalog' => $roleCatalog,
];

$cssVersion = file_exists(__DIR__ . '/copilot.css') ? (string) filemtime(__DIR__ . '/copilot.css') : '1';
$guardrailsVersion = file_exists(__DIR__ . '/copilot_guardrails.js') ? (string) filemtime(__DIR__ . '/copilot_guardrails.js') : '1';
?>
<!doctype html>
<html lang="en">
<head>
    <title><?php echo xlt('Medical Co-Pilot'); ?></title>
    <?php Header::setupHeader(); ?>
    <link rel="stylesheet" href="copilot.css?v=<?php echo attr_url($cssVersion); ?>">
</head>
<body class="body_top<?php echo $isEmbedded ? ' copilot-embedded' : ''; ?>">
<main class="copilot-shell<?php echo $isEmbedded ? ' copilot-shell-embedded' : ''; ?>">
    <?php if (!$isEmbedded) { ?>
        <section class="copilot-page-hero">
            <p class="copilot-page-eyebrow"><?php echo xlt('OpenEMR Demo'); ?></p>
            <h1><?php echo xlt('Medical Co-Pilot'); ?></h1>
            <p class="copilot-page-intro">
                <?php echo xlt('Chat with a beta clinical support assistant using seeded demo data. Human review required.'); ?>
            </p>
        </section>
    <?php } ?>

    <section class="copilot-panel">
        <?php if (!$isEmbedded) { ?>
            <header class="copilot-panel-header">
                <div>
                    <p class="copilot-panel-kicker"><?php echo xlt('Medical Co-Pilot'); ?></p>
                    <h2><?php echo xlt('Beta clinical support chat'); ?></h2>
                </div>
                <span class="copilot-panel-badge"><?php echo xlt('Beta'); ?></span>
            </header>
        <?php } ?>

        <div class="copilot-panel-body">
            <?php if ($isEmbedded) { ?>
                <div class="copilot-inline-beta"><?php echo xlt('Beta'); ?></div>
            <?php } ?>

            <section class="copilot-controls" aria-label="<?php echo attr(xl('Demo controls')); ?>">
                <div class="copilot-control-bar">
                    <div class="copilot-control">
                        <label class="copilot-control-label" for="copilot-patient-select"><?php echo xlt('Demo patient'); ?></label>
                        <select id="copilot-patient-select" class="form-control copilot-select copilot-control-select">
                            <option value=""><?php echo xlt('No demo patient selected (general prompts only)'); ?></option>
                            <?php foreach ($demoPatients as $patient) { ?>
                                <?php
                                $label = trim(
                                    $patient['lname'] . ', ' . $patient['fname'] .
                                    (!empty($patient['DOB']) ? ' (' . $patient['DOB'] . ')' : '') .
                                    (!empty($patient['pubpid']) ? ' - ' . $patient['pubpid'] : '')
                                );
                                ?>
                                <option
                                    value="<?php echo attr((string) $patient['pid']); ?>"
                                    data-fname="<?php echo attr((string) $patient['fname']); ?>"
                                    data-lname="<?php echo attr((string) $patient['lname']); ?>"
                                    data-pubpid="<?php echo attr((string) $patient['pubpid']); ?>"
                                >
                                    <?php echo text($label); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="copilot-control">
                        <label class="copilot-control-label" for="copilot-role-select"><?php echo xlt('Staff role'); ?></label>
                        <select id="copilot-role-select" class="form-control copilot-select copilot-control-select">
                            <?php foreach ($roleCatalog as $roleKey => $roleConfig) { ?>
                                <option value="<?php echo attr($roleKey); ?>"<?php echo $roleKey === 'doctor' ? ' selected' : ''; ?>>
                                    <?php echo text($roleConfig['title']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="copilot-control">
                        <label class="copilot-control-label" for="copilot-mode-select"><?php echo xlt('Focus mode'); ?></label>
                        <select id="copilot-mode-select" class="form-control copilot-select copilot-control-select" aria-label="<?php echo attr(xl('Focus mode')); ?>"></select>
                    </div>

                    <div class="copilot-control">
                        <label class="copilot-control-label" for="copilot-quick-action-select"><?php echo xlt('Quick Actions'); ?></label>
                        <select
                            id="copilot-quick-action-select"
                            class="form-control copilot-select copilot-control-select copilot-quick-actions-select"
                            aria-label="<?php echo attr(xl('Quick actions')); ?>"
                        ></select>
                    </div>
                </div>

                <p id="copilot-role-note" class="copilot-role-note"><?php echo text($roleCatalog['doctor']['note']); ?></p>
                <section class="copilot-guardrails-panel" aria-label="<?php echo attr(xl('Guardrails status')); ?>">
                    <div class="copilot-guardrails-header">
                        <span class="copilot-guardrails-title"><?php echo xlt('Guardrails active'); ?></span>
                    </div>
                    <div class="copilot-guardrails-items">
                        <span class="copilot-guardrails-chip copilot-guardrails-chip-strong">
                            <?php echo xlt('Role scope:'); ?> <span id="copilot-guardrails-role-scope"><?php echo text($roleCatalog['doctor']['title']); ?></span>
                        </span>
                        <span class="copilot-guardrails-chip"><?php echo xlt('Prompt injection filter: On'); ?></span>
                        <span class="copilot-guardrails-chip"><?php echo xlt('Draft-only enforcement: On'); ?></span>
                        <span class="copilot-guardrails-chip"><?php echo xlt('PHI minimum necessary: On'); ?></span>
                    </div>
                </section>
            </section>

            <section id="copilot-thread" class="copilot-thread" aria-live="polite"></section>

            <form id="copilot-form" class="copilot-composer">
                <div class="copilot-composer-shell">
                    <label class="copilot-visually-hidden" for="copilot-input"><?php echo xlt('Medical Co-Pilot prompt'); ?></label>
                    <textarea
                        id="copilot-input"
                        class="copilot-input"
                        rows="1"
                        placeholder="<?php echo attr(xl('Ask a chart question or enter a general clinical support prompt...')); ?>"
                    ></textarea>
                    <button type="submit" id="copilot-send" class="copilot-send-button">
                        <?php echo xlt('Send'); ?>
                    </button>
                </div>

                <div class="copilot-composer-footer">
                    <p class="copilot-footer-text"><?php echo xlt('AI-assisted clinical support • Always verify with clinical judgment'); ?></p>
                    <p id="copilot-context-text" class="copilot-context-text"></p>
                </div>
            </form>
        </div>
    </section>
</main>

<script src="copilot_guardrails.js?v=<?php echo attr_url($guardrailsVersion); ?>"></script>
<script>
const copilotConfig = <?php echo json_encode($appConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

const patientSelect = document.getElementById('copilot-patient-select');
const roleSelect = document.getElementById('copilot-role-select');
const roleNote = document.getElementById('copilot-role-note');
const modeSelect = document.getElementById('copilot-mode-select');
const quickActionSelect = document.getElementById('copilot-quick-action-select');
const thread = document.getElementById('copilot-thread');
const form = document.getElementById('copilot-form');
const input = document.getElementById('copilot-input');
const sendButton = document.getElementById('copilot-send');
const contextText = document.getElementById('copilot-context-text');
const guardrailsRoleScope = document.getElementById('copilot-guardrails-role-scope');

const actionCatalog = Object.fromEntries(
    (copilotConfig.quickActions || []).map((action) => [action.mode, action])
);
const roleCatalog = copilotConfig.roleCatalog || {};
const modeCatalog = Object.fromEntries(
    [{ mode: 'general_assistant', title: copilotConfig.generalModeTitle }].concat(copilotConfig.quickActions).map((item) => [
        item.mode,
        item.title
    ])
);

const state = {
    activeMode: 'general_assistant',
    activeRole: copilotConfig.defaultRole || 'doctor',
    loading: false,
    messages: []
};

const copiedStateTimers = new Map();

function createId(prefix) {
    if (window.crypto && typeof window.crypto.randomUUID === 'function') {
        return `${prefix}_${window.crypto.randomUUID()}`;
    }

    return `${prefix}_${Date.now()}_${Math.random().toString(16).slice(2)}`;
}

function getCopilotHostWindow() {
    try {
        if (window.top && window.top !== window && window.top.OPENEMR_AI_COPILOT_URL) {
            return window.top;
        }
    } catch (error) {
    }

    return window;
}

function ensureTelemetryHost(targetWindow) {
    if (!targetWindow) {
        return window;
    }

    targetWindow.OpenEMRCopilotState = targetWindow.OpenEMRCopilotState || {
        role: state.activeRole,
        mode: state.activeMode,
        selectedPatientKey: null
    };

    if (targetWindow.CopilotTelemetry && targetWindow.CopilotMetrics && typeof targetWindow.printCopilotMetrics === 'function') {
        return targetWindow;
    }

    const allowedKeys = new Set([
        'requestId',
        'responseId',
        'role',
        'previousRole',
        'newRole',
        'mode',
        'selectedPatientKey',
        'visibleQuickActions',
        'messageLength',
        'responseLength',
        'latencyMs',
        'success',
        'fallbackUsed',
        'fallbackReason',
        'restrictedByRole',
        'restrictionType',
        'copied',
        'feedback',
        'errorCategory',
        'contextScope',
        'hasChatHistory',
        'startedAt',
        'actionType',
        'selectedRole',
        'selectedMode',
        'allowed',
        'blockedReason',
        'riskLevel',
        'policyTags',
        'responseCharacterCount'
    ]);

    const metrics = targetWindow.CopilotMetrics || {
        sessionId: createId('session'),
        openedCount: 0,
        generationsStarted: 0,
        generationsSucceeded: 0,
        generationsFailed: 0,
        fallbackUsedCount: 0,
        copiedCount: 0,
        likedCount: 0,
        dislikedCount: 0,
        restrictedActionCount: 0,
        latencySamples: []
    };

    function sanitizePayload(payload) {
        const safePayload = {};
        Object.keys(payload || {}).forEach((key) => {
            if (!allowedKeys.has(key)) {
                return;
            }

            const value = payload[key];
            if (value === undefined || value === null || value === '') {
                return;
            }

            safePayload[key] = value;
        });

        return safePayload;
    }

    function updateMetrics(eventName, payload) {
        if (eventName === 'copilot_open') {
            metrics.openedCount += 1;
        }
        if (eventName === 'copilot_generation_started') {
            metrics.generationsStarted += 1;
        }
        if (eventName === 'copilot_generation_succeeded') {
            metrics.generationsSucceeded += 1;
            if (typeof payload.latencyMs === 'number') {
                metrics.latencySamples.push(payload.latencyMs);
            }
        }
        if (eventName === 'copilot_generation_failed') {
            metrics.generationsFailed += 1;
            if (typeof payload.latencyMs === 'number') {
                metrics.latencySamples.push(payload.latencyMs);
            }
        }
        if (eventName === 'copilot_fallback_used') {
            metrics.fallbackUsedCount += 1;
        }
        if (eventName === 'copilot_output_copied') {
            metrics.copiedCount += 1;
        }
        if (eventName === 'copilot_output_feedback') {
            if (payload.feedback === 'like') {
                metrics.likedCount += 1;
            }
            if (payload.feedback === 'dislike') {
                metrics.dislikedCount += 1;
            }
        }
        if (eventName === 'copilot_restricted_action') {
            metrics.restrictedActionCount += 1;
        }
    }

    function averageLatency() {
        if (metrics.latencySamples.length === 0) {
            return 0;
        }

        return Math.round(
            metrics.latencySamples.reduce((sum, value) => sum + value, 0) / metrics.latencySamples.length
        );
    }

    targetWindow.CopilotMetrics = metrics;
    targetWindow.printCopilotMetrics = function () {
        console.table([
            {
                sessionId: metrics.sessionId,
                openedCount: metrics.openedCount,
                generationsStarted: metrics.generationsStarted,
                generationsSucceeded: metrics.generationsSucceeded,
                generationsFailed: metrics.generationsFailed,
                fallbackUsed: metrics.fallbackUsedCount,
                copied: metrics.copiedCount,
                liked: metrics.likedCount,
                disliked: metrics.dislikedCount,
                restrictedActions: metrics.restrictedActionCount,
                averageLatencyMs: averageLatency()
            }
        ]);
    };

    targetWindow.CopilotTelemetry = {
        sessionId: metrics.sessionId,
        log(eventName, payload = {}) {
            const safePayload = sanitizePayload(payload);
            const event = {
                source: 'medical-copilot',
                event: eventName,
                timestamp: new Date().toISOString(),
                sessionId: metrics.sessionId,
                ...safePayload
            };

            updateMetrics(eventName, safePayload);

            const label = `[Medical Co-Pilot Audit] ${eventName}`;
            const warnEvents = new Set([
                'copilot_generation_failed',
                'copilot_fallback_used',
                'copilot_restricted_action'
            ]);
            const groupedEvents = new Set([
                'copilot_generation_started',
                'copilot_generation_succeeded',
                'copilot_generation_failed',
                'copilot_fallback_used',
                'copilot_restricted_action'
            ]);
            const method = warnEvents.has(eventName) ? 'warn' : 'info';

            if (groupedEvents.has(eventName) && typeof console.groupCollapsed === 'function') {
                console.groupCollapsed(label);
                console[method](event);
                console.groupEnd();
            } else {
                console[method](label, event);
            }

            return event;
        }
    };

    return targetWindow;
}

const copilotHostWindow = ensureTelemetryHost(getCopilotHostWindow());
const CopilotTelemetry = copilotHostWindow.CopilotTelemetry;
window.CopilotTelemetry = CopilotTelemetry;
window.CopilotMetrics = copilotHostWindow.CopilotMetrics;
window.printCopilotMetrics = copilotHostWindow.printCopilotMetrics;

function selectedPatientOptionByValue(value) {
    return Array.from(patientSelect.options).find((option) => option.value === String(value)) || null;
}

function selectedPatientKeyForValue(value) {
    const option = selectedPatientOptionByValue(value);
    return option ? option.dataset.pubpid || null : null;
}

function currentSelectedPatientKey() {
    return selectedPatientKeyForValue(patientSelect.value);
}

function currentVisibleQuickActions() {
    return (currentRoleConfig().quick_actions || []).slice();
}

function contextScopeFor(role, patientId) {
    if (!patientId) {
        return 'general_prompt';
    }

    if (role === 'nurse') {
        return 'nursing_limited';
    }
    if (role === 'billing') {
        return 'billing_limited';
    }
    if (role === 'front_desk') {
        return 'front_desk_minimum_phi';
    }

    return 'full_clinical';
}

function syncTopLevelCopilotState() {
    copilotHostWindow.OpenEMRCopilotState = {
        role: state.activeRole,
        mode: state.activeMode,
        selectedPatientKey: currentSelectedPatientKey()
    };
}

function createMessage(role, content, options = {}) {
    return {
        id: options.id || `${Date.now()}-${Math.random().toString(36).slice(2)}`,
        role,
        content,
        timestamp: options.timestamp || formatTimestamp(new Date()),
        mode: options.mode || 'general_assistant',
        staffRole: options.staffRole || state.activeRole,
        patientId: options.patientId || (patientSelect ? patientSelect.value || '' : ''),
        selectedPatientKey: options.selectedPatientKey || selectedPatientKeyForValue(options.patientId || patientSelect.value || ''),
        requestId: options.requestId || options.traceId || '',
        responseId: options.responseId || (role === 'assistant' && !options.isLoading ? createId('response') : ''),
        sources: options.sources || [],
        sections: options.sections || [],
        tags: options.tags || [],
        safety: options.safety || '',
        traceId: options.traceId || options.requestId || '',
        meta: options.meta || {},
        guardrails: options.guardrails || null,
        feedback: options.feedback || '',
        copied: Boolean(options.copied),
        isLoading: Boolean(options.isLoading),
        showResponseActions: options.showResponseActions !== false,
        metadataLogged: Boolean(options.metadataLogged)
    };
}

function formatTimestamp(date) {
    return date.toLocaleTimeString([], {
        hour: 'numeric',
        minute: '2-digit'
    });
}

function escapeModeTitle(mode) {
    return modeCatalog[mode] || copilotConfig.generalModeTitle;
}

function currentRoleConfig() {
    return roleCatalog[state.activeRole] || roleCatalog[copilotConfig.defaultRole] || { quick_actions: [], allowed_modes: [] };
}

function availableModesForRole(role = state.activeRole) {
    const allowedModes = roleCatalog[role]?.allowed_modes || [];
    return ['general_assistant'].concat(allowedModes).filter((mode, index, items) => items.indexOf(mode) === index);
}

function syncControlState() {
    if (modeSelect) {
        modeSelect.value = state.activeMode;
    }

    if (guardrailsRoleScope) {
        guardrailsRoleScope.textContent = roleCatalog[state.activeRole]?.title || 'Doctor';
    }
}

const defaultGuardrailSafetyNote = 'Draft only. Human review required. This does not replace clinical judgment or a final medical decision.';

function evaluateGuardrails(payload) {
    if (!window.OpenEMRCopilotGuardrails || typeof window.OpenEMRCopilotGuardrails.evaluate !== 'function') {
        return {
            allowed: true,
            finalResponse: payload.draftResponse || '',
            blockedReason: '',
            riskLevel: 'low',
            policyTags: [],
            auditSummary: {},
            finalSections: Array.isArray(payload.sections) ? payload.sections : [],
            finalSafety: payload.safetyText || defaultGuardrailSafetyNote,
            ui: {
                blocked: false,
                title: 'Guardrails checked',
                statusLabel: 'Guardrails checked · Role-safe · Draft-only',
                displayReason: 'Role scope, prompt safety, and draft-only rules passed.',
                alternative: '',
                roleLabel: roleCatalog[payload.role]?.title || 'Doctor',
                checks: ['Role scope', 'Prompt injection filter', 'Draft-only enforcement', 'PHI minimum necessary'],
                riskLevel: 'low',
                policyTags: []
            }
        };
    }

    return window.OpenEMRCopilotGuardrails.evaluate(payload);
}

function logGuardrailsEvaluation(result, context = {}) {
    if (!CopilotTelemetry) {
        return;
    }

    CopilotTelemetry.log('copilot_guardrails_evaluated', {
        requestId: context.requestId || null,
        role: context.role || null,
        mode: context.mode || null,
        selectedRole: context.role || null,
        selectedMode: context.mode || null,
        selectedPatientKey: context.selectedPatientKey || null,
        allowed: Boolean(result.allowed),
        blockedReason: result.blockedReason || '',
        riskLevel: result.riskLevel || 'low',
        policyTags: Array.isArray(result.policyTags) ? result.policyTags.slice(0, 6) : [],
        responseCharacterCount: (result.finalResponse || '').length
    });
}

function roleAllowsMode(role, mode) {
    if (mode === 'general_assistant') {
        return true;
    }

    return (roleCatalog[role]?.allowed_modes || []).includes(mode);
}

function resolvePatientIdFromPrompt(prompt) {
    if (patientSelect.value) {
        return patientSelect.value;
    }

    const normalizedPrompt = prompt.toLowerCase();
    const options = Array.from(patientSelect.options).slice(1);
    for (const option of options) {
        const firstName = (option.dataset.fname || '').toLowerCase();
        const lastName = (option.dataset.lname || '').toLowerCase();
        const fullName = [firstName, lastName].filter(Boolean).join(' ');
        if (
            (firstName && normalizedPrompt.includes(firstName)) ||
            (lastName && normalizedPrompt.includes(lastName)) ||
            (fullName && normalizedPrompt.includes(fullName))
        ) {
            return option.value;
        }
    }

    return '';
}

function updatePatientSelectionFromPrompt(prompt) {
    const resolvedPatientId = resolvePatientIdFromPrompt(prompt);
    if (!patientSelect.value && resolvedPatientId) {
        patientSelect.value = resolvedPatientId;
        updateContextText();
        if (CopilotTelemetry) {
            CopilotTelemetry.log('copilot_patient_selected', {
                selectedPatientKey: selectedPatientKeyForValue(resolvedPatientId),
                role: state.activeRole
            });
        }
    }

    return patientSelect.value || resolvedPatientId || null;
}

function availableQuickActions() {
    return (currentRoleConfig().quick_actions || [])
        .map((mode) => actionCatalog[mode] || null)
        .filter(Boolean);
}

function renderModeOptions() {
    modeSelect.innerHTML = '';

    availableModesForRole().forEach((mode) => {
        const option = document.createElement('option');
        option.value = mode;
        option.textContent = escapeModeTitle(mode);
        modeSelect.appendChild(option);
    });
}

function renderQuickActions() {
    quickActionSelect.innerHTML = '';

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.selected = true;
    placeholder.textContent = copilotConfig.quickActionPlaceholder || 'Select a quick action';
    quickActionSelect.appendChild(placeholder);

    availableQuickActions().forEach((action) => {
        const option = document.createElement('option');
        option.value = action.mode;
        option.textContent = action.title;
        quickActionSelect.appendChild(option);
    });

    quickActionSelect.disabled = availableQuickActions().length === 0;
    quickActionSelect.value = '';
}

function updateModeSelection(mode, options = {}) {
    const previousMode = state.activeMode;
    let nextMode = mode || 'general_assistant';
    if (!roleAllowsMode(state.activeRole, nextMode)) {
        nextMode = 'general_assistant';
    }

    state.activeMode = nextMode;
    syncTopLevelCopilotState();
    syncControlState();
    quickActionSelect.dataset.activeMode = state.activeMode;

    if (options.emitTelemetry && previousMode !== nextMode && CopilotTelemetry) {
        CopilotTelemetry.log('copilot_mode_selected', {
            mode: nextMode,
            role: state.activeRole,
            selectedPatientKey: currentSelectedPatientKey()
        });
    }
}

function updateRoleSelection(role, options = {}) {
    const previousRole = state.activeRole;
    const resolvedRole = roleCatalog[role] ? role : (copilotConfig.defaultRole || 'doctor');
    state.activeRole = resolvedRole;
    roleSelect.value = resolvedRole;
    roleNote.textContent = roleCatalog[resolvedRole]?.note || '';
    roleNote.classList.toggle('is-minimum-phi', resolvedRole === 'front_desk');
    renderModeOptions();
    renderQuickActions();
    updateModeSelection(state.activeMode);
    updateContextText();

    if (options.emitTelemetry && previousRole !== resolvedRole && CopilotTelemetry) {
        CopilotTelemetry.log('copilot_role_selected', {
            previousRole,
            newRole: resolvedRole,
            visibleQuickActions: currentVisibleQuickActions()
        });
    }
}

function updateContextText() {
    const roleTitle = roleCatalog[state.activeRole]?.title || '';
    const selectedOption = patientSelect.options[patientSelect.selectedIndex];
    if (!selectedOption || !patientSelect.value) {
        contextText.textContent = [copilotConfig.noPatientSelectedText, roleTitle].filter(Boolean).join(' • ');
        syncTopLevelCopilotState();
        return;
    }

    contextText.textContent = `${copilotConfig.selectedPatientPrefix} ${selectedOption.textContent}${roleTitle ? ` • ${roleTitle}` : ''}`;
    syncTopLevelCopilotState();
}

function resizeInput() {
    input.style.height = 'auto';
    input.style.height = `${Math.min(input.scrollHeight, 140)}px`;
}

function updateSendState() {
    const hasMessage = input.value.trim().length > 0;
    sendButton.disabled = state.loading || !hasMessage;
    input.disabled = state.loading;
}

function scrollThreadToBottom() {
    thread.scrollTop = thread.scrollHeight;
}

const svgNamespace = 'http://www.w3.org/2000/svg';

function createResponseIcon(name) {
    const svg = document.createElementNS(svgNamespace, 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', '1.8');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');
    svg.classList.add('copilot-message-action-icon');
    svg.setAttribute('aria-hidden', 'true');

    const shapesByIcon = {
        like: [
            ['path', { d: 'M7 10v10' }],
            ['path', { d: 'M14 5.5 11 10h7.2c1.1 0 1.9 1 1.7 2.1l-.9 6a2 2 0 0 1-2 1.7H7a2 2 0 0 1-2-2v-7.6a2 2 0 0 1 .6-1.4l4.8-4.8a1 1 0 0 1 1.7.8l.2 1.8c.1.5 0 1.1-.3 1.6Z' }]
        ],
        dislike: [
            ['path', { d: 'M7 14V4' }],
            ['path', { d: 'M14 18.5 11 14h7.2c1.1 0 1.9-1 1.7-2.1l-.9-6a2 2 0 0 0-2-1.7H7a2 2 0 0 0-2 2v7.6c0 .5.2 1 .6 1.4l4.8 4.8a1 1 0 0 0 1.7-.8l.2-1.8c.1-.5 0-1.1-.3-1.6Z' }]
        ],
        copy: [
            ['rect', { x: '9', y: '9', width: '10', height: '10', rx: '2' }],
            ['path', { d: 'M7 15H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v1' }]
        ]
    };

    (shapesByIcon[name] || []).forEach(([tagName, attributes]) => {
        const shape = document.createElementNS(svgNamespace, tagName);
        Object.entries(attributes).forEach(([key, value]) => {
            shape.setAttribute(key, value);
        });
        svg.appendChild(shape);
    });

    return svg;
}

function shouldRenderResponseActions(message) {
    return message.role === 'assistant' && !message.isLoading && message.showResponseActions !== false;
}

function logResponseMetadataIfNeeded(message) {
    if (!shouldRenderResponseActions(message) || message.metadataLogged) {
        return;
    }

    const safeSources = Array.isArray(message.sources) ? message.sources.slice(0, 8) : [];
    const safeContextTags = Array.isArray(message.tags) ? Array.from(new Set(message.tags)).slice(0, 5) : [];
    const safeSafetyLabels = message.safety ? [message.safety] : [];
    const safeRole = message.staffRole || state.activeRole;
    const safeMode = message.mode || 'general_assistant';
    const safePatientKey = message.selectedPatientKey || currentSelectedPatientKey() || null;
    const safeMeta = message.meta && typeof message.meta === 'object' ? {
        contextScope: message.meta.context_scope || null,
        fallbackUsed: Boolean(message.meta.fallback_used),
        restrictedByRole: Boolean(message.meta.restricted_by_role)
    } : {};

    if (typeof console.groupCollapsed === 'function') {
        console.groupCollapsed('[OpenEMR Copilot] Response metadata');
        console.info('messageId', message.id);
        console.info('requestId', message.requestId || null);
        console.info('responseId', message.responseId || null);
        console.info('role', safeRole);
        console.info('focusMode', safeMode);
        console.info('demoPatientKey', safePatientKey);
        console.info('sources', safeSources);
        console.info('contextTags', safeContextTags);
        console.info('safetyLabels', safeSafetyLabels);
        console.info('meta', safeMeta);
        console.groupEnd();
    } else {
        console.info('[OpenEMR Copilot] Response metadata', {
            messageId: message.id,
            requestId: message.requestId || null,
            responseId: message.responseId || null,
            role: safeRole,
            focusMode: safeMode,
            demoPatientKey: safePatientKey,
            sources: safeSources,
            contextTags: safeContextTags,
            safetyLabels: safeSafetyLabels,
            meta: safeMeta
        });
    }

    message.metadataLogged = true;
}

function createMessageTimeElement(message) {
    const time = document.createElement('div');
    time.className = `copilot-message-time copilot-message-time-${message.role}`;
    time.textContent = message.timestamp;
    return time;
}

function createIconActionButton(options) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = options.className;
    button.setAttribute('aria-label', options.label);
    button.title = options.label;

    if (options.pressed !== undefined) {
        button.setAttribute('aria-pressed', options.pressed ? 'true' : 'false');
    }

    if (options.copiedLabel) {
        button.dataset.copiedLabel = options.copiedLabel;
    }

    button.appendChild(createResponseIcon(options.icon));
    button.addEventListener('click', options.onClick);
    return button;
}

function buildGuardrailBanner(guardrails) {
    if (!guardrails || !guardrails.ui || !guardrails.ui.blocked) {
        return null;
    }

    const banner = document.createElement('section');
    banner.className = 'copilot-guardrail-banner';

    const title = document.createElement('h3');
    title.className = 'copilot-guardrail-banner-title';
    title.textContent = guardrails.ui.title || 'Guardrail blocked this request';
    banner.appendChild(title);

    if (guardrails.ui.displayReason) {
        const reason = document.createElement('p');
        reason.className = 'copilot-guardrail-banner-copy';
        reason.textContent = guardrails.ui.displayReason;
        banner.appendChild(reason);
    }

    if (guardrails.ui.alternative) {
        const alternative = document.createElement('p');
        alternative.className = 'copilot-guardrail-banner-alt';
        alternative.textContent = guardrails.ui.alternative;
        banner.appendChild(alternative);
    }

    return banner;
}

function buildGuardrailStatus(message) {
    if (!shouldRenderResponseActions(message) || !message.guardrails || !message.guardrails.ui) {
        return null;
    }

    const status = document.createElement('div');
    status.className = `copilot-guardrail-status ${message.guardrails.ui.blocked ? 'is-blocked' : 'is-allowed'}`.trim();
    status.textContent = message.guardrails.ui.statusLabel || 'Guardrails checked · Role-safe · Draft-only';
    return status;
}

function buildTypingBubble() {
    const typing = document.createElement('div');
    typing.className = 'copilot-typing';
    for (let index = 0; index < 3; index++) {
        const dot = document.createElement('span');
        typing.appendChild(dot);
    }
    return typing;
}

function toneClassName(tone) {
    if (tone === 'red') {
        return 'copilot-section-red';
    }
    if (tone === 'yellow') {
        return 'copilot-section-yellow';
    }
    return '';
}

function itemToneClassName(tone) {
    if (tone === 'red') {
        return 'copilot-red-flag';
    }
    if (tone === 'yellow') {
        return 'copilot-key-gap';
    }
    return '';
}

function appendBubbleContent(bubble, message) {
    const hasStructuredSections = message.role === 'assistant' && Array.isArray(message.sections) && message.sections.length > 0;
    const guardrailBanner = message.role === 'assistant' ? buildGuardrailBanner(message.guardrails) : null;
    if (!hasStructuredSections && !guardrailBanner) {
        bubble.textContent = message.content;
        return;
    }

    const body = document.createElement('div');
    body.className = 'copilot-bubble-body';

    if (guardrailBanner) {
        body.appendChild(guardrailBanner);
    }

    if (message.content) {
        const intro = document.createElement('p');
        intro.className = 'copilot-message-intro';
        intro.textContent = message.content;
        body.appendChild(intro);
    }

    const sections = document.createElement('div');
    sections.className = 'copilot-sections';

    message.sections.forEach((section) => {
        if (!section || typeof section !== 'object' || !section.title || !Array.isArray(section.items) || section.items.length === 0) {
            return;
        }

        const block = document.createElement('section');
        block.className = `copilot-section-block ${toneClassName(section.tone || 'neutral')}`.trim();

        const title = document.createElement('h3');
        title.className = 'copilot-section-title';
        title.textContent = section.title;
        block.appendChild(title);

        const list = document.createElement('ul');
        list.className = 'copilot-section-list';

        section.items.forEach((item) => {
            if (!item) {
                return;
            }

            const listItem = document.createElement('li');
            listItem.className = `copilot-section-item ${itemToneClassName(section.tone || 'neutral')}`.trim();
            listItem.textContent = item;
            list.appendChild(listItem);
        });

        block.appendChild(list);
        sections.appendChild(block);
    });

    if (sections.childElementCount > 0) {
        body.appendChild(sections);
    }

    bubble.appendChild(body);
}

function renderMessages(shouldScrollToBottom = false) {
    thread.innerHTML = '';

    state.messages.forEach((message) => {
        const row = document.createElement('div');
        row.className = `copilot-message copilot-message-${message.role}`;

        if (message.role === 'assistant') {
            const avatar = document.createElement('span');
            avatar.className = 'copilot-avatar';
            avatar.setAttribute('aria-hidden', 'true');
            avatar.textContent = '✦';
            row.appendChild(avatar);
        }

        const stack = document.createElement('div');
        stack.className = 'copilot-message-stack';

        const bubble = document.createElement('div');
        bubble.className = `copilot-bubble copilot-bubble-${message.role}`;

        if (message.isLoading) {
            bubble.classList.add('copilot-bubble-loading');
            bubble.appendChild(buildTypingBubble());
        } else {
            appendBubbleContent(bubble, message);
            logResponseMetadataIfNeeded(message);
        }

        stack.appendChild(bubble);

        const metadata = buildMessageMetaRow(message);
        if (metadata) {
            stack.appendChild(metadata);
        }

        if (!message.isLoading && message.role === 'assistant' && message.safety) {
            const safety = document.createElement('div');
            safety.className = 'copilot-message-safety';
            safety.textContent = message.safety;
            stack.appendChild(safety);
        }

        const guardrailStatus = buildGuardrailStatus(message);
        if (guardrailStatus) {
            stack.appendChild(guardrailStatus);
        }

        const assistantActions = buildAssistantActionRow(message);
        if (assistantActions) {
            stack.appendChild(assistantActions);
        }

        row.appendChild(stack);
        thread.appendChild(row);
    });

    if (shouldScrollToBottom) {
        scrollThreadToBottom();
    }
}

function addAssistantMessage(content, options = {}) {
    const message = createMessage('assistant', content, options);
    state.messages.push(message);
    renderMessages(true);
    return message;
}

function clearLoadingMessage() {
    const lastMessage = state.messages[state.messages.length - 1];
    if (lastMessage && lastMessage.isLoading) {
        state.messages.pop();
    }
}

function buildHistoryEntryContent(message) {
    const parts = [];
    if (message.content) {
        parts.push(message.content);
    }

    if (Array.isArray(message.sections) && message.sections.length > 0) {
        message.sections.forEach((section) => {
            if (!section || typeof section !== 'object' || !section.title || !Array.isArray(section.items) || section.items.length === 0) {
                return;
            }

            parts.push(`${section.title}: ${section.items.join('; ')}`);
        });
    }

    return parts.join('\n');
}

function buildHistoryPayload() {
    return state.messages
        .filter((message) => !message.isLoading)
        .slice(-8)
        .map((message) => ({
            role: message.role,
            content: buildHistoryEntryContent(message)
        }));
}

function updateMessageState(messageId, patch) {
    const messageIndex = state.messages.findIndex((message) => message.id === messageId);
    if (messageIndex === -1) {
        return;
    }

    state.messages[messageIndex] = {
        ...state.messages[messageIndex],
        ...patch
    };
    renderMessages(false);
}

function getAssistantMessagePlainText(message) {
    const lines = [];

    if (message.content) {
        lines.push(message.content);
    }

    if (Array.isArray(message.sections)) {
        message.sections.forEach((section) => {
            if (!section || typeof section !== 'object' || !section.title || !Array.isArray(section.items) || section.items.length === 0) {
                return;
            }

            lines.push('');
            lines.push(section.title);
            section.items.forEach((item) => {
                lines.push(`- ${item}`);
            });
        });
    }

    if (message.safety) {
        lines.push('');
        lines.push(`Safety: ${message.safety}`);
    }

    return lines.join('\n').trim();
}

async function copyTextToClipboard(value) {
    if (navigator.clipboard && typeof navigator.clipboard.writeText === 'function') {
        await navigator.clipboard.writeText(value);
        return;
    }

    const helper = document.createElement('textarea');
    helper.value = value;
    helper.setAttribute('readonly', 'readonly');
    helper.style.position = 'absolute';
    helper.style.left = '-9999px';
    document.body.appendChild(helper);
    helper.select();
    document.execCommand('copy');
    document.body.removeChild(helper);
}

async function copyAssistantMessage(message) {
    const fullText = getAssistantMessagePlainText(message);
    if (!fullText) {
        return;
    }

    try {
        await copyTextToClipboard(fullText);
        updateMessageState(message.id, { copied: true });
        window.clearTimeout(copiedStateTimers.get(message.id));
        copiedStateTimers.set(message.id, window.setTimeout(() => {
            updateMessageState(message.id, { copied: false });
            copiedStateTimers.delete(message.id);
        }, 1600));
        if (CopilotTelemetry) {
            CopilotTelemetry.log('copilot_output_copied', {
                responseId: message.responseId || null,
                requestId: message.requestId || null,
                role: message.staffRole || state.activeRole,
                mode: message.mode || 'general_assistant',
                selectedPatientKey: message.selectedPatientKey || currentSelectedPatientKey(),
                responseLength: fullText.length,
                copied: true
            });
        }
    } catch (error) {
    }
}

function updateFeedback(message, nextFeedback) {
    if (message.feedback === nextFeedback) {
        return;
    }

    updateMessageState(message.id, { feedback: nextFeedback });
    if (CopilotTelemetry) {
        CopilotTelemetry.log('copilot_output_feedback', {
            responseId: message.responseId || null,
            requestId: message.requestId || null,
            role: message.staffRole || state.activeRole,
            mode: message.mode || 'general_assistant',
            selectedPatientKey: message.selectedPatientKey || currentSelectedPatientKey(),
            feedback: nextFeedback
        });
    }
}

function buildMessageMetaRow(message) {
    if (message.isLoading) {
        return null;
    }

    const meta = document.createElement('div');
    meta.className = `copilot-message-meta copilot-message-meta-${message.role}`;

    const left = document.createElement('div');
    left.className = 'copilot-message-meta-left';
    left.appendChild(createMessageTimeElement(message));

    if (!shouldRenderResponseActions(message)) {
        meta.appendChild(left);
        return meta;
    }

    const feedback = document.createElement('div');
    feedback.className = 'copilot-response-controls';

    const likeButton = createIconActionButton({
        className: `copilot-response-control copilot-message-action copilot-feedback-button ${message.feedback === 'like' ? 'is-selected copilot-message-action-active' : ''}`.trim(),
        label: 'Like response',
        icon: 'like',
        pressed: message.feedback === 'like',
        onClick: () => updateFeedback(message, 'like')
    });

    const dislikeButton = createIconActionButton({
        className: `copilot-response-control copilot-message-action copilot-feedback-button ${message.feedback === 'dislike' ? 'is-selected copilot-message-action-active' : ''}`.trim(),
        label: 'Dislike response',
        icon: 'dislike',
        pressed: message.feedback === 'dislike',
        onClick: () => updateFeedback(message, 'dislike')
    });

    const copyButton = createIconActionButton({
        className: `copilot-response-control copilot-message-action copilot-copy-button ${message.copied ? 'is-copied copilot-message-action-active' : ''}`.trim(),
        label: message.copied ? copilotConfig.copiedText : 'Copy response',
        copiedLabel: copilotConfig.copiedText,
        icon: 'copy',
        onClick: () => copyAssistantMessage(message)
    });

    feedback.append(likeButton, dislikeButton);
    left.appendChild(feedback);
    meta.append(left, copyButton);
    return meta;
}

function buildAssistantActionRow(message) {
    if (!shouldRenderResponseActions(message)) {
        return null;
    }

    const actions = document.createElement('div');
    actions.className = 'copilot-message-actions';

    if (message.mode === 'treatment_plan') {
        const summaryButton = document.createElement('button');
        summaryButton.type = 'button';
        summaryButton.className = 'copilot-summary-button';
        summaryButton.textContent = copilotConfig.visitSummaryButtonLabel;
        summaryButton.disabled = state.loading;
        summaryButton.addEventListener('click', () => {
            requestAssistantResponse(copilotConfig.visitSummaryPrompt, {
                includeUserMessage: false,
                modeOverride: 'visit_summary',
                roleOverride: message.staffRole || state.activeRole,
                patientIdOverride: message.patientId || patientSelect.value || null
            });
        });
        actions.appendChild(summaryButton);
    }

    if (message.mode === 'send_reminder' && message.staffRole === 'front_desk') {
        const reminderButton = document.createElement('button');
        reminderButton.type = 'button';
        reminderButton.className = 'copilot-summary-button';
        reminderButton.textContent = copilotConfig.sendReminderEmailButtonLabel;
        reminderButton.disabled = state.loading;
        reminderButton.addEventListener('click', () => {
            requestReminderEmail(message.staffRole || 'front_desk', message.patientId || patientSelect.value || null);
        });
        actions.appendChild(reminderButton);
    }

    return actions.childElementCount > 0 ? actions : null;
}

function inferPromptMode(prompt) {
    const value = prompt.toLowerCase();
    if (/(appointment|scheduled|provider|check-in|check in|location)/.test(value)) {
        if (/(reminder|email)/.test(value)) {
            return 'send_reminder';
        }
        if (/(contact|phone|email address|confirm)/.test(value)) {
            return 'patient_contact';
        }
        if (/(summary)/.test(value)) {
            return 'front_desk_summary';
        }
        return 'appointment_info';
    }
    if (/(contact information|contact info|phone number|email address)/.test(value)) {
        return 'patient_contact';
    }
    if (/(reminder|send this patient a reminder|draft an appointment reminder)/.test(value)) {
        return 'send_reminder';
    }
    if (/(claim|rejection|denial|resubmi|missing diagnosis link)/.test(value)) {
        return 'billing_review';
    }
    if (/(billing|coding|cpt|icd|payer|documentation needed before billing|payment due|health insurance|insurance on file|insurance)/.test(value)) {
        return 'billing';
    }
    if (/(visit summary|summary of visit)/.test(value)) {
        return 'visit_summary';
    }
    if (/(patient-friendly|patient friendly|education)/.test(value)) {
        return 'patient_education';
    }
    if (/(medication|drug|interaction|counsel)/.test(value)) {
        return 'medication_info';
    }
    if (/(treatment plan|let me see .* treatment plan|give me .* treatment plan)/.test(value)) {
        return 'treatment_plan';
    }
    if (/(follow-up|follow up|monitoring items|escalation precautions|care coordination)/.test(value)) {
        return 'follow_up';
    }
    if (/(soap|note|documentation|chart for a doctor|30 seconds|30-second|summary)/.test(value)) {
        return 'clinical_notes';
    }
    if (/(differential|diagnosis|causing|red flag|miss|questions should the clinician ask)/.test(value)) {
        return 'differential_diagnosis';
    }

    return 'general_assistant';
}

function resolveModeForPrompt(prompt, options = {}) {
    if (options.modeOverride) {
        return options.modeOverride;
    }

    const inferredMode = inferPromptMode(prompt);
    if (inferredMode !== 'general_assistant') {
        return inferredMode;
    }

    if (state.activeMode && state.activeMode !== 'general_assistant') {
        return state.activeMode;
    }

    return 'general_assistant';
}

async function requestAssistantResponse(prompt, options = {}) {
    const trimmedPrompt = typeof prompt === 'string' ? prompt.trim() : '';
    if (!trimmedPrompt || state.loading) {
        return;
    }

    if (window.top && typeof window.top.restoreSession === 'function') {
        window.top.restoreSession();
    }

    const resolvedRole = options.roleOverride || state.activeRole;
    const resolvedMode = resolveModeForPrompt(trimmedPrompt, options);
    const resolvedPatientId = options.patientIdOverride ?? updatePatientSelectionFromPrompt(trimmedPrompt);
    const requestId = createId('request');
    const selectedPatientKey = selectedPatientKeyForValue(resolvedPatientId || '');
    const contextScope = contextScopeFor(resolvedRole, resolvedPatientId);
    const startedAt = new Date().toISOString();
    const startedPerf = window.performance && typeof window.performance.now === 'function'
        ? window.performance.now()
        : Date.now();
    const historyPayload = buildHistoryPayload();
    const preflightGuardrails = evaluateGuardrails({
        role: resolvedRole,
        mode: resolvedMode,
        prompt: trimmedPrompt,
        draftResponse: '',
        sections: [],
        safetyText: '',
        metadata: {
            selectedPatientKey,
            contextScope
        }
    });

    if (!preflightGuardrails.allowed) {
        updateModeSelection(resolvedMode);
        if (options.includeUserMessage !== false) {
            state.messages.push(createMessage('user', trimmedPrompt, {
                staffRole: resolvedRole,
                patientId: resolvedPatientId,
                selectedPatientKey,
                requestId
            }));
        }

        logGuardrailsEvaluation(preflightGuardrails, {
            requestId,
            role: resolvedRole,
            mode: resolvedMode,
            selectedPatientKey
        });
        if (CopilotTelemetry) {
            CopilotTelemetry.log('copilot_restricted_action', {
                requestId,
                role: resolvedRole,
                mode: resolvedMode,
                selectedPatientKey,
                restrictedByRole: true,
                restrictionType: preflightGuardrails.blockedReason || 'guardrails_block'
            });
        }

        addAssistantMessage(preflightGuardrails.finalResponse || copilotConfig.apiFailureMessage, {
            mode: resolvedMode,
            staffRole: resolvedRole,
            patientId: resolvedPatientId,
            selectedPatientKey,
            tags: preflightGuardrails.policyTags || [],
            safety: preflightGuardrails.finalSafety || defaultGuardrailSafetyNote,
            guardrails: preflightGuardrails,
            traceId: requestId,
            requestId,
            meta: {
                context_scope: contextScope,
                restricted_by_role: true,
                restriction_type: preflightGuardrails.blockedReason || 'guardrails_block'
            }
        });
        return;
    }

    const loadingMessage = createMessage('assistant', copilotConfig.loadingText, {
        isLoading: true,
        staffRole: resolvedRole,
        patientId: resolvedPatientId,
        selectedPatientKey,
        traceId: requestId,
        requestId
    });

    state.loading = true;
    if (options.includeUserMessage !== false) {
        state.messages.push(createMessage('user', trimmedPrompt, {
            staffRole: resolvedRole,
            patientId: resolvedPatientId,
            selectedPatientKey,
            requestId
        }), loadingMessage);
    } else {
        state.messages.push(loadingMessage);
    }

    updateSendState();
    renderMessages(true);

    if (CopilotTelemetry) {
        CopilotTelemetry.log('copilot_context_loaded', {
            requestId,
            role: resolvedRole,
            mode: resolvedMode,
            selectedPatientKey,
            contextScope
        });
        CopilotTelemetry.log('copilot_generation_started', {
            requestId,
            role: resolvedRole,
            mode: resolvedMode,
            selectedPatientKey,
            contextScope,
            messageLength: trimmedPrompt.length,
            hasChatHistory: historyPayload.length > 0,
            startedAt
        });
    }

    try {
        const response = await fetch(copilotConfig.apiUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'chat',
                patient_id: resolvedPatientId || null,
                role: resolvedRole,
                mode: resolvedMode,
                message: trimmedPrompt,
                chat_history: historyPayload,
                request_id: requestId,
                csrf_token_form: copilotConfig.csrfToken
            })
        });

        const data = await response.json().catch(() => ({}));
        clearLoadingMessage();

        if (!response.ok) {
            const apiError = new Error(data.error || copilotConfig.apiFailureMessage);
            apiError.requestId = requestId;
            apiError.errorCategory = data.meta?.error_category || `http_${response.status}`;
            apiError.fallbackUsed = Boolean(data.meta?.fallback_used);
            throw apiError;
        }

        updateModeSelection(data.mode || resolvedMode);
        const meta = data.meta || {};
        const guardrailsResult = evaluateGuardrails({
            role: data.role || resolvedRole,
            mode: data.mode || resolvedMode,
            prompt: trimmedPrompt,
            draftResponse: data.answer || copilotConfig.apiFailureMessage,
            sections: data.sections || [],
            safetyText: data.safety_note || '',
            metadata: {
                selectedPatientKey,
                contextScope,
                patientId: resolvedPatientId
            }
        });

        logGuardrailsEvaluation(guardrailsResult, {
            requestId,
            role: data.role || resolvedRole,
            mode: data.mode || resolvedMode,
            selectedPatientKey
        });

        const guardrailTags = Array.from(new Set([...(data.tags || []), ...(guardrailsResult.policyTags || [])])).slice(0, 6);
        const assistantMessage = addAssistantMessage(guardrailsResult.finalResponse || data.answer || copilotConfig.apiFailureMessage, {
            mode: data.mode || resolvedMode,
            staffRole: data.role || resolvedRole,
            patientId: resolvedPatientId,
            selectedPatientKey,
            sections: guardrailsResult.finalSections || [],
            tags: guardrailTags,
            sources: guardrailsResult.allowed ? (data.sources || []) : [],
            safety: guardrailsResult.finalSafety || data.safety_note || defaultGuardrailSafetyNote,
            guardrails: guardrailsResult,
            traceId: requestId,
            requestId,
            meta: {
                ...meta,
                restricted_by_role: Boolean(meta.restricted_by_role) || !guardrailsResult.allowed,
                restriction_type: meta.restriction_type || guardrailsResult.blockedReason || '',
                guardrails_risk_level: guardrailsResult.riskLevel || 'low'
            }
        });

        if (CopilotTelemetry) {
            const responseLength = getAssistantMessagePlainText(assistantMessage).length;
            CopilotTelemetry.log('copilot_generation_succeeded', {
                requestId,
                responseId: assistantMessage.responseId || null,
                role: data.role || resolvedRole,
                mode: data.mode || resolvedMode,
                selectedPatientKey,
                latencyMs: meta.latency_ms || Math.round((window.performance && typeof window.performance.now === 'function'
                    ? window.performance.now()
                    : Date.now()) - startedPerf),
                responseLength,
                fallbackUsed: Boolean(meta.fallback_used),
                restrictedByRole: Boolean(meta.restricted_by_role) || !guardrailsResult.allowed
            });

            if (meta.fallback_used) {
                CopilotTelemetry.log('copilot_fallback_used', {
                    requestId,
                    role: data.role || resolvedRole,
                    mode: data.mode || resolvedMode,
                    selectedPatientKey,
                    fallbackUsed: true,
                    fallbackReason: meta.fallback_reason || 'demo_mode'
                });
            }

            if (meta.restricted_by_role || !guardrailsResult.allowed) {
                CopilotTelemetry.log('copilot_restricted_action', {
                    requestId,
                    role: data.role || resolvedRole,
                    mode: data.mode || resolvedMode,
                    selectedPatientKey,
                    restrictedByRole: true,
                    restrictionType: meta.restriction_type || guardrailsResult.blockedReason || 'role_guardrail'
                });
            }
        }
    } catch (error) {
        clearLoadingMessage();
        addAssistantMessage(error.message || copilotConfig.apiFailureMessage, {
            staffRole: resolvedRole,
            patientId: resolvedPatientId,
            selectedPatientKey,
            safety: 'Draft only. Human review required.',
            traceId: error.requestId || requestId,
            requestId
        });

        if (CopilotTelemetry) {
            const endedPerf = window.performance && typeof window.performance.now === 'function'
                ? window.performance.now()
                : Date.now();
            CopilotTelemetry.log('copilot_generation_failed', {
                requestId,
                role: resolvedRole,
                mode: resolvedMode,
                selectedPatientKey,
                latencyMs: Math.round(endedPerf - startedPerf),
                errorCategory: error.errorCategory || 'request_failed',
                fallbackUsed: Boolean(error.fallbackUsed)
            });
        }
    } finally {
        state.loading = false;
        updateSendState();
    }
}

async function requestReminderEmail(role, patientId) {
    if (state.loading) {
        return;
    }

    if (window.top && typeof window.top.restoreSession === 'function') {
        window.top.restoreSession();
    }

    const requestId = createId('request');
    const selectedPatientKey = selectedPatientKeyForValue(patientId || '');
    state.loading = true;
    state.messages.push(createMessage('assistant', copilotConfig.sendingReminderText, {
        isLoading: true,
        staffRole: role,
        patientId,
        selectedPatientKey,
        traceId: requestId,
        requestId
    }));
    updateSendState();
    renderMessages(true);

    try {
        const response = await fetch(copilotConfig.apiUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'send_reminder_email',
                patient_id: patientId || null,
                role,
                request_id: requestId,
                csrf_token_form: copilotConfig.csrfToken
            })
        });

        const data = await response.json().catch(() => ({}));
        clearLoadingMessage();

        addAssistantMessage(data.message || copilotConfig.apiFailureMessage, {
            mode: 'send_reminder_result',
            staffRole: role,
            patientId,
            selectedPatientKey,
            tags: data.tags || ['Front desk', 'Reminder', 'Minimum PHI'],
            sources: data.sources || [],
            safety: data.safety_note || '',
            traceId: requestId,
            requestId,
            meta: data.meta || {}
        });
    } catch (error) {
        clearLoadingMessage();
        addAssistantMessage(error.message || copilotConfig.apiFailureMessage, {
            mode: 'send_reminder_result',
            staffRole: role,
            patientId,
            selectedPatientKey,
            tags: ['Front desk', 'Reminder', 'Minimum PHI'],
            safety: '',
            traceId: requestId,
            requestId
        });
    } finally {
        state.loading = false;
        updateSendState();
    }
}

async function submitPrompt() {
    const prompt = input.value.trim();
    if (!prompt || state.loading) {
        return;
    }

    input.value = '';
    resizeInput();
    updateSendState();
    await requestAssistantResponse(prompt, { includeUserMessage: true });
}

patientSelect.addEventListener('change', () => {
    updateContextText();
    if (CopilotTelemetry) {
        CopilotTelemetry.log('copilot_patient_selected', {
            selectedPatientKey: currentSelectedPatientKey(),
            role: state.activeRole
        });
    }
});
roleSelect.addEventListener('change', (event) => {
    updateRoleSelection(event.target.value, { emitTelemetry: true });
});

modeSelect.addEventListener('change', (event) => {
    updateModeSelection(event.target.value, { emitTelemetry: true });
});

quickActionSelect.addEventListener('change', (event) => {
    const selectedMode = event.target.value;
    const action = actionCatalog[selectedMode];
    if (!action) {
        return;
    }

    updateModeSelection(action.mode, { emitTelemetry: true });
    input.value = action.prompt || '';
    resizeInput();
    updateSendState();
    input.focus();
    input.setSelectionRange(input.value.length, input.value.length);
    quickActionSelect.value = '';
});

input.addEventListener('input', () => {
    resizeInput();
    updateSendState();
});

input.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        if (!sendButton.disabled) {
            submitPrompt();
        }
    }
});

form.addEventListener('submit', (event) => {
    event.preventDefault();
    submitPrompt();
});

updateRoleSelection(copilotConfig.defaultRole || 'doctor');
resizeInput();
updateSendState();
state.messages.push(createMessage('assistant', copilotConfig.greeting, {
    staffRole: copilotConfig.defaultRole || 'doctor',
    selectedPatientKey: currentSelectedPatientKey(),
    showResponseActions: false
}));

if (patientSelect.options.length <= 1) {
    state.messages.push(createMessage(
        'assistant',
        'No seeded demo patients are available yet. General prompts still work, but patient-specific answers require importing the demo seed.',
        {
            staffRole: copilotConfig.defaultRole || 'doctor',
            showResponseActions: false
        }
    ));
}

renderMessages(true);
</script>
</body>
</html>
