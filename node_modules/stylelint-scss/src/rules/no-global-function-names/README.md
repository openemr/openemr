# no-global-function-names

Disallows the use of global function names, as these global functions are now located inside built-in Sass modules.

A full list of disallowed names (and their alternatives) is located [here](https://github.com/sass/sass/blob/master/accepted/module-system.md#built-in-modules-1)

It is recommended to use the [Sass migrator](https://sass-lang.com/documentation/cli/migrator) to change these global function names automatically.

```scss
@use "sass:color";
a {
    background: color.adjust(#6b717f, $red: 15);
}
```

The following patterns are considered warnings:

```scss
a {
    background: adjust-color(#6b717f, $red: 15);
}
```

The following patterns are *not* considered warnings:

```scss
@use "sass:color";
a {
    background: color.adjust(#6b717f, $red: 15);
}
```