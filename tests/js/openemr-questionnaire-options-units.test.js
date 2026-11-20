/**
 * @jest-environment jsdom
 */

/**
 * answerOption on non-choice item types (R4 allows integer/date/time/string/
 * Coding; LForms exports drop-downs as string + answerOption), the
 * questionnaire-optionPrefix and questionnaire-answerConstraint extensions,
 * and questionnaire-unitOption / sdc-questionnaire-unitOpen on quantity items.
 * Item shapes are taken from the LOINC MDS v2.0 full assessment form (issue #14736).
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-options-units.test.js
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

const CONTROL = 'http://hl7.org/fhir/StructureDefinition/questionnaire-itemControl';
const PREFIX = 'http://hl7.org/fhir/StructureDefinition/questionnaire-optionPrefix';
const UNIT_OPTION = 'http://hl7.org/fhir/StructureDefinition/questionnaire-unitOption';
const UNIT_OPEN = 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-unitOpen';
const ANSWER_CONSTRAINT = 'http://hl7.org/fhir/StructureDefinition/questionnaire-answerConstraint';

const control = (code) => ({
    url: CONTROL,
    valueCodeableConcept: { coding: [{ system: 'http://hl7.org/fhir/questionnaire-item-control', code }] },
});
const option = (value, prefix) => ({ valueString: value, extension: [{ url: PREFIX, valueString: prefix }] });

// "Marital status" as exported by LForms in the MDS v2.0 form.
const maritalStatus = () => ({
    linkId: '/45982-6/45984-2/45410-8',
    type: 'string',
    text: 'Marital status',
    extension: [control('drop-down')],
    answerOption: [
        option('Never married', '1'),
        option('Married', '2'),
        option('Widowed', '3'),
        option('Separated', '4'),
        option('Divorced', '5'),
    ],
});

// "Body height Measured" from the MDS v2.0 form.
const bodyHeight = (extra = []) => ({
    linkId: '/46037-8/46039-4/3137-7',
    type: 'quantity',
    text: 'Body height Measured',
    extension: [
        { url: UNIT_OPTION, valueCoding: { display: '[in_us]' } },
        { url: UNIT_OPTION, valueCoding: { system: 'http://unitsofmeasure.org', code: 'cm', display: 'cm' } },
        ...extra,
    ],
});

function mount(items, questionnaireResponse = null) {
    const container = document.createElement('div');
    document.body.appendChild(container);
    const runtime = runtimeApi.mount({
        questionnaire: { resourceType: 'Questionnaire', status: 'active', item: items },
        questionnaireResponse,
        container,
    });
    return { container, runtime };
}

function answersOf(runtime, linkId) {
    const find = (items) => {
        for (const entry of items ?? []) {
            if (entry.linkId === linkId) {
                return entry.answer ?? [];
            }
            const nested = find(entry.item);
            if (nested) {
                return nested;
            }
        }
        return null;
    };
    return find(runtime.getQuestionnaireResponse().item) ?? [];
}

function choose(select, label) {
    const target = [...select.options].find((entry) => entry.textContent === label);
    select.value = target.value;
    select.dispatchEvent(new Event('change', { bubbles: true }));
}

beforeEach(() => {
    document.body.innerHTML = '';
});

describe('answerOption on non-choice item types', () => {
    test('a string drop-down renders a select with prefixed labels and stores valueString', () => {
        const { container, runtime } = mount([maritalStatus()]);
        const select = container.querySelector('select.oe-questionnaire-choice-select');
        expect(select).not.toBeNull();
        expect(container.querySelector('input[type="text"]')).toBeNull();
        expect([...select.options].map((entry) => entry.textContent)).toEqual([
            '', '1 Never married', '2 Married', '3 Widowed', '4 Separated', '5 Divorced',
        ]);
        choose(select, '2 Married');
        expect(answersOf(runtime, '/45982-6/45984-2/45410-8')).toEqual([{ valueString: 'Married' }]);
    });

    test('a saved string answer is preselected on load', () => {
        const { container } = mount([maritalStatus()], {
            resourceType: 'QuestionnaireResponse',
            status: 'in-progress',
            item: [{ linkId: '/45982-6/45984-2/45410-8', answer: [{ valueString: 'Widowed' }] }],
        });
        const select = container.querySelector('select');
        expect(select.options[select.selectedIndex].textContent).toBe('3 Widowed');
    });

    test('without an itemControl, a short string option list renders radios', () => {
        const { container, runtime } = mount([{
            linkId: 's', type: 'string', text: 'Pick',
            answerOption: [{ valueString: 'A' }, { valueString: 'B' }],
        }]);
        const radios = container.querySelectorAll('input[type="radio"]');
        expect(radios).toHaveLength(2);
        radios[1].checked = true;
        radios[1].dispatchEvent(new Event('change', { bubbles: true }));
        expect(answersOf(runtime, 's')).toEqual([{ valueString: 'B' }]);
    });

    test('repeating string options render checkboxes', () => {
        const { container, runtime } = mount([{
            linkId: 'm', type: 'string', text: 'Many', repeats: true,
            answerOption: [{ valueString: 'X' }, { valueString: 'Y' }, { valueString: 'Z' }],
        }]);
        const boxes = container.querySelectorAll('input[type="checkbox"]');
        expect(boxes).toHaveLength(3);
        for (const index of [0, 2]) {
            boxes[index].checked = true;
            boxes[index].dispatchEvent(new Event('change', { bubbles: true }));
        }
        expect(answersOf(runtime, 'm')).toEqual([{ valueString: 'X' }, { valueString: 'Z' }]);
    });

    test('integer options store valueInteger', () => {
        const { container, runtime } = mount([{
            linkId: 'n', type: 'integer', text: 'Score', extension: [control('drop-down')],
            answerOption: [{ valueInteger: 0 }, { valueInteger: 1 }, { valueInteger: 2 }],
        }]);
        choose(container.querySelector('select'), '1');
        expect(answersOf(runtime, 'n')).toEqual([{ valueInteger: 1 }]);
    });

    test('options without value[x] are skipped: the item falls back to its plain input', () => {
        // MDS "Frequency of verbally abusive behavioral symptoms": prefixes only.
        const { container } = mount([{
            linkId: '/45996-6/46001-4/45560-0', type: 'decimal', text: 'Frequency of verbally abusive behavioral symptoms',
            answerOption: ['0', '1', '2', '3'].map((prefix) => ({ extension: [{ url: PREFIX, valueString: prefix }] })),
        }]);
        expect(container.querySelector('select')).toBeNull();
        expect(container.querySelector('input[type="number"]')).not.toBeNull();
        // advisory only: no "could not be fully rendered" banner
        expect(container.querySelector('.oe-questionnaire-warnings').classList.contains('d-none')).toBe(true);
    });

    test('an empty answerOption list keeps the free-text input', () => {
        const { container } = mount([{ linkId: 'icd', type: 'string', text: 'ICD9', answerOption: [] }]);
        expect(container.querySelector('input[type="text"]')).not.toBeNull();
    });
});

describe('questionnaire-optionPrefix and answerConstraint', () => {
    test('a prefix is shown as written, with no period added', () => {
        const { container } = mount([{
            linkId: 'p', type: 'string', text: 'P', extension: [control('drop-down')],
            answerOption: [option('Yes', 'a)'), option('No', 'b.'), option('Maybe', 'Prefix ='), option('Never', '[Prefix]')],
        }]);
        expect([...container.querySelector('select').options].map((entry) => entry.textContent)).toEqual(['', 'a) Yes', 'b. No', 'Prefix = Maybe', '[Prefix] Never']);
    });

    test('choice items show prefixes on radios too', () => {
        const { container } = mount([{
            linkId: 'c', type: 'choice', text: 'C',
            answerOption: [{ valueCoding: { code: 'y', display: 'Yes' }, extension: [{ url: PREFIX, valueString: 'A' }] }],
        }]);
        expect(container.querySelector('.oe-questionnaire-choice-label').textContent).toBe('A Yes');
    });

    test('optionsOrString adds a free-text box that holds only a non-option answer', () => {
        const item = { ...maritalStatus(), extension: [control('drop-down'), { url: ANSWER_CONSTRAINT, valueCode: 'optionsOrString' }] };
        const { container, runtime } = mount([item]);
        const other = container.querySelector('input[type="text"]');
        expect(other).not.toBeNull();
        choose(container.querySelector('select'), '2 Married');
        // the selected option is not echoed into the free-text box on re-render
        const { container: reloaded } = mount([item], runtime.getQuestionnaireResponse());
        expect(reloaded.querySelector('input[type="text"]').value).toBe('');

        other.value = 'Domestic partnership';
        other.dispatchEvent(new Event('change', { bubbles: true }));
        expect(answersOf(runtime, item.linkId)).toEqual([{ valueString: 'Domestic partnership' }]);
    });

    test('the default (optionsOnly) has no free-text box', () => {
        const { container } = mount([maritalStatus()]);
        expect(container.querySelector('input[type="text"]')).toBeNull();
    });
});

describe('questionnaire-unitOption on quantity', () => {
    test('unit options render a select, preselect the first, and save the chosen coding', () => {
        const { container, runtime } = mount([bodyHeight()]);
        const unit = container.querySelector('select.oe-questionnaire-unit-select');
        expect([...unit.options].map((entry) => entry.textContent)).toEqual(['', '[in_us]', 'cm']);
        expect(unit.options[unit.selectedIndex].textContent).toBe('[in_us]');

        const value = container.querySelector('input[type="number"]');
        value.value = '70';
        value.dispatchEvent(new Event('change', { bubbles: true }));
        expect(answersOf(runtime, '/46037-8/46039-4/3137-7')).toEqual([{ valueQuantity: { value: 70, unit: '[in_us]' } }]);

        choose(unit, 'cm');
        expect(answersOf(runtime, '/46037-8/46039-4/3137-7')).toEqual([
            { valueQuantity: { value: 70, unit: 'cm', system: 'http://unitsofmeasure.org', code: 'cm' } },
        ]);
    });

    test('a saved unit is preselected on load', () => {
        const { container } = mount([bodyHeight()], {
            resourceType: 'QuestionnaireResponse',
            status: 'in-progress',
            item: [{ linkId: '/46037-8/46039-4/3137-7', answer: [{ valueQuantity: { value: 178, unit: 'cm', code: 'cm' } }] }],
        });
        const unit = container.querySelector('select.oe-questionnaire-unit-select');
        expect(unit.options[unit.selectedIndex].textContent).toBe('cm');
        expect(container.querySelector('input[type="number"]').value).toBe('178');
    });

    test('unitOpen optionsOrString allows free text with the options as suggestions', () => {
        const { container, runtime } = mount([bodyHeight([{ url: UNIT_OPEN, valueCode: 'optionsOrString' }])]);
        expect(container.querySelector('select.oe-questionnaire-unit-select')).toBeNull();
        const unit = container.querySelector('input[aria-label="Unit"]');
        const list = container.querySelector('datalist#' + unit.getAttribute('list'));
        expect([...list.options].map((entry) => entry.value)).toEqual(['[in_us]', 'cm']);

        const value = container.querySelector('input[type="number"]');
        value.value = '2';
        value.dispatchEvent(new Event('change', { bubbles: true }));
        unit.value = 'm';
        unit.dispatchEvent(new Event('change', { bubbles: true }));
        expect(answersOf(runtime, '/46037-8/46039-4/3137-7')).toEqual([{ valueQuantity: { value: 2, unit: 'm', code: 'm' } }]);
    });

    test('quantity without unit options keeps the free-text unit input', () => {
        const { container } = mount([{ linkId: 'qty', type: 'quantity', text: 'Q' }]);
        expect(container.querySelector('select')).toBeNull();
        expect(container.querySelector('input[placeholder="Unit"]')).not.toBeNull();
    });
});
