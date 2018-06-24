# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.7.0 - 2018-05-01

### Added

- [#23](https://github.com/zendframework/zend-permissions-acl/pull/23) adds a new assertion, `ExpressionAssertion`, to allow programatically or
  automatically (from configuration) building standard comparison assertions
  using a variety of operators, including `=` (`==`), `!=`, `<`, `<=`, `>`,
  `>=`, `===`, `!==`, `in` (`in_array`), `!in` (`! in_array`), `regex`
  (`preg_match`), and `!regex` (`! preg_match`). See https://docs.zendframework.com/zend-permissions-acl/expression/
  for details on usage.

- [#3](https://github.com/zendframework/zend-permissions-acl/pull/3) adds two new interfaces designed to allow creation of ownership-based assertions
  easier:

  - `Zend\Permissions\Acl\ProprietaryInterface` is applicable to both roles and
    resources, and provides the method `getOwnerId()` for retrieving the owner
    role of an object.

  - `Zend\Permissions\Acl\Assertion\OwnershipAssertion` ensures that the owner
    of a proprietary resource matches that of the role.

  See https://docs.zendframework.com/zend-permissions-acl/ownership/ for details
  on usage.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 2.6.1 - 2018-05-01

### Added

- [#35](https://github.com/zendframework/zend-permissions-acl/pull/35) adds support for PHP 7.2.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#29](https://github.com/zendframework/zend-permissions-acl/pull/29) provides a change to `Acl::removeResourceAll()` that increases performance by a factor of 100.

## 2.6.0 - 2016-02-03

### Added

- [#15](https://github.com/zendframework/zend-permissions-acl/pull/15) adds
  completed documentation, and publishes it to
  https://zendframework.github.io/zend-permissions-acl/

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#7](https://github.com/zendframework/zend-permissions-acl/pull/7) and
  [#14](https://github.com/zendframework/zend-permissions-acl/pull/14) update the
  component to be forwards-compatible with zend-servicemanager v3.
