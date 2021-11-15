# selector-no-redundant-nesting-selector

Disallow redundant nesting selectors (`&`).

```scss
p {
  & a {}
//↑
// This type of selector
}
```

The following patterns are considered warnings:

```scss
p {
  & a {}
}
```

```scss
p {
  & > a {}
}
```

```scss
p {
  & .class {}
}
```

```scss
p {
  & + .foo {}
}
```

The following patterns are *not* considered warnings:

```scss
p {
  &.foo {}
}
```

```scss
p {
  .foo > & {}
}
```

```scss
p {
  &,
  .foo,
  .bar {
    margin: 0;
  }
}
```

## Options

`ignoreKeywords`: `["/regex/", /regex/, "string"]`

if you are using Less or some other non-SCSS syntax, the warnings can be disabled by using `ignoreKeywords` option.

For example, you need to ignore the `when` keyword in `less`:

```js
{
  rules: {
    'scss/selector-no-redundant-nesting-selector', [true, { ignoreKeywords: ['when'] }],
  },
}
```

The following patterns are *not* considered warnings:

```less
 @theme: ~'dark';
p {
  & when (@theme = dark) {
    color: #000;
  }
  & when not(@theme = dark) {
    color: #fff;
  }
}
```

Conversely, if you do not use the `ignoreKeywords` option:

```js
{
  rules: {
    'scss/selector-no-redundant-nesting-selector', true,
  },
}
```

The following patterns are considered warnings:

```less
 @theme: ~'dark';
p {
  & when (@theme = dark) {
    color: #000;
  }
  & when not(@theme = dark) {
    color: #fff;
  }
}
```
