# C-CDA / QRDA Schematron Validation

Pure-PHP schematron validation for C-CDA and QRDA documents. Replaces the
`oe-schematron-service` / `oe-cda-schematron` Node.js sidecar that OpenEMR
previously started on port 6662 and called over HTTP.

Entry point for callers is `OpenEMR\Services\Cda\CdaValidateDocuments`; nothing
outside that class should need to construct a validator directly.

## Why it changed

The Node service was a second runtime to install, start, supervise and keep
patched, spawned per-request via `exec()` with a socket poll to see whether it
had come up. Validation is pure XPath over a DOM — PHP already has both. Removing
the sidecar removes an installation step, a failure mode (the service not
starting, leaving validation silently disabled), and a supply-chain surface.

Cold-path cost dropped roughly 7.5x with the sidecar gone (0.74 s vs 5.83 s).
A 296 KB C-CDA validates in under 200 ms in process.

## What a caller gets

```php
$validator = new CdaValidateDocuments();
$result = $validator->validateDocument($xml, 'ccda');   // 'ccda' | 'qrda1' | 'qrda3'
```

The returned array keeps the shape the Node service produced, so existing
renderers need no change:

| key | meaning |
|---|---|
| `errorCount` / `warningCount` / `ignoredCount` | counts for the three buckets |
| `errors` / `warnings` / `ignored` | the findings themselves |
| `validationFailed` | `true` when validation could not run at all |
| `xsd` | XSD schema findings, added by `validateXmlXsd()` |

Each finding carries `type`, `test`, `simplifiedTest`, `description`,
`patternId`, `ruleId`, `assertionId`, `context`, `line`, `path` and a truncated
`xml` snippet of the offending node.

### `validationFailed`

If validation throws — unreadable schema, malformed input, out of memory — the
result is **not** an empty error list. `errorCount` is 1, `validationFailed` is
`true`, and the single finding says the document was not checked. An empty error
list is indistinguishable from a conformant document, and a certification tool
must never report a failure as a pass.

### `ignored`

An assertion the evaluator could not evaluate at all. **This bucket is empty for
all three shipped schematrons.** A non-zero `ignoredCount` is a defect to
diagnose, not a number to accept.

## Architecture

| Class | Responsibility |
|---|---|
| `SchematronParser` | `.sch` → `ParsedSchematron` / `ParsedRule` / `ParsedAssertion` / `ParsedExtension` |
| `SchematronValidator` | evaluates the parse tree against a document via `DOMXPath` |
| `SchemaRegistry` | maps `ccda`/`qrda1`/`qrda3` to its `.sch` + `vocab.php`, builds a configured validator |
| `DocumentPredicateRewriter` | rewrites `document('voc.xml')` value-set predicates inline |
| `XPathVariableExpander` | resolves `<sch:let>` variables to literals |
| `VocabularyLookup` / `ArrayVocabularyLookup` | OID → allowed values |
| `VocabularyExtractor` | build-time: `.sch` + `voc.xml` → committed `vocab.php` |
| `ValidationResult` | the three buckets plus `toArray()` |

Shipped schemas:

| type | file | patterns | rules | assertions | error / warning patterns |
|---|---|---|---|---|---|
| `ccda` | `Consolidation.sch` (977 KB) | 433 | 1007 | 2481 | 218 / 215 |
| `qrda1` | `2022_CMS_QRDA_I.sch` (574 KB) | 303 | 671 | 1574 | 256 / 47 |
| `qrda3` | `2022_CMS_QRDA_Category_III.sch` (171 KB) | 75 | 181 | 423 | 68 / 7 |

The `.sch` files are HL7 IG deliverables, shipped verbatim. `.pre-commit-config.yaml`
excludes them from whitespace and large-file hooks for that reason.

## Two deliberate improvements over the Node engine

Both convert assertions the old engine silently skipped into real pass/fail
results, so the PHP validator reports findings the Node one never did. That is
the improvement, not a regression.

**1. Value-set predicates.** The JS XPath library could not evaluate
`document('voc.xml')/voc:systems/voc:system[@valueSetOid='…']/voc:code/@value`
and quietly marked those assertions ignored. `DocumentPredicateRewriter` expands
each one into an inline disjunction using a committed vocabulary table, so they
now evaluate. All 25 OIDs referenced across the three schematrons resolve.

**2. `<sch:let>` variables.** `DOMXPath` has no variable-binding API, so `$name`
failed evaluation outright. 40 assertions reference one — including QRDA I's NPI
checksum and UTC-offset rules (`r-validate_NPI_format-errors`,
`r-validate_TZ-errors`) and 35 QRDA III assertions. None of them ran. Enabling
this added four genuine `CONF: CMS_0121` timezone findings to the QRDA I fixture.

`XPathVariableExpander` substitutes the variable's **evaluated value**, not its
defining expression, per ISO/IEC 19757-3: a rule-scoped `let` is "calculated and
scoped to the current rule and context". Substituting the expression breaks as
soon as the variable appears inside a predicate, where it is re-evaluated against
each candidate node — C-CDA's `hasCompatibleR1.1TemplateId` compares
`@root=$root`, which inlines to the tautology `@root=@root` and passes for a
sibling carrying a different root.

**Scope decides the context node.** ISO/IEC 19757-3 5.4.5 draws a line the
evaluator has to respect: a `let` declared as a child of a rule is calculated
against that rule's context node, while one declared on the schema or a pattern
is calculated against the *instance document root*. `ParsedRule` therefore keeps
`variables` (rule-scoped) and `documentVariables` (outer-scoped) apart, and
`expand()` takes both maps with both context nodes, pinning each definition to
the context its own scope dictates. Merging the two would re-evaluate an outer
definition against every rule node, changing the answer for any
context-dependent expression. A rule-scoped name shadows an outer one.

The three shipped schematrons declare only three distinct outer-scope names —
`timeZoneExists`, `intendedRecipient-Doc` and `intendedRecipient-Measure-CMS` —
and every one is written with absolute paths, so the golden fixtures are
identical under either model. The scope handling is correctness for future IG
revisions, and `SchematronValidatorTest` is the only thing guarding it; do not
drop those tests as redundant.

Value conversion is type-aware and deliberately conservative:

| value | literal | why |
|---|---|---|
| boolean | `true()` / `false()` | — |
| number | plain digits | XPath 1.0 has no exponent notation |
| NaN | `number('NaN')` | no NaN literal; NPI checksum relies on NaN comparing false |
| string | quoted literal | — |
| empty node-set | `(/..)` | **not** `''` — `$empty != 'x'` is false for a node-set, true for a string, which would invert every `$intendedRecipient-Doc != 'PCF'` branch in QRDA III |
| 2+ node node-set | throws | no faithful XPath 1.0 form; reported ignored rather than answered wrongly |

## Behavior parity notes

Ported deliberately from `oe-cda-schematron`; do not "fix" these:

- A rule context not starting with `/` is prefixed with `//`.
- `<sch:extends rule="X"/>` evaluates X's assertions against the *current*
  rule's context, recursively. Cycles are guarded; a reference to an undefined
  rule throws rather than silently skipping the inherited assertions.
- Level comes from `<sch:phase id="errors|warnings">`, overridden to `error` when
  the assertion text contains `SHALL` and either lacks `SHOULD` or has `SHALL`
  first.
- **Warnings are off by default.** Only SHALL-level findings are reported unless a
  caller passes `includeWarnings: true` to `SchemaRegistry::loadValidator()` or the
  `SchematronValidator` constructor. This is a deliberate change from the Node
  sidecar, which reported SHOULD-level findings because `oe-cda-schematron`'s
  `validate()` read an absent option as `true`. On the C-CDA sample, turning them
  on adds 201 warnings. The flag filters per finding, not per pattern, so a SHALL
  assertion inside a warnings-phase pattern still reports as an error either way:
  the error count never depends on it.
- `simplifiedTest` records the `document('voc.xml')` rewrite only. It is `null`
  for an assertion using `<sch:let>`, because the expansion differs per context
  node.

## Regenerating the vocabulary tables

`schemas/<type>/vocab.php` is generated, committed and shipped, so runtime never
reads `voc.xml`. Rebuild it when the upstream IG revision changes:

```bash
php bin/console openemr:regen-schematron-vocab /path/to/oe-schematron-service --skip-globals
```

The source checkout must contain `schematron/<type>/{*.sch,voc.xml}` for all three
types. The run is all-or-nothing across all three: the command preflights every
input, extracts and stages all three `.sch`/`vocab.php` pairs to temp files, and
only then swaps any of them into place. A failure during the swap phase rolls
every already-swapped file back from a snapshot taken before the first rename,
and names any path it could not restore so a half-applied set is never reported
as a clean one.

That matters because committing each pair as it was extracted left the shipped
set mixed-revision whenever a later target failed — ccda regenerated against the
new IG while qrda1 and qrda3 still held the old one, validating documents against
two revisions at once. Review the diff before committing.

## Gotchas

- **Truncate snippets with `mb_strcut`, never `substr`.** The finding list is
  `json_encode`d into `documents.document_data`. A snippet cut mid-character is
  invalid UTF-8, `json_encode` returns `false`, the column stores empty, and the
  report renders as "No Errors" — a validation failure disguised as a pass.
- The external MDHT/ETT conformance server still takes precedence when
  `mdht_conformance_server_enable` is set; the PHP validator is the local path.
- `libxml` error state is global. Every entry point saves and restores it, and
  clears the buffer *before* evaluating, so a stale entry cannot make a
  legitimately failing assertion look unevaluable.

## See also

- `TESTING.md` in this directory — how to run and extend the test suite
- `tests/Tests/Isolated/Cda/Schematron/` — unit tests and golden fixtures
- `tests/Tests/Services/Cda/CdaValidateDocumentsTest.php` — consumer-side snapshot
