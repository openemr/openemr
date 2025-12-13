# GitHub Actions Test Files

This directory contains test files specifically designed to test GitHub Actions workflows and their problem matchers.

## Files

### test-syntax-error.php
A PHP file with an intentional syntax error to test the PHP syntax checker workflow (`syntax.yml`) and its problem matcher.

**Error:** Missing closing quote in string literal

**To fix:** Uncomment the fix line in the file or delete the file to pass the syntax check.

## Purpose

These files are used to verify that:
1. GitHub Actions workflows properly detect issues
2. Problem matchers correctly parse tool output
3. Annotations appear inline with problematic code
4. Workflow summaries display useful information

## Usage

To test the workflows:
1. Push changes including these test files
2. Observe the GitHub Actions run
3. Verify that annotations appear on the problematic lines
4. Check that workflow summaries are informative
5. Fix or remove the test files to make the checks pass
