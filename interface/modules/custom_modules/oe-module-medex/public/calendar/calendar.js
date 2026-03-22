/**
 * MedEx Full Calendar Initialization - Multi-Provider Support
 */

console.log('calendar.js loaded and executing...');

// Store calendar instances
let calendarInstances = [];

// Use immediate execution with fallback
function initializeCalendars() {
    console.log('=== Initializing calendars... ===');

    if (typeof FullCalendar === 'undefined') {
        console.error('FullCalendar library not loaded!');
        document.getElementById('calendars-container').innerHTML = '<div style="padding: 50px; text-align: center; color: red;"><h2>Error: FullCalendar library failed to load</h2><p>Please check browser console for details.</p></div>';
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

    // Get calendar display settings from OpenEMR
    // Check for saved user preference first, then fall back to OpenEMR default
    const savedView = localStorage.getItem('medexCalendarView');
    const defaultView = savedView || window.calendarDefaultView || 'timeGridWeek';
    const savedDate = localStorage.getItem('medexCalendarDate');
    const timeIncrement = parseInt(window.calendarTimeIncrement || '5');
    const use24Hours = window.calendar24Hours !== undefined ? window.calendar24Hours : false;
    const firstDayOfWeek = (window.calendarFirstDayOfWeek !== undefined) ? parseInt(window.calendarFirstDayOfWeek) : 0;

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
    const providerCheckboxes = document.querySelectorAll('#provider-filter input[type="checkbox"]:checked');
    const selectedProviders = Array.from(providerCheckboxes).map(cb => cb.value);

    console.log('Provider checkboxes found:', providerCheckboxes.length);
    console.log('Selected providers:', selectedProviders);
    console.log('Unique providers:', [...new Set(selectedProviders)]);

    if (selectedProviders.length === 0) {
        container.innerHTML = '<div style="padding: 50px; text-align: center;"><h3>Please select at least one provider</h3></div>';
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
                use24Hours, firstDayOfWeek);
        } else {
            // Create a calendar for each facility
            selectedFacilities.forEach(facilityId => {
                const facilityInfo = window.facilityData ? window.facilityData[facilityId] : null;
                const facilityName = facilityInfo ? facilityInfo.name : 'Facility ' + facilityId;

                createProviderCalendar(providerId, providerInfo, facilityId, container, defaultView, savedDate,
                    scheduleStart, scheduleEnd, slotMinTime, slotMaxTime, slotDuration, scrollTime,
                    use24Hours, firstDayOfWeek, facilityName);
            });
        }
    });

    console.log('Total calendars created:', calendarInstances.length);
}

function createProviderCalendar(providerId, providerInfo, facilityId, container, defaultView, savedDate,
    scheduleStart, scheduleEnd, slotMinTime, slotMaxTime, slotDuration, scrollTime,
    use24Hours, firstDayOfWeek, facilityName = null) {

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
            eventOverlap: true,
            editable: true,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            weekends: true,
            height: 'auto',
            eventClassNames: function(arg) {
                const category = arg.event.extendedProps.category || '';
                const patientId = arg.event.extendedProps.patientId;
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

                // Add short-appointment class for appointments under 20 minutes
                if (duration < 20) {
                    classes.push('short-appointment');
                }

                return classes;
            },
            events: function(info, successCallback, failureCallback) {
                const params = {
                    start: info.startStr,
                    end: info.endStr,
                    providers: providerId  // Filter by this specific provider (note: backend expects 'providers' not 'provider')
                };

                // Add facility filter if specified
                if (facilityId) {
                    params.facilities = facilityId;
                }

                console.log('Fetching events for provider', providerId, 'facility', facilityId || 'all', 'with params:', params);

                fetch(webroot + '/interface/modules/custom_modules/oe-module-medex/public/calendar/get_events.php?' + new URLSearchParams(params))
                    .then(response => response.json())
                    .then(data => {
                        console.log('Received', data.length, 'events for provider', providerId);

                        // Dynamically adjust time range to show appointments outside schedule hours
                        if (Array.isArray(data) && data.length > 0) {
                            let earliestHour = scheduleStart;
                            let latestHour = scheduleEnd;

                            data.forEach(event => {
                                const startTime = new Date(event.start.replace(' ', 'T'));
                                const endTime = new Date(event.end.replace(' ', 'T'));
                                const startHour = startTime.getHours();
                                const endHour = endTime.getHours() + (endTime.getMinutes() > 0 ? 1 : 0);

                                if (startHour < earliestHour) earliestHour = startHour;
                                if (endHour > latestHour) latestHour = endHour;
                            });

                            const newMinTime = String(earliestHour).padStart(2, '0') + ':00:00';
                            const newMaxTime = String(latestHour).padStart(2, '0') + ':00:00';

                            if (newMinTime !== calendar.getOption('slotMinTime') || newMaxTime !== calendar.getOption('slotMaxTime')) {
                                console.log('Expanding calendar view for provider', providerId, ':', newMinTime, 'to', newMaxTime);
                                calendar.setOption('slotMinTime', newMinTime);
                                calendar.setOption('slotMaxTime', newMaxTime);
                            }
                        }

                        successCallback(data);
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
                const eventTitle = info.event.title;

                // Calculate event duration to determine if it's a short appointment
                const start = info.event.start;
                const end = info.event.end;
                let duration = 15;
                if (start && end) {
                    duration = Math.round((end - start) / (1000 * 60));
                }

                // For short appointments (< 20 min) in timeGrid views, set nowrap on all elements
                if (duration < 20 && (info.view.type === 'timeGridWeek' || info.view.type === 'timeGridDay')) {
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

                if (patientId && patientName && eventTitle) {
                    if (typeof medexDebug !== 'undefined' && medexDebug) {
                        console.log('[MedEx Calendar Debug]', medexDebug);
                    }
                    // Find the title element and make patient name clickable
                    const titleEl = info.el.querySelector('.fc-event-title');
                    if (titleEl) {
                        // Parse the title to extract patient name and appointment type
                        // Title format is typically: "Patient Name - Appointment Type"
                        const parts = eventTitle.split(' - ');

                        if (parts.length >= 2) {
                            // Clear existing content
                            titleEl.innerHTML = '';

                            // For short appointments in timeGrid, force inline display and nowrap
                            if (duration < 20 && (info.view.type === 'timeGridWeek' || info.view.type === 'timeGridDay')) {
                                titleEl.style.whiteSpace = 'nowrap';
                                titleEl.style.overflow = 'hidden';
                                titleEl.style.textOverflow = 'ellipsis';
                                titleEl.style.display = 'block'; // Block inside flex item works better usually
                                titleEl.style.width = '100%';
                            }

                            // Create clickable patient name
                            const patientLink = document.createElement('span');
                            patientLink.className = 'patient-name-link';
                            patientLink.textContent = patientName;
                            patientLink.style.cursor = 'pointer';
                            patientLink.style.textDecoration = 'underline';
                            patientLink.style.fontWeight = 'bold';
                            patientLink.title = 'Click to view patient dashboard';
                            if (duration < 20 && (info.view.type === 'timeGridWeek' || info.view.type === 'timeGridDay')) {
                                patientLink.style.display = 'inline';
                            }

                            // Add click handler for patient name
                            patientLink.addEventListener('click', function(e) {
                                e.stopPropagation(); // Prevent event click from firing
                                if (top.restoreSession) {
                                    top.restoreSession();
                                }
                                if (top.RTop) {
                                    // Open in OpenEMR's right top frame (like OpenEMR calendar does)
                                    top.RTop.location = webroot + '/interface/patient_file/summary/demographics.php?set_pid=' + encodeURIComponent(patientId);
                                } else {
                                    // Fallback to new tab if not in OpenEMR frame structure
                                    window.open(webroot + '/interface/patient_file/summary/demographics.php?set_pid=' + encodeURIComponent(patientId), '_blank');
                                }
                            });

                            // Append patient name link
                            titleEl.appendChild(patientLink);

                            // Add status if present
                            if (statusIcon || status) {
                                const statusSeparator = document.createTextNode(' ');
                                const statusSpan = document.createElement('span');
                                if (statusIcon) {
                                    statusSpan.innerHTML = statusIcon;
                                } else if (status) {
                                    statusSpan.textContent = '[' + status + ']';
                                }
                                statusSpan.style.fontStyle = 'italic';
                                statusSpan.style.fontSize = '10px';
                                if (duration < 20 && (info.view.type === 'timeGridWeek' || info.view.type === 'timeGridDay')) {
                                    statusSpan.style.display = 'inline';
                                }
                                titleEl.appendChild(statusSeparator);
                                titleEl.appendChild(statusSpan);
                            }

                            // Append the rest of the title (appointment type)
                            const appointmentType = parts.slice(1).join(' - ');
                            if (appointmentType) {
                                const separator = document.createTextNode(' - ');
                                const typeSpan = document.createElement('span');
                                typeSpan.textContent = appointmentType;
                                if (duration < 20 && (info.view.type === 'timeGridWeek' || info.view.type === 'timeGridDay')) {
                                    typeSpan.style.display = 'inline';
                                }
                                titleEl.appendChild(separator);
                                titleEl.appendChild(typeSpan);
                            }
                        }
                    }
                }
            },
            eventClick: function(info) {
                const eventId = info.event.id;
                const start = info.event.start;
                const end = info.event.end;
                let duration = 15;

                if (start && end) {
                    duration = Math.round((end - start) / (1000 * 60));
                }

                console.log('Event clicked:', eventId, 'duration:', duration);

                const url = webroot + '/interface/modules/custom_modules/oe-module-medex/public/calendar/edit_event_wrapper.php?eid=' + encodeURIComponent(eventId) + '&duration=' + duration;

                // Use a single shared callback that refreshes all calendars
                const callbackName = 'medexRefreshAllCalendars';
                if (!window[callbackName]) {
                    window[callbackName] = function() {
                        console.log('Dialog closed, refreshing all calendar instances');
                        calendarInstances.forEach(cal => {
                            cal.refetchEvents();
                        });
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

                // Route through wrapper so duration is injected into form_duration
                const url = webroot + '/interface/modules/custom_modules/oe-module-medex/public/calendar/edit_event_wrapper.php?date=' + encodeURIComponent(dateStr) +
                           '&starttimeh=' + encodeURIComponent(hour) +
                           '&starttimem=' + encodeURIComponent(minute) +
                           '&userid=' + encodeURIComponent(providerId) +
                           '&duration=' + duration;

                // Use a single shared callback that refreshes all calendars
                const callbackName = 'medexRefreshAllCalendars';
                if (!window[callbackName]) {
                    window[callbackName] = function() {
                        console.log('Dialog closed, refreshing all calendar instances');
                        calendarInstances.forEach(cal => {
                            cal.refetchEvents();
                        });
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
                updateEventTime(info, calendar, providerId);
            },
            eventResize: function(info) {
                updateEventTime(info, calendar, providerId);
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
            }
        });

    calendar.render();
    calendarInstances.push(calendar);

    console.log('Calendar rendered for provider:', providerId, 'facility:', facilityId || 'all');
}

function updateEventTime(info, calendar, providerId) {
    const eventId = info.event.id;
    const newStart = info.event.start;
    const newEnd = info.event.end || info.event.start;

    console.log('Updating event', eventId, 'for provider', providerId, '- new start:', newStart, 'new end:', newEnd);

    if (!confirm('Move this appointment to ' + newStart.toLocaleString() + '?')) {
        info.revert();
        return;
    }

    const params = {
        eid: eventId,
        start: formatLocalDateTime(newStart),
        end: formatLocalDateTime(newEnd)
    };

    fetch(webroot + '/interface/modules/custom_modules/oe-module-medex/public/calendar/update_event.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(params)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Event updated successfully for provider', providerId);
        } else {
            console.error('Error updating event:', data.error);
            alert('Error updating appointment: ' + (data.error || 'Unknown error'));
            info.revert();
        }
    })
    .catch(error => {
        console.error('Error updating event for provider', providerId, ':', error);
        alert('Error updating appointment: ' + error);
        info.revert();
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
    restoreFilterSelections();
    initializeCalendars();

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
});

// Save filter selections to localStorage
function saveFilterSelections() {
    const providers = Array.from(document.querySelectorAll('#provider-filter input[type="checkbox"]:checked')).map(cb => cb.value);
    const facilities = Array.from(document.querySelectorAll('#facility-filter input[type="checkbox"]:checked')).map(cb => cb.value);
    localStorage.setItem('medexSelectedProviders', JSON.stringify(providers));
    localStorage.setItem('medexSelectedFacilities', JSON.stringify(facilities));
    console.log('Saved filter selections - providers:', providers, 'facilities:', facilities);
}

// Restore filter selections from URL parameters (priority) or localStorage
function restoreFilterSelections() {
    // Parse URL parameters
    const urlParams = new URLSearchParams(window.location.search);

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
    const urlProviders = urlParams.get('providers');
    let providersToRestore = [];

    if (urlProviders) {
        // URL providers can be comma-separated (from OpenEMR pc_username)
        providersToRestore = urlProviders.split(',').map(p => p.trim()).filter(p => p);
        localStorage.setItem('medexSelectedProviders', JSON.stringify(providersToRestore));
        console.log('Restored providers from URL:', providersToRestore);
    } else {
        // Fall back to localStorage
        providersToRestore = JSON.parse(localStorage.getItem('medexSelectedProviders') || '[]');
        console.log('Restored providers from localStorage:', providersToRestore);
    }

    if (providersToRestore.length > 0) {
        document.querySelectorAll('#provider-filter input[type="checkbox"]').forEach(cb => {
            cb.checked = providersToRestore.includes(cb.value);
        });
    }

    // Restore facilities from URL or localStorage
    const urlFacility = urlParams.get('facility');
    let facilitiesToRestore = [];

    if (urlFacility) {
        // URL facility is a single value (from OpenEMR pc_facility)
        facilitiesToRestore = [urlFacility];
        localStorage.setItem('medexSelectedFacilities', JSON.stringify(facilitiesToRestore));
        console.log('Restored facilities from URL:', facilitiesToRestore);
    } else {
        // Fall back to localStorage
        facilitiesToRestore = JSON.parse(localStorage.getItem('medexSelectedFacilities') || '[]');
        console.log('Restored facilities from localStorage:', facilitiesToRestore);
    }

    if (facilitiesToRestore.length > 0) {
        document.querySelectorAll('#facility-filter input[type="checkbox"]').forEach(cb => {
            cb.checked = facilitiesToRestore.includes(cb.value);
        });
    }
}

console.log('calendar.js initialization complete');
