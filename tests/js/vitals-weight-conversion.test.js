/**
 * @jest-environment jsdom
 */

/**
 * Vitals pounds#ounces weight conversion tests (#14700).
 *
 * convLbtoKg() must convert the US pounds#ounces form to kilograms with the
 * exact factor, keep fractional pounds, and reject malformed input.
 *
 * Run with: npm run test:js -- tests/js/vitals-weight-conversion.test.js
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

function buildForm({ height = '', weight = '', bmi = '' } = {}) {
    document.body.innerHTML = `
        <form id="vitalsForm">
            <div id="height_input_usa_feet_inches" hidden>
                <input type="text" id="height_input_usa_feet" />
                <input type="text" id="height_input_usa_inches" />
            </div>
            <input type="text" class="vitals-conv-unit" id="height_input_usa" value="${height}"
                data-system="usa" data-unit="in" data-target-input="height_input"
                data-target-input-conv="height_input_metric" />
            <input type="text" class="vitals-conv-unit" id="height_input_metric"
                data-system="metric" data-unit="cm" data-target-input="height_input"
                data-target-input-conv="height_input_usa" />
            <input type="hidden" id="height_input" name="height" value="${height}" />
            <input type="text" class="vitals-conv-unit" id="weight_input_usa" value="${weight}"
                data-system="usa" data-unit="lbs" data-target-input="weight_input"
                data-target-input-conv="weight_input_metric" />
            <input type="text" id="weight_input_metric" />
            <input type="hidden" id="weight_input" name="weight" value="${weight}" />
            <input type="text" id="BMI_input" name="BMI" value="${bmi}" data-precision="2" />
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

// .vitals-conv-unit inputs convert on 'change' (vitals.js initDOMEvents)
function change(id, value) {
    const el = document.getElementById(id);
    el.value = value;
    el.dispatchEvent(new Event('change', { bubbles: true }));
}

const val = (id) => document.getElementById(id).value;

describe('vitals pounds#ounces weight conversion', () => {
    beforeEach(() => {
        jest.spyOn(console, 'error').mockImplementation(() => {});
        buildForm();
        initVitals();
    });

    afterEach(() => {
        jest.restoreAllMocks();
    });

    test('5#4 converts to kilograms, not pounds', () => {
        expect(window.convLbtoKg('5#4')).toBeCloseTo(2.3813599425, 9);
        expect(window.convLbtoKg(' 5 # 4 ')).toBeCloseTo(2.3813599425, 9);
    });

    test('fractional pounds with ounces are kept', () => {
        expect(window.convLbtoKg('5.5#4')).toBeCloseTo(2.6081561275, 9);
    });

    test('plain pounds still convert', () => {
        expect(window.convLbtoKg('150')).toBeCloseTo(68.0388555, 9);
    });

    test('zero stays zero', () => {
        expect(window.convLbtoKg('0')).toBe(0);
    });

    test('malformed input is rejected', () => {
        expect(window.convLbtoKg('abc')).toBe(0);
        expect(window.convLbtoKg('')).toBe(0);
        expect(window.convLbtoKg('5#')).toBe(0);
        expect(window.convLbtoKg('5#4#2')).toBe(0);
    });

    test('kg display field shows 2.38 for 5#4 (issue repro)', () => {
        change('weight_input_usa', '5#4');
        expect(val('weight_input_metric')).toBe('2.38');
    });
});