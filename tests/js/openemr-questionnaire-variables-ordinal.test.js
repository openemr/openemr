/**
 * @jest-environment jsdom
 */

/**
 * Follow-up coverage for issue #14736 PR review: FHIRPath variable extensions
 * (including group-scoped variables and %context), the ordinalValue extension
 * surviving into the QuestionnaireResponse, and the help itemControl on a
 * group. Driven in part by the reporter's TestQuestionnaire2.json fixture.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-variables-ordinal.test.js
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

const fs = require('fs');
const path = require('path');

function loadRuntime() {
    const source = fs.readFileSync(
        path.resolve(__dirname, '../../interface/forms/questionnaire_assessments/native/openemr_questionnaire.js'),
        'utf8'
    );
    new Function('window', 'document', 'HTMLElement', 'HTMLInputElement', 'CustomEvent', source)(
        global.window, global.document, global.window.HTMLElement, global.window.HTMLInputElement, global.window.CustomEvent
    );
}

loadRuntime();
const runtimeApi = global.window.OpenEMRQuestionnaire;
const ORDINAL = 'http://hl7.org/fhir/StructureDefinition/ordinalValue';
const VARIABLE = 'http://hl7.org/fhir/StructureDefinition/variable';
const CALC = 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-calculatedExpression';
const testQuestionnaire2 = JSON.parse(
    fs.readFileSync(path.resolve(__dirname, 'fixtures/TestQuestionnaire2.json'), 'utf8')
);

function createContainer() {
    const container = document.createElement('div');
    document.body.appendChild(container);
    return container;
}
function mount(questionnaire) {
    const container = createContainer();
    const runtime = runtimeApi.mount({ questionnaire, container });
    return { container, runtime };
}
function node(container, linkId) {
    return container.querySelector('[data-link-id="' + linkId + '"]');
}
function pickRadio(container, linkId, index) {
    const radios = node(container, linkId).querySelectorAll('input[type="radio"]');
    radios[index].checked = true;
    radios[index].dispatchEvent(new Event('change', { bubbles: true }));
}
function findAnswer(resource, groupLinkId, linkId) {
    const group = resource.item.find((i) => i.linkId === groupLinkId);
    const item = (group.item || []).find((i) => i.linkId === linkId);
    return item ? item.answer && item.answer[0] : undefined;
}

beforeEach(() => {
    document.body.innerHTML = '';
    runtimeApi.runtime = null;
});

describe('FHIRPath variable extension', () => {
    test('a group-scoped constant variable is in scope for a child calculatedExpression', () => {
        const { runtime } = mount({
            resourceType: 'Questionnaire', status: 'active',
            item: [{
                linkId: 'g', type: 'group', text: 'G',
                extension: [{ url: VARIABLE, valueExpression: { name: 'v', language: 'text/fhirpath', expression: '5' } }],
                item: [{
                    linkId: 'out', type: 'integer', text: 'Out', readOnly: true,
                    extension: [{ url: CALC, valueExpression: { language: 'text/fhirpath', expression: '%v' } }],
                }],
            }],
        });
        expect(findAnswer(runtime.getQuestionnaireResponse(), 'g', 'out').valueInteger).toBe(5);
    });

    test('a group variable using %context aggregates answers within that group', () => {
        const { container, runtime } = mount({
            resourceType: 'Questionnaire', status: 'active',
            item: [{
                linkId: 'g', type: 'group', text: 'G',
                extension: [{
                    url: VARIABLE,
                    valueExpression: {
                        name: 'total', language: 'text/fhirpath',
                        expression: "%context.item.where(linkId='a' or linkId='b').answer.valueInteger.sum()",
                    },
                }],
                item: [
                    { linkId: 'a', type: 'integer', text: 'A' },
                    { linkId: 'b', type: 'integer', text: 'B' },
                    {
                        linkId: 'sum', type: 'integer', text: 'Sum', readOnly: true,
                        extension: [{ url: CALC, valueExpression: { language: 'text/fhirpath', expression: '%total' } }],
                    },
                ],
            }],
        });
        runtime.setAnswers('a', [{ valueKey: 'valueInteger', value: 4 }]);
        runtime.setAnswers('b', [{ valueKey: 'valueInteger', value: 6 }]);
        // %context is the group node, so only a+b are summed (sum item has no answer yet)
        expect(findAnswer(runtime.getQuestionnaireResponse(), 'g', 'sum').valueInteger).toBe(10);
        void container;
    });
});

describe('ordinalValue survives into the QuestionnaireResponse', () => {
    test('a coding answer from a contained ValueSet carries ordinalValue inside the coding', () => {
        const { container, runtime } = mount({
            resourceType: 'Questionnaire', status: 'active',
            contained: [{
                id: 'vs', resourceType: 'ValueSet', status: 'active',
                expansion: { contains: [
                    { display: 'One', extension: [{ url: ORDINAL, valueDecimal: 1 }] },
                    { display: 'Two', extension: [{ url: ORDINAL, valueDecimal: 2 }] },
                ] },
            }],
            item: [{ linkId: 'pick', type: 'choice', text: 'Pick', answerValueSet: '#vs' }],
        });
        pickRadio(container, 'pick', 1);
        const answer = runtime.getQuestionnaireResponse().item.find((i) => i.linkId === 'pick').answer[0];
        const ord = answer.valueCoding.extension.find((e) => e.url === ORDINAL);
        expect(ord.valueDecimal).toBe(2);
    });

    test('an ordinal-bearing valueString option becomes a scored Coding so it counts in select(valueCoding)', () => {
        const { container, runtime } = mount({
            resourceType: 'Questionnaire', status: 'active',
            item: [{
                linkId: 'pick', type: 'choice', text: 'Pick',
                answerOption: [
                    { valueString: 'Four', extension: [{ url: ORDINAL, valueDecimal: 4 }] },
                    { valueString: 'Six', extension: [{ url: ORDINAL, valueDecimal: 6 }] },
                ],
            }],
        });
        pickRadio(container, 'pick', 1);
        const answer = runtime.getQuestionnaireResponse().item.find((i) => i.linkId === 'pick').answer[0];
        expect(answer.valueCoding.display).toBe('Six');
        expect(answer.valueCoding.extension.find((e) => e.url === ORDINAL).valueDecimal).toBe(6);
    });

    test('a plain valueString option (no ordinalValue) stays a valueString answer', () => {
        const { container, runtime } = mount({
            resourceType: 'Questionnaire', status: 'active',
            item: [{
                linkId: 'pick', type: 'choice', text: 'Pick',
                answerOption: [{ valueString: 'Yes' }, { valueString: 'No' }],
            }],
        });
        pickRadio(container, 'pick', 0);
        const answer = runtime.getQuestionnaireResponse().item.find((i) => i.linkId === 'pick').answer[0];
        expect(answer.valueString).toBe('Yes');
    });
});

describe('help itemControl on a group', () => {
    test('a group help child renders a help toggle button, not an inline hidden block', () => {
        const { container } = mount({
            resourceType: 'Questionnaire', status: 'active',
            item: [{
                linkId: 'g', type: 'group', text: 'G',
                item: [
                    {
                        linkId: 'g_help', type: 'display', text: 'Helpful',
                        extension: [{ url: runtimeApi.EXTENSIONS.ITEM_CONTROL, valueCodeableConcept: { coding: [{ code: 'help' }] } }],
                    },
                    { linkId: 'q', type: 'string', text: 'Q' },
                ],
            }],
        });
        const groupNode = node(container, 'g');
        expect(groupNode.querySelector('.oe-questionnaire-help-button')).not.toBeNull();
        // help content opens in a popup only when the button is pushed
        expect(container.querySelector('.oe-questionnaire-popup')).toBeNull();
        groupNode.querySelector('.oe-questionnaire-help-button').click();
        expect(container.querySelector('.oe-questionnaire-popup.oe-questionnaire-help').textContent).toContain('Helpful');
    });
});

describe("reporter's TestQuestionnaire2.json", () => {
    test('variable resolves, ordinalValue survives, help renders, sums agree', () => {
        const { container, runtime } = mount(testQuestionnaire2);
        // group variable constant -> valueOfVariable = 5
        expect(findAnswer(runtime.getQuestionnaireResponse(), 'variableGroup', 'valueOfVariable').valueInteger).toBe(5);
        // help button present on variableGroup
        expect(node(container, 'variableGroup').querySelector('.oe-questionnaire-help-button')).not.toBeNull();
        // choose One (1), Three (3), and the valueString option Six (6)
        pickRadio(container, 'firstInteger', 0);
        pickRadio(container, 'secondInteger', 2);
        pickRadio(container, 'thirdInteger', 2);
        const resource = runtime.getQuestionnaireResponse();
        // ordinalValue present on a coding answer
        const first = findAnswer(resource, 'ordinalValueGroup', 'firstInteger');
        expect(first.valueCoding.extension.find((e) => e.url === ORDINAL).valueDecimal).toBe(1);
        // the valueString option Six is scored as a Coding so it participates
        const third = findAnswer(resource, 'ordinalValueGroup', 'thirdInteger');
        expect(third.valueCoding.display).toBe('Six');
        // the two calculation methods agree (both select(valueCoding): 1 + 3 + 6 = 10)
        const theSum = findAnswer(resource, 'ordinalValueGroup', 'theSum').valueInteger;
        const alt = findAnswer(resource, 'ordinalValueGroup', 'alternateSum').valueInteger;
        expect(theSum).toBe(alt);
        expect(theSum).toBe(10);
    });
});
