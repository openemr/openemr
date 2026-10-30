/**
 * @jest-environment jsdom
 */

/**
 * The encounter form's POS select follows the facility (and the provider's
 * facility) even when "Set POS code in encounter" is off. The select is only
 * hidden then, not disabled, so it's still posted, and save.php keeps any
 * non-empty value: a stale POS from the first facility would be saved.
 *
 * Run with: npm run test:js -- tests/js/newpatient-pos.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

const $ = require('jquery');
global.$ = global.jQuery = window.$ = window.jQuery = $;
$.fn.tooltip = function () {
    return this;
};
window.datetimepickerTranslated = function () {};

new Function('window', fs.readFileSync(
    path.resolve(__dirname, '../../interface/forms/newpatient/newpatient.js'),
    'utf8'
))(window);

// Each ajax call answers like library/ajax/facility_ajax_code.php.
function stubAjax(reply) {
    const calls = [];
    $.ajax = (options) => {
        calls.push(options.data);
        return $.Deferred().resolve(JSON.stringify(reply(options.data))).promise();
    };
    return calls;
}

function setUpForm(isPosEnabled) {
    document.body.innerHTML = `
        <form id="new-encounter-form">
            <input type="hidden" name="facility_id" id="facility_id" value="3">
            <select name="facility_id_sel" id="facility_id_sel">
                <option value="3" selected>Clinic</option>
                <option value="4">Nursing home</option>
            </select>
            <select name="provider_id" id="provider_id">
                <option value="1" selected>Dr A</option>
                <option value="2">Dr B</option>
            </select>
            <select name="billing_facility" id="billing_facility">
                <option value="3" selected>Clinic</option>
                <option value="4">Nursing home</option>
            </select>
            <select name="pos_code" id="pos_code">
                <option value="01">01</option>
                <option value="11" selected>11</option>
                <option value="31">31</option>
            </select>
        </form>`;
    // jsdom has no named access on forms (document.forms[0].pos_code), which the script uses.
    const form = document.forms[0];
    for (const element of Array.from(form.elements)) {
        Object.defineProperty(form, element.name, { get: () => form.elements.namedItem(element.name) });
    }
    window.NewPatientForm.init({ webroot: '', pid: '9002', csrfToken: 'token', isPosEnabled });
}

describe.each([true, false])('set_pos_code_encounter %s', (isPosEnabled) => {
    test('a facility change takes that facility\'s POS', () => {
        const calls = stubAjax(() => '31');
        setUpForm(isPosEnabled);

        $('#facility_id_sel').val('4').trigger('change');

        expect(calls).toEqual([expect.objectContaining({ mode: 'get_pos', facility_id: '4' })]);
        expect(document.forms[0].pos_code.value).toBe('31');
    });

    test('a provider change takes the provider\'s facility and its POS', () => {
        stubAjax(() => ['4', '31', '1']);
        setUpForm(isPosEnabled);

        $('#provider_id').val('2').trigger('change');

        expect(document.forms[0].facility_id.value).toBe('4');
        expect(document.forms[0].pos_code.value).toBe('31');
    });
});
