(function () {
    'use strict';

    function createId(prefix) {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return prefix + '_' + window.crypto.randomUUID();
        }

        return prefix + '_' + Date.now() + '_' + Math.random().toString(16).slice(2);
    }

    function ensureTelemetryHost(targetWindow) {
        if (!targetWindow) {
            return null;
        }

        targetWindow.OpenEMRCopilotState = targetWindow.OpenEMRCopilotState || {
            role: 'doctor',
            mode: 'general_assistant',
            selectedPatientKey: null
        };

        if (targetWindow.CopilotTelemetry && targetWindow.CopilotMetrics && typeof targetWindow.printCopilotMetrics === 'function') {
            return targetWindow.CopilotTelemetry;
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
            'actionType'
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
            Object.keys(payload || {}).forEach(function (key) {
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

            const total = metrics.latencySamples.reduce(function (sum, value) {
                return sum + value;
            }, 0);

            return Math.round(total / metrics.latencySamples.length);
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
            log: function (eventName, payload) {
                const safePayload = sanitizePayload(payload || {});
                const event = Object.assign({
                    source: 'medical-copilot',
                    event: eventName,
                    timestamp: new Date().toISOString(),
                    sessionId: metrics.sessionId
                }, safePayload);

                updateMetrics(eventName, safePayload);

                const label = '[Medical Co-Pilot Audit] ' + eventName;
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

        return targetWindow.CopilotTelemetry;
    }

    function initWidget() {
        if (window.__openemrAICopilotWidgetLoaded) {
            return;
        }

        if (!window.OPENEMR_AI_COPILOT_URL || document.getElementById('openemr-ai-copilot-root')) {
            return;
        }

        window.__openemrAICopilotWidgetLoaded = true;

        const telemetry = ensureTelemetryHost(window);

        const state = {
            isOpen: false,
            iframeLoaded: false
        };

        const root = document.createElement('div');
        root.id = 'openemr-ai-copilot-root';
        root.className = 'copilot-widget-root';

        const launcher = document.createElement('button');
        launcher.type = 'button';
        launcher.className = 'copilot-widget-launcher';
        launcher.setAttribute('aria-expanded', 'false');
        launcher.setAttribute('aria-controls', 'openemr-ai-copilot-drawer');
        launcher.setAttribute('aria-label', 'Medical Co-Pilot');
        launcher.innerHTML =
            '<span class="copilot-widget-launcher-icon" aria-hidden="true">✦</span>' +
            '<span class="copilot-widget-launcher-label" aria-hidden="true">AI</span>';

        const drawer = document.createElement('section');
        drawer.id = 'openemr-ai-copilot-drawer';
        drawer.className = 'copilot-widget-drawer';
        drawer.setAttribute('role', 'dialog');
        drawer.setAttribute('aria-modal', 'false');
        drawer.setAttribute('aria-labelledby', 'openemr-ai-copilot-title');
        drawer.setAttribute('aria-hidden', 'true');

        const header = document.createElement('div');
        header.className = 'copilot-widget-header';
        header.innerHTML =
            '<div>' +
            '<h2 id="openemr-ai-copilot-title" class="copilot-widget-title">Medical Co-Pilot</h2>' +
            '<p class="copilot-widget-note">Beta</p>' +
            '</div>';

        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = 'copilot-widget-close';
        closeButton.setAttribute('aria-label', 'Close Medical Co-Pilot');
        closeButton.innerHTML = '&times;';
        header.appendChild(closeButton);

        const body = document.createElement('div');
        body.className = 'copilot-widget-body';

        const frame = document.createElement('iframe');
        frame.className = 'copilot-widget-frame';
        frame.title = 'Medical Co-Pilot';
        frame.loading = 'lazy';
        frame.referrerPolicy = 'same-origin';
        body.appendChild(frame);

        drawer.appendChild(header);
        drawer.appendChild(body);
        root.appendChild(launcher);
        root.appendChild(drawer);
        document.body.appendChild(root);

        function tryRestoreSession() {
            if (window.top && typeof window.top.restoreSession === 'function') {
                window.top.restoreSession();
            }
        }

        function syncOpenState() {
            root.classList.toggle('is-open', state.isOpen);
            launcher.setAttribute('aria-expanded', state.isOpen ? 'true' : 'false');
            drawer.setAttribute('aria-hidden', state.isOpen ? 'false' : 'true');
        }

        function attachFrameEscapeHandler() {
            try {
                const frameDocument = frame.contentWindow && frame.contentWindow.document;
                if (!frameDocument || frameDocument.__openemrAICopilotEscapeBound) {
                    return;
                }

                frameDocument.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        event.preventDefault();
                        closeDrawer();
                    }
                });
                frameDocument.__openemrAICopilotEscapeBound = true;
            } catch (error) {
                // Same-origin is expected, but quietly ignore if the frame is unavailable during load.
            }
        }

        function ensureIframeLoaded() {
            if (state.iframeLoaded) {
                return;
            }

            frame.src = window.OPENEMR_AI_COPILOT_URL;
            state.iframeLoaded = true;
        }

        function openDrawer() {
            tryRestoreSession();
            ensureIframeLoaded();
            state.isOpen = true;
            syncOpenState();
            if (telemetry) {
                telemetry.log('copilot_open', {
                    role: window.OpenEMRCopilotState.role || 'doctor',
                    selectedPatientKey: window.OpenEMRCopilotState.selectedPatientKey || null
                });
            }
            closeButton.focus();
        }

        function closeDrawer() {
            state.isOpen = false;
            syncOpenState();
            if (telemetry) {
                telemetry.log('copilot_close', {
                    role: window.OpenEMRCopilotState.role || 'doctor',
                    selectedPatientKey: window.OpenEMRCopilotState.selectedPatientKey || null
                });
            }
            launcher.focus();
        }

        function toggleDrawer() {
            if (state.isOpen) {
                closeDrawer();
            } else {
                openDrawer();
            }
        }

        launcher.addEventListener('click', toggleDrawer);
        closeButton.addEventListener('click', closeDrawer);
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && state.isOpen) {
                event.preventDefault();
                closeDrawer();
            }
        });
        frame.addEventListener('load', attachFrameEscapeHandler);

        syncOpenState();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWidget, { once: true });
    } else {
        initWidget();
    }
})();
