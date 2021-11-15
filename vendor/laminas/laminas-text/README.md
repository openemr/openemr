# laminas-text

[![Build Status](https://github.com/laminas/laminas-text/workflows/Continuous%20Integration/badge.svg)](https://github.com/laminas/laminas-text/actions?query=workflow%3A"Continuous+Integration")

`Laminas\Text` is a component to work on text strings. It contains the subcomponents:

- `Laminas\Text\Figlet` that enables developers to create a so called FIGlet text.
  A FIGlet text is a string, which is represented as ASCII art. FIGlets use a
  special font format, called FLT (FigLet Font). By default, one standard font is
  shipped with `Laminas\Text\Figlet`, but you can download additional fonts [here](http://www.figlet.org)
- `Laminas\Text\Table` to create text based tables on the fly with different
  decorators. This can be helpful, if you either want to send structured data in
  text emails, which are used to have mono-spaced fonts, or to display table
  information in a CLI application. `Laminas\Text\Table` supports multi-line
  columns, colspan and align as well.

## Installation

Run the following to install this library:

```bash
$ composer require laminas/laminas-text
```

## Documentation

Browse the documentation online at https://docs.laminas.dev/laminas-text/

## Support

- [Issues](https://github.com/laminas/laminas-text/issues/)
- [Chat](https://laminas.dev/chat/)
- [Forum](https://discourse.laminas.dev/)
