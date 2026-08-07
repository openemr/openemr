/**
 * @jest-environment jsdom
 */

/**
 * enableWhen behavior tests for the OpenEMR FHIR Questionnaire Runtime.
 *
 * Covers the operator matrix (exists, =, !=, >, <, >=, <=), value types
 * (boolean, string, integer, decimal, coding), enableBehavior any/all and the
 * runtime's any-by-default compatibility behavior for multiple conditions,
 * parent cascade through nested groups, DOM visibility (d-none), and the
 * exclusion of disabled items from the built QuestionnaireResponse.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-enablewhen.test.js
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

function baseQuestionnaire(items) {
    return {
        resourceType: 'Questionnaire',
        status: 'active',
        item: items,
    };
}

/**
 * Mount a questionnaire with a controller question and a dependent question
 * carrying the given enableWhen conditions.
 */
function mountDependent({ controller, dependent }) {
    const container = createContainer();
    const runtime = runtimeApi.mount({
        questionnaire: baseQuestionnaire([controller, dependent]),
        container,
    });
    return { container, runtime };
}

function itemNode(container, linkId) {
    return container.querySelector('[data-link-id="' + linkId + '"]');
}

beforeEach(() => {
    document.body.innerHTML = '';
    runtimeApi.runtime = null;
});

describe('enableWhen operators', () => {
    describe('exists', () => {
        test.each([
            ['expected true, unanswered', true, null, false],
            ['expected true, answered', true, [{ valueKey: 'valueString', value: 'anything' }], true],
            ['expected false, unanswered', false, null, true],
            ['expected false, answered', false, [{ valueKey: 'valueString', value: 'anything' }], false],
        ])('%s => enabled %s', (label, expected, answers, shouldBeEnabled) => {
            const { runtime } = mountDependent({
                controller: { linkId: 'q1', type: 'string', text: 'Controller' },
                dependent: {
                    linkId: 'q2',
                    type: 'string',
                    text: 'Dependent',
                    enableWhen: [{ question: 'q1', operator: 'exists', answerBoolean: expected }],
                },
            });
            if (answers) {
                runtime.setAnswers('q1', answers);
            }
            expect(runtime.isEnabled('q2')).toBe(shouldBeEnabled);
        });
    });

    describe('equality (=)', () => {
        test('boolean answer matches answerBoolean', () => {
            const { runtime } = mountDependent({
                controller: { linkId: 'smoker', type: 'boolean', text: 'Do you smoke?' },
                dependent: {
                    linkId: 'packs',
                    type: 'integer',
                    text: 'Packs per day',
                    enableWhen: [{ question: 'smoker', operator: '=', answerBoolean: true }],
                },
            });
            expect(runtime.isEnabled('packs')).toBe(false);
            runtime.setAnswers('smoker', [{ valueKey: 'valueBoolean', value: true }]);
            expect(runtime.isEnabled('packs')).toBe(true);
            runtime.setAnswers('smoker', [{ valueKey: 'valueBoolean', value: false }]);
            expect(runtime.isEnabled('packs')).toBe(false);
        });

        test('string answer matches answerString', () => {
            const { runtime } = mountDependent({
                controller: { linkId: 'color', type: 'string', text: 'Color' },
                dependent: {
                    linkId: 'shade',
                    type: 'string',
                    text: 'Shade',
                    enableWhen: [{ question: 'color', operator: '=', answerString: 'red' }],
                },
            });
            runtime.setAnswers('color', [{ valueKey: 'valueString', value: 'red' }]);
            expect(runtime.isEnabled('shade')).toBe(true);
            runtime.setAnswers('color', [{ valueKey: 'valueString', value: 'blue' }]);
            expect(runtime.isEnabled('shade')).toBe(false);
        });

        test('integer answer matches answerInteger', () => {
            const { runtime } = mountDependent({
                controller: { linkId: 'count', type: 'integer', text: 'Count' },
                dependent: {
                    linkId: 'why',
                    type: 'string',
                    text: 'Why',
                    enableWhen: [{ question: 'count', operator: '=', answerInteger: 3 }],
                },
            });
            runtime.setAnswers('count', [{ valueKey: 'valueInteger', value: 3 }]);
            expect(runtime.isEnabled('why')).toBe(true);
            runtime.setAnswers('count', [{ valueKey: 'valueInteger', value: 4 }]);
            expect(runtime.isEnabled('why')).toBe(false);
        });

        test('coding answer matches answerCoding on code and system', () => {
            const { runtime } = mountDependent({
                controller: {
                    linkId: 'reason',
                    type: 'choice',
                    text: 'Reason',
                    answerOption: [
                        { valueCoding: { code: 'a', system: 'http://example.org/cs', display: 'A' } },
                        { valueCoding: { code: 'b', system: 'http://example.org/cs', display: 'B' } },
                    ],
                },
                dependent: {
                    linkId: 'detail',
                    type: 'string',
                    text: 'Detail',
                    enableWhen: [{
                        question: 'reason',
                        operator: '=',
                        answerCoding: { code: 'a', system: 'http://example.org/cs' },
                    }],
                },
            });
            runtime.setAnswers('reason', [{ valueKey: 'valueCoding', value: { code: 'a', system: 'http://example.org/cs', display: 'A' } }]);
            expect(runtime.isEnabled('detail')).toBe(true);
            runtime.setAnswers('reason', [{ valueKey: 'valueCoding', value: { code: 'b', system: 'http://example.org/cs', display: 'B' } }]);
            expect(runtime.isEnabled('detail')).toBe(false);
        });

        test('coding comparison treats a missing system as a wildcard', () => {
            const { runtime } = mountDependent({
                controller: { linkId: 'reason', type: 'choice', text: 'Reason', answerOption: [{ valueCoding: { code: 'a' } }] },
                dependent: {
                    linkId: 'detail',
                    type: 'string',
                    text: 'Detail',
                    enableWhen: [{ question: 'reason', operator: '=', answerCoding: { code: 'a' } }],
                },
            });
            runtime.setAnswers('reason', [{ valueKey: 'valueCoding', value: { code: 'a', system: 'http://example.org/cs' } }]);
            expect(runtime.isEnabled('detail')).toBe(true);
        });

        test('coding with same code but different systems does not match', () => {
            const { runtime } = mountDependent({
                controller: { linkId: 'reason', type: 'choice', text: 'Reason', answerOption: [{ valueCoding: { code: 'a', system: 'http://one' } }] },
                dependent: {
                    linkId: 'detail',
                    type: 'string',
                    text: 'Detail',
                    enableWhen: [{ question: 'reason', operator: '=', answerCoding: { code: 'a', system: 'http://two' } }],
                },
            });
            runtime.setAnswers('reason', [{ valueKey: 'valueCoding', value: { code: 'a', system: 'http://one' } }]);
            expect(runtime.isEnabled('detail')).toBe(false);
        });

        test('condition referencing an unknown question disables the item', () => {
            const { runtime } = mountDependent({
                controller: { linkId: 'q1', type: 'string', text: 'Controller' },
                dependent: {
                    linkId: 'q2',
                    type: 'string',
                    text: 'Dependent',
                    enableWhen: [{ question: 'does-not-exist', operator: '=', answerString: 'x' }],
                },
            });
            expect(runtime.isEnabled('q2')).toBe(false);
        });
    });

    describe('inequality (!=)', () => {
        test('enabled when the answer differs, disabled when it matches', () => {
            const { runtime } = mountDependent({
                controller: { linkId: 'status', type: 'string', text: 'Status' },
                dependent: {
                    linkId: 'other',
                    type: 'string',
                    text: 'Other',
                    enableWhen: [{ question: 'status', operator: '!=', answerString: 'none' }],
                },
            });
            runtime.setAnswers('status', [{ valueKey: 'valueString', value: 'some' }]);
            expect(runtime.isEnabled('other')).toBe(true);
            runtime.setAnswers('status', [{ valueKey: 'valueString', value: 'none' }]);
            expect(runtime.isEnabled('other')).toBe(false);
        });

        test('unanswered question satisfies != (documents current runtime behavior)', () => {
            const { runtime } = mountDependent({
                controller: { linkId: 'status', type: 'string', text: 'Status' },
                dependent: {
                    linkId: 'other',
                    type: 'string',
                    text: 'Other',
                    enableWhen: [{ question: 'status', operator: '!=', answerString: 'none' }],
                },
            });
            expect(runtime.isEnabled('other')).toBe(true);
        });
    });

    describe('comparators', () => {
        test.each([
            ['>', 5, 6, true],
            ['>', 5, 5, false],
            ['<', 5, 4, true],
            ['<', 5, 5, false],
            ['>=', 5, 5, true],
            ['>=', 5, 4, false],
            ['<=', 5, 5, true],
            ['<=', 5, 6, false],
        ])('integer %s %i with answer %i => enabled %s', (operator, threshold, answer, shouldBeEnabled) => {
            const { runtime } = mountDependent({
                controller: { linkId: 'age', type: 'integer', text: 'Age' },
                dependent: {
                    linkId: 'gated',
                    type: 'string',
                    text: 'Gated',
                    enableWhen: [{ question: 'age', operator, answerInteger: threshold }],
                },
            });
            runtime.setAnswers('age', [{ valueKey: 'valueInteger', value: answer }]);
            expect(runtime.isEnabled('gated')).toBe(shouldBeEnabled);
        });

        test('decimal comparison', () => {
            const { runtime } = mountDependent({
                controller: { linkId: 'temp', type: 'decimal', text: 'Temperature' },
                dependent: {
                    linkId: 'fever',
                    type: 'string',
                    text: 'Fever follow-up',
                    enableWhen: [{ question: 'temp', operator: '>=', answerDecimal: 38.0 }],
                },
            });
            runtime.setAnswers('temp', [{ valueKey: 'valueDecimal', value: 38.2 }]);
            expect(runtime.isEnabled('fever')).toBe(true);
            runtime.setAnswers('temp', [{ valueKey: 'valueDecimal', value: 37.4 }]);
            expect(runtime.isEnabled('fever')).toBe(false);
        });

        test('comparator against an unanswered question disables the item', () => {
            const { runtime } = mountDependent({
                controller: { linkId: 'age', type: 'integer', text: 'Age' },
                dependent: {
                    linkId: 'gated',
                    type: 'string',
                    text: 'Gated',
                    enableWhen: [{ question: 'age', operator: '>', answerInteger: 0 }],
                },
            });
            expect(runtime.isEnabled('gated')).toBe(false);
        });
    });
});

describe('enableBehavior', () => {
    const controllerA = { linkId: 'a', type: 'boolean', text: 'A' };
    const controllerB = { linkId: 'b', type: 'boolean', text: 'B' };

    function mountTwoConditions(extraDependentProps) {
        const container = createContainer();
        const runtime = runtimeApi.mount({
            questionnaire: baseQuestionnaire([
                controllerA,
                controllerB,
                {
                    linkId: 'gated',
                    type: 'string',
                    text: 'Gated',
                    enableWhen: [
                        { question: 'a', operator: '=', answerBoolean: true },
                        { question: 'b', operator: '=', answerBoolean: true },
                    ],
                    ...extraDependentProps,
                },
            ]),
            container,
        });
        return runtime;
    }

    test('explicit all requires every condition', () => {
        const runtime = mountTwoConditions({ enableBehavior: 'all' });
        runtime.setAnswers('a', [{ valueKey: 'valueBoolean', value: true }]);
        expect(runtime.isEnabled('gated')).toBe(false);
        runtime.setAnswers('b', [{ valueKey: 'valueBoolean', value: true }]);
        expect(runtime.isEnabled('gated')).toBe(true);
    });

    test('explicit any requires one condition', () => {
        const runtime = mountTwoConditions({ enableBehavior: 'any' });
        runtime.setAnswers('a', [{ valueKey: 'valueBoolean', value: true }]);
        expect(runtime.isEnabled('gated')).toBe(true);
    });

    test('multiple conditions without enableBehavior default to any (compatibility behavior)', () => {
        const runtime = mountTwoConditions({});
        runtime.setAnswers('a', [{ valueKey: 'valueBoolean', value: true }]);
        expect(runtime.isEnabled('gated')).toBe(true);
    });
});

describe('cascade through nested groups', () => {
    function mountNested() {
        const container = createContainer();
        const runtime = runtimeApi.mount({
            questionnaire: baseQuestionnaire([
                { linkId: 'gate', type: 'boolean', text: 'Open the section?' },
                {
                    linkId: 'section',
                    type: 'group',
                    text: 'Section',
                    enableWhen: [{ question: 'gate', operator: '=', answerBoolean: true }],
                    item: [
                        {
                            linkId: 'subsection',
                            type: 'group',
                            text: 'Subsection',
                            item: [
                                { linkId: 'leaf', type: 'string', text: 'Leaf question' },
                            ],
                        },
                    ],
                },
            ]),
            container,
        });
        return { container, runtime };
    }

    test('disabled parent disables descendants through every level', () => {
        const { runtime } = mountNested();
        expect(runtime.isEnabled('section')).toBe(false);
        expect(runtime.isEnabled('subsection')).toBe(false);
        expect(runtime.isEnabled('leaf')).toBe(false);
        runtime.setAnswers('gate', [{ valueKey: 'valueBoolean', value: true }]);
        expect(runtime.isEnabled('section')).toBe(true);
        expect(runtime.isEnabled('subsection')).toBe(true);
        expect(runtime.isEnabled('leaf')).toBe(true);
    });

    test('descendant with its own satisfied condition stays disabled under a disabled parent', () => {
        const container = createContainer();
        const runtime = runtimeApi.mount({
            questionnaire: baseQuestionnaire([
                { linkId: 'gate', type: 'boolean', text: 'Gate' },
                { linkId: 'always', type: 'boolean', text: 'Always yes' },
                {
                    linkId: 'section',
                    type: 'group',
                    text: 'Section',
                    enableWhen: [{ question: 'gate', operator: '=', answerBoolean: true }],
                    item: [{
                        linkId: 'leaf',
                        type: 'string',
                        text: 'Leaf',
                        enableWhen: [{ question: 'always', operator: '=', answerBoolean: true }],
                    }],
                },
            ]),
            container,
        });
        runtime.setAnswers('always', [{ valueKey: 'valueBoolean', value: true }]);
        expect(runtime.isEnabled('leaf')).toBe(false);
        runtime.setAnswers('gate', [{ valueKey: 'valueBoolean', value: true }]);
        expect(runtime.isEnabled('leaf')).toBe(true);
    });
});

describe('DOM visibility and response building', () => {
    test('disabled item is hidden with d-none and revealed when enabled via its rendered control', () => {
        const { container } = mountDependent({
            controller: { linkId: 'smoker', type: 'boolean', text: 'Do you smoke?' },
            dependent: {
                linkId: 'packs',
                type: 'integer',
                text: 'Packs per day',
                enableWhen: [{ question: 'smoker', operator: '=', answerBoolean: true }],
            },
        });
        const packsNode = itemNode(container, 'packs');
        expect(packsNode.classList.contains('d-none')).toBe(true);

        // drive through the actual rendered Yes radio, like a user would
        const smokerNode = itemNode(container, 'smoker');
        const yesRadio = smokerNode.querySelectorAll('input[type="radio"]')[0];
        yesRadio.checked = true;
        yesRadio.dispatchEvent(new Event('change', { bubbles: true }));

        expect(packsNode.classList.contains('d-none')).toBe(false);
    });

    test('answers of a disabled item are excluded from the QuestionnaireResponse and restored on re-enable', () => {
        const { runtime } = mountDependent({
            controller: { linkId: 'smoker', type: 'boolean', text: 'Do you smoke?' },
            dependent: {
                linkId: 'packs',
                type: 'integer',
                text: 'Packs per day',
                enableWhen: [{ question: 'smoker', operator: '=', answerBoolean: true }],
            },
        });
        runtime.setAnswers('smoker', [{ valueKey: 'valueBoolean', value: true }]);
        runtime.setAnswers('packs', [{ valueKey: 'valueInteger', value: 2 }]);

        let linkIds = runtime.getQuestionnaireResponse().item.map((item) => item.linkId);
        expect(linkIds).toContain('packs');

        runtime.setAnswers('smoker', [{ valueKey: 'valueBoolean', value: false }]);
        linkIds = runtime.getQuestionnaireResponse().item.map((item) => item.linkId);
        expect(linkIds).not.toContain('packs');

        runtime.setAnswers('smoker', [{ valueKey: 'valueBoolean', value: true }]);
        const restored = runtime.getQuestionnaireResponse().item.find((item) => item.linkId === 'packs');
        expect(restored).toBeDefined();
        expect(restored.answer[0].valueInteger).toBe(2);
    });
});
