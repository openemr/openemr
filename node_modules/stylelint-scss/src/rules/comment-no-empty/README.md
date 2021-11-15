# comment-no-empty

Disallow empty comments. Should be used **instead of** the stylelint's [comment-no-empty](https://stylelint.io/user-guide/rules/comment-no-empty) because the core rule ignores SCSS-like comments.

<!-- prettier-ignore -->
```scss
    /* */
    //
```

To avoid duplicate issues, you must disable the core rule as follows:

```json
{
  "rules": {
    "comment-no-empty": null,
    "scss/comment-no-empty": true
  }
}
```

## Options

### `true`

The following patterns are considered violations:

<!-- prettier-ignore -->
```scss
/**/
```

<!-- prettier-ignore -->
```scss
/* */
```

<!-- prettier-ignore -->
```scss
/*

 */
```

<!-- prettier-ignore -->
```scss
//
```

<!-- prettier-ignore -->
```scss
width: 10px; //
```

The following patterns are _not_ considered violations:

<!-- prettier-ignore -->
```scss
/* comment */
```

<!-- prettier-ignore -->
```scss
/*
 * Multi-line Comment
**/
```
