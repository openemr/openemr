/**
 * @jest-environment jsdom
 */

/**
 * Broader SDC / R4 Questionnaire capability coverage added alongside #14736:
 * hidden, initialExpression, enableWhenExpression, questionnaire-constraint,
 * minValue/maxValue, maxDecimalPlaces, minOccurs/maxOccurs, choiceOrientation,
 * openLabel, displayCategory, the reference and attachment item types, and
 * repeating scalar items.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-spec-coverage.test.js
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
    new Function('window', 'document', 'HTMLElement', 'HTMLInputElement', 'CustomEvent', source)(
        global.window,
        global.document,
        global.window.HTMLElement,
        global.window.HTMLInputElement,
        global.window.CustomEvent
    );
}

loadRuntime();

const runtimeApi = global.window.OpenEMRQuestionnaire;
const X = runtimeApi.EXTENSIONS;

function createContainer() {
    const container = document.createElement('div');
    document.body.appendChild(container);
    return container;
}

function mount(items, extra = {}) {
    const container = createContainer();
    const runtime = runtimeApi.mount({
        questionnaire: { resourceType: 'Questionnaire', status: 'active', item: items, ...extra },
        container,
    });
    return { container, runtime };
}

function itemNode(container, linkId) {
    return container.querySelector('[data-link-id="' + linkId + '"]');
}

function fhirpath(expression) {
    return { valueExpression: { language: 'text/fhirpath', expression } };
}

function setValue(input, value) {
    input.value = value;
    input.dispatchEvent(new Event('change', { bubbles: true }));
}

beforeEach(() => {
    document.body.innerHTML = '';
    runtimeApi.runtime = null;
});

describe('hidden extension', () => {
    test('hidden item is rendered but visually hidden and not required-validated', () => {
        const { container, runtime } = mount([
            {
                linkId: 'h', type: 'string', text: 'Hidden', required: true,
                extension: [{ url: X.HIDDEN, valueBoolean: true }],
            },
        ]);
        expect(itemNode(container, 'h').classList.contains('d-none')).toBe(true);
        // required but hidden -> no validation error
        expect(runtime.validate().valid).toBe(true);
    });

    test('a hidden item with a value still contributes to the response', () => {
        const { runtime } = mount([
            {
                linkId: 'h', type: 'integer', text: 'Hidden',
                initial: [{ valueInteger: 42 }],
                extension: [{ url: X.HIDDEN, valueBoolean: true }],
            },
        ]);
        const item = runtime.getQuestionnaireResponse().item.find((entry) => entry.linkId === 'h');
        expect(item.answer[0].valueInteger).toBe(42);
    });
});

describe('initialExpression', () => {
    test('seeds a value when the item has no answer', () => {
        const { runtime } = mount([
            { linkId: 's', type: 'string', text: 'Greeting', extension: [{ url: X.INITIAL_EXPRESSION, ...fhirpath("'hello'") }] },
        ]);
        const item = runtime.getQuestionnaireResponse().item.find((entry) => entry.linkId === 's');
        expect(item.answer[0].valueString).toBe('hello');
    });

    test('does not override an answer supplied by an existing response', () => {
        const container = createContainer();
        const runtime = runtimeApi.mount({
            questionnaire: {
                resourceType: 'Questionnaire', status: 'active',
                item: [{ linkId: 's', type: 'string', text: 'G', extension: [{ url: X.INITIAL_EXPRESSION, ...fhirpath("'seed'") }] }],
            },
            questionnaireResponse: {
                resourceType: 'QuestionnaireResponse', status: 'in-progress',
                item: [{ linkId: 's', answer: [{ valueString: 'existing' }] }],
            },
            container,
        });
        const item = runtime.getQuestionnaireResponse().item.find((entry) => entry.linkId === 's');
        expect(item.answer[0].valueString).toBe('existing');
    });
});

describe('enableWhenExpression', () => {
    test('enables a dependent item via a FHIRPath expression', () => {
        const { runtime } = mount([
            { linkId: 'a', type: 'string', text: 'A' },
            {
                linkId: 'b', type: 'string', text: 'B',
                extension: [{ url: X.ENABLE_WHEN_EXPRESSION, ...fhirpath("%resource.item.where(linkId='a').answer.exists()") }],
            },
        ]);
        expect(runtime.isEnabled('b')).toBe(false);
        runtime.setAnswers('a', [{ valueKey: 'valueString', value: 'x' }]);
        expect(runtime.isEnabled('b')).toBe(true);
    });
});

describe('questionnaire-constraint', () => {
    function constraint(key, human, expression) {
        return {
            url: X.CONSTRAINT,
            extension: [
                { url: 'key', valueString: key },
                { url: 'severity', valueCode: 'error' },
                { url: 'human', valueString: human },
                { url: 'expression', valueString: expression },
            ],
        };
    }

    test('a failing constraint produces a validation error with its human message', () => {
        const { runtime } = mount([
            { linkId: 'a', type: 'integer', text: 'A' },
            {
                linkId: 'b', type: 'integer', text: 'B',
                extension: [constraint('a-le-b', 'A must be <= B',
                    "%resource.item.where(linkId='a').answer.valueInteger <= %resource.item.where(linkId='b').answer.valueInteger")],
            },
        ]);
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: 5 }]);
        runtime.setAnswers('b', [{ valueKey: 'valueInteger', value: 3 }]);
        const result = runtime.validate();
        expect(result.valid).toBe(false);
        expect(result.issues.some((issue) => issue.code === 'constraint' && issue.message === 'A must be <= B')).toBe(true);

        runtime.setAnswers('b', [{ valueKey: 'valueInteger', value: 9 }]);
        expect(runtime.validate().valid).toBe(true);
    });
    test('a warning-severity constraint does not invalidate the form', () => {
        const warnConstraint = {
            url: X.CONSTRAINT,
            extension: [
                { url: 'key', valueString: 'soft' },
                { url: 'severity', valueCode: 'warning' },
                { url: 'human', valueString: 'Soft advisory' },
                { url: 'expression', valueString: "%resource.item.where(linkId='v').answer.valueInteger < 10" },
            ],
        };
        const { runtime } = mount([
            { linkId: 'v', type: 'integer', text: 'V', extension: [warnConstraint] },
        ]);
        runtime.setAnswers('v', [{ valueKey: 'valueInteger', value: 50 }]);
        const result = runtime.validate();
        // the advisory fires as a warning but the form stays valid
        expect(result.issues.some((i) => i.code === 'constraint' && i.severity === 'warning')).toBe(true);
        expect(result.valid).toBe(true);
    });
});

describe('minValue / maxValue / maxDecimalPlaces', () => {
    test('minValue and maxValue bound an integer answer', () => {
        const { runtime } = mount([
            {
                linkId: 'n', type: 'integer', text: 'N',
                extension: [
                    { url: X.MIN_VALUE, valueInteger: 1 },
                    { url: X.MAX_VALUE, valueInteger: 10 },
                ],
            },
        ]);
        runtime.setAnswers('n', [{ valueKey: 'valueInteger', value: 0 }]);
        expect(runtime.validate().issues.some((i) => i.code === 'min-value')).toBe(true);
        runtime.setAnswers('n', [{ valueKey: 'valueInteger', value: 11 }]);
        expect(runtime.validate().issues.some((i) => i.code === 'max-value')).toBe(true);
        runtime.setAnswers('n', [{ valueKey: 'valueInteger', value: 5 }]);
        expect(runtime.validate().valid).toBe(true);
    });

    test('maxDecimalPlaces limits decimal precision', () => {
        const { runtime } = mount([
            { linkId: 'd', type: 'decimal', text: 'D', extension: [{ url: X.MAX_DECIMAL_PLACES, valueInteger: 2 }] },
        ]);
        runtime.setAnswers('d', [{ valueKey: 'valueDecimal', value: 1.234 }]);
        expect(runtime.validate().issues.some((i) => i.code === 'max-decimal-places')).toBe(true);
        runtime.setAnswers('d', [{ valueKey: 'valueDecimal', value: 1.23 }]);
        expect(runtime.validate().valid).toBe(true);
    });
});

describe('minOccurs / maxOccurs', () => {
    test('a repeating group shows minOccurs instances at mount and caps adds at maxOccurs', () => {
        const { container } = mount([
            {
                linkId: 'g', type: 'group', text: 'G', repeats: true,
                extension: [
                    { url: X.MIN_OCCURS, valueInteger: 2 },
                    { url: X.MAX_OCCURS, valueInteger: 2 },
                ],
                item: [{ linkId: 'c', type: 'string', text: 'C' }],
            },
        ]);
        // minOccurs=2 -> two instances rendered at mount
        const instances = container.querySelectorAll('[data-link-id="g"] .oe-questionnaire-group-instance');
        expect(instances).toHaveLength(2);
        // maxOccurs=2 already reached -> Add disabled
        expect(container.querySelector('[data-link-id="g"] .oe-questionnaire-repeat-add').disabled).toBe(true);
    });

    test('minOccurs on a non-repeating item requires that many answers', () => {
        const { runtime } = mount([
            { linkId: 'm', type: 'string', text: 'M', repeats: true, extension: [{ url: X.MIN_OCCURS, valueInteger: 2 }] },
        ]);
        runtime.setAnswers('m', [{ valueKey: 'valueString', value: 'one' }]);
        expect(runtime.validate().issues.some((i) => i.code === 'min-occurs')).toBe(true);
        runtime.setAnswers('m', [
            { valueKey: 'valueString', value: 'one' },
            { valueKey: 'valueString', value: 'two' },
        ]);
        expect(runtime.validate().valid).toBe(true);
    });
});

describe('choiceOrientation and openLabel', () => {
    const opts = [{ valueString: 'A' }, { valueString: 'B' }];

    test('horizontal orientation renders inline choice controls', () => {
        const { container } = mount([
            {
                linkId: 'c', type: 'choice', text: 'C', answerOption: opts,
                extension: [{ url: X.CHOICE_ORIENTATION, valueCode: 'horizontal' }],
            },
        ]);
        const row = itemNode(container, 'c').querySelector('.custom-control');
        expect(row.classList.contains('custom-control-inline')).toBe(true);
    });

    test('openLabel labels the open-choice free-text input', () => {
        const { container } = mount([
            {
                linkId: 'oc', type: 'open-choice', text: 'OC', answerOption: opts,
                extension: [{ url: X.OPEN_LABEL, valueString: 'Something else' }],
            },
        ]);
        expect(itemNode(container, 'oc').querySelector('.oe-questionnaire-open-label').textContent).toBe('Something else');
    });
});

describe('displayCategory', () => {
    test('instructions category uses the info style', () => {
        const { container } = mount([
            {
                linkId: 'd', type: 'display', text: 'Fill this out carefully',
                extension: [{ url: X.DISPLAY_CATEGORY, valueCodeableConcept: { coding: [{ code: 'instructions' }] } }],
            },
        ]);
        // displayCategory is commonly a CodeableConcept; the runtime reads its coding code.
        const alert = itemNode(container, 'd').querySelector('.alert');
        expect(alert.classList.contains('alert-info')).toBe(true);
    });

    test('instructions category (code string) uses alert-info', () => {
        const { container } = mount([
            {
                linkId: 'd', type: 'display', text: 'Note',
                extension: [{ url: X.DISPLAY_CATEGORY, valueCode: 'instructions' }],
            },
        ]);
        expect(itemNode(container, 'd').querySelector('.alert').classList.contains('alert-info')).toBe(true);
    });
});

describe('reference and attachment item types', () => {
    test('reference stores a valueReference', () => {
        const { container, runtime } = mount([{ linkId: 'r', type: 'reference', text: 'Ref' }]);
        setValue(itemNode(container, 'r').querySelector('input'), 'Patient/123');
        const item = runtime.getQuestionnaireResponse().item.find((entry) => entry.linkId === 'r');
        expect(item.answer[0].valueReference.reference).toBe('Patient/123');
    });

    test('attachment renders a file input and respects readOnly', () => {
        const { container } = mount([{ linkId: 'a', type: 'attachment', text: 'Upload', readOnly: true }]);
        const input = itemNode(container, 'a').querySelector('input[type="file"]');
        expect(input).not.toBeNull();
        expect(input.disabled).toBe(true);
    });
});

describe('repeating scalar items', () => {
    test('collects multiple answers through add rows and removes them', () => {
        const { container, runtime } = mount([{ linkId: 'm', type: 'string', text: 'Aliases', repeats: true }]);
        const control = itemNode(container, 'm').querySelector('.oe-questionnaire-control');

        // one blank row to start
        let inputs = control.querySelectorAll('input');
        expect(inputs).toHaveLength(1);
        setValue(inputs[0], 'first');

        inputs = control.querySelectorAll('input');
        // first committed + a new blank row
        expect(inputs).toHaveLength(2);
        setValue(inputs[1], 'second');

        let answers = runtime.getQuestionnaireResponse().item.find((entry) => entry.linkId === 'm').answer;
        expect(answers.map((a) => a.valueString)).toEqual(['first', 'second']);

        // remove the first row
        control.querySelector('.oe-questionnaire-repeat-remove').click();
        answers = runtime.getQuestionnaireResponse().item.find((entry) => entry.linkId === 'm').answer;
        expect(answers.map((a) => a.valueString)).toEqual(['second']);
    });
});
