/**
 * @jest-environment jsdom
 */

/**
 * Tests for the USA weight conversion in interface/forms/vitals/vitals.js,
 * including the pounds#ounces input form (e.g. "5#4" for 5 lb 4 oz).
 *
 * Run with: npm run test:js -- tests/js/vitals-weight-conversion.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

const src = fs.readFileSync(
    path.resolve(__dirname, '../../interface/forms/vitals/vitals.js'),
    'utf8'
);

const LB_TO_KG = 0.45359237;
const DECIMAL_WEIGHT_CASES = [
    ['5#4.', 5.25],
    ['5.#4', 5.25],
    ['.5#.4', 0.525],
    ['7.#', 7],
    ['#.5', 0.03125],
    [' 5. # .4 ', 5.025]
];

function loadVitals() {
    global.window.oeUI = { reasonCodeWidget: { init: () => {} } };
    return new Function('window', src + '\nreturn { convUnit, convLbtoKg };')(global.window);
}

const { convUnit, convLbtoKg } = loadVitals();

describe('convLbtoKg', () => {
    test('converts decimal pounds to kilograms', () => {
        expect(convLbtoKg('10')).toBeCloseTo(10 * LB_TO_KG, 10);
    });

    test('converts pounds#ounces to kilograms', () => {
        expect(convLbtoKg('5#4')).toBeCloseTo(5.25 * LB_TO_KG, 10);
        expect(convLbtoKg('5#4').toFixed(2)).toBe('2.38');
    });

    test('keeps fractional pounds and ounces in the pounds#ounces form', () => {
        expect(convLbtoKg('5.5#4')).toBeCloseTo(5.75 * LB_TO_KG, 10);
        expect(convLbtoKg('5#2.5')).toBeCloseTo((5 + 2.5 / 16) * LB_TO_KG, 10);
    });

    test.each(DECIMAL_WEIGHT_CASES)('converts decimal form %s to kilograms', (value, pounds) => {
        expect(convLbtoKg(value)).toBeCloseTo(pounds * LB_TO_KG, 10);
    });

    test('allows an omitted pounds or ounces part', () => {
        expect(convLbtoKg('7#')).toBeCloseTo(7 * LB_TO_KG, 10);
        expect(convLbtoKg('#12')).toBeCloseTo(0.75 * LB_TO_KG, 10);
    });

    test('rejects malformed pounds#ounces values', () => {
        expect(convLbtoKg('5#abc')).toBeNaN();
        expect(convLbtoKg('abc#4')).toBeNaN();
        expect(convLbtoKg('5#4#2')).toBeNaN();
        expect(convLbtoKg('-5#4')).toBeNaN();
        expect(convLbtoKg('#')).toBeNaN();
    });

    test('convUnit uses it for USA weight input', () => {
        expect(convUnit('usa', 'lbs', '5#4')).toBeCloseTo(5.25 * LB_TO_KG, 10);
    });
});

describe('vitals form weight input', () => {
    beforeAll(() => {
        document.body.innerHTML = `
            <form id="vitalsForm">
                <input type="hidden" id="weight_input" name="weight" value="" />
                <input type="text" class="vitals-conv-unit" id="weight_input_usa"
                       data-system="usa" data-unit="lbs" data-target-input="weight_input"
                       data-target-input-conv="weight_input_metric" data-min="0" data-max="2000" />
                <input type="text" class="vitals-conv-unit" id="weight_input_metric"
                       data-system="metric" data-unit="lbs" data-target-input="weight_input"
                       data-target-input-conv="weight_input_usa" data-min="0" data-max="910" />
            </form>`;
        global.window.vitalsForm.init('', {});
        document.dispatchEvent(new Event('DOMContentLoaded'));
    });

    function enterUsaWeight(value) {
        const usa = document.getElementById('weight_input_usa');
        usa.value = value;
        usa.dispatchEvent(new Event('change'));
    }

    test('pounds#ounces shows kilograms and saves decimal pounds', () => {
        enterUsaWeight('5#4');
        expect(document.getElementById('weight_input_metric').value).toBe('2.38');
        expect(document.getElementById('weight_input').value).toBe('5.25');
    });

    test.each(DECIMAL_WEIGHT_CASES)('changing a valid weight to %s updates the saved weight', (value, pounds) => {
        enterUsaWeight('6#8');
        expect(document.getElementById('weight_input').value).toBe('6.5');

        enterUsaWeight(value);
        expect(document.getElementById('weight_input_metric').value).toBe((pounds * LB_TO_KG).toFixed(2));
        expect(document.getElementById('weight_input').value).toBe(String(pounds));
        expect(document.getElementById('weight_input_usa').classList.contains('error')).toBe(false);
    });

    test.each(['5#abc', '5#4#2', '-5#4', '#', '.#4', '5#4.2.3', '5#1e2'])(
        'rejects %s on change and blocks submission of the previous weight', (value) => {
            const consoleError = jest.spyOn(console, 'error').mockImplementation(() => {});
            const alert = jest.spyOn(window, 'alert').mockImplementation(() => {});
            window.vitalsTranslations = { validateFailed: 'Please correct invalid fields.' };
            window.restoreSession = jest.fn(() => true);
            const usa = document.getElementById('weight_input_usa');
            usa.scrollIntoView = jest.fn();

            try {
                enterUsaWeight('6#8');
                enterUsaWeight(value);
                expect(usa.classList.contains('error')).toBe(true);

                const submit = new Event('submit', { cancelable: true });
                document.getElementById('vitalsForm').dispatchEvent(submit);
                expect(submit.defaultPrevented).toBe(true);
                expect(window.restoreSession).not.toHaveBeenCalled();
                expect(alert).toHaveBeenCalledWith(window.vitalsTranslations.validateFailed);
            } finally {
                consoleError.mockRestore();
                alert.mockRestore();
                delete window.vitalsTranslations;
                delete window.restoreSession;
                delete usa.scrollIntoView;
            }
        }
    );

    test('decimal pounds are saved as entered', () => {
        enterUsaWeight('150');
        expect(document.getElementById('weight_input_metric').value).toBe('68.04');
        expect(document.getElementById('weight_input').value).toBe('150');
    });
});
