/*
 * vitals_functions.js
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

(function(window, oeUI) {

    let translations = {};
    let webroot = null;
    const VITAL_RANGES = {
        'bps': {
            warning_min: 80, warning_max: 180,
            label: 'BP Systolic (mmHg)'
        },
        'bpd': {
            warning_min: 40, warning_max: 120,
            label: 'BP Diastolic (mmHg)'
        },
        'pulse': {
            warning_min: 40, warning_max: 200,
            label: 'Pulse (bpm)'
        },
        'respiration': {
            warning_min: 8, warning_max: 50,
            label: 'Respiration (breaths/min)'
        },
        'temperature': {
            warning_min: 95, warning_max: 105,
            label: 'Temperature (°F)'
        },
        'weight': {
            warning_min: 5.5, warning_max: 650,
            label: 'Weight (lbs)'
        },
        'height': {
            warning_min: 11, warning_max: 98,
            label: 'Height (in)'
        },
        'oxygen_saturation': {
            warning_min: 90, warning_max: 100,
            label: 'Oxygen Saturation (%)'
        }
    };

    function validateVitalValue(fieldId, value) {
        if (!value || value === '' || isNaN(value)) {
            return { valid: true, error: null, warning: null };
        }

        const numValue = parseFloat(value);
        const range = VITAL_RANGES[fieldId];

        if (!range) {
            return { valid: true, error: null, warning: null };
        }

         if (
            (range.warning_min !== null && numValue < range.warning_min) ||
            (range.warning_max !== null && numValue > range.warning_max)
        ) {
            return {
                valid: true,
                error: null,
                warning: range.label + ' is outside typical range (' + range.warning_min + '-' + range.warning_max + ')'
            };
        }

        return { valid: true, error: null, warning: null };
    }

    /**
     * Validate blood pressure constraint: systolic >= diastolic
     */
    function validateBPConstraint() {
        const bpsInput = document.getElementById('bps_input');
        const bpdInput = document.getElementById('bpd_input');
        
        if (!bpsInput || !bpdInput) return null;

        const bps = parseFloat(bpsInput.value);
        const bpd = parseFloat(bpdInput.value);

        if (isNaN(bps) || isNaN(bpd) || bps === 0 || bpd === 0) {
            return null;
        }

        if (bps < bpd) {
            return 'BP Systolic must be greater than or equal to BP Diastolic';
        }

        return null;
    }

        /**
     * Add visual feedback to a field (error or warning)
     */
    function updateFieldStatus(fieldId, validation) {
        const field = document.getElementById(fieldId + '_input') || 
                      document.getElementById(fieldId + '_input_usa') ||
                      document.getElementById(fieldId + '_input_metric');
        
        if (!field) return;

        // Remove existing feedback
        field.classList.remove('is-invalid', 'is-warning');
        const existingFeedback = field.parentElement?.querySelector('.invalid-feedback, .warning-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }

        if (validation.error) {
            field.classList.add('is-invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback d-block';
            errorDiv.textContent = validation.error;
            field.parentElement?.appendChild(errorDiv);
        } else if (validation.warning) {
            field.classList.add('is-warning');
            const warningDiv = document.createElement('div');
            warningDiv.className = 'warning-feedback d-block';
            warningDiv.style.color = '#856404';
            warningDiv.textContent = '⚠ ' + validation.warning;
            field.parentElement?.appendChild(warningDiv);
        }
    }
    /**
     * Real-time validation on field change
     */
    function onVitalFieldChange(event) {
        const field = event.target;
        const fieldId = field.id.replace('_input', '').replace('_usa', '').replace('_metric', '');
        
        const validation = validateVitalValue(fieldId, field.value);
        updateFieldStatus(fieldId, validation);
    }

    function vitalsFormSubmitted() {
        let hasErrors = false;
        let errorMessages = [];
        let warnings = [];

        // UPDATED: Validate all vital fields with ranges
        const fieldsToValidate = ['bps', 'bpd', 'pulse', 'respiration', 'temperature', 'weight', 'height', 'oxygen_saturation'];

        for (let fieldId of fieldsToValidate) {
            const field = document.getElementById(fieldId + '_input') ||
                         document.getElementById(fieldId + '_input_usa') ||
                         document.getElementById(fieldId + '_input_metric');
            
            if (!field || !field.value) continue;

            const validation = validateVitalValue(fieldId, field.value);
            updateFieldStatus(fieldId, validation);

            if (!validation.valid) {
                hasErrors = true;
                errorMessages.push(validation.error);
            } else if (validation.warning) {
                warnings.push(validation.warning);
            }
        }

        // Check BP constraint
        const bpError = validateBPConstraint();
        if (bpError) {
            hasErrors = true;
            errorMessages.push(bpError);
            updateFieldStatus('bps', { valid: false, error: bpError, warning: null });
        }

        // Show errors or warnings
        if (hasErrors) {
            alert(errorMessages.join('\n'));
            return false;
        }

        if (warnings.length > 0) {
            const confirmMessage = warnings.join('\n') + '\n\nThese values are outside typical ranges. Continue anyway?';
            if (!confirm(confirmMessage)) {
                return false;
            }
        }

        return top.restoreSession();
    }


    function convInputElement(evt) {
        let node = evt.currentTarget;
        if (!node) {
            console.error("Missing node from event");
            return;
        }
        let system = node.dataset.system || "usa";
        let unit = node.dataset.unit || "";
        let targetSaveUnit = node.dataset.targetInput || "";
        let targetInputConv = node.dataset.targetInputConv || "";
        let precision = vitalsGetPrecision(node, 2);

        // we need to convert the value and store the original value in the hidden input field that we end up saving

        // we then need to show a two digit representation of the value
        let value = node.value;
        let inputSave = document.getElementById(targetSaveUnit);
        if (!inputSave) {
            console.error("Failed to find node with data-target-input of ", targetSaveUnit);
            return;
        }
        let inputConv = document.getElementById(targetInputConv);
        if (!inputConv) {
            console.error("Failed to find node with data-target-input-conv of ", targetInputConv);
            return;
        }

        if (value != "") {
            let convValue = convUnit(system, unit, value);
            if (!isNaN(convValue)) {
                inputConv.value = convValue.toFixed(precision);
                // all values are saved in usa system units
                if (system !== "usa") {
                    inputSave.value = inputSave.value = convValue;
                } else {
                    inputSave.value = value;
                }
            } else {
                console.error("Failed to get valid number for input with id ", node.id, " with value ", value);
            }
        } else {
            inputSave.value = "";
            inputConv.value = "";
        }

        if (targetSaveUnit == "weight_input_usa" || targetSaveUnit == "height_input_usa") {
            calculateBMI();
        }
         onVitalFieldChange(evt);
    }

    function initDOMEvents() {
        let vitalsForm = document.getElementById('vitalsForm');
        if (!vitalsForm) {
            console.error("Failed to find vitalsForm DOM Node");
            return;
        }
        document.getElementById('vitalsForm').addEventListener('submit', function(event) {
            if (!vitalsFormSubmitted()) {
                event.preventDefault(); // stop the form from submitting
                let firstErrorElement = document.querySelector('.error');
                if (firstErrorElement) {
                    firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return false;
            }
        });

        // we want to setup our reason code widgets
        if (oeUI.reasonCodeWidget) {
            oeUI.reasonCodeWidget.init(webroot);
        } else {
            console.error("Missing required dependency reason-code-widget");
            return;
        }

        let vitalsConvInputs = vitalsForm.querySelectorAll(".vitals-conv-unit");
        vitalsConvInputs.forEach(function(node) {
            node.addEventListener('change', convInputElement);
            node.addEventListener('blur', onVitalFieldChange);
        });

        const allVitalInputs = vitalsForm.querySelectorAll('input[type="text"][name]');
        allVitalInputs.forEach(function(node) {
            if (node.id && (node.id.includes('_input') || VITAL_RANGES[node.name])) {
                node.addEventListener('blur', onVitalFieldChange);
            }
        });
    }
    function init(webRootParam, vitalsTranslations) {
        webroot = webRootParam;
        translations = vitalsTranslations;
        window.document.addEventListener("DOMContentLoaded", initDOMEvents);
    }

    let vitalsForm = {
        "init": init
    };
    window.vitalsForm = vitalsForm;
})(window, window.oeUI || {});

function vitalsGetPrecision(node, defaultValue) {
    defaultValue = defaultValue || 2;
    let precision = parseInt(node.dataset.precision || defaultValue);
    precision = !isNaN(precision) ? precision : defaultValue;
    return precision;
}

// TODO: we need to move all of these functions into the anonymous function and connect the events via event listeners
function convUnit(system, unit, value)
{
    if (unit == 'kg' || unit == 'lbs')
    {
        if (system == 'metric')
        {
            return convKgtoLb(value);
        }
        else
        {
            return convLbtoKg(value);
        }
    }

    if (unit == 'in' || unit == 'cm')
    {
        if (system == 'metric')
        {
            return convCmtoIn(value);
        }
        else
        {
            return convIntoCm(value);
        }
    }

    if (unit == 'C' || unit=='F')
    {
        if (system == 'metric')
        {
            return convCtoF(value);
        }
        else
        {
            return convFtoC(value);
        }
    }
}

function convLbtoKg(value) {
    var lb = value;
    var hash_loc=lb.indexOf("#");
    if(hash_loc>=0)
    {
        var pounds=lb.substr(0,hash_loc);
        var ounces=lb.substr(hash_loc+1);
        var num=parseInt(pounds)+parseInt(ounces)/16;
        lb=num;
        return lb;
    }
    if (lb == "0") {
        return 0;
    }
    else if (lb == parseFloat(lb)) {
        kg = lb*0.45359237;
        return kg;
    }
    else {
        return 0;
    }
}

function convKgtoLb(value) {
    var kg = value;

    if (kg == "0") {
        return 0;
    }
    else if (kg == parseFloat(kg)) {
        lb = kg/0.45359237;
        return lb;
    }
    else {
        return 0;
    }
}

function convIntoCm(value) {
    var inch = value;

    if (inch == "0") {
        return 0;
    }
    else if (inch == parseFloat(inch)) {
        cm = inch*2.54;
        return cm;
    }
    else {
        return 0;
    }
}

function convCmtoIn(value) {
    var cm = value

    if (cm == "0") {
        return 0;
    }
    else if (cm == parseFloat(cm)) {
        inch = cm/2.54;
        return inch;
    }
    else {
        return 0;
    }
}

function convFtoC(value) {
    var Fdeg = value;
    if (Fdeg == "0") {
        return 0;
    }
    else if (Fdeg == parseFloat(Fdeg)) {
        let Cdeg = (Fdeg-32)*5/9; // originally 0.5556 which is not precise!
        return Cdeg;
    }
    else {
        return 0;
    }
}

function convCtoF(value) {
    var Cdeg = value;
    if (Cdeg == "0") {
        return 0;
    }
    else if (Cdeg == parseFloat(Cdeg)) {
        Cdeg = parseFloat(Cdeg);
        let Fdeg = (Cdeg*9/5)+32; // originally 0.5556 which is not precise when working with 2 digit decimal conversions!
        return Fdeg;
    }
    else {
        $("#"+name).val("");
    }
}

function calculateBMI() {
    var bmi = 0;
    let bmiNode = document.getElementById("BMI_input");
    if (!bmiNode) {
        console.error("Failed to find node with id BMI_input");
        return;
    }

    let precision = vitalsGetPrecision(bmiNode, 2);

    let heightNode = document.getElementById("height_input_usa");
    if (!heightNode) {
        console.error("Failed to find node with id height_input_usa");
        return;
    }
    let weightNode = document.getElementById("weight_input_usa");
    if (!weightNode) {
        console.error("Failed to find node with id weight_input_usa");
        return;
    }
    var height = parseFloat(heightNode.value);
    var weight = parseFloat(weightNode.value);
    if(isNaN(height) || height == 0 || isNaN(weight) || weight == 0) {
        bmiNode.value = "";
    }
    else if((height == parseFloat(height)) && (weight == parseFloat(weight))) {
        bmi = weight/height/height*703;
        bmi = bmi.toFixed(precision);
        bmiNode.value = bmi;
    }
    else {
        bmiNode.value = "";
    }
}
