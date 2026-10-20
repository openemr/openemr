/**
 * @jest-environment jsdom
 */

/**
 * Regression coverage for issue #14736 — LForms-to-native-runtime gaps.
 *
 * Covers: display prefix, entryFormat, regex, readOnly-as-disabled, DOM
 * warning surfacing, answerValueSet (contained/pre-expanded), ordinalValue,
 * the expanded FHIRPath surface (select/all/sort/repeat/repeatAll/aggregate
 * and $this/$index/$total/%context/%rootResource), and repeating groups,
 * including a regression guard that non-repeating responses are unchanged.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-regressions.test.js
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
const testQuestionnaire = JSON.parse(
    fs.readFileSync(path.resolve(__dirname, 'fixtures/TestQuestionnaire.json'), 'utf8')
);

function createContainer() {
    const container = document.createElement('div');
    document.body.appendChild(container);
    return container;
}

function mount(questionnaire, questionnaireResponse) {
    const container = createContainer();
    const runtime = runtimeApi.mount({ questionnaire, questionnaireResponse, container });
    return { container, runtime };
}

function baseQuestionnaire(items, extra = {}) {
    return { resourceType: 'Questionnaire', status: 'active', item: items, ...extra };
}

function setValue(input, value) {
    input.value = value;
    input.dispatchEvent(new Event('change', { bubbles: true }));
}

beforeEach(() => {
    document.body.innerHTML = '';
    runtimeApi.runtime = null;
});

describe('display prefix (minor regression)', () => {
    test('prefix is rendered before the display text', () => {
        const { container } = mount(baseQuestionnaire([
            { linkId: 'd1', type: 'display', prefix: 'Prefix', text: 'Body text' },
        ]));
        const node = container.querySelector('[data-link-id="d1"]');
        expect(node.querySelector('.oe-questionnaire-prefix').textContent).toBe('Prefix');
        expect(node.querySelector('.oe-questionnaire-display-text').textContent).toBe('Body text');
    });
});

describe('entryFormat, regex, readOnly', () => {
    test('entryFormat becomes the input placeholder', () => {
        const { container } = mount(baseQuestionnaire([
            {
                linkId: 'zip', type: 'text', text: 'Zip',
                extension: [{ url: runtimeApi.EXTENSIONS.ENTRY_FORMAT, valueString: 'Example: 12345' }],
            },
        ]));
        expect(container.querySelector('[data-link-id="zip"] textarea').placeholder).toBe('Example: 12345');
    });

    test('regex is applied as a pattern and validated', () => {
        const { container, runtime } = mount(baseQuestionnaire([
            {
                linkId: 'zip', type: 'string', text: 'Zip',
                extension: [{ url: runtimeApi.EXTENSIONS.REGEX, valueString: '^[0-9]{5}$' }],
            },
        ]));
        const input = container.querySelector('[data-link-id="zip"] input');
        expect(input.pattern).toBe('^[0-9]{5}$');
        setValue(input, 'abcde');
        expect(runtime.validate().valid).toBe(false);
        setValue(input, '12345');
        expect(runtime.validate().valid).toBe(true);
    });

    test('readOnly renders as disabled, not readonly', () => {
        const { container } = mount(baseQuestionnaire([
            { linkId: 'ro', type: 'string', text: 'Read only', readOnly: true },
        ]));
        const input = container.querySelector('[data-link-id="ro"] input');
        expect(input.disabled).toBe(true);
        expect(input.readOnly).toBe(false);
    });
});

describe('runtime warnings surfaced in the DOM', () => {
    test('an unresolved answerValueSet renders a visible warning', () => {
        const { container } = mount(baseQuestionnaire([
            { linkId: 'c', type: 'choice', text: 'Choice', answerValueSet: 'https://example.org/external' },
        ]));
        const warnings = container.querySelector('.oe-questionnaire-warnings');
        expect(warnings.classList.contains('d-none')).toBe(false);
        expect(warnings.textContent).toContain('answerValueSet');
    });

    test('no warning panel is shown for a clean questionnaire', () => {
        const { container } = mount(baseQuestionnaire([
            { linkId: 's', type: 'string', text: 'Fine' },
        ]));
        expect(container.querySelector('.oe-questionnaire-warnings').classList.contains('d-none')).toBe(true);
    });
});

describe('answerValueSet (contained, pre-expanded) and ordinalValue', () => {
    function containedQuestionnaire() {
        return baseQuestionnaire(
            [{ linkId: 'picks', type: 'choice', text: 'Pick', answerValueSet: '#vs' }],
            {
                contained: [{
                    id: 'vs', resourceType: 'ValueSet', status: 'active',
                    expansion: {
                        contains: [
                            { display: 'One', extension: [{ url: runtimeApi.EXTENSIONS.ORDINAL_VALUE, valueDecimal: 1 }] },
                            { display: 'Two', extension: [{ url: runtimeApi.EXTENSIONS.ORDINAL_VALUE, valueDecimal: 2 }] },
                            { display: 'Three', extension: [{ url: runtimeApi.EXTENSIONS.ORDINAL_VALUE, valueDecimal: 3 }] },
                        ],
                    },
                }],
            }
        );
    }

    test('choice options are drawn from the contained ValueSet expansion', () => {
        const { container } = mount(containedQuestionnaire());
        const labels = [...container.querySelectorAll('[data-link-id="picks"] .oe-questionnaire-choice-label')]
            .map((label) => label.textContent);
        expect(labels).toEqual(['One', 'Two', 'Three']);
    });

    test('ordinalValue is exposed as data-ordinal on each option', () => {
        const { container } = mount(containedQuestionnaire());
        const ordinals = [...container.querySelectorAll('[data-link-id="picks"] input[type="radio"]')]
            .map((input) => input.dataset.ordinal);
        expect(ordinals).toEqual(['1', '2', '3']);
    });

    test('selecting a contained-value-set option stores its coding answer', () => {
        const { container, runtime } = mount(containedQuestionnaire());
        const second = container.querySelectorAll('[data-link-id="picks"] input[type="radio"]')[1];
        second.checked = true;
        second.dispatchEvent(new Event('change', { bubbles: true }));
        const answer = runtime.getQuestionnaireResponse().item.find((entry) => entry.linkId === 'picks');
        expect(answer.answer[0].valueCoding.display).toBe('Two');
    });
});

describe('FHIRPath engine: tokenizer and new functions', () => {
    const parser = new runtimeApi.FhirPathParser();
    const evaluator = new runtimeApi.FhirPathEvaluator();
    const evalPath = (expr, environment = {}, context = null) =>
        evaluator.evaluate(parser.parse(expr), environment, context);

    test('tokenizes $this, $index, $total special variables', () => {
        const tokens = new runtimeApi.FhirPathTokenizer().tokenize('$this + $index - $total');
        expect(tokens.filter((t) => t.type === 'special').map((t) => t.value)).toEqual(['this', 'index', 'total']);
    });

    test('select projects with $this context', () => {
        expect(evalPath('%xs.select($this * 2)', { xs: [1, 2, 3] })).toEqual([2, 4, 6]);
    });

    test('select exposes $index', () => {
        expect(evalPath('%xs.select($index)', { xs: ['a', 'b', 'c'] })).toEqual([0, 1, 2]);
    });

    test('all returns true only when every item satisfies the predicate', () => {
        expect(evalPath('%xs.all($this > 0)', { xs: [1, 2, 3] })).toEqual([true]);
        expect(evalPath('%xs.all($this > 1)', { xs: [1, 2, 3] })).toEqual([false]);
    });

    test('sort orders a collection naturally and by key', () => {
        expect(evalPath('%xs.sort()', { xs: [3, 1, 2] })).toEqual([1, 2, 3]);
        expect(evalPath('%xs.sort($this)', { xs: [3, 1, 2] })).toEqual([1, 2, 3]);
    });

    test('aggregate folds with $this and $total', () => {
        expect(evalPath('%xs.aggregate($this + $total, 0)', { xs: [1, 2, 3, 4] })).toEqual([10]);
    });

    test('repeat walks a transitive chain', () => {
        const env = { root: [{ v: 1, next: { v: 2, next: { v: 3 } } }] };
        expect(evalPath('%root.repeat(next).count()', env)).toEqual([2]);
    });

    test('repeatAll keeps duplicates that repeat would collapse', () => {
        const env = { root: [{ step: { label: 'x', step: { label: 'x' } } }] };
        expect(evalPath('%root.repeat(step).count()', env)).toEqual([2]);
    });

    test('distinct removes duplicates', () => {
        expect(evalPath('%xs.distinct()', { xs: [1, 1, 2, 2, 3] })).toEqual([1, 2, 3]);
    });

    test('%context and %rootResource resolve from the environment', () => {
        const env = { context: { tag: 'ctx' }, rootResource: { tag: 'root' } };
        expect(evalPath('%context.tag', env)).toEqual(['ctx']);
        expect(evalPath('%rootResource.tag', env)).toEqual(['root']);
    });
});

describe('calculated expression using the new engine end to end', () => {
    test('a calculated total sums repeating answers via sum()', () => {
        const CALC = 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-calculatedExpression';
        const { runtime } = mount(baseQuestionnaire([
            { linkId: 'scores', type: 'integer', text: 'Scores', repeats: true },
            {
                linkId: 'total', type: 'integer', text: 'Total', readOnly: true,
                extension: [{
                    url: CALC,
                    valueExpression: {
                        language: 'text/fhirpath',
                        expression: "%resource.item.where(linkId='scores').answer.valueInteger.sum()",
                    },
                }],
            },
        ]));
        runtime.setAnswers('scores', [
            { valueKey: 'valueInteger', value: 2 },
            { valueKey: 'valueInteger', value: 5 },
        ]);
        const total = runtime.getQuestionnaireResponse().item.find((entry) => entry.linkId === 'total');
        expect(total.answer[0].valueInteger).toBe(7);
    });
});

describe('repeating groups (severe regression)', () => {
    function repeatingQuestionnaire() {
        return baseQuestionnaire([
            {
                linkId: 'g', type: 'group', text: 'Address', repeats: true,
                item: [
                    { linkId: 'zip', type: 'string', text: 'Zip' },
                    { linkId: 'city', type: 'string', text: 'City' },
                ],
            },
        ]);
    }

    function instances(container) {
        return [...container.querySelectorAll('[data-link-id="g"] .oe-questionnaire-group-instance')];
    }

    function childInput(instanceNode, childLinkId) {
        return instanceNode.querySelector('[data-link-id="' + childLinkId + '"] input');
    }

    test('renders one instance and an Add control by default', () => {
        const { container } = mount(repeatingQuestionnaire());
        expect(instances(container)).toHaveLength(1);
        expect(container.querySelector('[data-link-id="g"] .oe-questionnaire-repeat-add')).not.toBeNull();
    });

    test('Add creates an independent instance; answers do not bleed across instances', () => {
        const { container, runtime } = mount(repeatingQuestionnaire());
        setValue(childInput(instances(container)[0], 'zip'), '11111');

        container.querySelector('[data-link-id="g"] .oe-questionnaire-repeat-add').click();
        expect(instances(container)).toHaveLength(2);
        setValue(childInput(instances(container)[1], 'zip'), '22222');

        const groups = runtime.getQuestionnaireResponse().item.filter((entry) => entry.linkId === 'g');
        expect(groups).toHaveLength(2);
        expect(groups[0].item.find((i) => i.linkId === 'zip').answer[0].valueString).toBe('11111');
        expect(groups[1].item.find((i) => i.linkId === 'zip').answer[0].valueString).toBe('22222');
    });

    test('Remove deletes the right instance and reindexes the rest', () => {
        const { container, runtime } = mount(repeatingQuestionnaire());
        setValue(childInput(instances(container)[0], 'zip'), 'A');
        container.querySelector('[data-link-id="g"] .oe-questionnaire-repeat-add').click();
        setValue(childInput(instances(container)[1], 'zip'), 'B');
        container.querySelector('[data-link-id="g"] .oe-questionnaire-repeat-add').click();
        setValue(childInput(instances(container)[2], 'zip'), 'C');

        // remove the middle instance (#2)
        instances(container)[1].querySelector('.oe-questionnaire-repeat-remove').click();
        expect(instances(container)).toHaveLength(2);

        const groups = runtime.getQuestionnaireResponse().item.filter((entry) => entry.linkId === 'g');
        const zips = groups.map((g) => g.item.find((i) => i.linkId === 'zip').answer[0].valueString);
        expect(zips).toEqual(['A', 'C']);
    });

    test('an existing response with multiple instances round-trips on load', () => {
        const response = {
            resourceType: 'QuestionnaireResponse',
            status: 'in-progress',
            item: [
                { linkId: 'g', item: [{ linkId: 'zip', answer: [{ valueString: 'X1' }] }] },
                { linkId: 'g', item: [{ linkId: 'zip', answer: [{ valueString: 'X2' }] }] },
            ],
        };
        const { container } = mount(repeatingQuestionnaire(), response);
        expect(instances(container)).toHaveLength(2);
        expect(childInput(instances(container)[0], 'zip').value).toBe('X1');
        expect(childInput(instances(container)[1], 'zip').value).toBe('X2');
    });
});

describe('regression guard: non-repeating responses are unchanged', () => {
    test('a flat questionnaire produces the same response shape as before', () => {
        const { runtime } = mount(baseQuestionnaire([
            { linkId: 'a', type: 'string', text: 'A' },
            {
                linkId: 'grp', type: 'group', text: 'Group',
                item: [{ linkId: 'b', type: 'integer', text: 'B' }],
            },
        ]));
        runtime.setAnswers('a', [{ valueKey: 'valueString', value: 'hello' }]);
        runtime.setAnswers('b', [{ valueKey: 'valueInteger', value: 3 }]);
        const response = runtime.getQuestionnaireResponse();
        expect(response.item.find((i) => i.linkId === 'a').answer[0].valueString).toBe('hello');
        const group = response.item.find((i) => i.linkId === 'grp');
        expect(group.item.find((i) => i.linkId === 'b').answer[0].valueInteger).toBe(3);
        // group linkId must appear exactly once (not duplicated like a repeating group)
        expect(response.item.filter((i) => i.linkId === 'grp')).toHaveLength(1);
    });
});

describe("Brian's TestQuestionnaire.json fixture", () => {
    test('mounts and resolves every reported regression', () => {
        const { container } = mount(testQuestionnaire);

        // repeating group renders with Add control
        const group = container.querySelector('[data-link-id="groupWithRepeat"]');
        expect(group.querySelector('.oe-questionnaire-repeat-add')).not.toBeNull();

        // entryFormat placeholder on the zip question
        const zip = group.querySelector('[data-link-id="zipCodeQuestion"] textarea')
            || group.querySelector('[data-link-id="zipCodeQuestion"] input');
        expect(zip.placeholder).toBe('Example: 12345');

        // answerValueSet choice resolved from the contained pre-expanded ValueSet
        const choiceLabels = [...group.querySelectorAll('[data-link-id="integerQuestion"] .oe-questionnaire-choice-label')]
            .map((label) => label.textContent);
        expect(choiceLabels).toEqual(['One', 'Two', 'Three']);

        // display prefix rendered
        const display = container.querySelector('[data-link-id="displayWithPrefix"] .oe-questionnaire-prefix');
        expect(display.textContent).toBe('Prefix');

        // no unresolved warnings for this fixture
        expect(container.querySelector('.oe-questionnaire-warnings').classList.contains('d-none')).toBe(true);
    });
});
