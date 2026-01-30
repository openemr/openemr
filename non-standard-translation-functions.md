# Non-Standard Translation Function Definitions

Translation functions should be defined in:
- `library/translation.inc.php`
- `library/htmlspecialchars.inc.php`

The following files define translation-related functions outside these standard locations.
These are thin wrappers around the standard global `xl*` functions.

## interface/modules/zend_modules/module/Application/src/Application/Listener/Listener.php

Adapter class for OpenEMR language conversion within the Zend module system.

| Line | Function | Wraps |
|------|----------|-------|
| 59 | `z_xlt($str)` | `xlt()` |
| 69 | `z_xla($str)` | `xla()` |

## interface/modules/zend_modules/module/Application/src/Application/Helper/TranslatorViewHelper.php

Laminas view helper that decorates OpenEMR translation functions for use in `.phtml` templates.

| Line | Function | Wraps |
|------|----------|-------|
| 27 | `xl($str)` | `xl()` |
