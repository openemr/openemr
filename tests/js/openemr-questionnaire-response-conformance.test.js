/**
 * @jest-environment jsdom
 */

/**
 * The QuestionnaireResponse the runtime saves, against R4's rules for it, and
 * responses saved by LHC-Forms (OpenEMR 8.2 and earlier) loading back.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-response-conformance.test.js
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

function mount(items, questionnaireResponse) {
    const container = document.createElement('div');
    document.body.appendChild(container);
    const runtime = runtimeApi.mount({
        container,
        questionnaire: { resourceType: 'Questionnaire', status: 'active', item: items },
        ...(questionnaireResponse ? { questionnaireResponse } : {}),
    });
    return { container, runtime };
}

const responseItem = (runtime, linkId) => runtime.getQuestionnaireResponse().item.find((item) => item.linkId === linkId);

// R4 qrs-1 on every response item: (answer.exists() and item.exists()).not()
function qrs1Violations(items) {
    const found = [];
    for (const item of items ?? []) {
        if ((item.answer ?? []).length > 0 && (item.item ?? []).length > 0) {
            found.push(item.linkId);
        }
        found.push(...qrs1Violations(item.item));
        for (const answer of item.answer ?? []) {
            found.push(...qrs1Violations(answer.item));
        }
    }
    return found;
}

beforeEach(() => {
    document.body.innerHTML = '';
});

describe('a question with children (R4 qrs-1)', () => {
    const items = [{
        linkId: 'smoke', type: 'boolean', text: 'Do you smoke?',
        item: [{ linkId: 'packs', type: 'integer', text: 'Packs a day' }],
    }];

    test('the children of an answered question are saved under its answer', () => {
        const { runtime } = mount(items);
        runtime.setAnswers('smoke', [{ valueKey: 'valueBoolean', value: true }]);
        runtime.setAnswers('packs', [{ valueKey: 'valueInteger', value: 2 }]);
        const smoke = responseItem(runtime, 'smoke');
        expect(smoke.item).toBeUndefined();
        expect(smoke.answer).toEqual([{ valueBoolean: true, item: [{ linkId: 'packs', text: 'Packs a day', answer: [{ valueInteger: 2 }] }] }]);
        expect(qrs1Violations(runtime.getQuestionnaireResponse().item)).toEqual([]);
    });

    test('with no answer of its own, its answered children stay under item', () => {
        const { runtime } = mount(items);
        runtime.setAnswers('packs', [{ valueKey: 'valueInteger', value: 2 }]);
        const smoke = responseItem(runtime, 'smoke');
        expect(smoke.answer).toBeUndefined();
        expect(smoke.item).toEqual([{ linkId: 'packs', text: 'Packs a day', answer: [{ valueInteger: 2 }] }]);
    });

    test('a response saved with the children beside the answer loads, and saves them under it', () => {
        const { runtime } = mount(items, {
            resourceType: 'QuestionnaireResponse', status: 'in-progress',
            item: [{ linkId: 'smoke', answer: [{ valueBoolean: true }], item: [{ linkId: 'packs', answer: [{ valueInteger: 3 }] }] }],
        });
        const smoke = responseItem(runtime, 'smoke');
        expect(smoke.item).toBeUndefined();
        expect(smoke.answer[0].item[0].answer).toEqual([{ valueInteger: 3 }]);
    });

    test('descendants() still finds a child saved under an answer', () => {
        const { runtime } = mount([
            ...items,
            {
                linkId: 'total', type: 'integer', text: 'Packs', readOnly: true,
                extension: [{
                    url: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-calculatedExpression',
                    valueExpression: { language: 'text/fhirpath', expression: "%resource.descendants().where(linkId = 'packs').answer.value" },
                }],
            },
        ]);
        runtime.setAnswers('smoke', [{ valueKey: 'valueBoolean', value: true }]);
        runtime.setAnswers('packs', [{ valueKey: 'valueInteger', value: 2 }]);
        expect(responseItem(runtime, 'total').answer).toEqual([{ valueInteger: 2 }]);
    });
});

describe('responses saved by LHC-Forms', () => {
    // LHC-Forms 33 (OpenEMR 8.2 and earlier) saved a string option as valueCoding { display }.
    const items = [
        {
            linkId: 'pain', type: 'choice', text: 'Any pain?',
            answerOption: [{ valueString: 'Yes' }, { valueString: 'No' }],
        },
        {
            linkId: 'where', type: 'string', text: 'Where?',
            enableWhen: [{ question: 'pain', operator: '=', answerString: 'Yes' }],
        },
    ];
    const saved = (coding) => ({
        resourceType: 'QuestionnaireResponse', status: 'completed',
        item: [{ linkId: 'pain', answer: [{ valueCoding: coding }] }],
    });

    test('a display-only Coding naming a string option loads as that option', () => {
        const { container, runtime } = mount(items, saved({ display: 'Yes' }));
        // The options are Yes, then No.
        const radios = [...container.querySelectorAll('[data-link-id="pain"] input[type="radio"]')];
        expect(radios.map((input) => input.checked)).toEqual([true, false]);
        expect(runtime.isEnabled('where')).toBe(true);
        expect(responseItem(runtime, 'pain').answer).toEqual([{ valueString: 'Yes' }]);
    });

    test('a display-only Coding that names no option is kept as it was', () => {
        const { runtime } = mount(items, saved({ display: 'Sometimes' }));
        expect(responseItem(runtime, 'pain').answer).toEqual([{ valueCoding: { display: 'Sometimes' } }]);
        expect(runtime.isEnabled('where')).toBe(false);
    });

    test('a Coding with a code is never rewritten', () => {
        const { runtime } = mount(items, saved({ code: 'Yes', display: 'Yes' }));
        expect(responseItem(runtime, 'pain').answer).toEqual([{ valueCoding: { code: 'Yes', display: 'Yes' } }]);
    });
});
