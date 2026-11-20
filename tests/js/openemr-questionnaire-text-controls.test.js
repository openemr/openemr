/**
 * @jest-environment jsdom
 */

/**
 * The simpler R4 item controls: a question's prompt, unit, lower and upper
 * display children placed in or beside its answer area, and spinner as a
 * drop-down for a choice. Under a group the same children stay ordinary text.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-text-controls.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

new Function('window', 'document', 'HTMLElement', 'HTMLInputElement', 'CustomEvent', fs.readFileSync(
    path.resolve(__dirname, '../../interface/forms/questionnaire_assessments/native/openemr_questionnaire.js'),
    'utf8'
))(global.window, global.document, global.window.HTMLElement, global.window.HTMLInputElement, global.window.CustomEvent);

const runtimeApi = global.window.OpenEMRQuestionnaire;
const control = (code) => ({
    url: 'http://hl7.org/fhir/StructureDefinition/questionnaire-itemControl',
    valueCodeableConcept: { coding: [{ system: 'http://hl7.org/fhir/questionnaire-item-control', code }] },
});
const display = (linkId, code, text) => ({ linkId, type: 'display', text, extension: [control(code)] });

function setup(items) {
    const container = document.createElement('div');
    document.body.appendChild(container);
    const runtime = runtimeApi.mount({ questionnaire: { resourceType: 'Questionnaire', status: 'active', item: items }, container });
    return { container, runtime };
}

const item = (root, linkId) => root.querySelector('[data-link-id="' + linkId + '"]');

beforeEach(() => {
    document.body.innerHTML = '';
});

test('prompt becomes the placeholder of a text entry', () => {
    const { container } = setup([{ linkId: 'q', type: 'string', text: 'City', item: [display('q-p', 'prompt', 'e.g. Springfield')] }]);
    expect(item(container, 'q').querySelector('input').placeholder).toBe('e.g. Springfield');
    expect(item(container, 'q-p')).toBeNull();
});

test('unit sits beside the answer space', () => {
    const { container } = setup([{ linkId: 'w', type: 'decimal', text: 'Weight', item: [display('w-u', 'unit', 'kg')] }]);
    const input = item(container, 'w').querySelector('input');
    expect(input.parentElement.classList.contains('input-group')).toBe(true);
    expect(item(container, 'w').querySelector('.oe-questionnaire-unit').textContent).toBe('kg');
});

test('lower and upper bracket the answer choices', () => {
    const { container } = setup([{
        linkId: 's', type: 'choice', text: 'Agree?', answerOption: [1, 2, 3].map((n) => ({ valueInteger: n })),
        item: [display('s-l', 'lower', 'Strongly disagree'), display('s-u', 'upper', 'Strongly agree')],
    }]);
    const answerArea = item(container, 's').querySelector(':scope > .oe-questionnaire-control');
    expect(answerArea.firstElementChild.textContent).toBe('Strongly disagree');
    expect(answerArea.lastElementChild.textContent).toBe('Strongly agree');
    expect(answerArea.querySelectorAll('input[type="radio"]')).toHaveLength(3);
});

test('under a group the same display children stay ordinary text', () => {
    const { container } = setup([{ linkId: 'g', type: 'group', text: 'Group', item: [display('g-u', 'unit', 'kg')] }]);
    expect(item(container, 'g-u').textContent).toContain('kg');
});

test('spinner shows a choice as a drop-down', () => {
    const { container } = setup([{ linkId: 'n', type: 'choice', text: 'How many?', extension: [control('spinner')], answerOption: [1, 2, 3].map((n) => ({ valueInteger: n })) }]);
    expect(item(container, 'n').querySelector('select')).not.toBeNull();
    expect(item(container, 'n').querySelector('input[type="radio"]')).toBeNull();
});
