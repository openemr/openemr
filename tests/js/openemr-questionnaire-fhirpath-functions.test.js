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

// Tests below mount into document.body; start each from an empty page.
beforeEach(() => {
    document.body.innerHTML = '';
});

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

// FHIRPath 2.0.0: and, or, xor and implies give "the empty collection ({ }) otherwise", and =
// with an empty operand "returns an empty collection". Collection equality is item by item, in
// order ("Comparison is order dependent").
describe('empty operands and collections', () => {
    test.each([
        ['true and {}', []],
        ['{} and {}', []],
        ['false and {}', [false]],
        ['{} and false', [false]],
        ['true or {}', [true]],
        ['{} or true', [true]],
        ['false or {}', []],
        ['true xor {}', []],
        ['{} xor false', []],
        ['true implies {}', []],
        ['{} implies true', [true]],
        ['{} implies false', []],
        ['1 = {}', []],
        ['1 != {}', []],
        ['{} = {}', []],
        [answer('missing', 'value') + " != 'x'", []],
        ['(1 | 2) = (1 | 2)', [true]],
        ['(1 | 2) = (2 | 1)', [false]],
        ['(1 | 2) != (2 | 1)', [true]],
        ['(1 | 2) = 1', [false]],
    ])('%s', (expression, expected) => {
        expect(evaluate(expression)).toEqual(expected);
    });

    test('an item gated on != stays hidden until the question it reads is answered', () => {
        const container = document.createElement('div');
        document.body.appendChild(container);
        const runtime = runtimeApi.mount({
            container,
            questionnaire: {
                resourceType: 'Questionnaire',
                status: 'active',
                item: [
                    { linkId: 'smoke', type: 'string', text: 'Do you smoke?' },
                    {
                        linkId: 'advice', type: 'display', text: 'Advice',
                        extension: [{
                            url: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-enableWhenExpression',
                            valueExpression: { language: 'text/fhirpath', expression: "%resource.item.where(linkId = 'smoke').answer.value != 'no'" },
                        }],
                    },
                ],
            },
        });
        expect(runtime.isEnabled('advice')).toBe(false);
        runtime.setAnswers('smoke', [{ valueKey: 'valueString', value: 'yes' }]);
        expect(runtime.isEnabled('advice')).toBe(true);
        runtime.setAnswers('smoke', [{ valueKey: 'valueString', value: 'no' }]);
        expect(runtime.isEnabled('advice')).toBe(false);
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

    // FHIRPath 2.0.0: iif's otherwise-result is optional; a '.' after an integer literal starts a
    // method call; an empty replace() pattern surrounds every character; min() and max() order
    // strings, dates and times as well as numbers.
    test.each([
        ["iif(true, 'y')", ['y']],
        ["iif(false, 'y')", []],
        ["iif({}, 'y')", []],
        ["iif(false, 'y', 'n')", ['n']],
        ['2.power(10)', [1024]],
        ['1.not()', [false]],
        ['100.log(10)', [2]],
        ['2.5.round()', [3]],
        ["'abc'.replace('', 'x')", ['xaxbxcx']],
        ["''.replace('', 'x')", ['x']],
        ["'a$b'.replace('$', '$$')", ['a$$b']],
        ["('b' | 'a' | 'c').min()", ['a']],
        ["('b' | 'a' | 'c').max()", ['c']],
        ['(@2026-03-01 | @2026-01-15).min()', ['2026-01-15']],
        ['(@2026 | @2026-01).max()', []],
        ['%resource.children().count()', [5]],
    ])('%s', (expression, expected) => {
        expect(evaluate(expression)).toEqual(expected);
    });

    test('memberOf() stays unsupported: it needs a terminology service', () => {
        expect(() => evaluate(answer('a', 'value') + ".memberOf('http://example.org/vs')")).toThrow(/Unsupported FHIRPath method: memberOf/);
    });
});

describe('quantities', () => {
    const rate = (value) => ({ resourceType: 'QuestionnaireResponse', item: [{ linkId: 'rr', answer: [{ valueQuantity: { value, unit: '{Breaths}/min', system: 'http://unitsofmeasure.org', code: '{Breaths}/min' } }] }] });
    const evaluateOn = (resource, expression) => new runtimeApi.FhirPathEvaluator().evaluate(new runtimeApi.FhirPathParser().parse(expression), { resource, rootResource: resource, context: resource }, resource);
    const rr = "%resource.item.where(linkId = 'rr').answer.value";

    test.each([
        [70, '>= 60', [true]],
        [40, '>= 60', [false]],
        [60, '= 60', [true]],
        [61, '!= 60', [true]],
    ])('a %s breaths/min answer %s breaths/min', (value, comparison, expected) => {
        const [operator, number] = comparison.split(' ');
        expect(evaluateOn(rate(value), rr + ' ' + operator + ' ' + number + " '{Breaths}/min'")).toEqual(expected);
    });

    test.each([
        [{ value: 1 }, " > 60 '{Breaths}/min'", []],
        [{ value: 70 }, ' > 60', [true]],
        [{ value: 1 }, ' = 1', [true]],
        [{ value: 70, unit: '{Breaths}/min' }, " > 'fast'", []],
    ])('an answer of %j%s (a quantity with no unit is in unit 1)', (quantity, comparison, expected) => {
        const resource = { resourceType: 'QuestionnaireResponse', item: [{ linkId: 'rr', answer: [{ valueQuantity: quantity }] }] };
        expect(evaluateOn(resource, rr + comparison)).toEqual(expected);
    });

    test.each([
        ["5 'mg' > 4 'kg'", []],
        ["5 'mg' = 5 'kg'", []],
        ["2 '1' > 1", [true]],
        ["2 weeks > 10 'd'", []],
        ["(3 'mg' + 2 'mg').value", [5]],
        ["(3 'mg' * 2).value", [6]],
        ["(3 'mg' + 2 'kg')", []],
    ])('%s', (expression, expected) => {
        expect(evaluate(expression)).toEqual(expected);
    });

    // FHIRPath 2.0.0: "calendar durations and definite quantity durations above seconds are
    // considered unequal" (1 year = 1 'a' // false; 1 second = 1 's' // true). A quoted calendar
    // word stays its UCUM unit, which WHO SMART's forms rely on (answers in 'wk' >= 104 'weeks').
    test.each([
        ["4 days = 4 'd'", [false]],
        ["4 days != 4 'd'", [true]],
        ["1 year = 1 'a'", [false]],
        ["1 second = 1 's'", [true]],
        ["4 days < 5 'd'", []],
        ['4 days = 4 days', [true]],
        ['4 days > 3 days', [true]],
        ['2 * 4 days = 8 days', [true]],
        ["2 * 4 days = 8 'd'", [false]],
        ["4 'weeks' = 4 'wk'", [true]],
    ])('%s (calendar durations)', (expression, expected) => {
        expect(evaluate(expression)).toEqual(expected);
    });

    test('an answer in weeks against a quoted weeks literal', () => {
        const resource = { resourceType: 'QuestionnaireResponse', item: [{ linkId: 'eweek', answer: [{ valueQuantity: { value: 110, system: 'http://unitsofmeasure.org', code: 'wk' } }] }] };
        expect(evaluateOn(resource, "%resource.item.where(linkId = 'eweek').answer.value >= 104 'weeks'")).toEqual([true]);
    });

    test('a calculated calendar duration is saved as a plain quantity', () => {
        const container = document.createElement('div');
        document.body.appendChild(container);
        const runtime = runtimeApi.mount({
            container,
            questionnaire: {
                resourceType: 'Questionnaire',
                status: 'active',
                item: [{
                    linkId: 'wait', type: 'quantity', text: 'Wait', readOnly: true,
                    extension: [{
                        url: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-calculatedExpression',
                        valueExpression: { language: 'text/fhirpath', expression: '2 * 3 days' },
                    }],
                }],
            },
        });
        const wait = runtime.getQuestionnaireResponse().item.find((item) => item.linkId === 'wait');
        expect(wait.answer[0].valueQuantity.value).toBe(6);
        expect(JSON.parse(JSON.stringify(wait.answer[0]))).toEqual(wait.answer[0]);
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
        ['@2026-10-23T10:30:00-05:00 + 90 minutes', ['2026-10-23T12:00:00-05:00']],
    ])('%s', (expression, expected) => {
        expect(evaluate(expression)).toEqual(expected);
    });

    // FHIRPath 2.0.0: a duration finer than the value's precision is converted to it, "truncating
    // any resulting decimal portion" (@2014 + 24 months is @2016).
    test.each([
        ['@2026 + 1 day', ['2026']],
        ['@2026 - 1 day', ['2026']],
        ['@2026 + 18 months', ['2027']],
        ['@2026-01 + 45 days', ['2026-02']],
        ['@2026-01-31 + 36 hours', ['2026-02-01']],
        ['@2026-01-31T10:30 + 90 seconds', ['2026-01-31T10:31']],
    ])('%s (partial precision)', (expression, expected) => {
        expect(evaluate(expression)).toEqual(expected);
    });

    // FHIRPath 2.0.0: values compare precision by precision; "If one input has a value for the
    // precision and the other does not, the comparison stops and the values cannot be compared",
    // with seconds and milliseconds one precision, and dateTimes respecting their offsets.
    test.each([
        ['@2026 = @2026-01', []],
        ['@2026 < @2026-01', []],
        ['@2026 = @2027-01', [false]],
        ['@2026 < @2027-01', [true]],
        ['@2026-01-31 = @2026-01-31T00:00:00Z', []],
        ['@2026-01-30 < @2026-01-31T10:00:00Z', [true]],
        ['now() > today()', []],
        ['@T10:30 = @T10:30:00', []],
        ['@T10:30:00 = @T10:30:00.000', [true]],
        ['@2026-01-31T10:30:00Z = @2026-01-31T05:30:00-05:00', [true]],
        ['@2026-01-31T10:30:00Z > @2026-01-31T05:30:00-05:00', [false]],
        ['@T10:30:00.toString()', ['10:30:00']],
        ['@T10:30:00 is Time', [true]],
    ])('%s', (expression, expected) => {
        expect(evaluate(expression)).toEqual(expected);
    });

    test('a date answer compared with a shifted date', () => {
        expect(evaluate(answer('d', 'value') + ' < @2026-03-01 - 1 day')).toEqual([true]);
    });

    test('a time answer compared with a time literal', () => {
        const resource = { resourceType: 'QuestionnaireResponse', item: [{ linkId: 't', answer: [{ valueTime: '10:30:00' }] }] };
        const at = (expression) => new runtimeApi.FhirPathEvaluator().evaluate(new runtimeApi.FhirPathParser().parse(expression), { resource, rootResource: resource, context: resource }, resource);
        expect(at(answer('t', 'value') + ' > @T09:00:00')).toEqual([true]);
        expect(at(answer('t', 'value') + ' = @T10:30:00')).toEqual([true]);
    });
});

describe('in a questionnaire', () => {
    test('%qitem is the Questionnaire item the expression is on', () => {
        const container = document.createElement('div');
        document.body.appendChild(container);
        const runtime = runtimeApi.mount({
            container,
            questionnaire: {
                resourceType: 'Questionnaire', status: 'active',
                item: [{
                    linkId: 'echo', type: 'string', text: 'Echo my own text', readOnly: true,
                    extension: [{
                        url: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-calculatedExpression',
                        valueExpression: { language: 'text/fhirpath', expression: "%qitem.text & ' (' & %qitem.linkId & ')'" },
                    }],
                }],
            },
        });
        const echo = runtime.getQuestionnaireResponse().item.find((item) => item.linkId === 'echo');
        expect(echo.answer).toEqual([{ valueString: 'Echo my own text (echo)' }]);
    });

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
