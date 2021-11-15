# dollar-variable-first-in-block

Require `$`-variable declarations to be placed first in a block (root or a rule).

## Options

### `true`

The following patterns are considered violations:

```scss
@import "1.css";
$var: 200px;
```

```scss
a {
  width: 100px;
  $var: 1;
}
```

The following patterns are _not_ considered warnings:

```scss
$var: 100px;
@import "1.css";
```

```scss
a {
  $var: 1;
  color: red;
}
```

## Optional secondary options

### `ignore: ["comments", "imports"]`

### `"comments"`

The following patterns are _not_ considered violations:

```scss
// Comment
$var: 1;
```

```scss
a {
  // Comment
  $var: 1;
  color: red;
}
```

### `"imports"`

The following patterns are _not_ considered violations:

```scss
@import "1.css";
$var: 1;
```

```scss
@use "sass:color";
$primary-color: #f26e21 !default;
$secondary-color: color.change($primary-color, $alpha: 0.08) !default;
```

```scss
@forward "src/list";
$var1: 100px;
```

### `except: ["root", "at-rule", "function", "mixin", "if-else", "loops"]`

### `"root"`

The following patterns are _not_ considered warnings:

```scss
// Imports
@import "1.css";

// Variables
$var: 1;
```

```scss
/* Imports */
@import "1.css";
// Variables
$var1: 1;
$var2: 1;

a {
  width: 100px;
}
```

### `"at-rule"`

The following patterns are _not_ considered warnings:

```scss
@at-root .class {
  width: 100px;
  $var: 1;
}
```

### `"function"`

The following patterns are _not_ considered warnings:

```scss
@function function-name($numbers1, $numbers2) {
  $var1: 1;

  @each $number in $numbers1 {
    $var1: $var1 + $number;
  }

  $var: 2;

  @each $number in $numbers2 {
    $var2: $var2 + $number;
  }

  @return $var1 + $var2;
}
```

### `"mixin"`

The following patterns are _not_ considered warnings:

```scss
@mixin mixin-name {
  width: 100px;
  $var: 1000px;
  height: $var1;
}
```

### `"if-else"`

The following patterns are _not_ considered warnings:

```scss
@if $direction == up {
  width: 100px;
  $var: 1000px;
}
```

```scss
@if $direction == up {
  width: 100px;
} @else {
  height: 100px;
  $var: 1000px;
}
```

```scss
@if $direction == up {
  width: 100px;
  $var1: 1000px;
} @else {
  height: 100px;
  $var2: 1000px;
}
```

### `"loops"`

The following patterns are _not_ considered warnings:

```scss
@each $size in $sizes {
  width: 100px;
  $var: 1000px;
}
```

```scss
@for $i from 1 through 3 {
  width: 100px;
  $var: 1000px;
}
```

```scss
@while $value > $base {
  width: 100px;
  $var: 1000px;
}
```
