/**
 * @jest-environment jsdom
 */

/**
 * QuestionnaireResponse.status as R4 defines it: in-progress while being
 * filled, completed when a host saves a response that passed validation, and
 * amended when a completed response is changed and saved again.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-response-status.test.js
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

const questionnaire = {
    resourceType: 'Questionnaire',
    url: 'http://example.org/Questionnaire/status-test',
    version: '2',
    status: 'active',
    item: [{ linkId: 'q', type: 'string', text: 'Q' }],
};

function mount(questionnaireResponse = null) {
    const container = document.createElement('div');
    document.body.appendChild(container);
    return runtimeApi.mount({ questionnaire, questionnaireResponse, container });
}

const loaded = (status) => ({
    resourceType: 'QuestionnaireResponse',
    status,
    item: [{ linkId: 'q', answer: [{ valueString: 'x' }] }],
});

beforeEach(() => {
    document.body.innerHTML = '';
});

describe('response status', () => {
    test('a new response is in-progress until it is completed', () => {
        const runtime = mount();
        expect(runtime.getQuestionnaireResponse().status).toBe('in-progress');
        expect(runtime.getQuestionnaireResponse({ complete: true }).status).toBe('completed');
    });

    test.each([
        ['in-progress', 'completed'],
        ['completed', 'amended'],
        ['amended', 'amended'],
    ])('a loaded %s response saved as complete becomes %s', (status, expected) => {
        const runtime = mount(loaded(status));
        expect(runtime.getQuestionnaireResponse().status).toBe(status);
        expect(runtime.getQuestionnaireResponse({ complete: true }).status).toBe(expected);
    });

    test('completing keeps the questionnaire canonical with its version', () => {
        const runtime = mount();
        expect(runtime.getQuestionnaireResponse({ complete: true }).questionnaire).toBe('http://example.org/Questionnaire/status-test|2');
    });

    test('change events carry the response as it stands, not completed', () => {
        const runtime = mount();
        const statuses = [];
        runtime.onChange((detail) => statuses.push(detail.questionnaireResponse.status));
        runtime.setAnswers('q', [{ valueKey: 'valueString', value: 'y' }]);
        expect(statuses).toEqual(['in-progress']);
    });
});
