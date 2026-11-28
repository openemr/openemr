/**
 * @jest-environment jsdom
 */

/**
 * Vitals pounds#ounces weight conversion tests (#14700).
 *
 * convLbtoKg() returned the pounds#ounces value unconverted (and parseInt
 * dropped fractional pounds), so 5#4 showed 5.25 "kg" instead of 2.38 kg.
 * The fix reuses parseUsWeightValue() and multiplies by 0.45359237.
 *
 * Run with: npm run test:js -- tests/js/vitals-weight-pounds-ounces.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

const source = fs.readFileSync(
    path.resolve(__dirname, '../../interface/forms/vitals/vitals.js'),
    'utf8'
);

function buildForm({ weight = '', height = '' } = {}) {
    document.body.innerHTML = `
        <form id="vitalsForm">
            <input type="text" class="vitals-conv-unit" id="weight_input_usa" value="${weight}"
                data-system="usa" data-unit="lbs" data-target-input="weight_input"
                data-target-input-conv="weight_input_metric" />
            <input type="text" id="weight_input_metric" />
            <input type="hidden" id="weight_input" name="weight" value="${weight}" />
            <input type="text" id="height_input_usa" value="${height}" />
            <input type="text" id="BMI_input" name="BMI" value="" data-precision="2" />
        </form>`;
}

function initVitals() {
    window.oeUI = { reasonCodeWidget: { init: jest.fn() } };
    window.vitalsTranslations = {};
    window.restoreSession = () => true;
    window.alert = jest.fn();
    window.Element.prototype.scrollIntoView = jest.fn();
    window.eval(source);
    window.vitalsForm.init('', {});
    document.dispatchEvent(new Event('DOMContentLoaded'));
}

function typeChange(id, value) {
    const el = document.getElementById(id);
    el.value = value;
    el.dispatchEvent(new Event('change', { bubbles: true }));
}

const val = (id) => document.getElementById(id).value;

describe('vitals pounds#ounces weight conversion (#14700)', () => {
    beforeEach(() => {
        jest.spyOn(console, 'error').mockImplementation(() => {});
    });

    afterEach(() => {
        jest.restoreAllMocks();
    });

    test('5#4 converts to 2.38 kg', () => {
        buildForm();
        initVitals();
        typeChange('weight_input_usa', '5#4');
        expect(val('weight_input_metric')).toBe('2.38');
    });

    test('fractional pounds keep the fraction (5.5#4 -> 2.61 kg)', () => {
        buildForm();
        initVitals();
        typeChange('weight_input_usa', '5.5#4');
        expect(val('weight_input_metric')).toBe('2.61');
    });

    test('ounces-only entry converts (0#8 -> 0.23 kg)', () => {
        buildForm();
        initVitals();
        typeChange('weight_input_usa', '0#8');
        expect(val('weight_input_metric')).toBe('0.23');
    });

    test('plain pounds still convert (150 -> 68.04 kg)', () => {
        buildForm();
        initVitals();
        typeChange('weight_input_usa', '150');
        expect(val('weight_input_metric')).toBe('68.04');
    });

    test('malformed pounds#ounces leaves the metric field untouched', () => {
        buildForm();
        initVitals();
        typeChange('weight_input_usa', '5#abc');
        expect(val('weight_input_metric')).toBe('');
    });
});