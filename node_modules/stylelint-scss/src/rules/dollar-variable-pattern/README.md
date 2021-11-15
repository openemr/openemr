# dollar-variable-pattern

Specify a pattern for Sass-like variables.

```scss
a { $foo: 1px; }
/** ↑
 * The pattern of this */
```

## Options

`regex` or `string`

A string will be translated into a RegExp like so `new RegExp(yourString)` — so be sure to escape properly.

### Examples


The following patterns are considered warnings:

```scss
/* stylelint scss/dollar-variable-pattern: /foo-.+/ */
a { $boo-bar: 0; }
a { $fooBar: 0; }

/* stylelint scss/dollar-variable-pattern: /[a-z][a-zA-Z]+/ */
a { $foo-bar: 0; }
a { $FooBar: 0; }
a { $fooBar-baz: 0; }
```

The following patterns are *not* considered warnings:

```scss
/* stylelint scss/dollar-variable-pattern: /foo-.+/ */
a { $foo-bar: 0; }
a { $foo-bar-baz: 0; }
a { $foo-barBaz: 0; }
a { $boo-foo-bar: 0; }

/* stylelint scss/dollar-variable-pattern: /[a-z][a-zA-Z]+/ */
a { $fooBar: 0; }
a { $fooBarBaz: 0; }
```

## Optional Options

### `ignore: "local"|"global"`

#### `"local"`

Makes this rule ignore local variables (variables defined inside a rule/mixin/function, etc.).

The following patterns are *not* considered warnings:

```scss
/* stylelint scss/dollar-variable-pattern: [/^foo-/, {"ignore": "local"}] */
$foo-name00: 10px;
a { $bar-name01: 10px; }
```

#### `"global"`

Makes this rule ignore global variables (variables defined in the stylesheet root).

The following patterns are *not* considered warnings:

```scss
/* stylelint scss/dollar-variable-pattern: [/^foo-/, {"ignore": "global"}] */
$bar-name01: 10px;
a { $foo-name02: 10px; }
```
