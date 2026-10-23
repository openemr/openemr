const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const root = path.join(__dirname, '../../interface/forms/eye_mag');

class Element {
    constructor() { this.listeners = {}; this.children = []; this.attributes = {}; this.style = {}; this.value = ''; this.textContent = ''; }
    addEventListener(event, callback) { this.listeners[event] = callback; }
    click() { if (!this.disabled) this.listeners.click?.(); }
    setAttribute(name, value) { this.attributes[name] = value; }
    getAttribute(name) { return this.attributes[name]; }
    appendChild(child) { this.children.push(child); }
    replaceChildren() { this.children = []; }
}
function row() {
    const result = new Element();
    const parts = Object.fromEntries(['.materials-pdf-name', '[data-action="open"]', '[data-action="print"]', '[data-action="remove"]'].map(key => [key, new Element()]));
    result.querySelector = key => parts[key];
    result.cloneNode = row;
    return result;
}
function setup(ids = [], available = ['10', '20', '30']) {
    const elements = Object.fromEntries(['MATERIALS_HANDOUT_PICKER', 'MATERIALS_HANDOUT_IDS', 'OPEN_MATERIALS_HANDOUT', 'MATERIALS_ADDED_PDFS', 'MATERIALS_PDF_ROW'].map(key => [key, new Element()]));
    const picker = elements.MATERIALS_HANDOUT_PICKER;
    picker.options = available.map(value => {
        const option = new Element();
        option.value = value;
        option.textContent = 'PDF <' + value + '>';
        option.setAttribute('data-patient-id', value === '20' ? '2' : '-1');
        return option;
    });
    picker.querySelectorAll = () => picker.options;
    elements.MATERIALS_HANDOUT_IDS.value = JSON.stringify(ids);
    elements.MATERIALS_PDF_ROW.content = {firstElementChild: row()};
    const saves = [], pending = [], windows = [], urls = [], alerts = [];
    const document = {readyState: 'complete', getElementById: key => elements[key] || null};
    const window = {
        alert: value => alerts.push(value),
        open() {
            const popup = {document: {body: new Element(), createElement: () => new Element()}};
            windows.push(popup);
            return popup;
        }
    };
    const context = {window, document, base: '/openemr',
        top: {restoreSession() {}}, parent: {twAddFrameTab: (...args) => urls.push(args[2])},
        submit_form() {
            saves.push(JSON.parse(elements.MATERIALS_HANDOUT_IDS.value));
            return {always(callback) { pending.push(callback); }};
        }};
    vm.runInNewContext(fs.readFileSync(path.join(root, 'js/document_notes.js'), 'utf8'), context);
    return {elements, picker, saves, pending, windows, urls, alerts,
        ids: () => JSON.parse(elements.MATERIALS_HANDOUT_IDS.value),
        rows: () => elements.MATERIALS_ADDED_PDFS.children,
        add(id) { picker.value = id; elements.OPEN_MATERIALS_HANDOUT.click(); },
        flush() { while (pending.length) pending.shift()(); }};
}

test('adding three PDFs retains all; rapid saves serialize the latest list', () => {
    const ui = setup();
    ui.add('10'); ui.add('20'); ui.add('30');
    assert.deepEqual(ui.ids(), ['10', '20', '30']);
    assert.equal(ui.rows().length, 3);
    assert.deepEqual(ui.saves, [['10']]);
    ui.flush();
    assert.deepEqual(ui.saves, [['10'], ['10', '20', '30']]);
    assert.equal(ui.picker.value, '');
});
test('duplicate is ignored; removing one does not remove siblings; reload preserves order', () => {
    const ui = setup([10, 20, 30]);
    ui.add('20');
    assert.deepEqual(ui.ids(), ['10', '20', '30']);
    assert.equal(ui.saves.length, 0);
    ui.rows()[1].querySelector('[data-action="remove"]').click();
    ui.flush();
    assert.deepEqual(ui.saves, [['10', '30']]);
    const reloaded = setup(ui.ids());
    assert.deepEqual(reloaded.rows().map(item => item.getAttribute('data-document-id')), ['10', '30']);
    assert.equal(reloaded.rows()[0].querySelector('.materials-pdf-name').textContent, 'PDF <10>');
});
test('each Open and Print targets its own document and patient', () => {
    const ui = setup([10, 20]);
    ui.rows()[0].querySelector('[data-action="open"]').click();
    ui.rows()[1].querySelector('[data-action="open"]').click();
    assert.match(ui.urls[0], /patient_id=0&document_id=10&/);
    assert.match(ui.urls[1], /patient_id=2&document_id=20&/);
    ui.rows()[1].querySelector('[data-action="print"]').click();
    assert.match(ui.windows[0].document.body.children[1].src, /patient_id=2&document_id=20&/);
});
test('unavailable prior selection remains removable without exposing document actions', () => {
    const ui = setup([99, 10]);
    assert.match(ui.rows()[0].querySelector('.materials-pdf-name').textContent, /Unavailable PDF #99/);
    assert.equal(ui.rows()[0].querySelector('[data-action="open"]').disabled, true);
    assert.equal(ui.rows()[0].querySelector('[data-action="print"]').disabled, true);
    ui.rows()[0].querySelector('[data-action="remove"]').click();
    assert.deepEqual(ui.ids(), ['10']);
    ui.rows()[0].querySelector('[data-action="remove"]').click();
    ui.flush();
    assert.deepEqual(ui.ids(), []);
    assert.deepEqual(ui.saves.at(-1), []);
    assert.equal(setup(ui.ids()).rows().length, 0);
});
test('single-choice convenience and empty catalog remain functional', () => {
    const only = setup([], ['20']);
    only.add('');
    assert.deepEqual(only.ids(), ['20']);
    const many = setup();
    many.add('');
    assert.equal(many.alerts.length, 1);
    assert.deepEqual(many.ids(), []);
    assert.equal(setup([], []).elements.OPEN_MATERIALS_HANDOUT.disabled, true);
});
test('form template uses a list payload, row template and accessible per-row buttons', () => {
    const view = fs.readFileSync(path.join(root, 'view.php'), 'utf8');
    assert.match(view, /name="MATERIALS_HANDOUT_IDS"/);
    assert.doesNotMatch(view, /name="MATERIALS_HANDOUT_ID"/);
    assert.match(view, /<template id="MATERIALS_PDF_ROW">/);
    const markup = view.slice(view.indexOf('<template id="MATERIALS_PDF_ROW">'), view.indexOf('</template>', view.indexOf('<template id="MATERIALS_PDF_ROW">')))
        .replace(/<\?php[\s\S]*?\?>/g, '');
    for (const action of ['open', 'print', 'remove']) {
        assert.match(markup, new RegExp('type="button" data-action="' + action + '"[^>]+aria-label='));
    }
    const save = fs.readFileSync(path.join(root, 'save.php'), 'utf8');
    assert.match(save, /eye_mag_save_materials\(\(int\) \$form_id, \(int\) \$pid, \$_POST\)/);
    const base = fs.readFileSync(path.join(root, 'js/eye_base.php'), 'utf8');
    const submit = base.slice(base.indexOf('function submit_form('), base.indexOf('function code_400('));
    assert.match(submit, /return \$\.ajax\(/);
});
