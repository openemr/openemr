# Native Questionnaire Runtime: what it supports

OpenEMR renders FHIR R4 Questionnaires with its own runtime, `interface/forms/questionnaire_assessments/native/openemr_questionnaire.js` (version 0.4.0). This page lists what it does with each part of a Questionnaire, for people writing Questionnaires and for triaging issues. "Partly" says what's missing. The runtime follows FHIR R4 and SDC 4.0.0; where it is more lenient than the spec, the notes say so.

The list was read from the runtime's own code, including the lists it keeps of the item controls it handles and the extensions it reports as unsupported, and checked against its Jest tests (`tests/js/openemr-questionnaire-*.test.js`).

## Questionnaire (the resource)

| Element | Supported | Notes |
|---|---|---|
| `url`, `version` | Yes | Saved as the response's `questionnaire`, `url\|version` |
| `title`, `name` | Yes | Form heading (`name` when there's no title); also labels the form's signature |
| `description` | Yes | Shown under the heading (markdown), unless the host sets `showDescription: false` |
| `copyright` | Yes | A copyright button on the heading, unless the host sets `showCopyright: false` |
| `language` | Yes | Sets `lang` on the form |
| `contained` | Partly | A ValueSet is used for `answerValueSet` and `unitValueSet` when it has an `expansion`, or a `compose` that lists its concepts (excluded concepts are left out). A compose with a filter, a whole code system or another value set needs a terminology service, so it gives no options and a warning. Other contained resources are ignored |
| `item` | Yes | Any depth (next table) |
| `extension` | See the extensions table | |
| `modifierExtension` | Yes | One the runtime doesn't understand, anywhere in the form, stops the render with a message naming it, as R4 requires ("Applications processing a resource are required to check for modifier extensions"). Those inside contained resources are left to whatever uses them |
| `identifier`, `derivedFrom`, `status`, `experimental`, `subjectType`, `date`, `publisher`, `contact`, `useContext`, `jurisdiction`, `purpose`, `approvalDate`, `lastReviewDate`, `effectivePeriod`, `code` | Not read | Metadata: no effect on filling. Which forms are offered (by `status`, for example) is the host's choice |

## Questionnaire.item

| Element | Supported | Notes |
|---|---|---|
| `linkId` | Yes | An item without one is skipped with a warning; duplicates are warned |
| `definition`, `code` | Not read | Kept on the definition, not used by the runtime |
| `prefix` | Yes | Shown before the text, on display items too |
| `text` | Yes | Plus `rendering-markdown` on `_text`, and a text expression (`cqf-expression` on `_text`) |
| `type` | Yes | All 16 R4 types (next table) |
| `enableWhen` | Yes | `question`; operators `exists`, `=`, `!=`, `>`, `<`, `>=`, `<=`; answers boolean, decimal, integer, date, dateTime, time, string, Coding, Quantity and Reference. A Quantity compares by value in the same unit; in different units it is neither equal nor ordered (no unit conversion) |
| `enableBehavior` | Yes | `all` and `any`. Several conditions without it are treated as `any`, with a console note (R4 requires it then) |
| `required` | Yes | Validation, `aria-required`, the asterisk; also dynamic through `cqf-expression` on `_required` |
| `repeats` | Yes | Groups add and remove repetitions; choice and open-choice become check boxes; every other type gets a list of rows, one answer each |
| `readOnly` | Yes | Also dynamic through `cqf-expression` on `_readOnly` |
| `maxLength` | Yes | Set on the input and validated |
| `answerValueSet` | Partly | A contained ValueSet (see `contained` above), with its codings' extensions (`ordinalValue` among them). A canonical to anything else gives a warning; there's no terminology `$expand` |
| `answerOption` | Yes | `value[x]` (Coding, string, integer, date, time, Reference) and `initialSelected` |
| `initial` | Yes | `value[x]`, seeded per repetition inside repeating groups |
| `item` | Yes | Nested items, under groups and under questions |

## Item types

| Type | Supported | Rendered as |
|---|---|---|
| `group` | Yes | A section; repeating groups add and remove repetitions; `table` and `gtable` controls (below) |
| `display` | Yes | Text, styled by `displayCategory` (instructions, security) |
| `boolean` | Yes | Yes / No radios |
| `decimal`, `integer` | Yes | Number input, or slider |
| `date`, `dateTime`, `time` | Yes | Native date and time inputs |
| `string`, `text`, `url` | Yes | Text input or text area |
| `choice`, `open-choice` | Yes | Radios, check boxes, drop-down or autocomplete; open choice adds free text (also through `answerConstraint`) |
| `attachment` | Yes | File input, checked against `mimeType` and `maxSize` |
| `reference` | Yes | Text input for a reference, checked against `referenceResource` |
| `quantity` | Yes | Number plus unit (`unitOption`, `unitValueSet`, or a lone `questionnaire-unit`, preselected) |

## Item controls (R4 `questionnaire-item-control`)

| Control | Supported | Notes |
|---|---|---|
| `drop-down`, `radio-button`, `check-box`, `autocomplete`, `spinner` (shown as a drop-down), `slider`, `text-box` | Yes | |
| `list`, `table`, `gtable`, `header`, `footer` | Yes | `table` is the answer grid (one row per question); `gtable` is a grid of a repeating group |
| `help`, `legal`, `flyover` | Yes | Buttons with popups |
| `inline`, `prompt`, `unit`, `lower`, `upper` | Yes | |
| `htable`, `atable`, `page`, `tab-container`, `lookup` | No | Shown the default way, with a console note (`item-control-unsupported`) |

## Extensions

| Extension | Supported | Notes |
|---|---|---|
| `questionnaire-itemControl` | Yes | Previous table |
| `questionnaire-hidden` | Yes | Not shown and not validated, still in the response |
| `questionnaire-choiceOrientation` | Yes | Horizontal or vertical choices |
| `sdc-questionnaire-openLabel` | Yes | Label for the open-choice text |
| `questionnaire-displayCategory` | Yes | Styles display items |
| `questionnaire-optionPrefix`, `questionnaire-optionExclusive` | Yes | "1." before an option; an exclusive option clears the others |
| `ordinalValue`, `itemWeight` | Yes | Carried into the answer's Coding; read by `weight()` (and `ordinal()`) |
| `questionnaire-unit`, `questionnaire-unitOption`, `questionnaire-unitValueSet`, `sdc-questionnaire-unitOpen` | Yes | A lone `questionnaire-unit` is the question's unit, preselected and saved. R4 defines `questionnaire-unit` for integer and decimal questions; reading it on a quantity question is a leniency for forms that put it there |
| `minValue`, `maxValue`, `maxDecimalPlaces`, `minLength`, `regex`, `entryFormat` | Yes | Validation; `entryFormat` as the placeholder |
| `sdc-questionnaire-minQuantity`, `sdc-questionnaire-maxQuantity` | Yes | In the same unit (no unit conversion) |
| `questionnaire-minOccurs`, `questionnaire-maxOccurs` | Yes | Answer counts, and repetitions of a repeating group |
| `mimeType`, `maxSize` | Yes | Attachment checks |
| `questionnaire-referenceResource` | Yes | Placeholder and type check |
| `questionnaire-answerConstraint` | Yes | `optionsOnly`, `optionsOrString`, `optionsOrType` (the R5 backport) |
| `questionnaire-constraint`, `targetConstraint` | Yes | FHIRPath checks with `human` messages, by severity |
| `questionnaire-usageMode` | Yes | `display` and `display-non-empty` keep an item out of the form being filled |
| `questionnaire-supportHyperlink` | Yes | Links beside the question |
| `questionnaire-sliderStepValue` | Yes | |
| `questionnaire-signatureRequired`, `questionnaireresponse-signature` | Yes | Form-level and item-level; a drawn pad or a typed name; saved as R4 Signature; signed responses load back. `who` comes from the host's `options.signer`, else the typed name |
| `variable` | Yes | On the root and on items, in scope for descendants |
| `sdc-questionnaire-calculatedExpression` | Yes | Recalculated as answers change; a value typed over an editable calculated item is kept (SDC 4.0.0), and clearing it hands it back |
| `sdc-questionnaire-initialExpression` | Yes | Once, when the form opens; per repetition |
| `sdc-questionnaire-enableWhenExpression` | Yes | Combined with `enableWhen`. An empty result disables the item (SDC: it "will in turn evaluate to false") |
| `sdc-questionnaire-answerExpression`, `sdc-questionnaire-answerOptionsToggleExpression` | Yes | Per repetition inside repeating groups |
| `cqf-expression` | Partly | On `required`, `readOnly` and `text`, per repetition. On other elements (`repeats`, occurrence counts, bounds) it's reported as unsupported |
| `sdc-questionnaire-launchContext` | Partly | Names `%patient`, `%encounter`, `%user` and others from the host's `options.launchContext`; a missing one gives a console note. OpenEMR's own pages don't pass them yet |
| `sdc-questionnaire-subQuestionnaire` | Partly | Assembled from the host's `options.subQuestionnaires`; one not supplied leaves a warning in its place. OpenEMR's pages don't supply them yet |
| `sdc-rendering-criticalExtension` | Yes | A critical extension the runtime doesn't support stops the render with a message |
| `rendering-markdown` | Yes | GitHub-flavoured markdown (a safe subset: no raw HTML, links limited to http(s), mailto and tel) on item text and answer options. `description` and `copyright`, which are FHIR markdown, render the same way |
| `rendering-xhtml` | By design, no | Not trusted as markup; the plain `text` is shown |
| `sdc-questionnaire-itemMedia`, `sdc-questionnaire-itemAnswerMedia` | Partly | Images are shown on questions and options; other media (audio, video) appear as a link opening in a new tab |
| `sdc-questionnaire-collapsible`, `sdc-questionnaire-columnCount`, `sdc-questionnaire-width`, `sdc-questionnaire-shortText`, `sdc-questionnaire-keyboard` | Yes | `width` and `shortText` on table columns; `keyboard` sets the input mode |
| `sdc-questionnaire-candidateExpression`, `sdc-questionnaire-lookupQuestionnaire`, `questionnaire-referenceProfile` | No | Reported as unsupported (console note) |
| `sdc-questionnaire-choiceColumn`, `sdc-questionnaire-entryMode`, `rendering-style` | No | Reported as unsupported; no effect on the form |
| `sdc-questionnaire-observationExtract` and other extraction extensions | Not read | Extraction is server-side, and not built yet |
| Any other extension | Not read | No note, no effect (a modifier extension stops the form; see above) |

## FHIRPath

The runtime has its own FHIRPath evaluator, following FHIRPath 2.0.0 (the version FHIR R4 uses).

- **Environment:** `%resource`, `%rootResource`, `%context` (inside a repeating group, that repetition's own group), `%questionnaire`, `%qitem` (the Questionnaire item the expression is on; for a variable, the item that defines it), the launch context names, and named variables. SDC's `%factory` and `%terminologies` aren't provided.
- **Operators:** `=`, `!=`, `~`, `!~`, `<`, `>`, `<=`, `>=`, `+`, `-`, `*`, `/`, `div`, `mod`, `&`, `|`, `and`, `or`, `xor`, `implies`, `in`, `contains`, `is`, `as`. Indexers too. Date, dateTime, time and quantity literals; calendar duration arithmetic (`today() - 18 years`).
- **Functions:**
  - existence and filtering: `empty`, `exists`, `all`, `allTrue`, `anyTrue`, `allFalse`, `anyFalse`, `subsetOf`, `supersetOf`, `count`, `distinct`, `isDistinct`, `where`, `select`, `repeat`, `ofType`;
  - subsetting: `single`, `first`, `last`, `tail`, `skip`, `take`, `intersect`, `exclude`;
  - combining: `union`, `combine`;
  - conversion: `iif` (with or without its otherwise-result), `toBoolean`, `toInteger`, `toDecimal`, `toString`, `convertsToBoolean`, `convertsToInteger`, `convertsToDecimal`, `convertsToString`;
  - strings: `indexOf`, `substring`, `startsWith`, `endsWith`, `contains`, `upper`, `lower`, `replace`, `matches`, `replaceMatches`, `length`, `toChars`, `trim`, `split`, `join`;
  - math: `abs`, `ceiling`, `exp`, `floor`, `ln`, `log`, `power`, `round`, `sqrt`, `truncate`;
  - tree: `children`, `descendants`;
  - utility: `trace`, `now`, `today`, `timeOfDay`, `not`, `hasValue`;
  - aggregates (Trial Use in FHIRPath 2.0.0): `aggregate`, `sum`, `min`, `max` (numbers, strings, dates and times), `avg`;
  - also `sort`, `repeatAll`, FHIR's `extension(url)`, and SDC's `weight()` (with `ordinal()`).
- **Semantics:**
  - Empty is unknown. `and`, `or`, `xor` and `implies` follow FHIRPath's three-valued logic, and `=` or `!=` with an empty operand gives empty. Collections compare item by item, in order.
  - Dates, dateTimes and times compare precision by precision, and give empty when one value's precision runs out first (`@2026 = @2026-01`). DateTimes with a time of day compare in UTC when either has an offset; one without an offset is read as local time.
  - Quantities compare and add in the same unit only (no unit conversion). A calendar keyword (`4 days`) is unequal to a UCUM duration above seconds (`4 'd'`), and the two aren't ordered.
  - Date arithmetic with a duration finer than the value's precision converts and truncates it (`@2026 + 1 day` is `@2026`).
- **Lenient where the spec is strict:** a quoted calendar word counts as its UCUM unit (`104 'weeks'` matches an answer in `wk`); type mismatches give empty or a best effort rather than an error (`'a' & 1` is `'a1'`); numbers have no separate decimal representation (`1.0.toString()` is `'1'`, and `1 is Decimal` is true).
- **Not supported:** `memberOf` (needs a terminology service), `resolve`, `conformsTo`, `htmlChecks`; the date, time and quantity conversions (`toDate`, `toDateTime`, `toTime`, `toQuantity` and their `convertsTo…`); `encode`, `decode`, `escape`, `unescape`; `lowBoundary`, `highBoundary`, `precision`, `comparable`; `defineVariable`; time-of-day arithmetic (`@T10:30:00 + 1 hour` gives empty). Each unsupported function fails with "Unsupported FHIRPath method", shown as a warning. Only `text/fhirpath` expressions are evaluated; CQL is not.
- **Per repetition:** inside repeating groups, every expression (`enableWhen`, `enableWhenExpression`, `calculatedExpression`, `initialExpression`, `answerExpression`, `answerOptionsToggleExpression`, and the `required`, `readOnly` and `text` expressions) is evaluated for each repetition, with `%context` that repetition.

## The QuestionnaireResponse it builds

- **`status`:** `in-progress` while being filled; `completed` (or `amended` for one loaded as completed) when the host saves with `getQuestionnaireResponse({ complete: true })` after `validate()` passes.
- **`questionnaire`:** `url|version`, set from the form.
- **`item`:** answers by `linkId`, in the form's order. Disabled items are left out.
- **Nesting:** a group's children go under its `item`. A question's children go under its answer (R4's qrs-1: "Nested item can't be beneath both item and answer"); for a repeating question, under its first answer, since children aren't kept per answer. While the question has no answer, its answered children stay under its `item`.
- **Answers:** keep their Coding's extensions (`ordinalValue`), quantity units (UCUM code and display), attachments and references.
- **Signatures:** `questionnaireresponse-signature` on the response or the item.
- **Host-owned:** `subject`, `author`, `encounter` and `source` are set by the host, not the runtime. In OpenEMR, the save services set what they set today.
- **Loading older responses:** children saved beside an answer load and are saved under it. An answer LHC-Forms saved (OpenEMR 8.2 and earlier) to a choice with string options, `valueCoding { display: "Yes" }`, is read as the string option it names.

## Notes for form authors

- **Finding a question's children:** use `descendants()` or `repeat(item | answer.item)`. `repeat(item)` stops at an answered question, whose children sit under its answer.
- **Conditions on unanswered questions:** an `enableWhenExpression` that comes out empty disables its item, and comparisons with an unanswered question come out empty. So `%resource.item.where(linkId = 'x').answer.value != 'no'` keeps its item hidden until `x` is answered. To show it before then, write `%resource.item.where(linkId = 'x').answer.empty() or %resource.item.where(linkId = 'x').answer.value != 'no'`.
- **Units:** answers and limits compare only in the same unit. Prefer UCUM codes (`'wk'`, `'kg'`) on both sides.

## Known limits

- No terminology service: no `$expand` of an external ValueSet, no `memberOf`.
- No unit conversion (`70 'kg'` and `70000 'g'` are neither equal nor ordered).
- Extraction (`observationExtract`, definition-based or template-based) isn't built.
- OpenEMR's own pages pass no launch context, sub-questionnaires or signer yet; a host that does gets them.
- The item controls and extensions marked "No" above.
- A repeating question's children are kept once, not per answer.

## How the list was checked

Beyond the Jest tests, the runtime was checked on 2026-10-24 against:
- 320 published Questionnaires: 52 from LOINC, OpenEMR's 18 contrib SDC templates, 146 from WHO SMART guidelines, and 104 others. Each was filled the same way in two builds, and every difference in what was shown, saved and validated was read.
- fhirpath.js 5.2.1 (HL7's JavaScript FHIRPath, with its R4 model), on 242 edge expressions and every expression in 98 of those forms. Each difference was judged against the FHIRPath 2.0.0 text, since fhirpath.js has deviations of its own (`skip(-1)`, `take(-1)`, `{}.sum()`).
- LHC-Forms 33.0.0, the renderer of OpenEMR 7.0.2 to 8.2, given the runtime's own responses to the same forms.
- The HL7 FHIR validator (R4), on responses the runtime saved for OpenEMR's contrib templates and for forms whose questions have children: no errors. The same nested question saved by the 0.3.0 runtime fails qrs-1.
