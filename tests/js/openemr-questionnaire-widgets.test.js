/**
 * @jest-environment jsdom
 */

/**
 * Widget rendering and answer round-trip tests for the OpenEMR FHIR
 * Questionnaire Runtime.
 *
 * One battery per item type: choice in its radio, checkbox, and drop-down
 * variants (control extension and heuristic driven), open-choice with a
 * custom answer, boolean, integer/decimal, date/time, string/text/url,
 * quantity, readOnly propagation, unsupported-type fallback, and answer
 * restoration from an existing QuestionnaireResponse.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-widgets.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

function loadRuntime() {
    const source = fs.readFileSync(
        path.resolve(
            __dirname,
            '../../interface/forms/questionnaire_assessments/native/openemr_questionnaire.js'
        ),
        'utf8'
    );

    new Function(
        'window',
        'document',
        'HTMLElement',
        'HTMLInputElement',
        'CustomEvent',
        source
    )(
        global.window,
        global.document,
        global.window.HTMLElement,
        global.window.HTMLInputElement,
        global.window.CustomEvent
    );
}

loadRuntime();

const runtimeApi = global.window.OpenEMRQuestionnaire;

function createContainer() {
    const container = document.createElement('div');
    document.body.appendChild(container);
    return container;
}

function mountSingle(item, questionnaireResponse) {
    const container = createContainer();
    const runtime = runtimeApi.mount({
        questionnaire: {
            resourceType: 'Questionnaire',
            status: 'active',
            item: [item],
        },
        questionnaireResponse,
        container,
    });
    return { container, runtime };
}

function itemNode(container, linkId) {
    return container.querySelector('[data-link-id="' + linkId + '"]');
}

function answerOf(runtime, linkId) {
    // the runtime omits the item array entirely when no items carry answers
    const items = runtime.getQuestionnaireResponse().item ?? [];
    const item = items.find((entry) => entry.linkId === linkId);
    return item ? item.answer : undefined;
}

function change(element) {
    element.dispatchEvent(new Event('change', { bubbles: true }));
}

function itemControl(code) {
    return [{
        url: runtimeApi.EXTENSIONS.ITEM_CONTROL,
        valueCodeableConcept: { coding: [{ code }] },
    }];
}

const CODED_OPTIONS = [
    { valueCoding: { code: 'a', system: 'http://example.org/cs', display: 'Alpha' } },
    { valueCoding: { code: 'b', system: 'http://example.org/cs', display: 'Beta' } },
    { valueCoding: { code: 'c', system: 'http://example.org/cs', display: 'Gamma' } },
];

beforeEach(() => {
    document.body.innerHTML = '';
    runtimeApi.runtime = null;
});

describe('choice widgets', () => {
    test('few options render as radios and selection stores the coding', () => {
        const { container, runtime } = mountSingle({
            linkId: 'reason', type: 'choice', text: 'Reason', answerOption: CODED_OPTIONS,
        });
        const node = itemNode(container, 'reason');
        const radios = node.querySelectorAll('input[type="radio"]');
        expect(radios).toHaveLength(3);
        expect(node.querySelector('select')).toBeNull();

        radios[1].checked = true;
        change(radios[1]);

        const answers = answerOf(runtime, 'reason');
        expect(answers).toHaveLength(1);
        expect(answers[0].valueCoding).toMatchObject({ code: 'b', system: 'http://example.org/cs' });
    });

    test('radio labels show the coding display text', () => {
        const { container } = mountSingle({
            linkId: 'reason', type: 'choice', text: 'Reason', answerOption: CODED_OPTIONS,
        });
        const labels = [...itemNode(container, 'reason').querySelectorAll('label.custom-control-label')]
            .map((label) => label.textContent);
        expect(labels).toEqual(['Alpha', 'Beta', 'Gamma']);
    });

    test('more than six options render as a drop-down', () => {
        const manyOptions = 'abcdefgh'.split('').map((code) => ({ valueCoding: { code, display: code.toUpperCase() } }));
        const { container, runtime } = mountSingle({
            linkId: 'many', type: 'choice', text: 'Many', answerOption: manyOptions,
        });
        const select = itemNode(container, 'many').querySelector('select');
        expect(select).not.toBeNull();
        // blank option + 8 real options
        expect(select.querySelectorAll('option')).toHaveLength(9);

        select.value = '2';
        change(select);
        expect(answerOf(runtime, 'many')[0].valueCoding.code).toBe('c');

        select.value = '';
        change(select);
        expect(answerOf(runtime, 'many')).toBeUndefined();
    });

    test('drop-down item control forces a select even for few options', () => {
        const { container } = mountSingle({
            linkId: 'forced', type: 'choice', text: 'Forced', answerOption: CODED_OPTIONS,
            extension: itemControl('drop-down'),
        });
        const node = itemNode(container, 'forced');
        expect(node.querySelector('select')).not.toBeNull();
        expect(node.querySelectorAll('input[type="radio"]')).toHaveLength(0);
    });

    test('repeats renders checkboxes and collects multiple answers', () => {
        const { container, runtime } = mountSingle({
            linkId: 'multi', type: 'choice', text: 'Multi', repeats: true, answerOption: CODED_OPTIONS,
        });
        const checkboxes = itemNode(container, 'multi').querySelectorAll('input[type="checkbox"]');
        expect(checkboxes).toHaveLength(3);

        checkboxes[0].checked = true;
        change(checkboxes[0]);
        checkboxes[2].checked = true;
        change(checkboxes[2]);
        expect(answerOf(runtime, 'multi').map((answer) => answer.valueCoding.code)).toEqual(['a', 'c']);

        checkboxes[0].checked = false;
        change(checkboxes[0]);
        expect(answerOf(runtime, 'multi').map((answer) => answer.valueCoding.code)).toEqual(['c']);
    });

    test('check-box item control renders checkboxes without repeats', () => {
        const { container } = mountSingle({
            linkId: 'cb', type: 'choice', text: 'CB', answerOption: CODED_OPTIONS,
            extension: itemControl('check-box'),
        });
        expect(itemNode(container, 'cb').querySelectorAll('input[type="checkbox"]')).toHaveLength(3);
    });

    test('valueString answer options work end to end', () => {
        const { container, runtime } = mountSingle({
            linkId: 'strings', type: 'choice', text: 'Strings',
            answerOption: [{ valueString: 'Yes' }, { valueString: 'No' }],
        });
        const radios = itemNode(container, 'strings').querySelectorAll('input[type="radio"]');
        radios[0].checked = true;
        change(radios[0]);
        expect(answerOf(runtime, 'strings')[0].valueString).toBe('Yes');
    });

    test('restores a selected radio from an existing QuestionnaireResponse', () => {
        const { container } = mountSingle(
            { linkId: 'reason', type: 'choice', text: 'Reason', answerOption: CODED_OPTIONS },
            {
                resourceType: 'QuestionnaireResponse',
                status: 'in-progress',
                item: [{
                    linkId: 'reason',
                    answer: [{ valueCoding: { code: 'b', system: 'http://example.org/cs', display: 'Beta' } }],
                }],
            }
        );
        const radios = itemNode(container, 'reason').querySelectorAll('input[type="radio"]');
        expect(radios[0].checked).toBe(false);
        expect(radios[1].checked).toBe(true);
    });

    test('restores multiple checked boxes from an existing QuestionnaireResponse', () => {
        const { container } = mountSingle(
            { linkId: 'multi', type: 'choice', text: 'Multi', repeats: true, answerOption: CODED_OPTIONS },
            {
                resourceType: 'QuestionnaireResponse',
                status: 'in-progress',
                item: [{
                    linkId: 'multi',
                    answer: [
                        { valueCoding: { code: 'a', system: 'http://example.org/cs' } },
                        { valueCoding: { code: 'c', system: 'http://example.org/cs' } },
                    ],
                }],
            }
        );
        const checkboxes = itemNode(container, 'multi').querySelectorAll('input[type="checkbox"]');
        expect([...checkboxes].map((box) => box.checked)).toEqual([true, false, true]);
    });
});

describe('open-choice widget', () => {
    test('renders an Other input whose text becomes a valueString answer', () => {
        const { container, runtime } = mountSingle({
            linkId: 'open', type: 'open-choice', text: 'Open', answerOption: CODED_OPTIONS,
        });
        const other = itemNode(container, 'open').querySelector('input[type="text"]');
        expect(other).not.toBeNull();
        expect(other.placeholder).toBe('Other');

        other.value = 'something else';
        change(other);
        const answers = answerOf(runtime, 'open');
        expect(answers.some((answer) => answer.valueString === 'something else')).toBe(true);
    });
});

describe('boolean widget', () => {
    test('renders Yes/No radios and No stores valueBoolean false', () => {
        const { container, runtime } = mountSingle({ linkId: 'flag', type: 'boolean', text: 'Flag' });
        const radios = itemNode(container, 'flag').querySelectorAll('input[type="radio"]');
        expect(radios).toHaveLength(2);

        radios[1].checked = true;
        change(radios[1]);
        expect(answerOf(runtime, 'flag')[0].valueBoolean).toBe(false);
    });
});

describe('numeric widgets', () => {
    test('integer renders a step-1 number input and stores valueInteger', () => {
        const { container, runtime } = mountSingle({ linkId: 'count', type: 'integer', text: 'Count' });
        const input = itemNode(container, 'count').querySelector('input[type="number"]');
        expect(input.step).toBe('1');

        input.value = '42';
        change(input);
        expect(answerOf(runtime, 'count')[0].valueInteger).toBe(42);

        input.value = '';
        change(input);
        expect(answerOf(runtime, 'count')).toBeUndefined();
    });

    test('decimal renders a step-any number input and stores valueDecimal', () => {
        const { container, runtime } = mountSingle({ linkId: 'temp', type: 'decimal', text: 'Temp' });
        const input = itemNode(container, 'temp').querySelector('input[type="number"]');
        expect(input.step).toBe('any');

        input.value = '37.6';
        change(input);
        expect(answerOf(runtime, 'temp')[0].valueDecimal).toBeCloseTo(37.6);
    });
});

describe('temporal widgets', () => {
    test('date renders a date input and stores valueDate as entered', () => {
        const { container, runtime } = mountSingle({ linkId: 'dob', type: 'date', text: 'DOB' });
        const input = itemNode(container, 'dob').querySelector('input[type="date"]');
        input.value = '2026-07-23';
        change(input);
        expect(answerOf(runtime, 'dob')[0].valueDate).toBe('2026-07-23');
    });

    test('time renders a time input and stores valueTime as entered', () => {
        const { container, runtime } = mountSingle({ linkId: 'at', type: 'time', text: 'At' });
        const input = itemNode(container, 'at').querySelector('input[type="time"]');
        input.value = '13:45';
        change(input);
        expect(answerOf(runtime, 'at')[0].valueTime).toBe('13:45');
    });
});

describe('text family widgets', () => {
    test('string renders a text input storing valueString', () => {
        const { container, runtime } = mountSingle({ linkId: 's', type: 'string', text: 'S' });
        const input = itemNode(container, 's').querySelector('input[type="text"]');
        input.value = 'hello';
        change(input);
        expect(answerOf(runtime, 's')[0].valueString).toBe('hello');
    });

    test('text renders a textarea storing valueString and restores it', () => {
        const { container, runtime } = mountSingle(
            { linkId: 'notes', type: 'text', text: 'Notes' },
            {
                resourceType: 'QuestionnaireResponse',
                status: 'in-progress',
                item: [{ linkId: 'notes', answer: [{ valueString: 'existing note' }] }],
            }
        );
        const textarea = itemNode(container, 'notes').querySelector('textarea');
        expect(textarea).not.toBeNull();
        expect(textarea.rows).toBe(3);
        expect(textarea.value).toBe('existing note');

        textarea.value = 'updated note';
        change(textarea);
        expect(answerOf(runtime, 'notes')[0].valueString).toBe('updated note');
    });

    test('url renders a url input storing valueUri', () => {
        const { container, runtime } = mountSingle({ linkId: 'site', type: 'url', text: 'Site' });
        const input = itemNode(container, 'site').querySelector('input[type="url"]');
        input.value = 'https://example.org';
        change(input);
        expect(answerOf(runtime, 'site')[0].valueUri).toBe('https://example.org');
    });
});

describe('quantity widget', () => {
    test('value and unit inputs build a valueQuantity with unit and code', () => {
        const { container, runtime } = mountSingle({ linkId: 'dose', type: 'quantity', text: 'Dose' });
        const node = itemNode(container, 'dose');
        const valueInput = node.querySelector('input[type="number"]');
        const unitInput = node.querySelector('input[type="text"]');

        valueInput.value = '2.5';
        change(valueInput);
        unitInput.value = 'mg';
        change(unitInput);

        expect(answerOf(runtime, 'dose')[0].valueQuantity).toEqual({ value: 2.5, unit: 'mg', code: 'mg' });
    });

    test('empty unit stores a bare value; clearing the value clears the answer', () => {
        const { container, runtime } = mountSingle({ linkId: 'dose', type: 'quantity', text: 'Dose' });
        const node = itemNode(container, 'dose');
        const valueInput = node.querySelector('input[type="number"]');

        valueInput.value = '3';
        change(valueInput);
        expect(answerOf(runtime, 'dose')[0].valueQuantity).toEqual({ value: 3 });

        valueInput.value = '';
        change(valueInput);
        expect(answerOf(runtime, 'dose')).toBeUndefined();
    });

    test('restores value and unit from an existing QuestionnaireResponse', () => {
        const { container } = mountSingle(
            { linkId: 'dose', type: 'quantity', text: 'Dose' },
            {
                resourceType: 'QuestionnaireResponse',
                status: 'in-progress',
                item: [{ linkId: 'dose', answer: [{ valueQuantity: { value: 5, unit: 'mL', code: 'mL' } }] }],
            }
        );
        const node = itemNode(container, 'dose');
        expect(node.querySelector('input[type="number"]').value).toBe('5');
        expect(node.querySelector('input[type="text"]').value).toBe('mL');
    });
});

describe('readOnly propagation', () => {
    test('readOnly text input cannot be edited', () => {
        const { container } = mountSingle({ linkId: 's', type: 'string', text: 'S', readOnly: true });
        expect(itemNode(container, 's').querySelector('input[type="text"]').readOnly).toBe(true);
    });

    test('readOnly choice radios are disabled', () => {
        const { container } = mountSingle({
            linkId: 'reason', type: 'choice', text: 'Reason', readOnly: true, answerOption: CODED_OPTIONS,
        });
        const radios = itemNode(container, 'reason').querySelectorAll('input[type="radio"]');
        expect([...radios].every((radio) => radio.disabled)).toBe(true);
    });
});

describe('unsupported item types', () => {
    test('renders a visible warning naming the type instead of crashing', () => {
        const { container } = mountSingle({ linkId: 'file', type: 'attachment', text: 'Upload' });
        const warning = itemNode(container, 'file').querySelector('.alert.alert-warning');
        expect(warning).not.toBeNull();
        expect(warning.textContent).toContain('attachment');
    });
});
