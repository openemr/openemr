# Final classes

The `final` keyword was removed in version 1.4.0. It was replaced by `@final` annotation.
This was done due popular demand, not because it is a good technical reason to
extend the classes.

This document will show the correct way to work with PSR-7 classes. The "correct way"
refers to best practices and good software design. I strongly believe that one should
be aware of how a problem *should* be solved, however, it is not needed to always
implement that solution.

## Extending classes

You should never extend the classes, you should rather use composition or implement
the interface yourself. Please refer to the [decorator pattern](https://refactoring.guru/design-patterns/decorator).

## Mocking classes

The PSR-7 classes are all value objects and they can be used without mocking. If
one really needs to create a special scenario, one can mock the interface instead.
