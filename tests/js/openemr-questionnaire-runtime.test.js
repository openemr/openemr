/**
 * @jest-environment jsdom
 */

/**
 * Regression tests for the OpenEMR FHIR Questionnaire Runtime.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-runtime.test.js
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

beforeEach(() => {
    document.body.innerHTML = '';
    runtimeApi.runtime = null;
});

describe('FHIRPath tokenizer', () => {
    test('tokenizes subtraction without whitespace after a variable', () => {
        const tokenizer = new runtimeApi.FhirPathTokenizer();
        const tokens = tokenizer.tokenize('%total-1');

        expect(tokens.map(({ type, value }) => ({ type, value }))).toEqual([
            { type: '%', value: '%' },
            { type: 'identifier', value: 'total' },
            { type: 'operator', value: '-' },
            { type: 'number', value: 1 },
            { type: 'eof', value: null },
        ]);
    });
});

describe('Questionnaire item rendering', () => {
    test('renders nested sibling questions when a help display child is present', () => {
        const questionnaire = {
            resourceType: 'Questionnaire',
            status: 'active',
            item: [{
                linkId: 'parent',
                type: 'string',
                text: 'Parent question',
                item: [
                    {
                        linkId: 'parent-help',
                        type: 'display',
                        text: 'Helpful information',
                        extension: [{
                            url: runtimeApi.EXTENSIONS.ITEM_CONTROL,
                            valueCodeableConcept: {
                                coding: [{ code: 'help' }],
                            },
                        }],
                    },
                    {
                        linkId: 'child',
                        type: 'string',
                        text: 'Nested child question',
                    },
                ],
            }],
        };

        const container = createContainer();
        runtimeApi.mount({ questionnaire, container });

        expect(container.querySelector('[data-link-id="child"]')).not.toBeNull();
        expect(container.querySelectorAll('input[type="text"]')).toHaveLength(2);
        expect(container.querySelector('.oe-questionnaire-help').textContent).toBe('Helpful information');
    });

    test('only links a question label to an input id that actually exists', () => {
        const questionnaire = {
            resourceType: 'Questionnaire',
            status: 'active',
            item: [
                { linkId: 'boolean-question', type: 'boolean', text: 'Boolean question' },
                { linkId: 'text-question', type: 'string', text: 'Text question' },
            ],
        };

        const container = createContainer();
        runtimeApi.mount({ questionnaire, container });

        const booleanItem = container.querySelector('[data-link-id="boolean-question"]');
        const textItem = container.querySelector('[data-link-id="text-question"]');
        const booleanLabel = booleanItem.querySelector('.oe-questionnaire-label');
        const textLabel = textItem.querySelector('.oe-questionnaire-label');
        const textInput = textItem.querySelector('input[type="text"]');

        expect(booleanLabel.hasAttribute('for')).toBe(false);
        expect(textLabel.htmlFor).toBe(textInput.id);
    });
});

describe('FHIR dateTime handling', () => {
    test('persists a local datetime with an offset and restores it to datetime-local', () => {
        const questionnaire = {
            resourceType: 'Questionnaire',
            status: 'active',
            item: [{
                linkId: 'appointment-time',
                type: 'dateTime',
                text: 'Appointment time',
            }],
        };

        const container = createContainer();
        const runtime = runtimeApi.mount({ questionnaire, container });
        const input = container.querySelector('input[type="datetime-local"]');

        input.value = '2026-07-11T10:30';
        input.dispatchEvent(new Event('change', { bubbles: true }));

        const questionnaireResponse = runtime.getQuestionnaireResponse();
        const savedDateTime = questionnaireResponse.item[0].answer[0].valueDateTime;

        expect(savedDateTime).toMatch(/^2026-07-11T10:30:00[+-]\d{2}:\d{2}$/);

        const editContainer = createContainer();
        runtimeApi.mount({
            questionnaire,
            questionnaireResponse,
            container: editContainer,
        });

        expect(editContainer.querySelector('input[type="datetime-local"]').value)
            .toBe('2026-07-11T10:30');
    });
});
