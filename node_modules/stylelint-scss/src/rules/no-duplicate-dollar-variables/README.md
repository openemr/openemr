# no-duplicate-dollar-variables

Disallow duplicate dollar variables within a stylesheet.

```scss
$a: 1;
$a: 2;
/** ↑
 * These are duplicates */
```

A dollar variable is considered a duplicate if it shadows a variable of the same name (see the [Sass documentation](https://sass-lang.com/documentation/variables#shadowing)). Two dollar variables are not duplicates if their scopes are unrelated.

```scss
.one {
  $a: 1;
  /** ↑
   * Not a duplicate */
}
.two {
  $a: 2;
  /** ↑
   * Not a duplicate */
}
```

A dollar variable is **not** considered a duplicate if it contains the `!default` keyword (see the [Sass documentation](https://sass-lang.com/documentation/variables#default-values)). Two dollar variables are duplicates if they both contain `!default` keyword.

```scss
$a: 1;
$a: 5 !default;
/** ↑
   * Not a duplicate */

$b: 1 !default;
$b: 5 !default;
/** ↑
   * These are duplicates  */
```



## Options

### `true`

The following patterns are considered violations:

```scss
$a: 1;
$a: 2;
```

```scss
$a: 1;
$b: 2;
$a: 3;
```

```scss
$a: 1;
.b {
  $a: 1;
}
```

```scss
$a: 1;
.b {
  .c {
    $a: 1;
  }
}
```

```scss
$a: 1;
@mixin b {
  $a: 1;
}
```

The following patterns are _not_ considered violations:

```scss
$a: 1;
$b: 2;
```

```scss
$a: 1;
.b {
  $b: 2;
}
```

___

### `ignoreInside: ["at-rule", "nested-at-rule"]`

#### `"at-rule"`

Ignores dollar variables that are inside both nested and non-nested at-rules (`@media`, `@mixin`, etc.).

Given:

```json
{ "ignoreInside": ["at-rule"] }
```

The following patterns are _not_ considered warnings:

```scss
$a: 1;
@mixin c {
  $a: 1;
}
```

```scss
$a: 1;
.b {
  @mixin c {
    $a: 1;
  }
}
```

#### `"nested-at-rule"`

Ignores dollar variables that are inside nested at-rules (`@media`, `@mixin`, etc.).

Given:

```json
{ "ignoreInside": ["nested-at-rule"] }
```

The following patterns are _not_ considered warnings:

```scss
$a: 1;
.b {
  @mixin c {
    $a: 1;
  }
}
```

___

### `ignoreInsideAtRules: ["array", "of", "at-rules"]`

Ignores all variables that are inside specified at-rules.

Given:

```json
{ "ignoreInsideAtRules": ["if", "mixin"] }
```

The following patterns are _not_ considered warnings:

```scss
$a: 1;

@mixin b {
  $a: 2;
}
```

```scss
$a: 1;

@if (true) {
  $a: 2;
}
```

___

### `ignoreDefaults: [boolean]`

Ignore all variables containing the `!default` keyword.

Given:

```json
{ "ignoreDefaults": true }
```

The following patterns are _not_ considered warnings:

```scss
$a: 5 !default;
$a: $a + 1;

$a: 15 !default;
```

Given:

```json
{ "ignoreDefaults": false }
```

The following patterns are considered warnings:

```scss
$a: 5 !default;
$a: 1;
```
