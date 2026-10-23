/**
 * @jest-environment jsdom
 */

/**
 * SDC 4.0.0 Advanced Behavior: cqf-expression on required, readOnly and
 * text; answerExpression; answerOptionsToggleExpression; usageMode;
 * unitValueSet; referenceResource; targetConstraint; minQuantity and
 * maxQuantity.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-advanced-behavior.test.js
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
const SDC = 'http://hl7.org/fhir/uv/sdc/StructureDefinition/';
const CORE = 'http://hl7.org/fhir/StructureDefinition/';
const fhirpath = (expression) => ({ language: 'text/fhirpath', expression });
const cqf = (expression) => ({ extension: [{ url: CORE + 'cqf-expression', valueExpression: fhirpath(expression) }] });
const answerOf = (linkId) => "%resource.item.where(linkId = '" + linkId + "').answer.value";

function setup(items, extra = {}) {
    const container = document.createElement('div');
    document.body.appendChild(container);
    const runtime = runtimeApi.mount({ questionnaire: { resourceType: 'Questionnaire', status: 'active', item: items, ...extra }, container });
    return { container, runtime };
}

const item = (root, linkId) => root.querySelector('[data-link-id="' + linkId + '"]');
const yes = (runtime, linkId, value) => runtime.setAnswers(linkId, [{ valueKey: 'valueBoolean', value }]);

beforeEach(() => {
    document.body.innerHTML = '';
});

describe('expressions on required, readOnly and text', () => {
    const items = [
        { linkId: 'smoker', type: 'boolean', text: 'Do you smoke?' },
        { linkId: 'packs', type: 'integer', text: 'Packs a day', _required: cqf(answerOf('smoker') + ' = true') },
        { linkId: 'note', type: 'string', text: 'Note', _readOnly: cqf(answerOf('smoker') + ' = false') },
        { linkId: 'greeting', type: 'display', text: 'Hello' },
        { linkId: 'name', type: 'string', text: 'Name' },
        { linkId: 'echo', type: 'string', text: 'Your name', _text: cqf("'Name given: ' & " + answerOf('name')) },
    ];

    test('requiredExpression decides whether the question is required, in validation and for assistive technology', () => {
        const { container, runtime } = setup(items);
        expect(runtime.validate().valid).toBe(true);
        yes(runtime, 'smoker', true);
        expect(runtime.validate().issues.map((issue) => issue.linkId)).toEqual(['packs']);
        expect(item(container, 'packs').querySelector('input').getAttribute('aria-required')).toBe('true');
        expect(item(container, 'packs').querySelector('.oe-questionnaire-label .text-danger')).not.toBeNull();
        yes(runtime, 'smoker', false);
        expect(runtime.validate().valid).toBe(true);
        expect(item(container, 'packs').querySelector('input').hasAttribute('aria-required')).toBe(false);
    });

    test('readOnlyExpression disables and re-enables the entry', () => {
        const { container, runtime } = setup(items);
        yes(runtime, 'smoker', false);
        expect(item(container, 'note').querySelector('input').disabled).toBe(true);
        yes(runtime, 'smoker', true);
        expect(item(container, 'note').querySelector('input').disabled).toBe(false);
    });

    test('a text expression rewrites the question\'s label', () => {
        const { container, runtime } = setup(items);
        runtime.setAnswers('name', [{ valueKey: 'valueString', value: 'Ana' }]);
        expect(item(container, 'echo').querySelector('.oe-questionnaire-label').textContent).toBe('Name given: Ana');
    });
});

describe('answer options from expressions', () => {
    test('answerExpression gives the answers, and the list is rebuilt when it changes', () => {
        const { container, runtime } = setup([
            { linkId: 'kids', type: 'integer', text: 'How many children?' },
            {
                linkId: 'youngest', type: 'choice', text: 'Which is the youngest?',
                extension: [{ url: SDC + 'sdc-questionnaire-answerExpression', valueExpression: fhirpath("iif(" + answerOf('kids') + " > 1, 'First' | 'Second', 'Only')") }],
            },
        ]);
        const labels = () => [...item(container, 'youngest').querySelectorAll('.oe-questionnaire-choice-label')].map((label) => label.textContent);
        expect(labels()).toEqual(['Only']);
        runtime.setAnswers('kids', [{ valueKey: 'valueInteger', value: 2 }]);
        expect(labels()).toEqual(['First', 'Second']);
        expect(item(container, 'youngest').querySelector(':scope > .oe-questionnaire-control').getAttribute('role')).toBe('radiogroup');
    });

    test('answerOptionsToggleExpression turns its options off while false', () => {
        const { container, runtime } = setup([
            { linkId: 'pregnant', type: 'boolean', text: 'Pregnant?' },
            {
                linkId: 'care', type: 'choice', text: 'Care', answerOption: [{ valueString: 'General' }, { valueString: 'Prenatal' }],
                extension: [{
                    url: SDC + 'sdc-questionnaire-answerOptionsToggleExpression',
                    extension: [{ url: 'option', valueString: 'Prenatal' }, { url: 'expression', valueExpression: fhirpath(answerOf('pregnant') + ' = true') }],
                }],
            },
        ]);
        const disabled = () => [...item(container, 'care').querySelectorAll('input[type="radio"]')].map((radio) => radio.disabled);
        expect(disabled()).toEqual([false, true]);
        yes(runtime, 'pregnant', true);
        expect(disabled()).toEqual([false, false]);
    });
});

describe('static behaviour extensions', () => {
    test('usageMode display keeps an item out of the form being filled', () => {
        const { container, runtime } = setup([
            { linkId: 'summary', type: 'string', text: 'Summary', required: true, extension: [{ url: CORE + 'questionnaire-usageMode', valueCode: 'display' }] },
        ]);
        expect(item(container, 'summary').classList.contains('d-none')).toBe(true);
        expect(runtime.validate().valid).toBe(true);
    });

    test('unitValueSet offers a contained ValueSet\'s units', () => {
        const { container } = setup([
            { linkId: 'w', type: 'quantity', text: 'Weight', extension: [{ url: CORE + 'questionnaire-unitValueSet', valueCanonical: '#weight-units' }] },
        ], {
            contained: [{
                resourceType: 'ValueSet', id: 'weight-units', status: 'active',
                expansion: { timestamp: '2026-10-23', contains: [{ system: 'http://unitsofmeasure.org', code: 'kg', display: 'kg' }, { system: 'http://unitsofmeasure.org', code: '[lb_av]', display: 'lb' }] },
            }],
        });
        const units = [...item(container, 'w').querySelectorAll('select option')].map((option) => option.textContent).filter(Boolean);
        expect(units).toEqual(['kg', 'lb']);
    });

    test('referenceResource hints and checks the referenced type', () => {
        const { container, runtime } = setup([
            { linkId: 'gp', type: 'reference', text: 'Your doctor', extension: [{ url: CORE + 'questionnaire-referenceResource', valueCode: 'Practitioner' }] },
        ]);
        expect(item(container, 'gp').querySelector('input').placeholder).toBe('Reference to Practitioner (e.g. Practitioner/123)');
        runtime.setAnswers('gp', [{ valueKey: 'valueReference', value: { reference: 'Patient/1' } }]);
        expect(runtime.validate().issues.map((issue) => issue.code)).toEqual(['reference-type']);
        runtime.setAnswers('gp', [{ valueKey: 'valueReference', value: { reference: 'Practitioner/7' } }]);
        expect(runtime.validate().valid).toBe(true);
    });

    test('targetConstraint is checked like questionnaire-constraint', () => {
        const { runtime } = setup([{
            linkId: 'age', type: 'integer', text: 'Age', extension: [{
                url: CORE + 'targetConstraint',
                extension: [
                    { url: 'key', valueId: 'age-1' },
                    { url: 'severity', valueCode: 'error' },
                    { url: 'expression', valueExpression: fhirpath(answerOf('age') + ' < 130') },
                    { url: 'human', valueString: 'Age must be under 130' },
                ],
            }],
        }]);
        runtime.setAnswers('age', [{ valueKey: 'valueInteger', value: 140 }]);
        expect(runtime.validate().issues.map((issue) => issue.message)).toEqual(['Age must be under 130']);
    });

    test('minQuantity and maxQuantity bound an answer in the same unit', () => {
        const kg = (value) => ({ value, unit: 'kg', system: 'http://unitsofmeasure.org', code: 'kg' });
        const { runtime } = setup([{
            linkId: 'w', type: 'quantity', text: 'Weight', extension: [
                { url: SDC + 'sdc-questionnaire-minQuantity', valueQuantity: kg(1) },
                { url: SDC + 'sdc-questionnaire-maxQuantity', valueQuantity: kg(300) },
            ],
        }]);
        runtime.setAnswers('w', [{ valueKey: 'valueQuantity', value: kg(400) }]);
        expect(runtime.validate().issues.map((issue) => issue.message)).toEqual(['Weight must be at most 300 kg']);
        runtime.setAnswers('w', [{ valueKey: 'valueQuantity', value: { value: 400, unit: 'lb', code: '[lb_av]' } }]);
        expect(runtime.validate().valid).toBe(true);
    });
});
