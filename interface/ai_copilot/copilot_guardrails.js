(function (root, factory) {
    if (typeof module === 'object' && module.exports) {
        module.exports = factory();
        return;
    }

    root.OpenEMRCopilotGuardrails = factory();
}(typeof globalThis !== 'undefined' ? globalThis : this, function () {
    const CLINICAL_DRAFT_NOTE = 'Draft only. Human review required. This does not replace clinical judgment or a final medical decision.';
    const BILLING_DRAFT_NOTE = 'Draft only. Human review required. This does not replace billing, compliance, or coder review.';
    const FRONT_DESK_NOTE = 'Administrative draft only. Human review required. Use minimum necessary PHI.';

    const ALL_MODES = [
        'general_assistant',
        'differential_diagnosis',
        'medication_info',
        'clinical_notes',
        'treatment_plan',
        'billing',
        'billing_review',
        'follow_up',
        'visit_summary',
        'patient_education',
        'appointment_info',
        'patient_contact',
        'send_reminder',
        'front_desk_summary'
    ];

    const ROLE_ALLOWED_MODES = {
        doctor: new Set(ALL_MODES),
        nurse: new Set([
            'general_assistant',
            'medication_info',
            'clinical_notes',
            'follow_up',
            'visit_summary',
            'patient_education',
            'appointment_info',
            'patient_contact',
            'front_desk_summary'
        ]),
        billing: new Set([
            'general_assistant',
            'billing',
            'billing_review',
            'visit_summary',
            'appointment_info',
            'patient_contact',
            'front_desk_summary'
        ]),
        front_desk: new Set([
            'general_assistant',
            'appointment_info',
            'patient_contact',
            'send_reminder',
            'front_desk_summary'
        ])
    };

    const PROMPT_INJECTION_PATTERNS = [
        /\bignore (all|any|previous|prior) instructions\b/i,
        /\bbypass (role|guardrail|restriction|policy|safety)/i,
        /\bshow (me )?(the )?full chart\b/i,
        /\breveal (the )?(hidden|restricted|internal) (context|notes|data)\b/i,
        /\bact as (an )?admin\b/i,
        /\boverride (hipaa|role restrictions|privacy rules|safety rules)\b/i
    ];

    const TOPIC_PATTERNS = {
        medication_info: /\b(medication|medications|meds|dose|dosage|prescription|prescriptions|interaction|counsel|refill|metformin|insulin|lisinopril|atorvastatin|albuterol|gabapentin)\b/i,
        differential_diagnosis: /\b(diagnose|diagnosis|differential|what could be causing|cause of|likely condition|red flag|do not miss)\b/i,
        clinical_notes: /\b(clinical note|soap|encounter note|documentation|note summary|chart summary|summarize the chart|subjective|objective|assessment|plan)\b/i,
        treatment_plan: /\b(treatment plan|plan for treatment|care plan|start treatment|stop treatment|change treatment|therap(y|ies)|dose change|prescribe)\b/i,
        follow_up: /\b(follow[- ]?up|monitor(ing)?|recheck|return visit|care coordination|next visit|escalation precaution)\b/i,
        billing: /\b(billing|claim|claim status|insurance|payer|payment|payment due|coverage|cpt|icd|coding|balance|invoice)\b/i,
        appointment_info: /\b(appointment|schedule|scheduled|provider|location|check[- ]?in)\b/i,
        patient_contact: /\b(contact|phone|email|preferred outreach|reach the patient|contact information)\b/i,
        send_reminder: /\b(reminder|notify|notification|outreach message|send reminder)\b/i,
        patient_education: /\b(patient[- ]?friendly|patient education|counseling|home instructions)\b/i,
        visit_summary: /\b(visit summary|summary of visit)\b/i
    };

    const DEFINITIVE_DIAGNOSIS_PATTERN = /\b(definitive diagnosis|final diagnosis|i diagnose|the diagnosis is|this patient definitely has|this is clearly)\b/i;
    const MEDICATION_CHANGE_PATTERN = /\b(start|stop|discontinue|increase|decrease|raise|lower|double|halve|switch)\b[\s\S]{0,48}\b(medication|medications|dose|dosage|mg|tablet|capsule|insulin|metformin|lisinopril|atorvastatin|albuterol|gabapentin)\b/i;
    const URGENT_DIRECTIVE_PATTERN = /\b(call 911|go to the er|go to the emergency room|seek emergency care immediately|hospitalize immediately|admit immediately)\b/i;
    const ESCALATION_LANGUAGE_PATTERN = /\b(licensed clinician|supervising clinician|clinical protocol|emergency services|urgent evaluation|escalate)\b/i;
    const CLINICAL_DISCLOSURE_PATTERN = /\b(a1c|troponin|glucose|wbc|ldl|creatinine|medication|metformin|insulin|lisinopril|atorvastatin|albuterol|gabapentin|diagnosis|differential|treatment plan|clinical note|soap|lab|labs)\b/i;

    function normalizeRole(role) {
        return ROLE_ALLOWED_MODES[String(role || '').toLowerCase()] ? String(role || '').toLowerCase() : 'doctor';
    }

    function normalizeMode(mode) {
        return String(mode || 'general_assistant').toLowerCase();
    }

    function unique(values) {
        return Array.from(new Set((values || []).filter(Boolean)));
    }

    function maxRisk(current, next) {
        const rank = { low: 1, medium: 2, high: 3 };
        return rank[next] > rank[current] ? next : current;
    }

    function responseToText(draftResponse, sections) {
        const parts = [];
        if (draftResponse) {
            parts.push(String(draftResponse));
        }

        (sections || []).forEach((section) => {
            if (!section || !Array.isArray(section.items) || !section.title) {
                return;
            }

            parts.push(section.title);
            section.items.forEach((item) => {
                if (item) {
                    parts.push(String(item));
                }
            });
        });

        return parts.join('\n').trim();
    }

    function inferTopic(mode, prompt) {
        const normalizedMode = normalizeMode(mode);
        if (normalizedMode && normalizedMode !== 'general_assistant') {
            return normalizedMode;
        }

        const text = String(prompt || '');
        for (const [topic, pattern] of Object.entries(TOPIC_PATTERNS)) {
            if (pattern.test(text)) {
                return topic;
            }
        }

        return 'general_assistant';
    }

    function containsPromptInjection(prompt) {
        const text = String(prompt || '');
        return PROMPT_INJECTION_PATTERNS.find((pattern) => pattern.test(text)) || null;
    }

    function buildBlockedMessage(role, blockedReason) {
        const messages = {
            prompt_injection_block: 'I can\'t bypass role restrictions or reveal hidden chart context. Please use a prompt that matches your selected role and approved workflow.',
            doctor_autonomous_diagnosis_block: 'I can support differential reasoning and draft clinical summaries, but I can\'t provide a definitive diagnosis. Ask for a differential diagnosis or chart summary for clinician review.',
            nurse_medication_change_block: 'I can help explain the current medication plan and flag items to review, but medication changes should be handled by the prescribing clinician.',
            nurse_clinical_scope_block: 'Diagnosis and prescribing guidance are restricted for the Nurse role. You can request care coordination, follow-up preparation, medication education, or patient education drafts.',
            billing_clinical_scope_block: 'Detailed clinical information is not available for the Billing Staff role. You can review claim status, insurance context, payment status, and billing workflow summaries.',
            front_desk_clinical_scope_block: 'Medication information is not available for the Front Desk role. You can view contact details, appointment information, and administrative outreach guidance.',
            front_desk_phi_limit: 'Clinical chart details are restricted for the Front Desk role. You can use appointment, contact, and reminder workflows with minimum necessary PHI only.',
            billing_phi_limit: 'This response includes more clinical detail than the Billing Staff role should receive. You can request claim status, insurance context, payment status, or billing workflow guidance instead.',
            medication_change_instruction: 'Medication start, stop, and dose-change instructions require licensed clinician review. I can summarize medication considerations, but I can\'t provide final medication change instructions.',
            autonomous_diagnosis_language: 'I can support differential reasoning, but I can\'t provide a definitive diagnosis. Please review the chart findings and confirm the assessment with a licensed clinician.'
        };

        if (messages[blockedReason]) {
            return messages[blockedReason];
        }

        if (role === 'front_desk') {
            return messages.front_desk_clinical_scope_block;
        }
        if (role === 'billing') {
            return messages.billing_clinical_scope_block;
        }
        if (role === 'nurse') {
            return messages.nurse_clinical_scope_block;
        }

        return 'This draft needs human review before it can be shown in this workflow.';
    }

    function defaultSafetyFor(role, topic, existingSafety) {
        const safetyText = String(existingSafety || '').trim();
        if (safetyText) {
            return safetyText;
        }

        if (role === 'front_desk') {
            return FRONT_DESK_NOTE;
        }
        if (role === 'billing' || topic === 'billing' || topic === 'billing_review') {
            return BILLING_DRAFT_NOTE;
        }

        return CLINICAL_DRAFT_NOTE;
    }

    function displayRoleLabel(role) {
        const labels = {
            doctor: 'Doctor',
            nurse: 'Nurse',
            billing: 'Billing Staff',
            front_desk: 'Front Desk'
        };

        return labels[role] || 'Doctor';
    }

    function displayReasonFor(blockedReason, role) {
        const reasons = {
            prompt_injection_block: 'This request attempted to override the demo safety rules.',
            doctor_autonomous_diagnosis_block: 'Definitive diagnosis language is restricted in this workflow.',
            nurse_medication_change_block: 'Medication change instructions are restricted for the Nurse role.',
            nurse_clinical_scope_block: 'This request is outside the Nurse role scope.',
            billing_clinical_scope_block: 'This request is outside the Billing Staff role scope.',
            front_desk_clinical_scope_block: 'This request is outside the Front Desk role scope.',
            front_desk_phi_limit: 'Clinical chart details are limited for the Front Desk role.',
            billing_phi_limit: 'Detailed clinical content is limited for the Billing Staff role.',
            medication_change_instruction: 'Medication start, stop, or dose-change instructions require clinician review.',
            autonomous_diagnosis_language: 'Autonomous diagnosis language is not allowed in the demo.'
        };

        return reasons[blockedReason] || `This request is outside the ${displayRoleLabel(role)} role scope.`;
    }

    function alternativeFor(role, blockedReason) {
        if (blockedReason === 'prompt_injection_block') {
            return 'Try a prompt that matches the selected staff role and approved workflow.';
        }

        const alternatives = {
            doctor: 'Ask for a differential diagnosis, chart summary, medication summary, or draft treatment plan for clinician review.',
            nurse: 'Ask for care coordination, follow-up preparation, medication education, or patient education support.',
            billing: 'Ask for claim status, insurance context, payment status, or billing workflow guidance.',
            front_desk: 'I can help with scheduling, contact confirmation, reminder drafting, or routing this to clinical staff.'
        };

        return alternatives[role] || alternatives.doctor;
    }

    function buildUiPayload(role, allowed, blockedReason, riskLevel, policyTags) {
        return {
            blocked: !allowed,
            title: allowed ? 'Guardrails checked' : 'Guardrail blocked this request',
            statusLabel: allowed
                ? 'Guardrails checked · Role-safe · Draft-only'
                : 'Guardrails blocked · Safer alternative shown',
            displayReason: allowed ? 'Role scope, prompt safety, and draft-only rules passed.' : displayReasonFor(blockedReason, role),
            alternative: allowed ? '' : alternativeFor(role, blockedReason),
            roleLabel: displayRoleLabel(role),
            checks: ['Role scope', 'Prompt injection filter', 'Draft-only enforcement', 'PHI minimum necessary'],
            riskLevel: riskLevel || 'low',
            policyTags: unique(policyTags)
        };
    }

    function evaluatePromptPolicy(role, topic, prompt) {
        const normalizedPrompt = String(prompt || '').toLowerCase();
        const injectionMatch = containsPromptInjection(prompt);
        if (injectionMatch) {
            return {
                allowed: false,
                blockedReason: 'prompt_injection_block',
                riskLevel: 'high',
                policyTags: ['guardrails', 'prompt_injection', role]
            };
        }

        if (role === 'doctor' && /\bdiagnose\b/i.test(prompt || '')) {
            return {
                allowed: false,
                blockedReason: 'doctor_autonomous_diagnosis_block',
                riskLevel: 'high',
                policyTags: ['guardrails', 'doctor', 'diagnosis_review_only']
            };
        }

        if (role === 'nurse' && (topic === 'differential_diagnosis' || topic === 'treatment_plan' || /\b(start|stop|increase|decrease|prescribe|diagnose)\b/i.test(prompt || ''))) {
            return {
                allowed: false,
                blockedReason: /\b(start|stop|increase|decrease|prescribe)\b/i.test(prompt || '')
                    ? 'nurse_medication_change_block'
                    : 'nurse_clinical_scope_block',
                riskLevel: 'high',
                policyTags: ['guardrails', 'nurse', 'role_scope']
            };
        }

        if (role === 'billing' && (
            topic === 'differential_diagnosis' ||
            topic === 'medication_info' ||
            topic === 'treatment_plan' ||
            /\b(full chart|full note|lab values|medications|treatment plan)\b/i.test(normalizedPrompt)
        )) {
            return {
                allowed: false,
                blockedReason: 'billing_clinical_scope_block',
                riskLevel: 'high',
                policyTags: ['guardrails', 'billing', 'role_scope']
            };
        }

        if (role === 'front_desk' && (
            topic === 'differential_diagnosis' ||
            topic === 'medication_info' ||
            topic === 'clinical_notes' ||
            topic === 'treatment_plan' ||
            topic === 'follow_up' ||
            topic === 'patient_education' ||
            topic === 'visit_summary' ||
            topic === 'billing_review' ||
            CLINICAL_DISCLOSURE_PATTERN.test(prompt || '')
        )) {
            return {
                allowed: false,
                blockedReason: 'front_desk_clinical_scope_block',
                riskLevel: 'high',
                policyTags: ['guardrails', 'front_desk', 'minimum_phi']
            };
        }

        if (!ROLE_ALLOWED_MODES[role].has(topic) && topic !== 'general_assistant') {
            const blockedReasonByRole = {
                nurse: 'nurse_clinical_scope_block',
                billing: 'billing_clinical_scope_block',
                front_desk: 'front_desk_clinical_scope_block'
            };

            return {
                allowed: false,
                blockedReason: blockedReasonByRole[role] || 'role_scope_block',
                riskLevel: 'high',
                policyTags: ['guardrails', role, 'role_scope']
            };
        }

        return null;
    }

    function evaluateResponsePolicy(role, topic, responseText) {
        const text = String(responseText || '');
        if (!text) {
            return {
                riskLevel: 'low',
                blockedReason: '',
                policyTags: ['guardrails', role, topic]
            };
        }

        let riskLevel = 'low';
        let blockedReason = '';
        const policyTags = ['guardrails', role, topic];

        if (role === 'front_desk' && CLINICAL_DISCLOSURE_PATTERN.test(text)) {
            blockedReason = 'front_desk_phi_limit';
            riskLevel = 'high';
            policyTags.push('minimum_phi');
        } else if (role === 'billing' && /\b(lab|labs|medication|medications|treatment plan|dose|dosage|troponin|a1c|glucose|wbc)\b/i.test(text)) {
            blockedReason = 'billing_phi_limit';
            riskLevel = 'high';
            policyTags.push('billing_scope');
        } else if (DEFINITIVE_DIAGNOSIS_PATTERN.test(text)) {
            blockedReason = 'autonomous_diagnosis_language';
            riskLevel = 'high';
            policyTags.push('diagnosis_review_only');
        } else if (MEDICATION_CHANGE_PATTERN.test(text)) {
            blockedReason = 'medication_change_instruction';
            riskLevel = 'high';
            policyTags.push('medication_review_only');
        } else if (URGENT_DIRECTIVE_PATTERN.test(text) && !ESCALATION_LANGUAGE_PATTERN.test(text)) {
            riskLevel = 'medium';
            policyTags.push('escalation_added');
        }

        return {
            riskLevel,
            blockedReason,
            policyTags: unique(policyTags)
        };
    }

    function cloneSections(sections) {
        return Array.isArray(sections)
            ? sections.map((section) => ({
                ...section,
                items: Array.isArray(section.items) ? section.items.slice() : []
            }))
            : [];
    }

    function evaluate(input) {
        const role = normalizeRole(input.role);
        const mode = normalizeMode(input.mode);
        const prompt = String(input.prompt || '');
        const topic = inferTopic(mode, prompt);
        const metadata = input.metadata || {};
        const sections = cloneSections(input.sections || []);
        const draftResponse = String(input.draftResponse || '');
        const draftText = responseToText(draftResponse, sections);
        const promptPolicy = evaluatePromptPolicy(role, topic, prompt);

        if (promptPolicy) {
            const finalResponse = buildBlockedMessage(role, promptPolicy.blockedReason);
            return {
                allowed: false,
                finalResponse,
                blockedReason: promptPolicy.blockedReason,
                riskLevel: promptPolicy.riskLevel,
                policyTags: unique(promptPolicy.policyTags),
                auditSummary: {
                    role,
                    mode,
                    topic,
                    patientKey: metadata.selectedPatientKey || metadata.patientKey || null,
                    phase: 'prompt',
                    blockedReason: promptPolicy.blockedReason
                },
                finalSections: [],
                finalSafety: defaultSafetyFor(role, topic, ''),
                ui: buildUiPayload(role, false, promptPolicy.blockedReason, promptPolicy.riskLevel, promptPolicy.policyTags)
            };
        }

        const responsePolicy = evaluateResponsePolicy(role, topic, draftText);
        if (responsePolicy.blockedReason) {
            const finalResponse = buildBlockedMessage(role, responsePolicy.blockedReason);
            return {
                allowed: false,
                finalResponse,
                blockedReason: responsePolicy.blockedReason,
                riskLevel: responsePolicy.riskLevel,
                policyTags: unique(responsePolicy.policyTags),
                auditSummary: {
                    role,
                    mode,
                    topic,
                    patientKey: metadata.selectedPatientKey || metadata.patientKey || null,
                    phase: draftText ? 'response' : 'prompt',
                    blockedReason: responsePolicy.blockedReason
                },
                finalSections: [],
                finalSafety: defaultSafetyFor(role, topic, ''),
                ui: buildUiPayload(role, false, responsePolicy.blockedReason, responsePolicy.riskLevel, responsePolicy.policyTags)
            };
        }

        let finalResponse = draftResponse;
        if (responsePolicy.riskLevel === 'medium' && URGENT_DIRECTIVE_PATTERN.test(draftText) && !ESCALATION_LANGUAGE_PATTERN.test(draftText)) {
            finalResponse = `${draftResponse}\n\nIf symptoms are urgent or worsening, escalate immediately to the supervising clinician or emergency services per protocol.`.trim();
        }

        return {
            allowed: true,
            finalResponse,
            blockedReason: '',
            riskLevel: responsePolicy.riskLevel,
            policyTags: unique(responsePolicy.policyTags),
            auditSummary: {
                role,
                mode,
                topic,
                patientKey: metadata.selectedPatientKey || metadata.patientKey || null,
                phase: draftText ? 'response' : 'prompt',
                blockedReason: ''
            },
            finalSections: sections,
            finalSafety: defaultSafetyFor(role, topic, input.safetyText || ''),
            ui: buildUiPayload(role, true, '', responsePolicy.riskLevel, responsePolicy.policyTags)
        };
    }

    return {
        evaluate,
        inferTopic
    };
}));
