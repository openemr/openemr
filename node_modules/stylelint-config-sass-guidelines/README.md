# stylelint-config-sass-guidelines

[![NPM version](http://img.shields.io/npm/v/stylelint-config-sass-guidelines.svg)](https://www.npmjs.org/package/stylelint-config-sass-guidelines)

[![Build Status](https://travis-ci.org/bjankord/stylelint-config-sass-guidelines.svg?branch=master)](https://travis-ci.org/bjankord/stylelint-config-sass-guidelines)
[![Downloads per month](https://img.shields.io/npm/dm/stylelint-config-sass-guidelines.svg)](http://npmcharts.com/compare/stylelint-config-sass-guidelines)

[![Dependency Status](https://david-dm.org/bjankord/stylelint-config-sass-guidelines.svg)](https://david-dm.org/bjankord/stylelint-config-sass-guidelines)
[![devDependency Status](https://david-dm.org/bjankord/stylelint-config-sass-guidelines/dev-status.svg)](https://david-dm.org/bjankord/stylelint-config-sass-guidelines/?type=dev)
[![Known Vulnerabilities](https://snyk.io/test/github/bjankord/stylelint-config-sass-guidelines/badge.svg)](https://snyk.io//test/github/bjankord/stylelint-config-sass-guidelines)

A stylelint config inspired by [sass-guidelin.es](https://sass-guidelin.es/).

This linter has been designed / tested with SCSS syntax based on the SCSS guidelines documented in https://sass-guidelin.es/. It is intended for use with SCSS syntax, not Sass (tab style) syntax.

# Translations

* [Deutsch](page/de.md)

## Installation

```console
$ npm i -D stylelint stylelint-config-sass-guidelines
```

## Usage

Set your stylelint config to:

```json
{
  "extends": "stylelint-config-sass-guidelines"
}
```

### Extending the config

Simply add a `"rules"` key to your config and add your overrides there.

For example, to change the `indentation` to tabs and turn off the `number-leading-zero` rule:


```json
{
  "extends": "stylelint-config-sass-guidelines",
  "rules": {
    "indentation": "tab",
    "number-leading-zero": null
  }
}
```

## [Lint Rule Comparison](https://github.com/bjankord/stylelint-config-sass-guidelines/wiki/Lint-Rule-Comparison)

## [Lint Report Comparison](https://github.com/bjankord/stylelint-config-sass-guidelines/wiki/Lint-Report-Comparison)

## Documentation

### Plugins

* [`stylelint-order`](https://github.com/hudochenkov/stylelint-order): A plugin pack of order related linting rules for stylelint.
* [`stylelint-scss`](https://github.com/kristerkari/stylelint-scss): A collection of SCSS specific linting rules for stylelint

### Configured lints

This is a list of the lints turned on in this configuration, and what they do.

#### At-rule

* [`at-rule-disallowed-list`](https://stylelint.io/user-guide/rules/at-rule-disallowed-list): Specify a list of disallowed at-rules.
  * `"debug"` Disallow the use of `@debug`.
* [`at-rule-no-vendor-prefix`](http://stylelint.io/user-guide/rules/at-rule-no-vendor-prefix/): Disallow vendor prefixes for at-rules.

#### Block

* [`block-no-empty`](http://stylelint.io/user-guide/rules/block-no-empty/): Disallow empty blocks.
* [`block-opening-brace-space-before`](http://stylelint.io/user-guide/rules/block-opening-brace-space-before/): There must always be a single space before the opening brace.

#### Color

* [`color-hex-case`](http://stylelint.io/user-guide/rules/color-hex-case/): Hex colors must be written in lowercase.
* [`color-hex-length`](http://stylelint.io/user-guide/rules/color-hex-length/): Always use short hex notation, where available.
* [`color-named`](http://stylelint.io/user-guide/rules/color-named/): Colors must never be named.
* [`color-no-invalid-hex`](http://stylelint.io/user-guide/rules/color-no-invalid-hex/): Hex values must be valid.

#### Declaration

* [`declaration-bang-space-after`](http://stylelint.io/user-guide/rules/declaration-bang-space-after/): There must never be whitespace after the bang.
* [`declaration-bang-space-before`](http://stylelint.io/user-guide/rules/declaration-bang-space-before/): There must always be a single space before the bang.
* [`declaration-colon-space-after`](http://stylelint.io/user-guide/rules/declaration-colon-space-after/): There must always be a single space after the colon if the declaration's value is single-line.
* [`declaration-colon-space-before`](http://stylelint.io/user-guide/rules/declaration-colon-space-before/): There must never be whitespace before the colon.

#### Declaration block

* [`declaration-block-properties-order`](http://stylelint.io/user-guide/rules/declaration-block-properties-order/): Properties in declaration blocks must be sorted alphabetically.
* [`declaration-block-semicolon-newline-after`](http://stylelint.io/user-guide/rules/declaration-block-semicolon-newline-after/): There must always be a newline after the semicolon.
* [`declaration-block-semicolon-space-before`](http://stylelint.io/user-guide/rules/declaration-block-semicolon-space-before/): There must never be whitespace before the semicolons.
* [`declaration-block-single-line-max-declarations`](http://stylelint.io/user-guide/rules/declaration-block-single-line-max-declarations/): There should never be more than `1` declaration per line.
* [`declaration-block-trailing-semicolon`](http://stylelint.io/user-guide/rules/declaration-block-trailing-semicolon/): There must always be a trailing semicolon.

#### Declaration Property

* [`declaration-property-value-disallowed-list`](http://stylelint.io/user-guide/rules/declaration-property-value-disallowed-list): Specify a list of disallowed property and value pairs within declarations.
  * `^border`: Disallow the use of the word `none` for borders, use `0` instead. The intent of this rule is to enforce consistency, rather than define which is "better."

#### Function

* [`function-comma-space-after`](http://stylelint.io/user-guide/rules/function-comma-space-after/): There must always be a single space after the commas in single-line functions.
* [`function-parentheses-space-inside`](http://stylelint.io/user-guide/rules/function-parentheses-space-inside/): There must never be whitespace on the inside of the parentheses of functions.
* [`function-url-quotes`](http://stylelint.io/user-guide/rules/function-url-quotes/): URLs must always be quoted.

#### General

* [`indentation`](http://stylelint.io/user-guide/rules/indentation/): Indentation should always be `2` spaces.
* [`length-zero-no-unit`](http://stylelint.io/user-guide/rules/length-zero-no-unit/): Disallow units for zero lengths.
* [`max-nesting-depth`](http://stylelint.io/user-guide/rules/max-nesting-depth/): Limit the allowed nesting depth to `1`. _Ignore_: Nested at-rules `@media`, `@supports`, and `@include`.
* [`no-missing-eof-newline`](http://stylelint.io/user-guide/rules/no-missing-eof-newline/): Disallow missing end-of-file newlines in non-empty files.

#### Media Feature

* [`media-feature-name-no-vendor-prefix`](http://stylelint.io/user-guide/rules/media-feature-name-no-vendor-prefix/): Disallow vendor prefixes for media feature names.

#### Number

* [`number-leading-zero`](http://stylelint.io/user-guide/rules/number-leading-zero/): There must always be a leading zero.
* [`number-no-trailing-zeros`](http://stylelint.io/user-guide/rules/number-no-trailing-zeros/): Disallow trailing zeros in numbers.

#### Property

* [`property-no-vendor-prefix`](http://stylelint.io/user-guide/rules/property-no-vendor-prefix/): Disallow vendor prefixes for properties.
* [`shorthand-property-no-redundant-values`](http://stylelint.io/user-guide/rules/shorthand-property-no-redundant-values/): Disallow redundant values in shorthand properties.


#### Rule

* [`rule-nested-empty-line-before`](http://stylelint.io/user-guide/rules/rule-nested-empty-line-before/): There must always be an empty line before multi-line rules. _Except_: Nested rules that are the first of their parent rule. _Ignore_: Rules that come after a comment.
* [`rule-non-nested-empty-line-before`](http://stylelint.io/user-guide/rules/rule-non-nested-empty-line-before/): There must always be an empty line before multi-line rules. _Ignore_: Rules that come after a comment.

#### SCSS
* [`at-extend-no-missing-placeholder`](https://github.com/kristerkari/stylelint-scss/blob/master/src/rules/at-extend-no-missing-placeholder/README.md): Disallow at-extends (`@extend`) with missing placeholders.
* [`at-function-pattern`](https://github.com/kristerkari/stylelint-scss/blob/master/src/rules/at-function-pattern/README.md): SCSS functions must be written in lowercase and match the regex `^[a-z]+([a-z0-9-]+[a-z0-9]+)?$`.
* [`at-import-no-partial-leading-underscore`](https://github.com/kristerkari/stylelint-scss/blob/master/src/rules/at-import-no-partial-leading-underscore/README.md): Disallow leading underscore in partial names in `@import`.
* [`at-import-partial-extension-blacklist`](https://github.com/kristerkari/stylelint-scss/blob/master/src/rules/at-import-partial-extension-blacklist/README.md): Specify list of disallowed file extensions for partial names in `@import` commands.
  * `.scss`: Disallow the use of the `.scss` file extension in imports.
* [`at-mixin-pattern`](https://github.com/kristerkari/stylelint-scss/blob/master/src/rules/at-mixin-pattern/README.md): SCSS mixins must be written in lowercase and match the regex `^[a-z]+([a-z0-9-]+[a-z0-9]+)?$`.
* [`dollar-variable-colon-space-after`](https://github.com/kristerkari/stylelint-scss/blob/master/src/rules/dollar-variable-colon-space-after/README.md): Require a single space after the colon in $-variable declarations.
* [`dollar-variable-colon-space-before`](https://github.com/kristerkari/stylelint-scss/blob/master/src/rules/dollar-variable-colon-space-before/README.md): Disallow whitespace before the colon in $-variable declarations.
* [`dollar-variable-pattern`](https://github.com/kristerkari/stylelint-scss/blob/master/src/rules/dollar-variable-pattern/README.md): SCSS variables must be written in lowercase and match the regex `^[a-z]+([a-z0-9-]+[a-z0-9]+)?$`.
* [`percent-placeholder-pattern`](https://github.com/kristerkari/stylelint-scss/blob/master/src/rules/percent-placeholder-pattern/README.md): SCSS `%`-placeholders must be written in lowercase and match the regex `^[a-z]+([a-z0-9-]+[a-z0-9]+)?$`.
* [`selector-no-redundant-nesting-selector`](https://github.com/kristerkari/stylelint-scss/blob/master/src/rules/selector-no-redundant-nesting-selector/README.md): Disallow redundant nesting selectors (`&`).

#### Selector

* [`selector-class-pattern`](http://stylelint.io/user-guide/rules/selector-class-pattern/): Selectors must be written in lowercase and match the regex `^(?:u|is|has)-[a-z][a-zA-Z0-9]*$|^(?!u|is|has)[a-zA-Z][a-zA-Z0-9]*(?:-[a-z][a-zA-Z0-9]*)?(?:--[a-z][a-zA-Z0-9]*)?$`.
* [`selector-list-comma-newline-after`](http://stylelint.io/user-guide/rules/selector-list-comma-newline-after/): There must always be a newline after the commas of selector lists.
* [`selector-max-compound-selectors`](http://stylelint.io/user-guide/rules/selector-max-compound-selectors/): Limit the number of compound selectors in a selector to `3`.
* [`selector-no-id`](http://stylelint.io/user-guide/rules/selector-no-id/): Disallow id selectors.
* [`selector-no-qualifying-type`](http://stylelint.io/user-guide/rules/selector-no-qualifying-type/): Disallow qualifying a selector by type.
* [`selector-no-vendor-prefix`](http://stylelint.io/user-guide/rules/selector-no-vendor-prefix/): Disallow vendor prefixes for selectors.
* [`selector-pseudo-element-colon-notation`](http://stylelint.io/user-guide/rules/selector-pseudo-element-colon-notation/): Applicable pseudo-elements must always use the double colon notation.
* [`selector-pseudo-element-no-unknown`](http://stylelint.io/user-guide/rules/selector-pseudo-element-no-unknown/): Disallow unknown pseudo-element selectors.

#### String

* [`string-quotes`](http://stylelint.io/user-guide/rules/string-quotes/): Strings must always be wrapped with single quotes.

#### Stylelint Disable Comment

* [`stylelint-disable-reason`](http://stylelint.io/user-guide/rules/stylelint-disable-reason/): Require a reason comment before stylelint-disable comments.

#### Value

* [`value-no-vendor-prefix`](http://stylelint.io/user-guide/rules/value-no-vendor-prefix/): Disallow vendor prefixes for values.

## [Changelog](CHANGELOG.md)

## [License](LICENSE)
