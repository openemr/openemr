/**
 * Advanced PDF Field Mapping Module
 * Extends editor.html with comprehensive database field mapping capabilities
 */

// Database schema for field mapping
const DB_SCHEMA = {
    "computed_patient": {
        "label": "📋 Computed Patient Fields",
        "note": "These combine multiple fields automatically",
        "fields": {
            "full_name": "Full Name (First Last)",
            "full_name_lfm": "Full Name (Last, First M)",
            "full_name_fml": "Full Name (First M Last)",
            "name_last_first": "Name (Last, First)",
            "full_address": "Full Address (Street, City, State Zip)",
            "full_address_multiline": "Full Address (Multi-line)",
            "city_state_zip": "City, State Zip",
            "age": "Age (calculated from DOB)",
            "dob_formatted": "DOB (MM/DD/YYYY)",
            "phone_primary": "Primary Phone (Cell or Home)"
        }
    },
    "patient_data": {
        "label": "Patient Data",
        "fields": {
            "fname": "First Name",
            "lname": "Last Name",
            "mname": "Middle Name",
            "DOB": "Date of Birth",
            "sex": "Gender",
            "street": "Street Address",
            "city": "City",
            "state": "State",
            "postal_code": "Zip Code",
            "country_code": "Country",
            "phone_home": "Home Phone",
            "phone_cell": "Cell Phone",
            "phone_biz": "Business Phone",
            "email": "Email",
            "ss": "SSN",
            "occupation": "Occupation",
            "status": "Status",
            "drivers_license": "Driver's License",
            "race": "Race",
            "ethnicity": "Ethnicity"
        }
    },
    "insurance_data": {
        "label": "Insurance Data",
        "fields": {
            "provider": "Insurance Provider",
            "plan_name": "Plan Name",
            "policy_number": "Policy Number",
            "group_number": "Group Number",
            "subscriber_fname": "Subscriber First Name",
            "subscriber_lname": "Subscriber Last Name",
            "subscriber_relationship": "Relationship to Patient",
            "subscriber_DOB": "Subscriber DOB",
            "subscriber_ss": "Subscriber SSN",
            "copay": "Copay Amount",
            "date": "Effective Date"
        }
    },
    "form_encounter": {
        "label": "Encounter",
        "fields": {
            "date": "Encounter Date",
            "reason": "Chief Complaint",
            "facility": "Facility",
            "onset_date": "Onset Date",
            "sensitivity": "Sensitivity",
            "billing_note": "Billing Note"
        }
    },
    "form_vitals": {
        "label": "Vitals",
        "fields": {
            "bps": "Blood Pressure Systolic",
            "bpd": "Blood Pressure Diastolic",
            "pulse": "Pulse",
            "respiration": "Respiration",
            "temperature": "Temperature",
            "weight": "Weight",
            "height": "Height",
            "BMI": "BMI",
            "oxygen_saturation": "O2 Saturation"
        }
    },
    "employer_data": {
        "label": "Employer Data",
        "fields": {
            "name": "Employer Name",
            "street": "Street Address",
            "city": "City",
            "state": "State",
            "postal_code": "Zip Code",
            "country": "Country"
        }
    },
    "form_eye_base": {
        "label": "Eye Exam - Base",
        "note": "⚠️ Eye forms may require encounter_id - see special handling",
        "fields": {
            "ODSPH": "OD Sphere",
            "ODCYL": "OD Cylinder",
            "ODAXIS": "OD Axis",
            "OSSPH": "OS Sphere",
            "OSCYL": "OS Cylinder",
            "OSAXIS": "OS Axis",
            "ODOS": "Both Eyes",
            "ODHPD": "OD HPD",
            "ODVPD": "OD VPD",
            "OSHPD": "OS HPD",
            "OSVPD": "OS VPD"
        }
    },
    "users": {
        "label": "Provider/User",
        "fields": {
            "fname": "First Name",
            "lname": "Last Name",
            "mname": "Middle Name",
            "npi": "NPI",
            "federaltaxid": "Federal Tax ID",
            "upin": "UPIN",
            "street": "Street Address",
            "city": "City",
            "state": "State",
            "zip": "Zip Code",
            "phone": "Phone"
        }
    },
    "facility": {
        "label": "Facility",
        "fields": {
            "name": "Facility Name",
            "phone": "Phone",
            "fax": "Fax",
            "street": "Street Address",
            "city": "City",
            "state": "State",
            "postal_code": "Zip Code",
            "federal_ein": "Federal EIN",
            "facility_npi": "NPI"
        }
    }
};

// Field type constants
const FIELD_TYPES = {
    TEXT: "Tx",
    CHECKBOX: "Btn",
    RADIO: "Btn", // Radio buttons are also type "Btn"
    BUTTON: "Btn"
};

// Mapping type constants
const MAPPING_TYPES = {
    DATABASE: "database",
    DEFAULT: "default",
    STATIC: "static"
};

/**
 * Generate field mapping HTML for a single field
 */
function generateFieldMappingHTML(field, index) {
    const mappingType = field.mappingType || MAPPING_TYPES.DATABASE;
    const selectedTable = field.dbField ? field.dbField.split('.')[0] : '';
    const selectedField = field.dbField ? field.dbField.split('.')[1] : '';
    const hasMapping = selectedTable && selectedField;
    const aiMapped = field.aiMapped ? true : false;
    const aiConfidence = field.aiConfidence ? Math.round(field.aiConfidence * 100) : 0;
    const aiReason = field.aiReason || '';

    // Build status badge - more prominent for AI
    let statusBadge = '';
    let cardBorderColor = '#e2e8f0';
    let cardBackground = 'white';

    if (aiMapped && hasMapping) {
        const confColor = aiConfidence >= 80 ? '#10b981' : aiConfidence >= 50 ? '#f59e0b' : '#ef4444';
        statusBadge = `<span style="background: linear-gradient(135deg, #8b5cf6, #6366f1); color: white; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 600; margin-left: 6px; box-shadow: 0 1px 4px rgba(139,92,246,0.3);">🤖 AI ${aiConfidence}%</span>`;
        cardBorderColor = '#8b5cf6';  // Purple for AI
        cardBackground = 'linear-gradient(135deg, #faf5ff 0%, #f5f3ff 100%)';  // Light purple background
    } else if (hasMapping) {
        statusBadge = `<span style="background: #3b82f6; color: white; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 600; margin-left: 6px;">✓ Manual</span>`;
        cardBorderColor = '#10b981';
    } else {
        statusBadge = `<span style="background: #f59e0b; color: white; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 600; margin-left: 6px;">⚠ Unmapped</span>`;
        cardBorderColor = '#f59e0b';
    }

    // AI reason tooltip
    const aiReasonHtml = aiMapped && aiReason ? `<div style="font-size: 11px; color: #7c3aed; margin-top: 4px; font-style: italic;">💡 ${aiReason}</div>` : '';

    let html = `
        <div class="field-item" data-field-index="${index}" style="background: ${cardBackground}; border: 2px solid ${cardBorderColor}; padding: 12px; margin-bottom: 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s; ${aiMapped ? 'box-shadow: 0 2px 8px rgba(139,92,246,0.15);' : ''}" onclick="navigateToField(${index})" onmouseenter="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 12px rgba(102,126,234,0.2)'" onmouseleave="this.style.transform=''; this.style.boxShadow='${aiMapped ? '0 2px 8px rgba(139,92,246,0.15)' : 'none'}'">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 0.25rem; font-weight: 600; color: #000; font-size: 11px;">
                        FIELD NAME ${statusBadge}
                        ${field.page ? `<span style="background: #6366f1; color: white; padding: 1px 6px; border-radius: 10px; font-size: 10px; margin-left: 6px;">Page ${field.page}</span>` : ''}
                    </label>
                    <input type="text" value="${field.name}" onchange="updateField(${index}, 'name', this.value)" onclick="event.stopPropagation()" style="width: 100%; padding: 0.4rem; font-size: 12px; border: 1px solid #667eea; border-radius: 4px; color: #000; background: #fff;" placeholder="Enter field name">
                    ${field.type ? `<span style="font-size: 10px; color: #666; margin-left: 0.25rem;">Type: ${field.type}</span>` : ''}
                    ${aiReasonHtml}
                </div>
                <div style="display: flex; gap: 0.35rem; margin-left: 0.5rem;">
                    <button type="button" onclick="event.stopPropagation(); navigateToField(${index})" style="background: #6366f1; color: white; padding: 0.3rem 0.5rem; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 10px;" title="View field on PDF">👁 View</button>
                    <button type="button" onclick="event.stopPropagation(); removeField(${index})" style="background: #dc3545; color: white; padding: 0.3rem 0.5rem; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 10px;">Remove</button>
                </div>
            </div>

            <div style="margin-bottom: 0.5rem;" onclick="event.stopPropagation()">
                <label style="display: block; margin-bottom: 0.25rem; font-weight: 600; color: #000; font-size: 11px;">MAPPING TYPE</label>
                <select onchange="updateFieldMappingType(${index}, this.value)" style="width: 100%; padding: 0.4rem; font-size: 12px; border: 1px solid #667eea; border-radius: 4px; color: #000; background: #fff;">
                    <option value="${MAPPING_TYPES.DATABASE}" ${mappingType === MAPPING_TYPES.DATABASE ? 'selected' : ''}>Database Field</option>
                    <option value="${MAPPING_TYPES.DEFAULT}" ${mappingType === MAPPING_TYPES.DEFAULT ? 'selected' : ''}>Default Value</option>
                    <option value="${MAPPING_TYPES.STATIC}" ${mappingType === MAPPING_TYPES.STATIC ? 'selected' : ''}>Static Text</option>
                </select>
            </div>`;

    if (mappingType === MAPPING_TYPES.DATABASE) {
        html += `
            <div style="margin-bottom: 0.5rem;" onclick="event.stopPropagation()">
                <label style="display: block; margin-bottom: 0.25rem; font-weight: 600; color: #000; font-size: 11px;">DATABASE TABLE</label>
                <select id="table-select-${index}" onchange="updateFieldTable(${index}, this.value)" style="width: 100%; padding: 0.4rem; font-size: 12px; border: 1px solid #667eea; border-radius: 4px; color: #000; background: #fff;">
                    <option value="">-- Select Table --</option>`;

        for (const [tableName, tableInfo] of Object.entries(DB_SCHEMA)) {
            html += `<option value="${tableName}" ${selectedTable === tableName ? 'selected' : ''}>${tableInfo.label}</option>`;
        }

        html += `
                </select>
                ${selectedTable && DB_SCHEMA[selectedTable]?.note ? `<p style="color: #f39c12; font-size: 0.875rem; margin-top: 0.25rem;">${DB_SCHEMA[selectedTable].note}</p>` : ''}
            </div>`;

        if (selectedTable && DB_SCHEMA[selectedTable]) {
            html += `
            <div style="margin-bottom: 0.5rem;" onclick="event.stopPropagation()">
                <label style="display: block; margin-bottom: 0.2rem; font-weight: 500; font-size: 11px;">Database Field</label>
                <select id="field-select-${index}" onchange="updateFieldColumn(${index}, this.value)" style="width: 100%; padding: 0.4rem; font-size: 12px; border: 1px solid #667eea; border-radius: 4px;">
                    <option value="">-- Select Field --</option>`;

            for (const [fieldName, fieldLabel] of Object.entries(DB_SCHEMA[selectedTable].fields)) {
                html += `<option value="${fieldName}" ${selectedField === fieldName ? 'selected' : ''}>${fieldLabel} (${fieldName})</option>`;
            }

            html += `
                </select>
            </div>

            <div style="margin-bottom: 0.5rem;" onclick="event.stopPropagation()">
                <label style="display: block; margin-bottom: 0.2rem; font-weight: 500; font-size: 11px;">Default Value (if empty)</label>
                <input type="text" value="${field.defaultValue || ''}" onchange="updateField(${index}, 'defaultValue', this.value)" style="width: 100%; padding: 0.4rem; font-size: 12px; border: 1px solid #667eea; border-radius: 4px;" placeholder="Optional fallback">
            </div>`;
        }

    } else if (mappingType === MAPPING_TYPES.DEFAULT) {
        html += `
            <div style="margin-bottom: 0.5rem;" onclick="event.stopPropagation()">
                <label style="display: block; margin-bottom: 0.2rem; font-weight: 500; font-size: 11px;">Default Value</label>
                <input type="text" value="${field.defaultValue || ''}" onchange="updateField(${index}, 'defaultValue', this.value)" style="width: 100%; padding: 0.4rem; font-size: 12px; border: 1px solid #667eea; border-radius: 4px;" placeholder="Enter default value">
            </div>`;

    } else if (mappingType === MAPPING_TYPES.STATIC) {
        html += `
            <div style="margin-bottom: 0.5rem;" onclick="event.stopPropagation()">
                <label style="display: block; margin-bottom: 0.2rem; font-weight: 500; font-size: 11px;">Static Text Value</label>
                <textarea onchange="updateField(${index}, 'staticValue', this.value)" style="width: 100%; padding: 0.4rem; font-size: 12px; border: 1px solid #667eea; border-radius: 4px; min-height: 40px;" placeholder="Enter static text">${field.staticValue || ''}</textarea>
            </div>`;
    }

    // Special handling for checkboxes and radio buttons
    if (field.type === FIELD_TYPES.CHECKBOX || field.type === FIELD_TYPES.BUTTON) {
        html += `
            <div style="margin-bottom: 0.5rem; padding: 0.5rem; background: #f8f9fa; border-radius: 4px; font-size: 11px;" onclick="event.stopPropagation()">
                <h5 style="margin: 0 0 0.35rem 0; font-size: 11px;">Checkbox/Button Options</h5>

                <label style="display: block; margin-bottom: 0.35rem; font-size: 11px;">
                    <input type="checkbox" ${field.isRadioGroup ? 'checked' : ''} onchange="updateField(${index}, 'isRadioGroup', this.checked)">
                    <span style="margin-left: 0.35rem;">This is part of a radio button group</span>
                </label>

                ${field.isRadioGroup ? `
                <div style="margin-top: 0.35rem;">
                    <label style="display: block; margin-bottom: 0.15rem; font-weight: 500; font-size: 11px;">Radio Group Name</label>
                    <input type="text" value="${field.radioGroupName || ''}" onchange="updateField(${index}, 'radioGroupName', this.value)" style="width: 100%; padding: 0.3rem; font-size: 11px; border: 1px solid #667eea; border-radius: 3px;" placeholder="e.g., gender, yesno">
                </div>

                <div style="margin-top: 0.35rem;">
                    <label style="display: block; margin-bottom: 0.15rem; font-weight: 500; font-size: 11px;">Value When Selected</label>
                    <input type="text" value="${field.radioValue || ''}" onchange="updateField(${index}, 'radioValue', this.value)" style="width: 100%; padding: 0.3rem; font-size: 11px; border: 1px solid #667eea; border-radius: 3px;" placeholder="e.g., male, yes">
                </div>
                ` : `
                <div style="margin-top: 0.35rem;">
                    <label style="display: block; margin-bottom: 0.15rem; font-weight: 500; font-size: 11px;">Check Style</label>
                    <select onchange="updateField(${index}, 'checkboxStyle', this.value)" style="width: 100%; padding: 0.3rem; font-size: 11px; border: 1px solid #667eea; border-radius: 3px;">
                        <option value="X" ${field.checkboxStyle === 'X' || !field.checkboxStyle ? 'selected' : ''}>X</option>
                        <option value="✓" ${field.checkboxStyle === '✓' ? 'selected' : ''}>✓</option>
                        <option value="■" ${field.checkboxStyle === '■' ? 'selected' : ''}>■</option>
                        <option value="●" ${field.checkboxStyle === '●' ? 'selected' : ''}>●</option>
                    </select>
                </div>

                <div style="margin-top: 0.35rem;">
                    <label style="display: block; margin-bottom: 0.15rem; font-weight: 500; font-size: 11px;">Value to Check</label>
                    <input type="text" value="${field.checkboxValue || ''}" onchange="updateField(${index}, 'checkboxValue', this.value)" style="width: 100%; padding: 0.3rem; font-size: 11px; border: 1px solid #667eea; border-radius: 3px;" placeholder="e.g., 1, true, yes">
                </div>
                `}
            </div>`;
    }

    // Add sample preview row if a database field is mapped
    if (hasMapping && mappingType === MAPPING_TYPES.DATABASE) {
        html += `
            <div id="sample-preview-${index}" style="margin-top: 0.5rem; padding: 0.5rem; background: linear-gradient(135deg, #e0f2fe 0%, #f0fdf4 100%); border: 1px solid #38bdf8; border-radius: 6px;" onclick="event.stopPropagation()">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 10px; font-weight: 600; color: #0369a1;">📋 PREVIEW</span>
                    <button type="button" onclick="event.stopPropagation(); fetchSampleValue(${index}, '${field.dbField}')" style="font-size: 9px; padding: 2px 6px; background: #0ea5e9; color: white; border: none; border-radius: 3px; cursor: pointer;">↻ Refresh</button>
                </div>
                <div id="sample-value-${index}" style="margin-top: 0.25rem; font-size: 12px; color: #334155; font-family: monospace; background: white; padding: 0.35rem; border-radius: 4px; word-break: break-all;">
                    Loading...
                </div>
            </div>`;
    }

    // Close the field-item div
    html += `
        </div>`;

    return html;
}

/**
 * Currently selected field for editing
 */
window.selectedFieldIndex = null;

/**
 * Update the field list display - shows only selected field
 */
function updateAdvancedFieldList() {
    console.log('updateAdvancedFieldList called with', window.fieldMappings?.length || 0, 'fields');
    const container = document.getElementById('field-mappings-container');

    if (!container) {
        console.warn('field-mappings-container not found');
        return;
    }

    if (!window.fieldMappings || window.fieldMappings.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <p>No field mappings yet</p>
                <p style="font-size: 0.875rem; margin-top: 0.5rem;">Click on the PDF or use "Add Field Mapping"</p>
            </div>
        `;
        return;
    }

    // If a field is selected, show only that field
    if (window.selectedFieldIndex !== null && window.fieldMappings[window.selectedFieldIndex]) {
        const field = window.fieldMappings[window.selectedFieldIndex];
        const index = window.selectedFieldIndex;
        const totalFields = window.fieldMappings.length;
        const hasPrev = index > 0;
        const hasNext = index < totalFields - 1;

        let html = `
            <div style="padding: 0.4rem 0.5rem; background: #f0f4ff; border-radius: 6px; margin-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 0.75rem; color: #4c51bf; font-weight: 600;">Editing: ${field.name}</span>
                <div style="display: flex; gap: 0.25rem; align-items: center;">
                    <button onclick="navigateToPrevField()" style="padding: 0.2rem 0.4rem; font-size: 10px; border: 1px solid ${hasPrev ? '#667eea' : '#ccc'}; background: ${hasPrev ? 'white' : '#f5f5f5'}; color: ${hasPrev ? '#667eea' : '#999'}; border-radius: 3px; cursor: ${hasPrev ? 'pointer' : 'default'};" ${hasPrev ? '' : 'disabled'} title="Previous field">◀</button>
                    <span style="font-size: 10px; color: #666; min-width: 35px; text-align: center;">${index + 1}/${totalFields}</span>
                    <button onclick="navigateToNextField()" style="padding: 0.2rem 0.4rem; font-size: 10px; border: 1px solid ${hasNext ? '#667eea' : '#ccc'}; background: ${hasNext ? 'white' : '#f5f5f5'}; color: ${hasNext ? '#667eea' : '#999'}; border-radius: 3px; cursor: ${hasNext ? 'pointer' : 'default'};" ${hasNext ? '' : 'disabled'} title="Next field">▶</button>
                    <button id="quick-save-btn" onclick="quickSaveTemplate()" style="padding: 0.2rem 0.5rem; font-size: 10px; border: none; background: #667eea; color: white; border-radius: 3px; cursor: pointer; margin-left: 0.25rem; transition: all 0.3s;" title="Save template">💾</button>
                </div>
            </div>
        `;

        html += generateFieldMappingHTML(field, index);
        container.innerHTML = html;
        
        // Auto-fetch sample value if field has a database mapping
        if (field.dbField && field.dbField.includes('.')) {
            window.autoFetchSample(index, field.dbField);
        }
    } else {
        // Show message prompting user to click a field
        container.innerHTML = `
            <div class="empty-state">
                <p>Click a field button above to edit its mapping</p>
                <p style="font-size: 0.875rem; margin-top: 0.5rem; color: #666;">
                    ${window.fieldMappings.length} fields available
                </p>
            </div>
        `;
    }
}

/**
 * Update field mapping type (database, default, static)
 */
function updateFieldMappingType(index, type) {
    if (window.fieldMappings && window.fieldMappings[index]) {
        window.fieldMappings[index].mappingType = type;
        updateAdvancedFieldList();
    }
}

/**
 * Update selected database table for a field
 */
function updateFieldTable(index, tableName) {
    if (window.fieldMappings && window.fieldMappings[index]) {
        const oldDbField = window.fieldMappings[index].dbField || '';
        const oldTable = oldDbField.split('.')[0];

        if (oldTable !== tableName) {
            // Table changed, clear the field selection
            window.fieldMappings[index].dbField = tableName + '.';
        }

        updateAdvancedFieldList();
    }
}

/**
 * Update selected database column for a field
 */
function updateFieldColumn(index, fieldName) {
    if (window.fieldMappings && window.fieldMappings[index]) {
        const tableName = window.fieldMappings[index].dbField ? window.fieldMappings[index].dbField.split('.')[0] : '';
        const newDbField = tableName + '.' + fieldName;
        window.fieldMappings[index].dbField = newDbField;

        // Refresh field buttons to show updated status
        if (typeof window.displayDetectedFields === 'function') {
            window.displayDetectedFields();
        }
        
        // Fetch and display sample value for the new field
        if (fieldName && newDbField.includes('.')) {
            window.autoFetchSample(index, newDbField);
        }
    }
}

// Provide updateField function for the generated HTML onchange handlers
window.updateField = function(index, property, value) {
    if (window.fieldMappings && window.fieldMappings[index]) {
        window.fieldMappings[index][property] = value;
        console.log(`Updated field ${index}: ${property} = ${value}`);

        // Refresh field buttons if mapping changed
        if (property === 'dbField' && typeof window.displayDetectedFields === 'function') {
            window.displayDetectedFields();
        }
    }
};

/**
 * Navigate to a field on the PDF - changes page if needed and highlights the field
 */
window.navigateToField = function(index) {
    const field = window.fieldMappings && window.fieldMappings[index];
    if (!field) {
        console.warn('Field not found:', index);
        return;
    }

    // Show this field in the mapping panel
    window.selectedFieldIndex = index;
    updateAdvancedFieldList();

    console.log('Navigating to field:', field.name, 'on page', field.page);

    // Change page if needed
    const targetPage = field.page || 1;
    if (typeof window.currentPage !== 'undefined' && window.currentPage !== targetPage) {
        window.currentPage = targetPage;
        if (typeof window.renderPage === 'function') {
            window.renderPage(targetPage);
        }
        // Update page info display
        const pageInfo = document.getElementById('page-info');
        if (pageInfo && typeof window.pdfDoc !== 'undefined') {
            pageInfo.textContent = `Page ${targetPage} of ${window.pdfDoc.numPages}`;
        }
    }

    // Wait for page render, then highlight the field
    setTimeout(() => {
        // Find the field overlay on the PDF
        const overlays = document.querySelectorAll('.field-overlay');
        let foundOverlay = null;

        overlays.forEach(overlay => {
            const overlayIndex = parseInt(overlay.dataset.fieldIndex);
            if (overlayIndex === index) {
                foundOverlay = overlay;
            }
        });

        if (foundOverlay) {
            // Scroll the overlay into view
            foundOverlay.scrollIntoView({ behavior: 'smooth', block: 'center' });

            // Highlight with animation
            foundOverlay.style.transition = 'all 0.3s ease';
            foundOverlay.style.boxShadow = '0 0 0 4px #fbbf24, 0 0 20px rgba(251, 191, 36, 0.6)';
            foundOverlay.style.zIndex = '1000';
            foundOverlay.style.transform = 'scale(1.05)';

            // Remove highlight after 2 seconds
            setTimeout(() => {
                foundOverlay.style.boxShadow = '';
                foundOverlay.style.zIndex = '';
                foundOverlay.style.transform = '';
            }, 2000);
        } else {
            console.log('Field overlay not found on current page, may need to re-render');
            // Try to render field overlays again
            if (typeof window.renderFieldOverlays === 'function') {
                window.renderFieldOverlays();
                // Try again after render
                setTimeout(() => {
                    const retryOverlays = document.querySelectorAll('.field-overlay');
                    retryOverlays.forEach(overlay => {
                        const overlayIndex = parseInt(overlay.dataset.fieldIndex);
                        if (overlayIndex === index) {
                            overlay.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            overlay.style.boxShadow = '0 0 0 4px #fbbf24, 0 0 20px rgba(251, 191, 36, 0.6)';
                            setTimeout(() => { overlay.style.boxShadow = ''; }, 2000);
                        }
                    });
                }, 300);
            }
        }
    }, 100);
};

// Export to window for use in HTML onclick handlers
window.updateAdvancedFieldList = updateAdvancedFieldList;
window.updateFieldMappingType = updateFieldMappingType;
window.updateFieldTable = updateFieldTable;
window.updateFieldColumn = updateFieldColumn;
window.generateFieldMappingHTML = generateFieldMappingHTML;

// Navigation functions for field list
window.navigateToPrevField = function() {
    if (window.selectedFieldIndex !== null && window.selectedFieldIndex > 0) {
        window.selectedFieldIndex = window.selectedFieldIndex - 1;
        updateAdvancedFieldList();
        if (typeof window.navigateToField === 'function') {
            window.navigateToField(window.selectedFieldIndex);
        }
    }
};

window.navigateToNextField = function() {
    if (window.selectedFieldIndex !== null && window.fieldMappings && window.selectedFieldIndex < window.fieldMappings.length - 1) {
        window.selectedFieldIndex = window.selectedFieldIndex + 1;
        updateAdvancedFieldList();
        if (typeof window.navigateToField === 'function') {
            window.navigateToField(window.selectedFieldIndex);
        }
    }
};

// Quick save function with visual feedback
window.quickSaveTemplate = function() {
    const btn = document.getElementById('quick-save-btn');
    if (!btn) return;
    
    // Visual feedback - processing (orange)
    btn.style.background = '#f59e0b';
    btn.textContent = '⏳';
    
    // Call the main save function if it exists
    const saveBtn = document.getElementById('save-template');
    if (saveBtn && typeof saveBtn.click === 'function') {
        // Trigger the main save
        saveBtn.click();
        
        // After a short delay, show success (green)
        setTimeout(() => {
            btn.style.background = '#10b981';
            btn.textContent = '✓';
            
            // Reset to normal after 1.5s
            setTimeout(() => {
                btn.style.background = '#667eea';
                btn.textContent = '💾';
            }, 1500);
        }, 500);
    } else {
        // No save button found, reset
        btn.style.background = '#ef4444';
        btn.textContent = '!';
        setTimeout(() => {
            btn.style.background = '#667eea';
            btn.textContent = '💾';
        }, 1500);
    }
};

// Fetch sample value from database for preview
window.fetchSampleValue = async function(index, dbField) {
    const container = document.getElementById(`sample-value-${index}`);
    if (!container) return;
    
    container.textContent = 'Loading...';
    container.style.color = '#64748b';
    
    try {
        // Use API_BASE_URL from editor.html if available, otherwise relative path
        const apiBase = window.API_BASE_URL || 'api.php';
        const response = await fetch(`${apiBase}?action=sample&field=${encodeURIComponent(dbField)}`);
        const result = await response.json();
        
        if (result.success && result.sample) {
            container.textContent = result.sample;
            container.style.color = '#334155';
        } else {
            container.textContent = '(no sample data)';
            container.style.color = '#94a3b8';
        }
    } catch (error) {
        container.textContent = '(error loading)';
        container.style.color = '#ef4444';
    }
};

// Auto-fetch sample when field is displayed
window.autoFetchSample = function(index, dbField) {
    if (dbField && dbField.includes('.')) {
        setTimeout(() => {
            window.fetchSampleValue(index, dbField);
        }, 100);
    }
};

// Database schema exports
window.DB_SCHEMA = DB_SCHEMA;
window.MAPPING_TYPES = MAPPING_TYPES;
window.FIELD_TYPES = FIELD_TYPES;

// Set updateFieldList immediately so it's available before any other scripts run
window.updateFieldList = updateAdvancedFieldList;
console.log('Advanced PDF Field Mapping Module loaded - updateFieldList is now available');
