/*
 * OpenEMR native FHIR R4 / SDC Questionnaire runtime.
 *
 * @package OpenEMR
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
(function (global) {
    'use strict';

    const NATIVE_CONTROL_FORM_ID = 'oe-questionnaire-native-controls';

    const EXTENSIONS = Object.freeze({
        ITEM_CONTROL: 'http://hl7.org/fhir/StructureDefinition/questionnaire-itemControl',
        ORDINAL_VALUE: 'http://hl7.org/fhir/StructureDefinition/ordinalValue',
        QUESTIONNAIRE_UNIT: 'http://hl7.org/fhir/StructureDefinition/questionnaire-unit',
        VARIABLE: 'http://hl7.org/fhir/StructureDefinition/variable',
        CALCULATED_EXPRESSION: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-calculatedExpression',
    });

    function deepClone(value) {
        return value === undefined ? undefined : JSON.parse(JSON.stringify(value));
    }

    function asCollection(value) {
        if (value === undefined || value === null) {
            return [];
        }
        return Array.isArray(value) ? value : [value];
    }

    function firstValue(value) {
        const collection = asCollection(value);
        return collection.length > 0 ? collection[0] : undefined;
    }

    function collectionBoolean(value) {
        const collection = asCollection(value);
        if (collection.length === 0) {
            return false;
        }
        return Boolean(collection[0]);
    }

    function equalPrimitive(left, right) {
        if (left && typeof left === 'object' && right && typeof right === 'object') {
            if ('code' in left || 'code' in right) {
                if ((left.code ?? null) !== (right.code ?? null)) {
                    return false;
                }
                const leftSystem = left.system ?? '';
                const rightSystem = right.system ?? '';
                return leftSystem === '' || rightSystem === '' || leftSystem === rightSystem;
            }
            return JSON.stringify(left) === JSON.stringify(right);
        }
        return left === right;
    }

    function collectionsEqual(left, right) {
        const leftCollection = asCollection(left);
        const rightCollection = asCollection(right);
        return leftCollection.some((leftValue) => rightCollection.some((rightValue) => equalPrimitive(leftValue, rightValue)));
    }

    function safeId(value) {
        let hash = 2166136261;
        const source = String(value ?? '');
        for (let index = 0; index < source.length; index++) {
            hash ^= source.charCodeAt(index);
            hash = Math.imul(hash, 16777619);
        }
        return 'oeq-' + (hash >>> 0).toString(36);
    }

    function findExtension(resource, url) {
        return (resource?.extension ?? []).find((extension) => extension?.url === url) ?? null;
    }

    function findExtensions(resource, url) {
        return (resource?.extension ?? []).filter((extension) => extension?.url === url);
    }

    function getItemControl(item) {
        const extension = findExtension(item, EXTENSIONS.ITEM_CONTROL);
        const coding = extension?.valueCodeableConcept?.coding ?? [];
        return coding.find((entry) => typeof entry?.code === 'string')?.code ?? '';
    }

    function getQuestionnaireUnit(item) {
        const extension = findExtension(item, EXTENSIONS.QUESTIONNAIRE_UNIT);
        return extension?.valueCoding?.display
            ?? extension?.valueCoding?.code
            ?? '';
    }

    function getAnswerOptionValue(answerOption) {
        if (!answerOption || typeof answerOption !== 'object') {
            return null;
        }
        const key = Object.keys(answerOption).find((candidate) => candidate.startsWith('value'));
        if (!key) {
            return null;
        }
        return {
            valueKey: key,
            value: deepClone(answerOption[key]),
        };
    }

    function getAnswerValue(answer) {
        if (!answer || typeof answer !== 'object') {
            return null;
        }
        const key = Object.keys(answer).find((candidate) => candidate.startsWith('value'));
        if (!key) {
            return null;
        }
        return {
            valueKey: key,
            value: deepClone(answer[key]),
        };
    }

    function answerDisplay(answer) {
        const value = answer?.value;
        if (value === undefined || value === null) {
            return '';
        }
        if (typeof value === 'object') {
            if ('display' in value) {
                return String(value.display ?? value.code ?? '');
            }
            if ('value' in value) {
                return [value.value, value.unit ?? value.code ?? ''].filter(Boolean).join(' ');
            }
            return JSON.stringify(value);
        }
        return String(value);
    }

    function padDateTimePart(value) {
        return String(value).padStart(2, '0');
    }

    function formatDateTimeLocalValue(value) {
        if (typeof value !== 'string' || value === '') {
            return '';
        }

        const hasTimezone = /T\d{2}:\d{2}(?::\d{2}(?:\.\d+)?)?(?:Z|[+-]\d{2}:\d{2})$/.test(value);
        if (!hasTimezone) {
            return value.slice(0, 16);
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return value.slice(0, 16);
        }

        return [
            date.getFullYear(),
            '-',
            padDateTimePart(date.getMonth() + 1),
            '-',
            padDateTimePart(date.getDate()),
            'T',
            padDateTimePart(date.getHours()),
            ':',
            padDateTimePart(date.getMinutes()),
        ].join('');
    }

    function toFhirDateTime(value) {
        if (typeof value !== 'string' || value === '') {
            return '';
        }

        const match = /^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2})(?::(\d{2})(?:\.(\d{1,3}))?)?$/.exec(value);
        if (!match) {
            return value;
        }

        const [, year, month, day, hour, minute, second = '00', fraction] = match;
        const localDate = new Date(
            Number(year),
            Number(month) - 1,
            Number(day),
            Number(hour),
            Number(minute),
            Number(second),
            fraction ? Number(fraction.padEnd(3, '0')) : 0
        );
        const offsetMinutes = -localDate.getTimezoneOffset();
        const offsetSign = offsetMinutes >= 0 ? '+' : '-';
        const absoluteOffset = Math.abs(offsetMinutes);
        const offsetHours = padDateTimePart(Math.floor(absoluteOffset / 60));
        const offsetRemainder = padDateTimePart(absoluteOffset % 60);
        const fractionalSeconds = fraction ? '.' + fraction : '';

        return `${year}-${month}-${day}T${hour}:${minute}:${second}${fractionalSeconds}${offsetSign}${offsetHours}:${offsetRemainder}`;
    }

    class QuestionnaireCompiler {
        compile(questionnaire) {
            if (!questionnaire || questionnaire.resourceType !== 'Questionnaire') {
                throw new Error('A FHIR R4 Questionnaire resource is required.');
            }

            const compiled = {
                questionnaire,
                items: [],
                itemsByLinkId: new Map(),
                parentByLinkId: new Map(),
                childrenByLinkId: new Map(),
                enableDependents: new Map(),
                calculatedItems: [],
                warnings: [],
            };

            const walk = (items, parentLinkId = null, depth = 0) => {
                for (const item of items ?? []) {
                    const linkId = typeof item?.linkId === 'string' ? item.linkId : '';
                    if (linkId === '') {
                        compiled.warnings.push({
                            code: 'missing-link-id',
                            message: 'Questionnaire item is missing linkId.',
                        });
                        continue;
                    }

                    if (compiled.itemsByLinkId.has(linkId)) {
                        compiled.warnings.push({
                            code: 'duplicate-link-id',
                            linkId,
                            message: 'Duplicate Questionnaire linkId encountered.',
                        });
                    }

                    const compiledItem = {
                        item,
                        linkId,
                        parentLinkId,
                        depth,
                        itemControl: getItemControl(item),
                        calculatedExpression: findExtension(item, EXTENSIONS.CALCULATED_EXPRESSION)?.valueExpression ?? null,
                        variables: findExtensions(item, EXTENSIONS.VARIABLE)
                            .map((extension) => extension?.valueExpression)
                            .filter((expression) => expression && typeof expression.name === 'string'),
                    };

                    compiled.items.push(compiledItem);
                    compiled.itemsByLinkId.set(linkId, compiledItem);
                    compiled.parentByLinkId.set(linkId, parentLinkId);
                    if (!compiled.childrenByLinkId.has(parentLinkId)) {
                        compiled.childrenByLinkId.set(parentLinkId, []);
                    }
                    compiled.childrenByLinkId.get(parentLinkId).push(linkId);

                    if (compiledItem.calculatedExpression) {
                        compiled.calculatedItems.push(compiledItem);
                    }

                    const enableWhen = item.enableWhen ?? [];
                    if (enableWhen.length > 1 && !item.enableBehavior) {
                        compiled.warnings.push({
                            code: 'missing-enable-behavior',
                            linkId,
                            message: 'Multiple enableWhen conditions found without enableBehavior; using any for compatibility.',
                        });
                    }
                    for (const condition of enableWhen) {
                        const questionLinkId = condition?.question;
                        if (typeof questionLinkId !== 'string' || questionLinkId === '') {
                            continue;
                        }
                        if (!compiled.enableDependents.has(questionLinkId)) {
                            compiled.enableDependents.set(questionLinkId, new Set());
                        }
                        compiled.enableDependents.get(questionLinkId).add(linkId);
                    }

                    walk(item.item, linkId, depth + 1);
                }
            };

            walk(questionnaire.item);
            return compiled;
        }
    }

    class QuestionnaireResponseStore {
        constructor(questionnaire, questionnaireResponse = null) {
            this.questionnaire = questionnaire;
            this.originalResponse = questionnaireResponse ? deepClone(questionnaireResponse) : null;
            this.answers = new Map();
            this.load(questionnaireResponse);
            this.applyInitialAnswers();
        }

        load(questionnaireResponse) {
            this.answers.clear();
            const walk = (items) => {
                for (const item of items ?? []) {
                    const linkId = typeof item?.linkId === 'string' ? item.linkId : '';
                    if (linkId !== '') {
                        const values = (item.answer ?? [])
                            .map(getAnswerValue)
                            .filter((answer) => answer !== null);
                        if (values.length > 0) {
                            this.answers.set(linkId, values);
                        }
                    }
                    walk(item.item);
                    for (const answer of item.answer ?? []) {
                        walk(answer.item);
                    }
                }
            };
            walk(questionnaireResponse?.item);
        }

        applyInitialAnswers() {
            const walk = (items) => {
                for (const item of items ?? []) {
                    if (!this.hasAnswers(item.linkId)) {
                        const initialAnswers = (item.initial ?? [])
                            .map(getAnswerValue)
                            .filter((answer) => answer !== null);
                        const selectedOptions = (item.answerOption ?? [])
                            .filter((option) => option?.initialSelected === true)
                            .map(getAnswerOptionValue)
                            .filter((answer) => answer !== null);
                        const answers = initialAnswers.length > 0 ? initialAnswers : selectedOptions;
                        if (answers.length > 0) {
                            this.setAnswers(item.linkId, answers);
                        }
                    }
                    walk(item.item);
                }
            };
            walk(this.questionnaire.item);
        }

        getAnswers(linkId) {
            return deepClone(this.answers.get(linkId) ?? []);
        }

        hasAnswers(linkId) {
            return (this.answers.get(linkId)?.length ?? 0) > 0;
        }

        setAnswers(linkId, answers) {
            const normalized = (answers ?? []).filter((answer) => answer && typeof answer.valueKey === 'string');
            if (normalized.length === 0) {
                this.answers.delete(linkId);
                return;
            }
            this.answers.set(linkId, deepClone(normalized));
        }

        setAnswer(linkId, answer) {
            this.setAnswers(linkId, answer ? [answer] : []);
        }

        clearAnswer(linkId) {
            this.answers.delete(linkId);
        }

        buildQuestionnaireResponse(isEnabled) {
            const base = this.originalResponse ? deepClone(this.originalResponse) : {
                resourceType: 'QuestionnaireResponse',
                status: 'in-progress',
            };

            base.resourceType = 'QuestionnaireResponse';
            base.status = base.status || 'in-progress';
            base.authored = new Date().toISOString();

            const buildItems = (questionnaireItems) => {
                const responseItems = [];
                for (const item of questionnaireItems ?? []) {
                    const linkId = item?.linkId;
                    if (typeof linkId !== 'string' || item.type === 'display' || !isEnabled(linkId)) {
                        continue;
                    }

                    const childItems = buildItems(item.item);
                    const answers = this.answers.get(linkId) ?? [];
                    const responseItem = {
                        linkId,
                    };
                    if (typeof item.text === 'string' && item.text !== '') {
                        responseItem.text = item.text;
                    }
                    if (answers.length > 0) {
                        responseItem.answer = answers.map((answer) => ({
                            [answer.valueKey]: deepClone(answer.value),
                        }));
                    }
                    if (childItems.length > 0) {
                        responseItem.item = childItems;
                    }
                    if (answers.length > 0 || childItems.length > 0 || item.type === 'group') {
                        responseItems.push(responseItem);
                    }
                }
                return responseItems;
            };

            base.item = buildItems(this.questionnaire.item);
            if (base.item.length === 0) {
                delete base.item;
            }
            delete base.text;
            return base;
        }
    }

    class FhirPathTokenizer {
        tokenize(source) {
            const tokens = [];
            let index = 0;

            const push = (type, value, position = index) => tokens.push({ type, value, position });
            const isIdentifierStart = (character) => /[A-Za-z_]/.test(character);
            const isIdentifierPart = (character) => /[A-Za-z0-9_]/.test(character);

            while (index < source.length) {
                const character = source[index];
                if (/\s/.test(character)) {
                    index++;
                    continue;
                }

                if (character === "'") {
                    const start = index++;
                    let value = '';
                    let closed = false;
                    while (index < source.length) {
                        const current = source[index++];
                        if (current === "'") {
                            if (source[index] === "'") {
                                value += "'";
                                index++;
                                continue;
                            }
                            closed = true;
                            break;
                        }
                        if (current === '\\' && index < source.length) {
                            value += source[index++];
                            continue;
                        }
                        value += current;
                    }
                    if (!closed) {
                        throw new Error('Unterminated FHIRPath string at position ' + start + '.');
                    }
                    push('string', value, start);
                    continue;
                }

                if (/\d/.test(character)) {
                    const start = index;
                    while (index < source.length && /\d/.test(source[index])) {
                        index++;
                    }
                    if (source[index] === '.') {
                        index++;
                        while (index < source.length && /\d/.test(source[index])) {
                            index++;
                        }
                    }
                    push('number', Number(source.slice(start, index)), start);
                    continue;
                }

                if (isIdentifierStart(character)) {
                    const start = index++;
                    while (index < source.length && isIdentifierPart(source[index])) {
                        index++;
                    }
                    const value = source.slice(start, index);
                    push('identifier', value, start);
                    continue;
                }

                const twoCharacter = source.slice(index, index + 2);
                if (['!=', '>=', '<='].includes(twoCharacter)) {
                    push('operator', twoCharacter, index);
                    index += 2;
                    continue;
                }

                if (['=', '>', '<', '+', '-', '*', '/'].includes(character)) {
                    push('operator', character, index++);
                    continue;
                }

                if (['%', '.', ',', '(', ')', '{', '}'].includes(character)) {
                    push(character, character, index++);
                    continue;
                }

                throw new Error('Unsupported FHIRPath token "' + character + '" at position ' + index + '.');
            }

            tokens.push({ type: 'eof', value: null, position: index });
            return tokens;
        }
    }

    class FhirPathParser {
        constructor() {
            this.tokenizer = new FhirPathTokenizer();
        }

        parse(source) {
            this.tokens = this.tokenizer.tokenize(source);
            this.index = 0;
            const expression = this.parseOr();
            this.expect('eof');
            return expression;
        }

        current() {
            return this.tokens[this.index];
        }

        consume(type, value = undefined) {
            const token = this.current();
            if (token.type !== type || (value !== undefined && token.value !== value)) {
                return null;
            }
            this.index++;
            return token;
        }

        expect(type, value = undefined) {
            const token = this.consume(type, value);
            if (!token) {
                const current = this.current();
                throw new Error('Expected ' + (value ?? type) + ' at position ' + current.position + '.');
            }
            return token;
        }

        consumeKeyword(keyword) {
            const token = this.current();
            if (token.type === 'identifier' && token.value.toLowerCase() === keyword) {
                this.index++;
                return token;
            }
            return null;
        }

        parseOr() {
            let expression = this.parseAnd();
            while (this.consumeKeyword('or')) {
                expression = { type: 'binary', operator: 'or', left: expression, right: this.parseAnd() };
            }
            return expression;
        }

        parseAnd() {
            let expression = this.parseComparison();
            while (this.consumeKeyword('and')) {
                expression = { type: 'binary', operator: 'and', left: expression, right: this.parseComparison() };
            }
            return expression;
        }

        parseComparison() {
            let expression = this.parseAdditive();
            const token = this.current();
            if (token.type === 'operator' && ['=', '!=', '>', '<', '>=', '<='].includes(token.value)) {
                this.index++;
                expression = { type: 'binary', operator: token.value, left: expression, right: this.parseAdditive() };
            }
            return expression;
        }

        parseAdditive() {
            let expression = this.parseMultiplicative();
            while (this.current().type === 'operator' && ['+', '-'].includes(this.current().value)) {
                const operator = this.current().value;
                this.index++;
                expression = { type: 'binary', operator, left: expression, right: this.parseMultiplicative() };
            }
            return expression;
        }

        parseMultiplicative() {
            let expression = this.parsePostfix();
            while (this.current().type === 'operator' && ['*', '/'].includes(this.current().value)) {
                const operator = this.current().value;
                this.index++;
                expression = { type: 'binary', operator, left: expression, right: this.parsePostfix() };
            }
            return expression;
        }

        parsePostfix() {
            let expression = this.parsePrimary();
            while (this.consume('.')) {
                const name = this.expect('identifier').value;
                if (this.consume('(')) {
                    const args = this.parseArguments();
                    expression = { type: 'method', name, source: expression, args };
                } else {
                    expression = { type: 'property', name, source: expression };
                }
            }
            return expression;
        }

        parsePrimary() {
            if (this.consume('%')) {
                return { type: 'variable', name: this.expect('identifier').value };
            }

            const stringToken = this.consume('string');
            if (stringToken) {
                return { type: 'literal', value: stringToken.value };
            }

            const numberToken = this.consume('number');
            if (numberToken) {
                return { type: 'literal', value: numberToken.value };
            }

            if (this.consume('{')) {
                this.expect('}');
                return { type: 'empty' };
            }

            if (this.consume('(')) {
                const expression = this.parseOr();
                this.expect(')');
                return expression;
            }

            const identifier = this.consume('identifier');
            if (identifier) {
                const lower = identifier.value.toLowerCase();
                if (lower === 'true' || lower === 'false') {
                    return { type: 'literal', value: lower === 'true' };
                }
                if (this.consume('(')) {
                    return { type: 'function', name: identifier.value, args: this.parseArguments() };
                }
                return { type: 'identifier', name: identifier.value };
            }

            const token = this.current();
            throw new Error('Unexpected FHIRPath token at position ' + token.position + '.');
        }

        parseArguments() {
            const args = [];
            if (this.consume(')')) {
                return args;
            }
            do {
                args.push(this.parseOr());
            } while (this.consume(','));
            this.expect(')');
            return args;
        }
    }

    class FhirPathEvaluator {
        evaluate(ast, environment, context = null) {
            switch (ast.type) {
                case 'literal':
                    return [ast.value];
                case 'empty':
                    return [];
                case 'variable':
                    return asCollection(environment[ast.name]);
                case 'identifier':
                    return this.project(context, ast.name);
                case 'property':
                    return this.project(this.evaluate(ast.source, environment, context), ast.name);
                case 'method':
                    return this.evaluateMethod(ast, environment, context);
                case 'function':
                    return this.evaluateFunction(ast, environment, context);
                case 'binary':
                    return this.evaluateBinary(ast, environment, context);
                default:
                    throw new Error('Unsupported FHIRPath AST node: ' + ast.type + '.');
            }
        }

        project(source, property) {
            const output = [];
            for (const value of asCollection(source)) {
                if (value === null || value === undefined || typeof value !== 'object') {
                    continue;
                }
                const projected = value[property];
                if (Array.isArray(projected)) {
                    output.push(...projected);
                } else if (projected !== undefined && projected !== null) {
                    output.push(projected);
                }
            }
            return output;
        }

        evaluateMethod(ast, environment, context) {
            const source = this.evaluate(ast.source, environment, context);
            const name = ast.name.toLowerCase();
            switch (name) {
                case 'where': {
                    const predicate = ast.args[0];
                    return asCollection(source).filter((value) => collectionBoolean(this.evaluate(predicate, environment, value)));
                }
                case 'exists':
                    return [asCollection(source).length > 0];
                case 'first': {
                    const collection = asCollection(source);
                    return collection.length > 0 ? [collection[0]] : [];
                }
                case 'count':
                    return [asCollection(source).length];
                case 'sum':
                    return [asCollection(source).reduce((sum, value) => sum + Number(value), 0)];
                default:
                    throw new Error('Unsupported FHIRPath method: ' + ast.name + '.');
            }
        }

        evaluateFunction(ast, environment, context) {
            const name = ast.name.toLowerCase();
            switch (name) {
                case 'iif': {
                    if (ast.args.length !== 3) {
                        throw new Error('FHIRPath iif() requires three arguments.');
                    }
                    const condition = collectionBoolean(this.evaluate(ast.args[0], environment, context));
                    return this.evaluate(condition ? ast.args[1] : ast.args[2], environment, context);
                }
                default:
                    throw new Error('Unsupported FHIRPath function: ' + ast.name + '.');
            }
        }

        evaluateBinary(ast, environment, context) {
            const operator = ast.operator;
            if (operator === 'or') {
                const left = collectionBoolean(this.evaluate(ast.left, environment, context));
                if (left) {
                    return [true];
                }
                return [collectionBoolean(this.evaluate(ast.right, environment, context))];
            }
            if (operator === 'and') {
                const left = collectionBoolean(this.evaluate(ast.left, environment, context));
                if (!left) {
                    return [false];
                }
                return [collectionBoolean(this.evaluate(ast.right, environment, context))];
            }

            const left = this.evaluate(ast.left, environment, context);
            const right = this.evaluate(ast.right, environment, context);
            switch (operator) {
                case '=':
                    return [collectionsEqual(left, right)];
                case '!=':
                    return [!collectionsEqual(left, right)];
                case '>':
                case '<':
                case '>=':
                case '<=': {
                    const leftValue = firstValue(left);
                    const rightValue = firstValue(right);
                    if (leftValue === undefined || rightValue === undefined) {
                        return [];
                    }
                    if (operator === '>') return [leftValue > rightValue];
                    if (operator === '<') return [leftValue < rightValue];
                    if (operator === '>=') return [leftValue >= rightValue];
                    return [leftValue <= rightValue];
                }
                case '+':
                case '-':
                case '*':
                case '/': {
                    const leftValue = Number(firstValue(left));
                    const rightValue = Number(firstValue(right));
                    if (!Number.isFinite(leftValue) || !Number.isFinite(rightValue)) {
                        return [];
                    }
                    if (operator === '+') return [leftValue + rightValue];
                    if (operator === '-') return [leftValue - rightValue];
                    if (operator === '*') return [leftValue * rightValue];
                    return rightValue === 0 ? [] : [leftValue / rightValue];
                }
                default:
                    throw new Error('Unsupported FHIRPath operator: ' + operator + '.');
            }
        }
    }

    class SdcExpressionEngine {
        constructor(compiled) {
            this.compiled = compiled;
            this.parser = new FhirPathParser();
            this.evaluator = new FhirPathEvaluator();
            this.compiledExpressions = new Map();
            this.warnings = [];
            this.compileExpressions();
        }

        compileExpressions() {
            for (const compiledItem of this.compiled.calculatedItems) {
                for (const expression of [...compiledItem.variables, compiledItem.calculatedExpression]) {
                    if (!expression || expression.language !== 'text/fhirpath' || typeof expression.expression !== 'string') {
                        continue;
                    }
                    try {
                        this.compiledExpressions.set(expression, this.parser.parse(expression.expression));
                    } catch (error) {
                        this.warnings.push({
                            code: 'fhirpath-compile-error',
                            linkId: compiledItem.linkId,
                            expression: expression.expression,
                            message: error instanceof Error ? error.message : String(error),
                        });
                    }
                }
            }
        }

        recalculate(runtime) {
            const resource = runtime.responseStore.buildQuestionnaireResponse((linkId) => runtime.isEnabled(linkId));
            for (const compiledItem of this.compiled.calculatedItems) {
                const item = compiledItem.item;
                const environment = {
                    questionnaire: this.compiled.questionnaire,
                    resource,
                };
                let failed = false;

                for (const variable of compiledItem.variables) {
                    const ast = this.compiledExpressions.get(variable);
                    if (!ast) {
                        failed = true;
                        break;
                    }
                    try {
                        environment[variable.name] = this.evaluator.evaluate(ast, environment, null);
                    } catch (error) {
                        this.warnings.push({
                            code: 'fhirpath-evaluate-error',
                            linkId: compiledItem.linkId,
                            expression: variable.expression,
                            message: error instanceof Error ? error.message : String(error),
                        });
                        failed = true;
                        break;
                    }
                }

                const calculatedExpression = compiledItem.calculatedExpression;
                const calculatedAst = this.compiledExpressions.get(calculatedExpression);
                if (failed || !calculatedAst) {
                    continue;
                }

                try {
                    const result = this.evaluator.evaluate(calculatedAst, environment, null);
                    const value = firstValue(result);
                    if (value === undefined) {
                        runtime.responseStore.clearAnswer(compiledItem.linkId);
                        continue;
                    }
                    runtime.responseStore.setAnswer(compiledItem.linkId, {
                        valueKey: runtime.answerValueKeyForItem(item),
                        value,
                    });
                } catch (error) {
                    this.warnings.push({
                        code: 'fhirpath-evaluate-error',
                        linkId: compiledItem.linkId,
                        expression: calculatedExpression.expression,
                        message: error instanceof Error ? error.message : String(error),
                    });
                }
            }
        }
    }

    class QuestionnaireBehaviorEngine {
        constructor(runtime) {
            this.runtime = runtime;
            this.enabled = new Map();
        }

        evaluateAll() {
            let changed = true;
            let pass = 0;
            while (changed && pass < 10) {
                changed = false;
                pass++;
                for (const compiledItem of this.runtime.compiled.items) {
                    const next = this.evaluateItem(compiledItem.item);
                    if (this.enabled.get(compiledItem.linkId) !== next) {
                        this.enabled.set(compiledItem.linkId, next);
                        changed = true;
                    }
                }
            }
        }

        evaluateItem(item) {
            const parentLinkId = this.runtime.compiled.parentByLinkId.get(item.linkId);
            if (parentLinkId && !this.isEnabled(parentLinkId)) {
                return false;
            }
            const conditions = item.enableWhen ?? [];
            if (conditions.length === 0) {
                return true;
            }
            const results = conditions.map((condition) => this.evaluateCondition(condition));
            const behavior = item.enableBehavior ?? (conditions.length > 1 ? 'any' : 'all');
            return behavior === 'any' ? results.some(Boolean) : results.every(Boolean);
        }

        evaluateCondition(condition) {
            const answers = this.runtime.responseStore.getAnswers(condition.question);
            const operator = condition.operator;
            const conditionKey = Object.keys(condition).find((key) => key.startsWith('answer'));
            const expected = conditionKey ? condition[conditionKey] : undefined;

            if (operator === 'exists') {
                return answers.length > 0 === Boolean(expected);
            }

            const actualValues = answers.map((answer) => answer.value);
            if (operator === '=') {
                return actualValues.some((actual) => equalPrimitive(actual, expected));
            }
            if (operator === '!=') {
                return !actualValues.some((actual) => equalPrimitive(actual, expected));
            }

            return actualValues.some((actual) => {
                if (operator === '>') return actual > expected;
                if (operator === '<') return actual < expected;
                if (operator === '>=') return actual >= expected;
                if (operator === '<=') return actual <= expected;
                return false;
            });
        }

        isEnabled(linkId) {
            return this.enabled.get(linkId) !== false;
        }
    }

    class ValidationEngine {
        constructor(runtime) {
            this.runtime = runtime;
        }

        validate() {
            const issues = [];
            for (const compiledItem of this.runtime.compiled.items) {
                const item = compiledItem.item;
                if (!this.runtime.isEnabled(compiledItem.linkId) || item.type === 'display' || item.type === 'group') {
                    continue;
                }
                const answers = this.runtime.responseStore.getAnswers(compiledItem.linkId);
                if (item.required === true && answers.length === 0) {
                    issues.push({
                        severity: 'error',
                        code: 'required',
                        linkId: compiledItem.linkId,
                        message: (item.text || compiledItem.linkId) + ' requires a value',
                    });
                }
                if (Number.isInteger(item.maxLength)) {
                    for (const answer of answers) {
                        if (typeof answer.value === 'string' && answer.value.length > item.maxLength) {
                            issues.push({
                                severity: 'error',
                                code: 'max-length',
                                linkId: compiledItem.linkId,
                                message: (item.text || compiledItem.linkId) + ' exceeds the maximum length',
                            });
                        }
                    }
                }
            }
            return {
                valid: issues.length === 0,
                issues,
            };
        }
    }

    class QuestionnaireRenderer {
        constructor(runtime) {
            this.runtime = runtime;
            this.nodes = new Map();
            this.inputs = new Map();
        }

        mount(container) {
            this.ensureNativeControlForm();
            container.replaceChildren();
            container.classList.add('oe-questionnaire-runtime');
            container.classList.toggle('oe-questionnaire-guides', this.runtime.options.hideTreeLine === false);
            const fragment = document.createDocumentFragment();
            for (const item of this.runtime.questionnaire.item ?? []) {
                fragment.appendChild(this.renderItem(item));
            }
            container.appendChild(fragment);
            this.refresh();
        }

        ensureNativeControlForm() {
            if (document.getElementById(NATIVE_CONTROL_FORM_ID)) {
                return;
            }

            const nativeControlForm = document.createElement('form');
            nativeControlForm.id = NATIVE_CONTROL_FORM_ID;
            nativeControlForm.hidden = true;
            nativeControlForm.setAttribute('aria-hidden', 'true');
            document.body.appendChild(nativeControlForm);
        }

        renderItem(item) {
            const wrapper = document.createElement('div');
            wrapper.dataset.linkId = item.linkId;
            wrapper.className = 'oe-questionnaire-item';
            this.nodes.set(item.linkId, wrapper);

            if (item.type === 'group') {
                return this.renderGroup(item, wrapper);
            }
            if (item.type === 'display') {
                return this.renderDisplay(item, wrapper);
            }

            wrapper.classList.add('oe-questionnaire-question', 'form-group');
            const header = document.createElement('div');
            header.className = 'd-flex align-items-start justify-content-between';
            const label = document.createElement('label');
            label.className = 'oe-questionnaire-label font-weight-bold mb-2';
            const labelText = [item.prefix, item.text].filter(Boolean).join(item.prefix && item.text ? ' — ' : '');
            label.textContent = labelText || item.linkId;
            if (item.required === true) {
                const required = document.createElement('span');
                required.className = 'text-danger ml-1';
                required.textContent = '*';
                label.appendChild(required);
            }
            header.appendChild(label);

            const inputId = safeId(item.linkId);
            const input = this.renderInput(item);
            if (input.querySelector('#' + inputId)) {
                label.htmlFor = inputId;
            }

            const helpItem = (item.item ?? []).find((child) => child.type === 'display' && getItemControl(child) === 'help');
            let help = null;
            if (helpItem) {
                const helpButton = document.createElement('button');
                helpButton.type = 'button';
                helpButton.className = 'btn btn-sm btn-link oe-questionnaire-help-button py-0';
                helpButton.setAttribute('aria-expanded', 'false');
                helpButton.textContent = '?';
                header.appendChild(helpButton);

                help = document.createElement('div');
                help.className = 'alert alert-info oe-questionnaire-help d-none';
                help.textContent = helpItem.text ?? '';
                helpButton.addEventListener('click', () => {
                    const hidden = help.classList.toggle('d-none');
                    helpButton.setAttribute('aria-expanded', hidden ? 'false' : 'true');
                });
            }

            wrapper.appendChild(header);
            wrapper.appendChild(input);
            if (help) {
                wrapper.appendChild(help);
            }
            for (const child of item.item ?? []) {
                if (child !== helpItem) {
                    wrapper.appendChild(this.renderItem(child));
                }
            }

            return wrapper;
        }

        renderGroup(item, wrapper) {
            wrapper.classList.add('card', 'mb-3', 'oe-questionnaire-group');
            const header = document.createElement('div');
            header.className = 'card-header';
            const title = document.createElement('h5');
            title.className = 'mb-1';
            title.textContent = item.prefix || item.text || item.linkId;
            header.appendChild(title);
            if (item.prefix && item.text) {
                const description = document.createElement('div');
                description.className = 'small text-muted';
                description.textContent = item.text;
                header.appendChild(description);
            }
            wrapper.appendChild(header);

            const body = document.createElement('div');
            body.className = 'card-body';
            for (const child of item.item ?? []) {
                body.appendChild(this.renderItem(child));
            }
            wrapper.appendChild(body);
            return wrapper;
        }

        renderDisplay(item, wrapper) {
            wrapper.classList.add('oe-questionnaire-display');
            if (getItemControl(item) === 'help') {
                wrapper.classList.add('d-none');
                return wrapper;
            }
            const display = document.createElement('div');
            display.className = 'alert alert-secondary';
            display.textContent = item.text ?? '';
            wrapper.appendChild(display);
            return wrapper;
        }

        renderInput(item) {
            const container = document.createElement('div');
            container.className = 'oe-questionnaire-control';
            const control = getItemControl(item);
            const answers = this.runtime.responseStore.getAnswers(item.linkId);
            const inputId = safeId(item.linkId);

            switch (item.type) {
                case 'choice':
                    return this.renderChoice(item, container, control, answers, inputId, false);
                case 'open-choice':
                    return this.renderChoice(item, container, control, answers, inputId, true);
                case 'boolean':
                    return this.renderBoolean(item, container, answers, inputId);
                case 'decimal':
                case 'integer':
                    return this.renderNumber(item, container, answers, inputId);
                case 'date':
                case 'dateTime':
                case 'time':
                    return this.renderTemporal(item, container, answers, inputId);
                case 'string':
                case 'text':
                case 'url':
                    return this.renderText(item, container, answers, inputId);
                case 'quantity':
                    return this.renderQuantity(item, container, answers, inputId);
                default: {
                    const unsupported = document.createElement('div');
                    unsupported.className = 'alert alert-warning';
                    unsupported.textContent = 'Unsupported Questionnaire item type: ' + item.type;
                    container.appendChild(unsupported);
                    return container;
                }
            }
        }

        renderChoice(item, container, control, answers, inputId, allowOpenChoice) {
            const options = item.answerOption ?? [];
            const selected = answers;
            const repeats = item.repeats === true;
            const useCheckboxes = repeats || control === 'check-box';
            const useRadios = !useCheckboxes && (control === 'radio-button' || (control !== 'drop-down' && options.length <= 6));

            if (useCheckboxes || useRadios) {
                const inputType = useCheckboxes ? 'checkbox' : 'radio';
                options.forEach((option, index) => {
                    const optionAnswer = getAnswerOptionValue(option);
                    if (!optionAnswer) return;
                    const row = document.createElement('div');
                    row.className = 'custom-control custom-' + inputType + ' mb-1';
                    const input = document.createElement('input');
                    input.type = inputType;
                    input.className = 'custom-control-input';
                    input.id = inputId + '-' + index;
                    input.name = inputId;
                    input.setAttribute('form', NATIVE_CONTROL_FORM_ID);
                    input.value = String(index);
                    input.checked = selected.some((answer) => answer.valueKey === optionAnswer.valueKey && equalPrimitive(answer.value, optionAnswer.value));
                    input.disabled = item.readOnly === true;
                    input.addEventListener('change', () => {
                        if (useCheckboxes) {
                            const current = this.runtime.responseStore.getAnswers(item.linkId);
                            const next = input.checked
                                ? [...current, optionAnswer]
                                : current.filter((answer) => !(answer.valueKey === optionAnswer.valueKey && equalPrimitive(answer.value, optionAnswer.value)));
                            this.runtime.setAnswers(item.linkId, next);
                        } else if (input.checked) {
                            this.runtime.setAnswers(item.linkId, [optionAnswer]);
                        }
                    });
                    const label = document.createElement('label');
                    label.className = 'custom-control-label';
                    label.htmlFor = input.id;
                    label.textContent = answerDisplay(optionAnswer);
                    row.append(input, label);
                    container.appendChild(row);
                });
            } else {
                const select = document.createElement('select');
                select.id = inputId;
                select.className = 'form-control';
                select.disabled = item.readOnly === true;
                const blank = document.createElement('option');
                blank.value = '';
                blank.textContent = '';
                select.appendChild(blank);
                options.forEach((option, index) => {
                    const optionAnswer = getAnswerOptionValue(option);
                    if (!optionAnswer) return;
                    const optionElement = document.createElement('option');
                    optionElement.value = String(index);
                    optionElement.textContent = answerDisplay(optionAnswer);
                    optionElement.selected = selected.some((answer) => answer.valueKey === optionAnswer.valueKey && equalPrimitive(answer.value, optionAnswer.value));
                    select.appendChild(optionElement);
                });
                select.addEventListener('change', () => {
                    if (select.value === '') {
                        this.runtime.setAnswers(item.linkId, []);
                        return;
                    }
                    const optionAnswer = getAnswerOptionValue(options[Number(select.value)]);
                    this.runtime.setAnswers(item.linkId, optionAnswer ? [optionAnswer] : []);
                });
                container.appendChild(select);
            }

            if (allowOpenChoice) {
                const openInput = document.createElement('input');
                openInput.type = 'text';
                openInput.className = 'form-control mt-2';
                openInput.placeholder = 'Other';
                const customAnswer = selected.find((answer) => answer.valueKey === 'valueString');
                openInput.value = customAnswer?.value ?? '';
                openInput.disabled = item.readOnly === true;
                openInput.addEventListener('change', () => {
                    const value = openInput.value.trim();
                    this.runtime.setAnswers(item.linkId, value === '' ? [] : [{ valueKey: 'valueString', value }]);
                });
                container.appendChild(openInput);
            }

            this.inputs.set(item.linkId, container);
            return container;
        }

        renderBoolean(item, container, answers, inputId) {
            const selected = firstValue(answers)?.value;
            [
                { value: true, label: 'Yes' },
                { value: false, label: 'No' },
            ].forEach((option, index) => {
                const row = document.createElement('div');
                row.className = 'custom-control custom-radio custom-control-inline';
                const input = document.createElement('input');
                input.type = 'radio';
                input.className = 'custom-control-input';
                input.id = inputId + '-' + index;
                input.name = inputId;
                input.setAttribute('form', NATIVE_CONTROL_FORM_ID);
                input.checked = selected === option.value;
                input.disabled = item.readOnly === true;
                input.addEventListener('change', () => {
                    if (input.checked) {
                        this.runtime.setAnswers(item.linkId, [{ valueKey: 'valueBoolean', value: option.value }]);
                    }
                });
                const label = document.createElement('label');
                label.className = 'custom-control-label';
                label.htmlFor = input.id;
                label.textContent = option.label;
                row.append(input, label);
                container.appendChild(row);
            });
            this.inputs.set(item.linkId, container);
            return container;
        }

        renderNumber(item, container, answers, inputId) {
            const inputGroup = document.createElement('div');
            inputGroup.className = getQuestionnaireUnit(item) ? 'input-group' : '';
            const input = document.createElement('input');
            input.type = 'number';
            input.id = inputId;
            input.className = 'form-control';
            input.step = item.type === 'integer' ? '1' : 'any';
            input.value = firstValue(answers)?.value ?? '';
            input.readOnly = item.readOnly === true;
            input.addEventListener('change', () => {
                const raw = input.value;
                if (raw === '') {
                    this.runtime.setAnswers(item.linkId, []);
                    return;
                }
                const value = item.type === 'integer' ? Number.parseInt(raw, 10) : Number.parseFloat(raw);
                this.runtime.setAnswers(item.linkId, Number.isFinite(value) ? [{ valueKey: this.runtime.answerValueKeyForItem(item), value }] : []);
            });
            inputGroup.appendChild(input);
            const unit = getQuestionnaireUnit(item);
            if (unit) {
                const append = document.createElement('div');
                append.className = 'input-group-append';
                const text = document.createElement('span');
                text.className = 'input-group-text';
                text.textContent = unit;
                append.appendChild(text);
                inputGroup.appendChild(append);
            }
            container.appendChild(inputGroup);
            this.inputs.set(item.linkId, input);
            return container;
        }

        renderTemporal(item, container, answers, inputId) {
            const input = document.createElement('input');
            input.id = inputId;
            input.className = 'form-control';
            input.type = item.type === 'dateTime' ? 'datetime-local' : item.type;
            let value = firstValue(answers)?.value ?? '';
            if (item.type === 'dateTime') {
                value = formatDateTimeLocalValue(value);
            }
            input.value = value;
            input.readOnly = item.readOnly === true;
            input.addEventListener('change', () => {
                let answerValue = input.value;
                if (item.type === 'dateTime') {
                    answerValue = toFhirDateTime(answerValue);
                }
                this.runtime.setAnswers(
                    item.linkId,
                    answerValue === '' ? [] : [{ valueKey: this.runtime.answerValueKeyForItem(item), value: answerValue }]
                );
            });
            container.appendChild(input);
            this.inputs.set(item.linkId, input);
            return container;
        }

        renderText(item, container, answers, inputId) {
            const input = item.type === 'text' ? document.createElement('textarea') : document.createElement('input');
            if (input instanceof HTMLInputElement) {
                input.type = item.type === 'url' ? 'url' : 'text';
            } else {
                input.rows = 3;
            }
            input.id = inputId;
            input.className = 'form-control';
            input.value = firstValue(answers)?.value ?? '';
            input.readOnly = item.readOnly === true;
            if (Number.isInteger(item.maxLength)) {
                input.maxLength = item.maxLength;
            }
            input.addEventListener('change', () => {
                this.runtime.setAnswers(item.linkId, input.value === '' ? [] : [{ valueKey: this.runtime.answerValueKeyForItem(item), value: input.value }]);
            });
            container.appendChild(input);
            this.inputs.set(item.linkId, input);
            return container;
        }

        renderQuantity(item, container, answers, inputId) {
            const quantity = firstValue(answers)?.value ?? {};
            const row = document.createElement('div');
            row.className = 'form-row';
            const valueColumn = document.createElement('div');
            valueColumn.className = 'col-md-6';
            const valueInput = document.createElement('input');
            valueInput.type = 'number';
            valueInput.step = 'any';
            valueInput.id = inputId;
            valueInput.className = 'form-control';
            valueInput.value = quantity.value ?? '';
            valueInput.readOnly = item.readOnly === true;
            valueColumn.appendChild(valueInput);
            const unitColumn = document.createElement('div');
            unitColumn.className = 'col-md-6';
            const unitInput = document.createElement('input');
            unitInput.type = 'text';
            unitInput.className = 'form-control';
            unitInput.placeholder = 'Unit';
            unitInput.value = quantity.unit ?? quantity.code ?? '';
            unitInput.readOnly = item.readOnly === true;
            unitColumn.appendChild(unitInput);
            row.append(valueColumn, unitColumn);
            const save = () => {
                if (valueInput.value === '') {
                    this.runtime.setAnswers(item.linkId, []);
                    return;
                }
                const unit = unitInput.value.trim();
                const next = { value: Number.parseFloat(valueInput.value) };
                if (unit !== '') {
                    next.unit = unit;
                    next.code = unit;
                }
                this.runtime.setAnswers(item.linkId, [{ valueKey: 'valueQuantity', value: next }]);
            };
            valueInput.addEventListener('change', save);
            unitInput.addEventListener('change', save);
            container.appendChild(row);
            this.inputs.set(item.linkId, row);
            return container;
        }

        refresh() {
            for (const compiledItem of this.runtime.compiled.items) {
                const node = this.nodes.get(compiledItem.linkId);
                if (!node) continue;
                node.classList.toggle('d-none', !this.runtime.isEnabled(compiledItem.linkId));
            }
            this.refreshCalculatedValues();
            this.clearValidationState();
        }

        refreshCalculatedValues() {
            for (const compiledItem of this.runtime.compiled.calculatedItems) {
                const input = this.inputs.get(compiledItem.linkId);
                if (!(input instanceof HTMLInputElement)) {
                    continue;
                }
                input.value = firstValue(this.runtime.responseStore.getAnswers(compiledItem.linkId))?.value ?? '';
            }
        }

        clearValidationState() {
            for (const node of this.nodes.values()) {
                node.classList.remove('oe-questionnaire-invalid');
            }
        }

        showValidationIssues(issues) {
            this.clearValidationState();
            for (const issue of issues) {
                this.nodes.get(issue.linkId)?.classList.add('oe-questionnaire-invalid');
            }
        }
    }

    class QuestionnaireRuntime {
        constructor({ questionnaire, questionnaireResponse = null, options = {} }) {
            this.questionnaire = deepClone(questionnaire);
            this.questionnaireResponse = questionnaireResponse ? deepClone(questionnaireResponse) : null;
            this.options = options;
            this.compiler = new QuestionnaireCompiler();
            this.compiled = this.compiler.compile(this.questionnaire);
            this.responseStore = new QuestionnaireResponseStore(this.questionnaire, this.questionnaireResponse);
            this.behaviorEngine = new QuestionnaireBehaviorEngine(this);
            this.validationEngine = new ValidationEngine(this);
            this.expressionEngine = new SdcExpressionEngine(this.compiled);
            this.renderer = new QuestionnaireRenderer(this);
            this.container = null;
            this.changeListeners = new Set();
            this.refreshState();
        }

        mount(container) {
            if (!(container instanceof HTMLElement)) {
                throw new Error('Questionnaire runtime requires a valid container element.');
            }
            this.container = container;
            this.renderer.mount(container);
            this.emitWarnings();
            return this;
        }

        answerValueKeyForItem(item) {
            return {
                boolean: 'valueBoolean',
                decimal: 'valueDecimal',
                integer: 'valueInteger',
                date: 'valueDate',
                dateTime: 'valueDateTime',
                time: 'valueTime',
                string: 'valueString',
                text: 'valueString',
                url: 'valueUri',
                quantity: 'valueQuantity',
            }[item.type] ?? 'valueString';
        }

        setAnswers(linkId, answers) {
            this.responseStore.setAnswers(linkId, answers);
            this.refreshState();
            this.renderer.refresh();
            this.emitChange(linkId);
        }

        refreshState() {
            this.behaviorEngine.evaluateAll();
            this.expressionEngine.recalculate(this);
            this.behaviorEngine.evaluateAll();
        }

        isEnabled(linkId) {
            return this.behaviorEngine.isEnabled(linkId);
        }

        validate() {
            const result = this.validationEngine.validate();
            this.renderer.showValidationIssues(result.issues);
            return result;
        }

        getQuestionnaireResponse() {
            return this.responseStore.buildQuestionnaireResponse((linkId) => this.isEnabled(linkId));
        }

        onChange(listener) {
            this.changeListeners.add(listener);
            return () => this.changeListeners.delete(listener);
        }

        emitChange(linkId) {
            const detail = {
                linkId,
                questionnaireResponse: this.getQuestionnaireResponse(),
            };
            for (const listener of this.changeListeners) {
                listener(detail);
            }
            if (this.container) {
                this.container.dispatchEvent(new CustomEvent('oe-questionnaire-change', { detail }));
            }
        }

        emitWarnings() {
            const warnings = [...this.compiled.warnings, ...this.expressionEngine.warnings];
            if (warnings.length > 0 && global.console?.warn) {
                console.warn('OpenEMR Questionnaire runtime warnings:', warnings);
            }
        }
    }

    global.OpenEMRQuestionnaire = {
        version: '0.2.0',
        EXTENSIONS,
        QuestionnaireCompiler,
        QuestionnaireResponseStore,
        QuestionnaireBehaviorEngine,
        ValidationEngine,
        SdcExpressionEngine,
        FhirPathTokenizer,
        FhirPathParser,
        FhirPathEvaluator,
        QuestionnaireRuntime,
        runtime: null,
        mount({ questionnaire, questionnaireResponse = null, container, options = {} }) {
            const runtime = new QuestionnaireRuntime({
                questionnaire,
                questionnaireResponse,
                options,
            });
            runtime.mount(container);
            this.runtime = runtime;
            return runtime;
        },
    };
})(typeof window !== 'undefined' ? window : globalThis);
