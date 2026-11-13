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

// Answers held back until the test resolves them, to control the order replies arrive in.
function holdAjax() {
    const pending = [];
    $.ajax = (options) => {
        const deferred = $.Deferred();
        pending.push({ data: options.data, answer: (reply) => deferred.resolve(JSON.stringify(reply)) });
        return deferred.promise();
    };
    return pending;
}

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

describe('set_pos_code_encounter off', () => {
    test('a provider at the same facility keeps the POS (an existing encounter\'s saved one)', () => {
        stubAjax(() => ['3', '31', '1']);
        setUpForm(false);

        $('#provider_id').val('2').trigger('change');

        expect(document.forms[0].pos_code.value).toBe('11');
    });

    test('until the facility lookup answers, no POS is posted', () => {
        const pending = holdAjax();
        setUpForm(false);

        $('#facility_id_sel').val('4').trigger('change');

        expect(new FormData(document.forms[0]).has('pos_code')).toBe(false);
        pending[0].answer('31');
        expect(new FormData(document.forms[0]).get('pos_code')).toBe('31');
    });

    test('a late answer to an earlier change is ignored', () => {
        const pending = holdAjax();
        setUpForm(false);

        $('#facility_id_sel').val('4').trigger('change');
        $('#facility_id_sel').val('3').trigger('change');
        pending[1].answer('11');
        pending[0].answer('31');

        expect(document.forms[0].pos_code.value).toBe('11');
    });

    test('a late facility answer doesn\'t overwrite a newer provider change', () => {
        const pending = holdAjax();
        setUpForm(false);

        $('#facility_id_sel').val('4').trigger('change');
        $('#provider_id').val('2').trigger('change');
        pending[1].answer(['3', '11', '1']);
        pending[0].answer('31');

        expect(document.forms[0].facility_id.value).toBe('3');
        expect(document.forms[0].pos_code.value).toBe('11');
    });

    test('a provider\'s billing facility still applies after a later facility change', () => {
        const pending = holdAjax();
        setUpForm(false);

        $('#provider_id').val('2').trigger('change');
        $('#facility_id_sel').val('3').trigger('change');
        pending[0].answer(['4', '31', '1']);
        pending[1].answer('11');

        expect(document.forms[0].facility_id.value).toBe('3');
        expect(document.forms[0].pos_code.value).toBe('11');
        expect(document.forms[0].billing_facility.value).toBe('4');
    });

    test('a provider at the new facility fills the POS a pending facility lookup left empty', () => {
        const pending = holdAjax();
        setUpForm(false);

        $('#facility_id_sel').val('4').trigger('change');
        $('#provider_id').val('2').trigger('change');
        pending[1].answer(['4', '31', '0']);
        pending[0].answer('31');

        expect(document.forms[0].pos_code.value).toBe('31');
    });
});
