# Figlets

`Zend\Text\Figlet` is a component which enables developers to create a so called FIGlet text.
FIGlet text is a string represented as *ASCII* art.

FIGlets use a special font format, called FLT (FigLet Font). By default, one
standard font is shipped with `Zend\Text\Figlet`, but you can download
additional fonts at [http://www.figlet.org](http://www.figlet.org).

> ## Compressed fonts
>
> `Zend\Text\Figlet` supports gzipped fonts. This means that you can take an
> `.flf` file and gzip it.  To allow `Zend\Text\Figlet` to recognize this, the
> gzipped font must have the extension `.gz`.  Further, to be able to use
> gzipped fonts, you have to have enabled the PHP GZIP extension.

> ## Encoding
>
> `Zend\Text\Figlet` expects your strings to be UTF-8 encoded by default. If
> this is not the case, you can supply the character encoding to the second
> parameter to the `render()` method.

You can define multiple options for a FIGlet. When instantiating
`Zend\Text\Figlet\Figlet`, you can supply an array, a `Traversable` that
supplies both keys and values, or an instance of `Zend\Config\Config`.

- `font`: Defines the font which should be used for rendering. If not defines,
  the built-in font will be used.
- `outputWidth`: Defines the maximum width of the output string. This is used
  for word-wrap as well as justification. Be careful when using small values;
  they may result in an undefined behaviour. The default value is 80.
- `handleParagraphs`: A boolean which indicates how new lines are handled. When
  set to `TRUE`, single new lines are ignored and instead treated as single
  spaces; only multiple new lines will be handled as such. The default value is
  `FALSE`.
- `justification`: May be one of the `Zend\Text\Figlet\Figlet::JUSTIFICATION_*`
  constants, which include `JUSTIFICATION_LEFT`, `JUSTIFICATION_CENTER`, and
  `JUSTIFICATION_RIGHT` The default justification is defined by the
  `rightToLeft` value.
- `rightToLeft`: Defines the direction in which text is written. May be either
  `Zend\Text\Figlet\Figlet::DIRECTION_LEFT_TO_RIGHT` or
  `Zend\Text\Figlet\Figlet::DIRECTION_RIGHT_TO_LEFT`. By default, the setting of
  the font file is used.  When justification is not defined, a text written from
  right-to-left is automatically right-aligned.
- `smushMode`: An integer bitfield which defines how single characters are
  smushed together; can be the sum of multiple values from
  `Zend\Text\Figlet\Figlet::SM_*`. The component defines the following smush
  modes: `SM_EQUAL`, `SM_LOWLINE`, `SM_HIERARCHY`, `SM_PAIR`, `SM_BIGX`,
  `SM_HARDBLANK`, `SM_KERN`, and `SM_SMUSH`. A value of 0 doesn't disable the
  entire smushing, but forces `SM_KERN` to be applied, while a value of -1
  disables it. An explanation of the different smush modes can be found
  [here](http://www.jave.de/figlet/figfont.txt). By default the setting of the
  font file is used. The smush mode option is normally used only by font
  designers testing the various layoutmodes with a new font.

## Basic Usage

```php
$figlet = new Zend\Text\Figlet\Figlet();
echo $figlet->render('Zend');
```

Assuming you are using a monospace font, the above results in the following:

```
  ______    ______    _  __   ______
 |__  //   |  ___||  | \| || |  __ \\
   / //    | ||__    |  ' || | |  \ ||
  / //__   | ||___   | .  || | |__/ ||
 /_____||  |_____||  |_|\_|| |_____//
 `-----`'  `-----`   `-` -`'  -----`
```
