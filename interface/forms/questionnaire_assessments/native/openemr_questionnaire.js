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

    // Separator for instance-qualified storage keys of children inside a
    // repeating group. NUL cannot occur in a FHIR linkId, so a plain linkId and
    // an instance-qualified key never collide: non-repeating questionnaires
    // never produce an instance key and their behaviour is byte-for-byte
    // unchanged. Key shape: `${groupLinkId}\u0000${instanceIndex}\u0000${childLinkId}`.
    const INSTANCE_SEPARATOR = '\u0000';

    function instanceKeyPrefix(groupLinkId, instanceIndex) {
        return groupLinkId + INSTANCE_SEPARATOR + String(instanceIndex) + INSTANCE_SEPARATOR;
    }

    function isRepeatingGroup(item) {
        return item?.type === 'group' && item?.repeats === true;
    }

    const EXTENSIONS = Object.freeze({
        ITEM_CONTROL: 'http://hl7.org/fhir/StructureDefinition/questionnaire-itemControl',
        ORDINAL_VALUE: 'http://hl7.org/fhir/StructureDefinition/ordinalValue',
        QUESTIONNAIRE_UNIT: 'http://hl7.org/fhir/StructureDefinition/questionnaire-unit',
        VARIABLE: 'http://hl7.org/fhir/StructureDefinition/variable',
        CALCULATED_EXPRESSION: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-calculatedExpression',
        INITIAL_EXPRESSION: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-initialExpression',
        ENABLE_WHEN_EXPRESSION: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-enableWhenExpression',
        ENTRY_FORMAT: 'http://hl7.org/fhir/StructureDefinition/entryFormat',
        REGEX: 'http://hl7.org/fhir/StructureDefinition/regex',
        HIDDEN: 'http://hl7.org/fhir/StructureDefinition/questionnaire-hidden',
        MIN_VALUE: 'http://hl7.org/fhir/StructureDefinition/minValue',
        MAX_VALUE: 'http://hl7.org/fhir/StructureDefinition/maxValue',
        MAX_DECIMAL_PLACES: 'http://hl7.org/fhir/StructureDefinition/maxDecimalPlaces',
        MIN_OCCURS: 'http://hl7.org/fhir/StructureDefinition/questionnaire-minOccurs',
        MAX_OCCURS: 'http://hl7.org/fhir/StructureDefinition/questionnaire-maxOccurs',
        CHOICE_ORIENTATION: 'http://hl7.org/fhir/StructureDefinition/questionnaire-choiceOrientation',
        OPEN_LABEL: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-openLabel',
        DISPLAY_CATEGORY: 'http://hl7.org/fhir/StructureDefinition/questionnaire-displayCategory',
        CONSTRAINT: 'http://hl7.org/fhir/StructureDefinition/questionnaire-constraint',
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

    function getEntryFormat(item) {
        const extension = findExtension(item, EXTENSIONS.ENTRY_FORMAT);
        return typeof extension?.valueString === 'string' ? extension.valueString : '';
    }

    function getRegex(item) {
        const extension = findExtension(item, EXTENSIONS.REGEX);
        return typeof extension?.valueString === 'string' ? extension.valueString : '';
    }

    function getOrdinalValue(node) {
        const extension = findExtension(node, EXTENSIONS.ORDINAL_VALUE);
        const value = extension?.valueDecimal ?? extension?.valueInteger;
        return typeof value === 'number' ? value : null;
    }

    function containedResourceById(questionnaire, reference) {
        if (typeof reference !== 'string' || reference === '' || reference[0] !== '#') {
            return null;
        }
        const id = reference.slice(1);
        return (questionnaire?.contained ?? []).find((resource) => resource?.id === id) ?? null;
    }

    function expansionToAnswerOptions(valueSet) {
        const options = [];
        const walkContains = (entries) => {
            for (const entry of entries ?? []) {
                if (entry && entry.abstract !== true && (entry.code !== undefined || entry.display !== undefined)) {
                    const coding = {};
                    if (typeof entry.system === 'string') {
                        coding.system = entry.system;
                    }
                    if (typeof entry.version === 'string') {
                        coding.version = entry.version;
                    }
                    if (entry.code !== undefined) {
                        coding.code = entry.code;
                    }
                    if (typeof entry.display === 'string') {
                        coding.display = entry.display;
                    }
                    const option = { valueCoding: coding };
                    if (Array.isArray(entry.extension)) {
                        option.extension = entry.extension;
                    }
                    options.push(option);
                }
                walkContains(entry?.contains);
            }
        };
        walkContains(valueSet?.expansion?.contains);
        return options;
    }

    function resolveAnswerOptions(item, questionnaire) {
        if (Array.isArray(item.answerOption) && item.answerOption.length > 0) {
            return item.answerOption;
        }
        if (typeof item.answerValueSet === 'string' && item.answerValueSet !== '') {
            const valueSet = containedResourceById(questionnaire, item.answerValueSet);
            if (valueSet && valueSet.resourceType === 'ValueSet' && valueSet.expansion) {
                return expansionToAnswerOptions(valueSet);
            }
        }
        return Array.isArray(item.answerOption) ? item.answerOption : [];
    }

    function extensionValueX(extension) {
        if (!extension || typeof extension !== 'object') {
            return undefined;
        }
        const key = Object.keys(extension).find((candidate) => candidate.startsWith('value'));
        return key ? extension[key] : undefined;
    }

    function getTypedExtensionValue(item, url) {
        return extensionValueX(findExtension(item, url));
    }

    function getBooleanExtension(item, url) {
        const value = getTypedExtensionValue(item, url);
        return value === true;
    }

    function getIntegerExtension(item, url) {
        const value = getTypedExtensionValue(item, url);
        return typeof value === 'number' && Number.isFinite(value) ? Math.trunc(value) : null;
    }

    function getStringExtension(item, url) {
        const extension = findExtension(item, url);
        const value = extension?.valueString ?? extension?.valueCode;
        return typeof value === 'string' ? value : '';
    }

    function isHiddenItem(item) {
        return getBooleanExtension(item, EXTENSIONS.HIDDEN);
    }

    function getChoiceOrientation(item) {
        const value = getStringExtension(item, EXTENSIONS.CHOICE_ORIENTATION);
        return value === 'horizontal' ? 'horizontal' : 'vertical';
    }

    function getConstraints(item) {
        return findExtensions(item, EXTENSIONS.CONSTRAINT).map((extension) => {
            const parts = extension?.extension ?? [];
            const read = (url) => extensionValueX(parts.find((part) => part?.url === url));
            return {
                key: read('key'),
                severity: read('severity') ?? 'error',
                human: read('human') ?? 'Value does not satisfy a constraint',
                expression: read('expression'),
            };
        }).filter((constraint) => typeof constraint.expression === 'string' && constraint.expression !== '');
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
                expressionItems: [],
                enableExpressionItems: [],
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
                        hidden: isHiddenItem(item),
                        calculatedExpression: findExtension(item, EXTENSIONS.CALCULATED_EXPRESSION)?.valueExpression ?? null,
                        initialExpression: findExtension(item, EXTENSIONS.INITIAL_EXPRESSION)?.valueExpression ?? null,
                        enableWhenExpression: findExtension(item, EXTENSIONS.ENABLE_WHEN_EXPRESSION)?.valueExpression ?? null,
                        constraints: getConstraints(item),
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
                    if (
                        compiledItem.calculatedExpression
                        || compiledItem.initialExpression
                        || compiledItem.enableWhenExpression
                        || compiledItem.constraints.length > 0
                        || compiledItem.variables.length > 0
                    ) {
                        compiled.expressionItems.push(compiledItem);
                    }
                    if (compiledItem.enableWhenExpression) {
                        compiled.enableExpressionItems.push(compiledItem);
                    }

                    if (typeof item.answerValueSet === 'string' && item.answerValueSet !== '') {
                        const resolved = resolveAnswerOptions(item, questionnaire);
                        if (resolved.length === 0) {
                            compiled.warnings.push({
                                code: 'unresolved-answer-value-set',
                                linkId,
                                message: 'answerValueSet "' + item.answerValueSet
                                    + '" could not be resolved; only contained, pre-expanded ValueSets (referenced by #id) are supported.',
                            });
                        }
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

            // Precompute, for every item, the chain of repeating-group ancestors
            // (outermost first), and for every repeating group the set of its
            // descendant linkIds. Both drive per-instance enableWhen scoping.
            compiled.repeatAncestors = new Map();
            compiled.repeatGroupDescendants = new Map();
            for (const compiledItem of compiled.items) {
                const ancestors = [];
                let cursor = compiled.parentByLinkId.get(compiledItem.linkId);
                while (cursor) {
                    const ancestorItem = compiled.itemsByLinkId.get(cursor);
                    if (ancestorItem && isRepeatingGroup(ancestorItem.item)) {
                        ancestors.unshift(cursor);
                        if (!compiled.repeatGroupDescendants.has(cursor)) {
                            compiled.repeatGroupDescendants.set(cursor, new Set());
                        }
                        compiled.repeatGroupDescendants.get(cursor).add(compiledItem.linkId);
                    }
                    cursor = compiled.parentByLinkId.get(cursor);
                }
                compiled.repeatAncestors.set(compiledItem.linkId, ancestors);
            }

            return compiled;
        }
    }

    class QuestionnaireResponseStore {
        constructor(questionnaire, questionnaireResponse = null) {
            this.questionnaire = questionnaire;
            this.originalResponse = questionnaireResponse ? deepClone(questionnaireResponse) : null;
            this.answers = new Map();
            // repeating-group linkId -> instance count (at least 1 when present)
            this.repeatCounts = new Map();
            this.load(questionnaireResponse);
            this.applyInitialAnswers();
        }

        load(questionnaireResponse) {
            this.answers.clear();
            this.repeatCounts.clear();

            // Walk the questionnaire and the response in parallel so repeating
            // groups (multiple response items sharing one linkId) are read into
            // instance-qualified keys. questionnaireItems drives the shape;
            // responseItems supplies values.
            const walk = (questionnaireItems, responseItems, keyPrefix) => {
                const responseByLinkId = new Map();
                for (const responseItem of responseItems ?? []) {
                    const linkId = typeof responseItem?.linkId === 'string' ? responseItem.linkId : '';
                    if (linkId === '') {
                        continue;
                    }
                    if (!responseByLinkId.has(linkId)) {
                        responseByLinkId.set(linkId, []);
                    }
                    responseByLinkId.get(linkId).push(responseItem);
                }

                for (const questionnaireItem of questionnaireItems ?? []) {
                    const linkId = typeof questionnaireItem?.linkId === 'string' ? questionnaireItem.linkId : '';
                    if (linkId === '') {
                        continue;
                    }
                    const matches = responseByLinkId.get(linkId) ?? [];

                    if (isRepeatingGroup(questionnaireItem)) {
                        const count = Math.max(matches.length, 0);
                        if (count > 0) {
                            this.repeatCounts.set(keyPrefix + linkId, count);
                        }
                        matches.forEach((match, instanceIndex) => {
                            const childPrefix = instanceKeyPrefix(keyPrefix + linkId, instanceIndex);
                            walk(questionnaireItem.item, match.item, childPrefix);
                        });
                        continue;
                    }

                    const match = matches[0];
                    if (match) {
                        const values = (match.answer ?? [])
                            .map(getAnswerValue)
                            .filter((answer) => answer !== null);
                        if (values.length > 0) {
                            this.answers.set(keyPrefix + linkId, values);
                        }
                        walk(questionnaireItem.item, match.item, keyPrefix);
                        for (const answer of match.answer ?? []) {
                            walk(questionnaireItem.item, answer.item, keyPrefix);
                        }
                    }
                }
            };

            walk(this.questionnaire.item, questionnaireResponse?.item, '');
        }

        getRepeatCount(storageKey) {
            return Math.max(this.repeatCounts.get(storageKey) ?? 1, 1);
        }

        setRepeatCount(storageKey, count) {
            this.repeatCounts.set(storageKey, Math.max(count, 1));
        }

        addRepeatInstance(storageKey) {
            const next = this.getRepeatCount(storageKey) + 1;
            this.repeatCounts.set(storageKey, next);
            // Seed the new instance's item.initial / answerOption.initialSelected
            // values. applyInitialAnswers only fills keys without answers, so
            // existing instances are preserved.
            this.applyInitialAnswers();
            return next;
        }

        removeRepeatInstance(storageKey, instanceIndex) {
            const count = this.getRepeatCount(storageKey);
            if (count <= 1) {
                return count;
            }
            // Shift every instance above the removed one down by one so stored
            // keys stay contiguous with the re-rendered instance indices.
            const prefixes = [];
            for (let index = 0; index < count; index++) {
                prefixes.push(instanceKeyPrefix(storageKey, index));
            }
            const removedPrefix = prefixes[instanceIndex];
            // Shift answers AND nested repeat counts: inner repeating groups keep
            // their instance counts under keys like outer\u0000i\u0000inner, which
            // must move with their instance or nested answers are lost on removal.
            for (const map of [this.answers, this.repeatCounts]) {
                for (const key of [...map.keys()]) {
                    if (key.startsWith(removedPrefix)) {
                        map.delete(key);
                    }
                }
                for (let index = instanceIndex + 1; index < count; index++) {
                    const fromPrefix = prefixes[index];
                    const toPrefix = prefixes[index - 1];
                    for (const key of [...map.keys()]) {
                        if (key.startsWith(fromPrefix)) {
                            map.set(toPrefix + key.slice(fromPrefix.length), map.get(key));
                            map.delete(key);
                        }
                    }
                }
            }
            this.repeatCounts.set(storageKey, count - 1);
            return count - 1;
        }

        applyInitialAnswers() {
            const walk = (items, keyPrefix) => {
                for (const item of items ?? []) {
                    const linkId = typeof item?.linkId === 'string' ? item.linkId : '';
                    if (linkId === '') {
                        continue;
                    }
                    if (isRepeatingGroup(item)) {
                        const count = this.getRepeatCount(keyPrefix + linkId);
                        for (let instanceIndex = 0; instanceIndex < count; instanceIndex++) {
                            walk(item.item, instanceKeyPrefix(keyPrefix + linkId, instanceIndex));
                        }
                        continue;
                    }
                    const storageKey = keyPrefix + linkId;
                    if (!this.hasAnswers(storageKey)) {
                        const initialAnswers = (item.initial ?? [])
                            .map(getAnswerValue)
                            .filter((answer) => answer !== null);
                        const selectedOptions = (item.answerOption ?? [])
                            .filter((option) => option?.initialSelected === true)
                            .map(getAnswerOptionValue)
                            .filter((answer) => answer !== null);
                        const answers = initialAnswers.length > 0 ? initialAnswers : selectedOptions;
                        if (answers.length > 0) {
                            this.setAnswers(storageKey, answers);
                        }
                    }
                    walk(item.item, keyPrefix);
                }
            };
            walk(this.questionnaire.item, '');
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

            const buildResponseItem = (item, keyPrefix) => {
                const linkId = item.linkId;
                const storageKey = keyPrefix + linkId;
                const childItems = buildItems(item.item, keyPrefix);
                const answers = this.answers.get(storageKey) ?? [];
                const responseItem = { linkId };
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
                return { responseItem, hasContent: answers.length > 0 || childItems.length > 0 };
            };

            const buildItems = (questionnaireItems, keyPrefix) => {
                const responseItems = [];
                for (const item of questionnaireItems ?? []) {
                    const linkId = item?.linkId;
                    if (typeof linkId !== 'string' || item.type === 'display' || !isEnabled(keyPrefix + linkId)) {
                        continue;
                    }

                    if (isRepeatingGroup(item)) {
                        const count = this.getRepeatCount(keyPrefix + linkId);
                        for (let instanceIndex = 0; instanceIndex < count; instanceIndex++) {
                            const childPrefix = instanceKeyPrefix(keyPrefix + linkId, instanceIndex);
                            const instanceChildren = buildItems(item.item, childPrefix);
                            const responseItem = { linkId };
                            if (typeof item.text === 'string' && item.text !== '') {
                                responseItem.text = item.text;
                            }
                            if (instanceChildren.length > 0) {
                                responseItem.item = instanceChildren;
                            }
                            // Always emit each instance so the instance count round-trips.
                            responseItems.push(responseItem);
                        }
                        continue;
                    }

                    const { responseItem, hasContent } = buildResponseItem(item, keyPrefix);
                    if (hasContent || item.type === 'group') {
                        responseItems.push(responseItem);
                    }
                }
                return responseItems;
            };

            base.item = buildItems(this.questionnaire.item, '');
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

                if (character === '$') {
                    const start = index++;
                    while (index < source.length && isIdentifierPart(source[index])) {
                        index++;
                    }
                    const name = source.slice(start + 1, index);
                    if (name === '') {
                        throw new Error('Unsupported FHIRPath token "$" at position ' + start + '.');
                    }
                    push('special', name, start);
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

            const specialToken = this.consume('special');
            if (specialToken) {
                return { type: 'special', name: specialToken.value };
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
                case 'special':
                    if (ast.name === 'this') {
                        return asCollection(context);
                    }
                    if (ast.name === 'index') {
                        return asCollection(environment['$index']);
                    }
                    if (ast.name === 'total') {
                        return asCollection(environment['$total']);
                    }
                    throw new Error('Unsupported FHIRPath special variable: $' + ast.name + '.');
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

        scopedEnvironment(environment, index) {
            const scoped = {};
            for (const key of Object.keys(environment)) {
                scoped[key] = environment[key];
            }
            scoped['$index'] = index;
            return scoped;
        }

        evaluateMethod(ast, environment, context) {
            const source = this.evaluate(ast.source, environment, context);
            const name = ast.name.toLowerCase();
            switch (name) {
                case 'where': {
                    const predicate = ast.args[0];
                    return asCollection(source).filter((value, index) =>
                        collectionBoolean(this.evaluate(predicate, this.scopedEnvironment(environment, index), value)));
                }
                case 'select': {
                    const projection = ast.args[0];
                    const output = [];
                    asCollection(source).forEach((value, index) => {
                        output.push(...asCollection(this.evaluate(projection, this.scopedEnvironment(environment, index), value)));
                    });
                    return output;
                }
                case 'all': {
                    const predicate = ast.args[0];
                    if (!predicate) {
                        return [asCollection(source).every((value) => collectionBoolean([value]))];
                    }
                    return [asCollection(source).every((value, index) =>
                        collectionBoolean(this.evaluate(predicate, this.scopedEnvironment(environment, index), value)))];
                }
                case 'sort': {
                    const collection = [...asCollection(source)];
                    const keySelectors = ast.args ?? [];
                    collection.sort((left, right) => {
                        if (keySelectors.length === 0) {
                            return this.compareValues(left, right);
                        }
                        for (const selector of keySelectors) {
                            const leftKey = firstValue(this.evaluate(selector, environment, left));
                            const rightKey = firstValue(this.evaluate(selector, environment, right));
                            const comparison = this.compareValues(leftKey, rightKey);
                            if (comparison !== 0) {
                                return comparison;
                            }
                        }
                        return 0;
                    });
                    return collection;
                }
                case 'repeat':
                case 'repeatall': {
                    const projection = ast.args[0];
                    const keepAll = name === 'repeatall';
                    const output = [];
                    const seen = new Set();
                    let frontier = asCollection(source);
                    let guard = 0;
                    while (frontier.length > 0 && guard < 1000) {
                        guard++;
                        const next = [];
                        for (const value of frontier) {
                            for (const produced of asCollection(this.evaluate(projection, environment, value))) {
                                const signature = JSON.stringify(produced);
                                if (keepAll || !seen.has(signature)) {
                                    seen.add(signature);
                                    output.push(produced);
                                    next.push(produced);
                                }
                            }
                        }
                        frontier = next;
                    }
                    return output;
                }
                case 'aggregate': {
                    const aggregator = ast.args[0];
                    const seed = ast.args.length > 1 ? this.evaluate(ast.args[1], environment, context) : [];
                    let total = seed;
                    asCollection(source).forEach((value, index) => {
                        const scoped = this.scopedEnvironment(environment, index);
                        scoped['$total'] = total;
                        total = this.evaluate(aggregator, scoped, value);
                    });
                    return asCollection(total);
                }
                case 'exists':
                    return [asCollection(source).length > 0];
                case 'empty':
                    return [asCollection(source).length === 0];
                case 'first': {
                    const collection = asCollection(source);
                    return collection.length > 0 ? [collection[0]] : [];
                }
                case 'last': {
                    const collection = asCollection(source);
                    return collection.length > 0 ? [collection[collection.length - 1]] : [];
                }
                case 'count':
                    return [asCollection(source).length];
                case 'distinct': {
                    const output = [];
                    const seen = new Set();
                    for (const value of asCollection(source)) {
                        const signature = JSON.stringify(value);
                        if (!seen.has(signature)) {
                            seen.add(signature);
                            output.push(value);
                        }
                    }
                    return output;
                }
                case 'sum':
                    return [asCollection(source).reduce((sum, value) => sum + Number(value), 0)];
                default:
                    throw new Error('Unsupported FHIRPath method: ' + ast.name + '.');
            }
        }

        compareValues(left, right) {
            if (left === undefined || left === null) {
                return right === undefined || right === null ? 0 : -1;
            }
            if (right === undefined || right === null) {
                return 1;
            }
            if (typeof left === 'number' && typeof right === 'number') {
                return left - right;
            }
            const leftString = String(left);
            const rightString = String(right);
            if (leftString < rightString) {
                return -1;
            }
            if (leftString > rightString) {
                return 1;
            }
            return 0;
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
            this.compiledByString = new Map();
            // compile-time warnings persist for the session; evaluation warnings
            // are reset each refresh so repeated passes don't accumulate copies.
            this.compileWarnings = [];
            this.warnings = [];
            this.compileExpressions();
        }

        compileExpressions() {
            for (const compiledItem of this.compiled.expressionItems) {
                const expressions = [
                    ...compiledItem.variables,
                    compiledItem.calculatedExpression,
                    compiledItem.initialExpression,
                    compiledItem.enableWhenExpression,
                    ...compiledItem.constraints.map((constraint) => ({
                        language: 'text/fhirpath',
                        expression: constraint.expression,
                    })),
                ];
                for (const expression of expressions) {
                    if (!expression || expression.language !== 'text/fhirpath' || typeof expression.expression !== 'string') {
                        continue;
                    }
                    if (this.compiledExpressions.has(expression)) {
                        continue;
                    }
                    try {
                        const ast = this.parser.parse(expression.expression);
                        this.compiledExpressions.set(expression, ast);
                        this.compiledByString.set(expression.expression, ast);
                    } catch (error) {
                        this.compileWarnings.push({
                            code: 'fhirpath-compile-error',
                            linkId: compiledItem.linkId,
                            expression: expression.expression,
                            message: error instanceof Error ? error.message : String(error),
                        });
                    }
                }
            }

            // calculatedExpression / initialExpression are not evaluated
            // per-instance (they would write to a canonical key that no repeat
            // instance renders or serializes). Surface that as a compile warning
            // so the gap is visible rather than silent.
            for (const compiledItem of this.compiled.expressionItems) {
                const insideRepeat = (this.compiled.repeatAncestors.get(compiledItem.linkId) ?? []).length > 0;
                if (insideRepeat && (compiledItem.calculatedExpression || compiledItem.initialExpression)) {
                    this.compileWarnings.push({
                        code: 'expression-in-repeat-unsupported',
                        linkId: compiledItem.linkId,
                        message: 'calculatedExpression/initialExpression inside a repeating group is not evaluated per instance and is skipped.',
                    });
                }
            }
        }

        isInsideRepeat(compiledItem) {
            return (this.compiled.repeatAncestors.get(compiledItem.linkId) ?? []).length > 0;
        }

        resetEvaluationWarnings() {
            this.warnings = [];
        }

        buildEnvironment(runtime, compiledItem) {
            const resource = runtime.responseStore.buildQuestionnaireResponse((linkId) => runtime.isEnabled(linkId));
            const environment = {
                questionnaire: this.compiled.questionnaire,
                resource,
                rootResource: resource,
                context: resource,
            };
            for (const variable of compiledItem.variables) {
                const ast = this.compiledExpressions.get(variable);
                if (!ast) {
                    continue;
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
                }
            }
            return environment;
        }

        // Seed answers from initialExpression for items the user has not answered.
        evaluateInitialExpressions(runtime) {
            for (const compiledItem of this.compiled.expressionItems) {
                if (this.isInsideRepeat(compiledItem)) {
                    continue;
                }
                const expression = compiledItem.initialExpression;
                const ast = expression && this.compiledExpressions.get(expression);
                if (!ast || runtime.responseStore.hasAnswers(compiledItem.linkId)) {
                    continue;
                }
                try {
                    const environment = this.buildEnvironment(runtime, compiledItem);
                    const value = firstValue(this.evaluator.evaluate(ast, environment, null));
                    if (value !== undefined) {
                        runtime.responseStore.setAnswer(compiledItem.linkId, {
                            valueKey: runtime.answerValueKeyForItem(compiledItem.item),
                            value,
                        });
                    }
                } catch (error) {
                    this.warnings.push({
                        code: 'fhirpath-evaluate-error',
                        linkId: compiledItem.linkId,
                        expression: expression.expression,
                        message: error instanceof Error ? error.message : String(error),
                    });
                }
            }
        }

        // Returns true/false for an enableWhenExpression, or null when it cannot
        // be evaluated (the behavior engine then falls back to enableWhen).
        evaluateEnableWhenExpression(runtime, compiledItem) {
            const expression = compiledItem.enableWhenExpression;
            const ast = expression && this.compiledExpressions.get(expression);
            if (!ast) {
                return null;
            }
            try {
                const environment = this.buildEnvironment(runtime, compiledItem);
                return collectionBoolean(this.evaluator.evaluate(ast, environment, null));
            } catch (error) {
                this.warnings.push({
                    code: 'fhirpath-evaluate-error',
                    linkId: compiledItem.linkId,
                    expression: expression.expression,
                    message: error instanceof Error ? error.message : String(error),
                });
                return null;
            }
        }

        evaluateConstraints(runtime, compiledItem) {
            const issues = [];
            for (const constraint of compiledItem.constraints) {
                const ast = this.compiledByString.get(constraint.expression);
                if (!ast) {
                    continue;
                }
                try {
                    const environment = this.buildEnvironment(runtime, compiledItem);
                    const result = this.evaluator.evaluate(ast, environment, null);
                    // An empty result means the constraint's inputs are absent;
                    // treat that as not-yet-violated rather than a failure.
                    if (result.length > 0 && !collectionBoolean(result)) {
                        issues.push({
                            severity: constraint.severity === 'warning' ? 'warning' : 'error',
                            code: 'constraint',
                            linkId: compiledItem.linkId,
                            message: constraint.human,
                        });
                    }
                } catch (error) {
                    this.warnings.push({
                        code: 'fhirpath-evaluate-error',
                        linkId: compiledItem.linkId,
                        expression: constraint.expression,
                        message: error instanceof Error ? error.message : String(error),
                    });
                }
            }
            return issues;
        }

        recalculate(runtime) {
            const resource = runtime.responseStore.buildQuestionnaireResponse((linkId) => runtime.isEnabled(linkId));
            for (const compiledItem of this.compiled.calculatedItems) {
                if (this.isInsideRepeat(compiledItem)) {
                    // Not evaluated per instance (warned at compile time); skip so
                    // nothing is written to an unused canonical key.
                    continue;
                }
                const item = compiledItem.item;
                const environment = {
                    questionnaire: this.compiled.questionnaire,
                    resource,
                    rootResource: resource,
                    context: resource,
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
            // storage key -> bool, for items rendered inside repeating-group
            // instances (per-instance enableWhen). Canonical items use `enabled`.
            this.instanceEnabled = new Map();
        }

        evaluateAll() {
            let changed = true;
            let pass = 0;
            while (changed && pass < 10) {
                changed = false;
                pass++;
                for (const compiledItem of this.runtime.compiled.items) {
                    const next = this.evaluateItem(compiledItem);
                    if (this.enabled.get(compiledItem.linkId) !== next) {
                        this.enabled.set(compiledItem.linkId, next);
                        changed = true;
                    }
                }
            }
            this.evaluateInstances();
        }

        // Per-instance enableWhen for descendants of repeating groups. Walks the
        // questionnaire with instance prefixes (mirroring the renderer) so a
        // condition referencing a sibling within the same repeat instance is
        // evaluated against that instance's answer. Nested repeating groups are
        // handled by recursion. Items outside any repeat never enter this map,
        // so non-repeating questionnaires are unaffected.
        evaluateInstances() {
            this.instanceEnabled.clear();
            // chain carries every enclosing repeating-group instance, outermost
            // first: [{ groupLinkId, prefix }, ...]. prefix is that instance's
            // storage-key prefix.
            const walk = (items, prefix, chain) => {
                for (const item of items ?? []) {
                    if (typeof item?.linkId !== 'string') {
                        continue;
                    }
                    const storageKey = prefix + item.linkId;
                    if (isRepeatingGroup(item)) {
                        const count = this.runtime.responseStore.getRepeatCount(storageKey);
                        for (let instanceIndex = 0; instanceIndex < count; instanceIndex++) {
                            const instancePrefix = instanceKeyPrefix(storageKey, instanceIndex);
                            walk(item.item, instancePrefix, [...chain, { groupLinkId: item.linkId, prefix: instancePrefix }]);
                        }
                        continue;
                    }
                    if (prefix !== '') {
                        this.instanceEnabled.set(storageKey, this.evaluateInstanceItem(item, chain));
                    }
                    walk(item.item, prefix, chain);
                }
            };
            walk(this.runtime.questionnaire.item, '', []);
        }

        evaluateInstanceItem(item, chain) {
            // Every enclosing repeating group's own enablement gates the instance.
            for (const { groupLinkId } of chain) {
                if (this.enabled.get(groupLinkId) === false) {
                    return false;
                }
            }

            // enableWhenExpression gates the instance the same way it gates a
            // canonical item in evaluateItem (combined with any enableWhen).
            const compiledItem = this.runtime.compiled.itemsByLinkId.get(item.linkId);
            if (compiledItem?.enableWhenExpression) {
                const expressionResult = this.runtime.expressionEngine.evaluateEnableWhenExpression(this.runtime, compiledItem);
                if (expressionResult === false) {
                    return false;
                }
            }

            const conditions = item.enableWhen ?? [];
            if (conditions.length === 0) {
                return true;
            }
            const results = conditions.map((condition) => this.matchCondition(condition, this.resolveConditionAnswers(condition, chain)));
            const behavior = item.enableBehavior ?? (conditions.length > 1 ? 'any' : 'all');
            return behavior === 'any' ? results.some(Boolean) : results.every(Boolean);
        }

        // Resolve a condition's referenced answers to the nearest enclosing repeat
        // instance whose group contains the question; fall back to the canonical
        // (outer, non-repeating) answer when no enclosing group matches.
        resolveConditionAnswers(condition, chain) {
            const questionLinkId = condition.question;
            for (let index = chain.length - 1; index >= 0; index--) {
                const { groupLinkId, prefix } = chain[index];
                const descendants = this.runtime.compiled.repeatGroupDescendants.get(groupLinkId);
                if (descendants && descendants.has(questionLinkId)) {
                    return this.runtime.responseStore.getAnswers(prefix + questionLinkId);
                }
            }
            return this.runtime.responseStore.getAnswers(questionLinkId);
        }

        evaluateItem(compiledItem) {
            const item = compiledItem.item;
            const parentLinkId = this.runtime.compiled.parentByLinkId.get(item.linkId);
            if (parentLinkId && !this.isEnabled(parentLinkId)) {
                return false;
            }

            let enabledByConditions = true;
            const conditions = item.enableWhen ?? [];
            if (conditions.length > 0) {
                const results = conditions.map((condition) => this.evaluateCondition(condition));
                const behavior = item.enableBehavior ?? (conditions.length > 1 ? 'any' : 'all');
                enabledByConditions = behavior === 'any' ? results.some(Boolean) : results.every(Boolean);
            }

            if (compiledItem.enableWhenExpression) {
                const expressionResult = this.runtime.expressionEngine.evaluateEnableWhenExpression(this.runtime, compiledItem);
                if (expressionResult !== null) {
                    return enabledByConditions && expressionResult;
                }
            }
            return enabledByConditions;
        }

        evaluateCondition(condition) {
            return this.matchCondition(condition, this.runtime.responseStore.getAnswers(condition.question));
        }

        matchCondition(condition, answers) {
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

        isEnabled(key) {
            if (this.instanceEnabled.has(key)) {
                return this.instanceEnabled.get(key);
            }
            return this.enabled.get(key) !== false;
        }
    }

    class ValidationEngine {
        constructor(runtime) {
            this.runtime = runtime;
        }

        validate() {
            const issues = [];

            // Constraints (questionnaire-constraint) evaluate against the whole
            // %resource, so they run once per compiled item (groups included).
            for (const compiledItem of this.runtime.compiled.items) {
                if (compiledItem.hidden === true || !this.runtime.isEnabled(compiledItem.linkId)) {
                    continue;
                }
                if (compiledItem.constraints.length > 0) {
                    issues.push(...this.runtime.expressionEngine.evaluateConstraints(this.runtime, compiledItem));
                }
            }

            // Answer validation traverses repeating-group instances so that each
            // instance's children validate against their own instance answers and
            // per-instance enablement, and issues target the correct node.
            const visit = (items, prefix) => {
                for (const item of items ?? []) {
                    if (typeof item?.linkId !== 'string') {
                        continue;
                    }
                    const storageKey = prefix + item.linkId;
                    const compiledItem = this.runtime.compiled.itemsByLinkId.get(item.linkId);
                    if (isRepeatingGroup(item)) {
                        const count = this.runtime.responseStore.getRepeatCount(storageKey);
                        for (let instanceIndex = 0; instanceIndex < count; instanceIndex++) {
                            visit(item.item, instanceKeyPrefix(storageKey, instanceIndex));
                        }
                        continue;
                    }
                    if (item.type === 'group') {
                        visit(item.item, prefix);
                        continue;
                    }
                    if (item.type === 'display' || !compiledItem || compiledItem.hidden === true || !this.runtime.isEnabled(storageKey)) {
                        continue;
                    }
                    this.validateItem(compiledItem, storageKey, issues);
                }
            };
            visit(this.runtime.questionnaire.item, '');

            return {
                valid: !issues.some((issue) => issue.severity === 'error'),
                issues,
            };
        }

        validateItem(compiledItem, storageKey, issues) {
            const item = compiledItem.item;
            const answers = this.runtime.responseStore.getAnswers(storageKey);
            const label = item.text || compiledItem.linkId;
            const add = (severity, code, message) => issues.push({
                severity, code, linkId: compiledItem.linkId, storageKey, message,
            });

            const minOccurs = getIntegerExtension(item, EXTENSIONS.MIN_OCCURS);
            const maxOccurs = getIntegerExtension(item, EXTENSIONS.MAX_OCCURS);
            const requiredCount = minOccurs !== null ? minOccurs : (item.required === true ? 1 : 0);
            if (requiredCount > 0 && answers.length < requiredCount) {
                add('error', minOccurs !== null ? 'min-occurs' : 'required',
                    requiredCount === 1 ? label + ' requires a value' : label + ' requires at least ' + requiredCount + ' answers');
            }
            if (maxOccurs !== null && answers.length > maxOccurs) {
                add('error', 'max-occurs', label + ' allows at most ' + maxOccurs + ' answers');
            }
            this.validateValueBounds(item, storageKey, label, answers, issues);
            if (Number.isInteger(item.maxLength)) {
                for (const answer of answers) {
                    if (typeof answer.value === 'string' && answer.value.length > item.maxLength) {
                        add('error', 'max-length', label + ' exceeds the maximum length');
                    }
                }
            }
            const regexPattern = getRegex(item);
            if (regexPattern !== '') {
                let compiledRegex = null;
                try {
                    compiledRegex = new RegExp('^(?:' + regexPattern + ')$');
                } catch {
                    compiledRegex = null;
                }
                if (compiledRegex) {
                    for (const answer of answers) {
                        if (typeof answer.value === 'string' && answer.value !== '' && !compiledRegex.test(answer.value)) {
                            add('error', 'regex', label + ' does not match the required format');
                        }
                    }
                }
            }
        }

        validateValueBounds(item, storageKey, label, answers, issues) {
            const linkId = storageKey.split(INSTANCE_SEPARATOR).pop();
            const minValue = getTypedExtensionValue(item, EXTENSIONS.MIN_VALUE);
            const maxValue = getTypedExtensionValue(item, EXTENSIONS.MAX_VALUE);
            const maxDecimalPlaces = getIntegerExtension(item, EXTENSIONS.MAX_DECIMAL_PLACES);

            for (const answer of answers) {
                const value = answer.value;
                const comparable = typeof value === 'number' ? value
                    : (typeof value === 'string' ? value : undefined);
                if (comparable !== undefined) {
                    if (minValue !== undefined && comparable < minValue) {
                        issues.push({
                            severity: 'error', code: 'min-value', linkId, storageKey,
                            message: label + ' must be at least ' + minValue,
                        });
                    }
                    if (maxValue !== undefined && comparable > maxValue) {
                        issues.push({
                            severity: 'error', code: 'max-value', linkId, storageKey,
                            message: label + ' must be at most ' + maxValue,
                        });
                    }
                }
                if (maxDecimalPlaces !== null && typeof value === 'number') {
                    const decimals = (String(value).split('.')[1] ?? '').length;
                    if (decimals > maxDecimalPlaces) {
                        issues.push({
                            severity: 'error', code: 'max-decimal-places', linkId, storageKey,
                            message: label + ' allows at most ' + maxDecimalPlaces + ' decimal places',
                        });
                    }
                }
            }
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
            this.warningsNode = document.createElement('div');
            this.warningsNode.className = 'oe-questionnaire-warnings d-none';
            this.warningsNode.setAttribute('role', 'alert');
            container.appendChild(this.warningsNode);
            const fragment = document.createDocumentFragment();
            for (const item of this.runtime.questionnaire.item ?? []) {
                fragment.appendChild(this.renderItem(item));
            }
            container.appendChild(fragment);
            this.refresh();
        }

        renderWarnings(warnings) {
            const node = this.warningsNode;
            if (!node) {
                return;
            }
            node.replaceChildren();
            if (!warnings || warnings.length === 0) {
                node.classList.add('d-none');
                return;
            }
            node.classList.remove('d-none');
            const alert = document.createElement('div');
            alert.className = 'alert alert-warning mb-3';
            const heading = document.createElement('div');
            heading.className = 'font-weight-bold';
            heading.textContent = 'This questionnaire could not be fully rendered';
            alert.appendChild(heading);
            const list = document.createElement('ul');
            list.className = 'mb-0 pl-3';
            for (const warning of warnings) {
                const entry = document.createElement('li');
                const linkIdLabel = warning.linkId ? '[' + warning.linkId + '] ' : '';
                entry.textContent = linkIdLabel + (warning.message ?? warning.code ?? 'Unknown issue');
                list.appendChild(entry);
            }
            alert.appendChild(list);
            node.appendChild(alert);
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

        renderItem(item, keyPrefix = '') {
            const storageKey = keyPrefix + item.linkId;
            const wrapper = document.createElement('div');
            wrapper.dataset.linkId = item.linkId;
            if (keyPrefix !== '') {
                wrapper.dataset.instanceKey = storageKey;
            }
            wrapper.className = 'oe-questionnaire-item';
            this.nodes.set(storageKey, wrapper);

            if (isRepeatingGroup(item)) {
                return this.renderRepeatingGroup(item, wrapper, keyPrefix);
            }
            if (item.type === 'group') {
                return this.renderGroup(item, wrapper, keyPrefix);
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

            const inputId = safeId(storageKey);
            const input = this.renderInput(item, storageKey);
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
                    wrapper.appendChild(this.renderItem(child, keyPrefix));
                }
            }

            return wrapper;
        }

        renderGroup(item, wrapper, keyPrefix = '') {
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
                body.appendChild(this.renderItem(child, keyPrefix));
            }
            wrapper.appendChild(body);
            return wrapper;
        }

        renderRepeatingGroup(item, wrapper, keyPrefix) {
            const storageKey = keyPrefix + item.linkId;
            wrapper.classList.add('card', 'mb-3', 'oe-questionnaire-group', 'oe-questionnaire-repeating-group');

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
            wrapper.appendChild(body);

            const minOccurs = Math.max(getIntegerExtension(item, EXTENSIONS.MIN_OCCURS) ?? 1, 1);
            const maxOccurs = getIntegerExtension(item, EXTENSIONS.MAX_OCCURS);
            if (this.runtime.responseStore.getRepeatCount(storageKey) < minOccurs) {
                this.runtime.responseStore.setRepeatCount(storageKey, minOccurs);
            }

            const rerender = () => {
                body.replaceChildren();
                const count = this.runtime.responseStore.getRepeatCount(storageKey);
                for (let instanceIndex = 0; instanceIndex < count; instanceIndex++) {
                    body.appendChild(this.renderGroupInstance(item, keyPrefix, instanceIndex, count, minOccurs, rerender));
                }
                const addButton = document.createElement('button');
                addButton.type = 'button';
                addButton.className = 'btn btn-sm btn-outline-primary oe-questionnaire-repeat-add';
                addButton.textContent = '+ Add ' + (item.text || 'item');
                addButton.disabled = item.readOnly === true || (maxOccurs !== null && count >= maxOccurs);
                addButton.addEventListener('click', () => {
                    this.runtime.responseStore.addRepeatInstance(storageKey);
                    rerender();
                    this.runtime.notifyStructureChanged();
                });
                body.appendChild(addButton);
            };

            rerender();
            return wrapper;
        }

        renderGroupInstance(item, keyPrefix, instanceIndex, count, minOccurs, rerender) {
            const storageKey = keyPrefix + item.linkId;
            const instancePrefix = instanceKeyPrefix(storageKey, instanceIndex);
            const instance = document.createElement('div');
            instance.className = 'oe-questionnaire-group-instance';
            instance.dataset.instanceIndex = String(instanceIndex);

            const instanceHeader = document.createElement('div');
            instanceHeader.className = 'oe-questionnaire-group-instance-header';
            const label = document.createElement('span');
            label.className = 'small text-muted';
            label.textContent = '#' + (instanceIndex + 1);
            instanceHeader.appendChild(label);
            if (item.readOnly !== true && count > minOccurs) {
                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'btn btn-sm btn-link text-danger oe-questionnaire-repeat-remove py-0';
                removeButton.textContent = 'Remove';
                removeButton.addEventListener('click', () => {
                    this.runtime.responseStore.removeRepeatInstance(storageKey, instanceIndex);
                    rerender();
                    this.runtime.notifyStructureChanged();
                });
                instanceHeader.appendChild(removeButton);
            }
            instance.appendChild(instanceHeader);

            for (const child of item.item ?? []) {
                instance.appendChild(this.renderItem(child, instancePrefix));
            }
            return instance;
        }

        renderDisplay(item, wrapper) {
            wrapper.classList.add('oe-questionnaire-display');
            if (getItemControl(item) === 'help') {
                wrapper.classList.add('d-none');
                return wrapper;
            }
            const display = document.createElement('div');
            const categoryExtension = findExtension(item, EXTENSIONS.DISPLAY_CATEGORY);
            const category = categoryExtension?.valueCodeableConcept?.coding
                ?.find((coding) => typeof coding?.code === 'string')?.code
                ?? getStringExtension(item, EXTENSIONS.DISPLAY_CATEGORY);
            const categoryClass = category === 'instructions' ? 'alert-info'
                : (category === 'security' ? 'alert-warning' : 'alert-secondary');
            display.className = 'alert ' + categoryClass;
            if (category !== '') {
                display.dataset.displayCategory = category;
            }
            if (typeof item.prefix === 'string' && item.prefix !== '') {
                const prefix = document.createElement('span');
                prefix.className = 'oe-questionnaire-prefix font-weight-bold mr-1';
                prefix.textContent = item.prefix;
                display.appendChild(prefix);
            }
            const text = document.createElement('span');
            text.className = 'oe-questionnaire-display-text';
            text.textContent = item.text ?? '';
            display.appendChild(text);
            wrapper.appendChild(display);
            return wrapper;
        }

        renderInput(item, storageKey = item.linkId) {
            const container = document.createElement('div');
            container.className = 'oe-questionnaire-control';
            const control = getItemControl(item);
            const answers = this.runtime.responseStore.getAnswers(storageKey);
            const inputId = safeId(storageKey);

            // Repeating scalar items (repeats=true on a non-choice, non-group
            // item) get an add/remove row list. choice/open-choice express
            // repeats through checkboxes and are handled in renderChoice.
            const repeatableScalar = ['string', 'text', 'url', 'integer', 'decimal', 'date', 'dateTime', 'time', 'reference'];
            if (item.repeats === true && repeatableScalar.includes(item.type)) {
                return this.renderRepeatingScalar(item, container, storageKey);
            }

            switch (item.type) {
                case 'choice':
                    return this.renderChoice(item, container, control, answers, inputId, false, storageKey);
                case 'open-choice':
                    return this.renderChoice(item, container, control, answers, inputId, true, storageKey);
                case 'boolean':
                    return this.renderBoolean(item, container, answers, inputId, storageKey);
                case 'decimal':
                case 'integer':
                    return this.renderNumber(item, container, answers, inputId, storageKey);
                case 'date':
                case 'dateTime':
                case 'time':
                    return this.renderTemporal(item, container, answers, inputId, storageKey);
                case 'string':
                case 'text':
                case 'url':
                    return this.renderText(item, container, answers, inputId, storageKey);
                case 'quantity':
                    return this.renderQuantity(item, container, answers, inputId, storageKey);
                case 'reference':
                    return this.renderReference(item, container, answers, inputId, storageKey);
                case 'attachment':
                    return this.renderAttachment(item, container, answers, inputId, storageKey);
                default: {
                    const unsupported = document.createElement('div');
                    unsupported.className = 'alert alert-warning';
                    unsupported.textContent = 'Unsupported Questionnaire item type: ' + item.type;
                    container.appendChild(unsupported);
                    return container;
                }
            }
        }

        renderChoice(item, container, control, answers, inputId, allowOpenChoice, storageKey = item.linkId) {
            const options = resolveAnswerOptions(item, this.runtime.questionnaire);
            const selected = answers;
            const repeats = item.repeats === true;
            const useCheckboxes = repeats || control === 'check-box';
            const useRadios = !useCheckboxes && (control === 'radio-button' || (control !== 'drop-down' && options.length <= 6));

            const orientation = getChoiceOrientation(item);
            if (useCheckboxes || useRadios) {
                const inputType = useCheckboxes ? 'checkbox' : 'radio';
                options.forEach((option, index) => {
                    const optionAnswer = getAnswerOptionValue(option);
                    if (!optionAnswer) return;
                    const row = document.createElement('div');
                    row.className = 'custom-control custom-' + inputType + ' mb-1'
                        + (orientation === 'horizontal' ? ' custom-control-inline' : '');
                    const input = document.createElement('input');
                    input.type = inputType;
                    input.className = 'custom-control-input';
                    input.id = inputId + '-' + index;
                    input.name = inputId;
                    input.setAttribute('form', NATIVE_CONTROL_FORM_ID);
                    input.value = String(index);
                    input.checked = selected.some((answer) => answer.valueKey === optionAnswer.valueKey && equalPrimitive(answer.value, optionAnswer.value));
                    input.disabled = item.readOnly === true;
                    const optionOrdinal = getOrdinalValue(option);
                    if (optionOrdinal !== null) {
                        input.dataset.ordinal = String(optionOrdinal);
                    }
                    input.addEventListener('change', () => {
                        if (useCheckboxes) {
                            const current = this.runtime.responseStore.getAnswers(storageKey);
                            const next = input.checked
                                ? [...current, optionAnswer]
                                : current.filter((answer) => !(answer.valueKey === optionAnswer.valueKey && equalPrimitive(answer.value, optionAnswer.value)));
                            this.runtime.setAnswers(storageKey, next);
                        } else if (input.checked) {
                            this.runtime.setAnswers(storageKey, [optionAnswer]);
                        }
                    });
                    const label = document.createElement('label');
                    label.className = 'custom-control-label oe-questionnaire-choice-label';
                    label.htmlFor = input.id;
                    label.textContent = answerDisplay(optionAnswer);
                    label.title = answerDisplay(optionAnswer);
                    row.append(input, label);
                    container.appendChild(row);
                });
            } else {
                const select = document.createElement('select');
                select.id = inputId;
                select.className = 'form-control oe-questionnaire-choice-select';
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
                    optionElement.title = answerDisplay(optionAnswer);
                    const optionOrdinal = getOrdinalValue(option);
                    if (optionOrdinal !== null) {
                        optionElement.dataset.ordinal = String(optionOrdinal);
                    }
                    optionElement.selected = selected.some((answer) => answer.valueKey === optionAnswer.valueKey && equalPrimitive(answer.value, optionAnswer.value));
                    select.appendChild(optionElement);
                });
                select.addEventListener('change', () => {
                    if (select.value === '') {
                        this.runtime.setAnswers(storageKey, []);
                        return;
                    }
                    const optionAnswer = getAnswerOptionValue(options[Number(select.value)]);
                    this.runtime.setAnswers(storageKey, optionAnswer ? [optionAnswer] : []);
                });
                container.appendChild(select);
            }

            if (allowOpenChoice) {
                const openLabelText = getStringExtension(item, EXTENSIONS.OPEN_LABEL);
                if (openLabelText !== '') {
                    const openLabel = document.createElement('label');
                    openLabel.className = 'oe-questionnaire-open-label small mt-2 mb-1 d-block';
                    openLabel.textContent = openLabelText;
                    container.appendChild(openLabel);
                }
                const openInput = document.createElement('input');
                openInput.type = 'text';
                openInput.className = 'form-control' + (openLabelText !== '' ? '' : ' mt-2');
                openInput.placeholder = openLabelText !== '' ? openLabelText : 'Other';
                const customAnswer = selected.find((answer) => answer.valueKey === 'valueString');
                openInput.value = customAnswer?.value ?? '';
                openInput.disabled = item.readOnly === true;
                openInput.addEventListener('change', () => {
                    const value = openInput.value.trim();
                    this.runtime.setAnswers(storageKey, value === '' ? [] : [{ valueKey: 'valueString', value }]);
                });
                container.appendChild(openInput);
            }

            this.inputs.set(storageKey, container);
            return container;
        }

        renderBoolean(item, container, answers, inputId, storageKey = item.linkId) {
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
                        this.runtime.setAnswers(storageKey, [{ valueKey: 'valueBoolean', value: option.value }]);
                    }
                });
                const label = document.createElement('label');
                label.className = 'custom-control-label';
                label.htmlFor = input.id;
                label.textContent = option.label;
                row.append(input, label);
                container.appendChild(row);
            });
            this.inputs.set(storageKey, container);
            return container;
        }

        renderNumber(item, container, answers, inputId, storageKey = item.linkId) {
            const inputGroup = document.createElement('div');
            inputGroup.className = getQuestionnaireUnit(item) ? 'input-group' : '';
            const input = document.createElement('input');
            input.type = 'number';
            input.id = inputId;
            input.className = 'form-control';
            input.step = item.type === 'integer' ? '1' : 'any';
            input.value = firstValue(answers)?.value ?? '';
            input.disabled = item.readOnly === true;
            const numberEntryFormat = getEntryFormat(item);
            if (numberEntryFormat !== '') {
                input.placeholder = numberEntryFormat;
            }
            input.addEventListener('change', () => {
                const raw = input.value;
                if (raw === '') {
                    this.runtime.setAnswers(storageKey, []);
                    return;
                }
                const value = item.type === 'integer' ? Number.parseInt(raw, 10) : Number.parseFloat(raw);
                this.runtime.setAnswers(storageKey, Number.isFinite(value) ? [{ valueKey: this.runtime.answerValueKeyForItem(item), value }] : []);
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
            this.inputs.set(storageKey, input);
            return container;
        }

        renderTemporal(item, container, answers, inputId, storageKey = item.linkId) {
            const input = document.createElement('input');
            input.id = inputId;
            input.className = 'form-control';
            input.type = item.type === 'dateTime' ? 'datetime-local' : item.type;
            let value = firstValue(answers)?.value ?? '';
            if (item.type === 'dateTime') {
                value = formatDateTimeLocalValue(value);
            }
            input.value = value;
            input.disabled = item.readOnly === true;
            input.addEventListener('change', () => {
                let answerValue = input.value;
                if (item.type === 'dateTime') {
                    answerValue = toFhirDateTime(answerValue);
                }
                this.runtime.setAnswers(
                    storageKey,
                    answerValue === '' ? [] : [{ valueKey: this.runtime.answerValueKeyForItem(item), value: answerValue }]
                );
            });
            container.appendChild(input);
            this.inputs.set(storageKey, input);
            return container;
        }

        renderText(item, container, answers, inputId, storageKey = item.linkId) {
            const input = item.type === 'text' ? document.createElement('textarea') : document.createElement('input');
            if (input instanceof HTMLInputElement) {
                input.type = item.type === 'url' ? 'url' : 'text';
            } else {
                input.rows = 3;
            }
            input.id = inputId;
            input.className = 'form-control';
            input.value = firstValue(answers)?.value ?? '';
            input.disabled = item.readOnly === true;
            if (Number.isInteger(item.maxLength)) {
                input.maxLength = item.maxLength;
            }
            const entryFormat = getEntryFormat(item);
            if (entryFormat !== '') {
                input.placeholder = entryFormat;
            }
            const regex = getRegex(item);
            if (regex !== '') {
                if (input instanceof HTMLInputElement) {
                    input.pattern = regex;
                }
                input.dataset.regex = regex;
            }
            input.addEventListener('change', () => {
                this.runtime.setAnswers(storageKey, input.value === '' ? [] : [{ valueKey: this.runtime.answerValueKeyForItem(item), value: input.value }]);
            });
            container.appendChild(input);
            this.inputs.set(storageKey, input);
            return container;
        }

        renderQuantity(item, container, answers, inputId, storageKey = item.linkId) {
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
            valueInput.disabled = item.readOnly === true;
            valueColumn.appendChild(valueInput);
            const unitColumn = document.createElement('div');
            unitColumn.className = 'col-md-6';
            const unitInput = document.createElement('input');
            unitInput.type = 'text';
            unitInput.className = 'form-control';
            unitInput.placeholder = 'Unit';
            unitInput.value = quantity.unit ?? quantity.code ?? '';
            unitInput.disabled = item.readOnly === true;
            unitColumn.appendChild(unitInput);
            row.append(valueColumn, unitColumn);
            const save = () => {
                if (valueInput.value === '') {
                    this.runtime.setAnswers(storageKey, []);
                    return;
                }
                const unit = unitInput.value.trim();
                const next = { value: Number.parseFloat(valueInput.value) };
                if (unit !== '') {
                    next.unit = unit;
                    next.code = unit;
                }
                this.runtime.setAnswers(storageKey, [{ valueKey: 'valueQuantity', value: next }]);
            };
            valueInput.addEventListener('change', save);
            unitInput.addEventListener('change', save);
            container.appendChild(row);
            this.inputs.set(storageKey, row);
            return container;
        }

        renderReference(item, container, answers, inputId, storageKey = item.linkId) {
            const input = document.createElement('input');
            input.type = 'text';
            input.id = inputId;
            input.className = 'form-control';
            input.placeholder = getEntryFormat(item) || 'Reference (e.g. Patient/123)';
            input.value = firstValue(answers)?.value?.reference ?? '';
            input.disabled = item.readOnly === true;
            input.addEventListener('change', () => {
                const value = input.value.trim();
                this.runtime.setAnswers(
                    storageKey,
                    value === '' ? [] : [{ valueKey: 'valueReference', value: { reference: value } }]
                );
            });
            container.appendChild(input);
            this.inputs.set(storageKey, input);
            return container;
        }

        renderAttachment(item, container, answers, inputId, storageKey = item.linkId) {
            const existing = firstValue(answers)?.value;
            const input = document.createElement('input');
            input.type = 'file';
            input.id = inputId;
            input.className = 'form-control-file';
            input.disabled = item.readOnly === true;
            const info = document.createElement('div');
            info.className = 'small text-muted mt-1 oe-questionnaire-attachment-info';
            if (existing?.title) {
                info.textContent = 'Current: ' + existing.title;
            }
            input.addEventListener('change', () => {
                const file = input.files && input.files[0];
                if (!file) {
                    this.runtime.setAnswers(storageKey, []);
                    return;
                }
                const reader = new FileReader();
                reader.onload = () => {
                    const result = String(reader.result || '');
                    const data = result.includes(',') ? result.slice(result.indexOf(',') + 1) : result;
                    const attachment = { data, title: file.name };
                    if (file.type) {
                        attachment.contentType = file.type;
                    }
                    if (Number.isFinite(file.size)) {
                        attachment.size = file.size;
                    }
                    this.runtime.setAnswers(storageKey, [{ valueKey: 'valueAttachment', value: attachment }]);
                };
                reader.readAsDataURL(file);
            });
            container.append(input, info);
            this.inputs.set(storageKey, input);
            return container;
        }

        makeScalarInput(item, rawValue) {
            let input;
            if (item.type === 'text') {
                input = document.createElement('textarea');
                input.rows = 2;
            } else {
                input = document.createElement('input');
                input.type = (item.type === 'integer' || item.type === 'decimal') ? 'number'
                    : item.type === 'date' ? 'date'
                        : item.type === 'dateTime' ? 'datetime-local'
                            : item.type === 'time' ? 'time'
                                : item.type === 'url' ? 'url'
                                    : 'text';
                if (item.type === 'integer') {
                    input.step = '1';
                }
                if (item.type === 'decimal') {
                    input.step = 'any';
                }
            }
            input.className = 'form-control';
            input.value = rawValue ?? '';
            return input;
        }

        scalarAnswerFromInput(item, input) {
            const raw = input.value;
            if (raw === '') {
                return null;
            }
            if (item.type === 'integer') {
                const value = Number.parseInt(raw, 10);
                return Number.isFinite(value) ? { valueKey: 'valueInteger', value } : null;
            }
            if (item.type === 'decimal') {
                const value = Number.parseFloat(raw);
                return Number.isFinite(value) ? { valueKey: 'valueDecimal', value } : null;
            }
            if (item.type === 'dateTime') {
                return { valueKey: 'valueDateTime', value: toFhirDateTime(raw) };
            }
            if (item.type === 'reference') {
                return { valueKey: 'valueReference', value: { reference: raw } };
            }
            return { valueKey: this.runtime.answerValueKeyForItem(item), value: raw };
        }

        scalarDisplayValue(item, answer) {
            if (!answer) {
                return '';
            }
            if (item.type === 'dateTime') {
                return formatDateTimeLocalValue(answer.value);
            }
            if (item.type === 'reference') {
                return answer.value?.reference ?? '';
            }
            return answer.value ?? '';
        }

        renderRepeatingScalar(item, container, storageKey) {
            const maxOccurs = getIntegerExtension(item, EXTENSIONS.MAX_OCCURS);

            const rerender = () => {
                container.replaceChildren();
                const answers = this.runtime.responseStore.getAnswers(storageKey);

                const buildRow = (answer, index) => {
                    const row = document.createElement('div');
                    row.className = 'form-row oe-questionnaire-repeat-row mb-1';
                    const column = document.createElement('div');
                    column.className = 'col';
                    const input = this.makeScalarInput(item, this.scalarDisplayValue(item, answer));
                    input.disabled = item.readOnly === true;
                    input.addEventListener('change', () => {
                        const current = this.runtime.responseStore.getAnswers(storageKey);
                        const parsed = this.scalarAnswerFromInput(item, input);
                        if (index < current.length) {
                            if (parsed) {
                                current[index] = parsed;
                            } else {
                                current.splice(index, 1);
                            }
                        } else if (parsed) {
                            current.push(parsed);
                        }
                        this.runtime.setAnswers(storageKey, current);
                        rerender();
                    });
                    column.appendChild(input);
                    row.appendChild(column);

                    if (answer && item.readOnly !== true) {
                        const removeColumn = document.createElement('div');
                        removeColumn.className = 'col-auto';
                        const removeButton = document.createElement('button');
                        removeButton.type = 'button';
                        removeButton.className = 'btn btn-sm btn-link text-danger oe-questionnaire-repeat-remove py-0';
                        removeButton.textContent = 'Remove';
                        removeButton.addEventListener('click', () => {
                            const current = this.runtime.responseStore.getAnswers(storageKey);
                            current.splice(index, 1);
                            this.runtime.setAnswers(storageKey, current);
                            rerender();
                        });
                        removeColumn.appendChild(removeButton);
                        row.appendChild(removeColumn);
                    }
                    return row;
                };

                answers.forEach((answer, index) => container.appendChild(buildRow(answer, index)));
                if (item.readOnly !== true && (maxOccurs === null || answers.length < maxOccurs)) {
                    container.appendChild(buildRow(null, answers.length));
                }
            };

            rerender();
            this.inputs.set(storageKey, container);
            return container;
        }

        refresh() {
            for (const [storageKey, node] of this.nodes) {
                const linkId = storageKey.split(INSTANCE_SEPARATOR).pop();
                const compiledItem = this.runtime.compiled.itemsByLinkId.get(linkId);
                const hidden = compiledItem?.hidden === true;
                node.classList.toggle('d-none', !this.runtime.isEnabled(storageKey) || hidden);
            }
            this.refreshCalculatedValues();
            this.renderWarnings(this.runtime.collectWarnings());
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
                this.nodes.get(issue.storageKey ?? issue.linkId)?.classList.add('oe-questionnaire-invalid');
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
            this.initialExpressionsSeeded = false;
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

        notifyStructureChanged() {
            this.refreshState();
            this.renderer.refresh();
            this.emitChange(null);
        }

        refreshState() {
            // Clear evaluation warnings up front so repeated passes (evaluateAll
            // runs twice per change, each up to 10 iterations) don't accumulate
            // duplicate entries in the warning panel.
            this.expressionEngine.resetEvaluationWarnings();
            this.behaviorEngine.evaluateAll();
            if (!this.initialExpressionsSeeded) {
                // Seed initialExpression values once, after enablement is known
                // but before the first calculation pass, so they behave like
                // author-provided initial answers.
                this.initialExpressionsSeeded = true;
                this.expressionEngine.evaluateInitialExpressions(this);
            }
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

        collectWarnings() {
            const merged = [
                ...this.compiled.warnings,
                ...this.expressionEngine.compileWarnings,
                ...this.expressionEngine.warnings,
            ];
            const seen = new Set();
            return merged.filter((warning) => {
                const signature = [warning.code, warning.linkId, warning.expression, warning.message].join(INSTANCE_SEPARATOR);
                if (seen.has(signature)) {
                    return false;
                }
                seen.add(signature);
                return true;
            });
        }

        emitWarnings() {
            const warnings = this.collectWarnings();
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
