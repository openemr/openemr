/**
 * @jest-environment jsdom
 */

/**
 * Exercise diagnosis save ordering and checksum propagation through the DOM.
 *
 * @package OpenEMR
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
/* global __dirname, codeselect_and_save, codeselect_and_save_queue, refresh_codes */
const fs = require('fs');
const path = require('path');
const $ = require('jquery');
const source = fs.readFileSync(
    path.resolve(__dirname, '../../interface/forms/fee_sheet/review/js/fee_sheet_core.js'), 'utf8'
);
let requests;

function response(checksum, message = '') {
    return `<div><table cellspacing="5"><tbody></tbody></table>
        <input name="form_checksum" value="${checksum}">
        <input name="form_alertmsg" value="${message}"></div>`;
}

async function flushQueue() {
    for (let i = 0; i < 5; ++i) await Promise.resolve();
}

function pick(index) {
    const select = document.getElementById('codes');
    select.selectedIndex = index;
    codeselect_and_save(select);
}

beforeEach(() => {
    document.body.innerHTML = `<form>
        <input name="form_checksum" value="111">
        <input name="newcodes" value="">
        <select id="codes"><option value="">Select</option>
            <option value="ICD10:A01.0">A01.0</option>
            <option value="ICD10:A02.0">A02.0</option></select>
        <table cellspacing="5"><tbody></tbody></table>
    </form>`;
    // jsdom does not expose named form controls as properties.
    document.forms[0].newcodes = document.querySelector('[name=newcodes]');
    global.$ = window.$ = $;
    global.restoreSession = jest.fn();
    global.alert = jest.fn();
    global.fee_sheet_new = '/interface/forms/fee_sheet/new.php';
    global.display_table_selector = "table[cellspacing='5']";
    global.ippf_specific = false;
    global.diags = [];
    requests = [];
    $.post = jest.fn((url, data, success) => {
        const deferred = $.Deferred();
        // refresh_codes() uses the third-argument success form rather than
        // .done(), so the stub has to honour both.
        if (typeof success === 'function') {
            deferred.done(success);
        }
        requests.push({url, data: new URLSearchParams(data), deferred});
        return deferred.promise();
    });
    // Execute the production script, including update_display_table().
    (0, eval)(source);
});

test('rapid selections use each preceding response checksum and finish both saves', async () => {
    pick(1);
    pick(2);
    await flushQueue();
    expect(requests).toHaveLength(1);
    expect(requests[0].data.get('newcodes')).toBe('ICD10:A01.0');
    expect(requests[0].data.get('form_checksum')).toBe('111');
    requests[0].deferred.resolve(response('222'));
    await flushQueue();
    expect(requests).toHaveLength(2);
    expect(requests[1].data.get('bn_save')).toBe('Save');
    expect(requests[1].data.get('newcodes')).toBe('');
    expect(requests[1].data.get('form_checksum')).toBe('222');
    requests[1].deferred.resolve(response('333'));
    await flushQueue();
    expect(requests).toHaveLength(3);
    expect(requests[2].data.get('newcodes')).toBe('ICD10:A02.0');
    expect(requests[2].data.get('form_checksum')).toBe('333');
    requests[2].deferred.resolve(response('444'));
    expect(requests).toHaveLength(4);
    expect(requests[3].data.get('form_checksum')).toBe('444');
    requests[3].deferred.resolve(response('555'));
    await codeselect_and_save_queue;
    expect(document.querySelector('[name=form_checksum]').value).toBe('555');
    expect(requests).toHaveLength(4);
});

test('refresh_codes carries the post-justification checksum back to the form', async () => {
    // fee_sheet_justify.php task=update rewrites billing.justify, which is part
    // of FeeSheet::visitChecksum(). The justify view model calls refresh_codes()
    // once that post returns; this is what stops the next save from reporting a
    // false conflict against the pre-justification checksum.
    expect(document.querySelector('[name=form_checksum]').value).toBe('111');
    refresh_codes();
    expect(requests).toHaveLength(1);
    expect(requests[0].url).toBe('/interface/forms/fee_sheet/new.php');
    expect(requests[0].data.get('running_as_ajax')).toBe('1');
    // No checksum is sent, so new.php re-renders instead of validating.
    expect(requests[0].data.get('form_checksum')).toBeNull();
    requests[0].deferred.resolve(response('777'));
    await flushQueue();
    expect(document.querySelector('[name=form_checksum]').value).toBe('777');
});

test('missing selection and placeholder do not post', async () => {
    codeselect_and_save(null);
    pick(0);
    await flushQueue();
    expect(requests).toHaveLength(0);
});

test('refresh failure reports the error, keeps the code, and still releases the queue', async () => {
    pick(1);
    pick(2);
    await flushQueue();
    requests[0].deferred.reject();
    expect(global.alert).toHaveBeenCalledWith('Fee sheet update failed. Please try again.');
    expect(document.forms[0].newcodes.value).toBe('ICD10:A01.0');
    await flushQueue();
    expect(requests).toHaveLength(2);
    expect(requests[1].data.get('newcodes')).toBe('ICD10:A02.0');
    requests[1].deferred.resolve(response('222'));
    requests[2].deferred.resolve(response('333'));
    await codeselect_and_save_queue;
});

test('save failure reports the error, keeps the code, and still releases the next selection', async () => {
    pick(1);
    pick(2);
    await flushQueue();
    requests[0].deferred.resolve(response('222'));
    requests[1].deferred.reject();
    expect(global.alert).toHaveBeenCalledWith('Fee sheet update failed. Please try again.');
    expect(document.forms[0].newcodes.value).toBe('ICD10:A01.0');
    await flushQueue();
    expect(requests).toHaveLength(3);
    requests[2].deferred.resolve(response('333'));
    requests[3].deferred.resolve(response('444'));
    await codeselect_and_save_queue;
});

test('server conflict is displayed and the conflicting refresh is not saved', async () => {
    pick(1);
    await flushQueue();
    requests[0].deferred.resolve(response('222', 'Concurrent edit'));
    await codeselect_and_save_queue;
    expect(global.alert).toHaveBeenCalledWith('Concurrent edit');
    expect(requests).toHaveLength(1);
});


test('category chooser submits the captured code batch without a select element', async () => {
    document.forms[0].newcodes.value = 'CPT4|99213|~CPT4|99214|';
    codeselect_and_save(null);
    expect(document.forms[0].newcodes.value).toBe('');
    await flushQueue();
    expect(requests[0].data.get('newcodes')).toBe('CPT4|99213|~CPT4|99214|');
    requests[0].deferred.resolve(response('222'));
    requests[1].deferred.resolve(response('333'));
    await codeselect_and_save_queue;
    expect(document.querySelector('[name=form_checksum]').value).toBe('333');
});

describe('saving before the justification dialog', () => {
    let ajax;
    let anchor;

    beforeEach(() => {
        document.forms[0].id = 'fee_sheet_form';
        document.querySelector('tbody').innerHTML = `<tr>
            <td billing_id="42"><a class="justify_label">99213</a></td>
            <td><select onchange="setJustify(this)"><option value="">None</option></select></td>
        </tr>`;
        anchor = document.querySelector('.justify_label');
        ajax = $.Deferred();
        $.ajax = jest.fn(() => ajax.promise());
        global.enc = 1;
        global.pid = 1;
        global.ko = {cleanNode: jest.fn(), applyBindings: jest.fn()};
        global.fee_sheet_justify_view_model = jest.fn();
    });

    test('updates the checksum without discarding the clicked row', () => {
        global.justify_start.call(anchor);
        expect($.ajax.mock.calls[0][0].data.get('dx_update')).toBe('1');
        ajax.resolve(response('222'));
        expect(document.querySelector('[name=form_checksum]').value).toBe('222');
        expect(document.querySelector('.justify_label')).toBe(anchor);
        expect(global.ko.applyBindings).toHaveBeenCalledTimes(1);
    });

    test('shows a conflict and does not open justification after a rejected save', () => {
        global.justify_start.call(anchor);
        ajax.resolve(response('222', 'Concurrent edit'));
        expect(global.alert).toHaveBeenCalledWith('Concurrent edit');
        expect(global.ko.applyBindings).not.toHaveBeenCalled();
        expect(document.querySelector('[name=form_checksum]').value).toBe('111');
        expect(document.querySelector('#wait')).toBeNull();
    });
});
