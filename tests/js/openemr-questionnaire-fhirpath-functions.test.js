/**
 * @jest-environment jsdom
 */

/**
 * FHIRPath 2.0.0 operators and functions beyond the scoped ones (the version
 * FHIR R4 uses), FHIR's extension(), SDC's weight(), and calendar date
 * arithmetic, evaluated by the runtime's own parser and evaluator.
 *
 * Run with: npm run test:js -- tests/js/openemr-questionnaire-fhirpath-functions.test.js
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
const ORDINAL = 'http://hl7.org/fhir/StructureDefinition/ordinalValue';
const ITEM_WEIGHT = 'http://hl7.org/fhir/StructureDefinition/itemWeight';

const questionnaire = {
    resourceType: 'Questionnaire',
    status: 'active',
    item: [
        {
            linkId: 'a', type: 'choice', text: 'A', answerOption: [
                { valueCoding: { system: 'http://loinc.org', code: 'LA6568-5', display: 'Not at all' }, extension: [{ url: ORDINAL, valueDecimal: 0 }] },
                { valueCoding: { system: 'http://loinc.org', code: 'LA6569-3', display: 'Several days' }, extension: [{ url: ITEM_WEIGHT, valueDecimal: 1 }] },
            ],
        },
    ],
};

const response = {
    resourceType: 'QuestionnaireResponse',
    item: [
        { linkId: 'a', answer: [{ valueCoding: { system: 'http://loinc.org', code: 'LA6569-3' } }] },
        { linkId: 'n', answer: [{ valueInteger: 7 }] },
        { linkId: 's', answer: [{ valueString: 'Hello' }] },
        { linkId: 'd', answer: [{ valueDate: '2026-01-31' }] },
        { linkId: 'c', answer: [{ valueCoding: { code: 'x', extension: [{ url: ORDINAL, valueDecimal: 3 }] } }] },
    ],
};

function evaluate(expression) {
    const ast = new runtimeApi.FhirPathParser().parse(expression);
    const environment = { questionnaire, resource: response, rootResource: response, context: response };
    return new runtimeApi.FhirPathEvaluator().evaluate(ast, environment, response);
}

const answer = (linkId, key) => "%resource.item.where(linkId = '" + linkId + "').answer." + key;

describe('operators', () => {
    test.each([
        ['1 + 2 * 3', [7]],
        ['(1 + 2) * 3', [9]],
        ['-2 + 5', [3]],
        ['7 div 2', [3]],
        ['7 mod 2', [1]],
        ['7 div 0', []],
        ["'a' & 'b'", ['ab']],
        ["'a' & {}", ['a']],
        ["'a' + 'b'", ['ab']],
        ['(1 | 2 | 2).count()', [2]],
        ['2 in (1 | 2)', [true]],
        ['(1 | 2) contains 3', [false]],
        ['{} in (1 | 2)', []],
        ['true xor false', [true]],
        ['false implies {}', [true]],
        ['true implies false', [false]],
        ["'Hello  World' ~ 'hello world'", [true]],
        ["'a' !~ 'A'", [false]],
        ['(5 | 6 | 7)[1]', [6]],
        ['(5 | 6)[4]', []],
        ['3 is Integer', [true]],
        ['3.5 is Integer', [false]],
        ["'x' as String", ['x']],
    ])('%s', (expression, expected) => {
        expect(evaluate(expression)).toEqual(expected);
    });

    test('comparison sits above equality: 1 < 2 = true', () => {
        expect(evaluate('1 < 2 = true')).toEqual([true]);
    });
});

describe('functions', () => {
    test.each([
        ['(1 > 2).not()', [true]],
        ['{}.not()', []],
        [answer('s', 'value') + '.length()', [5]],
        [answer('s', 'value') + ".contains('ell')", [true]],
        [answer('s', 'value') + ".matches('^H')", [true]],
        [answer('s', 'value') + '.upper()', ['HELLO']],
        [answer('s', 'value') + '.substring(1, 3)', ['ell']],
        [answer('s', 'value') + ".replace('l', 'L')", ['HeLLo']],
        [answer('n', 'value') + '.toString()', ['7']],
        ["'12'.toInteger() + 1", [13]],
        ["'1.5'.toDecimal() * 2", [3]],
        ["'abc'.convertsToInteger()", [false]],
        ['(2 / 3).round(2)', [0.67]],
        ['(-4).abs()', [4]],
        ['(5 | 9 | 2).max()', [9]],
        ['(1 | 2 | 3).tail()', [2, 3]],
        ['(1 | 2 | 3).take(2)', [1, 2]],
        ['(1 | 2).single()', null],
        ['%resource.item.answer.value.ofType(integer)', [7]],
        ['%resource.item.answer.value.ofType(date)', ['2026-01-31']],
        ['%resource.item.answer.value.ofType(string)', ['Hello']],
        ["%resource.descendants().where(linkId = 'n').answer.value", [7]],
    ])('%s', (expression, expected) => {
        if (expected === null) {
            expect(() => evaluate(expression)).toThrow(/single item/);
            return;
        }
        expect(evaluate(expression)).toEqual(expected);
    });

    test('extension(url) reads an extension by its url', () => {
        expect(evaluate(answer('c', 'value') + ".extension('" + ORDINAL + "').value")).toEqual([3]);
    });

    test('weight() reads a coding\'s own weight, else its answerOption\'s itemWeight or ordinalValue', () => {
        expect(evaluate(answer('c', 'value') + '.weight()')).toEqual([3]);
        expect(evaluate(answer('a', 'value') + '.weight()')).toEqual([1]);
        expect(evaluate(answer('a', 'value') + '.ordinal()')).toEqual([1]);
        expect(evaluate('%resource.item.answer.value.weight().sum()')).toEqual([4]);
    });

    test('memberOf() stays unsupported: it needs a terminology service', () => {
        expect(() => evaluate(answer('a', 'value') + ".memberOf('http://example.org/vs')")).toThrow(/Unsupported FHIRPath method: memberOf/);
    });
});

describe('dates', () => {
    test('today() and now() give FHIR date and dateTime strings', () => {
        expect(evaluate('today()')[0]).toMatch(/^\d{4}-\d{2}-\d{2}$/);
        expect(evaluate('now()')[0]).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/);
        expect(evaluate('now() > @2000-01-01')).toEqual([true]);
    });

    test.each([
        ['@2026-01-31 + 1 month', ['2026-02-28']],
        ['@2024-02-29 + 1 year', ['2025-02-28']],
        ['@2026-03-01 - 1 day', ['2026-02-28']],
        ['@2026-10-23 - 18 years', ['2008-10-23']],
        ["@2026-10-23 + 2 'wk'", ['2026-11-06']],
        ['@2026-10 + 3 months', ['2027-01']],
        ['@2026 + 1 day', []],
        ['@2026-10-23T10:30:00-05:00 + 90 minutes', ['2026-10-23T12:00:00-05:00']],
    ])('%s', (expression, expected) => {
        expect(evaluate(expression)).toEqual(expected);
    });

    test('a date answer compared with a shifted date', () => {
        expect(evaluate(answer('d', 'value') + ' < @2026-03-01 - 1 day')).toEqual([true]);
    });
});

describe('in a questionnaire', () => {
    test('a calculated total by weight() fills in as answers are chosen', () => {
        const container = document.createElement('div');
        document.body.appendChild(container);
        const runtime = runtimeApi.mount({
            container,
            questionnaire: {
                ...questionnaire,
                item: [
                    ...questionnaire.item,
                    {
                        linkId: 'total', type: 'decimal', text: 'Total', readOnly: true,
                        extension: [{
                            url: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-calculatedExpression',
                            valueExpression: { language: 'text/fhirpath', expression: '%resource.repeat(item).answer.value.weight().sum()' },
                        }],
                    },
                ],
            },
        });
        runtime.setAnswers('a', [{ valueKey: 'valueCoding', value: questionnaire.item[0].answerOption[1].valueCoding }]);
        const total = runtime.getQuestionnaireResponse().item.find((item) => item.linkId === 'total');
        expect(total.answer[0]).toEqual({ valueDecimal: 1 });
        expect(runtime.collectWarnings()).toEqual([]);
    });
});
