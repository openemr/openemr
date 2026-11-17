/**
 * @jest-environment jsdom
 */

/**
 * Vitals feet/inches height helper tests.
 *
 * Covers the helper fields shown from the stored total inches, live conversion
 * to the total, malformed input blocking the submit, and that saving a form
 * without editing the height leaves the stored height and BMI untouched.
 *
 * Run with: npm run test:js -- tests/js/vitals-height-helper.test.js
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

function submitForm() {
    const event = new Event('submit', { bubbles: true, cancelable: true });
    document.getElementById('vitalsForm').dispatchEvent(event);
    return event;
}

function type(id, value) {
    const el = document.getElementById(id);
    el.value = value;
    el.dispatchEvent(new Event('input', { bubbles: true }));
}

const val = (id) => document.getElementById(id).value;

describe('vitals feet/inches height helper', () => {
    beforeEach(() => {
        jest.spyOn(console, 'error').mockImplementation(() => {});
    });

    afterEach(() => {
        jest.restoreAllMocks();
    });

    test('shows the stored total as feet and inches', () => {
        buildForm({ height: '62' });
        initVitals();
        expect(val('height_input_usa_feet')).toBe('5');
        expect(val('height_input_usa_inches')).toBe('2');
    });

    test('shows 72 inches as 6 ft 0 in', () => {
        buildForm({ height: '72' });
        initVitals();
        expect(val('height_input_usa_feet')).toBe('6');
        expect(val('height_input_usa_inches')).toBe('0');
    });

    test('typing feet and inches updates the total inches', () => {
        buildForm();
        initVitals();
        type('height_input_usa_feet', '5');
        type('height_input_usa_inches', '2');
        expect(val('height_input_usa')).toBe('62');
        expect(val('height_input')).toBe('62');
    });

    test('blank feet with inches only keeps the inches', () => {
        buildForm();
        initVitals();
        type('height_input_usa_inches', '72');
        expect(val('height_input_usa')).toBe('72');
    });

    test('live BMI follows the helper height', () => {
        buildForm({ weight: '150' });
        initVitals();
        type('height_input_usa_feet', '5');
        type('height_input_usa_inches', '2');
        expect(val('BMI_input')).toBe('27.43');
    });

    test('malformed helper input blocks the submit and clears the height', () => {
        buildForm({ height: '62' });
        initVitals();
        type('height_input_usa_feet', '5abc');
        expect(val('height_input_usa')).toBe('');
        const event = submitForm();
        expect(event.defaultPrevented).toBe(true);
        expect(window.alert).toHaveBeenCalled();
    });

    test('saving without editing keeps a height stored with more than two decimals', () => {
        // 180 cm is stored as 70.866142 inches.
        buildForm({ height: '70.866142', weight: '150', bmi: '21.00' });
        initVitals();
        const event = submitForm();
        expect(event.defaultPrevented).toBe(false);
        expect(val('height_input_usa')).toBe('70.866142');
        expect(val('height_input')).toBe('70.866142');
    });

    test('saving without editing keeps a stored BMI when there is no weight', () => {
        buildForm({ height: '70', weight: '', bmi: '25.00' });
        initVitals();
        const event = submitForm();
        expect(event.defaultPrevented).toBe(false);
        expect(val('BMI_input')).toBe('25.00');
        expect(val('height_input_usa')).toBe('70');
    });
});
