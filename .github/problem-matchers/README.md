# GitHub Actions Problem Matchers

This directory contains problem matcher configurations for GitHub Actions workflows. Problem matchers automatically parse tool output and create annotations that appear inline with code in pull requests.

## What are Problem Matchers?

Problem matchers are JSON configurations that use regular expressions to parse output from tools (linters, compilers, test frameworks) and convert them into GitHub annotations. These annotations:
- Appear inline with the relevant code in pull requests
- Show up in the "Files changed" tab
- Are visible in the workflow run logs
- Make it easier to identify and fix issues

## Available Matchers

### php-syntax.json
Parses PHP syntax errors from `php -l` command.

**Workflow:** `syntax.yml`

**Example output:**
```
PHP Parse error:  syntax error, unexpected identifier "This" in tests/test.php on line 12
```

**Annotation result:** Error annotation on `tests/test.php` at line 12

### composer-validate.json
Parses warnings from `composer validate` command.

**Workflow:** `composer.yml`

**Example output:**
```
- require.package/name : exact version constraints (1.0.0) should be avoided
```

**Annotation result:** Warning annotation on `composer.json`

### composer-require-checker.json
Parses errors from composer-require-checker tool.

**Workflow:** `composer-require-checker.yml`

**Example output:**
```
  Package\ClassName
```

**Annotation result:** Error annotation on `composer.json`

## Usage in Workflows

To use a problem matcher in a workflow:

```yaml
- name: Run Check
  run: |
    echo "::add-matcher::.github/problem-matchers/php-syntax.json"
    # Run your tool here
    php -l file.php
    echo "::remove-matcher owner=php-syntax::"
```

The matcher is activated with `::add-matcher::` and deactivated with `::remove-matcher owner=<owner>::` where `<owner>` is defined in the JSON file.

## Format

Each problem matcher JSON file has the following structure:

```json
{
  "problemMatcher": [
    {
      "owner": "unique-identifier",
      "severity": "error",
      "pattern": [
        {
          "regexp": "^(.+):(\\d+):(\\d+): (.+)$",
          "file": 1,
          "line": 2,
          "column": 3,
          "message": 4
        }
      ]
    }
  ]
}
```

## References

- [GitHub Actions Problem Matchers Documentation](https://github.com/actions/toolkit/blob/master/docs/problem-matchers.md)
- [Problem Matcher Examples](https://github.com/actions/toolkit/blob/master/docs/commands.md#problem-matchers)
