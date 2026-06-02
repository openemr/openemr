/**
 * MedEx Full Calendar Initialization - Multi-Provider Support
 */

console.log('calendar.js loaded and executing...');

// Store calendar instances
let calendarInstances = [];
let autoRefreshTimer = null;
const FALLBACK_REFRESH_INTERVAL_MS = 180000;
const AUTO_REFRESH_CHECK_MS = 5000;
let lastFallbackRefreshAt = 0;
let lastActiveState = null;
const recentMoveSignatures = new Map();
let calendarEventSource = null;
let currentEventStreamKey = '';
let eventStreamReconnectTimer = null;
let eventStreamConnected = false;
let modernTooltipEl = null;
let modernTooltipTarget = null;
const DEFAULT_SLOT_STATE_FILTERS = [
    'filled',
    'open',
    'open_not_reschedulable',
    'held_staff',
    'held_patient',
    'open_reschedulable_available'
];

function restoreOpenEmrSessionIfAvailable() {
    try {
        if (typeof top !== 'undefined' && typeof top.restoreSession === 'function') {
            top.restoreSession();
        }
    } catch (err) {
        // Ignore frame/session refresh issues and proceed.
    }
}

function fetchWithOpenEmrSession(url, options) {
    restoreOpenEmrSessionIfAvailable();
    return fetch(url, options);
}

function computeSlotVisualState(event) {
    const props = event.extendedProps || {};
    const patientId = parseInt(props.patientId || 0, 10) || 0;
    const isReschedulable = !!props.isReschedulable;
    const rawSlotState = String(props.slotState || '').trim().toLowerCase();
    const reschedulerActive = !!(window.medexReschedulerActive);

    if (patientId > 0) {
        return 'filled';
    }
    if (rawSlotState === 'consumed') {
        return 'filled';
    }
    // Without the Rescheduler service, all open slots collapse to a single 'open' state.
    if (!reschedulerActive) {
        return 'open';
    }
    if (rawSlotState === 'held_staff') {
        return 'held_staff';
    }
    if (rawSlotState === 'held_patient') {
        return 'held_patient';
    }
    if (!isReschedulable) {
        return 'open_not_reschedulable';
    }
    return 'open_reschedulable_available';
}

function getSlotVisualMeta(slotVisualState) {
    const map = {
        filled: { short: 'FILLED', label: 'Filled' },
        open: { short: 'OPEN', label: 'Open' },
        open_not_reschedulable: { short: 'OPEN', label: 'Open and available to fill by staff. Not visible to the Patient Rescheduler.' },
        open_reschedulable_full: { short: 'FULL', label: 'Reschedulable, but full' },
        held_staff: { short: 'HELD-S', label: 'Held by staff' },
        held_patient: { short: 'HELD-P', label: 'Held by patient' },
        open_reschedulable_available: { short: 'Open-P', label: 'Open and available to fill by staff and patients using the Patient Rescheduler service.' }
    };
    return map[slotVisualState] || map.open_reschedulable_available;
}

function fitTextWithMinimumEllipsis(element, fullText, minChars = 5) {
    if (!element) {
        return;
    }

    const normalized = String(fullText || '').trim();
    if (!normalized) {
        element.textContent = '';
        return;
    }

    element.textContent = normalized;
    element.setAttribute('title', normalized);

    const availableWidth = element.clientWidth;
    if (availableWidth <= 0 || element.scrollWidth <= availableWidth) {
        return;
    }

    const ellipsis = '...';
    const minimum = Math.min(Math.max(parseInt(minChars, 10) || 5, 1), normalized.length);
    let best = minimum;
    let low = minimum;
    let high = normalized.length - 1;

    element.textContent = normalized.slice(0, minimum) + ellipsis;
    if (element.scrollWidth <= availableWidth) {
        while (low <= high) {
            const mid = Math.floor((low + high) / 2);
            element.textContent = normalized.slice(0, mid) + ellipsis;
            if (element.scrollWidth <= availableWidth) {
                best = mid;
                low = mid + 1;
            } else {
                high = mid - 1;
            }
        }
        element.textContent = normalized.slice(0, best) + ellipsis;
        return;
    }

    // If the slot is extremely narrow, still preserve the 5-character floor
    // in the source text instead of allowing the browser to collapse it to 2-3.
    element.textContent = normalized.slice(0, minimum) + ellipsis;
}

function cssColorToHex(colorValue) {
    const raw = String(colorValue || '').trim();
    if (!raw) {
        return '';
    }

    const six = raw.match(/^#([0-9a-f]{6})$/i);
    if (six) {
        return '#' + six[1].toUpperCase();
    }

    const three = raw.match(/^#([0-9a-f]{3})$/i);
    if (three) {
        const p = three[1];
        return ('#' + p[0] + p[0] + p[1] + p[1] + p[2] + p[2]).toUpperCase();
    }

    const rgb = raw.match(/^rgba?\((\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})(?:\s*,\s*[0-9.]+)?\)$/i);
    if (rgb) {
        const toHex = (n) => Math.max(0, Math.min(255, parseInt(n, 10) || 0)).toString(16).padStart(2, '0').toUpperCase();
        return '#' + toHex(rgb[1]) + toHex(rgb[2]) + toHex(rgb[3]);
    }

    return '';
}

function ensureModernTooltipElement() {
    if (!modernTooltipEl) {
        modernTooltipEl = document.getElementById('medex-modern-tooltip');
    }
    return modernTooltipEl;
}

function positionModernTooltip(target) {
    const tooltip = ensureModernTooltipElement();
    if (!tooltip || !target) {
        return;
    }
    const targetRect = target.getBoundingClientRect();
    const tipRect = tooltip.getBoundingClientRect();
    const gap = 10;
    let top = targetRect.top - tipRect.height - gap;
    if (top < 8) {
        top = targetRect.bottom + gap;
    }
    let left = targetRect.left + (targetRect.width / 2) - (tipRect.width / 2);
    left = Math.max(8, Math.min(left, window.innerWidth - tipRect.width - 8));
    tooltip.style.top = `${Math.round(top)}px`;
    tooltip.style.left = `${Math.round(left)}px`;
}

function showModernTooltip(target) {
    const tooltip = ensureModernTooltipElement();
    if (!tooltip || !target) {
        return;
    }
    const text = String(target.getAttribute('data-tooltip') || '').trim();
    if (!text) {
        return;
    }
    modernTooltipTarget = target;
    tooltip.textContent = text;
    tooltip.setAttribute('aria-hidden', 'false');
    tooltip.classList.add('show');
    positionModernTooltip(target);
}

function hideModernTooltip() {
    const tooltip = ensureModernTooltipElement();
    if (!tooltip) {
        return;
    }
    modernTooltipTarget = null;
    tooltip.classList.remove('show');
    tooltip.setAttribute('aria-hidden', 'true');
}

function bindModernTooltips() {
    ensureModernTooltipElement();

    const getTooltipTarget = (node) => {
        if (!node || !node.closest) {
            return null;
        }
        return node.closest('#sidebar .slot-state-legend-badge[data-tooltip]');
    };

    document.addEventListener('mouseover', function(e) {
        const target = getTooltipTarget(e.target);
        if (!target) {
            return;
        }
        showModernTooltip(target);
    });

    document.addEventListener('mouseout', function(e) {
        if (!modernTooltipTarget) {
            return;
        }
        const related = e.relatedTarget;
        if (related && modernTooltipTarget.contains(related)) {
            return;
        }
        const leavingFrom = getTooltipTarget(e.target);
        if (leavingFrom && leavingFrom === modernTooltipTarget) {
            hideModernTooltip();
        }
    });

    document.addEventListener('focusin', function(e) {
        const target = getTooltipTarget(e.target);
        if (!target) {
            return;
        }
        showModernTooltip(target);
    });

    document.addEventListener('focusout', function(e) {
        const leavingFrom = getTooltipTarget(e.target);
        if (leavingFrom && leavingFrom === modernTooltipTarget) {
            hideModernTooltip();
        }
    });

    window.addEventListener('scroll', function() {
        if (modernTooltipTarget) {
            positionModernTooltip(modernTooltipTarget);
        }
    }, true);

    window.addEventListener('resize', function() {
        if (modernTooltipTarget) {
            positionModernTooltip(modernTooltipTarget);
        }
    });
}

function getCheckedValues(selector) {
    return Array.from(document.querySelectorAll(selector)).map((cb) => String(cb.value || '').trim()).filter(Boolean);
}

function getSelectedSlotStateFilters() {
    if (!document.getElementById('slot-state-filter')) {
        return null;
    }
    const values = getCheckedValues('#slot-state-filter input[type="checkbox"]:checked');
    return new Set(values);
}

function getSelectedAppointmentCategoryFilters() {
    if (!document.getElementById('appointment-category-filter')) {
        return null;
    }
    const values = getCheckedValues('#appointment-category-filter input[type="checkbox"]:checked')
        .map((value) => parseInt(value, 10))
        .filter((value) => Number.isInteger(value) && value > 0);
    return new Set(values);
}

function shouldDisplayEventByFilters(eventData, selectedSlotStates, selectedCategoryIds) {
    const props = eventData && eventData.extendedProps ? eventData.extendedProps : {};
    const patientId = parseInt(props.patientId || 0, 10) || 0;
    const preferredCategoryId = parseInt(props.preferredCategoryId || 0, 10) || 0;
    const categoryId = parseInt(props.categoryId || 0, 10) || 0;
    const effectiveCategoryId = preferredCategoryId > 0 ? preferredCategoryId : categoryId;
    const slotVisualState = computeSlotVisualState({ extendedProps: props });

    // Patient appointments always pass the slot-state filter when 'filled' is active,
    // and always pass the category filter — the category filter targets template slot types.
    if (patientId > 0) {
        if (selectedSlotStates instanceof Set && selectedSlotStates.size > 0) {
            return selectedSlotStates.has('filled');
        }
        if (selectedSlotStates instanceof Set && selectedSlotStates.size === 0) {
            return false;
        }
        return true;
    }

    if (selectedSlotStates instanceof Set && selectedSlotStates.size > 0) {
        // When rescheduler is inactive, slots that were Open-P collapse to 'open'.
        // Treat 'open' as matching 'open_reschedulable_available' so saved filters
        // don't hide open slots just because the rescheduler was paused.
        const reschedulerActive = !!(window.medexReschedulerActive);
        let effectiveState = slotVisualState;
        if (!reschedulerActive && slotVisualState === 'open' && selectedSlotStates.has('open_reschedulable_available') && !selectedSlotStates.has('open')) {
            effectiveState = 'open_reschedulable_available';
        }
        if (!selectedSlotStates.has(effectiveState)) {
            return false;
        }
    } else if (selectedSlotStates instanceof Set && selectedSlotStates.size === 0) {
        return false;
    }

    if (selectedCategoryIds instanceof Set) {
        if (selectedCategoryIds.size === 0) {
            return false;
        }
        if (effectiveCategoryId <= 0 || !selectedCategoryIds.has(effectiveCategoryId)) {
            return false;
        }
    }

    return true;
}

function setFilterGroupSelection(groupId, isChecked) {
    const group = document.getElementById(groupId);
    if (!group) {
        return;
    }
    group.querySelectorAll('input[type="checkbox"]').forEach((cb) => {
        cb.checked = !!isChecked;
    });
}

function bindFilterBulkActions() {
    const actionButtons = Array.from(document.querySelectorAll('#sidebar .filter-bulk-action[data-target-filter][data-action]'));
    if (actionButtons.length === 0) {
        return;
    }

    actionButtons.forEach((button) => {
        button.addEventListener('click', function() {
            const targetFilterId = String(button.getAttribute('data-target-filter') || '').trim();
            const action = String(button.getAttribute('data-action') || '').trim();
            if (!targetFilterId || !action) {
                return;
            }

            if (action === 'select-all') {
                setFilterGroupSelection(targetFilterId, true);
            } else if (action === 'clear-all') {
                // For availability filter: reset to open-staff defaults, not blank.
                if (targetFilterId === 'slot-state-filter') {
                    setFilterGroupSelection(targetFilterId, false);
                    const openStaffDefaults = ['open_not_reschedulable', 'open_reschedulable_available', 'held_staff', 'open'];
                    const filterEl = document.getElementById(targetFilterId);
                    if (filterEl) {
                        openStaffDefaults.forEach(function(v) {
                            const cb = filterEl.querySelector('input[type="checkbox"][value="' + v + '"]');
                            if (cb) { cb.checked = true; }
                        });
                    }
                } else {
                    setFilterGroupSelection(targetFilterId, false);
                }
            } else {
                return;
            }

            saveFilterSelections();
            refetchAllCalendars('filter-bulk-action', true);
        });
    });
}

function setUnifiedHeaderTitle(text) {
    const titleEl = document.getElementById('unified-title');
    if (titleEl) {
        titleEl.textContent = text;
    }
}

function isCalendarActivelyDisplayed() {
    if (document.visibilityState !== 'visible') {
        return false;
    }

    const frameEl = window.frameElement;
    if (frameEl) {
        const frameStyle = window.getComputedStyle(frameEl);
        if (
            frameStyle.display === 'none' ||
            frameStyle.visibility === 'hidden' ||
            frameStyle.opacity === '0'
        ) {
            return false;
        }

        const frameRect = frameEl.getBoundingClientRect();
        if (frameRect.width === 0 || frameRect.height === 0) {
            return false;
        }
    }

    return true;
}

function refetchAllCalendars(reason = 'manual', force = false) {
    if (!Array.isArray(calendarInstances) || calendarInstances.length === 0) {
        return;
    }
    if (!force && !isCalendarActivelyDisplayed()) {
        return;
    }
    console.log('Refetching all calendars, reason:', reason);
    calendarInstances.forEach(cal => cal.refetchEvents());
}

function notifyCalendarDataChanged() {
    // Notify sibling tabs/windows via localStorage event.
    localStorage.setItem('medexCalendarLastMutation', String(Date.now()));
}

function getCurrentSyncContext() {
    if (!Array.isArray(calendarInstances) || calendarInstances.length === 0) {
        return null;
    }

    const firstCalendar = calendarInstances[0];
    if (!firstCalendar || !firstCalendar.view) {
        return null;
    }

    const providers = Array.from(
        document.querySelectorAll('#provider-filter input[type="checkbox"]:checked')
    ).map((cb) => cb.value).filter(Boolean);

    const facilities = Array.from(
        document.querySelectorAll('#facility-filter input[type="checkbox"]:checked')
    ).map((cb) => cb.value).filter(Boolean);

    const view = firstCalendar.view;
    const start = view.activeStart ? view.activeStart.toISOString().slice(0, 10) : '';
    let end = '';
    if (view.activeEnd) {
        const inclusiveEnd = new Date(view.activeEnd.getTime());
        inclusiveEnd.setDate(inclusiveEnd.getDate() - 1);
        end = inclusiveEnd.toISOString().slice(0, 10);
    }

    return { providers, facilities, start, end };
}

function stopCalendarEventStream() {
    if (eventStreamReconnectTimer) {
        clearTimeout(eventStreamReconnectTimer);
        eventStreamReconnectTimer = null;
    }
    if (calendarEventSource) {
        calendarEventSource.close();
        calendarEventSource = null;
    }
    eventStreamConnected = false;
    currentEventStreamKey = '';
}

function buildEventStreamQuery(context) {
    const params = new URLSearchParams();
    if (context.start) {
        params.set('start', context.start);
    }
    if (context.end) {
        params.set('end', context.end);
    }
    if (context.providers.length > 0) {
        params.set('providers', context.providers.join(','));
    }
    if (context.facilities.length > 0) {
        params.set('facilities', context.facilities.join(','));
    }
    return params.toString();
}

function ensureCalendarEventStream() {
    if (typeof window.EventSource === 'undefined') {
        return;
    }
    if (!isCalendarActivelyDisplayed()) {
        stopCalendarEventStream();
        return;
    }

    const context = getCurrentSyncContext();
    if (!context) {
        stopCalendarEventStream();
        return;
    }

    const query = buildEventStreamQuery(context);
    const streamKey = query;
    if (calendarEventSource && currentEventStreamKey === streamKey) {
        return;
    }

    stopCalendarEventStream();
    currentEventStreamKey = streamKey;

    const streamUrl = webroot + '/interface/modules/custom_modules/oe-module-medex/public/calendar/stream_events.php?' + query;
    calendarEventSource = new EventSource(streamUrl);

    calendarEventSource.addEventListener('open', function() {
        eventStreamConnected = true;
        console.log('Calendar event stream connected');
    });

    calendarEventSource.addEventListener('calendar-update', function() {
        refetchAllCalendars('sse-update', true);
    });

    calendarEventSource.addEventListener('error', function() {
        eventStreamConnected = false;
        if (calendarEventSource) {
            calendarEventSource.close();
            calendarEventSource = null;
        }
        if (eventStreamReconnectTimer) {
            clearTimeout(eventStreamReconnectTimer);
        }
        eventStreamReconnectTimer = setTimeout(() => {
            ensureCalendarEventStream();
        }, 4000);
    });
}

function startAutoRefresh() {
    if (autoRefreshTimer) {
        clearInterval(autoRefreshTimer);
    }
    lastActiveState = isCalendarActivelyDisplayed();
    lastFallbackRefreshAt = Date.now();
    autoRefreshTimer = setInterval(() => {
        const activeNow = isCalendarActivelyDisplayed();
        if (lastActiveState === false && activeNow) {
            refetchAllCalendars('became-active', true);
        }
        lastActiveState = activeNow;

        if (!activeNow) {
            stopCalendarEventStream();
            return;
        }

        ensureCalendarEventStream();

        const now = Date.now();
        if (!eventStreamConnected && now - lastFallbackRefreshAt >= FALLBACK_REFRESH_INTERVAL_MS) {
            lastFallbackRefreshAt = now;
            refetchAllCalendars('fallback-poll');
        }
    }, AUTO_REFRESH_CHECK_MS);
}

// Use immediate execution with fallback
function initializeCalendars() {
    console.log('=== Initializing calendars... ===');

    if (typeof FullCalendar === 'undefined') {
        console.error('FullCalendar library not loaded!');
        document.getElementById('calendars-container').innerHTML = '<div style="padding: 50px; text-align: center; color: red;"><h2>Error: FullCalendar library failed to load</h2><p>Please check browser console for details.</p></div>';
        setUnifiedHeaderTitle('Calendar Unavailable');
        return;
    }

    console.log('FullCalendar object:', FullCalendar);
    const container = document.getElementById('calendars-container');
    console.log('Calendars container element:', container);

    if (!container) {
        console.error('Calendars container element #calendars-container not found in DOM!');
        return;
    }

    // Get schedule times and interval from OpenEMR globals
    const scheduleStart = parseInt(window.scheduleStart || '8');
    const scheduleEnd = parseInt(window.scheduleEnd || '17');
    const calendarInterval = parseInt(window.calendarInterval || '15');
    const slotMinTime = String(scheduleStart).padStart(2, '0') + ':00:00';
    const slotMaxTime = String(scheduleEnd).padStart(2, '0') + ':00:00';
    const scrollTime = slotMinTime;
    const slotDuration = '00:' + String(calendarInterval).padStart(2, '0') + ':00';

    // Resolve opening view precedence:
    // URL view -> last saved view -> My Settings default view -> OpenEMR default view
    const normalizeView = (view) => {
        const map = {
            day: 'timeGridDay',
            week: 'timeGridWeek',
            month: 'dayGridMonth',
            year: 'dayGridMonth',
            list: 'listWeek',
            timeGridDay: 'timeGridDay',
            timeGridWeek: 'timeGridWeek',
            dayGridMonth: 'dayGridMonth',
            listWeek: 'listWeek'
        };
        return map[view] || null;
    };
    const urlParams = new URLSearchParams(window.location.search);
    const urlView = normalizeView(urlParams.get('view') || '');
    const savedView = normalizeView(localStorage.getItem('medexCalendarView') || '');
    const prefsView = normalizeView((window.medexUserPrefs && window.medexUserPrefs.defaultView) ? window.medexUserPrefs.defaultView : '');
    const openEmrView = normalizeView(window.calendarDefaultView || '');
    const defaultView = urlView || savedView || prefsView || openEmrView || 'timeGridWeek';
    const savedDate = localStorage.getItem('medexCalendarDate');
    const timeIncrement = parseInt(window.calendarTimeIncrement || '5');
    const use24Hours = window.calendar24Hours !== undefined ? window.calendar24Hours : false;
    const firstDayOfWeek = (window.calendarFirstDayOfWeek !== undefined) ? parseInt(window.calendarFirstDayOfWeek) : 0;
    const showWeekends = !(window.medexUserPrefs && window.medexUserPrefs.showWeekends === false);

    console.log('Saved view preference:', savedView);
    console.log('Saved date preference:', savedDate);
    console.log('Using calendar view:', defaultView);

    console.log('Using OpenEMR schedule times:', slotMinTime, 'to', slotMaxTime);
    console.log('Using OpenEMR calendar interval:', slotDuration);
    console.log('Using OpenEMR default view:', defaultView);
    console.log('Using 24-hour format:', use24Hours);
    console.log('First day of week (0=Sunday, 1=Monday, etc):', firstDayOfWeek);
    console.log('Raw window.calendarFirstDayOfWeek value:', window.calendarFirstDayOfWeek, 'type:', typeof window.calendarFirstDayOfWeek);

    // Get checked providers
    let providerCheckboxes = document.querySelectorAll('#provider-filter input[type="checkbox"]:checked');
    let selectedProviders = Array.from(providerCheckboxes).map(cb => cb.value);

    // Fallback: if stale preferences resulted in no active providers, select the first visible provider.
    if (selectedProviders.length === 0) {
        const firstProvider = document.querySelector('#provider-filter input[type="checkbox"]');
        if (firstProvider) {
            firstProvider.checked = true;
            providerCheckboxes = document.querySelectorAll('#provider-filter input[type="checkbox"]:checked');
            selectedProviders = Array.from(providerCheckboxes).map(cb => cb.value);
            saveFilterSelections();
            console.log('No providers selected. Auto-selected first provider:', selectedProviders[0]);
        }
    }

    console.log('Provider checkboxes found:', providerCheckboxes.length);
    console.log('Selected providers:', selectedProviders);
    console.log('Unique providers:', [...new Set(selectedProviders)]);

    if (selectedProviders.length === 0) {
        container.innerHTML = '<div style="padding: 50px; text-align: center;"><h3>Please select at least one provider</h3></div>';
        setUnifiedHeaderTitle('Select a Provider');
        return;
    }

    // Clear existing calendars
    calendarInstances.forEach(cal => cal.destroy());
    calendarInstances = [];
    container.innerHTML = '';

    // Get selected facilities
    const facilityCheckboxes = document.querySelectorAll('#facility-filter input[type="checkbox"]:checked');
    const selectedFacilities = Array.from(facilityCheckboxes).map(cb => cb.value);

    console.log('Selected facilities:', selectedFacilities);

    // Create calendars for each provider-facility combination
    selectedProviders.forEach(providerId => {
        const providerInfo = window.providerData[providerId];
        if (!providerInfo) {
            console.warn('Provider info not found for ID:', providerId);
            return;
        }

        // If no facilities selected, create one calendar for the provider (all facilities)
        if (selectedFacilities.length === 0) {
            createProviderCalendar(providerId, providerInfo, null, container, defaultView, savedDate,
                scheduleStart, scheduleEnd, slotMinTime, slotMaxTime, slotDuration, scrollTime,
                use24Hours, firstDayOfWeek, showWeekends, calendarInterval);
        } else {
            // Create a calendar for each facility
            selectedFacilities.forEach(facilityId => {
                const facilityInfo = window.facilityData ? window.facilityData[facilityId] : null;
                const facilityName = facilityInfo ? facilityInfo.name : 'Facility ' + facilityId;

                createProviderCalendar(providerId, providerInfo, facilityId, container, defaultView, savedDate,
                    scheduleStart, scheduleEnd, slotMinTime, slotMaxTime, slotDuration, scrollTime,
                    use24Hours, firstDayOfWeek, showWeekends, calendarInterval, facilityName);
            });
        }
    });

    console.log('Total calendars created:', calendarInstances.length);
    if (calendarInstances.length > 0) {
        updateUnifiedTitle();
        ensureCalendarEventStream();
    } else {
        setUnifiedHeaderTitle('No Calendars Available');
        stopCalendarEventStream();
    }
}

function createProviderCalendar(providerId, providerInfo, facilityId, container, defaultView, savedDate,
    scheduleStart, scheduleEnd, slotMinTime, slotMaxTime, slotDuration, scrollTime,
    use24Hours, firstDayOfWeek, showWeekends, calendarInterval, facilityName = null) {
    const targetProviderUserId = (providerInfo && providerInfo.id) ? parseInt(providerInfo.id, 10) : null;
    const targetFacilityId = facilityId ? parseInt(facilityId, 10) : null;
    const dragPolicy = window.medexDragPolicy || {
        role: 'secretary',
        canDragDrop: false,
        canDoubleBook: false,
        warnOnDoubleBook: false
    };
    const schedulingRules = window.medexSchedulingRules || {
        templateEnforcement: 'guideline',
        allowDoubleBooking: true
    };
    function normalizeTimeMs(value) {
        if (value === null || value === undefined) {
            return null;
        }
        if (typeof value === 'number') {
            return Number.isFinite(value) ? value : null;
        }
        if (value instanceof Date) {
            const ms = value.getTime();
            return Number.isFinite(ms) ? ms : null;
        }
        if (typeof value === 'string') {
            const parsed = Date.parse(value);
            return Number.isFinite(parsed) ? parsed : null;
        }
        if (value && typeof value.valueOf === 'function') {
            const primitive = value.valueOf();
            if (primitive !== value) {
                const primitiveMs = normalizeTimeMs(primitive);
                if (primitiveMs !== null) {
                    return primitiveMs;
                }
            }
        }
        if (value && typeof value.toISOString === 'function') {
            const parsedIso = Date.parse(value.toISOString());
            return Number.isFinite(parsedIso) ? parsedIso : null;
        }
        return null;
    }

    function toSortableTimeMs(ev) {
        if (!ev) {
            return 0;
        }

        const candidates = [
            ev.start,
            ev.startStr,
            ev.startMs,
            ev.eventRange && ev.eventRange.range ? ev.eventRange.range.start : null,
            ev.range ? ev.range.start : null,
            ev._instance && ev._instance.range ? ev._instance.range.start : null,
            (ev._instance && ev._instance.range && ev._instance.range.start && typeof ev._instance.range.start.valueOf === 'function')
                ? ev._instance.range.start.valueOf()
                : null
        ];

        for (let i = 0; i < candidates.length; i++) {
            const ms = normalizeTimeMs(candidates[i]);
            if (ms !== null) {
                return ms;
            }
        }

        return 0;
    }

    // Create wrapper div for this provider's calendar
    const calendarWrapper = document.createElement('div');
    calendarWrapper.className = 'provider-calendar-wrapper';

    // Add provider title (with facility if specified)
    const titleDiv = document.createElement('h3');
    if (facilityName) {
        titleDiv.textContent = providerInfo.name + ' - ' + facilityName;
    } else {
        titleDiv.textContent = providerInfo.name;
    }
    calendarWrapper.appendChild(titleDiv);

    // Create calendar div
    const calendarDiv = document.createElement('div');
    calendarDiv.className = 'provider-calendar';
    const calendarId = facilityId ? 'calendar-' + providerId + '-' + facilityId : 'calendar-' + providerId;
    calendarDiv.id = calendarId;
    calendarDiv.style.padding = '10px';
    calendarWrapper.appendChild(calendarDiv);

    container.appendChild(calendarWrapper);

    // Initialize calendar for this provider (and facility if specified)
    const calendar = new FullCalendar.Calendar(calendarDiv, {
        initialView: defaultView,
        initialDate: savedDate || undefined,
        firstDay: firstDayOfWeek,
            headerToolbar: false,  // Disable individual toolbars
            slotLabelFormat: {
                hour: 'numeric',
                minute: '2-digit',
                hour12: !use24Hours
            },
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                hour12: !use24Hours
            },
            slotMinTime: slotMinTime,
            slotMaxTime: slotMaxTime,
            slotDuration: slotDuration,
            slotLabelInterval: slotDuration,
            snapDuration: slotDuration,
            allDaySlot: false,
            nowIndicator: true,
            scrollTime: scrollTime,
            expandRows: true,
            eventOverlap: function(stillEvent, movingEvent) {
                // Template/open slots may always visually overlap patient appointments (two-lane display).
                const stillPatient  = parseInt((stillEvent.extendedProps  && stillEvent.extendedProps.patientId)  || 0, 10) || 0;
                const movingPatient = parseInt((movingEvent && movingEvent.extendedProps && movingEvent.extendedProps.patientId) || 0, 10) || 0;
                if (stillPatient <= 0 || movingPatient <= 0) { return true; }
                // Both are patient appointments — apply the double-booking rule.
                if (!schedulingRules.allowDoubleBooking) {
                    if (typeof window.showMedexStatusToast === 'function') {
                        window.showMedexStatusToast('Double-booking is not allowed', 'error', 2000);
                    }
                    return false;
                }
                return true;
            },
            eventsSet: function(events) {
                // Auto-tighten the grid when events shorter than calendarInterval are present.
                // Only applies to time-grid views; skip month/agenda views.
                const view = calendar ? calendar.view : null;
                if (!view || !view.type || !view.type.startsWith('timeGrid')) { return; }
                let minDurMin = calendarInterval;
                events.forEach(function(ev) {
                    if (!ev.start || !ev.end) { return; }
                    const durMin = Math.round((ev.end - ev.start) / 60000);
                    if (durMin >= 5 && durMin < minDurMin) { minDurMin = durMin; }
                });
                // Round down to nearest 5-minute boundary
                minDurMin = Math.max(5, Math.floor(minDurMin / 5) * 5);
                const target = '00:' + String(minDurMin).padStart(2, '0') + ':00';
                const current = calendar.getOption('slotDuration');
                if (target !== current) {
                    calendar.setOption('slotDuration', target);
                    calendar.setOption('slotLabelInterval', target);
                    calendar.setOption('snapDuration', target);
                }
            },
            eventOrder: function(a, b) {
                const aStartMs = toSortableTimeMs(a);
                const bStartMs = toSortableTimeMs(b);
                if (aStartMs !== bStartMs) {
                    return aStartMs - bStartMs;
                }

                const aPatient = parseInt((a.extendedProps && a.extendedProps.patientId) || 0, 10) || 0;
                const bPatient = parseInt((b.extendedProps && b.extendedProps.patientId) || 0, 10) || 0;
                const aIsSlot = aPatient <= 0;
                const bIsSlot = bPatient <= 0;

                // Month view should use a single chronological ordering lane,
                // not separate slot-first and appointment-second grouping.
                const viewType = (calendar && calendar.view && calendar.view.type) ? calendar.view.type : defaultView;
                if (viewType === 'dayGridMonth') {
                    if (aIsSlot !== bIsSlot) {
                        return aIsSlot ? 1 : -1;
                    }
                    const aTitle = String(a.title || '');
                    const bTitle = String(b.title || '');
                    return aTitle.localeCompare(bTitle);
                }

                if (aIsSlot !== bIsSlot) {
                    return aIsSlot ? -1 : 1;
                }
                return 0;
            },
            editable: !!dragPolicy.canDragDrop,
            selectable: true,
            droppable: !!dragPolicy.canDragDrop,
            selectMirror: true,
            dayMaxEvents: true,
            weekends: showWeekends,
            height: 'auto',
            navLinks: true,
            navLinkDayClick: function(date, jsEvent) {
                jsEvent.preventDefault();
                calendar.changeView('timeGridDay', date);
            },
            eventAllow: function(dropInfo, draggedEvent) {
                if (!dragPolicy.canDragDrop) {
                    return false;
                }
                if (!draggedEvent) {
                    return true;
                }

                const patientId = parseInt(draggedEvent.extendedProps.patientId || 0, 10) || 0;
                const isGeneratedSlot = !!draggedEvent.extendedProps.isGeneratedSlot;
                const isProviderAvailability = !!draggedEvent.extendedProps.isProviderAvailability;
                const isOpenSlotLike = !!draggedEvent.extendedProps.isOpenSlotLike;

                // Template/open availability slots are scheduling blueprint artifacts.
                // They are not movable from Full Calendar; move templates in Dashboard.
                if (patientId <= 0 || isGeneratedSlot || isProviderAvailability || isOpenSlotLike) {
                    return false;
                }

                // Template enforcement: check category match on the destination slot.
                // Guideline mode: block only when dragged appt has a category AND the slot has a different category.
                // Strict mode:    block also when the slot has a category but the appt does not (or vice-versa).
                const draggedCategoryId = parseInt(draggedEvent.extendedProps.preferredCategoryId || 0, 10) || 0;
                const dropStartMs = dropInfo && dropInfo.start ? dropInfo.start.getTime() : 0;
                const dropEndMs = dropInfo && dropInfo.end ? dropInfo.end.getTime() : 0;
                const isStrictEnforcement = schedulingRules.templateEnforcement === 'strict';
                if (dropStartMs > 0 && dropEndMs > dropStartMs) {
                    const overlappingSlot = calendar.getEvents().find(function(ev) {
                        if (!ev || ev.id === draggedEvent.id) { return false; }
                        const evProps = ev.extendedProps || {};
                        const evPatientId = parseInt(evProps.patientId || 0, 10) || 0;
                        const evIsTemplateLike = evPatientId <= 0 && (
                            !!evProps.isGeneratedSlot ||
                            !!evProps.isProviderAvailability ||
                            !!evProps.isOpenSlotLike
                        );
                        if (!evIsTemplateLike || !ev.start || !ev.end) { return false; }
                        const evStartMs = ev.start.getTime();
                        const evEndMs   = ev.end.getTime();
                        if (dropStartMs >= evEndMs || dropEndMs <= evStartMs) { return false; }
                        const slotCategoryId = parseInt(evProps.preferredCategoryId || 0, 10) || 0;
                        if (slotCategoryId <= 0) { return false; } // slot has no type → never block
                        if (isStrictEnforcement) {
                            // Strict: must match exactly; no category on appt is also a mismatch.
                            return draggedCategoryId !== slotCategoryId;
                        }
                        // Guideline: block only when appt has a category and it differs.
                        return draggedCategoryId > 0 && draggedCategoryId !== slotCategoryId;
                    });

                    if (overlappingSlot) {
                        if (typeof window.showMedexStatusToast === 'function') {
                            const msg = isStrictEnforcement
                                ? 'Cannot drop: templates are strictly enforced — appointment type must match slot type'
                                : 'Cannot drop: appointment type does not match slot type';
                            window.showMedexStatusToast(msg, 'error', 2200);
                        }
                        return false;
                    }
                }

                // Secretary policy: manual drag only for real patient appointments
                // that are category-typed; server enforces destination slot category match.
                if (dragPolicy.role === 'secretary') {
                    const preferredCategoryId = parseInt(draggedEvent.extendedProps.preferredCategoryId || 0, 10) || 0;
                    if (patientId <= 0) {
                        return false;
                    }
                    if (preferredCategoryId <= 0) {
                        return false;
                    }
                }

                return true;
            },
            eventClassNames: function(arg) {
                const category = arg.event.extendedProps.category || '';
                const patientId = arg.event.extendedProps.patientId;
                const slotTypeColor = arg.event.extendedProps.slotTypeColor || '';
                const start = arg.event.start;
                const end = arg.event.end;
                let duration = 15;

                if (start && end) {
                    duration = Math.round((end - start) / (1000 * 60));
                }

                const classes = [];

                if (category === 'In Office' || category === 'Out Of Office') {
                    classes.push('availability-block');
                }

                if (patientId) {
                    classes.push('patient-appointment');
                }

                const isGeneratedSlot = !!arg.event.extendedProps.isGeneratedSlot;
                const isProviderAvailability = !!arg.event.extendedProps.isProviderAvailability;
                const isOpenSlotLike = !!arg.event.extendedProps.isOpenSlotLike;
                if ((isGeneratedSlot || isProviderAvailability || isOpenSlotLike) && !patientId) {
                    classes.push('slot-anchor-chip');
                    classes.push('open-slot-chip');
                    if (duration < 20) {
                        classes.push('open-slot-chip-short');
                    } else {
                        classes.push('open-slot-chip-tall');
                    }
                }

                if (patientId) {
                    classes.push('appointment-second-chip');
                }

                // Add short-appointment class for appointments under 20 minutes
                if (duration < 20) {
                    classes.push('short-appointment');
                }
                if (slotTypeColor) {
                    classes.push('has-slot-type-color');
                }

                return classes;
            },
            events: function(info, successCallback, failureCallback) {
                const accent = cssColorToHex(getComputedStyle(document.body).getPropertyValue('--medex-accent'));
                const params = {
                    start: info.startStr,
                    end: info.endStr,
                    providers: providerId  // Filter by this specific provider (note: backend expects 'providers' not 'provider')
                };
                if (accent) {
                    params.fallback_color = accent;
                }

                // Add facility filter if specified
                if (facilityId) {
                    params.facilities = facilityId;
                }

                console.log('Fetching events for provider', providerId, 'facility', facilityId || 'all', 'with params:', params);

                fetchWithOpenEmrSession(webroot + '/interface/modules/custom_modules/oe-module-medex/public/calendar/get_events.php?' + new URLSearchParams(params))
                    .then(response => response.json())
                    .then(data => {
                        const selectedSlotStates = getSelectedSlotStateFilters();
                        const selectedCategoryIds = getSelectedAppointmentCategoryFilters();
                        const filteredData = Array.isArray(data)
                            ? data.filter((eventData) => shouldDisplayEventByFilters(eventData, selectedSlotStates, selectedCategoryIds))
                            : [];

                        console.log('Received', data.length, 'events for provider', providerId, 'after filters:', filteredData.length);
                        successCallback(filteredData);
                    })
                    .catch(error => {
                        console.error('Error fetching events for provider', providerId, ':', error);
                        failureCallback(error);
                    });
            },
            eventDidMount: function(info) {
                // Get patient info from event
                const patientId = info.event.extendedProps.patientId;
                const patientName = info.event.extendedProps.patientName;
                const status = info.event.extendedProps.status;
                const statusIcon = info.event.extendedProps.statusIcon;
                const apptStatusLabel = (info.event.extendedProps.apptStatusLabel || '').trim();
                const apptStatusColor = (info.event.extendedProps.apptStatusColor || '').trim();
                const reminderHistory = Array.isArray(info.event.extendedProps.reminderHistory) ? info.event.extendedProps.reminderHistory : [];
                const categoryLabel = (info.event.extendedProps.category || '').trim();
                const comments = (info.event.extendedProps.comments || '').replace(/\s+/g, ' ').trim();
                const slotTypeColor = info.event.extendedProps.slotTypeColor || '';
                const eventTitle = info.event.title;
                const slotVisualState = computeSlotVisualState(info.event);
                const slotVisualMeta = getSlotVisualMeta(slotVisualState);
                const rawSlotState = String(info.event.extendedProps.slotState || '').trim().toLowerCase();
                const hasReschedulableState = rawSlotState === 'held_staff'
                    || rawSlotState === 'held_patient'
                    || rawSlotState === 'consumed';

                // Calculate event duration to determine if it's a short appointment
                const start = info.event.start;
                const end = info.event.end;
                let duration = 15;
                if (start && end) {
                    duration = Math.round((end - start) / (1000 * 60));
                }

                let isOpenSlotChip = info.el.classList.contains('open-slot-chip');

                // Filled slot chips keep the same narrow chip-1 width as empty slots.
                // The patient appointment is rendered as a second chip in the right lane.

                if (isOpenSlotChip && (info.view.type === 'timeGridWeek' || info.view.type === 'timeGridDay')) {
                    const slotHarness = info.el.closest('.fc-timegrid-event-harness');
                    if (slotHarness) {
                        slotHarness.classList.add('slot-anchor-chip-harness');
                    }
                }

                // For short patient appointments (< 20 min) in timeGrid views, set nowrap on all elements.
                // Open-slot chips have dedicated CSS and should not receive these inline overrides.
                if (!isOpenSlotChip && duration < 20 && (info.view.type === 'timeGridWeek' || info.view.type === 'timeGridDay')) {
                    info.el.style.whiteSpace = 'nowrap';
                    info.el.style.overflow = 'hidden';
                    info.el.style.textOverflow = 'ellipsis';
                    info.el.style.padding = '0 1px'; // Minimal padding on the event itself to maximize space

                    // Force flex layout for the main content area to put time and title side-by-side
                    const eventMain = info.el.querySelector('.fc-event-main');
                    if (eventMain) {
                        eventMain.style.display = 'flex';
                        eventMain.style.flexDirection = 'row';
                        eventMain.style.alignItems = 'center';
                        eventMain.style.padding = '0 2px';
                        eventMain.style.overflow = 'hidden';
                        eventMain.style.height = '100%'; // Ensure full height

                        // Adjust font size and line height for tight spaces
                        eventMain.style.fontSize = '0.85em';
                        eventMain.style.lineHeight = '1.1';
                    }

                    const titleContainer = info.el.querySelector('.fc-event-title-container');
                    if (titleContainer) {
                        titleContainer.style.whiteSpace = 'nowrap';
                        titleContainer.style.overflow = 'hidden';
                        titleContainer.style.textOverflow = 'ellipsis';
                        titleContainer.style.display = 'block'; // Let it initiate block formatting context inside flex item
                        titleContainer.style.flex = '1'; // Grow to fill space
                        titleContainer.style.minWidth = '0'; // Allow text truncation inside flex item
                    }

                    const timeEl = info.el.querySelector('.fc-event-time');
                    if (timeEl) {
                        timeEl.style.display = 'inline-block';
                        timeEl.style.whiteSpace = 'nowrap';
                        timeEl.style.marginRight = '4px';
                        timeEl.style.flexShrink = '0'; // Don't shrink the time
                        timeEl.style.fontSize = '0.9em'; // Slightly smaller time
                        timeEl.style.fontWeight = 'bold'; // Make time stand out
                    }
                }

                // Keep original slot-type visible as a colored time strip, even if
                // event/status colors change later. Fall back to the event's own
                // backgroundColor for open-slot chips that lack a slotTypeColor.
                const effectiveSlotColor = slotTypeColor
                    || (isOpenSlotChip ? (info.event.backgroundColor || '') : '');
                if (effectiveSlotColor) {
                    info.el.style.setProperty('--medex-slot-type-color', effectiveSlotColor);
                    const timeEl = info.el.querySelector('.fc-event-time');
                    if (timeEl) {
                        timeEl.style.backgroundColor = effectiveSlotColor;
                        timeEl.style.borderRadius = '3px';
                        timeEl.style.padding = '0 4px';
                        timeEl.style.marginRight = '6px';
                        const r = parseInt(effectiveSlotColor.slice(1, 3), 16);
                        const g = parseInt(effectiveSlotColor.slice(3, 5), 16);
                        const b = parseInt(effectiveSlotColor.slice(5, 7), 16);
                        const luminance = ((r * 299) + (g * 587) + (b * 114)) / 1000;
                        timeEl.style.color = luminance >= 145 ? '#111111' : '#ffffff';
                    }
                }

                // For patient appointments and non-chip events, pin the colors from the
                // event data so the active OpenEMR theme (solar, etc.) cannot win.
                if (!isOpenSlotChip && !info.el.classList.contains('availability-block')) {
                    const evBg = info.event.backgroundColor || '';
                    const evBorder = info.event.borderColor || evBg;
                    const evText = info.event.textColor || '';
                    if (evBg) {
                        info.el.style.setProperty('background-color', evBg, 'important');
                        info.el.style.setProperty('border-color', evBorder || evBg, 'important');
                    }
                    if (evText) {
                        info.el.style.setProperty('color', evText, 'important');
                    }
                }

                // Render compact state badges on open-slot chips (Chip 1) only.
                // Always show the badge regardless of state so staff can see FILLED/OPEN at a glance.
                const shouldShowSlotStateBadge = isOpenSlotChip;
                if (shouldShowSlotStateBadge && !info.el.querySelector('.slot-state-indicator')) {
                    info.el.classList.add('slot-state-' + slotVisualState);
                    info.el.classList.add('has-slot-state-indicator');
                    const stateBadge = document.createElement('span');
                    stateBadge.className = 'slot-state-indicator slot-state-indicator--' + slotVisualState;
                    stateBadge.textContent = slotVisualMeta.short;
                    stateBadge.setAttribute('aria-label', slotVisualMeta.label);
                    stateBadge.setAttribute('title', 'Slot state: ' + slotVisualMeta.label);
                    const badgeHost = info.el.querySelector('.fc-event-main')
                        || info.el.querySelector('.fc-event-title-container')
                        || info.el;
                    badgeHost.appendChild(stateBadge);
                } else if (!shouldShowSlotStateBadge) {
                    info.el.classList.remove('has-slot-state-indicator');
                }


                if (isOpenSlotChip) {
                    const slotTitleEl = info.el.querySelector('.fc-event-title');
                    if (slotTitleEl) {
                        requestAnimationFrame(function() {
                            slotTitleEl.textContent = eventTitle;
                            slotTitleEl.style.whiteSpace = 'normal';
                            slotTitleEl.style.overflow = 'visible';
                            slotTitleEl.style.textOverflow = 'clip';
                            slotTitleEl.style.overflowWrap = 'break-word';
                            slotTitleEl.style.wordBreak = 'normal';
                        });
                    }
                }

                // Modern dark tooltip — replaces native browser title tooltip.
                {
                    const tipEl = ensureModernTooltipElement();
                    if (tipEl) {
                        const tipLines = [];
                        if (eventTitle) {
                            tipLines.push({ text: eventTitle, bold: true, size: '13px' });
                        }
                        if (start && end) {
                            const fmt = function(d) {
                                return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                            };
                            tipLines.push({ text: fmt(start) + ' – ' + fmt(end), size: '11px' });
                        }
                        if (categoryLabel) {
                            tipLines.push({ text: 'Category: ' + categoryLabel });
                        }
                        if (comments) {
                            tipLines.push({ text: comments, muted: true });
                        }
                        if (shouldShowSlotStateBadge && slotVisualMeta && slotVisualMeta.label) {
                            tipLines.push({ text: 'Slot: ' + slotVisualMeta.label, muted: true });
                        }

                        // Reminder message progress from medex_outgoing (synced from MedEx API).
                        if (patientId && reminderHistory.length > 0) {
                            tipLines.push({ text: 'Reminders:', bold: true, size: '11px' });
                            reminderHistory.forEach(function(r) {
                                const type = String(r.type || '').toUpperCase();
                                const reply = String(r.reply || '');
                                const dateStr = r.date ? new Date(r.date.replace(' ', 'T')).toLocaleString([], { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '';
                                const progress = String(r.progress || '').trim();
                                let line = type + ': ' + reply;
                                if (dateStr) { line += ' (' + dateStr + ')'; }
                                if (progress) { line += ' — ' + progress; }
                                tipLines.push({ text: line, muted: true });
                            });
                        }
                        if (slotVisualState === 'held_patient') {
                            const holdExpires = String(info.event.extendedProps.holdExpiresAt || '').trim();
                            const heldByRef = String(info.event.extendedProps.heldByRef || '').trim();
                            if (holdExpires) {
                                const expDate = new Date(holdExpires.replace(' ', 'T'));
                                const expStr = isNaN(expDate.getTime())
                                    ? holdExpires
                                    : expDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + ' ' + expDate.toLocaleDateString([], { month: 'short', day: 'numeric' });
                                tipLines.push({ text: 'Hold expires: ' + expStr, muted: true });
                            }
                            if (heldByRef && heldByRef !== 'deleted_appt_recovery') {
                                tipLines.push({ text: 'Held for: ' + heldByRef, muted: true });
                            }
                        }

                        if (tipLines.length > 0) {
                            const buildTip = function() {
                                tipEl.innerHTML = '';
                                tipLines.forEach(function(line, i) {
                                    if (i > 0) { tipEl.appendChild(document.createElement('br')); }
                                    const span = document.createElement('span');
                                    span.textContent = line.text;
                                    if (line.bold) { span.style.fontWeight = '700'; }
                                    if (line.size) { span.style.fontSize = line.size; }
                                    if (line.muted) { span.style.color = '#a8d4f5'; span.style.fontSize = '11px'; }
                                    tipEl.appendChild(span);
                                });
                            };
                            const posTip = function() {
                                const rect = info.el.getBoundingClientRect();
                                const tipRect = tipEl.getBoundingClientRect();
                                const gap = 10;
                                let top = rect.top - tipRect.height - gap;
                                if (top < 8) { top = rect.bottom + gap; }
                                let left = rect.left + rect.width / 2 - tipRect.width / 2;
                                left = Math.max(8, Math.min(left, window.innerWidth - tipRect.width - 8));
                                tipEl.style.top = top + 'px';
                                tipEl.style.left = left + 'px';
                            };
                            info.el.addEventListener('mouseenter', function() {
                                buildTip();
                                tipEl.classList.add('show');
                                tipEl.setAttribute('aria-hidden', 'false');
                                posTip();
                            });
                            info.el.addEventListener('mousemove', posTip);
                            info.el.addEventListener('mouseleave', function() {
                                tipEl.classList.remove('show');
                                tipEl.setAttribute('aria-hidden', 'true');
                            });
                        }
                    }
                }

                if (patientId && patientName) {
                    if (typeof medexDebug !== 'undefined' && medexDebug) {
                        console.log('[MedEx Calendar Debug]', medexDebug);
                    }

                    const rawStatus = (info.event.extendedProps.status || '').trim();

                    // ── STATUS BADGE: top-left, BEFORE the time element ─────────────────────
                    // pc_apptstatus drives this — staff set it manually, MedEx sets SMS/EMAIL/AVM/CALL.
                    // Build a compact pill and insert it as the first child of .fc-event-main-frame.
                    if (rawStatus && rawStatus !== '-') {
                        const frame = info.el.querySelector('.fc-event-main-frame') || info.el.querySelector('.fc-event-main') || info.el;
                        if (frame && !frame.querySelector('.medex-appt-status-badge')) {
                            const badge = document.createElement('span');
                            badge.className = 'medex-appt-status-badge';

                            // For modality codes show short label; for staff codes use apptStatusLabel.
                            const modalityShort = { 'SMS': 'SMS', 'EMAIL': 'Email', 'AVM': 'AVM', 'CALL': 'Call' };
                            const badgeText = modalityShort[rawStatus] || apptStatusLabel || rawStatus;
                            badge.textContent = badgeText;
                            badge.title = apptStatusLabel || rawStatus;

                            if (apptStatusColor) {
                                badge.style.backgroundColor = apptStatusColor;
                                const hexVal = parseInt(apptStatusColor.replace('#', ''), 16);
                                const r2 = (hexVal >> 16) & 0xff, g2 = (hexVal >> 8) & 0xff, b2 = hexVal & 0xff;
                                badge.style.color = ((r2 * 299 + g2 * 587 + b2 * 114) / 1000) >= 145 ? '#111' : '#fff';
                            }

                            frame.insertBefore(badge, frame.firstChild);
                        }
                    }

                    // ── TITLE CONTENT: name + modality icon + category + comments ────────────
                    // event.title is '' for patient appointments so FC renders nothing; we own this.
                    const titleEl = info.el.querySelector('.fc-event-title');
                    if (titleEl) {
                        titleEl.innerHTML = '';

                        // 1. Clickable patient name
                        const patientLink = document.createElement('span');
                        patientLink.className = 'patient-name-link';
                        patientLink.textContent = patientName;
                        patientLink.style.cssText = 'cursor:pointer;text-decoration:underline;font-weight:bold;';
                        patientLink.title = 'Click to view patient dashboard';
                        patientLink.addEventListener('click', function(e) {
                            e.stopPropagation();
                            if (top.restoreSession) { top.restoreSession(); }
                            if (top.RTop) {
                                top.RTop.location = webroot + '/interface/patient_file/summary/demographics.php?set_pid=' + encodeURIComponent(patientId);
                            } else {
                                window.open(webroot + '/interface/patient_file/summary/demographics.php?set_pid=' + encodeURIComponent(patientId), '_blank');
                            }
                        });
                        titleEl.appendChild(patientLink);

                        // 2. Modality icon inline (SMS/EMAIL/AVM/CALL only — from medex_icons HTML)
                        if (statusIcon && ['SMS','EMAIL','AVM','CALL'].indexOf(rawStatus) !== -1) {
                            const tmp = document.createElement('div');
                            tmp.innerHTML = statusIcon;
                            const btnEl = tmp.querySelector('.btn') || tmp.firstElementChild;
                            const iEl   = tmp.querySelector('i');
                            const bgColor = (btnEl && btnEl.style.backgroundColor) ? btnEl.style.backgroundColor : 'green';
                            const iconTitle = (btnEl && btnEl.getAttribute('title')) ? btnEl.getAttribute('title') : rawStatus;

                            const pill = document.createElement('span');
                            pill.className = 'medex-status-pill';
                            pill.title = iconTitle;
                            pill.style.cssText = 'display:inline-flex;align-items:center;gap:2px;padding:1px 4px;border-radius:3px;font-size:10px;line-height:1.4;vertical-align:middle;background:' + bgColor + ';color:#fff;margin-left:3px;';
                            if (iEl) {
                                const icon = iEl.cloneNode(true);
                                icon.style.fontSize = '10px';
                                pill.appendChild(icon);
                            } else {
                                pill.textContent = rawStatus;
                            }
                            titleEl.appendChild(pill);
                        }

                        // 3. Appointment category — always show so staff see what type was booked
                        //    (admins may book any type into any slot; this makes mismatches visible)
                        if (categoryLabel) {
                            const catSpan = document.createElement('span');
                            catSpan.className = 'appointment-category-inline';
                            catSpan.textContent = ' – ' + categoryLabel;
                            catSpan.style.cssText = 'opacity:0.85;font-size:0.9em;';
                            titleEl.appendChild(catSpan);
                        }

                        // 4. Comments / reason (pc_hometext)
                        if (comments) {
                            const reasonSpan = document.createElement('span');
                            reasonSpan.className = 'appointment-reason-inline';
                            reasonSpan.textContent = ' – ' + comments;
                            reasonSpan.style.cssText = 'opacity:0.7;font-size:0.85em;font-style:italic;';
                            titleEl.appendChild(reasonSpan);
                        }
                    }
                }

                // Position patient appointments in the right lane beside the slot chip.
                // 1 patient  → CSS class handles it (full right lane).
                // 2+ patients → sub-divide the right lane inline so chip stays narrow.
                if (patientId && (info.view.type === 'timeGridWeek' || info.view.type === 'timeGridDay') && start && end) {
                    const apptStartMs = start.getTime();
                    const apptEndMs = end.getTime();
                    const allEvents = info.view.calendar.getEvents();
                    let hasSlotAnchorOverlap = false;
                    const overlappingPatientIds = [];
                    allEvents.forEach(function(ev) {
                        if (!ev || !ev.start || !ev.end) { return; }
                        if (ev.id === info.event.id) { return; }
                        const evProps = ev.extendedProps || {};
                        const evPatientId = parseInt(evProps.patientId || 0, 10) || 0;
                        const evStartMs = ev.start.getTime();
                        const evEndMs = ev.end.getTime();
                        if (apptStartMs >= evEndMs || apptEndMs <= evStartMs) { return; }
                        if (evPatientId <= 0 && (evProps.isGeneratedSlot || evProps.isProviderAvailability || evProps.isOpenSlotLike)) {
                            hasSlotAnchorOverlap = true;
                        }
                        if (evPatientId > 0) { overlappingPatientIds.push(ev.id); }
                    });
                    // Include self
                    overlappingPatientIds.push(info.event.id);
                    overlappingPatientIds.sort();

                    if (hasSlotAnchorOverlap) {
                        info.el.classList.add('has-slot-anchor-overlap');
                        const apptHarness = info.el.closest('.fc-timegrid-event-harness');
                        if (apptHarness) {
                            const N = overlappingPatientIds.length;
                            if (N === 1) {
                                apptHarness.classList.add('appointment-second-chip-harness');
                            } else {
                                // Sub-divide right lane (84% after the 16% chip)
                                const chipPct = 16;
                                const myIndex = overlappingPatientIds.indexOf(info.event.id);
                                const slotPct = (100 - chipPct) / N;
                                const leftPct = chipPct + myIndex * slotPct;
                                apptHarness.style.setProperty('left', leftPct.toFixed(2) + '%', 'important');
                                apptHarness.style.setProperty('right', 'auto', 'important');
                                apptHarness.style.setProperty('width', 'calc(' + slotPct.toFixed(2) + '% - 2px)', 'important');
                                apptHarness.style.setProperty('max-width', 'calc(' + slotPct.toFixed(2) + '% - 2px)', 'important');
                            }
                        }
                    }
                }

                // For the same overlapped windows, pin slot anchors to the left lane —
                // but only when there is exactly 1 patient in the window.  With 2+ patients
                // skip the forced harness so FullCalendar lays out all events naturally.
                if (!patientId && (info.view.type === 'timeGridWeek' || info.view.type === 'timeGridDay') && start && end) {
                    const slotStartMs = start.getTime();
                    const slotEndMs = end.getTime();
                    const isSlotAnchor = !!info.event.extendedProps.isGeneratedSlot
                        || !!info.event.extendedProps.isProviderAvailability
                        || !!info.event.extendedProps.isOpenSlotLike;
                    if (isSlotAnchor) {
                        let overlappingPatientCount = 0;
                        info.view.calendar.getEvents().forEach(function(ev) {
                            if (!ev || ev.id === info.event.id || !ev.start || !ev.end) { return; }
                            const evPatientId = parseInt((ev.extendedProps && ev.extendedProps.patientId) || 0, 10) || 0;
                            if (evPatientId <= 0) { return; }
                            const evStartMs = ev.start.getTime();
                            const evEndMs = ev.end.getTime();
                            if (slotStartMs < evEndMs && slotEndMs > evStartMs) { overlappingPatientCount++; }
                        });

                        if (overlappingPatientCount >= 1) {
                            info.el.classList.add('slot-anchor-overlapped');
                            const slotHarness = info.el.closest('.fc-timegrid-event-harness');
                            if (slotHarness) {
                                slotHarness.classList.add('slot-anchor-overlap-harness');
                            }
                        }
                    }
                }
            },
            eventClick: function(info) {
                const eventId = info.event.id;
                const start = info.event.start;
                const end = info.event.end;
                const props = info.event.extendedProps || {};
                let duration = 15;

                if (start && end) {
                    duration = Math.round((end - start) / (1000 * 60));
                }

                const isGeneratedSlot = !!props.isGeneratedSlot;
                const patientId = props.patientId;
                const preferredCategoryId = parseInt(props.preferredCategoryId || 0, 10) || 0;
                const providerUserId = (providerInfo && providerInfo.id) ? providerInfo.id : providerId;

                console.log('Event clicked:', eventId, 'duration:', duration, 'isGeneratedSlot:', isGeneratedSlot);

                let url = '';
                if (isGeneratedSlot && !patientId && start) {
                    const year = start.getFullYear();
                    const month = String(start.getMonth() + 1).padStart(2, '0');
                    const day = String(start.getDate()).padStart(2, '0');
                    const dateStr = year + month + day;
                    const hour = start.getHours();
                    const minute = String(start.getMinutes()).padStart(2, '0');

                    // Clicking a generated open slot should create a new appointment
                    // pre-seeded to that slot's category+duration.
                    url = webroot + '/interface/modules/custom_modules/oe-module-medex/public/calendar/edit_event_wrapper.php?date=' + encodeURIComponent(dateStr) +
                        '&starttimeh=' + encodeURIComponent(hour) +
                        '&starttimem=' + encodeURIComponent(minute) +
                        '&userid=' + encodeURIComponent(providerUserId) +
                        '&duration=' + encodeURIComponent(duration);
                    if (preferredCategoryId > 0) {
                        url += '&catid=' + encodeURIComponent(preferredCategoryId);
                    }
                } else {
                    url = webroot + '/interface/modules/custom_modules/oe-module-medex/public/calendar/edit_event_wrapper.php?eid=' + encodeURIComponent(eventId) + '&duration=' + encodeURIComponent(duration);
                }

                // Use a single shared callback that refreshes all calendars
                const callbackName = 'medexRefreshAllCalendars';
                if (!window[callbackName]) {
                    window[callbackName] = function() {
                        console.log('Dialog closed, refreshing all calendar instances');
                        calendarInstances.forEach(cal => {
                            cal.refetchEvents();
                        });
                        notifyCalendarDataChanged();
                    };
                }

                if (window.parent && typeof window.parent.dlgopen !== 'undefined') {
                    window.parent.dlgopen(url, '_blank', 850, 600, '', '', {
                        onClosed: callbackName
                    });
                } else if (typeof dlgopen !== 'undefined') {
                    dlgopen(url, '_blank', 850, 600, '', '', {
                        onClosed: callbackName
                    });
                } else {
                    window.open(url, '_blank', 'width=850,height=600');
                }
            },
            select: function(info) {
                const start = info.start;
                const end = info.end;

                const year = start.getFullYear();
                const month = String(start.getMonth() + 1).padStart(2, '0');
                const day = String(start.getDate()).padStart(2, '0');
                const dateStr = year + month + day;
                const hour = start.getHours();
                const minute = String(start.getMinutes()).padStart(2, '0');

                // Compute selected duration in minutes; fall back to one calendar slot
                let duration = calendarInterval;
                if (start && end) {
                    duration = Math.round((end - start) / (1000 * 60));
                    if (duration <= 0) duration = calendarInterval;
                }

                // OpenEMR add_edit_event.php expects numeric userid; use provider record id.
                const providerUserId = (providerInfo && providerInfo.id) ? providerInfo.id : providerId;

                // Route through wrapper so duration is injected into form_duration
                const url = webroot + '/interface/modules/custom_modules/oe-module-medex/public/calendar/edit_event_wrapper.php?date=' + encodeURIComponent(dateStr) +
                           '&starttimeh=' + encodeURIComponent(hour) +
                           '&starttimem=' + encodeURIComponent(minute) +
                           '&userid=' + encodeURIComponent(providerUserId) +
                           '&duration=' + encodeURIComponent(duration);

                // Use a single shared callback that refreshes all calendars
                const callbackName = 'medexRefreshAllCalendars';
                if (!window[callbackName]) {
                    window[callbackName] = function() {
                        console.log('Dialog closed, refreshing all calendar instances');
                        calendarInstances.forEach(cal => {
                            cal.refetchEvents();
                        });
                        notifyCalendarDataChanged();
                    };
                }

                if (window.parent && typeof window.parent.dlgopen !== 'undefined') {
                    window.parent.dlgopen(url, '_blank', 850, 600, '', '', {
                        onClosed: callbackName
                    });
                } else if (typeof dlgopen !== 'undefined') {
                    dlgopen(url, '_blank', 850, 600, '', '', {
                        onClosed: callbackName
                    });
                } else {
                    window.open(url, '_blank', 'width=850,height=600');
                }

                calendar.unselect();
            },
            eventDrop: function(info) {
                updateEventTime(info, calendar, targetProviderUserId, targetFacilityId, { mode: 'drop' });
            },
            eventResize: function(info) {
                updateEventTime(info, calendar, targetProviderUserId, targetFacilityId, { mode: 'resize' });
            },
            eventReceive: function(info) {
                updateEventTime(info, calendar, targetProviderUserId, targetFacilityId, { mode: 'receive' });
            },
            datesSet: function(info) {
                // Save the current view preference and date
                const currentView = info.view.type;
                const currentDate = calendar.getDate();
                localStorage.setItem('medexCalendarView', currentView);
                localStorage.setItem('medexCalendarDate', currentDate.toISOString());
                console.log('Saved calendar view preference:', currentView);
                console.log('Saved calendar date preference:', currentDate.toISOString());

                // Update unified toolbar title
                const titleEl = document.getElementById('unified-title');
                if (titleEl) {
                    titleEl.textContent = info.view.title;
                }

                // Update date picker input to match current date
                const datePicker = document.getElementById('calendar-date-picker');
                if (datePicker) {
                    const year = currentDate.getFullYear();
                    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                    const day = String(currentDate.getDate()).padStart(2, '0');
                    const formattedDate = `${year}-${month}-${day}`;
                    if (datePicker.value !== formattedDate) {
                        datePicker.value = formattedDate;
                    }
                }

                // Update view button active state
                updateViewButtonState(currentView);

                // Sync all calendars to the same date range and view when one navigates
                calendarInstances.forEach(otherCalendar => {
                    if (otherCalendar !== calendar) {
                        const otherDate = otherCalendar.getDate();
                        const newDate = calendar.getDate();
                        if (otherDate.getTime() !== newDate.getTime()) {
                            otherCalendar.gotoDate(newDate);
                        }
                        // Also sync the view
                        if (otherCalendar.view.type !== currentView) {
                            otherCalendar.changeView(currentView);
                        }
                    }
                });
                ensureCalendarEventStream();
            }
        });

    calendar.render();
    calendarInstances.push(calendar);

    console.log('Calendar rendered for provider:', providerId, 'facility:', facilityId || 'all');
}

function updateEventTime(info, calendar, providerUserId, facilityId, options = {}) {
    const eventId = info.event.id;
    const newStart = info.event.start;
    const newEnd = info.event.end || info.event.start;
    const originalProviderId = info.event.extendedProps.providerId;

    console.log('Updating event', eventId, 'for provider', providerUserId, '- new start:', newStart, 'new end:', newEnd);

    // Prevent duplicate update submissions when FullCalendar emits multiple callbacks
    // for the same cross-calendar move.
    const startStr = formatLocalDateTime(newStart);
    const endStr = formatLocalDateTime(newEnd);
    const signature = [
        String(eventId),
        startStr,
        endStr,
        String(providerUserId || ''),
        String(facilityId || '')
    ].join('|');
    const now = Date.now();
    const lastSeenAt = recentMoveSignatures.get(signature) || 0;
    if (now - lastSeenAt < 1500) {
        console.log('Skipping duplicate move update:', signature);
        if (options.mode === 'receive' && info.event) {
            info.event.remove();
            refetchAllCalendars('dedupe-receive');
        }
        return;
    }
    recentMoveSignatures.set(signature, now);
    // Keep map small over time
    if (recentMoveSignatures.size > 200) {
        for (const [sig, ts] of recentMoveSignatures.entries()) {
            if (now - ts > 30000) {
                recentMoveSignatures.delete(sig);
            }
        }
    }

    const params = {
        eid: eventId,
        start: startStr,
        end: endStr
    };
    if (providerUserId) {
        params.provider = providerUserId;
    }
    if (facilityId) {
        params.facility = facilityId;
    }

    fetchWithOpenEmrSession(webroot + '/interface/modules/custom_modules/oe-module-medex/public/calendar/update_event.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(params)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Event updated successfully for provider', providerUserId);
            const hasWarning = !!(data.warning);
            if (typeof window.showMedexStatusToast === 'function') {
                window.showMedexStatusToast(hasWarning ? 'Updated with warning' : 'Appointment updated', hasWarning ? 'error' : 'success', hasWarning ? 2800 : 1600);
            }
            if (hasWarning) {
                alert(data.warning);
            }
            if (options.mode === 'receive' && info.event) {
                // Remove transient dragged copy before refetching canonical data.
                info.event.remove();
            }
            refetchAllCalendars('event-updated');
            notifyCalendarDataChanged();
        } else {
            console.error('Error updating event:', data.error);
            if (typeof window.showMedexStatusToast === 'function') {
                window.showMedexStatusToast('Update failed', 'error', 2000);
            }
            alert('Error updating appointment: ' + (data.error || 'Unknown error'));
            if (typeof info.revert === 'function') {
                info.revert();
            }
        }
    })
    .catch(error => {
        console.error('Error updating event for provider', providerUserId, ':', error);
        if (typeof window.showMedexStatusToast === 'function') {
            window.showMedexStatusToast('Update failed', 'error', 2000);
        }
        alert('Error updating appointment: ' + error);
        if (typeof info.revert === 'function') {
            info.revert();
        }
    });
}

function formatLocalDateTime(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const seconds = String(date.getSeconds()).padStart(2, '0');
    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

// Unified toolbar navigation functions
function navigateAllCalendars(action) {
    if (calendarInstances.length === 0) return;

    const firstCalendar = calendarInstances[0];
    if (action === 'prev') {
        firstCalendar.prev();
    } else if (action === 'next') {
        firstCalendar.next();
    } else if (action === 'today') {
        firstCalendar.today();
    }
    // Other calendars will sync via datesSet callback
}

function changeAllCalendarsView(viewType) {
    calendarInstances.forEach(calendar => {
        calendar.changeView(viewType);
    });
    updateViewButtonState(viewType);
}

function updateUnifiedTitle() {
    if (calendarInstances.length === 0) return;

    const firstCalendar = calendarInstances[0];
    const view = firstCalendar.view;
    const titleEl = document.getElementById('unified-title');

    if (titleEl && view) {
        titleEl.textContent = view.title;
    }
}

function updateViewButtonState(currentView) {
    // Remove active state from all buttons
    document.querySelectorAll('.fc-header-toolbar .fc-button').forEach(btn => {
        btn.classList.remove('fc-button-active');
    });

    // Add active state to current view button
    let activeButton;
    if (currentView === 'dayGridMonth') {
        activeButton = document.getElementById('btn-month');
    } else if (currentView === 'timeGridWeek') {
        activeButton = document.getElementById('btn-week');
    } else if (currentView === 'timeGridDay') {
        activeButton = document.getElementById('btn-day');
    }

    if (activeButton) {
        activeButton.classList.add('fc-button-active');
    }
}

// Single initialization method
console.log('Setting up event listeners...');

// Initialize once when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, restoring filters and initializing calendars...');
    restoreSidebarSectionStates();
    bindSidebarSectionStatePersistence();
    restoreFilterSelections();
    bindFilterBulkActions();
    initializeCalendars();
    startAutoRefresh();

    // Listen to checkbox changes in provider filter (auto-apply)
    const providerFilter = document.getElementById('provider-filter');
    if (providerFilter) {
        providerFilter.addEventListener('change', function(e) {
            if (e.target.type === 'checkbox') {
                console.log('Provider selection changed, saving and reinitializing calendars...');
                saveFilterSelections();
                initializeCalendars();
            }
        });
    }

    // Listen to checkbox changes in facility filter (auto-apply)
    const facilityFilter = document.getElementById('facility-filter');
    if (facilityFilter) {
        facilityFilter.addEventListener('change', function(e) {
            if (e.target.type === 'checkbox') {
                console.log('Facility selection changed, saving and reinitializing calendars...');
                saveFilterSelections();
                initializeCalendars();
            }
        });
    }

    const slotStateFilter = document.getElementById('slot-state-filter');
    if (slotStateFilter) {
        slotStateFilter.addEventListener('change', function(e) {
            if (e.target.type === 'checkbox') {
                // OPEN (open_not_reschedulable) implies staff-held slots are also visible:
                // auto-select open_reschedulable_available and held_staff alongside it.
                if (e.target.value === 'open_not_reschedulable' && e.target.checked) {
                    ['open_reschedulable_available', 'held_staff'].forEach(function(v) {
                        const cb = slotStateFilter.querySelector('input[type="checkbox"][value="' + v + '"]');
                        if (cb && !cb.checked) { cb.checked = true; }
                    });
                }
                // Selecting held_staff alone also implies open_reschedulable_available.
                if (e.target.value === 'held_staff' && e.target.checked) {
                    const rbotCheckbox = slotStateFilter.querySelector('input[type="checkbox"][value="open_reschedulable_available"]');
                    if (rbotCheckbox && !rbotCheckbox.checked) { rbotCheckbox.checked = true; }
                }
                saveFilterSelections();
                refetchAllCalendars('slot-state-filter-change', true);
            }
        });
    }

    const appointmentCategoryFilter = document.getElementById('appointment-category-filter');
    if (appointmentCategoryFilter) {
        appointmentCategoryFilter.addEventListener('change', function(e) {
            if (e.target.type === 'checkbox') {
                saveFilterSelections();
                refetchAllCalendars('appointment-category-filter-change', true);
            }
        });
    }

    // Listen to date picker changes
    const datePicker = document.getElementById('calendar-date-picker');
    if (datePicker) {
        datePicker.addEventListener('change', function(e) {
            const selectedDate = e.target.value;
            if (selectedDate && calendarInstances.length > 0) {
                console.log('Date picker changed to:', selectedDate);
                // Use the first calendar to navigate, datesSet will sync others
                calendarInstances[0].gotoDate(selectedDate);
            }
        });
    }

    // Refresh when user returns to this tab/window.
    window.addEventListener('focus', function() {
        ensureCalendarEventStream();
        refetchAllCalendars('window-focus');
    });

    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            ensureCalendarEventStream();
            refetchAllCalendars('tab-visible');
        } else {
            stopCalendarEventStream();
        }
    });

    // Refresh when another MedEx calendar tab/window makes a change.
    window.addEventListener('storage', function(e) {
        if (e.key === 'medexCalendarLastMutation' && e.newValue) {
            refetchAllCalendars('external-mutation');
        }
    });

    window.addEventListener('beforeunload', function() {
        stopCalendarEventStream();
    });
});

// Save filter selections to localStorage
function saveFilterSelections() {
    const providers = Array.from(document.querySelectorAll('#provider-filter input[type="checkbox"]:checked')).map(cb => cb.value);
    const facilities = Array.from(document.querySelectorAll('#facility-filter input[type="checkbox"]:checked')).map(cb => cb.value);
    const slotStates = Array.from(document.querySelectorAll('#slot-state-filter input[type="checkbox"]:checked')).map(cb => cb.value);
    const appointmentCategoryIds = Array.from(document.querySelectorAll('#appointment-category-filter input[type="checkbox"]:checked')).map(cb => cb.value);
    localStorage.setItem('medexSelectedProviders', JSON.stringify(providers));
    localStorage.setItem('medexSelectedFacilities', JSON.stringify(facilities));
    localStorage.setItem('medexSelectedSlotStates', JSON.stringify(slotStates));
    localStorage.setItem('medexSelectedAppointmentCategoryIds', JSON.stringify(appointmentCategoryIds));
    console.log('Saved filter selections - providers:', providers, 'facilities:', facilities, 'slot states:', slotStates, 'appointment categories:', appointmentCategoryIds);
}

// Restore filter selections from URL parameters (priority) or localStorage
function restoreFilterSelections() {
    // Parse URL parameters
    const urlParams = new URLSearchParams(window.location.search);

    const readStoredArray = (key) => {
        try {
            const parsed = JSON.parse(localStorage.getItem(key) || '[]');
            return Array.isArray(parsed) ? parsed.map(String).filter(Boolean) : [];
        } catch (e) {
            return [];
        }
    };

    // Restore date from URL or localStorage
    const urlDate = urlParams.get('date');
    if (urlDate) {
        localStorage.setItem('medexCalendarDate', urlDate);
        const datePicker = document.getElementById('calendar-date-picker');
        if (datePicker) {
            datePicker.value = urlDate;
        }
        console.log('Restored date from URL:', urlDate);
    }

    // Restore view from URL or localStorage
    const urlView = urlParams.get('view');
    if (urlView) {
        // Map OpenEMR view names to FullCalendar view names
        const viewMapping = {
            'day': 'timeGridDay',
            'week': 'timeGridWeek',
            'month': 'dayGridMonth',
            'year': 'dayGridMonth'
        };
        const fcView = viewMapping[urlView] || 'timeGridWeek';
        localStorage.setItem('medexCalendarView', fcView);
        console.log('Restored view from URL:', urlView, '→', fcView);
    }

    // Restore providers from URL or localStorage
    const urlProviders = urlParams.get('providers') || urlParams.get('pc_username') || '';
    let providersToRestore = [];
    const storedProviders = readStoredArray('medexSelectedProviders');

    if (urlProviders) {
        // URL providers can be comma-separated (from OpenEMR pc_username)
        providersToRestore = urlProviders.split(',').map(p => p.trim()).filter(p => p);

        // Preserve richer saved multi-select state when wrapper returns only one
        // provider from native OpenEMR view navigation.
        const shouldPreferStored =
            storedProviders.length > 1
            && providersToRestore.length <= 1
            && !urlParams.has('force_filters');

        if (shouldPreferStored) {
            providersToRestore = storedProviders;
            console.log('Using stored providers over single URL provider:', providersToRestore);
        } else {
            localStorage.setItem('medexSelectedProviders', JSON.stringify(providersToRestore));
            console.log('Restored providers from URL:', providersToRestore);
        }
    } else {
        // Fall back to localStorage
        providersToRestore = storedProviders;
        console.log('Restored providers from localStorage:', providersToRestore);
    }

    if (providersToRestore.length > 0) {
        document.querySelectorAll('#provider-filter input[type="checkbox"]').forEach(cb => {
            cb.checked = providersToRestore.includes(cb.value);
        });
    } else if (window.medexUserPrefs && Array.isArray(window.medexUserPrefs.defaultProviders) && window.medexUserPrefs.defaultProviders.length > 0) {
        const preferredProviders = window.medexUserPrefs.defaultProviders;
        document.querySelectorAll('#provider-filter input[type="checkbox"]').forEach(cb => {
            cb.checked = preferredProviders.includes(cb.value);
        });
    }

    // Restore facilities from URL or localStorage
    const urlFacilityParam = urlParams.get('facilities') || urlParams.get('facility') || urlParams.get('pc_facility') || '';
    let facilitiesToRestore = [];
    const storedFacilities = readStoredArray('medexSelectedFacilities');

    if (urlFacilityParam) {
        facilitiesToRestore = urlFacilityParam.split(',').map(f => f.trim()).filter(f => f);

        const shouldPreferStored =
            storedFacilities.length > 1
            && facilitiesToRestore.length <= 1
            && !urlParams.has('force_filters');

        if (shouldPreferStored) {
            facilitiesToRestore = storedFacilities;
            console.log('Using stored facilities over single URL facility:', facilitiesToRestore);
        } else {
            localStorage.setItem('medexSelectedFacilities', JSON.stringify(facilitiesToRestore));
            console.log('Restored facilities from URL:', facilitiesToRestore);
        }
    } else {
        // Fall back to localStorage
        facilitiesToRestore = storedFacilities;
        console.log('Restored facilities from localStorage:', facilitiesToRestore);
    }

    if (facilitiesToRestore.length > 0) {
        document.querySelectorAll('#facility-filter input[type="checkbox"]').forEach(cb => {
            cb.checked = facilitiesToRestore.includes(cb.value);
        });
    } else if (window.medexUserPrefs && Array.isArray(window.medexUserPrefs.defaultFacilities) && window.medexUserPrefs.defaultFacilities.length > 0) {
        const preferredFacilities = window.medexUserPrefs.defaultFacilities.map(String);
        document.querySelectorAll('#facility-filter input[type="checkbox"]').forEach(cb => {
            cb.checked = preferredFacilities.includes(cb.value);
        });
    }

    const storedSlotStates = readStoredArray('medexSelectedSlotStates');
    const slotStatesToRestore = storedSlotStates.length > 0 ? storedSlotStates : DEFAULT_SLOT_STATE_FILTERS;
    document.querySelectorAll('#slot-state-filter input[type="checkbox"]').forEach((cb) => {
        cb.checked = slotStatesToRestore.includes(cb.value);
    });

    const storedCategoryIds = readStoredArray('medexSelectedAppointmentCategoryIds');
    if (storedCategoryIds.length > 0) {
        document.querySelectorAll('#appointment-category-filter input[type="checkbox"]').forEach((cb) => {
            cb.checked = storedCategoryIds.includes(cb.value);
        });
    }
}

function restoreSidebarSectionStates() {
    const sections = Array.from(document.querySelectorAll('#sidebar details.filter-section[id]'));
    if (sections.length === 0) {
        return;
    }

    let savedState = {};
    try {
        const raw = localStorage.getItem('medexSidebarSectionStates') || '{}';
        const parsed = JSON.parse(raw);
        if (parsed && typeof parsed === 'object') {
            savedState = parsed;
        }
    } catch (e) {
        savedState = {};
    }

    sections.forEach((section) => {
        const key = section.id;
        if (Object.prototype.hasOwnProperty.call(savedState, key)) {
            section.open = !!savedState[key];
        }
    });
}

function bindSidebarSectionStatePersistence() {
    const sections = Array.from(document.querySelectorAll('#sidebar details.filter-section[id]'));
    if (sections.length === 0) {
        return;
    }

    const saveState = () => {
        const state = {};
        sections.forEach((section) => {
            state[section.id] = !!section.open;
        });
        localStorage.setItem('medexSidebarSectionStates', JSON.stringify(state));
    };

    sections.forEach((section) => {
        section.addEventListener('toggle', saveState);
    });
}

console.log('calendar.js initialization complete');
