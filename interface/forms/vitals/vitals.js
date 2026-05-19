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

    function validateVitalField(element) {
        let value = element.value.trim();
        let warningSpan = document.getElementById(element.id + '_warning');

        element.classList.remove('error', 'warning');
        if (warningSpan) {
            warningSpan.textContent = '';
        }

        if (value === '') {
            return { valid: true, warning: false };
        }

        // Special case: weight USA input allows # separator (lbs/oz format e.g. "5#4")
        if (element.id === 'weight_input_usa' && value.indexOf('#') >= 0) {
            let parts = value.split('#');
            let pounds = parseFloat(parts[0]) || 0;
            let ounces = parseFloat(parts[1]) || 0;
            value = String(pounds + ounces / 16);
        }

        let numValue = parseFloat(value);

        if (isNaN(numValue)) {
            element.classList.add('error');
            return { valid: false, warning: false };
        }

        if (numValue < 0) {
            element.classList.add('error');
            if (warningSpan) {
                warningSpan.textContent = vitalsTranslations['invalidNegative'] || '';
            }
            return { valid: false, warning: false };
        }

        let min = parseFloat(element.dataset.min);
        let max = parseFloat(element.dataset.max);
        if (!isNaN(min) && !isNaN(max) && (numValue < min || numValue > max)) {
            element.classList.add('error');
            if (warningSpan) {
                warningSpan.textContent = vitalsTranslations['invalidRange'] || '';
            }
            return { valid: false, warning: false };
        }

        let warningMin = parseFloat(element.dataset.warningMin);
        let warningMax = parseFloat(element.dataset.warningMax);
        if (!isNaN(warningMin) && !isNaN(warningMax) && (numValue < warningMin || numValue > warningMax)) {
            element.classList.add('warning');
            if (warningSpan) {
                warningSpan.textContent = vitalsTranslations['outsideRange'] || '';
            }
            return { valid: true, warning: true };
        }

        return { valid: true, warning: false };
    }

    function vitalsFormSubmitted() {
        let vitalsForm = document.getElementById('vitalsForm');
        if (!vitalsForm) {
            return false;
        }

        let inputs = vitalsForm.querySelectorAll('input[data-min]');
        let hasErrors = false;

        inputs.forEach(function(input) {
            let result = validateVitalField(input);
            if (!result.valid) {
                hasErrors = true;
            }
        });

        if (hasErrors) {
            alert(vitalsTranslations['validateFailed']);
            return false;
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
            node.addEventListener('change', function(evt) {
                validateVitalField(evt.currentTarget);
                // Also validate the counterpart conversion field
                let targetConvId = evt.currentTarget.dataset.targetInputConv;
                if (targetConvId) {
                    let counterpart = document.getElementById(targetConvId);
                    if (counterpart && counterpart.dataset.min) {
                        validateVitalField(counterpart);
                    }
                }
            });
        });

        // Real-time validation for non-conversion inputs
        let vitalsValidatedInputs = vitalsForm.querySelectorAll('input[data-min]:not(.vitals-conv-unit)');
        vitalsValidatedInputs.forEach(function(node) {
            node.addEventListener('change', function(evt) {
                validateVitalField(evt.currentTarget);
            });
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
