# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [8.0.0]
### Added
- Set node engine minimum to version 10.0.0
- Added node 14 to automated test matrix

### Removed
- Dropped official support for Node 8
- Removed node 8 from automated test matrix

## [7.1.0]
### Changed
- Add dependabot integration to help with keeping dependencies up to date and secure
- Bumped up `stylelint` peer/dev dependency to v13.7.0

### Fixed
- Replaced deprecated `at-rule-blacklist` rule with `at-rule-disallowed-list` rule
- Replaced deprecated `declaration-property-value-blacklist` rule with `declaration-property-value-disallowed-list` rule

### Removed
- Removed unused scss-lint files, these were only used to generate lint errors for comparison with stylelint and did not play a functional role in how this stylelint config worked
  - Removed unused Gemfile
  - Removed unused Gemfile.lock
  - Removed src/.scss-lint.yml

## [7.0.0]
### Changed
- Updated stylelint peerDependency range from `^10.0.1 || ^11.0.0 || ^12.0.0` to `^13.0.0`

**Node.js v10 or newer** is required. That's because stylelint v13 itself [doesn't support Node.js versions below 10](https://github.com/stylelint/stylelint/blob/master/CHANGELOG.md#1300).

- Bumped up `stylelint-order` dependency to `^4.0.0`
- Update test expectations to not require specific error message order

## [7.0.0]
### Changed
- Updated stylelint peerDependency range from `^10.0.1 || ^11.0.0 || ^12.0.0` to `^13.0.0`

**Node.js v10 or newer** is required. That's because stylelint v13 itself [doesn't support Node.js versions below 10](https://github.com/stylelint/stylelint/blob/master/CHANGELOG.md#1300).

- Bumped up `stylelint-order` dependency to `^4.0.0`
- Update test expectations to not require specific error message order

## [6.2.0]
### Changed
- Updated stylelint peerDependency range from `^10.0.1 || ^11.0.0` to `^10.0.1 || ^11.0.0 || ^12.0.0` to include stylelint 12

## [6.1.0]
### Changed
- Updated stylelint peerDependency range from `^10.0.1` to `^10.0.1 || ^11.0.0`

## [6.0.0]
### Changed
- Bumped up `stylelint` peer/dev dependency to `^10.0.1`
- Bumped up `stylelint-order` dependency to `^3.0.0`
- Node.js 8.7.0 or greater is now required

## [5.4.0]
### Fixed
- Fix patterns for variables like "$x1". [PR](https://github.com/bjankord/stylelint-config-sass-guidelines/pull/43)

### Changed
- Ignore all @-rules in max-nesting-depth. [PR](https://github.com/bjankord/stylelint-config-sass-guidelines/pull/45)

## [5.3.0]
### Changed
- Updated `stylelint-order` dependency range to pull in 1.x or 2.x versions. Both major versions are compatible. [PR](https://github.com/bjankord/stylelint-config-sass-guidelines/pull/41)
- Updated up `stylelint-scss` dependency to pull minimum of 3.4.0 [PR](https://github.com/bjankord/stylelint-config-sass-guidelines/pull/41)

## [5.2.0]
### Changed
- Bumped up `stylelint-order` dependency to ^1.0.0 [PR](https://github.com/bjankord/stylelint-config-sass-guidelines/pull/32)

## [5.1.0]
### Added
- Added ability to run tests on Windows [PR](https://github.com/bjankord/stylelint-config-sass-guidelines/pull/34)

### Changed
- Ignore @each for max-nesting-depth [PR](https://github.com/bjankord/stylelint-config-sass-guidelines/pull/35)

## [5.0.0]
### Added
- Added scss/at-rule-no-unknown rule [#18](https://github.com/bjankord/stylelint-config-sass-guidelines/issues/18)

### Changed
- Bumped up `stylelint` peer/dev dependency to v9.0.0
- Added stylelint-scss and stylelint-order as dependencies [#22](https://github.com/bjankord/stylelint-config-sass-guidelines/issues/22)
- Node.js 6.x or greater is now required [#24](https://github.com/bjankord/stylelint-config-sass-guidelines/issues/24))

## [4.2.0]
### Added
- Add "ignore" options to "max-nesting-depth" rule (fixes [#25](https://github.com/bjankord/stylelint-config-sass-guidelines/issues/25)) [PR](https://github.com/bjankord/stylelint-config-sass-guidelines/pull/26)

## [4.1.0]
### Changed
- Bumped up `stylelint-order` to v0.8.0

### Fixed
- Fixed border zero rule. Issue [16](https://github.com/bjankord/stylelint-config-sass-guidelines/issues/16)

## [4.0.1]
### Removed
- Removed `{"type": "at-rule", "hasBlock": true }` from order rule. Causes issues with `@media` queries and `@for` loops
- Removed `{"type": "rule", "selector": "/^&:\\w/"},` from order rule.
- Removed `{"type": "rule", "selector": "/^&::\\w/"},` from order rule.

## [4.0.0]
### Added
- Add rules & tests for declaration-order [PR](https://github.com/bjankord/stylelint-config-sass-guidelines/pull/15)

### Changed
- Moved stylelint, stylelint-scss, stylelint-order to peerDependencies / devDependencies [PR](https://github.com/bjankord/stylelint-config-sass-guidelines/pull/17/files)

## [3.0.1]
### Changed
- Update copyright years in license

## [3.0.0]
### Added
- Added [greenkeeper](https://greenkeeper.io/) to help keep dependencies up to date

### Changed
- Bumped up `stylelint` to v8.0.0
- Bumped up `stylelint-order` to v0.6.0

### Removed
- Removed unused `stylelint-selector-no-utility` dependency from package.json

## [3.0.0-rc.1]
### Added
- Added [greenkeeper](https://greenkeeper.io/) to help keep dependencies up to date

### Changed
- Bumped up `stylelint` to v8.0.0
- Bumped up `stylelint-order` to v0.6.0

### Removed
- Removed unused `stylelint-selector-no-utility` dependency from package.json

## [2.2.0]
### Changed
- Bumped up `stylelint` to v7.12.0

### Fixed
- Replaced deprecated `selector-no-id` rule with `selector-max-id` rule

## [2.1.0]
### Changed
- Bumped up `stylelint-order` to v0.4.3

### Fixed
- Replaced deprecated `order/declaration-block-properties-alphabetical-order` rule with `order/properties-alphabetical-order` rule

## [2.0.0]
### Added
- Added `stylelint-order` plugin

### Changed
- Bumped up `stylelint` to v7.8.0
- Bumped up `stylelint-scss` to v1.4.1

### Fixed
- Replaced deprecated `declaration-block-properties-order` rule with `order/declaration-block-properties-alphabetical-order` rule
- Replaced deprecated `rule-nested-empty-line-before` rule with `rule-empty-line-before` rule
- Replaced deprecated `rule-non-nested-empty-line-before` rule with `rule-empty-line-before` rule

### Removed
- `stylelint-disable-reason` rule. This rule has been deprecated in stylelint 7.8 and in 8.0 will be removed. See stylelint CHANGELOG: https://stylelint.io/CHANGELOG/#780

## [1.1.1]
### Fixed
- Regex for selector-class-pattern now matches lowercase with hyphens correctly
- Updated test for url-quotes.js to match updated error text

## [1.1.0]
### Added
- `scss/dollar-variable-colon-space-after` rule
- `scss/dollar-variable-colon-space-before` rule

### Changed
- Bumped up `stylelint` to v7.1.0
- Bumped up `stylelint-scss` to v1.3.4

## [1.0.0]
### Added
- `stylelint-disable-reason` rule
- `property-no-unknown` rule
- `media-feature-parentheses-space-inside` rule
- `no-missing-end-of-source-newline` rule

### Changed
- Bumped up `stylelint` to v7.0.2
- Bumped up `stylelint-scss` to v1.2.1

### Removed
- `no-missing-eof-newline `rule
- `function-calc-no-unspaced-operator` rule

## [0.2.0]
### Added
- `function-parentheses-space-inside` rule
- `scss/at-import-partial-extension-blacklist` rule
- `declaration-block-properties-order` rule
- `selector-no-vendor-prefix` rule
- `media-feature-name-no-vendor-prefix` rule
- `at-rule-no-vendor-prefix` rule

### Fixed
- Sorted stylelint rules alphabetically in config
- `max-nesting-depth` rule set to 1 to match Sass Guidelines NestingDepth max_depth: 1 rule
- Cleaned up comments in `failing-test-case.scss`
- Declaration order now sorted alphabetically in `passing-test-case.scss`
- Updated tests to account for new rules

### Removed
- `block-closing-brace-newline-after` rule
- `no-extra-semicolons` rule
- `string-no-newline` rule

## [0.1.0]
### Added
- Initial release
