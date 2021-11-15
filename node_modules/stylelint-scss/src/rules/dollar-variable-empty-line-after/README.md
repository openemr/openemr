# dollar-variable-empty-line-after

Require an empty line or disallow empty lines after `$`-variable declarations.

If the `$`-variable declaration is the last declaration in a file, it's ignored.

The `--fix` option on the [command line](https://github.com/stylelint/stylelint/blob/master/docs/user-guide/cli.md#autofixing-errors) can automatically fix all of the problems reported by this rule.

## Options

`string`: `"always"|"never"`

### `"always"`

There *must always* be one empty line after a `$`-variable declaration.

The following patterns are considered warnings:

```scss
$var: 200px;
@import '1.css';
```

```scss
a {
  $var: 1;
}
```

The following patterns are *not* considered warnings:

```scss
$var: 100px; // The last declaration in a stylesheet
```

```scss
$var: 1;

a { color: red; }
```

### `"never"`

There *must never* be an empty line after a `$`-variable declaration.

The following patterns are considered warnings:

```scss
$var: 1;

a { color: red; }
```

The following patterns are *not* considered warnings:

```scss
$var: 100px;
$var2: 200px;
```

```scss
$var: 1;
a {
  width: auto;
}
```

## Optional secondary options

### `except: ["last-nested", "before-comment", "before-dollar-variable"]`

### `"last-nested"`

Reverse the primary option for a `$`-variable declaration if it's the last child of its parent.

For example, with `"always"`:

The following patterns are considered warnings:

```scss
a {
  $var: 1;
  color: red;
}

b {
  $var: 1;

}
```

The following patterns are *not* considered warnings:

```scss
a {
  color: red;
  $var: 1;
}

b {
  $var: 1;

  color: red;
}
```

### `"before-comment"`

Reverse the primary option for `$`-variable declarations that go before comments.

For example, with `"always"`:

The following patterns are *not* considered warnings:

```scss
a {
  $var: 1;
  // comment
}
```

### `"before-dollar-variable"`

Reverse the primary option for `$`-variable declarations that go right after another `$`-variable declaration.

For example, with `"always"`:

The following patterns are considered warnings:

```scss
a {
  $var: 1; // this one is ok
  $var1: 2; // and this one shouldn't have a preceding empty line
  b {
    width: 100px;
  }
}
```

The following patterns are *not* considered warnings:

```scss
a {
  $var: 1;
  $var1: 2;
  
  b {
    width: 100%;
  }
}
```

### `ignore: ["before-comment", "inside-single-line-block"]`

### `"before-comment"`

Ignore `$`-variables that go before a comment.

For example, with `"always"`:

The following patterns are *not* considered warnings:

```scss
$var: 1
// comment

$var2: 1;
/* comment */
```

### `"inside-single-line-block"`

Ignore `$`-variables that are inside single-line blocks.

For example, with `"always"`:

The following patterns are *not* considered warnings:

```scss
a { $var: 10; }
```

### `disableFix: true`

Disables autofixing for this rule.
