/**
 * @jest-environment jsdom
 */

/**
 * Per-instance semantics for repeating groups (issue #14736 follow-through):
 * enableWhen inside a repeating group is evaluated independently per instance,
 * and nested repeating groups round-trip with independent per-instance answers.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-repeat-instances.test.js
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

function createContainer() {
    const container = document.createElement('div');
    document.body.appendChild(container);
    return container;
}

function mount(items) {
    const container = createContainer();
    const runtime = runtimeApi.mount({
        questionnaire: { resourceType: 'Questionnaire', status: 'active', item: items },
        container,
    });
    return { container, runtime };
}

function directInstances(groupNode) {
    return [...groupNode.querySelectorAll(':scope > .card-body > .oe-questionnaire-group-instance')];
}

beforeEach(() => {
    document.body.innerHTML = '';
    runtimeApi.runtime = null;
});

describe('per-instance enableWhen', () => {
    function repeatingWithDependency() {
        return mount([
            {
                linkId: 'g', type: 'group', text: 'Entry', repeats: true,
                item: [
                    { linkId: 'trig', type: 'boolean', text: 'Add detail?' },
                    {
                        linkId: 'dep', type: 'string', text: 'Detail',
                        enableWhen: [{ question: 'trig', operator: '=', answerBoolean: true }],
                    },
                ],
            },
        ]);
    }

    test('enabling the trigger in one instance does not enable the dependent in another', () => {
        const { container } = repeatingWithDependency();
        const groupNode = container.querySelector('[data-link-id="g"]');

        // start with 2 instances
        groupNode.querySelector(':scope > .card-body > .oe-questionnaire-repeat-add').click();
        let inst = directInstances(groupNode);
        expect(inst).toHaveLength(2);

        // both dependents hidden initially
        expect(inst[0].querySelector('[data-link-id="dep"]').classList.contains('d-none')).toBe(true);
        expect(inst[1].querySelector('[data-link-id="dep"]').classList.contains('d-none')).toBe(true);

        // turn the trigger on in instance 0 only (Yes radio)
        const yes = inst[0].querySelectorAll('[data-link-id="trig"] input[type="radio"]')[0];
        yes.checked = true;
        yes.dispatchEvent(new Event('change', { bubbles: true }));

        inst = directInstances(groupNode);
        expect(inst[0].querySelector('[data-link-id="dep"]').classList.contains('d-none')).toBe(false);
        expect(inst[1].querySelector('[data-link-id="dep"]').classList.contains('d-none')).toBe(true);
    });

    test('a per-instance disabled dependent is excluded from that instance of the response', () => {
        const { container, runtime } = repeatingWithDependency();
        const groupNode = container.querySelector('[data-link-id="g"]');
        groupNode.querySelector(':scope > .card-body > .oe-questionnaire-repeat-add').click();
        const inst = directInstances(groupNode);

        // instance 0: trigger on, fill dependent
        const yes0 = inst[0].querySelectorAll('[data-link-id="trig"] input[type="radio"]')[0];
        yes0.checked = true;
        yes0.dispatchEvent(new Event('change', { bubbles: true }));
        const dep0 = inst[0].querySelector('[data-link-id="dep"] input');
        dep0.value = 'kept';
        dep0.dispatchEvent(new Event('change', { bubbles: true }));

        const groups = runtime.getQuestionnaireResponse().item.filter((entry) => entry.linkId === 'g');
        expect(groups).toHaveLength(2);
        // instance 0 keeps its dependent answer
        expect(groups[0].item.find((i) => i.linkId === 'dep').answer[0].valueString).toBe('kept');
        // instance 1 has no dependent (disabled there)
        expect((groups[1].item || []).some((i) => i.linkId === 'dep')).toBe(false);
    });
});

describe('nested repeating groups', () => {
    function nested() {
        return mount([
            {
                linkId: 'outer', type: 'group', text: 'Outer', repeats: true,
                item: [
                    { linkId: 'outerName', type: 'string', text: 'Name' },
                    {
                        linkId: 'inner', type: 'group', text: 'Inner', repeats: true,
                        item: [{ linkId: 'innerVal', type: 'string', text: 'Value' }],
                    },
                ],
            },
        ]);
    }

    test('inner instances are independent within each outer instance and round-trip', () => {
        const { container, runtime } = nested();
        const outerNode = container.querySelector('[data-link-id="outer"]');

        // add a second outer instance
        outerNode.querySelector(':scope > .card-body > .oe-questionnaire-repeat-add').click();
        const outerInstances = directInstances(outerNode);
        expect(outerInstances).toHaveLength(2);

        // in outer instance 0, add a second inner instance
        const inner0 = outerInstances[0].querySelector('[data-link-id="inner"]');
        inner0.querySelector(':scope > .card-body > .oe-questionnaire-repeat-add').click();
        const inner0Instances = directInstances(inner0);
        expect(inner0Instances).toHaveLength(2);

        // fill values
        const setVal = (instanceNode, value) => {
            const input = instanceNode.querySelector('[data-link-id="innerVal"] input');
            input.value = value;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        };
        setVal(inner0Instances[0], 'a');
        setVal(inner0Instances[1], 'b');

        const inner1 = directInstances(outerNode)[1].querySelector('[data-link-id="inner"]');
        setVal(directInstances(inner1)[0], 'c');

        const outers = runtime.getQuestionnaireResponse().item.filter((entry) => entry.linkId === 'outer');
        expect(outers).toHaveLength(2);

        const innerValues = (outerItem) => outerItem.item
            .filter((i) => i.linkId === 'inner')
            .map((innerItem) => innerItem.item.find((c) => c.linkId === 'innerVal').answer[0].valueString);

        expect(innerValues(outers[0])).toEqual(['a', 'b']);
        expect(innerValues(outers[1])).toEqual(['c']);
    });

    test('removing an outer instance preserves nested inner counts and answers', () => {
        const { container, runtime } = nested();
        const outerNode = container.querySelector('[data-link-id="outer"]');
        outerNode.querySelector(':scope > .card-body > .oe-questionnaire-repeat-add').click();

        const setVal = (instanceNode, value) => {
            const input = instanceNode.querySelector('[data-link-id="innerVal"] input');
            input.value = value;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        };

        // outer 1 gets two inner instances
        const inner1 = directInstances(outerNode)[1].querySelector('[data-link-id="inner"]');
        inner1.querySelector(':scope > .card-body > .oe-questionnaire-repeat-add').click();
        const inner1Instances = directInstances(directInstances(outerNode)[1].querySelector('[data-link-id="inner"]'));
        setVal(inner1Instances[0], 'x');
        setVal(inner1Instances[1], 'y');

        // remove outer instance 0
        directInstances(outerNode)[0].querySelector('.oe-questionnaire-repeat-remove').click();

        const outers = runtime.getQuestionnaireResponse().item.filter((entry) => entry.linkId === 'outer');
        expect(outers).toHaveLength(1);
        const innerValues = outers[0].item
            .filter((i) => i.linkId === 'inner')
            .map((innerItem) => innerItem.item.find((c) => c.linkId === 'innerVal').answer[0].valueString);
        // both inner answers survive the outer removal (nested counts shifted)
        expect(innerValues).toEqual(['x', 'y']);
    });

    test('enableWhen referencing an outer-scope answer resolves across nesting', () => {
        const { container } = mount([
            {
                linkId: 'outer', type: 'group', text: 'Outer', repeats: true,
                item: [
                    { linkId: 'outerFlag', type: 'boolean', text: 'Outer flag' },
                    {
                        linkId: 'inner', type: 'group', text: 'Inner', repeats: true,
                        item: [{
                            linkId: 'dep', type: 'string', text: 'Dep',
                            enableWhen: [{ question: 'outerFlag', operator: '=', answerBoolean: true }],
                        }],
                    },
                ],
            },
        ]);
        const outerNode = container.querySelector('[data-link-id="outer"]');
        const outer0 = directInstances(outerNode)[0];
        const inner0 = outer0.querySelector('[data-link-id="inner"]');
        const dep = directInstances(inner0)[0].querySelector('[data-link-id="dep"]');

        // dep depends on the outer-scope flag, initially off -> hidden
        expect(dep.classList.contains('d-none')).toBe(true);

        const yes = outer0.querySelectorAll('[data-link-id="outerFlag"] input[type="radio"]')[0];
        yes.checked = true;
        yes.dispatchEvent(new Event('change', { bubbles: true }));

        const depAfter = directInstances(container.querySelector('[data-link-id="outer"] [data-link-id="inner"]'))[0]
            .querySelector('[data-link-id="dep"]');
        expect(depAfter.classList.contains('d-none')).toBe(false);
    });
});

describe('per-instance validation', () => {
    test('a required child of a repeating group validates against its own instance answer', () => {
        const { container, runtime } = mount([
            {
                linkId: 'g', type: 'group', text: 'G', repeats: true,
                item: [{ linkId: 'req', type: 'string', text: 'Required', required: true }],
            },
        ]);
        // empty required child -> invalid
        expect(runtime.validate().valid).toBe(false);
        // fill the instance-0 field -> valid (canonical key stays empty; proves per-instance lookup)
        const input = container.querySelector('[data-link-id="g"] .oe-questionnaire-group-instance [data-link-id="req"] input');
        input.value = 'filled';
        input.dispatchEvent(new Event('change', { bubbles: true }));
        expect(runtime.validate().valid).toBe(true);
    });

    test('a regex child of a repeating group is validated per instance', () => {
        const { container, runtime } = mount([
            {
                linkId: 'g', type: 'group', text: 'G', repeats: true,
                item: [{
                    linkId: 'zip', type: 'string', text: 'Zip',
                    extension: [{ url: runtimeApi.EXTENSIONS.REGEX, valueString: '^[0-9]{5}$' }],
                }],
            },
        ]);
        const input = container.querySelector('[data-link-id="g"] .oe-questionnaire-group-instance [data-link-id="zip"] input');
        input.value = 'abc';
        input.dispatchEvent(new Event('change', { bubbles: true }));
        const result = runtime.validate();
        expect(result.valid).toBe(false);
        expect(result.issues.some((i) => i.code === 'regex')).toBe(true);
        input.value = '12345';
        input.dispatchEvent(new Event('change', { bubbles: true }));
        expect(runtime.validate().valid).toBe(true);
    });
});

describe('warning accumulation', () => {
    test('a failing expression does not grow the warning panel across edits', () => {
        const CALC = 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-calculatedExpression';
        const { container, runtime } = mount([
            { linkId: 'a', type: 'string', text: 'A' },
            {
                linkId: 'bad', type: 'integer', text: 'Bad', readOnly: true,
                extension: [{ url: CALC, valueExpression: { language: 'text/fhirpath', expression: 'this is ! broken (' } }],
            },
        ]);
        const countWarnings = () => container.querySelectorAll('.oe-questionnaire-warnings li').length;
        const first = countWarnings();
        for (let i = 0; i < 5; i++) {
            runtime.setAnswers('a', [{ valueKey: 'valueString', value: 'x' + i }]);
        }
        expect(countWarnings()).toBe(first);
    });
});

describe('CodeRabbit rev2 fixes', () => {
    const CALC = 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-calculatedExpression';

    test('enableWhenExpression gates an item inside a repeating group', () => {
        const { container, runtime } = mount([
            { linkId: 'flag', type: 'boolean', text: 'Flag' },
            {
                linkId: 'g', type: 'group', text: 'G', repeats: true,
                item: [{
                    linkId: 'dep', type: 'string', text: 'Dep',
                    extension: [{
                        url: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-enableWhenExpression',
                        valueExpression: { language: 'text/fhirpath', expression: "%resource.item.where(linkId='flag').answer.exists()" },
                    }],
                }],
            },
        ]);
        const dep = () => container.querySelector('[data-link-id="g"] .oe-questionnaire-group-instance [data-link-id="dep"]');
        expect(dep().classList.contains('d-none')).toBe(true);
        runtime.setAnswers('flag', [{ valueKey: 'valueBoolean', value: true }]);
        expect(dep().classList.contains('d-none')).toBe(false);
    });

    test('a newly added repeat instance receives item.initial values', () => {
        const { container, runtime } = mount([
            {
                linkId: 'g', type: 'group', text: 'G', repeats: true,
                item: [{ linkId: 'seed', type: 'string', text: 'Seed', initial: [{ valueString: 'default' }] }],
            },
        ]);
        container.querySelector('[data-link-id="g"] .oe-questionnaire-repeat-add').click();
        const groups = runtime.getQuestionnaireResponse().item.filter((entry) => entry.linkId === 'g');
        expect(groups).toHaveLength(2);
        // both the original and the added instance carry the initial value
        expect(groups[0].item.find((i) => i.linkId === 'seed').answer[0].valueString).toBe('default');
        expect(groups[1].item.find((i) => i.linkId === 'seed').answer[0].valueString).toBe('default');
    });

    test('a calculatedExpression inside a repeating group evaluates per instance', () => {
        const { container, runtime } = mount([
            {
                linkId: 'g', type: 'group', text: 'G', repeats: true,
                item: [
                    { linkId: 'x', type: 'integer', text: 'X' },
                    {
                        linkId: 'calc', type: 'integer', text: 'Calc', readOnly: true,
                        extension: [{
                            url: CALC,
                            valueExpression: {
                                language: 'text/fhirpath',
                                // %context is this instance, so each instance's calc
                                // uses only its own x.
                                expression: "%context.item.where(linkId='x').answer.valueInteger * 10",
                            },
                        }],
                    },
                ],
            },
        ]);
        const groupNode = container.querySelector('[data-link-id="g"]');
        groupNode.querySelector(':scope > .card-body > .oe-questionnaire-repeat-add').click();
        const inst = directInstances(groupNode);
        expect(inst).toHaveLength(2);

        const setX = (instanceNode, value) => {
            const input = instanceNode.querySelector('[data-link-id="x"] input');
            input.value = String(value);
            input.dispatchEvent(new Event('change', { bubbles: true }));
        };
        setX(inst[0], 2);
        setX(inst[1], 5);

        // the no-longer-applicable "unsupported in a repeating group" warning is gone
        const warnings = container.querySelector('.oe-questionnaire-warnings');
        expect(warnings ? warnings.textContent : '').not.toContain('repeating group');

        // each instance's calc is its own x * 10, both in the DOM and the response
        const calcInput = (instanceNode) => instanceNode.querySelector('[data-link-id="calc"] input');
        expect(calcInput(directInstances(groupNode)[0]).value).toBe('20');
        expect(calcInput(directInstances(groupNode)[1]).value).toBe('50');

        const groups = runtime.getQuestionnaireResponse().item.filter((entry) => entry.linkId === 'g');
        expect(groups).toHaveLength(2);
        const calcOf = (groupItem) => groupItem.item.find((i) => i.linkId === 'calc').answer[0].valueInteger;
        expect(calcOf(groups[0])).toBe(20);
        expect(calcOf(groups[1])).toBe(50);
    });

    test('an initialExpression inside a repeating group seeds each instance', () => {
        const INITIAL = 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-initialExpression';
        // Two empty instances present at load: initialExpression is seeded once,
        // after enablement, so every instance that exists at mount is seeded.
        const container = createContainer();
        const runtime = runtimeApi.mount({
            questionnaire: {
                resourceType: 'Questionnaire', status: 'active',
                item: [{
                    linkId: 'g', type: 'group', text: 'G', repeats: true,
                    item: [{
                        linkId: 'seeded', type: 'string', text: 'Seeded',
                        extension: [{
                            url: INITIAL,
                            valueExpression: { language: 'text/fhirpath', expression: "'from-expression'" },
                        }],
                    }],
                }],
            },
            questionnaireResponse: {
                resourceType: 'QuestionnaireResponse', status: 'in-progress',
                item: [{ linkId: 'g', item: [] }, { linkId: 'g', item: [] }],
            },
            container,
        });
        const groups = runtime.getQuestionnaireResponse().item.filter((entry) => entry.linkId === 'g');
        expect(groups).toHaveLength(2);
        expect(groups[0].item.find((i) => i.linkId === 'seeded').answer[0].valueString).toBe('from-expression');
        expect(groups[1].item.find((i) => i.linkId === 'seeded').answer[0].valueString).toBe('from-expression');
    });
});
