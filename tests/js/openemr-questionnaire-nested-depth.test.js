/**
 * @jest-environment jsdom
 */

/**
 * Six-level nesting (fixtures/nested-depth-test.json, CC0): a repeating group
 * inside a repeating group, enableWhen reaching up one, two and five levels,
 * a required question at level 6, and a total over every instance using
 * FHIRPath choice-type access (answer.value) (issue #14736).
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-nested-depth.test.js
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
const nestedDepth = require('./fixtures/nested-depth-test.json');

function setup() {
    const container = document.createElement('div');
    document.body.appendChild(container);
    const runtime = runtimeApi.mount({ questionnaire: nestedDepth, container });
    return { container, runtime };
}

const visible = (root, linkId) => {
    const node = root.querySelector('[data-link-id="' + linkId + '"]');
    return node !== null && node.closest('.d-none') === null;
};
const instancesOf = (root, linkId) => [...root.querySelector('[data-link-id="' + linkId + '"]')
    .querySelectorAll(':scope > .card-body > .oe-questionnaire-group-instance')];
const addInstance = (root, linkId) => root.querySelector('[data-link-id="' + linkId + '"]')
    .querySelector(':scope > .card-body > .oe-questionnaire-repeat-add').click();
const pickRadio = (root, linkId, index) => {
    const radio = root.querySelectorAll('[data-link-id="' + linkId + '"] input[type="radio"]')[index];
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

describe('six-level nesting', () => {
    test('level 2 is gated by level 1', () => {
        const { container } = setup();
        expect(visible(container, 'L2')).toBe(false);
        pickRadio(container, 'q1', 0);
        expect(visible(container, 'L2')).toBe(true);
    });

    test('a repeating group inside a repeating group follows its own instance condition', () => {
        const { container } = setup();
        pickRadio(container, 'q1', 0);
        addInstance(container, 'L3');
        expect(instancesOf(container, 'L3')).toHaveLength(2);
        // hidden everywhere until q3 is answered Yes in an instance
        for (const instance of instancesOf(container, 'L3')) {
            expect(visible(instance, 'L5')).toBe(false);
        }
        pickRadio(instancesOf(container, 'L3')[0], 'q3', 0);
        const [first, second] = instancesOf(container, 'L3');
        expect(visible(first, 'L5')).toBe(true);
        expect(visible(second, 'L5')).toBe(false);
    });

    test('a level 6 item follows a level 1 answer five levels up', () => {
        const { container } = setup();
        pickRadio(container, 'q1', 0);
        pickRadio(instancesOf(container, 'L3')[0], 'q3', 0);
        expect(visible(instancesOf(container, 'L3')[0], 'q6b')).toBe(true);
        pickRadio(container, 'q1', 1);
        // q1 = No hides level 2 and everything under it
        expect(visible(container, 'L2')).toBe(false);
    });

    test('the total sums every level 4 count across instances via answer.value', () => {
        const { container, runtime } = setup();
        pickRadio(container, 'q1', 0);
        addInstance(container, 'L3');
        typeInto(instancesOf(container, 'L3')[0], 'q4', 3);
        typeInto(instancesOf(container, 'L3')[1], 'q4', 4);
        expect(container.querySelector('[data-link-id="total"] input').value).toBe('7');
        const level1 = runtime.getQuestionnaireResponse().item.find((item) => item.linkId === 'L1');
        expect(level1.item.find((item) => item.linkId === 'total').answer).toEqual([{ valueInteger: 7 }]);
    });

    test('the level 6 required question is enforced per enabled instance only', () => {
        const { container, runtime } = setup();
        pickRadio(container, 'q1', 0);
        addInstance(container, 'L3');
        // nothing deeper is enabled yet: valid
        expect(runtime.validate().valid).toBe(true);

        pickRadio(instancesOf(container, 'L3')[0], 'q3', 0);
        const level5 = instancesOf(container, 'L3')[0];
        addInstance(level5, 'L5');
        let result = runtime.validate();
        expect(result.valid).toBe(false);
        expect(result.issues.filter((issue) => issue.code === 'required')).toHaveLength(2);

        const level5Instances = instancesOf(instancesOf(container, 'L3')[0], 'L5');
        typeInto(level5Instances[0], 'q6', 1.5);
        typeInto(instancesOf(instancesOf(container, 'L3')[0], 'L5')[1], 'q6', 2.5);
        result = runtime.validate();
        expect(result.valid).toBe(true);
    });
});

describe('FHIRPath choice-type access', () => {
    test('value resolves value[x]; an exact property still wins', () => {
        const evaluator = new runtimeApi.FhirPathEvaluator();
        const parser = new runtimeApi.FhirPathParser();
        const environment = {
            resource: { answer: [{ valueInteger: 2 }, { valueDecimal: 1.5 }, { valueCoding: { code: 'x' } }] },
        };
        expect(evaluator.evaluate(parser.parse('%resource.answer.value'), environment, null)).toEqual([2, 1.5, { code: 'x' }]);
        expect(evaluator.evaluate(parser.parse('%resource.value'), { resource: { value: 'exact', valueString: 'other' } }, null)).toEqual(['exact']);
    });
});
