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

    // QuestionnaireResponse provenance, mirroring what LForms stamps on its
    // export: the base R4 QuestionnaireResponse profile, the questionnaire
    // canonical (url|version), and a meta.tag naming the producing version.
    const QR_BASE_PROFILE = 'http://hl7.org/fhir/4.0/StructureDefinition/QuestionnaireResponse';
    const VERSION_TAG_SYSTEM = 'https://www.open-emr.org/fhir/CodeSystem/openemr-version';
    const VERSION_TAG_PREFIX = 'openemrVersion: ';
    // The host <script data-openemr-version="..."> tag supplies the OpenEMR
    // version; read it while this script is executing (currentScript is only
    // set during evaluation). options.openemrVersion overrides it per mount.
    const SCRIPT_OPENEMR_VERSION = (() => {
        try {
            const value = global.document?.currentScript?.dataset?.openemrVersion;
            return typeof value === 'string' ? value.trim() : '';
        } catch {
            return '';
        }
    })();

    // The runtime's own words, in English. The host translates them the
    // same way it supplies the version: <script data-openemr-strings="{...}">
    // with the same keys (OpenEMR's include fills it with xl()), and
    // options.strings overrides per mount. {0}, {1} are filled in order, so
    // a translation can reorder them.
    const DEFAULT_STRINGS = Object.freeze({
        add: '+ Add {0}',
        addItem: 'item',
        remove: 'Remove',
        instance: '#{0}',
        yes: 'Yes',
        no: 'No',
        other: 'Other',
        unit: 'Unit',
        referencePlaceholder: 'Reference (e.g. Patient/123)',
        currentFile: 'Current: {0}',
        close: 'Close',
        help: 'Help',
        legal: 'Legal notice',
        copyright: 'Copyright',
        warningsHeading: 'Some questionnaire items could not be fully rendered',
        unknownIssue: 'Unknown issue',
        unsupportedType: 'Unsupported Questionnaire item type: {0}',
        requiresValue: '{0} requires a value',
        requiresAtLeast: '{0} requires at least {1} answers',
        allowsAtMost: '{0} allows at most {1} answers',
        maxLength: '{0} exceeds the maximum length',
        minLength: '{0} needs at least {1} characters',
        mimeType: '{0} must be a file of type {1}',
        maxSize: '{0} must be at most {1} bytes',
        regex: '{0} does not match the required format',
        minValue: '{0} must be at least {1}',
        maxValue: '{0} must be at most {1}',
        maxDecimalPlaces: '{0} allows at most {1} decimal places',
        moreInfo: 'More information',
        referenceTo: 'Reference to {0} (e.g. {1}/123)',
        referenceType: '{0} must refer to {1}',
        signHere: 'Sign here',
        signaturePad: 'Signature pad: draw your signature',
        typeToSign: 'Or type your full name to sign',
        clear: 'Clear',
        signatureRequired: '{0} needs a signature',
        thisForm: 'This form',
        expand: 'Expand',
        collapse: 'Collapse',
    });
    const SCRIPT_STRINGS = (() => {
        try {
            const value = global.document?.currentScript?.dataset?.openemrStrings;
            const parsed = typeof value === 'string' && value !== '' ? JSON.parse(value) : {};
            return parsed !== null && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
        } catch {
            return {};
        }
    })();

    function formatString(template, args) {
        return String(template).replace(/\{(\d+)\}/g, (match, position) => (args[Number(position)] === undefined ? match : String(args[Number(position)])));
    }

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

    // Depth-first search for the first QuestionnaireResponse item with a given
    // linkId; used to resolve %context for a group/item-defined FHIRPath variable.
    function findResponseItemByLinkId(resource, linkId) {
        const stack = [...(resource?.item ?? [])];
        while (stack.length > 0) {
            const node = stack.shift();
            if (node?.linkId === linkId) {
                return node;
            }
            if (Array.isArray(node?.item)) {
                stack.unshift(...node.item);
            }
            for (const answer of node?.answer ?? []) {
                if (Array.isArray(answer?.item)) {
                    stack.unshift(...answer.item);
                }
            }
        }
        return null;
    }

    const EXTENSIONS = Object.freeze({
        ITEM_CONTROL: 'http://hl7.org/fhir/StructureDefinition/questionnaire-itemControl',
        ORDINAL_VALUE: 'http://hl7.org/fhir/StructureDefinition/ordinalValue',
        ITEM_WEIGHT: 'http://hl7.org/fhir/StructureDefinition/itemWeight',
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
        OPTION_PREFIX: 'http://hl7.org/fhir/StructureDefinition/questionnaire-optionPrefix',
        UNIT_OPTION: 'http://hl7.org/fhir/StructureDefinition/questionnaire-unitOption',
        UNIT_OPEN: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-unitOpen',
        ANSWER_CONSTRAINT: 'http://hl7.org/fhir/StructureDefinition/questionnaire-answerConstraint',
        MIN_LENGTH: 'http://hl7.org/fhir/StructureDefinition/minLength',
        MIME_TYPE: 'http://hl7.org/fhir/StructureDefinition/mimeType',
        MAX_SIZE: 'http://hl7.org/fhir/StructureDefinition/maxSize',
        OPTION_EXCLUSIVE: 'http://hl7.org/fhir/StructureDefinition/questionnaire-optionExclusive',
        CRITICAL_EXTENSION: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-rendering-criticalExtension',
        ITEM_MEDIA: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-itemMedia',
        ITEM_ANSWER_MEDIA: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-itemAnswerMedia',
        COLLAPSIBLE: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-collapsible',
        COLUMN_COUNT: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-columnCount',
        SLIDER_STEP_VALUE: 'http://hl7.org/fhir/StructureDefinition/questionnaire-sliderStepValue',
        SUPPORT_HYPERLINK: 'http://hl7.org/fhir/StructureDefinition/questionnaire-supportHyperlink',
        USAGE_MODE: 'http://hl7.org/fhir/StructureDefinition/questionnaire-usageMode',
        UNIT_VALUE_SET: 'http://hl7.org/fhir/StructureDefinition/questionnaire-unitValueSet',
        REFERENCE_RESOURCE: 'http://hl7.org/fhir/StructureDefinition/questionnaire-referenceResource',
        TARGET_CONSTRAINT: 'http://hl7.org/fhir/StructureDefinition/targetConstraint',
        MIN_QUANTITY: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-minQuantity',
        MAX_QUANTITY: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-maxQuantity',
        CQF_EXPRESSION: 'http://hl7.org/fhir/StructureDefinition/cqf-expression',
        ANSWER_EXPRESSION: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-answerExpression',
        ANSWER_OPTIONS_TOGGLE: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-answerOptionsToggleExpression',
        LAUNCH_CONTEXT: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-launchContext',
        SIGNATURE_REQUIRED: 'http://hl7.org/fhir/StructureDefinition/questionnaire-signatureRequired',
        RESPONSE_SIGNATURE: 'http://hl7.org/fhir/StructureDefinition/questionnaireresponse-signature',
        SUB_QUESTIONNAIRE: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-subQuestionnaire',
        SHORT_TEXT: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-shortText',
        WIDTH: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-width',
        KEYBOARD: 'http://hl7.org/fhir/uv/sdc/StructureDefinition/sdc-questionnaire-keyboard',
    });

    // sdc-questionnaire-keyboard codes as the inputmode hint browsers understand.
    const KEYBOARD_INPUT_MODES = Object.freeze({ phone: 'tel', email: 'email', number: 'decimal', url: 'url', chat: 'text' });

    // cqf-expression on the item elements the runtime evaluates (_required, _readOnly, _text).
    const DYNAMIC_ELEMENTS = Object.freeze(['required', 'readOnly', 'text']);

    // Extensions SDC 4.0.0's Advanced Rendering and Advanced Behavior
    // profiles list that this runtime doesn't act on. Those that change what
    // is captured or valid get a visible warning; presentation-only ones a
    // console note. Population and extraction extensions (launchContext,
    // observationExtract ...) are left out: they don't change the form shown.
    const SDC_BASE = 'http://hl7.org/fhir/uv/sdc/StructureDefinition/';
    const CORE_BASE = 'http://hl7.org/fhir/StructureDefinition/';
    const UNSUPPORTED_BEHAVIOR_EXTENSIONS = Object.freeze([
        SDC_BASE + 'sdc-questionnaire-candidateExpression',
        SDC_BASE + 'sdc-questionnaire-lookupQuestionnaire',
        CORE_BASE + 'cqf-expression',
        CORE_BASE + 'questionnaire-referenceProfile',
    ]);
    const UNSUPPORTED_PRESENTATION_EXTENSIONS = Object.freeze([
        SDC_BASE + 'sdc-questionnaire-choiceColumn',
        SDC_BASE + 'sdc-questionnaire-entryMode',
        CORE_BASE + 'rendering-style',
    ]);

    // FHIR canonical form is url|version#fragment: the version goes before any
    // fragment the url carries (QuestionnaireCanonical::forResponse() matches).
    function canonicalWithVersion(url, version) {
        if (version === '') {
            return url;
        }
        const hash = url.indexOf('#');
        return hash === -1 ? url + '|' + version : url.slice(0, hash) + '|' + version + url.slice(hash);
    }

    function hasOwn(object, key) {
        return object !== null && typeof object === 'object' && Object.prototype.hasOwnProperty.call(object, key);
    }

    // Defines an own property even for names such as __proto__, so a
    // questionnaire variable can never change an environment's prototype.
    function defineVariable(environment, name, value) {
        Object.defineProperty(environment, name, { value, writable: true, enumerable: true, configurable: true });
    }

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

    // A collection as an operand of and, or, xor and implies: true, false, or null for empty
    // (FHIRPath's three-valued logic, where empty means unknown).
    function logicValue(value) {
        const collection = asCollection(value);
        return collection.length === 0 ? null : Boolean(collection[0]);
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

    // FHIRPath = on two collections: true, false, or null (empty) when either is empty or an
    // item's comparison is unknown. Items are compared in order, and collections of different
    // sizes are unequal.
    function collectionsEqual(left, right) {
        const leftCollection = asCollection(left);
        const rightCollection = asCollection(right);
        if (leftCollection.length === 0 || rightCollection.length === 0) {
            return null;
        }
        if (leftCollection.length !== rightCollection.length) {
            return false;
        }
        const results = leftCollection.map((value, index) => itemsEqual(value, rightCollection[index]));
        if (results.includes(false)) {
            return false;
        }
        return results.includes(null) ? null : true;
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

    // Display-item text controls that are not rendered in place (FHIR
    // questionnaire-item-control): 'help' and 'legal' are shown "in a dialog
    // box or similar control if invoked by pushing a button"; 'flyover' is
    // "temporarily visible over top of an item" on hover.
    const DETACHED_DISPLAY_CONTROLS = Object.freeze(['help', 'legal', 'flyover']);
    const RENDERING_MARKDOWN = 'http://hl7.org/fhir/StructureDefinition/rendering-markdown';

    function isDetachedDisplay(item) {
        return item?.type === 'display' && DETACHED_DISPLAY_CONTROLS.includes(getItemControl(item));
    }

    // Display-item text controls shown beside a question's answer area
    // rather than as their own paragraph: 'prompt' "below or within the
    // answer-entry area", 'unit' "adjacent to the answer space", 'lower' and
    // 'upper' to the left and right of the answer choices. Under a group they
    // render as ordinary text.
    const ADJACENT_DISPLAY_CONTROLS = Object.freeze(['prompt', 'unit', 'lower', 'upper']);

    function isAdjacentDisplay(item) {
        return item?.type === 'display' && ADJACENT_DISPLAY_CONTROLS.includes(getItemControl(item));
    }

    // The first help / legal / flyover display child of an item, by control.
    function getDisplayControls(item) {
        const controls = { help: null, legal: null, flyover: null };
        for (const child of item?.item ?? []) {
            if (isDetachedDisplay(child)) {
                const control = getItemControl(child);
                controls[control] = controls[control] ?? child;
            }
        }
        return controls;
    }

    // rendering-markdown on a primitive (_text, _prefix, _display, _valueString),
    // which SDC's Advanced Rendering lists for titles, prefixes, item text and
    // answer options. The plain value stays the fallback.
    function markdownFor(holder, key) {
        const markdown = (holder?.['_' + key]?.extension ?? []).find((extension) => extension?.url === RENDERING_MARKDOWN)?.valueMarkdown;
        return typeof markdown === 'string' && markdown.trim() !== '' ? markdown : null;
    }

    // Inline markdown for a label (prefix kept as plain text before it), else the plain text.
    function setInlineText(element, plain, markdown, prefix = '') {
        if (markdown === null) {
            element.textContent = plain;
            return;
        }
        element.replaceChildren(...(prefix !== '' ? [document.createTextNode(prefix)] : []), ...parseMarkdownInline(markdown, document));
    }

    function answerOptionMarkdown(option) {
        return markdownFor(option, 'valueString') ?? markdownFor(option?.valueCoding, 'display');
    }

    // An itemMedia / itemAnswerMedia Attachment: an image when it is one and
    // its source is safe (http(s) url or inline data), else a link to it.
    function mediaElement(attachment, alt) {
        if (!attachment || typeof attachment !== 'object') {
            return null;
        }
        const contentType = typeof attachment.contentType === 'string' ? attachment.contentType : '';
        const source = typeof attachment.data === 'string' && attachment.data !== '' && /^image\//.test(contentType)
            ? 'data:' + contentType + ';base64,' + attachment.data
            : markdownSafeHref(attachment.url);
        if (source === null || source.startsWith('mailto:') || source.startsWith('tel:')) {
            return null;
        }
        const label = typeof attachment.title === 'string' && attachment.title !== '' ? attachment.title : alt;
        if (contentType === '' || contentType.startsWith('image/')) {
            const image = document.createElement('img');
            image.className = 'oe-questionnaire-media';
            image.src = source;
            image.alt = label ?? '';
            return image;
        }
        const link = document.createElement('a');
        link.className = 'oe-questionnaire-media-link';
        link.href = source;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        link.textContent = label || source;
        return link;
    }

    // Plain display text, or GFM when the text carries the rendering-markdown
    // extension (_text.extension). rendering-xhtml is not trusted as markup;
    // the plain text is used instead.
    function plainDisplayText(item) {
        return typeof item?.text === 'string' ? item.text : '';
    }

    function renderDisplayText(item, doc) {
        const markdown = (item?._text?.extension ?? []).find((extension) => extension?.url === RENDERING_MARKDOWN)?.valueMarkdown;
        if (typeof markdown === 'string' && markdown.trim() !== '') {
            const wrapper = doc.createElement('div');
            wrapper.className = 'oe-questionnaire-markdown';
            wrapper.appendChild(renderMarkdown(markdown, doc));
            return wrapper;
        }
        const text = doc.createElement('div');
        text.className = 'oe-questionnaire-plain-text';
        text.textContent = plainDisplayText(item);
        return text;
    }

    // R4 lets answerOption constrain integer, date, time, string and Coding
    // items, not only choice/open-choice (LForms exports drop-downs as string
    // items with answerOption). Those render with the option controls.
    const OPTION_CAPABLE_TYPES = Object.freeze(['string', 'text', 'integer', 'decimal', 'date', 'dateTime', 'time', 'coding']);

    // questionnaire-answerConstraint (R5 backport): optionsOrString /
    // optionsOrType allow a free-text answer beside the list; the default,
    // optionsOnly, does not.
    function allowsFreeTextAnswer(item) {
        const constraint = getStringExtension(item, EXTENSIONS.ANSWER_CONSTRAINT);
        return constraint === 'optionsOrString' || constraint === 'optionsOrType';
    }

    // Option label with its questionnaire-optionPrefix, as the prefix is written: "a) Never married",
    // "Prefix = Option 1". Nothing is added to the prefix (a period after it was LForms's habit).
    function answerOptionLabel(option, optionAnswer) {
        const text = answerDisplay(optionAnswer);
        const prefix = getStringExtension(option, EXTENSIONS.OPTION_PREFIX).trim();
        return prefix === '' ? text : prefix + ' ' + text;
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
        // itemWeight (which replaces the deprecated ordinalValue) or
        // ordinalValue, on the answerOption itself or inside its Coding (the
        // latter when the option came from a ValueSet expansion entry).
        const extension = findExtension(node, EXTENSIONS.ITEM_WEIGHT)
            ?? findExtension(node?.valueCoding, EXTENSIONS.ITEM_WEIGHT)
            ?? findExtension(node, EXTENSIONS.ORDINAL_VALUE)
            ?? findExtension(node?.valueCoding, EXTENSIONS.ORDINAL_VALUE);
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
                    // Keep the expansion entry's extensions (e.g. ordinalValue)
                    // inside the Coding so they survive into the selected answer
                    // and are reachable via FHIRPath select(valueCoding).extension.
                    if (Array.isArray(entry.extension)) {
                        coding.extension = deepClone(entry.extension);
                    }
                    options.push({ valueCoding: coding });
                }
                walkContains(entry?.contains);
            }
        };
        walkContains(valueSet?.expansion?.contains);
        return options;
    }

    // A ValueSet's compose read without a terminology service: only when every include lists
    // its concepts (no filter, no whole code system, no other value set). Excluded concepts are
    // left out. Anything else gives no options.
    function composeToAnswerOptions(valueSet) {
        const includes = valueSet?.compose?.include ?? [];
        if (includes.length === 0 || includes.some((include) => !Array.isArray(include?.concept) || include.filter || include.valueSet)) {
            return [];
        }
        const excluded = new Set((valueSet.compose.exclude ?? []).flatMap((exclude) => (exclude?.concept ?? [])
            .map((concept) => (exclude.system ?? '') + '|' + concept?.code)));
        const options = [];
        for (const include of includes) {
            for (const concept of include.concept) {
                if (concept?.code === undefined || excluded.has((include.system ?? '') + '|' + concept.code)) {
                    continue;
                }
                const coding = {};
                if (typeof include.system === 'string') {
                    coding.system = include.system;
                }
                if (typeof include.version === 'string') {
                    coding.version = include.version;
                }
                coding.code = concept.code;
                if (typeof concept.display === 'string') {
                    coding.display = concept.display;
                }
                if (Array.isArray(concept.extension)) {
                    coding.extension = deepClone(concept.extension);
                }
                options.push({ valueCoding: coding });
            }
        }
        return options;
    }

    function valueSetToAnswerOptions(valueSet) {
        return valueSet?.expansion ? expansionToAnswerOptions(valueSet) : composeToAnswerOptions(valueSet);
    }

    function resolveAnswerOptions(item, questionnaire) {
        if (Array.isArray(item.answerOption) && item.answerOption.length > 0) {
            return item.answerOption;
        }
        if (typeof item.answerValueSet === 'string' && item.answerValueSet !== '') {
            const valueSet = containedResourceById(questionnaire, item.answerValueSet);
            if (valueSet && valueSet.resourceType === 'ValueSet') {
                return valueSetToAnswerOptions(valueSet);
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

    // The mimeType extension repeats: one valueCode per allowed type.
    function attachmentMimeTypes(item) {
        return findExtensions(item, EXTENSIONS.MIME_TYPE)
            .map((extension) => extension?.valueCode)
            .filter((code) => typeof code === 'string' && code !== '');
    }

    function isExclusiveOption(option) {
        return getBooleanExtension(option, EXTENSIONS.OPTION_EXCLUSIVE) === true;
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

    // questionnaire-constraint (R4) and targetConstraint (its successor in
    // SDC 4.0.0, whose expression is an Expression rather than a string).
    function getConstraints(item) {
        return [...findExtensions(item, EXTENSIONS.CONSTRAINT), ...findExtensions(item, EXTENSIONS.TARGET_CONSTRAINT)].map((extension) => {
            const parts = extension?.extension ?? [];
            const read = (url) => extensionValueX(parts.find((part) => part?.url === url));
            const expression = read('expression');
            return {
                key: read('key'),
                severity: read('severity') ?? 'error',
                human: read('human') ?? 'Value does not satisfy a constraint',
                expression: expression !== null && typeof expression === 'object'
                    ? (expression.language === 'text/fhirpath' ? expression.expression : undefined)
                    : expression,
            };
        }).filter((constraint) => typeof constraint.expression === 'string' && constraint.expression !== '');
    }

    // An item element's own expression (cqf-expression on _required, ...).
    function elementExpression(item, element) {
        const expression = (item?.['_' + element]?.extension ?? []).find((extension) => extension?.url === EXTENSIONS.CQF_EXPRESSION)?.valueExpression;
        return expression && typeof expression === 'object' ? expression : null;
    }

    // answerOptionsToggleExpression: the options it names are available only while its expression is true.
    function getOptionToggles(item) {
        return findExtensions(item, EXTENSIONS.ANSWER_OPTIONS_TOGGLE).map((extension) => {
            const parts = extension?.extension ?? [];
            return {
                options: parts.filter((part) => part?.url === 'option').map(extensionValueX).filter((value) => value !== undefined),
                expression: parts.find((part) => part?.url === 'expression')?.valueExpression ?? null,
            };
        }).filter((toggle) => toggle.expression && toggle.options.length > 0);
    }

    // answerExpression results as answerOptions.
    function valuesToAnswerOptions(values, item) {
        return asCollection(values).map((value) => {
            if (value !== null && typeof value === 'object') {
                return 'reference' in value ? { valueReference: value } : { valueCoding: value };
            }
            if (typeof value === 'number') {
                return Number.isInteger(value) ? { valueInteger: value } : { valueString: String(value) };
            }
            if (typeof value === 'string' && (item.type === 'date' || item.type === 'time')) {
                return item.type === 'date' ? { valueDate: value } : { valueTime: toFhirTime(value) };
            }
            return { valueString: String(value) };
        });
    }

    // The renderer's node for a signature pad: not a linkId, so it never collides with one.
    function signatureNodeKey(key) {
        return INSTANCE_SEPARATOR + 'signature' + INSTANCE_SEPARATOR + key;
    }

    // UTF-8 text to and from base64, without TextEncoder (not in every embedding).
    function utf8Base64(text) {
        return btoa(encodeURIComponent(text).replace(/%([0-9A-F]{2})/g, (match, hex) => String.fromCharCode(Number.parseInt(hex, 16))));
    }

    function base64Utf8(data) {
        return decodeURIComponent(Array.from(atob(data), (character) => '%' + character.charCodeAt(0).toString(16).padStart(2, '0')).join(''));
    }

    // SDC modular forms: each display item carrying subQuestionnaire is
    // replaced by the referenced Questionnaire's items, from the forms the
    // host supplies (options.subQuestionnaires, by canonical, with or without
    // |version), recursively; their contained resources join the form's.
    // One the host didn't supply stays as it is, and is reported.
    function assembleQuestionnaire(questionnaire, supplied) {
        const lookup = (canonical) => supplied[canonical] ?? supplied[String(canonical).split('|')[0]] ?? null;
        const contained = [...(questionnaire.contained ?? [])];
        const seen = new Set();
        const expand = (items) => (items ?? []).flatMap((item) => {
            const canonical = findExtension(item, EXTENSIONS.SUB_QUESTIONNAIRE)?.valueCanonical;
            const sub = item.type === 'display' && typeof canonical === 'string' && !seen.has(canonical) ? lookup(canonical) : null;
            if (sub && Array.isArray(sub.item)) {
                seen.add(canonical);
                for (const resource of sub.contained ?? []) {
                    if (!contained.some((existing) => existing?.id === resource?.id)) {
                        contained.push(deepClone(resource));
                    }
                }
                const inlined = expand(deepClone(sub.item));
                seen.delete(canonical);
                return inlined;
            }
            return [Array.isArray(item.item) ? { ...item, item: expand(item.item) } : item];
        });
        const assembled = { ...questionnaire, item: expand(questionnaire.item) };
        if (contained.length > 0) {
            assembled.contained = contained;
        }
        return assembled;
    }

    // usageMode display / display-non-empty: shown when reviewing a form, not while filling it.
    function isDisplayOnly(item) {
        return ['display', 'display-non-empty'].includes(getStringExtension(item, EXTENSIONS.USAGE_MODE));
    }

    function referenceTypes(item) {
        return findExtensions(item, EXTENSIONS.REFERENCE_RESOURCE)
            .map((extension) => extension?.valueCode)
            .filter((code) => typeof code === 'string' && code !== '');
    }

    function getAnswerOptionValue(answerOption) {
        if (!answerOption || typeof answerOption !== 'object') {
            return null;
        }
        const key = Object.keys(answerOption).find((candidate) => candidate.startsWith('value'));
        if (!key) {
            return null;
        }
        const value = deepClone(answerOption[key]);
        // Only ordinalValue travels from the option into the response. Other
        // option extensions (questionnaire-optionPrefix, rendering-*, ...) are
        // defined on Questionnaire.item.answerOption and do not belong on a
        // QuestionnaireResponse answer.
        const carried = findExtensions(answerOption, EXTENSIONS.ORDINAL_VALUE);
        const hasExtension = carried.length > 0;

        // An ordinal-bearing valueString option is a scored coded answer: the
        // ordinalValue extension exists to weight it for scoring, and SDC
        // scoring expressions read it via select(valueCoding).extension. Store
        // it as a Coding (display = the string, ordinalValue inside the coding)
        // so it participates in those sums, matching LForms output. Plain string
        // options (no ordinalValue) are left as valueString.
        if (key === 'valueString' && hasExtension) {
            return {
                valueKey: 'valueCoding',
                value: { display: value, extension: deepClone(carried) },
            };
        }

        const result = { valueKey: key, value };
        // For object values (Coding/Quantity) nest ordinalValue inside the value
        // so it round-trips in that object; for primitives keep it on the answer
        // so it serializes as a FHIR _value[x] sibling.
        if (hasExtension) {
            if (value && typeof value === 'object' && !Array.isArray(value)) {
                if (!Array.isArray(value.extension)) {
                    value.extension = deepClone(carried);
                }
            } else {
                result.extension = deepClone(carried);
            }
        }
        return result;
    }

    function getAnswerValue(answer) {
        if (!answer || typeof answer !== 'object') {
            return null;
        }
        const key = Object.keys(answer).find((candidate) => candidate.startsWith('value'));
        if (!key) {
            return null;
        }
        const result = { valueKey: key, value: deepClone(answer[key]) };
        // Round-trip a primitive value's extension from its _value[x] sibling.
        const sibling = answer['_' + key];
        if (sibling && Array.isArray(sibling.extension)) {
            result.extension = deepClone(sibling.extension);
        }
        return result;
    }

    // LHC-Forms (OpenEMR 8.2 and earlier) saved an answer to a choice with string options
    // (answerOption.valueString) as a Coding holding only the text, valueCoding { display: 'Yes' }.
    // Read it as the string option it stands for, so it shows as chosen and enableWhen on
    // answerString matches it.
    function fromLhcFormsAnswer(item, value) {
        const coding = value?.valueKey === 'valueCoding' ? value.value : null;
        if (coding === null || typeof coding !== 'object' || typeof coding.display !== 'string'
            || coding.code !== undefined || coding.system !== undefined
            || (item?.type !== 'choice' && item?.type !== 'open-choice')) {
            return value;
        }
        const isOption = (item.answerOption ?? []).some((option) => option?.valueString === coding.display);
        return isOption ? { valueKey: 'valueString', value: coding.display } : value;
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

    // FHIR R4 time is hh:mm:ss: "Seconds must be provided due to schema type constraints but may be
    // zero-filled". An HTML time input gives hh:mm, so a time answer gains ':00'.
    function toFhirTime(value) {
        return typeof value === 'string' && /^\d{2}:\d{2}$/.test(value) ? value + ':00' : value;
    }

    // A time answer as a time input shows it: hh:mm when its seconds are zero.
    function formatTimeInputValue(value) {
        if (typeof value !== 'string') {
            return '';
        }
        return /^\d{2}:\d{2}:00(?:\.0+)?$/.test(value) ? value.slice(0, 5) : value;
    }

    // FHIRPath calendar durations (singular and plural) and the UCUM units
    // that date arithmetic accepts in their place, by the field they move.
    const CALENDAR_UNITS = Object.freeze({
        year: 'year', years: 'year', month: 'month', months: 'month', week: 'week', weeks: 'week',
        day: 'day', days: 'day', hour: 'hour', hours: 'hour', minute: 'minute', minutes: 'minute',
        second: 'second', seconds: 'second',
    });
    const UCUM_CALENDAR_UNITS = Object.freeze({ a: 'year', mo: 'month', wk: 'week', d: 'day', h: 'hour', min: 'minute', s: 'second' });
    const FHIR_DATE_PATTERN = /^(\d{4})(?:-(\d{2})(?:-(\d{2}))?)?(?:T(\d{2}):(\d{2})(?::(\d{2})(\.\d+)?)?(Z|[+-]\d{2}:\d{2})?)?$/;

    function fhirToday(now = new Date()) {
        return now.getFullYear() + '-' + padDateTimePart(now.getMonth() + 1) + '-' + padDateTimePart(now.getDate());
    }

    function fhirNow(now = new Date()) {
        return toFhirDateTime(fhirToday(now) + 'T' + padDateTimePart(now.getHours()) + ':'
            + padDateTimePart(now.getMinutes()) + ':' + padDateTimePart(now.getSeconds()));
    }

    const SECONDS_IN = Object.freeze({ week: 604800, day: 86400, hour: 3600, minute: 60, second: 1 });

    // A FHIR date or dateTime moved by a calendar duration, kept at its own
    // precision. A month or year past the end of a shorter month lands on its
    // last day (2026-01-31 + 1 month = 2026-02-28). A unit finer than the
    // value's precision is converted to that precision and truncated, by
    // FHIRPath's factors (1 year = 12 months or 365 days, 1 month = 30 days),
    // so @2026-01 + 45 days is @2026-02 and @2026 + 1 day is @2026. Anything
    // that isn't a date gives null.
    function shiftFhirDate(value, quantity, sign) {
        const match = typeof value === 'string' ? FHIR_DATE_PATTERN.exec(value) : null;
        let unit = CALENDAR_UNITS[String(quantity?.unit ?? '').toLowerCase()] ?? UCUM_CALENDAR_UNITS[quantity?.unit] ?? null;
        let amount = sign * Math.trunc(Number(quantity?.value));
        if (!match || unit === null || !Number.isFinite(amount)) {
            return null;
        }
        const [, year, month, day, hour, minute, second, fraction = '', zone = ''] = match;
        const precision = second !== undefined ? 'second' : (hour !== undefined ? 'minute' : (day !== undefined ? 'day' : (month !== undefined ? 'month' : 'year')));
        if (precision === 'year' && unit !== 'year') {
            amount = Math.trunc(unit === 'month' ? amount / 12 : amount * SECONDS_IN[unit] / (365 * 86400));
            unit = 'year';
        } else if (precision === 'month' && unit !== 'year' && unit !== 'month') {
            amount = Math.trunc(amount * SECONDS_IN[unit] / (30 * 86400));
            unit = 'month';
        } else if (precision === 'day' && SECONDS_IN[unit] < 86400) {
            amount = Math.trunc(amount * SECONDS_IN[unit] / 86400);
            unit = 'day';
        } else if (precision === 'minute' && unit === 'second') {
            amount = Math.trunc(amount / 60);
            unit = 'minute';
        }
        let date;
        if (unit === 'year' || unit === 'month') {
            const months = Number(year) * 12 + (month !== undefined ? Number(month) - 1 : 0) + (unit === 'year' ? 12 * amount : amount);
            const newYear = Math.floor(months / 12);
            const newMonth = ((months % 12) + 12) % 12;
            const lastDay = new Date(Date.UTC(newYear, newMonth + 1, 0)).getUTCDate();
            date = new Date(Date.UTC(newYear, newMonth, Math.min(day !== undefined ? Number(day) : 1, lastDay),
                Number(hour ?? 0), Number(minute ?? 0), Number(second ?? 0)));
        } else {
            date = new Date(Date.UTC(Number(year), Number(month) - 1, Number(day), Number(hour ?? 0), Number(minute ?? 0), Number(second ?? 0)));
            date = new Date(date.getTime() + amount * SECONDS_IN[unit] * 1000);
        }
        const yearText = String(date.getUTCFullYear()).padStart(4, '0');
        const monthText = padDateTimePart(date.getUTCMonth() + 1);
        const dayText = padDateTimePart(date.getUTCDate());
        if (precision === 'year') return yearText;
        if (precision === 'month') return yearText + '-' + monthText;
        if (precision === 'day') return yearText + '-' + monthText + '-' + dayText;
        return yearText + '-' + monthText + '-' + dayText + 'T' + padDateTimePart(date.getUTCHours()) + ':' + padDateTimePart(date.getUTCMinutes())
            + (second !== undefined ? ':' + padDateTimePart(date.getUTCSeconds()) + fraction : '') + zone;
    }

    // A date, dateTime or time as FHIRPath compares it: its fields from the year (for a time, the
    // hour) down to its own precision, seconds and milliseconds as one decimal field, and its
    // offset. Null for anything else.
    function temporalValue(value) {
        if (typeof value !== 'string') {
            return null;
        }
        const time = /^(\d{2}):(\d{2})(?::(\d{2}(?:\.\d+)?))?$/.exec(value);
        if (time) {
            return { time: true, fields: time.slice(1).filter((field) => field !== undefined).map(Number), zone: null };
        }
        const match = FHIR_DATE_PATTERN.exec(value);
        if (!match) {
            return null;
        }
        const [, year, month, day, hour, minute, second, fraction = '', zone] = match;
        const fields = [year, month, day, hour, minute, second === undefined ? undefined : second + fraction];
        const end = fields.indexOf(undefined);
        return { time: false, fields: (end === -1 ? fields : fields.slice(0, end)).map(Number), zone: zone ?? null };
    }

    // A dateTime's fields moved to UTC; with no offset it is read as local time.
    function utcFields(temporal) {
        const [year, month, day, hour, minute, ...seconds] = temporal.fields;
        let offset;
        if (temporal.zone === null) {
            offset = -new Date(year, month - 1, day, hour, minute).getTimezoneOffset();
        } else {
            offset = temporal.zone === 'Z' ? 0 : (temporal.zone[0] === '-' ? -1 : 1) * (Number(temporal.zone.slice(1, 3)) * 60 + Number(temporal.zone.slice(4, 6)));
        }
        const utc = new Date(Date.UTC(year, month - 1, day, hour, minute) - offset * 60000);
        return [utc.getUTCFullYear(), utc.getUTCMonth() + 1, utc.getUTCDate(), utc.getUTCHours(), utc.getUTCMinutes(), ...seconds];
    }

    // FHIRPath's ordering of two dates, dateTimes or times: -1, 0 or 1, field by field from the
    // largest. Null when they agree as far as one value's precision goes and the other goes
    // further ("the comparison stops and the result is empty"): @2026 against @2026-01, or a date
    // against a dateTime on that day. Two times of day compare in UTC when either has an offset.
    // Undefined when the two aren't both dates and dateTimes, or both times.
    function compareTemporal(left, right) {
        const a = temporalValue(left);
        const b = temporalValue(right);
        if (a === null || b === null || a.time !== b.time) {
            return undefined;
        }
        const inUtc = !a.time && a.fields.length > 3 && b.fields.length > 3 && (a.zone !== null || b.zone !== null);
        const fieldsA = inUtc ? utcFields(a) : a.fields;
        const fieldsB = inUtc ? utcFields(b) : b.fields;
        for (let index = 0; index < Math.min(fieldsA.length, fieldsB.length); index++) {
            if (fieldsA[index] !== fieldsB[index]) {
                return fieldsA[index] < fieldsB[index] ? -1 : 1;
            }
        }
        return fieldsA.length === fieldsB.length ? 0 : null;
    }

    // FHIRPath quantities ({ value, unit, code }, as answers and literals carry them). A plain
    // number, or a quantity answer with no unit, is a quantity in unit '1'. A quoted calendar word
    // counts as its UCUM code (4 'weeks' = 4 'wk'), a leniency for forms such as WHO SMART's, which
    // compare answers in 'wk' with 104 'weeks'. A calendar keyword (4 days) is a calendar duration:
    // above seconds it is unequal to a UCUM duration, and the two don't compare (FHIRPath:
    // "1 year = 1 'a' // false", "1 second = 1 's' // true").
    const UCUM_FOR_CALENDAR = Object.freeze({ year: 'a', month: 'mo', week: 'wk', day: 'd', hour: 'h', minute: 'min', second: 's' });

    // The calendar durations FHIRPath literals write, kept apart from the objects so the mark
    // never reaches a saved answer.
    const CALENDAR_DURATIONS = new WeakSet();

    function calendarDuration(value, unit) {
        const duration = { value, unit };
        CALENDAR_DURATIONS.add(duration);
        return duration;
    }

    function isQuantity(value) {
        return value !== null && typeof value === 'object' && typeof value.value === 'number';
    }

    function quantityUnit(quantity) {
        if (CALENDAR_DURATIONS.has(quantity)) {
            return quantity.unit === 'second' ? 's' : 'calendar ' + quantity.unit;
        }
        const unit = String(quantity.code ?? quantity.unit ?? '1');
        return UCUM_FOR_CALENDAR[CALENDAR_UNITS[unit.toLowerCase()]] ?? unit;
    }

    // [left value, right value] when both are numbers or quantities in one unit; false for a
    // calendar duration against a UCUM one above seconds (unequal, and not ordered); null when
    // their units otherwise differ (FHIRPath gives empty); undefined when either is something else.
    function comparableQuantities(left, right) {
        const asQuantity = (value) => (typeof value === 'number' ? { value, unit: '1' } : (isQuantity(value) ? value : null));
        const a = asQuantity(left);
        const b = asQuantity(right);
        if (a === null || b === null) {
            return undefined;
        }
        const unitA = quantityUnit(a);
        const unitB = quantityUnit(b);
        if (unitA === unitB) {
            return [a.value, b.value];
        }
        const calendarAgainstUcum = (calendar, other) => calendar.startsWith('calendar ') && UCUM_CALENDAR_UNITS[other] !== undefined;
        return calendarAgainstUcum(unitA, unitB) || calendarAgainstUcum(unitB, unitA) ? false : null;
    }

    // FHIRPath = on two items: true, false, or null (empty) when the comparison is unknown. A
    // quantity against a number or quantity compares by value when the units match and is unknown
    // when they don't; dates and times compare as compareTemporal does; anything else compares as
    // equalPrimitive does.
    function itemsEqual(left, right) {
        if (isQuantity(left) || isQuantity(right)) {
            const pair = comparableQuantities(left, right);
            if (pair !== undefined) {
                return pair === null || pair === false ? pair : pair[0] === pair[1];
            }
        }
        const order = compareTemporal(left, right);
        if (order !== undefined) {
            return order === null ? null : order === 0;
        }
        return equalPrimitive(left, right);
    }

    function quantityArithmetic(operator, left, right) {
        const result = (value, unitOf) => [CALENDAR_DURATIONS.has(unitOf) ? calendarDuration(value, unitOf.unit) : {
            value,
            ...((unitOf.unit ?? unitOf.code) !== undefined ? { unit: unitOf.unit ?? unitOf.code } : {}),
            ...(unitOf.code !== undefined ? { code: unitOf.code } : {}),
            ...(unitOf.system !== undefined ? { system: unitOf.system } : {}),
        }];
        if (operator === '+' || operator === '-') {
            const pair = comparableQuantities(left, right);
            if (!pair) {
                return [];
            }
            return result(operator === '+' ? pair[0] + pair[1] : pair[0] - pair[1], isQuantity(left) ? left : right);
        }
        if (isQuantity(left) && typeof right === 'number') {
            if (operator === '/' && right === 0) {
                return [];
            }
            return result(operator === '*' ? left.value * right : left.value / right, left);
        }
        if (operator === '*' && typeof left === 'number' && isQuantity(right)) {
            return result(left * right.value, right);
        }
        return [];
    }

    // FHIRPath's type test (is, as, ofType) over the plain JSON the runtime
    // evaluates. Dates are JSON strings, so a string that reads as a date,
    // dateTime or time counts as that type rather than as a string.
    const FHIR_TIME_PATTERN = /^\d{2}:\d{2}(?::\d{2}(?:\.\d+)?)?$/;
    function fhirPathTypeMatches(value, typeName) {
        const name = String(typeName).toLowerCase();
        const isDateLike = typeof value === 'string' && (FHIR_DATE_PATTERN.test(value) || FHIR_TIME_PATTERN.test(value));
        switch (name) {
            case 'boolean':
                return typeof value === 'boolean';
            case 'integer':
                return typeof value === 'number' && Number.isInteger(value);
            case 'decimal':
                return typeof value === 'number';
            case 'string':
            case 'code':
            case 'uri':
            case 'url':
            case 'canonical':
            case 'id':
            case 'markdown':
                return typeof value === 'string' && !isDateLike;
            case 'date':
                return typeof value === 'string' && FHIR_DATE_PATTERN.test(value) && !value.includes('T');
            case 'datetime':
                return typeof value === 'string' && FHIR_DATE_PATTERN.test(value);
            case 'time':
                return typeof value === 'string' && FHIR_TIME_PATTERN.test(value);
            case 'coding':
                return value !== null && typeof value === 'object' && ('code' in value || 'system' in value) && !('value' in value);
            case 'quantity':
                return value !== null && typeof value === 'object' && typeof value.value === 'number';
            case 'reference':
                return value !== null && typeof value === 'object' && typeof value.reference === 'string';
            case 'attachment':
                return value !== null && typeof value === 'object' && ('contentType' in value || 'data' in value || 'url' in value) && !('reference' in value);
            default:
                return value !== null && typeof value === 'object' && value.resourceType === typeName;
        }
    }

    // A Coding's weight (SDC weight(), formerly ordinal()): its own itemWeight
    // or ordinalValue, else that of the answerOption carrying the same code
    // anywhere in the Questionnaire.
    function codingWeight(coding, questionnaire) {
        const own = getOrdinalValue({ valueCoding: coding });
        if (own !== null) {
            return own;
        }
        const visit = (items) => {
            for (const item of items ?? []) {
                for (const option of item.answerOption ?? []) {
                    if (option.valueCoding && equalPrimitive(option.valueCoding, coding)) {
                        const weight = getOrdinalValue(option);
                        if (weight !== null) {
                            return weight;
                        }
                    }
                }
                const nested = visit(item.item);
                if (nested !== null) {
                    return nested;
                }
            }
            return null;
        };
        return coding !== null && typeof coding === 'object' ? visit(questionnaire?.item) : null;
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
                        hidden: isHiddenItem(item) || isDisplayOnly(item),
                        calculatedExpression: findExtension(item, EXTENSIONS.CALCULATED_EXPRESSION)?.valueExpression ?? null,
                        initialExpression: findExtension(item, EXTENSIONS.INITIAL_EXPRESSION)?.valueExpression ?? null,
                        enableWhenExpression: findExtension(item, EXTENSIONS.ENABLE_WHEN_EXPRESSION)?.valueExpression ?? null,
                        constraints: getConstraints(item),
                        requiredExpression: elementExpression(item, 'required'),
                        readOnlyExpression: elementExpression(item, 'readOnly'),
                        textExpression: elementExpression(item, 'text'),
                        answerExpression: findExtension(item, EXTENSIONS.ANSWER_EXPRESSION)?.valueExpression ?? null,
                        optionToggles: getOptionToggles(item),
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
                        || compiledItem.requiredExpression
                        || compiledItem.readOnlyExpression
                        || compiledItem.textExpression
                        || compiledItem.answerExpression
                        || compiledItem.optionToggles.length > 0
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
                                    + '" could not be resolved; only contained ValueSets (referenced by #id) with an expansion, or a compose that lists its concepts, are supported.',
                            });
                        }
                    }

                    // answerOption.value[x] is required (1..1). Options without a
                    // value cannot be answered; the item falls back to its
                    // plain input (see renderInput).
                    const answerOptions = Array.isArray(item.answerOption) ? item.answerOption : [];
                    const valuelessOptions = answerOptions.filter((option) => getAnswerOptionValue(option) === null).length;
                    if (valuelessOptions > 0) {
                        compiled.warnings.push({
                            code: 'answer-option-missing-value',
                            linkId,
                            severity: 'info',
                            message: valuelessOptions + ' of ' + answerOptions.length
                                + ' answerOption entries have no value[x] and were skipped.',
                        });
                    }

                    const enableWhen = item.enableWhen ?? [];
                    if (enableWhen.length > 1 && !item.enableBehavior) {
                        compiled.warnings.push({
                            code: 'missing-enable-behavior',
                            linkId,
                            severity: 'info',
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
            this.checkExtensions(questionnaire, compiled);
            const signatureType = (holder) => {
                const extension = findExtension(holder, EXTENSIONS.SIGNATURE_REQUIRED);
                return extension ? (extension.valueCodeableConcept?.coding ?? []) : null;
            };
            compiled.signatureRequirements = [];
            if (signatureType(questionnaire) !== null) {
                compiled.signatureRequirements.push({ key: '', type: signatureType(questionnaire) });
            }
            for (const compiledItem of compiled.items) {
                const type = signatureType(compiledItem.item);
                if (type !== null) {
                    compiled.signatureRequirements.push({ key: compiledItem.linkId, type });
                }
            }
            compiled.launchContextNames = findExtensions(questionnaire, EXTENSIONS.LAUNCH_CONTEXT)
                .map((extension) => (extension?.extension ?? []).find((part) => part?.url === 'name'))
                .map((part) => part?.valueCoding?.code ?? part?.valueId)
                .filter((name) => typeof name === 'string' && name !== '');

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

            // Variable scope: a variable extension defined on an item is in scope
            // for that item and all its descendants (FHIRPath %name). Precompute,
            // for each item, the chain of variable-defining ancestors plus self,
            // outermost first, so buildEnvironment can evaluate them in order and
            // let an inner definition shadow an outer one of the same name.
            for (const compiledItem of compiled.items) {
                const scope = [];
                const chain = [];
                let cursor = compiledItem.linkId;
                while (cursor) {
                    chain.unshift(cursor);
                    cursor = compiled.parentByLinkId.get(cursor);
                }
                for (const linkId of chain) {
                    const ancestor = compiled.itemsByLinkId.get(linkId);
                    if (ancestor && ancestor.variables.length > 0) {
                        for (const expression of ancestor.variables) {
                            scope.push({ definingLinkId: linkId, expression });
                        }
                    }
                }
                compiledItem.variableScope = scope;
            }

            return compiled;
        }

        // SDC's rendering-criticalExtension names extensions a form can't be
        // shown without; one this runtime doesn't support stops the render
        // ("should display an error message or warning and refrain from
        // presenting the instance"). Other spec extensions it doesn't act on
        // are reported once each, with the first item that carries them.
        checkExtensions(questionnaire, compiled) {
            const supported = new Set([...Object.values(EXTENSIONS).filter((url) => url !== EXTENSIONS.CQF_EXPRESSION), RENDERING_MARKDOWN, CORE_BASE + 'rendering-xhtml']);
            const critical = [...(questionnaire.extension ?? []), ...(questionnaire.modifierExtension ?? [])]
                .filter((extension) => extension?.url === EXTENSIONS.CRITICAL_EXTENSION)
                .map((extension) => extension.valueCanonical ?? extension.valueUri ?? extension.valueUrl)
                .filter((url) => typeof url === 'string' && url !== '');
            compiled.unsupportedCritical = critical.filter((url) => !supported.has(url.split('|')[0]));
            for (const url of compiled.unsupportedCritical) {
                compiled.warnings.push({
                    code: 'critical-extension-unsupported',
                    severity: 'error',
                    message: 'This form needs ' + url + ', which this renderer does not support, so it is not shown.',
                });
            }

            // R4: "Applications processing a resource are required to check for modifier
            // extensions". One this runtime doesn't understand may change the meaning of the element
            // it sits on, so the form isn't shown either. Contained resources are left to their use.
            const modifiers = new Set();
            const collectModifiers = (node) => {
                if (Array.isArray(node)) {
                    node.forEach(collectModifiers);
                    return;
                }
                if (node === null || typeof node !== 'object') {
                    return;
                }
                for (const extension of node.modifierExtension ?? []) {
                    if (typeof extension?.url === 'string') {
                        modifiers.add(extension.url);
                    }
                }
                for (const [key, value] of Object.entries(node)) {
                    if (key !== 'contained' && key !== 'modifierExtension' && value !== null && typeof value === 'object') {
                        collectModifiers(value);
                    }
                }
            };
            collectModifiers(questionnaire);
            compiled.unsupportedModifiers = [...modifiers].filter((url) => !supported.has(url.split('|')[0]));
            for (const url of compiled.unsupportedModifiers) {
                compiled.warnings.push({
                    code: 'modifier-extension-unsupported',
                    severity: 'error',
                    message: 'This form has a modifier extension, ' + url + ', which this renderer does not understand, so it is not shown.',
                });
            }

            // A subQuestionnaire still here wasn't supplied by the host, so its part of the form is missing.
            for (const compiledItem of compiled.items) {
                const canonical = findExtension(compiledItem.item, EXTENSIONS.SUB_QUESTIONNAIRE)?.valueCanonical;
                if (compiledItem.item.type === 'display' && typeof canonical === 'string') {
                    compiled.warnings.push({
                        code: 'sub-questionnaire-missing',
                        linkId: compiledItem.linkId,
                        message: 'This form includes ' + canonical + ', which was not supplied, so that part is not shown.',
                    });
                }
            }

            const handledControls = ['drop-down', 'check-box', 'radio-button', 'autocomplete', 'slider', 'spinner', 'text-box', 'list',
                'table', 'gtable', 'header', 'footer', 'help', 'legal', 'flyover', 'prompt', 'unit', 'lower', 'upper', 'inline'];
            for (const compiledItem of compiled.items) {
                const itemControl = compiledItem.itemControl;
                if (itemControl !== '' && !handledControls.includes(itemControl)) {
                    compiled.warnings.push({
                        code: 'item-control-unsupported',
                        linkId: compiledItem.linkId,
                        severity: 'info',
                        message: 'Item control "' + itemControl + '" is not supported here; the item is shown the default way.',
                    });
                }
            }

            const firstUse = new Map();
            const evaluatedElements = DYNAMIC_ELEMENTS.map((element) => '_' + element);
            const visit = (node, linkId, key = '') => {
                if (Array.isArray(node)) {
                    node.forEach((child) => visit(child, linkId, key));
                    return;
                }
                if (node === null || typeof node !== 'object') {
                    return;
                }
                const here = typeof node.linkId === 'string' ? node.linkId : linkId;
                for (const extension of [...(node.extension ?? []), ...(node.modifierExtension ?? [])]) {
                    const evaluated = extension?.url === EXTENSIONS.CQF_EXPRESSION && evaluatedElements.includes(key);
                    if (typeof extension?.url === 'string' && !evaluated && !firstUse.has(extension.url)) {
                        firstUse.set(extension.url, here);
                    }
                }
                for (const [childKey, value] of Object.entries(node)) {
                    if (childKey !== 'contained' && value !== null && typeof value === 'object') {
                        visit(value, here, childKey);
                    }
                }
            };
            visit(questionnaire.item, undefined);
            visit(questionnaire.extension ?? [], undefined);
            for (const [url, linkId] of firstUse) {
                const behavior = UNSUPPORTED_BEHAVIOR_EXTENSIONS.includes(url);
                if (!behavior && !UNSUPPORTED_PRESENTATION_EXTENSIONS.includes(url)) {
                    continue;
                }
                compiled.warnings.push({
                    code: 'extension-unsupported',
                    linkId,
                    severity: behavior ? 'warning' : 'info',
                    message: url.slice(url.lastIndexOf('/') + 1) + ' is not supported here, so it has no effect'
                        + (behavior ? ' on what is captured or checked.' : ' on how the form looks.'),
                });
            }
        }
    }

    class QuestionnaireResponseStore {
        constructor(questionnaire, questionnaireResponse = null) {
            this.questionnaire = questionnaire;
            this.originalResponse = questionnaireResponse ? deepClone(questionnaireResponse) : null;
            this.answers = new Map();
            // repeating-group linkId -> instance count (at least 1 when present)
            this.repeatCounts = new Map();
            // Bumped on every change to answers or instance counts, so a built
            // response can be reused until the data behind it changes.
            this.revision = 0;
            this.load(questionnaireResponse);
            this.applyInitialAnswers();
        }

        load(questionnaireResponse) {
            this.revision++;
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
                            .filter((answer) => answer !== null)
                            .map((answer) => fromLhcFormsAnswer(questionnaireItem, answer));
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
            this.revision++;
            this.repeatCounts.set(storageKey, Math.max(count, 1));
        }

        addRepeatInstance(storageKey) {
            this.revision++;
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
            this.revision++;
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
                if (this.answers.delete(linkId)) {
                    this.revision++;
                }
                return;
            }
            // Recalculation rewrites unchanged values on every pass; leave the
            // revision alone then so cached responses stay valid.
            const current = this.answers.get(linkId);
            if (current !== undefined && JSON.stringify(current) === JSON.stringify(normalized)) {
                return;
            }
            this.revision++;
            this.answers.set(linkId, deepClone(normalized));
        }

        setAnswer(linkId, answer) {
            this.setAnswers(linkId, answer ? [answer] : []);
        }

        clearAnswer(linkId) {
            if (this.answers.has(linkId)) {
                this.revision++;
            }
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
                    responseItem.answer = answers.map((answer) => {
                        const entry = { [answer.valueKey]: answer.valueKey === 'valueTime' ? toFhirTime(answer.value) : deepClone(answer.value) };
                        // Primitive-value extensions (e.g. ordinalValue on a
                        // valueString) serialize as the FHIR _value[x] sibling;
                        // object values carry their extension inside the object.
                        if (Array.isArray(answer.extension) && answer.extension.length > 0) {
                            entry['_' + answer.valueKey] = { extension: deepClone(answer.extension) };
                        }
                        return entry;
                    });
                }
                // R4 qrs-1: "Nested item can't be beneath both item and answer". A question's
                // children go under its answer (the first, for a repeating question, since
                // children aren't kept per answer); under item only while it has no answer.
                if (childItems.length > 0) {
                    if (responseItem.answer) {
                        responseItem.answer[0].item = childItems;
                    } else {
                        responseItem.item = childItems;
                    }
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
                    // A '.' not followed by a digit starts a method call (2.power(10)).
                    if (source[index] === '.' && /\d/.test(source[index + 1] ?? '')) {
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

                // Date, dateTime and time literals: @2026-10-23, @2026-10-23T10:30:00Z, @T10:30.
                if (character === '@') {
                    const match = /^@(?:T\d{2}(?::\d{2}(?::\d{2}(?:\.\d+)?)?)?|\d{4}(?:-\d{2}(?:-\d{2})?)?(?:T(?:\d{2}(?::\d{2}(?::\d{2}(?:\.\d+)?)?)?)?(?:Z|[+-]\d{2}:\d{2})?)?)/
                        .exec(source.slice(index));
                    if (!match || match[0] === '@') {
                        throw new Error('Invalid FHIRPath date literal at position ' + index + '.');
                    }
                    // Kept as FHIR writes the value: a time without its 'T' (10:30:00, as a
                    // valueTime answer holds it), a date without a trailing 'T'.
                    const literal = match[0].slice(1);
                    push('date', literal.startsWith('T') ? literal.slice(1) : (literal.endsWith('T') ? literal.slice(0, -1) : literal), index);
                    index += match[0].length;
                    continue;
                }

                const twoCharacter = source.slice(index, index + 2);
                if (['!=', '>=', '<=', '!~'].includes(twoCharacter)) {
                    push('operator', twoCharacter, index);
                    index += 2;
                    continue;
                }

                if (['=', '>', '<', '+', '-', '*', '/', '|', '&', '~'].includes(character)) {
                    push('operator', character, index++);
                    continue;
                }

                if (['%', '.', ',', '(', ')', '{', '}', '[', ']'].includes(character)) {
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
            const expression = this.parseExpression();
            this.expect('eof');
            return expression;
        }

        // FHIRPath operator precedence, loosest first: implies; or, xor; and;
        // in, contains; =, ~, !=, !~; >, <, >=, <=; |; is, as; +, -, &;
        // *, /, div, mod; unary + and -; then invocation and indexing.
        parseExpression() {
            let expression = this.parseOr();
            while (this.consumeKeyword('implies')) {
                expression = { type: 'binary', operator: 'implies', left: expression, right: this.parseOr() };
            }
            return expression;
        }

        consumeOperator(values) {
            const token = this.current();
            if (token.type === 'operator' && values.includes(token.value)) {
                this.index++;
                return token.value;
            }
            return null;
        }

        consumeAnyKeyword(keywords) {
            for (const keyword of keywords) {
                if (this.consumeKeyword(keyword)) {
                    return keyword;
                }
            }
            return null;
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
            let operator;
            while ((operator = this.consumeAnyKeyword(['or', 'xor']))) {
                expression = { type: 'binary', operator, left: expression, right: this.parseAnd() };
            }
            return expression;
        }

        parseAnd() {
            let expression = this.parseMembership();
            while (this.consumeKeyword('and')) {
                expression = { type: 'binary', operator: 'and', left: expression, right: this.parseMembership() };
            }
            return expression;
        }

        parseMembership() {
            let expression = this.parseEquality();
            let operator;
            while ((operator = this.consumeAnyKeyword(['in', 'contains']))) {
                expression = { type: 'binary', operator, left: expression, right: this.parseEquality() };
            }
            return expression;
        }

        parseEquality() {
            let expression = this.parseComparison();
            let operator;
            while ((operator = this.consumeOperator(['=', '!=', '~', '!~']))) {
                expression = { type: 'binary', operator, left: expression, right: this.parseComparison() };
            }
            return expression;
        }

        parseComparison() {
            let expression = this.parseUnion();
            let operator;
            while ((operator = this.consumeOperator(['>', '<', '>=', '<=']))) {
                expression = { type: 'binary', operator, left: expression, right: this.parseUnion() };
            }
            return expression;
        }

        parseUnion() {
            let expression = this.parseTypeOperator();
            while (this.consumeOperator(['|'])) {
                expression = { type: 'binary', operator: '|', left: expression, right: this.parseTypeOperator() };
            }
            return expression;
        }

        parseTypeOperator() {
            let expression = this.parseAdditive();
            let operator;
            while ((operator = this.consumeAnyKeyword(['is', 'as']))) {
                const typeName = this.parseTypeSpecifier();
                expression = { type: 'typeOperator', operator, source: expression, typeName };
            }
            return expression;
        }

        // A type name, optionally namespaced (System.Integer, FHIR.Coding).
        parseTypeSpecifier() {
            let name = this.expect('identifier').value;
            while (this.consume('.')) {
                name = this.expect('identifier').value;
            }
            return name;
        }

        parseAdditive() {
            let expression = this.parseMultiplicative();
            let operator;
            while ((operator = this.consumeOperator(['+', '-', '&']))) {
                expression = { type: 'binary', operator, left: expression, right: this.parseMultiplicative() };
            }
            return expression;
        }

        parseMultiplicative() {
            let expression = this.parseUnary();
            let operator;
            while ((operator = this.consumeOperator(['*', '/']) ?? this.consumeAnyKeyword(['div', 'mod']))) {
                expression = { type: 'binary', operator, left: expression, right: this.parseUnary() };
            }
            return expression;
        }

        parseUnary() {
            const operator = this.consumeOperator(['+', '-']);
            if (operator === '-') {
                return { type: 'binary', operator: '*', left: { type: 'literal', value: -1 }, right: this.parseUnary() };
            }
            return this.parsePostfix();
        }

        parsePostfix() {
            let expression = this.parsePrimary();
            for (;;) {
                if (this.consume('.')) {
                    const name = this.expect('identifier').value;
                    if (this.consume('(')) {
                        const args = this.parseArguments();
                        expression = { type: 'method', name, source: expression, args };
                    } else {
                        expression = { type: 'property', name, source: expression };
                    }
                } else if (this.consume('[')) {
                    const index = this.parseExpression();
                    this.expect(']');
                    expression = { type: 'index', source: expression, index };
                } else {
                    return expression;
                }
            }
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
                // A quantity literal: a number with a calendar duration (4 days)
                // or a quoted UCUM unit (4 'mg').
                const unitToken = this.current();
                if (unitToken.type === 'identifier' && CALENDAR_UNITS[unitToken.value.toLowerCase()] !== undefined) {
                    this.index++;
                    return { type: 'literal', value: calendarDuration(numberToken.value, CALENDAR_UNITS[unitToken.value.toLowerCase()]) };
                }
                if (unitToken.type === 'string') {
                    this.index++;
                    return { type: 'literal', value: { value: numberToken.value, unit: unitToken.value, code: unitToken.value } };
                }
                return { type: 'literal', value: numberToken.value };
            }

            const dateToken = this.consume('date');
            if (dateToken) {
                return { type: 'literal', value: dateToken.value };
            }

            if (this.consume('{')) {
                this.expect('}');
                return { type: 'empty' };
            }

            if (this.consume('(')) {
                const expression = this.parseExpression();
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
                args.push(this.parseExpression());
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
                    // Own properties only: %constructor or %toString must not
                    // reach Object.prototype and be saved as an answer.
                    return asCollection(hasOwn(environment, ast.name) ? environment[ast.name] : undefined);
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
                case 'index': {
                    const collection = asCollection(this.evaluate(ast.source, environment, context));
                    const position = firstValue(this.evaluate(ast.index, environment, context));
                    return Number.isInteger(position) && position >= 0 && position < collection.length ? [collection[position]] : [];
                }
                case 'typeOperator': {
                    const collection = asCollection(this.evaluate(ast.source, environment, context));
                    if (collection.length > 1) {
                        throw new Error('FHIRPath "' + ast.operator + '" needs a single item, not ' + collection.length + '.');
                    }
                    if (ast.operator === 'is') {
                        return collection.length === 0 ? [] : [fhirPathTypeMatches(collection[0], ast.typeName)];
                    }
                    return collection.filter((value) => fhirPathTypeMatches(value, ast.typeName));
                }
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
                let projected = hasOwn(value, property) ? value[property] : undefined;
                if (projected === undefined) {
                    // FHIR choice elements (value[x], effective[x], ...) are
                    // addressed without their type suffix in FHIRPath:
                    // answer.value reaches valueInteger / valueCoding / ...
                    const choiceKey = Object.keys(value).find((key) =>
                        key.length > property.length && key.startsWith(property) && /^[A-Z]/.test(key.charAt(property.length)));
                    projected = choiceKey === undefined ? undefined : value[choiceKey];
                }
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
                defineVariable(scoped, key, environment[key]);
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
                    // A frontier still growing after 1000 levels is a cycle
                    // (repeatAll) or runaway data: report it rather than return
                    // a silently truncated collection.
                    if (frontier.length > 0) {
                        throw new Error('FHIRPath ' + ast.name + '() stopped after 1000 levels without finishing.');
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
                    return this.evaluateMoreMethods(name, ast, asCollection(source), environment, context);
            }
        }

        // FHIRPath 2.0.0 functions beyond the scoped ones above, FHIR's
        // extension(), and SDC's weight() (ordinal() before it).
        evaluateMoreMethods(name, ast, collection, environment, context) {
            const arg = (position) => this.evaluate(ast.args[position], environment, context);
            const argValue = (position) => firstValue(arg(position));
            const typeArgument = () => {
                const node = ast.args[0];
                if (node?.type === 'identifier') return node.name;
                if (node?.type === 'property') return node.name;
                throw new Error('FHIRPath ' + ast.name + '() needs a type name.');
            };
            // Functions defined on a single input: empty in, empty out; more than one is an error.
            const onSingle = (apply) => {
                if (collection.length === 0) return [];
                if (collection.length > 1) {
                    throw new Error('FHIRPath ' + ast.name + '() needs a single item, not ' + collection.length + '.');
                }
                const result = apply(collection[0]);
                return result === null || result === undefined || (typeof result === 'number' && !Number.isFinite(result)) ? [] : [result];
            };
            const onString = (apply) => onSingle((value) => (typeof value === 'string' ? apply(value) : null));
            const onNumber = (apply) => onSingle((value) => (typeof value === 'number' ? apply(value) : null));
            const signature = (value) => JSON.stringify(value);
            const distinct = (values) => {
                const seen = new Set();
                return values.filter((value) => !seen.has(signature(value)) && seen.add(signature(value)));
            };
            const children = (values) => {
                const output = [];
                for (const value of values) {
                    if (value !== null && typeof value === 'object') {
                        // resourceType names the resource; it isn't one of its elements.
                        for (const [key, child] of Object.entries(value)) {
                            if (key !== 'resourceType') {
                                output.push(...(Array.isArray(child) ? child : [child]));
                            }
                        }
                    }
                }
                return output.filter((value) => value !== null && value !== undefined);
            };
            switch (name) {
                case 'not':
                    return collection.length === 0 ? [] : [!collectionBoolean(collection)];
                case 'oftype':
                    return collection.filter((value) => fhirPathTypeMatches(value, typeArgument()));
                case 'is':
                    return onSingle((value) => fhirPathTypeMatches(value, typeArgument()));
                case 'as':
                    return onSingle((value) => (fhirPathTypeMatches(value, typeArgument()) ? value : null));
                case 'single':
                    return onSingle((value) => value);
                case 'tail':
                    return collection.slice(1);
                case 'skip':
                    return collection.slice(Math.max(0, Number(argValue(0)) || 0));
                case 'take':
                    return collection.slice(0, Math.max(0, Number(argValue(0)) || 0));
                case 'union':
                    return distinct([...collection, ...arg(0)]);
                case 'combine':
                    return [...collection, ...arg(0)];
                case 'intersect': {
                    const other = new Set(arg(0).map(signature));
                    return distinct(collection.filter((value) => other.has(signature(value))));
                }
                case 'exclude': {
                    const other = new Set(arg(0).map(signature));
                    return collection.filter((value) => !other.has(signature(value)));
                }
                case 'isdistinct':
                    return [distinct(collection).length === collection.length];
                case 'subsetof': {
                    const other = new Set(arg(0).map(signature));
                    return [collection.every((value) => other.has(signature(value)))];
                }
                case 'supersetof': {
                    const own = new Set(collection.map(signature));
                    return [arg(0).every((value) => own.has(signature(value)))];
                }
                case 'alltrue':
                    return [collection.every((value) => value === true)];
                case 'anytrue':
                    return [collection.some((value) => value === true)];
                case 'allfalse':
                    return [collection.every((value) => value === false)];
                case 'anyfalse':
                    return [collection.some((value) => value === false)];
                case 'hasvalue':
                    return [collection.length === 1 && (collection[0] === null || typeof collection[0] !== 'object')];
                case 'trace':
                    return collection;
                case 'children':
                    return children(collection);
                case 'descendants': {
                    const output = [];
                    let frontier = children(collection);
                    for (let depth = 0; frontier.length > 0 && depth < 100; depth++) {
                        output.push(...frontier);
                        frontier = children(frontier);
                    }
                    return output;
                }
                case 'extension': {
                    const url = argValue(0);
                    return children(collection.map((value) => ({ extension: value?.extension ?? [] })))
                        .filter((extension) => extension?.url === url);
                }
                case 'weight':
                case 'ordinal': {
                    const questionnaire = environment.questionnaire;
                    return collection.map((value) => codingWeight(value, questionnaire)).filter((weight) => weight !== null);
                }
                case 'min':
                case 'max': {
                    const numbers = collection.filter((value) => typeof value === 'number');
                    if (numbers.length > 0) {
                        return [name === 'min' ? Math.min(...numbers) : Math.max(...numbers)];
                    }
                    // Strings as text, dates and times by precision; empty when two can't be ordered.
                    const strings = collection.filter((value) => typeof value === 'string');
                    let best = strings[0];
                    for (const value of strings.slice(1)) {
                        const temporal = compareTemporal(value, best);
                        const order = temporal !== undefined ? temporal : (value < best ? -1 : (value > best ? 1 : 0));
                        if (order === null) {
                            return [];
                        }
                        if (name === 'min' ? order < 0 : order > 0) {
                            best = value;
                        }
                    }
                    return best === undefined ? [] : [best];
                }
                case 'avg': {
                    const numbers = collection.filter((value) => typeof value === 'number');
                    return numbers.length === 0 ? [] : [numbers.reduce((sum, value) => sum + value, 0) / numbers.length];
                }
                // Strings.
                case 'length':
                    return onString((value) => value.length);
                case 'upper':
                    return onString((value) => value.toUpperCase());
                case 'lower':
                    return onString((value) => value.toLowerCase());
                case 'trim':
                    return onString((value) => value.trim());
                case 'tochars':
                    return collection.length === 1 && typeof collection[0] === 'string' ? [...collection[0]] : [];
                case 'indexof':
                    return onString((value) => value.indexOf(String(argValue(0))));
                case 'substring':
                    return onString((value) => {
                        const start = Number(argValue(0));
                        if (!Number.isInteger(start) || start < 0 || start >= value.length) return null;
                        return ast.args.length > 1 ? value.substring(start, start + Math.max(0, Number(argValue(1)))) : value.slice(start);
                    });
                case 'startswith':
                    return onString((value) => value.startsWith(String(argValue(0))));
                case 'endswith':
                    return onString((value) => value.endsWith(String(argValue(0))));
                case 'contains':
                    return onString((value) => value.includes(String(argValue(0))));
                case 'replace':
                    // An empty pattern surrounds each character ('abc'.replace('', 'x') is 'xaxbxcx').
                    return onString((value) => value.replaceAll(String(argValue(0)), () => String(argValue(1))));
                case 'matches':
                    return onString((value) => new RegExp(String(argValue(0)), 'u').test(value));
                case 'replacematches':
                    return onString((value) => value.replace(new RegExp(String(argValue(0)), 'gu'), String(argValue(1))));
                case 'split':
                    return collection.length === 1 && typeof collection[0] === 'string' ? collection[0].split(String(argValue(0))) : [];
                case 'join':
                    return [collection.map((value) => String(value)).join(ast.args.length > 0 ? String(argValue(0)) : '')];
                // Conversions.
                case 'tostring':
                    return onSingle((value) => (value !== null && typeof value === 'object' ? null : String(value)));
                case 'tointeger':
                    return onSingle((value) => {
                        if (typeof value === 'number') return Number.isInteger(value) ? value : null;
                        if (typeof value === 'boolean') return value ? 1 : 0;
                        return typeof value === 'string' && /^[+-]?\d+$/.test(value) ? Number.parseInt(value, 10) : null;
                    });
                case 'todecimal':
                    return onSingle((value) => {
                        if (typeof value === 'number') return value;
                        if (typeof value === 'boolean') return value ? 1 : 0;
                        return typeof value === 'string' && /^[+-]?\d+(\.\d+)?$/.test(value) ? Number.parseFloat(value) : null;
                    });
                case 'toboolean':
                    return onSingle((value) => {
                        if (typeof value === 'boolean') return value;
                        const text = String(value).toLowerCase();
                        if (['true', 't', 'yes', 'y', '1', '1.0'].includes(text)) return true;
                        if (['false', 'f', 'no', 'n', '0', '0.0'].includes(text)) return false;
                        return null;
                    });
                case 'convertstostring':
                case 'convertstointeger':
                case 'convertstodecimal':
                case 'convertstoboolean': {
                    if (collection.length === 0) return [];
                    const converted = this.evaluateMoreMethods(name.replace('convertsto', 'to'), ast, collection, environment, context);
                    return [converted.length > 0];
                }
                // Math.
                case 'abs':
                    return onNumber(Math.abs);
                case 'ceiling':
                    return onNumber(Math.ceil);
                case 'floor':
                    return onNumber(Math.floor);
                case 'truncate':
                    return onNumber(Math.trunc);
                case 'sqrt':
                    return onNumber((value) => (value < 0 ? null : Math.sqrt(value)));
                case 'exp':
                    return onNumber(Math.exp);
                case 'ln':
                    return onNumber((value) => (value <= 0 ? null : Math.log(value)));
                case 'log':
                    return onNumber((value) => (value <= 0 ? null : Math.log(value) / Math.log(Number(argValue(0)))));
                case 'power':
                    return onNumber((value) => Math.pow(value, Number(argValue(0))));
                case 'round':
                    return onNumber((value) => {
                        const precision = ast.args.length > 0 ? Number(argValue(0)) : 0;
                        if (!Number.isInteger(precision) || precision < 0) return null;
                        const factor = 10 ** precision;
                        return Math.round(value * factor) / factor;
                    });
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
                    // otherwise-result is optional; without it a false or empty criterion gives empty.
                    if (ast.args.length !== 2 && ast.args.length !== 3) {
                        throw new Error('FHIRPath iif() takes two or three arguments.');
                    }
                    const condition = collectionBoolean(this.evaluate(ast.args[0], environment, context));
                    if (!condition && ast.args.length === 2) {
                        return [];
                    }
                    return this.evaluate(condition ? ast.args[1] : ast.args[2], environment, context);
                }
                case 'today':
                    return [fhirToday()];
                case 'now':
                    return [fhirNow()];
                case 'timeofday':
                    return [fhirNow().slice(11, 19)];
                default:
                    // A function called without a source applies to the
                    // current context: not(), exists(), length() ...
                    return this.evaluateMethod({ type: 'method', name: ast.name, source: { type: 'special', name: 'this' }, args: ast.args }, environment, context);
            }
        }

        evaluateBinary(ast, environment, context) {
            const operator = ast.operator;
            // Three-valued logic: an empty operand is unknown, so the result is empty unless the
            // other operand decides it (false and {} is false, true or {} is true).
            if (operator === 'or') {
                const left = logicValue(this.evaluate(ast.left, environment, context));
                if (left === true) {
                    return [true];
                }
                const right = logicValue(this.evaluate(ast.right, environment, context));
                if (right === true) {
                    return [true];
                }
                return left === false && right === false ? [false] : [];
            }
            if (operator === 'and') {
                const left = logicValue(this.evaluate(ast.left, environment, context));
                if (left === false) {
                    return [false];
                }
                const right = logicValue(this.evaluate(ast.right, environment, context));
                if (right === false) {
                    return [false];
                }
                return left === true && right === true ? [true] : [];
            }
            if (operator === 'xor') {
                const left = logicValue(this.evaluate(ast.left, environment, context));
                const right = logicValue(this.evaluate(ast.right, environment, context));
                return left === null || right === null ? [] : [left !== right];
            }
            if (operator === 'implies') {
                const left = logicValue(this.evaluate(ast.left, environment, context));
                if (left === false) {
                    return [true];
                }
                const right = logicValue(this.evaluate(ast.right, environment, context));
                if (right === true) {
                    return [true];
                }
                return left === true && right === false ? [false] : [];
            }

            const left = this.evaluate(ast.left, environment, context);
            const right = this.evaluate(ast.right, environment, context);
            // Equivalence (~): strings compare case- and whitespace-insensitively, and two empties are equivalent.
            const equivalent = (a, b) => {
                const normalise = (value) => (typeof value === 'string' ? value.trim().replace(/\s+/g, ' ').toLowerCase() : value);
                return a.length === b.length && a.every((value, index) => {
                    const other = b[index];
                    return typeof value === 'string' && typeof other === 'string' ? normalise(value) === normalise(other) : equalPrimitive(value, other);
                });
            };
            switch (operator) {
                case '=':
                case '!=': {
                    const equal = collectionsEqual(left, right);
                    if (equal === null) {
                        return [];
                    }
                    return [operator === '=' ? equal : !equal];
                }
                case '~':
                    return [equivalent(asCollection(left), asCollection(right))];
                case '!~':
                    return [!equivalent(asCollection(left), asCollection(right))];
                case '|': {
                    const seen = new Set();
                    return [...asCollection(left), ...asCollection(right)].filter((value) => {
                        const signature = JSON.stringify(value);
                        return !seen.has(signature) && seen.add(signature);
                    });
                }
                case 'in':
                case 'contains': {
                    const item = operator === 'in' ? asCollection(left) : asCollection(right);
                    const within = operator === 'in' ? asCollection(right) : asCollection(left);
                    if (item.length === 0) {
                        return [];
                    }
                    return [within.some((value) => equalPrimitive(value, item[0]))];
                }
                case '&': {
                    const text = (collection) => {
                        const value = firstValue(collection);
                        return value === undefined || value === null ? '' : String(value);
                    };
                    return [text(left) + text(right)];
                }
                case 'div':
                case 'mod': {
                    const leftValue = Number(firstValue(left));
                    const rightValue = Number(firstValue(right));
                    if (!Number.isFinite(leftValue) || !Number.isFinite(rightValue) || rightValue === 0) {
                        return [];
                    }
                    return [operator === 'div' ? Math.trunc(leftValue / rightValue) : leftValue % rightValue];
                }
                case '>':
                case '<':
                case '>=':
                case '<=': {
                    let leftValue = firstValue(left);
                    let rightValue = firstValue(right);
                    if (leftValue === undefined || rightValue === undefined) {
                        return [];
                    }
                    if (isQuantity(leftValue) || isQuantity(rightValue)) {
                        // Empty when the units differ, or when the other side is neither a number nor a quantity.
                        const pair = comparableQuantities(leftValue, rightValue);
                        if (!pair) {
                            return [];
                        }
                        [leftValue, rightValue] = pair;
                    }
                    // Dates and times by precision and offset: empty when the order is unknown.
                    const order = compareTemporal(leftValue, rightValue);
                    if (order === null) {
                        return [];
                    }
                    if (order !== undefined) {
                        [leftValue, rightValue] = [order, 0];
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
                    const leftFirst = firstValue(left);
                    const rightFirst = firstValue(right);
                    // A date or dateTime plus or minus a calendar duration (today() - 18 years).
                    if ((operator === '+' || operator === '-') && typeof leftFirst === 'string'
                        && rightFirst !== null && typeof rightFirst === 'object' && 'unit' in rightFirst) {
                        const shifted = shiftFhirDate(leftFirst, rightFirst, operator === '+' ? 1 : -1);
                        return shifted === null ? [] : [shifted];
                    }
                    // String concatenation: + on two strings.
                    if (operator === '+' && typeof leftFirst === 'string' && typeof rightFirst === 'string') {
                        return [leftFirst + rightFirst];
                    }
                    // Quantities: + and - in the same unit; * and / by a number.
                    if (isQuantity(leftFirst) || isQuantity(rightFirst)) {
                        return quantityArithmetic(operator, leftFirst, rightFirst);
                    }
                    const leftValue = Number(leftFirst);
                    const rightValue = Number(rightFirst);
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
                    compiledItem.requiredExpression,
                    compiledItem.readOnlyExpression,
                    compiledItem.textExpression,
                    compiledItem.answerExpression,
                    ...compiledItem.optionToggles.map((toggle) => toggle.expression),
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
        }

        // SDC launchContext: the resources the host supplies
        // (options.launchContext, by name) for the names the form declares,
        // as %patient, %encounter, %user ... A declared name the host didn't
        // supply is reported once, and expressions using it come out empty.
        launchContext(runtime) {
            const supplied = runtime.options?.launchContext ?? {};
            const variables = {};
            for (const name of this.compiled.launchContextNames ?? []) {
                if (supplied[name] !== undefined && supplied[name] !== null) {
                    variables[name] = supplied[name];
                }
            }
            return variables;
        }

        isInsideRepeat(compiledItem) {
            return (this.compiled.repeatAncestors.get(compiledItem.linkId) ?? []).length > 0;
        }

        // The response expressions evaluate against (%resource), built once per
        // change to answers, instance counts or enablement rather than once per
        // expression; the evaluator never mutates it.
        currentResource(runtime) {
            const key = runtime.responseStore.revision + ':' + runtime.behaviorEngine.revision;
            if (this.resourceCache?.key !== key) {
                this.resourceCache = {
                    key,
                    resource: runtime.responseStore.buildQuestionnaireResponse((linkId) => runtime.isEnabled(linkId)),
                };
            }
            return this.resourceCache.resource;
        }

        resetEvaluationWarnings() {
            this.warnings = [];
        }

        buildEnvironment(runtime, compiledItem) {
            const resource = this.currentResource(runtime);
            const environment = {
                ...this.launchContext(runtime),
                questionnaire: this.compiled.questionnaire,
                resource,
                rootResource: resource,
                context: resource,
                // SDC's %qitem: the Questionnaire item the expression is on (for a variable, the
                // item that defines it).
                qitem: compiledItem.item,
            };
            // Evaluate variables in scope (ancestor-defined first, then self), so
            // a variable defined on a group is available to descendant
            // expressions, and an inner definition shadows an outer same-named
            // one. %context for a variable is the response node of the item it is
            // defined on (the whole resource for a root-level variable).
            const scope = compiledItem.variableScope ?? compiledItem.variables.map((expression) => ({ definingLinkId: compiledItem.linkId, expression }));
            for (const { definingLinkId, expression } of scope) {
                const ast = this.compiledExpressions.get(expression);
                if (!ast) {
                    continue;
                }
                const definingContext = findResponseItemByLinkId(resource, definingLinkId) ?? resource;
                try {
                    environment.context = definingContext;
                    environment.qitem = this.compiled.itemsByLinkId.get(definingLinkId)?.item;
                    defineVariable(environment, expression.name, this.evaluator.evaluate(ast, environment, definingContext));
                } catch (error) {
                    this.warnings.push({
                        code: 'fhirpath-evaluate-error',
                        linkId: compiledItem.linkId,
                        expression: expression.expression,
                        message: error instanceof Error ? error.message : String(error),
                    });
                }
            }
            // Restore %context to the resource for the consuming expression.
            environment.context = resource;
            environment.qitem = compiledItem.item;
            return environment;
        }

        // Seed answers from initialExpression for items the user has not answered.
        evaluateInitialExpressions(runtime) {
            for (const compiledItem of this.compiled.expressionItems) {
                if (this.isInsideRepeat(compiledItem)) {
                    // Repeat descendants are seeded per instance in
                    // seedInitialExpressionInstances against their own keys.
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
                            valueKey: runtime.answerValueKeyForValue(compiledItem.item, value),
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
        // nodePath (from the instance walk) scopes %context and group-defined
        // variables to one repeat instance; without it the expression sees the
        // whole response, as a canonical (non-repeating) item does.
        evaluateEnableWhenExpression(runtime, compiledItem, nodePath = null) {
            const expression = compiledItem.enableWhenExpression;
            const ast = expression && this.compiledExpressions.get(expression);
            if (!ast) {
                return null;
            }
            try {
                const environment = nodePath !== null && nodePath.length > 0
                    ? this.buildEnvironmentForInstance(runtime, compiledItem, nodePath)
                    : this.buildEnvironment(runtime, compiledItem);
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

        evaluateFor(runtime, compiledItem, expression, environment = null) {
            const ast = expression && this.compiledExpressions.get(expression);
            if (!ast) {
                return null;
            }
            try {
                return this.evaluator.evaluate(ast, environment ?? this.buildEnvironment(runtime, compiledItem), null);
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

        // Required, read-only and text by expression, answers by
        // answerExpression, and which options answerOptionsToggleExpression
        // turns off, for each item that has them: by linkId against the whole
        // response, and, inside repeating groups, by storage key for each
        // repetition, with %context its own instance (as enableWhen and
        // calculations are).
        evaluateDynamic(runtime) {
            const state = new Map();
            const evaluateItem = (compiledItem, environment) => {
                const { requiredExpression, readOnlyExpression, textExpression, answerExpression, optionToggles } = compiledItem;
                const boolean = (expression) => {
                    const result = expression ? this.evaluateFor(runtime, compiledItem, expression, environment) : null;
                    return result === null || result.length === 0 ? null : collectionBoolean(result);
                };
                const text = textExpression ? this.evaluateFor(runtime, compiledItem, textExpression, environment) : null;
                const answers = answerExpression ? this.evaluateFor(runtime, compiledItem, answerExpression, environment) : null;
                const offOptions = [];
                for (const toggle of optionToggles) {
                    if (boolean(toggle.expression) !== true) {
                        offOptions.push(...toggle.options);
                    }
                }
                return {
                    required: boolean(requiredExpression),
                    readOnly: boolean(readOnlyExpression),
                    text: text !== null && text.length > 0 ? String(text[0]) : null,
                    answerOptions: answers === null ? null : valuesToAnswerOptions(answers, compiledItem.item),
                    offOptions,
                };
            };
            const dynamicItems = this.compiled.expressionItems.filter(({ requiredExpression, readOnlyExpression, textExpression, answerExpression, optionToggles }) =>
                requiredExpression || readOnlyExpression || textExpression || answerExpression || optionToggles.length > 0);
            for (const compiledItem of dynamicItems) {
                state.set(compiledItem.linkId, evaluateItem(compiledItem, null));
            }
            const dynamicLinkIds = new Set(dynamicItems.map((compiledItem) => compiledItem.linkId));
            if (dynamicLinkIds.size > 0) {
                this.walkRepeatInstances(runtime, (compiledItem, storageKey, nodePath) => {
                    if (dynamicLinkIds.has(compiledItem.linkId)) {
                        state.set(storageKey, evaluateItem(compiledItem, this.buildEnvironmentForInstance(runtime, compiledItem, nodePath)));
                    }
                });
            }
            return state;
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

        // Whether a stored answer to a calculated item is one someone gave over the calculation.
        // On the first pass over a loaded response, SDC's test: an item a person could edit whose
        // stored answer differs from what the calculation gives now. Afterwards, what setAnswers
        // recorded.
        isCalculationOverridden(runtime, compiledItem, storageKey, value, firstPassOnLoad) {
            if (runtime.calculationOverrides.has(storageKey)) {
                return true;
            }
            if (!firstPassOnLoad || !runtime.isCalculationEditable(compiledItem) || !runtime.responseStore.hasAnswers(storageKey)) {
                return false;
            }
            const stored = firstValue(runtime.responseStore.getAnswers(storageKey))?.value;
            const same = isQuantity(stored) && isQuantity(value)
                ? stored.value === value.value && quantityUnit(stored) === quantityUnit(value)
                : JSON.stringify(stored) === JSON.stringify(value);
            if (same) {
                return false;
            }
            runtime.calculationOverrides.add(storageKey);
            return true;
        }

        recalculate(runtime) {
            const firstPassOnLoad = runtime.questionnaireResponse !== null && this.overridesLoaded !== true;
            this.overridesLoaded = true;
            for (const compiledItem of this.compiled.calculatedItems) {
                if (this.isInsideRepeat(compiledItem)) {
                    // Repeat descendants are evaluated per instance in
                    // recalculateInstances so each instance writes to its own
                    // storage key; skip the canonical pass here.
                    continue;
                }
                if (runtime.calculationOverrides.has(compiledItem.linkId)) {
                    continue;
                }
                const item = compiledItem.item;
                // Shared environment builder resolves in-scope variables
                // (including ancestor-group variables) with the correct %context.
                const environment = this.buildEnvironment(runtime, compiledItem);

                const calculatedExpression = compiledItem.calculatedExpression;
                const calculatedAst = this.compiledExpressions.get(calculatedExpression);
                if (!calculatedAst) {
                    continue;
                }

                try {
                    const result = this.evaluator.evaluate(calculatedAst, environment, null);
                    const value = firstValue(result);
                    if (this.isCalculationOverridden(runtime, compiledItem, compiledItem.linkId, value, firstPassOnLoad)) {
                        continue;
                    }
                    if (value === undefined) {
                        runtime.responseStore.clearAnswer(compiledItem.linkId);
                        continue;
                    }
                    runtime.responseStore.setAnswer(compiledItem.linkId, {
                        valueKey: runtime.answerValueKeyForValue(item, value),
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
            this.recalculateInstances(runtime, firstPassOnLoad);
        }

        // Per-instance calculatedExpression for descendants of repeating groups.
        // Each instance writes to its own instance-qualified storage key so the
        // value renders and serializes for that instance (canonical items are
        // handled above and never enter this walk).
        recalculateInstances(runtime, firstPassOnLoad = false) {
            this.walkRepeatInstances(runtime, (compiledItem, storageKey, nodePath) => {
                const calculatedExpression = compiledItem.calculatedExpression;
                const ast = calculatedExpression && this.compiledExpressions.get(calculatedExpression);
                if (!ast || runtime.calculationOverrides.has(storageKey)) {
                    return;
                }
                try {
                    const environment = this.buildEnvironmentForInstance(runtime, compiledItem, nodePath);
                    const value = firstValue(this.evaluator.evaluate(ast, environment, null));
                    if (this.isCalculationOverridden(runtime, compiledItem, storageKey, value, firstPassOnLoad)) {
                        return;
                    }
                    if (value === undefined) {
                        runtime.responseStore.clearAnswer(storageKey);
                        return;
                    }
                    runtime.responseStore.setAnswer(storageKey, {
                        valueKey: runtime.answerValueKeyForValue(compiledItem.item, value),
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
            });
        }

        // Per-instance initialExpression seeding, mirroring the canonical
        // evaluateInitialExpressions: only fill an instance key with no answer.
        seedInitialExpressionInstances(runtime) {
            this.walkRepeatInstances(runtime, (compiledItem, storageKey, nodePath) => {
                const expression = compiledItem.initialExpression;
                const ast = expression && this.compiledExpressions.get(expression);
                if (!ast || runtime.responseStore.hasAnswers(storageKey)) {
                    return;
                }
                try {
                    const environment = this.buildEnvironmentForInstance(runtime, compiledItem, nodePath);
                    const value = firstValue(this.evaluator.evaluate(ast, environment, null));
                    if (value !== undefined) {
                        runtime.responseStore.setAnswer(storageKey, {
                            valueKey: runtime.answerValueKeyForValue(compiledItem.item, value),
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
            });
        }

        // Walk the questionnaire following repeating-group instance counts
        // (mirrors the response store and renderer). For every leaf item that
        // lives inside at least one repeat instance, invoke visit() with the
        // compiled item, its instance-qualified storage key, and nodePath — the
        // ordered list of enclosing group ancestors ({ linkId, index }, index
        // null for a non-repeating group) used to resolve the instance's own
        // response nodes for %context and in-scope variables.
        walkRepeatInstances(runtime, visit) {
            const walk = (items, storagePrefix, nodePath, insideRepeat) => {
                for (const item of items ?? []) {
                    if (typeof item?.linkId !== 'string') {
                        continue;
                    }
                    const storageKey = storagePrefix + item.linkId;
                    if (isRepeatingGroup(item)) {
                        const count = runtime.responseStore.getRepeatCount(storageKey);
                        for (let index = 0; index < count; index++) {
                            walk(
                                item.item,
                                instanceKeyPrefix(storageKey, index),
                                [...nodePath, { linkId: item.linkId, index }],
                                true,
                            );
                        }
                        continue;
                    }
                    if (item.type === 'group') {
                        walk(item.item, storagePrefix, [...nodePath, { linkId: item.linkId, index: null }], insideRepeat);
                        continue;
                    }
                    if (insideRepeat) {
                        const compiledItem = runtime.compiled.itemsByLinkId.get(item.linkId);
                        if (compiledItem) {
                            visit(compiledItem, storageKey, nodePath);
                        }
                    }
                    walk(item.item, storagePrefix, nodePath, insideRepeat);
                }
            };
            walk(runtime.questionnaire.item, '', [], false);
        }

        // Instance-scoped environment: %resource stays the whole response, while
        // %context and group-defined variables resolve to the specific instance's
        // response nodes located by nodePath. Falls back to the canonical first
        // match for ancestors defined outside any repeat.
        buildEnvironmentForInstance(runtime, compiledItem, nodePath) {
            const resource = this.currentResource(runtime);
            const environment = {
                ...this.launchContext(runtime),
                questionnaire: this.compiled.questionnaire,
                resource,
                rootResource: resource,
                context: resource,
                qitem: compiledItem.item,
            };
            // Descend the response in lockstep with nodePath, recording each
            // enclosing group's instance node. For a repeating group pick the
            // index-th sibling sharing that linkId; for a non-repeating group the
            // single one — exactly how buildQuestionnaireResponse nests them.
            const groupNodes = new Map();
            let currentItems = resource.item ?? [];
            let instanceNode = resource;
            for (const { linkId, index } of nodePath) {
                const matches = currentItems.filter((node) => node?.linkId === linkId);
                const node = index === null ? matches[0] : matches[index];
                if (!node) {
                    instanceNode = null;
                    break;
                }
                groupNodes.set(linkId, node);
                instanceNode = node;
                currentItems = node.item ?? [];
            }
            const scope = compiledItem.variableScope ?? compiledItem.variables.map((expression) => ({ definingLinkId: compiledItem.linkId, expression }));
            for (const { definingLinkId, expression } of scope) {
                const ast = this.compiledExpressions.get(expression);
                if (!ast) {
                    continue;
                }
                const definingContext = groupNodes.get(definingLinkId) ?? findResponseItemByLinkId(resource, definingLinkId) ?? resource;
                try {
                    environment.context = definingContext;
                    environment.qitem = this.compiled.itemsByLinkId.get(definingLinkId)?.item;
                    defineVariable(environment, expression.name, this.evaluator.evaluate(ast, environment, definingContext));
                } catch (error) {
                    this.warnings.push({
                        code: 'fhirpath-evaluate-error',
                        linkId: compiledItem.linkId,
                        expression: expression.expression,
                        message: error instanceof Error ? error.message : String(error),
                    });
                }
            }
            // %context for the consuming expression is the item's innermost
            // enclosing repeat-instance node (the whole resource if unresolved).
            environment.context = instanceNode ?? resource;
            environment.qitem = compiledItem.item;
            return environment;
        }
    }

    class QuestionnaireBehaviorEngine {
        constructor(runtime) {
            this.runtime = runtime;
            this.enabled = new Map();
            // storage key -> bool, for items rendered inside repeating-group
            // instances (per-instance enableWhen). Canonical items use `enabled`.
            this.instanceEnabled = new Map();
            // Bumped whenever any enablement changes (it filters the response
            // expressions see), alongside the store's revision.
            this.revision = 0;
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
                        this.revision++;
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
            // Expressions evaluated during the walk see the last complete
            // enablement until a value actually changes (then it is rebuilt).
            const previous = new Map(this.instanceEnabled);
            this.instanceEnabled.clear();
            // chain carries every enclosing repeating-group instance, outermost
            // first: [{ groupLinkId, prefix }, ...]. prefix is that instance's
            // storage-key prefix. parentKey is the storage key of the item's
            // parent (canonical linkId outside any repeat), so an item is gated
            // by its actual parent's per-instance state, at any depth. nodePath
            // lists every enclosing group ({ linkId, index }, index null for a
            // non-repeating group) so an enableWhenExpression is evaluated
            // against this instance's response nodes (%context), as the
            // calculated/initial expressions are.
            const walk = (items, prefix, chain, parentKey, nodePath) => {
                for (const item of items ?? []) {
                    if (typeof item?.linkId !== 'string') {
                        continue;
                    }
                    const storageKey = prefix + item.linkId;
                    // Every item inside a repeat instance — including a nested
                    // repeating group itself — gets its own enablement.
                    if (prefix !== '') {
                        const enabled = this.evaluateInstanceItem(item, chain, parentKey, nodePath);
                        if (previous.get(storageKey) !== enabled) {
                            this.revision++;
                        }
                        this.instanceEnabled.set(storageKey, enabled);
                    }
                    if (isRepeatingGroup(item)) {
                        const count = this.runtime.responseStore.getRepeatCount(storageKey);
                        for (let instanceIndex = 0; instanceIndex < count; instanceIndex++) {
                            const instancePrefix = instanceKeyPrefix(storageKey, instanceIndex);
                            walk(item.item, instancePrefix, [...chain, { groupLinkId: item.linkId, prefix: instancePrefix }], storageKey,
                                [...nodePath, { linkId: item.linkId, index: instanceIndex }]);
                        }
                        continue;
                    }
                    walk(item.item, prefix, chain, storageKey,
                        item.type === 'group' ? [...nodePath, { linkId: item.linkId, index: null }] : nodePath);
                }
            };
            walk(this.runtime.questionnaire.item, '', [], null, []);
            if ([...previous.keys()].some((key) => !this.instanceEnabled.has(key))) {
                this.revision++;
            }
        }

        evaluateInstanceItem(item, chain, parentKey, nodePath = []) {
            // The parent (already evaluated: the walk is top-down) gates its
            // children, so a disabled group anywhere above disables the item.
            if (parentKey !== null && !this.isEnabled(parentKey)) {
                return false;
            }

            // enableWhenExpression gates the instance the same way it gates a
            // canonical item in evaluateItem (combined with any enableWhen).
            const compiledItem = this.runtime.compiled.itemsByLinkId.get(item.linkId);
            if (compiledItem?.enableWhenExpression) {
                const expressionResult = this.runtime.expressionEngine.evaluateEnableWhenExpression(this.runtime, compiledItem, nodePath);
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
            // A quantity compares by its value in the same unit; in different units it is
            // neither equal nor ordered (no unit conversion).
            if (conditionKey === 'answerQuantity') {
                const holds = { '=': (a, b) => a === b, '>': (a, b) => a > b, '<': (a, b) => a < b, '>=': (a, b) => a >= b, '<=': (a, b) => a <= b }[operator === '!=' ? '=' : operator];
                if (!holds) {
                    return false;
                }
                const anyHolds = actualValues.map((actual) => comparableQuantities(actual, expected)).filter(Array.isArray).some(([a, b]) => holds(a, b));
                return operator === '!=' ? !anyHolds : anyHolds;
            }
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

            // signatureRequired: the form, or an enabled item, must be signed.
            for (const requirement of this.runtime.compiled.signatureRequirements) {
                const item = this.runtime.compiled.itemsByLinkId.get(requirement.key)?.item;
                if ((requirement.key !== '' && (!item || !this.runtime.isEnabled(requirement.key))) || this.runtime.signatures.has(requirement.key)) {
                    continue;
                }
                const label = requirement.key === '' ? (this.runtime.questionnaire.title || this.runtime.t('thisForm')) : (item.text || requirement.key);
                issues.push({
                    severity: 'error', code: 'signature-required', linkId: requirement.key || undefined,
                    storageKey: signatureNodeKey(requirement.key), message: this.runtime.t('signatureRequired', label),
                });
            }

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
            const requiredCount = minOccurs !== null ? minOccurs : (this.runtime.isRequired(storageKey, item) ? 1 : 0);
            if (requiredCount > 0 && answers.length < requiredCount) {
                add('error', minOccurs !== null ? 'min-occurs' : 'required',
                    requiredCount === 1 ? this.runtime.t('requiresValue', label) : this.runtime.t('requiresAtLeast', label, requiredCount));
            }
            if (maxOccurs !== null && answers.length > maxOccurs) {
                add('error', 'max-occurs', this.runtime.t('allowsAtMost', label, maxOccurs));
            }
            this.validateValueBounds(item, storageKey, label, answers, issues);
            if (Number.isInteger(item.maxLength)) {
                for (const answer of answers) {
                    if (typeof answer.value === 'string' && answer.value.length > item.maxLength) {
                        add('error', 'max-length', this.runtime.t('maxLength', label));
                    }
                }
            }
            const minLength = getIntegerExtension(item, EXTENSIONS.MIN_LENGTH);
            if (minLength !== null) {
                for (const answer of answers) {
                    if (typeof answer.value === 'string' && answer.value !== '' && answer.value.length < minLength) {
                        add('error', 'min-length', this.runtime.t('minLength', label, minLength));
                    }
                }
            }
            if (item.type === 'attachment') {
                const mimeTypes = attachmentMimeTypes(item);
                const maxSize = getTypedExtensionValue(item, EXTENSIONS.MAX_SIZE);
                for (const answer of answers) {
                    const attachment = answer.value ?? {};
                    if (mimeTypes.length > 0 && !mimeTypes.includes(attachment.contentType)) {
                        add('error', 'mime-type', this.runtime.t('mimeType', label, mimeTypes.join(', ')));
                    }
                    if (typeof maxSize === 'number' && Number.isFinite(attachment.size) && attachment.size > maxSize) {
                        add('error', 'max-size', this.runtime.t('maxSize', label, maxSize));
                    }
                }
            }
            const types = referenceTypes(item);
            if (item.type === 'reference' && types.length > 0) {
                for (const answer of answers) {
                    const reference = String(answer.value?.reference ?? '');
                    if (reference !== '' && !types.some((type) => reference.startsWith(type + '/') || reference.includes('/' + type + '/'))) {
                        add('error', 'reference-type', this.runtime.t('referenceType', label, types.join(', ')));
                    }
                }
            }
            if (item.type === 'quantity') {
                const bounds = [
                    [EXTENSIONS.MIN_QUANTITY, 'min-quantity', 'minValue', (value, bound) => value < bound],
                    [EXTENSIONS.MAX_QUANTITY, 'max-quantity', 'maxValue', (value, bound) => value > bound],
                ];
                for (const [url, code, key, outside] of bounds) {
                    const bound = getTypedExtensionValue(item, url);
                    if (!bound || typeof bound.value !== 'number') {
                        continue;
                    }
                    // Only an answer in the bound's unit is compared; no unit conversion here.
                    const boundUnit = bound.code ?? bound.unit ?? '';
                    for (const answer of answers) {
                        const quantity = answer.value ?? {};
                        const unit = quantity.code ?? quantity.unit ?? '';
                        if (typeof quantity.value === 'number' && (boundUnit === '' || unit === boundUnit) && outside(quantity.value, bound.value)) {
                            add('error', code, this.runtime.t(key, label, (bound.value + ' ' + (bound.unit ?? bound.code ?? '')).trim()));
                        }
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
                            add('error', 'regex', this.runtime.t('regex', label));
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
                            message: this.runtime.t('minValue', label, minValue),
                        });
                    }
                    if (maxValue !== undefined && comparable > maxValue) {
                        issues.push({
                            severity: 'error', code: 'max-value', linkId, storageKey,
                            message: this.runtime.t('maxValue', label, maxValue),
                        });
                    }
                }
                if (maxDecimalPlaces !== null && typeof value === 'number') {
                    const decimals = (String(value).split('.')[1] ?? '').length;
                    if (decimals > maxDecimalPlaces) {
                        issues.push({
                            severity: 'error', code: 'max-decimal-places', linkId, storageKey,
                            message: this.runtime.t('maxDecimalPlaces', label, maxDecimalPlaces),
                        });
                    }
                }
            }
        }
    }

    // ---- GitHub Flavored Markdown (safe subset) -------------------------------
    // Questionnaire.copyright and Questionnaire.description are FHIR `markdown`
    // (GFM). The renderer builds DOM nodes directly and never assigns
    // innerHTML, so questionnaire content cannot inject markup, attributes or
    // script. Raw HTML in the source is shown as literal text (except <br>);
    // links are limited to http(s)/mailto/tel; images render as links so a
    // questionnaire cannot make the browser load remote content.
    const MARKDOWN_SAFE_SCHEME = /^(?:https?|mailto|tel):/i;
    const MARKDOWN_PUNCTUATION = /[!"#$%&'()*+,\-./:;<=>?@[\\\]^_`{|}~]/;
    const MARKDOWN_THEMATIC_BREAK = /^ {0,3}([-*_])(?:[ \t]*\1){2,}[ \t]*$/;
    const MARKDOWN_ATX_HEADING = /^ {0,3}(#{1,6})(?:[ \t]+(.*?))?(?:[ \t]+#+)?[ \t]*$/;
    const MARKDOWN_FENCE = /^( {0,3})(`{3,}|~{3,})(.*)$/;
    const MARKDOWN_BLOCKQUOTE = /^ {0,3}>/;
    const MARKDOWN_TABLE_DELIMITER = /^ {0,3}\|?[ \t]*:?-+:?[ \t]*(?:\|[ \t]*:?-+:?[ \t]*)*\|?[ \t]*$/;
    const MARKDOWN_NAMED_ENTITIES = Object.freeze({
        amp: '&', lt: '<', gt: '>', quot: '"', apos: "'", nbsp: ' ',
        copy: '©', reg: '®', trade: '™', mdash: '—', ndash: '–',
        hellip: '…', laquo: '«', raquo: '»', deg: '°', middot: '·',
        bull: '•', sect: '§', para: '¶', times: '×', plusmn: '±',
    });

    function decodeMarkdownEntities(text) {
        return text.replace(/&(#\d{1,7}|#[xX][0-9a-fA-F]{1,6}|[A-Za-z][A-Za-z0-9]{1,31});/g, (match, body) => {
            if (body[0] === '#') {
                const hex = body[1] === 'x' || body[1] === 'X';
                const code = parseInt(body.slice(hex ? 2 : 1), hex ? 16 : 10);
                if (!Number.isFinite(code) || code === 0 || code > 0x10ffff || (code >= 0xd800 && code <= 0xdfff)) {
                    return '�';
                }
                return String.fromCodePoint(code);
            }
            return Object.prototype.hasOwnProperty.call(MARKDOWN_NAMED_ENTITIES, body) ? MARKDOWN_NAMED_ENTITIES[body] : match;
        });
    }

    // Returns a usable href, or null when the destination is not an absolute
    // http(s)/mailto/tel URL (javascript:, data:, relative paths, ...).
    function markdownSafeHref(raw) {
        // Browsers ignore control characters and whitespace inside a scheme, so
        // strip them before the scheme check rather than after.
        const href = Array.from(decodeMarkdownEntities(String(raw ?? '')))
            .filter((character) => {
                const code = character.codePointAt(0);
                return code > 0x20 && code !== 0x7f;
            })
            .join('');
        return href !== '' && MARKDOWN_SAFE_SCHEME.test(href) ? href : null;
    }

    function markdownRun(text, index, character) {
        let end = index;
        while (text[end] === character) {
            end++;
        }
        return end - index;
    }

    // Index of the closing backtick run of exactly `length`, or -1.
    function markdownCodeClose(text, from, length) {
        let index = from;
        while (index < text.length) {
            if (text[index] === '`') {
                const run = markdownRun(text, index, '`');
                if (run === length) {
                    return index;
                }
                index += run;
                continue;
            }
            index++;
        }
        return -1;
    }

    function markdownLeadingSpaces(line) {
        return /^ */.exec(line)[0].length;
    }

    function createMarkdownLink(doc, href, title) {
        const anchor = doc.createElement('a');
        anchor.href = href;
        anchor.target = '_blank';
        anchor.rel = 'noopener noreferrer';
        if (title) {
            anchor.title = title;
        }
        return anchor;
    }

    // Parses an inline link `[label](destination "title")` whose '[' is at
    // `start`. Returns { label, destination, title, end } or null.
    function parseMarkdownLink(text, start) {
        let depth = 0;
        let index = start;
        for (; index < text.length; index++) {
            const character = text[index];
            if (character === '\\') {
                index++;
                continue;
            }
            if (character === '`') {
                const run = markdownRun(text, index, '`');
                const close = markdownCodeClose(text, index + run, run);
                index = (close === -1 ? index + run : close + run) - 1;
                continue;
            }
            if (character === '[') {
                depth++;
            } else if (character === ']') {
                depth--;
                if (depth === 0) {
                    break;
                }
            }
        }
        if (index >= text.length || text[index + 1] !== '(') {
            return null;
        }
        const label = text.slice(start + 1, index);
        let cursor = index + 2;
        const skipSpace = () => {
            while (cursor < text.length && /[ \t\n]/.test(text[cursor])) {
                cursor++;
            }
        };
        skipSpace();
        let destination = '';
        if (text[cursor] === '<') {
            const close = text.indexOf('>', cursor + 1);
            if (close === -1 || text.slice(cursor + 1, close).includes('\n')) {
                return null;
            }
            destination = text.slice(cursor + 1, close);
            cursor = close + 1;
        } else {
            const begin = cursor;
            let parens = 0;
            while (cursor < text.length) {
                const character = text[cursor];
                if (character === '\\' && cursor + 1 < text.length) {
                    cursor += 2;
                    continue;
                }
                if (/\s/.test(character) || character.charCodeAt(0) < 0x20) {
                    break;
                }
                if (character === '(') {
                    parens++;
                } else if (character === ')') {
                    if (parens === 0) {
                        break;
                    }
                    parens--;
                }
                cursor++;
            }
            destination = text.slice(begin, cursor);
        }
        destination = destination.replace(/\\([!-/:-@[-`{-~])/g, '$1');
        skipSpace();
        let title = '';
        const quote = text[cursor];
        if (quote === '"' || quote === "'" || quote === '(') {
            const close = text.indexOf(quote === '(' ? ')' : quote, cursor + 1);
            if (close === -1) {
                return null;
            }
            title = decodeMarkdownEntities(text.slice(cursor + 1, close));
            cursor = close + 1;
            skipSpace();
        }
        if (text[cursor] !== ')') {
            return null;
        }
        return { label, destination, title, end: cursor + 1 };
    }

    // Index where a closing emphasis run of exactly `size` `character`s starts,
    // or -1. Skips escapes and code spans; the run must be right-flanking (not
    // preceded by whitespace) and, for '_', not followed by a word character.
    function markdownEmphasisClose(text, from, character, size) {
        let index = from;
        while (index < text.length) {
            const current = text[index];
            if (current === '\\') {
                index += 2;
                continue;
            }
            if (current === '`') {
                const run = markdownRun(text, index, '`');
                const close = markdownCodeClose(text, index + run, run);
                index = close === -1 ? index + run : close + run;
                continue;
            }
            if (current === character) {
                const run = markdownRun(text, index, character);
                const before = text[index - 1];
                const after = text[index + run] ?? ' ';
                const rightFlanking = index > from && before !== undefined && !/\s/.test(before);
                const underscoreOk = character !== '_' || !/[A-Za-z0-9]/.test(after);
                if (run === size && rightFlanking && underscoreOk) {
                    return index;
                }
                index += run;
                continue;
            }
            index++;
        }
        return -1;
    }

    // Bare URL/email autolinks (GFM extension) at a word boundary.
    function matchMarkdownAutolink(text, index) {
        const rest = text.slice(index);
        let match = /^(?:https?:\/\/|www\.)[^\s<]+/i.exec(rest);
        let email = false;
        if (!match) {
            match = /^[A-Za-z0-9._+-]+@[A-Za-z0-9_-]+(?:\.[A-Za-z0-9_-]+)+/.exec(rest);
            email = match !== null;
        }
        if (!match) {
            return null;
        }
        let value = match[0];
        // Trailing punctuation is not part of the link, nor is an unbalanced ')'.
        for (;;) {
            if (/[?!.,:*_~'"]$/.test(value)) {
                value = value.slice(0, -1);
                continue;
            }
            if (value.endsWith(')') && (value.match(/\)/g) ?? []).length > (value.match(/\(/g) ?? []).length) {
                value = value.slice(0, -1);
                continue;
            }
            break;
        }
        if (email && /[-_]$/.test(value)) {
            return null;
        }
        if (value === '' || (!email && /^www\.$/i.test(value))) {
            return null;
        }
        const href = email ? 'mailto:' + value : (/^www\./i.test(value) ? 'http://' + value : value);
        return { text: value, href, end: index + value.length };
    }

    function parseMarkdownInline(text, doc, inLink = false) {
        const nodes = [];
        let buffer = '';
        const flush = () => {
            if (buffer !== '') {
                nodes.push(doc.createTextNode(decodeMarkdownEntities(buffer)));
                buffer = '';
            }
        };
        const appendAll = (parent, children) => {
            for (const child of children) {
                parent.appendChild(child);
            }
            return parent;
        };

        let index = 0;
        while (index < text.length) {
            const character = text[index];

            if (character === '\\' && index + 1 < text.length) {
                const next = text[index + 1];
                if (next === '\n') {
                    flush();
                    nodes.push(doc.createElement('br'));
                    index += 2;
                    continue;
                }
                if (MARKDOWN_PUNCTUATION.test(next)) {
                    flush();
                    nodes.push(doc.createTextNode(next));
                    index += 2;
                    continue;
                }
            }

            if (character === '\n') {
                const hardBreak = / {2,}$/.test(buffer);
                buffer = buffer.replace(/ +$/, '');
                if (hardBreak) {
                    flush();
                    nodes.push(doc.createElement('br'));
                } else {
                    buffer += '\n';
                }
                index++;
                while (text[index] === ' ') {
                    index++;
                }
                continue;
            }

            if (character === '`') {
                const run = markdownRun(text, index, '`');
                const close = markdownCodeClose(text, index + run, run);
                if (close !== -1) {
                    flush();
                    let content = text.slice(index + run, close).replace(/\n/g, ' ');
                    if (content.length > 1 && content.startsWith(' ') && content.endsWith(' ') && content.trim() !== '') {
                        content = content.slice(1, -1);
                    }
                    const code = doc.createElement('code');
                    code.textContent = content;
                    nodes.push(code);
                    index = close + run;
                    continue;
                }
                buffer += text.slice(index, index + run);
                index += run;
                continue;
            }

            if (character === '<') {
                const rest = text.slice(index);
                const lineBreak = /^<br\s*\/?>/i.exec(rest);
                if (lineBreak) {
                    flush();
                    nodes.push(doc.createElement('br'));
                    index += lineBreak[0].length;
                    continue;
                }
                const uri = /^<([A-Za-z][A-Za-z0-9+.-]{1,31}:[^\s<>]*)>/.exec(rest);
                const mail = uri ? null : /^<([A-Za-z0-9.!#$%&'*+/=?^_`{|}~-]+@[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?(?:\.[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?)*)>/.exec(rest);
                const href = uri ? markdownSafeHref(uri[1]) : (mail ? markdownSafeHref('mailto:' + mail[1]) : null);
                if (href && !inLink) {
                    flush();
                    const anchor = createMarkdownLink(doc, href, '');
                    anchor.textContent = (uri ?? mail)[1];
                    nodes.push(anchor);
                    index += (uri ?? mail)[0].length;
                    continue;
                }
                // Any other markup is literal text.
                buffer += character;
                index++;
                continue;
            }

            if (!inLink && (character === '[' || (character === '!' && text[index + 1] === '['))) {
                const image = character === '!';
                const parsed = parseMarkdownLink(text, image ? index + 1 : index);
                if (parsed) {
                    flush();
                    const href = markdownSafeHref(parsed.destination);
                    if (image) {
                        // Never load remote images: show the alt text, linked when safe.
                        const alt = decodeMarkdownEntities(parsed.label);
                        if (href) {
                            const anchor = createMarkdownLink(doc, href, parsed.title);
                            anchor.textContent = alt !== '' ? alt : href;
                            nodes.push(anchor);
                        } else if (alt !== '') {
                            nodes.push(doc.createTextNode(alt));
                        }
                    } else if (href) {
                        nodes.push(appendAll(createMarkdownLink(doc, href, parsed.title), parseMarkdownInline(parsed.label, doc, true)));
                    } else {
                        nodes.push(...parseMarkdownInline(parsed.label, doc, true));
                    }
                    index = parsed.end;
                    continue;
                }
            }

            if (character === '*' || character === '_' || character === '~') {
                const run = markdownRun(text, index, character);
                const previous = text[index - 1] ?? ' ';
                const next = text[index + run] ?? ' ';
                const leftFlanking = !/\s/.test(next) && (character !== '_' || !/[A-Za-z0-9]/.test(previous));
                const sizes = character === '~' ? (run <= 2 ? [run] : []) : [3, 2, 1].filter((size) => size <= run);
                let handled = false;
                for (const size of leftFlanking ? sizes : []) {
                    const contentStart = index + run;
                    const close = markdownEmphasisClose(text, contentStart, character, size);
                    if (close === -1 || close === contentStart) {
                        continue;
                    }
                    flush();
                    if (run > size) {
                        nodes.push(doc.createTextNode(character.repeat(run - size)));
                    }
                    const inner = parseMarkdownInline(text.slice(contentStart, close), doc, inLink);
                    let element;
                    if (character === '~') {
                        element = appendAll(doc.createElement('del'), inner);
                    } else if (size === 3) {
                        element = doc.createElement('strong');
                        element.appendChild(appendAll(doc.createElement('em'), inner));
                    } else {
                        element = appendAll(doc.createElement(size === 2 ? 'strong' : 'em'), inner);
                    }
                    nodes.push(element);
                    index = close + size;
                    handled = true;
                    break;
                }
                if (!handled) {
                    buffer += text.slice(index, index + run);
                    index += run;
                }
                continue;
            }

            const previous = text[index - 1];
            if (!inLink && (previous === undefined || /[\s*_~(]/.test(previous)) && /[A-Za-z0-9]/.test(character)) {
                const autolink = matchMarkdownAutolink(text, index);
                if (autolink) {
                    const href = markdownSafeHref(autolink.href);
                    if (href) {
                        flush();
                        const anchor = createMarkdownLink(doc, href, '');
                        anchor.textContent = autolink.text;
                        nodes.push(anchor);
                        index = autolink.end;
                        continue;
                    }
                }
            }

            buffer += character;
            index++;
        }
        flush();
        return nodes;
    }

    function splitMarkdownTableRow(line) {
        let row = line.trim();
        if (row.startsWith('|')) {
            row = row.slice(1);
        }
        if (row.endsWith('|') && !row.endsWith('\\|')) {
            row = row.slice(0, -1);
        }
        const cells = [];
        let current = '';
        for (let index = 0; index < row.length; index++) {
            if (row[index] === '\\' && row[index + 1] === '|') {
                current += '|';
                index++;
                continue;
            }
            if (row[index] === '|') {
                cells.push(current.trim());
                current = '';
                continue;
            }
            current += row[index];
        }
        cells.push(current.trim());
        return cells;
    }

    function matchMarkdownListItem(line) {
        const match = /^( {0,3})([-+*]|\d{1,9}[.)])(?:( {1,4})(.*)| *)$/.exec(line);
        if (!match) {
            return null;
        }
        const ordered = /\d/.test(match[2][0]);
        return {
            ordered,
            delimiter: ordered ? match[2].slice(-1) : match[2],
            start: ordered ? parseInt(match[2], 10) : 1,
            content: match[4] ?? '',
            indent: match[1].length + match[2].length + (match[3] ? match[3].length : 1),
        };
    }

    function isMarkdownBlockStart(line) {
        return MARKDOWN_ATX_HEADING.test(line)
            || MARKDOWN_FENCE.test(line)
            || MARKDOWN_BLOCKQUOTE.test(line)
            || MARKDOWN_THEMATIC_BREAK.test(line);
    }

    function parseMarkdownBlocks(lines, doc) {
        const nodes = [];
        let paragraph = [];
        const inline = (text) => {
            const fragment = doc.createDocumentFragment();
            for (const node of parseMarkdownInline(text, doc)) {
                fragment.appendChild(node);
            }
            return fragment;
        };
        const flushParagraph = () => {
            if (paragraph.length === 0) {
                return;
            }
            const element = doc.createElement('p');
            element.appendChild(inline(paragraph.map((line) => line.replace(/^ +/, '')).join('\n').replace(/\s+$/, '')));
            nodes.push(element);
            paragraph = [];
        };
        const blank = (line) => /^\s*$/.test(line);

        let index = 0;
        while (index < lines.length) {
            const line = lines[index];
            if (blank(line)) {
                flushParagraph();
                index++;
                continue;
            }

            const fence = MARKDOWN_FENCE.exec(line);
            if (fence && !(fence[2][0] === '`' && fence[3].includes('`'))) {
                flushParagraph();
                const marker = fence[2];
                const indent = fence[1].length;
                const closing = new RegExp('^ {0,3}' + (marker[0] === '`' ? '`' : '~') + '{' + marker.length + ',}[ \\t]*$');
                const body = [];
                index++;
                while (index < lines.length && !closing.test(lines[index])) {
                    body.push(lines[index].slice(Math.min(indent, markdownLeadingSpaces(lines[index]))));
                    index++;
                }
                index++;
                const pre = doc.createElement('pre');
                const code = doc.createElement('code');
                const language = fence[3].trim().split(/\s+/)[0].replace(/[^A-Za-z0-9_+-]/g, '');
                if (language !== '') {
                    code.className = 'language-' + language;
                }
                code.textContent = body.join('\n');
                pre.appendChild(code);
                nodes.push(pre);
                continue;
            }

            const heading = MARKDOWN_ATX_HEADING.exec(line);
            if (heading) {
                flushParagraph();
                const element = doc.createElement('h' + heading[1].length);
                element.appendChild(inline(heading[2] ?? ''));
                nodes.push(element);
                index++;
                continue;
            }

            // Setext underline turns the open paragraph into a heading.
            if (paragraph.length > 0 && /^ {0,3}(?:=+|-+)[ \t]*$/.test(line)) {
                const element = doc.createElement(line.trim()[0] === '=' ? 'h1' : 'h2');
                element.appendChild(inline(paragraph.map((entry) => entry.trim()).join('\n')));
                nodes.push(element);
                paragraph = [];
                index++;
                continue;
            }

            if (MARKDOWN_THEMATIC_BREAK.test(line)) {
                flushParagraph();
                nodes.push(doc.createElement('hr'));
                index++;
                continue;
            }

            if (MARKDOWN_BLOCKQUOTE.test(line)) {
                flushParagraph();
                const inner = [];
                while (index < lines.length && MARKDOWN_BLOCKQUOTE.test(lines[index])) {
                    inner.push(lines[index].replace(/^ {0,3}> ?/, ''));
                    index++;
                }
                const quote = doc.createElement('blockquote');
                for (const node of parseMarkdownBlocks(inner, doc)) {
                    quote.appendChild(node);
                }
                nodes.push(quote);
                continue;
            }

            if (line.includes('|') && index + 1 < lines.length && MARKDOWN_TABLE_DELIMITER.test(lines[index + 1])) {
                const header = splitMarkdownTableRow(line);
                const alignments = splitMarkdownTableRow(lines[index + 1]).map((cell) => {
                    const left = cell.startsWith(':');
                    const right = cell.endsWith(':');
                    return left && right ? 'text-center' : (right ? 'text-right' : (left ? 'text-left' : ''));
                });
                if (header.length === alignments.length) {
                    flushParagraph();
                    const table = doc.createElement('table');
                    table.className = 'table table-sm table-bordered';
                    const buildRow = (cells, tag) => {
                        const row = doc.createElement('tr');
                        alignments.forEach((alignment, column) => {
                            const cell = doc.createElement(tag);
                            if (alignment !== '') {
                                cell.className = alignment;
                            }
                            cell.appendChild(inline(cells[column] ?? ''));
                            row.appendChild(cell);
                        });
                        return row;
                    };
                    const head = doc.createElement('thead');
                    head.appendChild(buildRow(header, 'th'));
                    table.appendChild(head);
                    index += 2;
                    const body = doc.createElement('tbody');
                    while (index < lines.length && !blank(lines[index]) && lines[index].includes('|') && !isMarkdownBlockStart(lines[index])) {
                        body.appendChild(buildRow(splitMarkdownTableRow(lines[index]), 'td'));
                        index++;
                    }
                    if (body.childNodes.length > 0) {
                        table.appendChild(body);
                    }
                    nodes.push(table);
                    continue;
                }
            }

            const listItem = matchMarkdownListItem(line);
            // A list may interrupt a paragraph only when it is a bullet list or
            // starts at 1, and its first item is not empty (GFM).
            const canStartList = listItem && (paragraph.length === 0
                || ((!listItem.ordered || listItem.start === 1) && listItem.content.trim() !== ''));
            if (canStartList) {
                flushParagraph();
                const list = doc.createElement(listItem.ordered ? 'ol' : 'ul');
                if (listItem.ordered && listItem.start !== 1) {
                    list.setAttribute('start', String(listItem.start));
                }
                const items = [];
                let loose = false;
                while (index < lines.length) {
                    const current = matchMarkdownListItem(lines[index]);
                    if (!current || current.ordered !== listItem.ordered || current.delimiter !== listItem.delimiter
                        || MARKDOWN_THEMATIC_BREAK.test(lines[index])) {
                        break;
                    }
                    const itemLines = [current.content];
                    let blankInside = false;
                    index++;
                    while (index < lines.length) {
                        const next = lines[index];
                        if (blank(next)) {
                            let ahead = index + 1;
                            while (ahead < lines.length && blank(lines[ahead])) {
                                ahead++;
                            }
                            if (ahead < lines.length && markdownLeadingSpaces(lines[ahead]) >= current.indent) {
                                for (; index < ahead; index++) {
                                    itemLines.push('');
                                }
                                blankInside = true;
                                continue;
                            }
                            break;
                        }
                        if (markdownLeadingSpaces(next) >= current.indent) {
                            itemLines.push(next.slice(current.indent));
                            index++;
                            continue;
                        }
                        // Lazy continuation of the item's paragraph text.
                        if (!blankInside && !matchMarkdownListItem(next) && !isMarkdownBlockStart(next)) {
                            itemLines.push(next.trim());
                            index++;
                            continue;
                        }
                        break;
                    }
                    items.push({ lines: itemLines, blankInside });
                    if (index < lines.length && blank(lines[index])) {
                        let ahead = index;
                        while (ahead < lines.length && blank(lines[ahead])) {
                            ahead++;
                        }
                        const following = ahead < lines.length ? matchMarkdownListItem(lines[ahead]) : null;
                        if (following && following.ordered === listItem.ordered && following.delimiter === listItem.delimiter) {
                            loose = true;
                            index = ahead;
                            continue;
                        }
                        break;
                    }
                }
                for (const item of items) {
                    const element = doc.createElement('li');
                    let itemLines = item.lines;
                    const task = /^\[([ xX])\](?:[ \t]+|$)/.exec(itemLines[0]);
                    if (task) {
                        const box = doc.createElement('input');
                        box.type = 'checkbox';
                        box.disabled = true;
                        box.checked = task[1] !== ' ';
                        box.className = 'oe-questionnaire-task-box';
                        element.classList.add('oe-questionnaire-task-item');
                        element.appendChild(box);
                        element.appendChild(doc.createTextNode(' '));
                        itemLines = [itemLines[0].slice(task[0].length), ...itemLines.slice(1)];
                    }
                    for (const child of parseMarkdownBlocks(itemLines, doc)) {
                        // Tight lists render item text without <p> wrappers.
                        if (!loose && !item.blankInside && child.nodeName === 'P') {
                            while (child.firstChild) {
                                element.appendChild(child.firstChild);
                            }
                        } else {
                            element.appendChild(child);
                        }
                    }
                    list.appendChild(element);
                }
                nodes.push(list);
                continue;
            }

            paragraph.push(line);
            index++;
        }
        flushParagraph();
        return nodes;
    }

    // Renders GFM `markdown` text to a DocumentFragment of safe DOM nodes.
    function renderMarkdown(source, doc = document) {
        const fragment = doc.createDocumentFragment();
        const lines = String(source ?? '').replace(/\r\n?/g, '\n').replace(/\t/g, '    ').split('\n');
        for (const node of parseMarkdownBlocks(lines, doc)) {
            fragment.appendChild(node);
        }
        return fragment;
    }

    class QuestionnaireRenderer {
        constructor(runtime) {
            this.runtime = runtime;
            this.nodes = new Map();
            this.inputs = new Map();
            this.dynamicRendered = new Map();
            this.container = null;
            this.popup = null;
            this.popupSequence = 0;
            this.documentListeners = null;
        }

        // ---- Popups for help / legal / copyright buttons ---------------------
        // A non-modal dialog anchored under its button. One is open at a time;
        // Escape, a click outside, or its close button dismiss it.
        createPopupTrigger(kind, symbol, title, buildContent) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-sm btn-link oe-questionnaire-popup-button oe-questionnaire-' + kind + '-button py-0';
            button.textContent = symbol;
            button.title = title;
            button.setAttribute('aria-label', title);
            button.setAttribute('aria-haspopup', 'dialog');
            button.setAttribute('aria-expanded', 'false');
            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                if (this.popup?.button === button) {
                    this.closePopup(true);
                    return;
                }
                this.openPopup(button, kind, title, buildContent);
            });
            return button;
        }

        openPopup(button, kind, title, buildContent) {
            this.closePopup(false);
            const host = this.container ?? document.body;
            const panel = document.createElement('div');
            panel.id = 'oe-questionnaire-popup-' + (++this.popupSequence);
            panel.className = 'oe-questionnaire-popup oe-questionnaire-' + kind + ' card shadow';
            panel.setAttribute('role', 'dialog');
            panel.setAttribute('aria-label', title);
            panel.tabIndex = -1;

            const header = document.createElement('div');
            header.className = 'oe-questionnaire-popup-header card-header';
            const heading = document.createElement('span');
            heading.className = 'font-weight-bold';
            heading.textContent = title;
            const close = document.createElement('button');
            close.type = 'button';
            close.className = 'close oe-questionnaire-popup-close';
            close.setAttribute('aria-label', this.runtime.t('close'));
            close.textContent = '×';
            close.addEventListener('click', (event) => {
                event.preventDefault();
                this.closePopup(true);
            });
            header.append(heading, close);

            const body = document.createElement('div');
            body.className = 'oe-questionnaire-popup-body card-body';
            body.appendChild(buildContent());
            panel.append(header, body);
            host.appendChild(panel);

            button.setAttribute('aria-expanded', 'true');
            button.setAttribute('aria-controls', panel.id);
            this.popup = { button, panel };
            this.positionPopup();
            panel.focus();
        }

        positionPopup() {
            if (!this.popup || !this.container) {
                return;
            }
            const { button, panel } = this.popup;
            const hostRect = this.container.getBoundingClientRect();
            const buttonRect = button.getBoundingClientRect();
            const maxLeft = Math.max(hostRect.width - panel.offsetWidth, 0);
            const left = Math.min(Math.max(buttonRect.left - hostRect.left, 0), maxLeft);
            panel.style.top = (buttonRect.bottom - hostRect.top + 4) + 'px';
            panel.style.left = left + 'px';
        }

        closePopup(restoreFocus) {
            if (!this.popup) {
                return;
            }
            const { button, panel } = this.popup;
            this.popup = null;
            panel.remove();
            button.setAttribute('aria-expanded', 'false');
            button.removeAttribute('aria-controls');
            if (restoreFocus && button.isConnected) {
                button.focus();
            }
        }

        bindDocumentListeners() {
            if (this.documentListeners) {
                return;
            }
            const onKeyDown = (event) => {
                if (event.key === 'Escape' && this.popup) {
                    this.closePopup(true);
                }
            };
            const onPointerDown = (event) => {
                if (this.popup && !this.popup.panel.contains(event.target) && !this.popup.button.contains(event.target)) {
                    this.closePopup(false);
                }
            };
            const onResize = () => this.positionPopup();
            document.addEventListener('keydown', onKeyDown);
            document.addEventListener('mousedown', onPointerDown);
            document.addEventListener('touchstart', onPointerDown);
            global.addEventListener?.('resize', onResize);
            this.documentListeners = { onKeyDown, onPointerDown, onResize };
        }

        // Buttons for an item's help / legal display children, plus the
        // flyover text (applied by the caller as a hover title).
        buildDisplayControls(item) {
            const controls = getDisplayControls(item);
            const buttons = [];
            if (controls.help) {
                buttons.push(this.createPopupTrigger('help', '?', this.runtime.t('help'), () => renderDisplayText(controls.help, document)));
            }
            if (controls.legal) {
                buttons.push(this.createPopupTrigger('legal', '§', this.runtime.t('legal'), () => renderDisplayText(controls.legal, document)));
            }
            return { buttons, flyover: controls.flyover ? plainDisplayText(controls.flyover) : '' };
        }

        // supportHyperlink: links to more about the item, opened in a new tab.
        supportLinks(item) {
            return findExtensions(item, EXTENSIONS.SUPPORT_HYPERLINK)
                .map((extension) => markdownSafeHref(extension?.valueUri ?? extension?.valueUrl))
                .filter((href) => href !== null && /^https?:/i.test(href))
                .map((href) => {
                    const link = document.createElement('a');
                    link.className = 'oe-questionnaire-support-link small ml-1';
                    link.href = href;
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    link.textContent = this.runtime.t('moreInfo');
                    return link;
                });
        }

        // collapsible (default-open / default-closed): the group's header
        // toggles its body; header and footer item controls keep a group in view.
        applyGroupPresentation(item, wrapper, header, body) {
            const control = getItemControl(item);
            if (control === 'header' || control === 'footer') {
                wrapper.classList.add('oe-questionnaire-sticky-' + control);
            }
            const collapsible = getTypedExtensionValue(item, EXTENSIONS.COLLAPSIBLE);
            if (collapsible !== 'default-open' && collapsible !== 'default-closed') {
                return;
            }
            const toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'btn btn-sm btn-link oe-questionnaire-collapse-toggle p-0 mr-1';
            body.id = body.id || safeId('collapse-' + (wrapper.dataset.instanceKey ?? item.linkId));
            toggle.setAttribute('aria-controls', body.id);
            const set = (open) => {
                toggle.setAttribute('aria-expanded', String(open));
                toggle.textContent = open ? '▾' : '▸';
                toggle.setAttribute('aria-label', this.runtime.t(open ? 'collapse' : 'expand'));
                body.classList.toggle('d-none', !open);
            };
            toggle.addEventListener('click', () => set(toggle.getAttribute('aria-expanded') !== 'true'));
            set(collapsible === 'default-open');
            header.querySelector('.oe-questionnaire-group-title')?.prepend(toggle);
        }

        // Drop node/input entries for every instance of a repeating group
        // (keys group\u0000index\u0000...), nested instances included.
        forgetInstanceNodes(storageKey) {
            const prefix = storageKey + INSTANCE_SEPARATOR;
            for (const map of [this.nodes, this.inputs]) {
                for (const key of [...map.keys()]) {
                    if (key.startsWith(prefix)) {
                        map.delete(key);
                    }
                }
            }
        }

        applyFlyover(element, text) {
            if (text !== '') {
                element.title = text;
                element.classList.add('oe-questionnaire-flyover');
            }
        }

        mount(container) {
            this.ensureNativeControlForm();
            this.closePopup(false);
            this.container = container;
            this.bindDocumentListeners();
            container.replaceChildren();
            container.classList.add('oe-questionnaire-runtime');
            // The form's own language, so its text is read in that language (WCAG 3.1.2).
            const language = typeof this.runtime.questionnaire.language === 'string' ? this.runtime.questionnaire.language.trim() : '';
            if (language !== '') {
                container.lang = language;
            }
            container.classList.toggle('oe-questionnaire-guides', this.runtime.options.hideTreeLine === false);
            const formHeader = this.renderFormHeader();
            if (formHeader) {
                container.appendChild(formHeader);
            }
            this.warningsNode = document.createElement('div');
            this.warningsNode.className = 'oe-questionnaire-warnings d-none';
            this.warningsNode.setAttribute('role', 'alert');
            container.appendChild(this.warningsNode);
            if ((this.runtime.compiled.unsupportedCritical ?? []).length > 0 || (this.runtime.compiled.unsupportedModifiers ?? []).length > 0) {
                // The form can't be shown safely without these; show why, not the form.
                this.renderWarnings(this.runtime.collectWarnings());
                return;
            }
            const fragment = document.createDocumentFragment();
            for (const item of this.runtime.questionnaire.item ?? []) {
                fragment.appendChild(this.renderItem(item));
            }
            if (this.runtime.compiled.signatureRequirements.some((requirement) => requirement.key === '')) {
                fragment.appendChild(this.renderSignature(''));
            }
            container.appendChild(fragment);
            this.refresh();
        }

        // Form-level header: the title carries a copyright button (popup, as
        // LForms does) and buttons for any top-level help / legal display
        // items; Questionnaire.description follows inline. copyright and
        // description are FHIR markdown (GFM) rendered by the safe renderer.
        // Hosts can hide parts with options.showTitle / showCopyright /
        // showDescription === false.
        renderFormHeader() {
            const questionnaire = this.runtime.questionnaire;
            const options = this.runtime.options;
            const text = (value) => (typeof value === 'string' ? value.trim() : '');
            const titleText = text(questionnaire.title) || text(questionnaire.name);
            const copyright = options.showCopyright === false ? '' : text(questionnaire.copyright);
            const description = options.showDescription === false ? '' : text(questionnaire.description);
            const showTitle = options.showTitle !== false && titleText !== '';
            const rootControls = this.buildDisplayControls(questionnaire);
            const buttons = [...rootControls.buttons];
            if (copyright !== '') {
                buttons.unshift(this.createPopupTrigger('copyright', '©', this.runtime.t('copyright'), () => {
                    const content = document.createElement('div');
                    content.className = 'oe-questionnaire-markdown';
                    content.appendChild(renderMarkdown(copyright, document));
                    return content;
                }));
            }
            if (!showTitle && buttons.length === 0 && description === '') {
                return null;
            }
            const header = document.createElement('div');
            header.className = 'oe-questionnaire-header';
            if (showTitle || buttons.length > 0) {
                const title = document.createElement(showTitle ? 'h4' : 'div');
                title.className = 'oe-questionnaire-title';
                if (showTitle) {
                    title.appendChild(document.createTextNode(titleText));
                    this.applyFlyover(title, rootControls.flyover);
                }
                title.append(...buttons);
                header.appendChild(title);
            }
            if (description !== '') {
                const node = document.createElement('div');
                node.className = 'oe-questionnaire-description oe-questionnaire-markdown';
                node.appendChild(renderMarkdown(description, document));
                header.appendChild(node);
            }
            return header;
        }

        renderWarnings(warnings) {
            const node = this.warningsNode;
            if (!node) {
                return;
            }
            node.replaceChildren();
            // Only surface warnings that indicate real degradation (something was
            // dropped or not rendered). Advisory notices such as an omitted
            // enableBehavior are a documented compatibility default, not a
            // failure, so they stay out of the alert panel (console only).
            const blocking = (warnings ?? []).filter((warning) => warning.severity !== 'info');
            if (blocking.length === 0) {
                node.classList.add('d-none');
                return;
            }
            node.classList.remove('d-none');
            const alert = document.createElement('div');
            alert.className = 'alert alert-warning mb-3';
            const heading = document.createElement('div');
            heading.className = 'font-weight-bold';
            heading.textContent = this.runtime.t('warningsHeading');
            alert.appendChild(heading);
            const list = document.createElement('ul');
            list.className = 'mb-0 pl-3';
            for (const warning of blocking) {
                const entry = document.createElement('li');
                const linkIdLabel = warning.linkId ? '[' + warning.linkId + '] ' : '';
                entry.textContent = linkIdLabel + (warning.message ?? warning.code ?? this.runtime.t('unknownIssue'));
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
            // help / legal / flyover text lives on the parent's (or the form
            // title's) buttons; keep a hidden placeholder that refresh() never
            // toggles back into view.
            if (isDetachedDisplay(item)) {
                wrapper.classList.add('oe-questionnaire-display', 'd-none');
                return wrapper;
            }
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
            // Block row with an inline label so the help / legal buttons follow
            // the label text (wrapping with it) instead of being pushed right.
            const header = document.createElement('div');
            header.className = 'oe-questionnaire-label-row';
            const label = document.createElement('label');
            label.className = 'oe-questionnaire-label font-weight-bold d-inline mb-0';
            const labelText = [item.prefix, item.text].filter(Boolean).join(item.prefix && item.text ? ' — ' : '');
            setInlineText(label, labelText || item.linkId, markdownFor(item, 'text'), item.prefix && item.text ? item.prefix + ' — ' : '');
            if (item.required === true) {
                const required = document.createElement('span');
                required.className = 'text-danger ml-1';
                required.textContent = '*';
                // The required state is announced from aria-required below, not as "star".
                required.setAttribute('aria-hidden', 'true');
                label.appendChild(required);
            }
            header.appendChild(label);

            const inputId = safeId(storageKey);
            label.id = inputId + '-label';
            const input = this.renderInput(item, storageKey);
            this.connectLabel(item, label, input, inputId, item.required === true);

            const controls = this.buildDisplayControls(item);
            header.append(...controls.buttons, ...this.supportLinks(item));
            this.applyFlyover(label, controls.flyover);
            this.applyAdjacentDisplays(item, input);

            wrapper.appendChild(header);
            const media = mediaElement(getTypedExtensionValue(item, EXTENSIONS.ITEM_MEDIA), item.text);
            if (media) {
                wrapper.appendChild(media);
            }
            wrapper.appendChild(input);
            if (keyPrefix === '' && this.runtime.compiled.signatureRequirements.some((requirement) => requirement.key === item.linkId)) {
                wrapper.appendChild(this.renderSignature(item.linkId));
            }
            for (const child of item.item ?? []) {
                if (!isDetachedDisplay(child) && !isAdjacentDisplay(child)) {
                    wrapper.appendChild(this.renderItem(child, keyPrefix));
                }
            }

            return wrapper;
        }

        // Ties a question's label to its answer area: <label for> a single
        // entry, or a radiogroup / group named by the question for radios and
        // checkboxes, so assistive technology reads the question with each
        // answer (WCAG 1.3.1, 4.1.2); aria-required when it is required.
        connectLabel(item, label, input, inputId, required) {
            const single = input.querySelector('#' + inputId);
            if (single) {
                label.htmlFor = inputId;
                if (required) {
                    single.setAttribute('aria-required', 'true');
                }
            } else if (input.querySelector('input[type="radio"], input[type="checkbox"]')) {
                const radios = input.querySelector('input[type="checkbox"]') === null;
                input.setAttribute('role', radios ? 'radiogroup' : 'group');
                input.setAttribute('aria-labelledby', label.id);
                if (required && radios) {
                    input.setAttribute('aria-required', 'true');
                }
            }
        }

        // The expression-driven parts of the questions that have them:
        // text, required, read-only, the answer list (answerExpression,
        // rebuilt when it changes) and options turned off by
        // answerOptionsToggleExpression.
        applyDynamicState() {
            for (const [key, state] of this.runtime.dynamicState ?? []) {
                const node = this.nodes.get(key);
                const compiledItem = this.runtime.compiled.itemsByLinkId.get(key.split(INSTANCE_SEPARATOR).pop());
                if (!node || !compiledItem || !node.classList.contains('oe-questionnaire-question')) {
                    continue;
                }
                const item = compiledItem.item;
                const label = node.querySelector(':scope > .oe-questionnaire-label-row > .oe-questionnaire-label');
                let control = node.querySelector(':scope > .oe-questionnaire-control');
                if (state.answerOptions !== null && control) {
                    const signature = JSON.stringify(state.answerOptions);
                    if (this.dynamicRendered.has(key) && this.dynamicRendered.get(key) !== signature) {
                        const fresh = this.renderInput(item, key);
                        control.replaceWith(fresh);
                        control = fresh;
                        this.connectLabel(item, label, fresh, safeId(key), this.runtime.isRequired(key, item));
                    }
                    this.dynamicRendered.set(key, signature);
                }
                if (label && state.text !== null && label.firstChild?.nodeType === Node.TEXT_NODE) {
                    label.firstChild.textContent = [item.prefix, state.text].filter(Boolean).join(item.prefix ? ' — ' : '');
                }
                if (label && state.required !== null) {
                    let mark = label.querySelector(':scope > .text-danger');
                    if (state.required && !mark) {
                        mark = document.createElement('span');
                        mark.className = 'text-danger ml-1';
                        mark.textContent = '*';
                        mark.setAttribute('aria-hidden', 'true');
                        label.appendChild(mark);
                    } else if (!state.required && mark) {
                        mark.remove();
                    }
                    const target = control?.querySelector('#' + safeId(key)) ?? (control?.getAttribute('role') === 'radiogroup' ? control : null);
                    if (target) {
                        if (state.required) {
                            target.setAttribute('aria-required', 'true');
                        } else {
                            target.removeAttribute('aria-required');
                        }
                    }
                }
                if (control && state.readOnly !== null) {
                    for (const field of control.querySelectorAll('input, select, textarea, button')) {
                        field.disabled = state.readOnly;
                    }
                }
                if (control && compiledItem.optionToggles.length > 0) {
                    // Re-applied on every change, so an option comes back when its expression turns true.
                    const options = this.runtime.answerOptionsFor(item, key);
                    const off = (index) => {
                        const value = getAnswerOptionValue(options[index]);
                        return value !== null && state.offOptions.some((candidate) => equalPrimitive(candidate, value.value));
                    };
                    const readOnly = state.readOnly ?? item.readOnly === true;
                    for (const field of control.querySelectorAll('input[type="radio"], input[type="checkbox"], option')) {
                        if (field.value !== '') {
                            field.disabled = (field.tagName !== 'OPTION' && readOnly) || off(Number(field.value));
                        }
                    }
                }
            }
        }

        // A signature for signatureRequired: drawn on a pad, or typed as a
        // full name for anyone who can't draw one (keyboard, screen reader).
        renderSignature(key) {
            const box = document.createElement('div');
            box.className = 'oe-questionnaire-signature mt-2';
            this.nodes.set(signatureNodeKey(key), box);
            const idBase = safeId('signature-' + (key || 'form'));
            const heading = document.createElement('div');
            heading.className = 'small font-weight-bold';
            heading.id = idBase + '-heading';
            heading.textContent = this.runtime.t('signHere');
            const canvas = document.createElement('canvas');
            canvas.width = 400;
            canvas.height = 120;
            canvas.className = 'oe-questionnaire-signature-pad border d-block';
            canvas.setAttribute('role', 'img');
            canvas.setAttribute('aria-label', this.runtime.t('signaturePad'));
            const typedLabel = document.createElement('label');
            typedLabel.className = 'small mt-1 mb-0 d-block';
            typedLabel.htmlFor = idBase + '-typed';
            typedLabel.textContent = this.runtime.t('typeToSign');
            const typed = document.createElement('input');
            typed.type = 'text';
            typed.id = idBase + '-typed';
            typed.className = 'form-control form-control-sm';
            typed.autocomplete = 'name';
            const clear = document.createElement('button');
            clear.type = 'button';
            clear.className = 'btn btn-sm btn-link p-0 mt-1';
            clear.textContent = this.runtime.t('clear');
            const existing = this.runtime.signatures.get(key);
            const context = typeof canvas.getContext === 'function' ? (() => {
                try {
                    return canvas.getContext('2d');
                } catch {
                    return null;
                }
            })() : null;
            if (existing?.sigFormat === 'text/plain') {
                try {
                    typed.value = base64Utf8(existing.data);
                } catch {
                    typed.value = '';
                }
            } else if (existing && context) {
                const image = new Image();
                image.onload = () => context.drawImage(image, 0, 0);
                image.src = 'data:' + existing.sigFormat + ';base64,' + existing.data;
            }
            if (context) {
                let drawing = false;
                const point = (event) => {
                    const rect = canvas.getBoundingClientRect();
                    return [(event.clientX - rect.left) * (canvas.width / (rect.width || canvas.width)), (event.clientY - rect.top) * (canvas.height / (rect.height || canvas.height))];
                };
                canvas.addEventListener('pointerdown', (event) => {
                    drawing = true;
                    context.beginPath();
                    context.moveTo(...point(event));
                });
                canvas.addEventListener('pointermove', (event) => {
                    if (drawing) {
                        context.lineTo(...point(event));
                        context.stroke();
                    }
                });
                const finish = () => {
                    if (!drawing) {
                        return;
                    }
                    drawing = false;
                    typed.value = '';
                    this.runtime.setSignature(key, { data: canvas.toDataURL('image/png').split(',')[1], sigFormat: 'image/png' });
                };
                canvas.addEventListener('pointerup', finish);
                canvas.addEventListener('pointerleave', finish);
            } else {
                canvas.classList.add('d-none');
            }
            typed.addEventListener('change', () => {
                const name = typed.value.trim();
                context?.clearRect(0, 0, canvas.width, canvas.height);
                this.runtime.setSignature(key, name === '' ? null : { data: utf8Base64(name), sigFormat: 'text/plain', name });
            });
            clear.addEventListener('click', () => {
                context?.clearRect(0, 0, canvas.width, canvas.height);
                typed.value = '';
                this.runtime.setSignature(key, null);
            });
            box.append(heading, canvas, typedLabel, typed, clear);
            return box;
        }

        // A question's prompt, unit, lower and upper display children, placed
        // in or beside its answer area.
        applyAdjacentDisplays(item, control) {
            const texts = {};
            for (const child of item.item ?? []) {
                if (isAdjacentDisplay(child)) {
                    texts[getItemControl(child)] = texts[getItemControl(child)] ?? plainDisplayText(child);
                }
            }
            const note = (kind, text) => {
                const node = document.createElement('span');
                node.className = 'oe-questionnaire-' + kind + ' small text-muted';
                node.textContent = text;
                return node;
            };
            const entry = control.querySelector('input:not([type="radio"]):not([type="checkbox"]):not([type="file"]), textarea');
            if (texts.prompt) {
                if (entry && entry.placeholder === '') {
                    entry.placeholder = texts.prompt;
                } else {
                    const prompt = note('prompt', texts.prompt);
                    prompt.classList.add('d-block');
                    control.appendChild(prompt);
                }
            }
            if (texts.unit) {
                if (entry && entry.tagName === 'INPUT' && !entry.closest('.input-group')) {
                    const group = document.createElement('div');
                    group.className = 'input-group';
                    entry.replaceWith(group);
                    const append = document.createElement('div');
                    append.className = 'input-group-append';
                    const unit = document.createElement('span');
                    unit.className = 'input-group-text oe-questionnaire-unit';
                    unit.textContent = texts.unit;
                    append.appendChild(unit);
                    group.append(entry, append);
                } else if (!entry?.closest('.input-group')) {
                    control.appendChild(note('unit', ' ' + texts.unit));
                }
            }
            if (texts.lower) {
                control.prepend(note('lower', texts.lower));
            }
            if (texts.upper) {
                control.appendChild(note('upper', texts.upper));
            }
            if (texts.lower || texts.upper) {
                control.classList.add('oe-questionnaire-scale');
            }
        }

        // Group card header. Help / legal buttons sit inside the heading,
        // directly after its text, rather than on a line of their own.
        // A group whose text is explicitly empty ("text": "") has no header, only its body; one with no
        // text at all still shows its linkId. A header that carries help, a legal notice, support
        // links, media or the collapse toggle stays, with no title text.
        buildGroupHeader(item) {
            const controls = this.buildDisplayControls(item);
            const links = this.supportLinks(item);
            const untitled = item.text === '' && !item.prefix;
            const collapsible = ['default-open', 'default-closed'].includes(getTypedExtensionValue(item, EXTENSIONS.COLLAPSIBLE));
            if (untitled && controls.buttons.length === 0 && links.length === 0 && !collapsible
                && !getTypedExtensionValue(item, EXTENSIONS.ITEM_MEDIA)) {
                return null;
            }
            const header = document.createElement('div');
            header.className = 'card-header';
            const title = document.createElement('h5');
            title.className = 'mb-1 oe-questionnaire-group-title';
            const titleMarkdown = item.prefix ? markdownFor(item, 'prefix') : markdownFor(item, 'text');
            if (titleMarkdown !== null) {
                title.append(...parseMarkdownInline(titleMarkdown, document));
            } else if (!untitled) {
                title.appendChild(document.createTextNode(item.prefix || item.text || item.linkId));
            }
            title.append(...controls.buttons, ...links);
            this.applyFlyover(title, controls.flyover);
            header.appendChild(title);
            if (item.prefix && item.text) {
                const description = document.createElement('div');
                description.className = 'small text-muted';
                setInlineText(description, item.text, markdownFor(item, 'text'));
                header.appendChild(description);
            }
            const media = mediaElement(getTypedExtensionValue(item, EXTENSIONS.ITEM_MEDIA), item.text);
            if (media) {
                header.appendChild(media);
            }
            return header;
        }

        renderGroup(item, wrapper, keyPrefix = '') {
            wrapper.classList.add('card', 'mb-3', 'oe-questionnaire-group');
            const header = this.buildGroupHeader(item);
            if (header) {
                wrapper.appendChild(header);
            }

            const body = document.createElement('div');
            body.className = 'card-body';
            this.applyGroupPresentation(item, wrapper, header, body);
            if (getItemControl(item) === 'table' && this.renderMatrix(item, body, keyPrefix)) {
                wrapper.appendChild(body);
                return wrapper;
            }
            for (const child of item.item ?? []) {
                if (isDetachedDisplay(child)) {
                    continue;
                }
                body.appendChild(this.renderItem(child, keyPrefix));
            }
            wrapper.appendChild(body);
            return wrapper;
        }

        // table: "questions within the group are rows in the table with
        // possible answers as columns", for groups of choice questions that
        // share their answers (the PHQ-9's shape). Each row is the question's
        // node, so enableWhen and validation work as usual; each answer is
        // named by its row and column headers. A group with anything else in
        // it renders as usual.
        renderMatrix(item, body, keyPrefix) {
            const children = (item.item ?? []).filter((child) => !isDetachedDisplay(child));
            const choice = (child) => (child.type === 'choice' || child.type === 'open-choice' || OPTION_CAPABLE_TYPES.includes(child.type))
                && (child.item ?? []).every(isDetachedDisplay)
                && this.runtime.answerOptionsFor(child).some((option) => getAnswerOptionValue(option) !== null);
            if (children.length === 0 || !children.every(choice)) {
                return false;
            }
            const columns = [];
            for (const child of children) {
                for (const option of this.runtime.answerOptionsFor(child)) {
                    const value = getAnswerOptionValue(option);
                    if (value !== null && !columns.some((column) => column.value.valueKey === value.valueKey && equalPrimitive(column.value.value, value.value))) {
                        columns.push({ value, label: answerOptionLabel(option, value) });
                    }
                }
            }
            const tableId = safeId(keyPrefix + item.linkId) + '-matrix';
            const table = document.createElement('table');
            table.className = 'table table-sm oe-questionnaire-matrix';
            const headRow = table.createTHead().insertRow();
            headRow.appendChild(document.createElement('td'));
            columns.forEach((column, index) => {
                const heading = document.createElement('th');
                heading.scope = 'col';
                heading.id = tableId + '-col-' + index;
                heading.textContent = column.label;
                headRow.appendChild(heading);
            });
            const tbody = table.createTBody();
            for (const child of children) {
                const storageKey = keyPrefix + child.linkId;
                const row = tbody.insertRow();
                row.dataset.linkId = child.linkId;
                if (keyPrefix !== '') {
                    row.dataset.instanceKey = storageKey;
                }
                row.className = 'oe-questionnaire-item oe-questionnaire-question';
                this.nodes.set(storageKey, row);
                const heading = document.createElement('th');
                heading.scope = 'row';
                heading.id = safeId(storageKey) + '-label';
                heading.className = 'oe-questionnaire-label';
                heading.textContent = [child.prefix, child.text].filter(Boolean).join(child.prefix && child.text ? ' — ' : '') || child.linkId;
                if (this.runtime.isRequired(storageKey, child)) {
                    const required = document.createElement('span');
                    required.className = 'text-danger ml-1';
                    required.textContent = '*';
                    required.setAttribute('aria-hidden', 'true');
                    heading.appendChild(required);
                }
                row.appendChild(heading);
                const options = this.runtime.answerOptionsFor(child, storageKey);
                const repeats = child.repeats === true;
                columns.forEach((column, columnIndex) => {
                    const cell = row.insertCell();
                    const optionIndex = options.findIndex((option) => {
                        const value = getAnswerOptionValue(option);
                        return value !== null && value.valueKey === column.value.valueKey && equalPrimitive(value.value, column.value.value);
                    });
                    if (optionIndex === -1) {
                        return;
                    }
                    const optionAnswer = getAnswerOptionValue(options[optionIndex]);
                    const input = document.createElement('input');
                    input.type = repeats ? 'checkbox' : 'radio';
                    input.name = safeId(storageKey);
                    input.id = safeId(storageKey) + '-' + optionIndex;
                    input.value = String(optionIndex);
                    input.setAttribute('form', NATIVE_CONTROL_FORM_ID);
                    input.setAttribute('aria-labelledby', heading.id + ' ' + tableId + '-col-' + columnIndex);
                    input.checked = this.runtime.responseStore.getAnswers(storageKey).some((answer) => answer.valueKey === optionAnswer.valueKey && equalPrimitive(answer.value, optionAnswer.value));
                    input.disabled = child.readOnly === true;
                    const ordinal = getOrdinalValue(options[optionIndex]);
                    if (ordinal !== null) {
                        input.dataset.ordinal = String(ordinal);
                    }
                    input.addEventListener('change', () => {
                        const current = this.runtime.responseStore.getAnswers(storageKey);
                        const same = (answer) => answer.valueKey === optionAnswer.valueKey && equalPrimitive(answer.value, optionAnswer.value);
                        if (repeats) {
                            this.runtime.setAnswers(storageKey, input.checked ? [...current, optionAnswer] : current.filter((answer) => !same(answer)));
                        } else if (input.checked) {
                            this.runtime.setAnswers(storageKey, [optionAnswer]);
                        }
                    });
                    cell.appendChild(input);
                });
            }
            const scroll = document.createElement('div');
            scroll.className = 'table-responsive';
            scroll.appendChild(table);
            body.appendChild(scroll);
            return true;
        }

        // gtable: "questions within the group are columns in the table with
        // each group repetition as a row" (a medication list, a log). Each
        // cell is its question's node for that repetition, named by its
        // column header.
        renderGridTable(item, keyPrefix, count, minOccurs, rerender) {
            const storageKey = keyPrefix + item.linkId;
            const columns = (item.item ?? []).filter((child) => !isDetachedDisplay(child) && child.type !== 'display');
            const tableId = safeId(storageKey) + '-grid';
            const table = document.createElement('table');
            table.className = 'table table-sm oe-questionnaire-grid';
            const headRow = table.createTHead().insertRow();
            columns.forEach((child, index) => {
                const heading = document.createElement('th');
                heading.scope = 'col';
                heading.id = tableId + '-col-' + index;
                heading.textContent = getStringExtension(child, EXTENSIONS.SHORT_TEXT) || child.text || child.linkId;
                const width = getTypedExtensionValue(child, EXTENSIONS.WIDTH);
                const widthUnit = width?.code ?? width?.unit;
                if (typeof width?.value === 'number' && ['%', 'em', 'rem', 'px'].includes(widthUnit)) {
                    heading.style.width = width.value + widthUnit;
                }
                if (child.required === true) {
                    const required = document.createElement('span');
                    required.className = 'text-danger ml-1';
                    required.textContent = '*';
                    required.setAttribute('aria-hidden', 'true');
                    heading.appendChild(required);
                }
                headRow.appendChild(heading);
            });
            headRow.appendChild(document.createElement('td'));
            const tbody = table.createTBody();
            for (let instanceIndex = 0; instanceIndex < count; instanceIndex++) {
                const instancePrefix = instanceKeyPrefix(storageKey, instanceIndex);
                const row = tbody.insertRow();
                row.className = 'oe-questionnaire-group-instance';
                row.dataset.instanceIndex = String(instanceIndex);
                columns.forEach((child, columnIndex) => {
                    const key = instancePrefix + child.linkId;
                    const cell = row.insertCell();
                    if (child.type === 'group') {
                        cell.appendChild(this.renderItem(child, instancePrefix));
                        return;
                    }
                    cell.dataset.linkId = child.linkId;
                    cell.dataset.instanceKey = key;
                    cell.className = 'oe-questionnaire-item oe-questionnaire-question';
                    this.nodes.set(key, cell);
                    const control = this.renderInput(child, key);
                    const headingId = tableId + '-col-' + columnIndex;
                    const single = control.querySelector('#' + safeId(key));
                    if (single) {
                        single.setAttribute('aria-labelledby', headingId);
                        if (child.required === true) {
                            single.setAttribute('aria-required', 'true');
                        }
                    } else if (control.querySelector('input[type="radio"], input[type="checkbox"]')) {
                        control.setAttribute('role', control.querySelector('input[type="checkbox"]') ? 'group' : 'radiogroup');
                        control.setAttribute('aria-labelledby', headingId);
                    }
                    cell.appendChild(control);
                });
                const actions = row.insertCell();
                if (item.readOnly !== true && count > minOccurs) {
                    const removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.className = 'btn btn-sm btn-link text-danger oe-questionnaire-repeat-remove py-0';
                    removeButton.textContent = this.runtime.t('remove');
                    removeButton.addEventListener('click', () => {
                        this.runtime.responseStore.removeRepeatInstance(storageKey, instanceIndex);
                        rerender();
                        this.runtime.notifyStructureChanged();
                    });
                    actions.appendChild(removeButton);
                }
            }
            const scroll = document.createElement('div');
            scroll.className = 'table-responsive';
            scroll.appendChild(table);
            return scroll;
        }

        renderRepeatingGroup(item, wrapper, keyPrefix) {
            const storageKey = keyPrefix + item.linkId;
            wrapper.classList.add('card', 'mb-3', 'oe-questionnaire-group', 'oe-questionnaire-repeating-group');

            const header = this.buildGroupHeader(item);
            if (header) {
                wrapper.appendChild(header);
            }

            const body = document.createElement('div');
            body.className = 'card-body';
            wrapper.appendChild(body);
            this.applyGroupPresentation(item, wrapper, header, body);

            const minOccurs = Math.max(getIntegerExtension(item, EXTENSIONS.MIN_OCCURS) ?? 1, 1);
            const maxOccurs = getIntegerExtension(item, EXTENSIONS.MAX_OCCURS);
            if (this.runtime.responseStore.getRepeatCount(storageKey) < minOccurs) {
                this.runtime.responseStore.setRepeatCount(storageKey, minOccurs);
            }

            const rerender = () => {
                body.replaceChildren();
                // Forget the instances' old nodes and inputs (they are rebuilt
                // below); after a removal they would point at detached elements.
                this.forgetInstanceNodes(storageKey);
                const count = this.runtime.responseStore.getRepeatCount(storageKey);
                if (getItemControl(item) === 'gtable') {
                    body.appendChild(this.renderGridTable(item, keyPrefix, count, minOccurs, rerender));
                } else {
                    for (let instanceIndex = 0; instanceIndex < count; instanceIndex++) {
                        body.appendChild(this.renderGroupInstance(item, keyPrefix, instanceIndex, count, minOccurs, rerender));
                    }
                }
                const addButton = document.createElement('button');
                addButton.type = 'button';
                addButton.className = 'btn btn-sm btn-outline-primary oe-questionnaire-repeat-add';
                addButton.textContent = this.runtime.t('add', item.text || this.runtime.t('addItem'));
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
            label.textContent = this.runtime.t('instance', instanceIndex + 1);
            instanceHeader.appendChild(label);
            if (item.readOnly !== true && count > minOccurs) {
                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'btn btn-sm btn-link text-danger oe-questionnaire-repeat-remove py-0';
                removeButton.textContent = this.runtime.t('remove');
                removeButton.addEventListener('click', () => {
                    this.runtime.responseStore.removeRepeatInstance(storageKey, instanceIndex);
                    rerender();
                    this.runtime.notifyStructureChanged();
                });
                instanceHeader.appendChild(removeButton);
            }
            instance.appendChild(instanceHeader);

            for (const child of item.item ?? []) {
                // help / legal / flyover displays surface once on the group header.
                if (isDetachedDisplay(child)) {
                    continue;
                }
                instance.appendChild(this.renderItem(child, instancePrefix));
            }
            return instance;
        }

        renderDisplay(item, wrapper) {
            wrapper.classList.add('oe-questionnaire-display');
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
            const markdown = markdownFor(item, 'text');
            if (markdown !== null) {
                text.appendChild(renderMarkdown(markdown, document));
            } else {
                text.textContent = item.text ?? '';
            }
            display.appendChild(text);
            const media = mediaElement(getTypedExtensionValue(item, EXTENSIONS.ITEM_MEDIA), item.text);
            if (media) {
                display.appendChild(media);
            }
            wrapper.appendChild(display);
            return wrapper;
        }

        renderInput(item, storageKey = item.linkId) {
            const container = document.createElement('div');
            container.className = 'oe-questionnaire-control';
            const control = getItemControl(item);
            const answers = this.runtime.responseStore.getAnswers(storageKey);
            const inputId = safeId(storageKey);

            if (OPTION_CAPABLE_TYPES.includes(item.type)
                && this.runtime.answerOptionsFor(item, storageKey).some((option) => getAnswerOptionValue(option) !== null)) {
                const openText = (item.type === 'string' || item.type === 'text') && allowsFreeTextAnswer(item);
                return this.renderChoice(item, container, control, answers, inputId, openText, storageKey);
            }

            // Repeating scalar items (repeats=true on a non-choice, non-group
            // item) get an add/remove row list. choice/open-choice express
            // repeats through checkboxes and are handled in renderChoice.
            const repeatableScalar = ['string', 'text', 'url', 'integer', 'decimal', 'date', 'dateTime', 'time', 'reference'];
            if (item.repeats === true && repeatableScalar.includes(item.type)) {
                return this.renderRepeatingScalar(item, container, storageKey);
            }
            if (item.repeats === true && ['boolean', 'quantity', 'attachment'].includes(item.type)) {
                return this.renderRepeatingRows(item, container, storageKey, inputId);
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
                    unsupported.textContent = this.runtime.t('unsupportedType', item.type);
                    container.appendChild(unsupported);
                    return container;
                }
            }
        }

        renderChoice(item, container, control, answers, inputId, allowOpenChoice, storageKey = item.linkId) {
            const options = this.runtime.answerOptionsFor(item, storageKey);
            const selected = answers;
            const repeats = item.repeats === true;
            const useCheckboxes = repeats || control === 'check-box';
            // spinner ("a list of ... ordered values scrolled through via arrows") is a drop-down here.
            const useRadios = !useCheckboxes && (control === 'radio-button' || (control !== 'drop-down' && control !== 'spinner' && options.length <= 6));

            const orientation = getChoiceOrientation(item);
            if (control === 'autocomplete' && !repeats) {
                return this.renderAutocomplete(item, container, options, selected, inputId, allowOpenChoice, storageKey);
            }
            if (useCheckboxes || useRadios) {
                const inputType = useCheckboxes ? 'checkbox' : 'radio';
                const columns = getIntegerExtension(item, EXTENSIONS.COLUMN_COUNT);
                if (columns !== null && columns > 1 && orientation !== 'horizontal') {
                    container.classList.add('oe-questionnaire-columns');
                    container.style.columnCount = String(columns);
                }
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
                            const same = (answer, value) => answer.valueKey === value.valueKey && equalPrimitive(answer.value, value.value);
                            let next = input.checked
                                ? [...current, optionAnswer]
                                : current.filter((answer) => !same(answer, optionAnswer));
                            // optionExclusive: "None of these" clears the others, and any other clears it.
                            if (input.checked) {
                                const exclusive = options.filter(isExclusiveOption).map(getAnswerOptionValue).filter(Boolean);
                                next = isExclusiveOption(option)
                                    ? [optionAnswer]
                                    : next.filter((answer) => !exclusive.some((value) => same(answer, value)));
                            }
                            for (const box of container.querySelectorAll('input[type="checkbox"]')) {
                                const boxAnswer = getAnswerOptionValue(options[Number(box.value)]);
                                box.checked = boxAnswer !== null && next.some((answer) => same(answer, boxAnswer));
                            }
                            this.runtime.setAnswers(storageKey, next);
                        } else if (input.checked) {
                            this.runtime.setAnswers(storageKey, [optionAnswer]);
                        }
                    });
                    const label = document.createElement('label');
                    label.className = 'custom-control-label oe-questionnaire-choice-label';
                    label.htmlFor = input.id;
                    const plainLabel = answerOptionLabel(option, optionAnswer);
                    const optionPrefix = plainLabel.slice(0, plainLabel.length - answerDisplay(optionAnswer).length);
                    setInlineText(label, plainLabel, answerOptionMarkdown(option), optionPrefix);
                    label.title = plainLabel;
                    const answerMedia = mediaElement(getTypedExtensionValue(option, EXTENSIONS.ITEM_ANSWER_MEDIA), plainLabel);
                    if (answerMedia) {
                        label.appendChild(answerMedia);
                    }
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
                    optionElement.textContent = answerOptionLabel(option, optionAnswer);
                    optionElement.title = optionElement.textContent;
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
                openInput.placeholder = openLabelText !== '' ? openLabelText : this.runtime.t('other');
                // The free-text answer is a valueString that is not one of the
                // options (string-typed options are valueString too).
                const optionAnswers = options.map(getAnswerOptionValue).filter(Boolean);
                const customAnswer = selected.find((answer) => answer.valueKey === 'valueString'
                    && !optionAnswers.some((optionAnswer) => optionAnswer.valueKey === 'valueString' && equalPrimitive(optionAnswer.value, answer.value)));
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

        // autocomplete: "a list of potential matches based on text entered",
        // for long answer lists. The typed text is matched to an option's
        // label; an open choice keeps unmatched text as its own answer.
        renderAutocomplete(item, container, options, selected, inputId, allowOpenChoice, storageKey) {
            const input = document.createElement('input');
            input.type = 'text';
            input.id = inputId;
            input.className = 'form-control oe-questionnaire-autocomplete';
            input.autocomplete = 'off';
            input.disabled = item.readOnly === true;
            const list = document.createElement('datalist');
            list.id = inputId + '-options';
            input.setAttribute('list', list.id);
            const labelled = options.map((option) => ({ option, answer: getAnswerOptionValue(option) }))
                .filter((entry) => entry.answer !== null)
                .map((entry) => ({ ...entry, label: answerOptionLabel(entry.option, entry.answer) }));
            for (const entry of labelled) {
                const element = document.createElement('option');
                element.value = entry.label;
                list.appendChild(element);
            }
            const current = labelled.find((entry) => selected.some((answer) => answer.valueKey === entry.answer.valueKey && equalPrimitive(answer.value, entry.answer.value)));
            const custom = selected.find((answer) => answer.valueKey === 'valueString');
            input.value = current?.label ?? (allowOpenChoice ? custom?.value ?? '' : '');
            input.addEventListener('change', () => {
                const text = input.value.trim();
                const match = labelled.find((entry) => entry.label === text)
                    ?? labelled.find((entry) => entry.label.toLowerCase() === text.toLowerCase());
                if (text === '') {
                    this.runtime.setAnswers(storageKey, []);
                } else if (match) {
                    this.runtime.setAnswers(storageKey, [match.answer]);
                } else if (allowOpenChoice) {
                    this.runtime.setAnswers(storageKey, [{ valueKey: 'valueString', value: text }]);
                } else {
                    this.runtime.setAnswers(storageKey, []);
                }
            });
            container.append(input, list);
            this.inputs.set(storageKey, container);
            return container;
        }

        // onAnswer receives the one answer the control makes (null to clear); by default it
        // becomes the item's answer. A repeating item's rows pass their own.
        renderBoolean(item, container, answers, inputId, storageKey = item.linkId, onAnswer = (answer) => this.runtime.setAnswers(storageKey, answer ? [answer] : [])) {
            const selected = firstValue(answers)?.value;
            [
                { value: true, label: this.runtime.t('yes') },
                { value: false, label: this.runtime.t('no') },
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
                        onAnswer({ valueKey: 'valueBoolean', value: option.value });
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
            const sliderMin = getTypedExtensionValue(item, EXTENSIONS.MIN_VALUE);
            const sliderMax = getTypedExtensionValue(item, EXTENSIONS.MAX_VALUE);
            if (getItemControl(item) === 'slider' && typeof sliderMin === 'number' && typeof sliderMax === 'number') {
                return this.renderSlider(item, container, answers, inputId, storageKey, sliderMin, sliderMax);
            }
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

        // slider: a range between minValue and maxValue, stepped by
        // sliderStepValue (1 by default). Nothing is answered until the
        // slider is moved; the current value is shown beside it.
        renderSlider(item, container, answers, inputId, storageKey, min, max) {
            const row = document.createElement('div');
            row.className = 'oe-questionnaire-slider d-flex align-items-center';
            const input = document.createElement('input');
            input.type = 'range';
            input.id = inputId;
            input.className = 'custom-range';
            input.min = String(min);
            input.max = String(max);
            const step = getTypedExtensionValue(item, EXTENSIONS.SLIDER_STEP_VALUE);
            input.step = typeof step === 'number' && step > 0 ? String(step) : (item.type === 'integer' ? '1' : 'any');
            const current = firstValue(answers)?.value;
            input.value = typeof current === 'number' ? String(current) : String(min);
            input.disabled = item.readOnly === true;
            const output = document.createElement('output');
            output.className = 'ml-2 oe-questionnaire-slider-value';
            output.htmlFor = inputId;
            output.textContent = typeof current === 'number' ? String(current) : '–';
            const commit = () => {
                const value = item.type === 'integer' ? Number.parseInt(input.value, 10) : Number.parseFloat(input.value);
                output.textContent = String(value);
                this.runtime.setAnswers(storageKey, Number.isFinite(value) ? [{ valueKey: this.runtime.answerValueKeyForItem(item), value }] : []);
            };
            input.addEventListener('change', commit);
            input.addEventListener('input', () => {
                output.textContent = input.value;
            });
            row.append(input, output);
            container.appendChild(row);
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
            } else if (item.type === 'time') {
                value = formatTimeInputValue(value);
            }
            input.value = value;
            input.disabled = item.readOnly === true;
            input.addEventListener('change', () => {
                let answerValue = input.value;
                if (item.type === 'dateTime') {
                    answerValue = toFhirDateTime(answerValue);
                } else if (item.type === 'time') {
                    answerValue = toFhirTime(answerValue);
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
            const keyboard = getTypedExtensionValue(item, EXTENSIONS.KEYBOARD)?.code;
            if (KEYBOARD_INPUT_MODES[keyboard]) {
                input.inputMode = KEYBOARD_INPUT_MODES[keyboard];
            }
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

        renderQuantity(item, container, answers, inputId, storageKey = item.linkId, onAnswer = (answer) => this.runtime.setAnswers(storageKey, answer ? [answer] : [])) {
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
            // questionnaire-unitOption lists the allowed units (Codings). Closed
            // by default -> a select; sdc-questionnaire-unitOpen
            // optionsOrString / optionsOrType -> free text with suggestions.
            const unitValueSet = containedResourceById(this.runtime.questionnaire, getTypedExtensionValue(item, EXTENSIONS.UNIT_VALUE_SET));
            let unitOptions = [
                ...findExtensions(item, EXTENSIONS.UNIT_OPTION).map((extension) => extension?.valueCoding),
                ...(unitValueSet?.resourceType === 'ValueSet' ? valueSetToAnswerOptions(unitValueSet).map((option) => option.valueCoding) : []),
            ].filter((coding) => coding && (typeof coding.code === 'string' || typeof coding.display === 'string'));
            // With no unit options, a lone questionnaire-unit is the question's one unit, so the
            // answer carries it and comparisons in that unit work. R4 defines questionnaire-unit
            // for integer and decimal questions only, and LHC-Forms refuses it on a quantity
            // question; reading it here is a leniency for forms such as WHO SMART's that put it there.
            const fixedUnit = findExtension(item, EXTENSIONS.QUESTIONNAIRE_UNIT)?.valueCoding;
            if (unitOptions.length === 0 && fixedUnit && (typeof fixedUnit.code === 'string' || typeof fixedUnit.display === 'string')) {
                unitOptions = [fixedUnit];
            }
            const unitLabel = (coding) => coding.display ?? coding.code;
            const unitOpen = ['optionsOrString', 'optionsOrType'].includes(getStringExtension(item, EXTENSIONS.UNIT_OPEN));
            const currentUnit = quantity.unit ?? quantity.code ?? '';
            let unitInput;
            if (unitOptions.length > 0 && !unitOpen) {
                unitInput = document.createElement('select');
                unitInput.className = 'form-control oe-questionnaire-unit-select';
                unitInput.setAttribute('aria-label', this.runtime.t('unit'));
                const blank = document.createElement('option');
                blank.value = '';
                blank.textContent = '';
                unitInput.appendChild(blank);
                unitOptions.forEach((coding, index) => {
                    const option = document.createElement('option');
                    option.value = String(index);
                    option.textContent = unitLabel(coding);
                    if (typeof coding.code === 'string') {
                        option.dataset.code = coding.code;
                    }
                    option.selected = currentUnit !== '' && (coding.code === quantity.code || unitLabel(coding) === currentUnit);
                    unitInput.appendChild(option);
                });
                if (currentUnit === '' && unitOptions.length > 0) {
                    // LForms preselects the first unit option.
                    unitInput.value = '0';
                }
            } else {
                unitInput = document.createElement('input');
                unitInput.type = 'text';
                unitInput.className = 'form-control';
                unitInput.placeholder = this.runtime.t('unit');
                unitInput.setAttribute('aria-label', this.runtime.t('unit'));
                unitInput.value = currentUnit;
                if (unitOptions.length > 0) {
                    const list = document.createElement('datalist');
                    list.id = inputId + '-units';
                    for (const coding of unitOptions) {
                        const option = document.createElement('option');
                        option.value = unitLabel(coding);
                        list.appendChild(option);
                    }
                    unitInput.setAttribute('list', list.id);
                    unitColumn.appendChild(list);
                }
            }
            unitInput.disabled = item.readOnly === true;
            unitColumn.appendChild(unitInput);
            row.append(valueColumn, unitColumn);
            const save = () => {
                if (valueInput.value === '') {
                    onAnswer(null);
                    return;
                }
                const next = { value: Number.parseFloat(valueInput.value) };
                if (unitInput.tagName === 'SELECT') {
                    const coding = unitInput.value === '' ? null : unitOptions[Number(unitInput.value)];
                    if (coding) {
                        next.unit = unitLabel(coding);
                        if (typeof coding.system === 'string') {
                            next.system = coding.system;
                        }
                        if (typeof coding.code === 'string') {
                            next.code = coding.code;
                        }
                    }
                } else {
                    const unit = unitInput.value.trim();
                    const coding = unitOptions.find((entry) => unitLabel(entry) === unit);
                    if (coding) {
                        next.unit = unitLabel(coding);
                        if (typeof coding.system === 'string') {
                            next.system = coding.system;
                        }
                        if (typeof coding.code === 'string') {
                            next.code = coding.code;
                        }
                    } else if (unit !== '') {
                        next.unit = unit;
                        next.code = unit;
                    }
                }
                onAnswer({ valueKey: 'valueQuantity', value: next });
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
            const types = referenceTypes(item);
            input.placeholder = getEntryFormat(item)
                || (types.length > 0 ? this.runtime.t('referenceTo', types.join(', '), types[0]) : this.runtime.t('referencePlaceholder'));
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

        renderAttachment(item, container, answers, inputId, storageKey = item.linkId, onAnswer = (answer) => this.runtime.setAnswers(storageKey, answer ? [answer] : [])) {
            const existing = firstValue(answers)?.value;
            const input = document.createElement('input');
            input.type = 'file';
            input.id = inputId;
            input.className = 'form-control-file';
            input.disabled = item.readOnly === true;
            const mimeTypes = attachmentMimeTypes(item);
            if (mimeTypes.length > 0) {
                input.accept = mimeTypes.join(',');
            }
            const info = document.createElement('div');
            info.className = 'small text-muted mt-1 oe-questionnaire-attachment-info';
            if (existing?.title) {
                info.textContent = this.runtime.t('currentFile', existing.title);
            }
            input.addEventListener('change', () => {
                const file = input.files && input.files[0];
                if (!file) {
                    onAnswer(null);
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
                    onAnswer({ valueKey: 'valueAttachment', value: attachment });
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
            if (item.type === 'time') {
                return { valueKey: 'valueTime', value: toFhirTime(raw) };
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
            if (item.type === 'time') {
                return formatTimeInputValue(answer.value);
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
                        removeButton.textContent = this.runtime.t('remove');
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

        // A repeating boolean, quantity or attachment: one row per answer, each with the type's
        // own control, plus an empty row to add one (up to maxOccurs).
        renderRepeatingRows(item, container, storageKey, inputId) {
            const maxOccurs = getIntegerExtension(item, EXTENSIONS.MAX_OCCURS);
            const renderOne = { boolean: this.renderBoolean, quantity: this.renderQuantity, attachment: this.renderAttachment }[item.type];
            const rerender = () => {
                container.replaceChildren();
                const answers = this.runtime.responseStore.getAnswers(storageKey);
                const update = (index, answer) => {
                    const current = this.runtime.responseStore.getAnswers(storageKey);
                    if (index < current.length) {
                        if (answer) {
                            current[index] = answer;
                        } else {
                            current.splice(index, 1);
                        }
                    } else if (answer) {
                        current.push(answer);
                    }
                    this.runtime.setAnswers(storageKey, current);
                    rerender();
                };
                const rows = answers.map((answer, index) => [answer, index]);
                if (item.readOnly !== true && (maxOccurs === null || answers.length < maxOccurs)) {
                    rows.push([null, answers.length]);
                }
                for (const [answer, index] of rows) {
                    const row = document.createElement('div');
                    row.className = 'form-row oe-questionnaire-repeat-row mb-1';
                    const column = document.createElement('div');
                    column.className = 'col';
                    renderOne.call(this, item, column, answer ? [answer] : [], inputId + '-' + index, storageKey, (next) => update(index, next));
                    row.appendChild(column);
                    if (answer && item.readOnly !== true) {
                        const removeColumn = document.createElement('div');
                        removeColumn.className = 'col-auto';
                        const removeButton = document.createElement('button');
                        removeButton.type = 'button';
                        removeButton.className = 'btn btn-sm btn-link text-danger oe-questionnaire-repeat-remove py-0';
                        removeButton.textContent = this.runtime.t('remove');
                        removeButton.addEventListener('click', () => update(index, null));
                        removeColumn.appendChild(removeButton);
                        row.appendChild(removeColumn);
                    }
                    container.appendChild(row);
                }
                this.inputs.set(storageKey, container);
            };
            rerender();
            return container;
        }

        refresh() {
            for (const [storageKey, node] of this.nodes) {
                const linkId = storageKey.split(INSTANCE_SEPARATOR).pop();
                const compiledItem = this.runtime.compiled.itemsByLinkId.get(linkId);
                const hidden = compiledItem?.hidden === true;
                node.classList.toggle('d-none', !this.runtime.isEnabled(storageKey) || hidden);
            }
            this.applyDynamicState();
            this.refreshCalculatedValues();
            this.renderWarnings(this.runtime.collectWarnings());
            this.clearValidationState();
        }

        refreshCalculatedValues() {
            // Keyed by storage key so both canonical items (key === linkId) and
            // repeat-instance items (group\0index\0child) refresh from their own
            // stored value.
            const calculatedItems = new Map(this.runtime.compiled.calculatedItems.map((compiledItem) => [compiledItem.linkId, compiledItem.item]));
            for (const [storageKey, entry] of this.inputs) {
                const item = calculatedItems.get(storageKey.split(INSTANCE_SEPARATOR).pop());
                if (!item || !entry) {
                    continue;
                }
                const answers = this.runtime.responseStore.getAnswers(storageKey);
                if (entry instanceof HTMLInputElement) {
                    entry.value = firstValue(answers)?.value ?? '';
                } else {
                    this.showCalculatedAnswers(item, entry, answers);
                }
            }
        }

        // A calculated answer in a control made of several elements: a quantity's number and
        // unit, a boolean's radios, a choice's radios, check boxes, drop-down, autocomplete or
        // open text.
        showCalculatedAnswers(item, entry, answers) {
            const value = firstValue(answers)?.value;
            if (item.type === 'quantity') {
                const box = entry.querySelector('input[type="number"]');
                if (box) {
                    box.value = isQuantity(value) ? value.value : '';
                }
                // With no value, the unit stays as the person or the form left it (a lone unit is preselected).
                const unit = isQuantity(value) ? entry.querySelector('select.oe-questionnaire-unit-select, input[type="text"]') : null;
                const unitText = isQuantity(value) ? (value.unit ?? value.code ?? '') : '';
                if (unit instanceof HTMLSelectElement) {
                    // The option for the answer's unit, or none: another option's label would misstate it.
                    const match = [...unit.options].find((option) => option.value !== ''
                        && ((isQuantity(value) && value.code !== undefined && option.dataset.code === value.code) || option.textContent === unitText));
                    unit.value = match ? match.value : '';
                } else if (unit) {
                    unit.value = unitText;
                }
                return;
            }
            if (item.type === 'boolean') {
                const [yes, no] = entry.querySelectorAll('input[type="radio"]');
                if (yes && no) {
                    yes.checked = value === true;
                    no.checked = value === false;
                }
                return;
            }
            if (item.type !== 'choice' && item.type !== 'open-choice') {
                return;
            }
            const options = this.runtime.answerOptionsFor(item);
            const optionAnswers = options.map((option) => getAnswerOptionValue(option));
            const chosen = (optionAnswer) => optionAnswer !== null
                && answers.some((answer) => answer.valueKey === optionAnswer.valueKey && equalPrimitive(answer.value, optionAnswer.value));
            for (const input of entry.querySelectorAll('input.custom-control-input')) {
                input.checked = chosen(optionAnswers[Number(input.value)] ?? null);
            }
            const select = entry.querySelector('select.oe-questionnaire-choice-select');
            if (select) {
                const index = optionAnswers.findIndex(chosen);
                select.value = index >= 0 ? String(index) : '';
            }
            // An open choice's own text: a string answer that is not one of the options.
            const custom = answers.find((answer) => answer.valueKey === 'valueString'
                && !optionAnswers.some((optionAnswer) => optionAnswer?.valueKey === 'valueString' && equalPrimitive(optionAnswer.value, answer.value)));
            const autocomplete = entry.querySelector('input.oe-questionnaire-autocomplete');
            if (autocomplete) {
                const index = optionAnswers.findIndex(chosen);
                autocomplete.value = index >= 0 ? answerOptionLabel(options[index], optionAnswers[index]) : (item.type === 'open-choice' ? custom?.value ?? '' : '');
                return;
            }
            const openText = entry.querySelector('input[type="text"]');
            if (openText && item.type === 'open-choice') {
                openText.value = custom?.value ?? '';
            }
        }

        clearValidationState() {
            for (const node of this.nodes.values()) {
                node.classList.remove('oe-questionnaire-invalid');
            }
            for (const message of this.container?.querySelectorAll('.oe-questionnaire-error') ?? []) {
                message.remove();
            }
            for (const control of this.container?.querySelectorAll('[data-oeq-invalid]') ?? []) {
                control.removeAttribute('aria-invalid');
                control.removeAttribute('aria-describedby');
                control.removeAttribute('data-oeq-invalid');
            }
        }

        // Each issue's message is shown at its question and tied to the
        // question's control (aria-invalid, aria-describedby), so the error
        // is identified in text where it is, not by colour alone.
        showValidationIssues(issues) {
            this.clearValidationState();
            const messages = new Map();
            for (const issue of issues) {
                const key = issue.storageKey ?? issue.linkId;
                const node = this.nodes.get(key);
                if (!node) {
                    continue;
                }
                node.classList.add('oe-questionnaire-invalid');
                if (typeof issue.message !== 'string' || issue.message === '') {
                    continue;
                }
                if (!messages.has(key)) {
                    const message = document.createElement('div');
                    message.className = 'oe-questionnaire-error invalid-feedback d-block';
                    message.id = safeId(key) + '-error';
                    const control = node.querySelector(':scope > .oe-questionnaire-control');
                    if (node.tagName === 'TR') {
                        node.querySelector(':scope > th')?.appendChild(message);
                        for (const field of node.querySelectorAll('input')) {
                            field.setAttribute('aria-invalid', 'true');
                            field.setAttribute('aria-describedby', message.id);
                            field.setAttribute('data-oeq-invalid', '');
                        }
                    } else if (control) {
                        control.after(message);
                        const target = control.querySelector('#' + safeId(key)) ?? (control.getAttribute('role') ? control : null);
                        if (target) {
                            target.setAttribute('aria-invalid', 'true');
                            target.setAttribute('aria-describedby', message.id);
                            target.setAttribute('data-oeq-invalid', '');
                        }
                    } else {
                        node.appendChild(message);
                    }
                    messages.set(key, message);
                }
                const line = document.createElement('div');
                line.textContent = issue.message;
                messages.get(key).appendChild(line);
            }
        }
    }

    class QuestionnaireRuntime {
        constructor({ questionnaire, questionnaireResponse = null, options = {} }) {
            this.questionnaire = deepClone(questionnaire);
            if (options.subQuestionnaires && typeof options.subQuestionnaires === 'object') {
                this.questionnaire = assembleQuestionnaire(this.questionnaire, options.subQuestionnaires);
            }
            this.questionnaireResponse = questionnaireResponse ? deepClone(questionnaireResponse) : null;
            this.options = options;
            this.compiler = new QuestionnaireCompiler();
            this.compiled = this.compiler.compile(this.questionnaire);
            this.responseStore = new QuestionnaireResponseStore(this.questionnaire, this.questionnaireResponse);
            this.behaviorEngine = new QuestionnaireBehaviorEngine(this);
            this.initialExpressionsSeeded = false;
            this.validationEngine = new ValidationEngine(this);
            this.expressionEngine = new SdcExpressionEngine(this.compiled);
            for (const name of this.compiled.launchContextNames) {
                if (this.options.launchContext?.[name] === undefined) {
                    this.compiled.warnings.push({
                        code: 'launch-context-missing',
                        severity: 'info',
                        message: 'The form asks for %' + name + ' (launchContext), which the host did not supply; expressions using it come out empty.',
                    });
                }
            }
            this.renderer = new QuestionnaireRenderer(this);
            this.container = null;
            this.changeListeners = new Set();
            // Storage keys of calculated items someone answered over. SDC 4.0.0 (Using
            // Expressions, calculatedExpression): "If a user has edited a calculated value, it
            // SHOULD no longer be changed when other answers in the questionnaire response change."
            this.calculationOverrides = new Set();
            // Signatures by requirement key ('' for the form, else the item's linkId).
            this.signatures = new Map();
            const loadSignature = (key, holder) => {
                const signature = findExtension(holder, EXTENSIONS.RESPONSE_SIGNATURE)?.valueSignature;
                if (signature && typeof signature.data === 'string') {
                    this.signatures.set(key, { data: signature.data, sigFormat: signature.sigFormat ?? 'image/png', when: signature.when, loaded: signature });
                }
            };
            for (const requirement of this.compiled.signatureRequirements) {
                loadSignature(requirement.key, requirement.key === '' ? this.questionnaireResponse : findResponseItemByLinkId(this.questionnaireResponse, requirement.key));
            }
            this.refreshState();
        }

        // The response item for a linkId outside any repeat, created with its
        // parent groups when the response has none.
        ensureResponseItem(response, linkId) {
            const chain = [];
            for (let cursor = linkId; cursor; cursor = this.compiled.parentByLinkId.get(cursor)) {
                chain.unshift(cursor);
            }
            let level = response;
            for (const id of chain) {
                level.item = level.item ?? [];
                let node = level.item.find((entry) => entry?.linkId === id);
                if (!node) {
                    const text = this.compiled.itemsByLinkId.get(id)?.item?.text;
                    node = text ? { linkId: id, text } : { linkId: id };
                    level.item.push(node);
                }
                level = node;
            }
            return level;
        }

        setSignature(key, signature) {
            if (signature) {
                this.signatures.set(key, { ...signature, when: new Date().toISOString() });
            } else {
                this.signatures.delete(key);
            }
            this.renderer.clearValidationState();
            this.emitChange(null);
        }

        // questionnaireresponse-signature on the response or its item, for
        // each requirement signed. who is the host's options.signer when it
        // gives one, else the typed name.
        applySignatures(response) {
            for (const requirement of this.compiled.signatureRequirements) {
                const signature = this.signatures.get(requirement.key);
                let holder = requirement.key === '' ? response : findResponseItemByLinkId(response, requirement.key);
                if (!holder && signature && this.isEnabled(requirement.key)) {
                    // A signed item with no answer still needs its response item to carry the signature.
                    holder = this.ensureResponseItem(response, requirement.key);
                }
                if (!holder) {
                    continue;
                }
                holder.extension = (holder.extension ?? []).filter((extension) => extension?.url !== EXTENSIONS.RESPONSE_SIGNATURE);
                if (!signature) {
                    if (holder.extension.length === 0) {
                        delete holder.extension;
                    }
                    continue;
                }
                const valueSignature = signature.loaded ?? {
                    type: requirement.type.length > 0 ? requirement.type : [{ system: 'urn:iso-astm:E1762-95:2013', code: '1.2.840.10065.1.12.1.7', display: 'Consent Signature' }],
                    when: signature.when,
                    who: this.options.signer ?? { display: signature.name ?? '' },
                    sigFormat: signature.sigFormat,
                    data: signature.data,
                };
                holder.extension.push({ url: EXTENSIONS.RESPONSE_SIGNATURE, valueSignature });
            }
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

        // One of the runtime's own strings, in the host's language when it supplied one.
        t(key, ...args) {
            const fromOptions = this.options?.strings?.[key];
            const template = typeof fromOptions === 'string' ? fromOptions
                : (typeof SCRIPT_STRINGS[key] === 'string' ? SCRIPT_STRINGS[key] : DEFAULT_STRINGS[key] ?? key);
            return formatString(template, args);
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

        // The answer key for a value an expression gave (calculated or initial). A choice's value
        // is a Coding, or whatever type its options are (R4 answerOption value[x]); reference and
        // attachment answers keep their own keys.
        answerValueKeyForValue(item, value) {
            if (item.type === 'reference') {
                return 'valueReference';
            }
            if (item.type === 'attachment') {
                return 'valueAttachment';
            }
            if (item.type === 'choice' || item.type === 'open-choice') {
                if (value !== null && typeof value === 'object') {
                    return 'reference' in value ? 'valueReference' : 'valueCoding';
                }
                if (typeof value === 'number') {
                    return Number.isInteger(value) ? 'valueInteger' : 'valueDecimal';
                }
                if (typeof value === 'string' && FHIR_TIME_PATTERN.test(value)) {
                    return 'valueTime';
                }
                if (typeof value === 'string' && FHIR_DATE_PATTERN.test(value)) {
                    return 'valueDate';
                }
                return 'valueString';
            }
            return this.answerValueKeyForItem(item);
        }

        // Whether a person filling the form could answer over this calculated item: it is shown
        // and not read-only (as readOnlyExpression says, else as item.readOnly).
        isCalculationEditable(compiledItem) {
            const readOnly = this.dynamicState?.get(compiledItem.linkId)?.readOnly ?? compiledItem.item.readOnly === true;
            return !compiledItem.hidden && readOnly !== true;
        }

        setAnswers(linkId, answers) {
            // An answer given to a calculated item a person can edit stops its calculation;
            // clearing it resumes it.
            const compiledItem = this.compiled.itemsByLinkId.get(String(linkId).split(INSTANCE_SEPARATOR).pop());
            if (compiledItem?.calculatedExpression && this.isCalculationEditable(compiledItem)) {
                if (answers.length > 0) {
                    this.calculationOverrides.add(linkId);
                } else {
                    this.calculationOverrides.delete(linkId);
                }
            }
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
                this.expressionEngine.seedInitialExpressionInstances(this);
            }
            // Calculate until nothing changes: a form-level calculation can
            // read per-instance ones (evaluated after it), and a calculated
            // value can change what is enabled. Unchanged rewrites leave the
            // revisions alone, so a settled form takes one pass.
            for (let pass = 0; pass < 10; pass++) {
                const before = this.responseStore.revision + ':' + this.behaviorEngine.revision;
                this.expressionEngine.recalculate(this);
                this.behaviorEngine.evaluateAll();
                if (this.responseStore.revision + ':' + this.behaviorEngine.revision === before) {
                    break;
                }
            }
            this.dynamicState = this.expressionEngine.evaluateDynamic(this);
            this.behaviorEngine.evaluateAll();
        }

        // Required as the item's requiredExpression says (for this repetition, given its storage
        // key), else as item.required.
        isRequired(key, item) {
            const dynamic = (this.dynamicState?.get(key) ?? this.dynamicState?.get(item?.linkId))?.required;
            return dynamic === null || dynamic === undefined ? item?.required === true : dynamic;
        }

        // The item's answer options: answerExpression's (for this repetition, given its storage
        // key) when it has one, else its own.
        answerOptionsFor(item, key = item.linkId) {
            const dynamic = (this.dynamicState?.get(key) ?? this.dynamicState?.get(item.linkId))?.answerOptions;
            return Array.isArray(dynamic) ? dynamic : resolveAnswerOptions(item, this.questionnaire);
        }

        isEnabled(linkId) {
            return this.behaviorEngine.isEnabled(linkId);
        }

        validate() {
            const result = this.validationEngine.validate();
            this.renderer.showValidationIssues(result.issues);
            return result;
        }

        // { complete: true } is for a host saving a response that passed
        // validate(): R4's completed ("the current content is regarded as
        // definitive"), or amended when the loaded response was already
        // completed or amended. Without it the status stays as loaded, or
        // in-progress for a new response.
        getQuestionnaireResponse({ complete = false } = {}) {
            const response = this.responseStore.buildQuestionnaireResponse((linkId) => this.isEnabled(linkId));
            if (complete) {
                const loaded = this.questionnaireResponse?.status;
                response.status = loaded === 'completed' || loaded === 'amended' ? 'amended' : 'completed';
            }
            this.applyResponseMetadata(response);
            this.applySignatures(response);
            return response;
        }

        // LForms parity for the exported response: questionnaire canonical,
        // base profile (only when the response declares none) and a version
        // tag that replaces any earlier tag of ours. Other meta (versionId,
        // lastUpdated, security, other tags) carried by a loaded response is
        // preserved. Questionnaire.meta itself is not copied: its versionId,
        // lastUpdated and profiles describe the Questionnaire, not this response.
        applyResponseMetadata(response) {
            const questionnaire = this.questionnaire;
            if (typeof questionnaire.url === 'string' && questionnaire.url.trim() !== '') {
                const version = typeof questionnaire.version === 'string' ? questionnaire.version.trim() : '';
                response.questionnaire = canonicalWithVersion(questionnaire.url.trim(), version);
            }
            const meta = response.meta !== null && typeof response.meta === 'object' && !Array.isArray(response.meta) ? response.meta : {};
            if (!Array.isArray(meta.profile) || meta.profile.length === 0) {
                meta.profile = [QR_BASE_PROFILE];
            }
            const optionVersion = typeof this.options.openemrVersion === 'string' ? this.options.openemrVersion.trim() : '';
            const openemrVersion = optionVersion !== '' ? optionVersion : SCRIPT_OPENEMR_VERSION;
            if (openemrVersion !== '') {
                const tags = (Array.isArray(meta.tag) ? meta.tag : []).filter((tag) =>
                    !(tag?.system === VERSION_TAG_SYSTEM || (typeof tag?.code === 'string' && tag.code.startsWith(VERSION_TAG_PREFIX))));
                tags.push({ system: VERSION_TAG_SYSTEM, code: VERSION_TAG_PREFIX + openemrVersion });
                meta.tag = tags;
            }
            response.meta = meta;
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
        // Raised when the runtime gains what a host or a form may test for (0.3.0: the SDC and R4 additions of #14744;
        // 0.4.0: its post-freeze list, among them FHIRPath 2.0.0's empty and precision rules, %qitem and qrs-1 nesting).
        version: '0.4.0',
        EXTENSIONS,
        DEFAULT_STRINGS,
        QuestionnaireCompiler,
        QuestionnaireResponseStore,
        QuestionnaireBehaviorEngine,
        ValidationEngine,
        SdcExpressionEngine,
        FhirPathTokenizer,
        FhirPathParser,
        FhirPathEvaluator,
        QuestionnaireRuntime,
        renderMarkdown,
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
