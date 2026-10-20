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
});
