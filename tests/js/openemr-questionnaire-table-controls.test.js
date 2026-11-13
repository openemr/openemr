/**
 * @jest-environment jsdom
 */

/**
 * The table item controls: table (questions as rows, shared answers as
 * columns) and gtable (a repeating group with a row per repetition), with
 * enableWhen, validation and accessible names working as for any question.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-table-controls.test.js
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
const ORDINAL = 'http://hl7.org/fhir/StructureDefinition/ordinalValue';
const frequency = ['Not at all', 'Several days', 'More than half the days', 'Nearly every day']
    .map((display, points) => ({ valueCoding: { system: 'http://loinc.org', code: 'LA' + points, display }, extension: [{ url: ORDINAL, valueDecimal: points }] }));

function setup(items) {
    const container = document.createElement('div');
    document.body.appendChild(container);
    const runtime = runtimeApi.mount({ questionnaire: { resourceType: 'Questionnaire', status: 'active', item: items }, container });
    return { container, runtime };
}

const pick = (input) => {
    input.checked = true;
    input.dispatchEvent(new Event('change', { bubbles: true }));
};

beforeEach(() => {
    document.body.innerHTML = '';
});

describe('table', () => {
    const matrix = {
        linkId: 'phq', type: 'group', text: 'Over the last 2 weeks...', extension: [control('table')], item: [
            { linkId: 'q1', prefix: '1.', type: 'choice', text: 'Little interest or pleasure in doing things', required: true, answerOption: frequency },
            { linkId: 'q2', prefix: '2.', type: 'choice', text: 'Feeling down, depressed, or hopeless', answerOption: frequency },
        ],
    };

    test('questions are rows and the shared answers columns', () => {
        const { container } = setup([matrix]);
        const table = container.querySelector('table.oe-questionnaire-matrix');
        expect([...table.querySelectorAll('thead th')].map((th) => th.textContent)).toEqual(frequency.map((option) => option.valueCoding.display));
        expect([...table.querySelectorAll('tbody tr')].map((row) => row.dataset.linkId)).toEqual(['q1', 'q2']);
        expect(table.querySelectorAll('tbody input[type="radio"]')).toHaveLength(8);
    });

    test('each answer is named by its question and its column', () => {
        const { container } = setup([matrix]);
        const radio = container.querySelector('tr[data-link-id="q1"] input[value="2"]');
        const names = radio.getAttribute('aria-labelledby').split(' ').map((id) => document.getElementById(id).textContent);
        expect(names).toEqual(['1. — Little interest or pleasure in doing things*', 'More than half the days']);
    });

    test('choosing answers stores them, scores add up, and a missing required row is reported at its row', () => {
        const { container, runtime } = setup([matrix, {
            linkId: 'total', type: 'decimal', text: 'Total', readOnly: true,
            extension: [{ url: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-calculatedExpression', valueExpression: { language: 'text/fhirpath', expression: '%resource.repeat(item).answer.value.weight().sum()' } }],
        }]);
        expect(runtime.validate().issues.map((issue) => issue.linkId)).toEqual(['q1']);
        const row = container.querySelector('tr[data-link-id="q1"]');
        expect(row.querySelector('th .oe-questionnaire-error').textContent).toContain('requires a value');
        expect(row.querySelector('input').getAttribute('aria-invalid')).toBe('true');
        pick(container.querySelector('tr[data-link-id="q1"] input[value="2"]'));
        pick(container.querySelector('tr[data-link-id="q2"] input[value="3"]'));
        const response = runtime.getQuestionnaireResponse();
        expect(response.item.find((item) => item.linkId === 'total').answer[0].valueDecimal).toBe(5);
        expect(runtime.validate().valid).toBe(true);
    });

    test('enableWhen hides a row', () => {
        const { container, runtime } = setup([
            { linkId: 'screen', type: 'boolean', text: 'Any symptoms?' },
            { ...matrix, item: [matrix.item[0], { ...matrix.item[1], enableWhen: [{ question: 'screen', operator: '=', answerBoolean: true }] }] },
        ]);
        expect(container.querySelector('tr[data-link-id="q2"]').classList.contains('d-none')).toBe(true);
        runtime.setAnswers('screen', [{ valueKey: 'valueBoolean', value: true }]);
        expect(container.querySelector('tr[data-link-id="q2"]').classList.contains('d-none')).toBe(false);
    });

    test('a group with anything but choice questions renders the usual way', () => {
        const { container } = setup([{ ...matrix, item: [...matrix.item, { linkId: 'note', type: 'string', text: 'Note' }] }]);
        expect(container.querySelector('table.oe-questionnaire-matrix')).toBeNull();
        expect(container.querySelectorAll('[data-link-id="phq"] input[type="radio"]')).toHaveLength(8);
    });
});

describe('gtable', () => {
    const medications = {
        linkId: 'meds', type: 'group', repeats: true, text: 'Medicines', extension: [control('gtable')], item: [
            { linkId: 'name', type: 'string', text: 'Medicine', required: true },
            { linkId: 'dose', type: 'string', text: 'Dose' },
        ],
    };

    test('a row per repetition, a column per question, each entry named by its column', () => {
        const { container } = setup([medications]);
        const table = container.querySelector('table.oe-questionnaire-grid');
        expect([...table.querySelectorAll('thead th')].map((th) => th.textContent)).toEqual(['Medicine*', 'Dose']);
        expect(table.querySelectorAll('tbody tr')).toHaveLength(1);
        const input = table.querySelector('tbody td[data-link-id="name"] input');
        expect(document.getElementById(input.getAttribute('aria-labelledby')).textContent).toBe('Medicine*');
        expect(input.getAttribute('aria-required')).toBe('true');
    });

    test('Add adds a row, each row keeps its own answers, and the response has a repetition per row', () => {
        const { container, runtime } = setup([medications]);
        container.querySelector('[data-link-id="meds"] .oe-questionnaire-repeat-add').click();
        const inputs = [...container.querySelectorAll('td[data-link-id="name"] input')];
        expect(inputs).toHaveLength(2);
        inputs[0].value = 'Metformin';
        inputs[0].dispatchEvent(new Event('change', { bubbles: true }));
        const second = container.querySelectorAll('td[data-link-id="name"] input')[1];
        second.value = 'Lisinopril';
        second.dispatchEvent(new Event('change', { bubbles: true }));
        const groups = runtime.getQuestionnaireResponse().item.filter((item) => item.linkId === 'meds');
        expect(groups.map((group) => group.item.find((child) => child.linkId === 'name').answer[0].valueString)).toEqual(['Metformin', 'Lisinopril']);
        expect(container.querySelectorAll('.oe-questionnaire-grid .oe-questionnaire-repeat-remove')).toHaveLength(2);
    });

    test('a required cell left empty is reported in its cell', () => {
        const { container, runtime } = setup([medications]);
        expect(runtime.validate().valid).toBe(false);
        const cell = container.querySelector('td[data-link-id="name"]');
        expect(cell.querySelector('.oe-questionnaire-error').textContent).toBe('Medicine requires a value');
    });
});

test('an item control the runtime lacks gets a console note, and the item still renders', () => {
    const { container, runtime } = setup([{ linkId: 'g', type: 'group', text: 'G', extension: [control('htable')], item: [{ linkId: 'x', type: 'string', text: 'X' }] }]);
    expect(runtime.collectWarnings().filter((warning) => warning.code === 'item-control-unsupported')).toEqual([
        expect.objectContaining({ linkId: 'g', severity: 'info' }),
    ]);
    expect(container.querySelector('[data-link-id="x"] input')).not.toBeNull();
});

describe('modular forms and column hints', () => {
    const subQuestionnaire = (canonical) => ({ url: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-subQuestionnaire', valueCanonical: canonical });

    test('a subQuestionnaire display item is replaced by the supplied form\'s items, with its contained resources', () => {
        const phq2 = {
            resourceType: 'Questionnaire', url: 'http://example.org/Questionnaire/phq-2', status: 'active',
            contained: [{ resourceType: 'ValueSet', id: 'freq', status: 'active', expansion: { timestamp: '2026-10-23', contains: [{ code: '0', display: 'Not at all' }, { code: '1', display: 'Several days' }] } }],
            item: [{ linkId: 'p1', type: 'choice', text: 'Little interest', answerValueSet: '#freq' }],
        };
        const container = document.createElement('div');
        document.body.appendChild(container);
        const runtime = runtimeApi.mount({
            container,
            questionnaire: { resourceType: 'Questionnaire', status: 'active', item: [
                { linkId: 'intro', type: 'display', text: 'Yearly screen' },
                { linkId: 'phq2-here', type: 'display', text: 'PHQ-2', extension: [subQuestionnaire('http://example.org/Questionnaire/phq-2|1')] },
                { linkId: 'missing', type: 'display', text: 'GAD-2', extension: [subQuestionnaire('http://example.org/Questionnaire/gad-2')] },
            ] },
            options: { subQuestionnaires: { 'http://example.org/Questionnaire/phq-2': phq2 } },
        });
        expect(container.querySelector('[data-link-id="phq2-here"]')).toBeNull();
        expect(container.querySelectorAll('[data-link-id="p1"] input[type="radio"]')).toHaveLength(2);
        expect(container.querySelector('[data-link-id="missing"]')).not.toBeNull();
        expect(runtime.collectWarnings().filter((warning) => warning.code === 'sub-questionnaire-missing').map((warning) => warning.linkId)).toEqual(['missing']);
    });

    test('gtable columns use shortText and width; keyboard sets inputmode', () => {
        const SDC = 'http://hl7.org/fhir/uv/sdc/StructureDefinition/';
        const { container } = setup([{
            linkId: 'contacts', type: 'group', repeats: true, text: 'Contacts', extension: [control('gtable')], item: [
                { linkId: 'phone', type: 'string', text: 'Telephone number, with area code', extension: [
                    { url: SDC + 'sdc-questionnaire-shortText', valueString: 'Phone' },
                    { url: SDC + 'sdc-questionnaire-width', valueQuantity: { value: 40, unit: '%', system: 'http://unitsofmeasure.org', code: '%' } },
                    { url: SDC + 'sdc-questionnaire-keyboard', valueCoding: { system: 'http://hl7.org/fhir/uv/sdc/CodeSystem/keyboardType', code: 'phone' } },
                ] },
            ],
        }]);
        const heading = container.querySelector('.oe-questionnaire-grid thead th');
        expect(heading.textContent).toBe('Phone');
        expect(heading.style.width).toBe('40%');
        expect(container.querySelector('td[data-link-id="phone"] input').inputMode).toBe('tel');
    });
});
