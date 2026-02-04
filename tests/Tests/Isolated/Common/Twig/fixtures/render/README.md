# Twig Render Test Fixtures

Expected HTML output for `TwigTemplateRenderTest`. Each `.html` file is the
full rendered output of a Twig template with known parameters. The test renders
the template and asserts the output matches the fixture.

## Adding a fixture

1. Add a new test case to `renderCaseProvider()` in `TwigTemplateRenderTest.php`
   with the template name, parameters, and fixture path.
2. Generate the fixture file:
   ```bash
   composer update-twig-fixtures
   ```
3. Review the generated file with `git diff`, then commit it.

## Updating fixtures

When a template changes intentionally, regenerate and review:

```bash
composer update-twig-fixtures
git diff tests/Tests/Isolated/Common/Twig/fixtures/render/
```

## Test environment

These tests run without a database or application kernel. Two things to know:

- **Translation is disabled.** `xlt()`, `xla()`, etc. return the original
  English string with escaping applied. Fixtures show untranslated text.
- **`setupHeader()` is stubbed.** The real function requires the kernel and
  event dispatcher. The stub returns `<!-- setupHeader stub -->`, which appears
  in autologin fixtures wherever the real output would be.

## Trailing whitespace

Twig's block processing can leave trailing whitespace on otherwise-empty lines
(e.g., indentation before `{{ parent() }}` followed by the parent block's
leading newline). The test normalizes trailing whitespace before writing
fixtures and before comparing, so the `.html` files stay clean for pre-commit
hooks and the assertions still match.
