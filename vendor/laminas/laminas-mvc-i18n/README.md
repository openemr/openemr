# laminas-mvc-i18n

[![Build Status](https://github.com/laminas/laminas-mvc-i18n/workflows/Continuous%20Integration/badge.svg)](https://github.com/laminas/laminas-mvc-i18n/actions?query=workflow%3A"Continuous+Integration")

laminas-mvc-i18n provides integration between:

- laminas-i18n
- laminas-mvc
- laminas-router

and replaces the i18n functionality found in the v2 releases of the latter
two components.

- File issues at https://github.com/laminas/laminas-mvc-i18n/issues
- Documentation is at https://docs.laminas.dev/laminas-mvc-i18n/

## Installation

```console
$ composer require laminas/laminas-mvc-i18n
```

Assuming you are using the [component installer](https://docs.laminas.dev/laminas-component-installer/),
doing so will enable the component in your application, allowing you to
immediately start developing console applications via your MVC. If you are not,
please read the [introduction](https://docs.laminas.dev/laminas-mvc-i18n/intro/)
for details on how to register the functionality with your application.

## For use with laminas-mvc v3 and up

While this component has an initial stable release, please do not use it with
laminas-mvc releases prior to v3, as it is not compatible.

## Migrating from laminas-mvc v2 i18n features to laminas-mvc-i18n

Please see the [migration guide](https://docs.laminas.dev/laminas-mvc-i18n/migration/v2-to-v3/)
for details on how to migrate your existing laminas-mvc console functionality to
the features exposed by this component.
