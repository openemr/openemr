/**
 * @jest-environment jsdom
 */

/**
 * Regression tests for the PR #14744 review: enableWhenExpression evaluated
 * per repeat instance, FHIRPath names that must not reach Object.prototype,
 * pruning of removed instances' nodes, the response cache shared by
 * expressions (and that it never goes stale), repeat()'s level guard, and the
 * url|version#fragment canonical.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-review-fixes.test.js
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
const ENABLE_WHEN_EXPRESSION = SDC + 'sdc-questionnaire-enableWhenExpression';
const CALC = SDC + 'sdc-questionnaire-calculatedExpression';
const VARIABLE = 'http://hl7.org/fhir/StructureDefinition/variable';
const fhirpath = (expression) => ({ language: 'text/fhirpath', expression });

function mount(items, { questionnaire = {}, questionnaireResponse = null } = {}) {
    const container = document.createElement('div');
    document.body.appendChild(container);
    const runtime = runtimeApi.mount({
        questionnaire: { resourceType: 'Questionnaire', status: 'active', item: items, ...questionnaire },
        questionnaireResponse,
        container,
    });
    return { container, runtime };
}

const instancesOf = (container, linkId) => [...container.querySelector('[data-link-id="' + linkId + '"]')
    .querySelectorAll(':scope > .card-body > .oe-questionnaire-group-instance')];
const addInstance = (container, linkId) => container.querySelector('[data-link-id="' + linkId + '"]')
    .querySelector(':scope > .card-body > .oe-questionnaire-repeat-add').click();
const visible = (root, linkId) => {
    const node = root.querySelector('[data-link-id="' + linkId + '"]');
    return node !== null && node.closest('.d-none') === null;
};
const pickYes = (root, linkId) => {
    const radio = root.querySelector('[data-link-id="' + linkId + '"] input[type="radio"]');
    radio.checked = true;
    radio.dispatchEvent(new Event('change', { bubbles: true }));
};
const typeInto = (root, linkId, value) => {
    const input = root.querySelector('[data-link-id="' + linkId + '"] input');
    input.value = String(value);
    input.dispatchEvent(new Event('change', { bubbles: true }));
};

beforeEach(() => {
    document.body.innerHTML = '';
});

describe('enableWhenExpression inside a repeating group', () => {
    const flagged = () => mount([{
        linkId: 'g', type: 'group', text: 'Person', repeats: true, item: [
            { linkId: 'flag', type: 'boolean', text: 'Flag' },
            {
                linkId: 'dep', type: 'string', text: 'Detail',
                extension: [{ url: ENABLE_WHEN_EXPRESSION, valueExpression: fhirpath("%context.item.where(linkId = 'flag').answer.value = true") }],
            },
        ],
    }]);

    test('is evaluated against each instance, not the first', () => {
        const { container } = flagged();
        addInstance(container, 'g');
        expect(visible(instancesOf(container, 'g')[0], 'dep')).toBe(false);
        pickYes(instancesOf(container, 'g')[0], 'flag');
        expect(visible(instancesOf(container, 'g')[0], 'dep')).toBe(true);
        expect(visible(instancesOf(container, 'g')[1], 'dep')).toBe(false);
        pickYes(instancesOf(container, 'g')[1], 'flag');
        expect(visible(instancesOf(container, 'g')[1], 'dep')).toBe(true);
    });

    test('a disabled instance item is left out of that instance of the response', () => {
        const { container, runtime } = flagged();
        addInstance(container, 'g');
        pickYes(instancesOf(container, 'g')[0], 'flag');
        typeInto(instancesOf(container, 'g')[0], 'dep', 'kept');
        const groups = runtime.getQuestionnaireResponse().item.filter((entry) => entry.linkId === 'g');
        expect(groups[0].item.find((entry) => entry.linkId === 'dep').answer).toEqual([{ valueString: 'kept' }]);
        expect((groups[1].item ?? []).some((entry) => entry.linkId === 'dep')).toBe(false);
    });

    test('group variables resolve to the instance as well', () => {
        const { container } = mount([{
            linkId: 'g', type: 'group', text: 'G', repeats: true,
            extension: [{ url: VARIABLE, valueExpression: { name: 'age', ...fhirpath("item.where(linkId = 'age').answer.value") } }],
            item: [
                { linkId: 'age', type: 'integer', text: 'Age' },
                { linkId: 'adult', type: 'string', text: 'Adult only', extension: [{ url: ENABLE_WHEN_EXPRESSION, valueExpression: fhirpath('%age >= 18') }] },
            ],
        }]);
        addInstance(container, 'g');
        typeInto(instancesOf(container, 'g')[0], 'age', 40);
        typeInto(instancesOf(container, 'g')[1], 'age', 9);
        expect(visible(instancesOf(container, 'g')[0], 'adult')).toBe(true);
        expect(visible(instancesOf(container, 'g')[1], 'adult')).toBe(false);
    });
});

describe('FHIRPath names never reach Object.prototype', () => {
    test.each(['%constructor', '%toString', '%hasOwnProperty', '%__proto__', '%resource.constructor', "'x'.toString.length()"])('%s gives no answer', (expression) => {
        const { runtime } = mount([{ linkId: 'out', type: 'string', text: 'Out', readOnly: true, extension: [{ url: CALC, valueExpression: fhirpath(expression) }] }]);
        expect(runtime.getQuestionnaireResponse().item).toBeUndefined();
    });

    test('a variable may be named like a prototype member and still works', () => {
        const { runtime } = mount([{
            linkId: 'out', type: 'integer', text: 'Out', readOnly: true, extension: [
                { url: VARIABLE, valueExpression: { name: '__proto__', ...fhirpath('5') } },
                { url: VARIABLE, valueExpression: { name: 'constructor', ...fhirpath('%__proto__ + 1') } },
                { url: CALC, valueExpression: fhirpath('%constructor + (1 | 2).where($this > 1).count()') },
            ],
        }]);
        expect(runtime.getQuestionnaireResponse().item[0].answer).toEqual([{ valueInteger: 7 }]);
        expect(Object.getPrototypeOf({})).toBe(Object.prototype);
        expect({}.constructor).toBe(Object);
    });
});

describe('removed instances are forgotten by the renderer', () => {
    test('nodes and inputs of removed instances are pruned', () => {
        const { container, runtime } = mount([{ linkId: 'g', type: 'group', text: 'G', repeats: true, item: [{ linkId: 's', type: 'string', text: 'S' }] }]);
        for (let index = 0; index < 4; index++) {
            addInstance(container, 'g');
        }
        for (let index = 0; index < 4; index++) {
            instancesOf(container, 'g')[1].querySelector('.oe-questionnaire-repeat-remove').click();
        }
        const detached = (map) => [...map.values()].filter((node) => !node.isConnected).length;
        expect(detached(runtime.renderer.nodes)).toBe(0);
        expect(detached(runtime.renderer.inputs)).toBe(0);
        expect(runtime.renderer.nodes.size).toBe(2);
    });
});

describe('response cache shared by expressions', () => {
    const fiftyInstances = () => {
        const response = {
            resourceType: 'QuestionnaireResponse', status: 'in-progress',
            item: Array.from({ length: 50 }, (_, index) => ({ linkId: 'g', item: [{ linkId: 'a', answer: [{ valueInteger: index }] }] })),
        };
        return mount([
            {
                linkId: 'g', type: 'group', text: 'G', repeats: true, item: [
                    { linkId: 'a', type: 'integer', text: 'A' },
                    { linkId: 'b', type: 'integer', text: 'B', readOnly: true, extension: [{ url: CALC, valueExpression: fhirpath("%context.item.where(linkId = 'a').answer.value * 2") }] },
                    { linkId: 'c', type: 'string', text: 'C', extension: [{ url: ENABLE_WHEN_EXPRESSION, valueExpression: fhirpath("%context.item.where(linkId = 'a').answer.value > 10") }] },
                ],
            },
            { linkId: 'total', type: 'integer', text: 'Total', readOnly: true, extension: [{ url: CALC, valueExpression: fhirpath("%resource.repeat(item).where(linkId = 'b').answer.value.sum()") }] },
        ], { questionnaireResponse: response });
    };

    test('builds the response a handful of times per change, not once per expression', () => {
        const { container, runtime } = fiftyInstances();
        let builds = 0;
        const original = runtime.responseStore.buildQuestionnaireResponse.bind(runtime.responseStore);
        runtime.responseStore.buildQuestionnaireResponse = (isEnabled) => {
            builds++;
            return original(isEnabled);
        };
        typeInto(instancesOf(container, 'g')[0], 'a', 100);
        // 50 calculations + 50 enableWhenExpressions; previously ~150 builds
        expect(builds).toBeLessThan(15);
        // and the results are current: 2 * (100 + 1 + ... + 49)
        expect(container.querySelector('[data-link-id="total"] input').value).toBe(String(2 * (100 + (49 * 50) / 2)));
    });

    test('a form-level total over per-instance calculations is current after one change', () => {
        const { container } = mount([
            {
                linkId: 'g', type: 'group', text: 'G', repeats: true, item: [
                    { linkId: 'a', type: 'integer', text: 'A' },
                    { linkId: 'b', type: 'integer', text: 'B', readOnly: true, extension: [{ url: CALC, valueExpression: fhirpath("%context.item.where(linkId = 'a').answer.value * 2") }] },
                ],
            },
            { linkId: 'total', type: 'integer', text: 'Total', readOnly: true, extension: [{ url: CALC, valueExpression: fhirpath("%resource.repeat(item).where(linkId = 'b').answer.value.sum()") }] },
        ]);
        addInstance(container, 'g');
        typeInto(instancesOf(container, 'g')[0], 'a', 3);
        // previously the total lagged one change behind the instance calculation
        expect(container.querySelector('[data-link-id="total"] input').value).toBe('6');
        typeInto(instancesOf(container, 'g')[1], 'a', 5);
        expect(container.querySelector('[data-link-id="total"] input').value).toBe('16');
    });

    test('a chain of calculations still settles on the latest answer', () => {
        const { container, runtime } = mount([
            { linkId: 'a', type: 'integer', text: 'A' },
            { linkId: 'b', type: 'integer', text: 'B', readOnly: true, extension: [{ url: CALC, valueExpression: fhirpath("%resource.item.where(linkId = 'a').answer.value + 1") }] },
            { linkId: 'c', type: 'integer', text: 'C', readOnly: true, extension: [{ url: CALC, valueExpression: fhirpath("%resource.item.where(linkId = 'b').answer.value * 10") }] },
        ]);
        for (const value of [1, 7, 3]) {
            typeInto(container, 'a', value);
            const answers = Object.fromEntries(runtime.getQuestionnaireResponse().item.map((entry) => [entry.linkId, entry.answer[0].valueInteger]));
            expect(answers).toEqual({ a: value, b: value + 1, c: (value + 1) * 10 });
        }
    });

    test('an enablement change reaches expressions in the same refresh', () => {
        const { container } = mount([
            { linkId: 'show', type: 'boolean', text: 'Show' },
            { linkId: 'n', type: 'integer', text: 'N', enableWhen: [{ question: 'show', operator: '=', answerBoolean: true }] },
            { linkId: 'sum', type: 'integer', text: 'Sum', readOnly: true, extension: [{ url: CALC, valueExpression: fhirpath("%resource.item.where(linkId = 'n').answer.value.sum()") }] },
        ]);
        pickYes(container, 'show');
        typeInto(container, 'n', 4);
        expect(container.querySelector('[data-link-id="sum"] input').value).toBe('4');
        const no = container.querySelectorAll('[data-link-id="show"] input[type="radio"]')[1];
        no.checked = true;
        no.dispatchEvent(new Event('change', { bubbles: true }));
        // the disabled answer leaves %resource at once
        expect(container.querySelector('[data-link-id="sum"] input').value).toBe('0');
    });

    test('rewriting an unchanged answer leaves the store revision alone', () => {
        const { runtime } = mount([{ linkId: 'a', type: 'string', text: 'A' }]);
        runtime.responseStore.setAnswers('a', [{ valueKey: 'valueString', value: 'x' }]);
        const revision = runtime.responseStore.revision;
        runtime.responseStore.setAnswers('a', [{ valueKey: 'valueString', value: 'x' }]);
        runtime.responseStore.clearAnswer('missing');
        expect(runtime.responseStore.revision).toBe(revision);
        runtime.responseStore.setAnswers('a', [{ valueKey: 'valueString', value: 'y' }]);
        expect(runtime.responseStore.revision).toBe(revision + 1);
    });
});

describe('repeat() level guard', () => {
    test('a runaway repeat is reported, not silently truncated', () => {
        const evaluator = new runtimeApi.FhirPathEvaluator();
        const ast = new runtimeApi.FhirPathParser().parse('1.repeatAll(1)');
        expect(() => evaluator.evaluate(ast, {}, null)).toThrow(/stopped after 1000 levels/);
    });

    test('a finite repeat still returns everything', () => {
        const tree = { item: [{ linkId: 'a', item: [{ linkId: 'b', item: [{ linkId: 'c' }] }] }] };
        const ast = new runtimeApi.FhirPathParser().parse('%resource.repeat(item).linkId');
        expect(new runtimeApi.FhirPathEvaluator().evaluate(ast, { resource: tree }, null)).toEqual(['a', 'b', 'c']);
    });
});

describe('questionnaire canonical', () => {
    test.each([
        ['http://example.org/q/x', '1.0', 'http://example.org/q/x|1.0'],
        ['http://example.org/q/x#part', '1.0', 'http://example.org/q/x|1.0#part'],
        ['http://example.org/q/x#part', undefined, 'http://example.org/q/x#part'],
    ])('%s + %s -> %s', (url, version, expected) => {
        const { runtime } = mount([], { questionnaire: { url, ...(version ? { version } : {}) } });
        expect(runtime.getQuestionnaireResponse().questionnaire).toBe(expected);
    });
});
