# Testing the schematron validator

Everything except one consumer-side snapshot is database-free and lives in the
isolated suite, so the normal loop is fast.

## Running

```bash
# whole isolated suite (in container)
openemr-cmd phpunit-isolated                       # alias: pit

# just this area - `e` does not start in the app root, so cd first
openemr-cmd e 'cd /var/www/localhost/htdocs/openemr && \
  vendor/bin/phpunit -c phpunit-isolated.xml --filter "Schematron|XPathVariable"'

# the one DB-backed test (CdaValidateDocumentsTest)
openemr-cmd services-test                          # alias: st

# on the host, if you keep a full PHP/Composer toolchain
composer phpunit-isolated
```

There is no bare `openemr-cmd phpunit` subcommand.

## What is covered

| File | Tests | Covers |
|---|---:|---|
| `Isolated/Cda/Schematron/SchematronParserTest.php` | 15 | `.sch` → parse tree: namespaces, phase levels, `SHALL`/`SHOULD` classification, rule- vs document-scoped `<sch:let>` separation, mixed-content assertions, id-less rules, document order |
| `Isolated/Cda/Schematron/SchematronValidatorTest.php` | 15 | evaluation end to end, golden parity, rule-context variables, document-root scoping and shadowing, uncompilable rule context, undefined `extends`, snippet truncation, warnings off by default and opt-in |
| `Isolated/Cda/Schematron/XPathVariableExpanderTest.php` | 15 | `<sch:let>` resolution: every value type, chained definitions, hyphenated names, and each throw path |
| `Isolated/Cda/Schematron/DocumentPredicateRewriterTest.php` | 11 | `document('voc.xml')` predicate rewriting and XPath literal quoting |
| `Isolated/Cda/Schematron/SchemaRegistryTest.php` | 5 | type → file resolution, validator construction, warnings off unless requested |
| `Isolated/Cda/Schematron/ArrayVocabularyLookupTest.php` | 4 | OID lookup |
| `Isolated/Cda/Schematron/VocabularyExtractorTest.php` | 2 | `.sch` + `voc.xml` → `vocab.php` |
| `Isolated/Common/Command/RegenSchematronVocabCommandTest.php` | 7 | regen preflight, atomic writes, `.sch` restore on failure, cross-target rollback when a later target fails |
| `Services/Cda/CdaValidateDocumentsTest.php` | 1 | **DB-backed** consumer snapshot of the full result shape |

## Golden fixtures

In `tests/Tests/Isolated/Cda/Schematron/fixtures/`:

```
<name>.xml                  input document
<name>.node-golden.json     what the Node validator produced
<name>.php-golden.json      what this validator produces
```

`node-golden` is the **parity** check: every error the Node service reported must
still appear, compared by occurrence count rather than distinct value — Node
reporting one assertion on four nodes where PHP reports it on one has to fail.

`php-golden` is a **change-detection snapshot**. It is not an oracle. If it moves,
find out why before regenerating it.

| fixture | errors | warnings | ignored | parity pair |
|---|---:|---:|---:|---|
| `ccda-example-response1` | 6 | 0 | 0 | yes |
| `qrda1-catI-doc-28` | 39 | 0 | 0 | yes |
| `qrda3-minimal` | 3 | 0 | 0 | yes |
| `qrda3-cms-variables` | 14 | 0 | 0 | no |

The goldens are captured with the default settings, so warnings are off and the
`warnings` bucket is empty. With `includeWarnings: true` the same fixtures report
201, 45, 0 and 3 warnings, and the error counts do not change.
`SchemaRegistryTest` checks that the opt-in path still reaches the warnings-phase
patterns on the real C-CDA sample.

`node-golden` also records `warningCount: 0`, because those captures ran with
warnings explicitly disabled. The Node service itself reported warnings in
production. The parity check compares errors only.

### `qrda3-cms-variables` is not a parity fixture

`qrda3-minimal.xml` is 2.5 KB and reaches exactly one of the 35 QRDA III
assertions that reference a `<sch:let>` variable, which cannot distinguish a
working expander from one that is never asked. `qrda3-cms-variables.xml` drives
them deliberately: `$intendedRecipient-Doc` via a `MIPS_GROUP` recipient,
`$timeZoneExists` via a document `effectiveTime` with an offset alongside a
serviceEvent time without one, `$NPI-Count`/`$TIN-Count` via an assignedEntity
carrying both, and the `$s` → `$n` → `$sum` NPI checksum through four `cda:id`
nodes.

The checksum cases are the point:

| NPI | fails | why |
|---|---|---|
| `123456789` | `a-CMS_0115`, `a-CMS_0117` | nine digits |
| `12345678AB` | `a-CMS_0116`, `a-CMS_0117` | not numeric |
| `1234567890` | `a-CMS_0117` | correct length, wrong check digit |
| `1234567893` | **nothing** | valid |

That last row is what separates "the chain computed" from "the chain ran and
rejected everything". **Preserve it when editing the fixture.**

## Regenerating a golden

Only after you understand why the output moved. There is no maintenance command
— run the validator and write the JSON:

```php
$reg = new OpenEMR\Services\Cda\Schematron\SchemaRegistry();
$out = $reg->loadValidator('ccda')->validate(
    file_get_contents("$fixtures/ccda-example-response1.xml"),
    file_get_contents($reg->schematronPath('ccda'))
)->toArray();
file_put_contents(
    "$fixtures/ccda-example-response1.php-golden.json",
    json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
);
```

Never edit a golden by hand to make a test pass.

## Invariants worth asserting against

A change that breaks one of these is a regression even if the suite is green:

1. **`ignoredCount` is 0** for all four fixtures. An assertion the evaluator
   cannot evaluate is a bug in the evaluator, not an acceptable outcome.
2. **Error counts are stable** at 6 / 39 / 3 / 14. Errors moving without a
   deliberate reason means evaluation semantics changed.
3. **Warnings are off by default and the error count never depends on them.**
   A non-zero `warningCount` in a default-settings golden means the default got
   flipped. An error count that changes with `includeWarnings` means the filter
   stopped working per finding and started dropping whole warnings-phase patterns,
   which loses SHALL assertions.
4. **A valid NPI passes.** See the table above.
5. **Snippets are valid UTF-8.** `json_encode` of the finding list must not
   return `false`.

## Writing new tests

- Prefer the isolated suite. Only reach for `services-test` when the test needs
  `CdaValidateDocuments` wiring or the database.
- Assert on `assertionId` and `ruleId`, not on positional array indexes or
  `path` strings — those shift when evaluation order changes and produce
  failures that say nothing.
- Annotate data providers with the repo's standard comment; PHPUnit runs them
  before coverage instrumentation, so they otherwise show as uncovered:
  ```php
  /**
   * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
   */
  ```
- Reduced repros beat fixtures. Several tests here build a four-line `.sch`
  inline that mirrors a shipped rule; that reads far better in a failure than a
  pointer into a 977 KB file.

## Diagnosing a failure

**A golden moved.** Diff the JSON and look at `assertionId` first. A changed
`simplifiedTest` with unchanged counts is usually a rewriting change; changed
counts are an evaluation change.

**`ignoredCount` went up.** Something threw during evaluation. The bucket
deliberately carries one generic message so nothing leaks into the UI, so
reproduce it directly: call `XPathVariableExpander::expand()` or
`DocumentPredicateRewriter::rewrite()` on the reported `test` and read the real
exception.

**Parity check failed.** PHP dropped an error Node caught. Suspect the parser
first (a rule or assertion not collected) before the evaluator.

**A test passes that shouldn't.** Mutate the source and confirm the test fails —
several fixes in this area were originally guarded by tests that could not have
caught the bug. Reverting `mb_strcut` to `substr` must fail
`testTruncatedSnippetStaysValidUtf8`; inlining the expression instead of the
value in `XPathVariableExpander` must fail
`testRuleScopedVariableIsEvaluatedAgainstTheRuleContext`.

## Static analysis

PHPStan runs at level 10 over the whole codebase and takes around 15 minutes, so
it is worth reading your own diff for these first:

- `.phpstan/baseline/method.deprecated.php` pins `CdaValidateDocuments` at
  **three** `getSystemLogger()` calls. Adding or removing one breaks the count.
  New code should use `ServiceContainer::getLogger()`.
- Do not add baseline entries.
- `phpstan-phpunit` narrows types through `assertSame`, so a defensive `??` on a
  literal array offset in a test gets flagged as dead code.

```bash
openemr-cmd phpstan            # alias: pst
openemr-cmd prek run phpstan   # same config, via the pre-commit hook
```
