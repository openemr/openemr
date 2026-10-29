/**
 * @jest-environment jsdom
 */

/**
 * Calculated expression (SDC calculatedExpression + variable) tests for the
 * OpenEMR FHIR Questionnaire Runtime.
 *
 * Covers FHIRPath arithmetic, aggregate methods (sum, count, exists, first),
 * where() filtering on %resource, iif() branching, variable extensions,
 * divide-by-zero and empty-input clearing, interplay with enableWhen (disabled
 * answers leave %resource; calculated values can gate other items), and
 * resilience to expressions that fail to compile.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-calculated.test.js
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

const CALCULATED_EXPRESSION = 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-calculatedExpression';
const VARIABLE = 'http://hl7.org/fhir/StructureDefinition/variable';

function createContainer() {
    const container = document.createElement('div');
    document.body.appendChild(container);
    return container;
}

function baseQuestionnaire(items) {
    return {
        resourceType: 'Questionnaire',
        status: 'active',
        item: items,
    };
}

function calculated(expression) {
    return [{
        url: CALCULATED_EXPRESSION,
        valueExpression: { language: 'text/fhirpath', expression },
    }];
}

function variable(name, expression) {
    return {
        url: VARIABLE,
        valueExpression: { name, language: 'text/fhirpath', expression },
    };
}

function answerPath(linkId, valueKey) {
    return "%resource.item.where(linkId='" + linkId + "').answer." + valueKey;
}

function mountItems(items) {
    const container = createContainer();
    const runtime = runtimeApi.mount({ questionnaire: baseQuestionnaire(items), container });
    return { container, runtime };
}

function answerOf(runtime, linkId) {
    const item = runtime.getQuestionnaireResponse().item.find((entry) => entry.linkId === linkId);
    return item ? item.answer[0] : undefined;
}

beforeEach(() => {
    document.body.innerHTML = '';
    runtimeApi.runtime = null;
});

describe('calculated expressions: arithmetic', () => {
    function mountTotal(expression) {
        return mountItems([
            { linkId: 'a', type: 'integer', text: 'A' },
            { linkId: 'b', type: 'integer', text: 'B' },
            { linkId: 'total', type: 'integer', text: 'Total', readOnly: true, extension: calculated(expression) },
        ]);
    }

    test('addition recalculates as source answers change', () => {
        const { runtime } = mountTotal(answerPath('a', 'valueInteger') + ' + ' + answerPath('b', 'valueInteger'));
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: 2 }]);
        runtime.setAnswers('b', [{ valueKey: 'valueInteger', value: 3 }]);
        expect(answerOf(runtime, 'total').valueInteger).toBe(5);
        runtime.setAnswers('b', [{ valueKey: 'valueInteger', value: 10 }]);
        expect(answerOf(runtime, 'total').valueInteger).toBe(12);
    });

    test.each([
        ['-', 7, 2, 5],
        ['*', 4, 3, 12],
        ['/', 10, 4, 2.5],
    ])('operator %s', (operator, left, right, expected) => {
        const { runtime } = mountTotal(
            answerPath('a', 'valueInteger') + ' ' + operator + ' ' + answerPath('b', 'valueInteger')
        );
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: left }]);
        runtime.setAnswers('b', [{ valueKey: 'valueInteger', value: right }]);
        expect(answerOf(runtime, 'total').valueInteger).toBe(expected);
    });

    test('division by zero clears the calculated answer', () => {
        const { runtime } = mountTotal(
            answerPath('a', 'valueInteger') + ' / ' + answerPath('b', 'valueInteger')
        );
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: 10 }]);
        runtime.setAnswers('b', [{ valueKey: 'valueInteger', value: 2 }]);
        expect(answerOf(runtime, 'total').valueInteger).toBe(5);
        runtime.setAnswers('b', [{ valueKey: 'valueInteger', value: 0 }]);
        expect(answerOf(runtime, 'total')).toBeUndefined();
    });

    test('missing source input clears the calculated answer', () => {
        const { runtime } = mountTotal(
            answerPath('a', 'valueInteger') + ' + ' + answerPath('b', 'valueInteger')
        );
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: 1 }]);
        runtime.setAnswers('b', [{ valueKey: 'valueInteger', value: 2 }]);
        expect(answerOf(runtime, 'total').valueInteger).toBe(3);
        runtime.setAnswers('a', []);
        expect(answerOf(runtime, 'total')).toBeUndefined();
    });

    test('decimal target item stores its calculated value as valueDecimal', () => {
        const { runtime } = mountItems([
            { linkId: 'a', type: 'decimal', text: 'A' },
            {
                linkId: 'half',
                type: 'decimal',
                text: 'Half',
                readOnly: true,
                extension: calculated(answerPath('a', 'valueDecimal') + ' / 2'),
            },
        ]);
        runtime.setAnswers('a', [{ valueKey: 'valueDecimal', value: 5 }]);
        expect(answerOf(runtime, 'half').valueDecimal).toBe(2.5);
    });
});

describe('calculated expressions: aggregates and filtering', () => {
    test('sum() totals every answer of a repeating item', () => {
        const { runtime } = mountItems([
            {
                linkId: 'scores',
                type: 'integer',
                text: 'Scores',
                repeats: true,
            },
            {
                linkId: 'total',
                type: 'integer',
                text: 'Total',
                readOnly: true,
                extension: calculated(answerPath('scores', 'valueInteger') + '.sum()'),
            },
        ]);
        runtime.setAnswers('scores', [
            { valueKey: 'valueInteger', value: 2 },
            { valueKey: 'valueInteger', value: 3 },
            { valueKey: 'valueInteger', value: 4 },
        ]);
        expect(answerOf(runtime, 'total').valueInteger).toBe(9);
    });

    test('count() and exists() drive an iif() branch', () => {
        const { runtime } = mountItems([
            { linkId: 'symptoms', type: 'string', text: 'Symptoms', repeats: true },
            {
                linkId: 'symptom-count',
                type: 'integer',
                text: 'Symptom count',
                readOnly: true,
                extension: calculated(answerPath('symptoms', 'valueString') + '.count()'),
            },
            {
                linkId: 'triage',
                type: 'integer',
                text: 'Triage flag',
                readOnly: true,
                extension: calculated(
                    'iif(' + answerPath('symptoms', 'valueString') + '.exists(), 1, 0)'
                ),
            },
        ]);
        expect(answerOf(runtime, 'symptom-count').valueInteger).toBe(0);
        expect(answerOf(runtime, 'triage').valueInteger).toBe(0);
        runtime.setAnswers('symptoms', [
            { valueKey: 'valueString', value: 'cough' },
            { valueKey: 'valueString', value: 'fever' },
        ]);
        expect(answerOf(runtime, 'symptom-count').valueInteger).toBe(2);
        expect(answerOf(runtime, 'triage').valueInteger).toBe(1);
    });

    test('first() picks the first answer of a repeating item', () => {
        const { runtime } = mountItems([
            { linkId: 'readings', type: 'integer', text: 'Readings', repeats: true },
            {
                linkId: 'baseline',
                type: 'integer',
                text: 'Baseline',
                readOnly: true,
                extension: calculated(answerPath('readings', 'valueInteger') + '.first()'),
            },
        ]);
        runtime.setAnswers('readings', [
            { valueKey: 'valueInteger', value: 7 },
            { valueKey: 'valueInteger', value: 9 },
        ]);
        expect(answerOf(runtime, 'baseline').valueInteger).toBe(7);
    });

    test('where() isolates the addressed item so sibling answers do not leak in', () => {
        const { runtime } = mountItems([
            { linkId: 'a', type: 'integer', text: 'A' },
            { linkId: 'b', type: 'integer', text: 'B' },
            {
                linkId: 'echo-a',
                type: 'integer',
                text: 'Echo of A',
                readOnly: true,
                extension: calculated(answerPath('a', 'valueInteger')),
            },
        ]);
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: 1 }]);
        runtime.setAnswers('b', [{ valueKey: 'valueInteger', value: 99 }]);
        expect(answerOf(runtime, 'echo-a').valueInteger).toBe(1);
    });

    test('iif() comparison branches: score band', () => {
        const { runtime } = mountItems([
            { linkId: 'score', type: 'integer', text: 'Score' },
            {
                linkId: 'band',
                type: 'integer',
                text: 'Band',
                readOnly: true,
                extension: calculated(
                    'iif(' + answerPath('score', 'valueInteger') + " >= 10, 2, 1)"
                ),
            },
        ]);
        runtime.setAnswers('score', [{ valueKey: 'valueInteger', value: 12 }]);
        expect(answerOf(runtime, 'band').valueInteger).toBe(2);
        runtime.setAnswers('score', [{ valueKey: 'valueInteger', value: 4 }]);
        expect(answerOf(runtime, 'band').valueInteger).toBe(1);
    });
});

describe('calculated expressions: variables', () => {
    test('variable extensions feed the calculated expression, later variables see earlier ones', () => {
        const { runtime } = mountItems([
            { linkId: 'weight', type: 'decimal', text: 'Weight (kg)' },
            { linkId: 'height', type: 'decimal', text: 'Height (m)' },
            {
                linkId: 'bmi',
                type: 'decimal',
                text: 'BMI',
                readOnly: true,
                extension: [
                    variable('w', answerPath('weight', 'valueDecimal')),
                    variable('h', answerPath('height', 'valueDecimal')),
                    variable('h2', '%h * %h'),
                    ...calculated('%w / %h2'),
                ],
            },
        ]);
        runtime.setAnswers('weight', [{ valueKey: 'valueDecimal', value: 80 }]);
        runtime.setAnswers('height', [{ valueKey: 'valueDecimal', value: 2 }]);
        expect(answerOf(runtime, 'bmi').valueDecimal).toBe(20);
    });
});

describe('calculated expressions: interplay with enableWhen', () => {
    test('answers of a disabled item drop out of %resource and the total', () => {
        const { runtime } = mountItems([
            { linkId: 'include-extra', type: 'boolean', text: 'Include extra?' },
            { linkId: 'base', type: 'integer', text: 'Base' },
            {
                linkId: 'extra',
                type: 'integer',
                text: 'Extra',
                enableWhen: [{ question: 'include-extra', operator: '=', answerBoolean: true }],
            },
            {
                linkId: 'total',
                type: 'integer',
                text: 'Total',
                readOnly: true,
                extension: calculated(
                    'iif(' + answerPath('extra', 'valueInteger') + '.exists(), '
                    + answerPath('base', 'valueInteger') + ' + ' + answerPath('extra', 'valueInteger') + ', '
                    + answerPath('base', 'valueInteger') + ')'
                ),
            },
        ]);
        runtime.setAnswers('include-extra', [{ valueKey: 'valueBoolean', value: true }]);
        runtime.setAnswers('base', [{ valueKey: 'valueInteger', value: 5 }]);
        runtime.setAnswers('extra', [{ valueKey: 'valueInteger', value: 3 }]);
        expect(answerOf(runtime, 'total').valueInteger).toBe(8);

        // disabling 'extra' removes its answer from %resource, so the total recalculates without it
        runtime.setAnswers('include-extra', [{ valueKey: 'valueBoolean', value: false }]);
        expect(answerOf(runtime, 'total').valueInteger).toBe(5);
    });

    test('a calculated value can gate another item through enableWhen', () => {
        const { runtime } = mountItems([
            { linkId: 'a', type: 'integer', text: 'A' },
            { linkId: 'b', type: 'integer', text: 'B' },
            {
                linkId: 'total',
                type: 'integer',
                text: 'Total',
                readOnly: true,
                extension: calculated(answerPath('a', 'valueInteger') + ' + ' + answerPath('b', 'valueInteger')),
            },
            {
                linkId: 'high-score-followup',
                type: 'string',
                text: 'Follow-up for high totals',
                enableWhen: [{ question: 'total', operator: '>=', answerInteger: 10 }],
            },
        ]);
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: 4 }]);
        runtime.setAnswers('b', [{ valueKey: 'valueInteger', value: 4 }]);
        expect(runtime.isEnabled('high-score-followup')).toBe(false);
        runtime.setAnswers('b', [{ valueKey: 'valueInteger', value: 7 }]);
        expect(runtime.isEnabled('high-score-followup')).toBe(true);
    });
});

describe('calculated expressions: resilience', () => {
    test('an expression that fails to compile does not break the mount or other calculations', () => {
        const { runtime } = mountItems([
            { linkId: 'a', type: 'integer', text: 'A' },
            {
                linkId: 'broken',
                type: 'integer',
                text: 'Broken',
                readOnly: true,
                extension: calculated('this is ! not fhirpath ('),
            },
            {
                linkId: 'working',
                type: 'integer',
                text: 'Working',
                readOnly: true,
                extension: calculated(answerPath('a', 'valueInteger') + ' * 2'),
            },
        ]);
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: 6 }]);
        expect(answerOf(runtime, 'broken')).toBeUndefined();
        expect(answerOf(runtime, 'working').valueInteger).toBe(12);
    });

    test('a non-fhirpath expression language is ignored without error', () => {
        const { runtime } = mountItems([
            { linkId: 'a', type: 'integer', text: 'A' },
            {
                linkId: 'cql-item',
                type: 'integer',
                text: 'CQL item',
                readOnly: true,
                extension: [{
                    url: CALCULATED_EXPRESSION,
                    valueExpression: { language: 'text/cql', expression: 'define x: 1' },
                }],
            },
        ]);
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: 1 }]);
        expect(answerOf(runtime, 'cql-item')).toBeUndefined();
    });
});

describe('calculated expressions: answering over a calculation (SDC 4.0.0 Using Expressions)', () => {
    const typeInto = (container, linkId, value) => {
        const input = container.querySelector('[data-link-id="' + linkId + '"] input');
        input.value = value;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    };
    const doubled = [
        { linkId: 'a', type: 'integer', text: 'A' },
        { linkId: 'double', type: 'integer', text: 'Double (editable)', extension: calculated(answerPath('a', 'valueInteger') + ' * 2') },
    ];

    test('a value typed over an editable calculation stays as other answers change', () => {
        const { container, runtime } = mountItems(doubled);
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: 2 }]);
        expect(answerOf(runtime, 'double').valueInteger).toBe(4);
        typeInto(container, 'double', '9');
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: 3 }]);
        expect(answerOf(runtime, 'double').valueInteger).toBe(9);
        expect(container.querySelector('[data-link-id="double"] input').value).toBe('9');
    });

    test('clearing the typed value hands the item back to its calculation', () => {
        const { container, runtime } = mountItems(doubled);
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: 2 }]);
        typeInto(container, 'double', '9');
        typeInto(container, 'double', '');
        expect(answerOf(runtime, 'double').valueInteger).toBe(4);
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: 5 }]);
        expect(answerOf(runtime, 'double').valueInteger).toBe(10);
    });

    test('a measurement the calculation cannot supply is kept when typed, and conditions on it work', () => {
        // The shape of WHO's respiratory rate questions: taken from a profile value when the
        // host has one, else counted and typed in.
        const rateExtensions = [
            { url: 'http://hl7.org/fhir/StructureDefinition/questionnaire-unit', valueCoding: { system: 'http://unitsofmeasure.org', code: '{Breaths}/min', display: 'breaths/min' } },
            ...calculated("iif(%resource.item.where(linkId='profile').answer.exists(), %resource.item.where(linkId='profile').answer.value, {})"),
        ];
        const { container, runtime } = mountItems([
            { linkId: 'profile', type: 'quantity', text: 'Rate on file', readOnly: true },
            { linkId: 'rate', type: 'quantity', text: 'Breathing rate', extension: rateExtensions },
            {
                linkId: 'fast', type: 'display', text: 'Fast breathing',
                extension: [{ url: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-enableWhenExpression', valueExpression: { language: 'text/fhirpath', expression: "%resource.item.where(linkId='rate').answer.value >= 60 '{Breaths}/min'" } }],
            },
        ]);
        container.querySelector('[data-link-id="rate"] input[type="number"]').value = '70';
        container.querySelector('[data-link-id="rate"] input[type="number"]').dispatchEvent(new Event('change', { bubbles: true }));
        expect(answerOf(runtime, 'rate').valueQuantity).toEqual({ value: 70, unit: 'breaths/min', system: 'http://unitsofmeasure.org', code: '{Breaths}/min' });
        expect(runtime.isEnabled('fast')).toBe(true);
    });

    test('a calculated quantity shows its number in the value box', () => {
        const { container, runtime } = mountItems([
            { linkId: 'kg', type: 'decimal', text: 'Weight in kg' },
            {
                linkId: 'w', type: 'quantity', text: 'Weight', readOnly: true,
                extension: calculated("%resource.item.where(linkId='kg').answer.value * 1 'kg'"),
            },
        ]);
        runtime.setAnswers('kg', [{ valueKey: 'valueDecimal', value: 70 }]);
        expect(answerOf(runtime, 'w').valueQuantity.value).toBe(70);
        expect(container.querySelector('[data-link-id="w"] input[type="number"]').value).toBe('70');
    });

    test('a saved response whose editable calculated answer differs from the calculation keeps it', () => {
        const container = createContainer();
        const runtime = runtimeApi.mount({
            questionnaire: baseQuestionnaire(doubled),
            questionnaireResponse: { resourceType: 'QuestionnaireResponse', status: 'in-progress', item: [
                { linkId: 'a', answer: [{ valueInteger: 2 }] },
                { linkId: 'double', answer: [{ valueInteger: 9 }] },
            ] },
            container,
        });
        expect(answerOf(runtime, 'double').valueInteger).toBe(9);
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: 3 }]);
        expect(answerOf(runtime, 'double').valueInteger).toBe(9);
    });

    test('a saved response whose calculated answers match keeps calculating, and read-only ones always do', () => {
        const container = createContainer();
        const runtime = runtimeApi.mount({
            questionnaire: baseQuestionnaire([
                ...doubled,
                { linkId: 'total', type: 'integer', text: 'Total', readOnly: true, extension: calculated(answerPath('a', 'valueInteger') + ' + 1') },
            ]),
            questionnaireResponse: { resourceType: 'QuestionnaireResponse', status: 'in-progress', item: [
                { linkId: 'a', answer: [{ valueInteger: 2 }] },
                { linkId: 'double', answer: [{ valueInteger: 4 }] },
                { linkId: 'total', answer: [{ valueInteger: 99 }] },
            ] },
            container,
        });
        expect(answerOf(runtime, 'total').valueInteger).toBe(3);
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: 5 }]);
        expect(answerOf(runtime, 'double').valueInteger).toBe(10);
        expect(answerOf(runtime, 'total').valueInteger).toBe(6);
    });
});

describe('calculated expressions: each answer type saved and shown', () => {
    const atLeast = (limit) => answerPath('a', 'valueInteger') + ' > ' + limit;

    test('a calculated boolean shows as Yes or No', () => {
        const { container, runtime } = mountItems([
            { linkId: 'a', type: 'integer', text: 'A' },
            { linkId: 'big', type: 'boolean', text: 'Big', readOnly: true, extension: calculated(atLeast(5)) },
        ]);
        const radios = () => [...container.querySelectorAll('[data-link-id="big"] input[type="radio"]')].map((radio) => radio.checked);
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: 9 }]);
        expect(answerOf(runtime, 'big').valueBoolean).toBe(true);
        expect(radios()).toEqual([true, false]);
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: 2 }]);
        expect(radios()).toEqual([false, true]);
    });

    test.each([['radio-button'], ['drop-down']])('a calculated choice is saved as a Coding and shown selected (%s)', (control) => {
        const options = [{ valueCoding: { code: 'lo', display: 'Low' } }, { valueCoding: { code: 'hi', display: 'High' } }];
        const { container, runtime } = mountItems([
            { linkId: 'a', type: 'integer', text: 'A' },
            {
                linkId: 'band', type: 'choice', text: 'Band', readOnly: true, answerOption: options,
                extension: [
                    { url: 'http://hl7.org/fhir/StructureDefinition/questionnaire-itemControl', valueCodeableConcept: { coding: [{ system: 'http://hl7.org/fhir/questionnaire-item-control', code: control }] } },
                    ...calculated('iif(' + atLeast(5) + ", %questionnaire.item.where(linkId='band').answerOption[1].value, %questionnaire.item.where(linkId='band').answerOption[0].value)"),
                ],
            },
        ]);
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: 9 }]);
        expect(answerOf(runtime, 'band')).toEqual({ valueCoding: { code: 'hi', display: 'High' } });
        const shown = control === 'drop-down'
            ? container.querySelector('[data-link-id="band"] select').value
            : String([...container.querySelectorAll('[data-link-id="band"] input[type="radio"]')].findIndex((radio) => radio.checked));
        expect(shown).toBe('1');
    });

    test('an initial expression on a choice is saved as a Coding', () => {
        const { runtime } = mountItems([{
            linkId: 'band', type: 'choice', text: 'Band', answerOption: [{ valueCoding: { code: 'lo', display: 'Low' } }],
            extension: [{ url: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-initialExpression', valueExpression: { language: 'text/fhirpath', expression: "%questionnaire.item.where(linkId='band').answerOption.first().value" } }],
        }]);
        expect(answerOf(runtime, 'band')).toEqual({ valueCoding: { code: 'lo', display: 'Low' } });
    });

    test('a calculated quantity shows its own unit, and no unit it is not in', () => {
        const unitOption = (code, display) => ({ url: 'http://hl7.org/fhir/StructureDefinition/questionnaire-unitOption', valueCoding: { system: 'http://unitsofmeasure.org', code, display } });
        const { container, runtime } = mountItems([
            { linkId: 'n', type: 'decimal', text: 'Number' },
            { linkId: 'unit', type: 'string', text: 'Unit code' },
            {
                linkId: 'w', type: 'quantity', text: 'Weight', readOnly: true,
                extension: [unitOption('kg', 'kg'), unitOption('[lb_av]', 'lb'), ...calculated("iif(%resource.item.where(linkId='unit').answer.value = 'g', " + answerPath('n', 'value') + " * 1 'g', " + answerPath('n', 'value') + " * 1 '[lb_av]')")],
            },
        ]);
        const select = container.querySelector('[data-link-id="w"] select');
        runtime.setAnswers('n', [{ valueKey: 'valueDecimal', value: 150 }]);
        expect(select.options[select.selectedIndex].textContent).toBe('lb');
        runtime.setAnswers('unit', [{ valueKey: 'valueString', value: 'g' }]);
        expect(select.value).toBe('');
        expect(answerOf(runtime, 'w').valueQuantity).toEqual({ value: 150, unit: 'g', code: 'g' });
    });

    test('a typed answer over a calculation stays with its own repetition', () => {
        const { container, runtime } = mountItems([{
            linkId: 'kids', type: 'group', repeats: true, text: 'Child', item: [
                { linkId: 'age', type: 'integer', text: 'Age' },
                { linkId: 'months', type: 'integer', text: 'Months', extension: calculated("%context.item.where(linkId='age').answer.value * 12") },
            ],
        }]);
        [...container.querySelectorAll('button')].find((button) => /Add/.test(button.textContent)).click();
        const key = (index, linkId) => 'kids\u0000' + index + '\u0000' + linkId;
        const months = (index) => runtime.responseStore.getAnswers(key(index, 'months'))[0]?.value;
        runtime.setAnswers(key(0, 'age'), [{ valueKey: 'valueInteger', value: 2 }]);
        runtime.setAnswers(key(1, 'age'), [{ valueKey: 'valueInteger', value: 3 }]);
        expect([months(0), months(1)]).toEqual([24, 36]);
        const box = runtime.renderer.inputs.get(key(0, 'months'));
        box.value = '30';
        box.dispatchEvent(new Event('change', { bubbles: true }));
        runtime.setAnswers(key(0, 'age'), [{ valueKey: 'valueInteger', value: 4 }]);
        runtime.setAnswers(key(1, 'age'), [{ valueKey: 'valueInteger', value: 5 }]);
        expect([months(0), months(1)]).toEqual([30, 60]);
    });
});
